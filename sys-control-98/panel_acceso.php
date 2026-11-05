<?php
// Ruta rotativa del panel: /sys-control-<token>/. Cambia en cada inicio de sesión y el enlace nuevo se envía por correo.
// El token solo oculta la ruta: el acceso sigue exigiendo usuario, contraseña, sesión y CSRF.
// Se activa cuando ADMIN_ALERT_EMAIL está definido en un entorno serverless.
//
// Recuperación si se pierde el enlace, desde la base de datos:
//   DELETE FROM panel_acceso;
// El panel vuelve a responder en la ruta inicial /sys-control-98/.

function panelEnsureTable(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `panel_acceso` (
        `id` TINYINT NOT NULL,
        `token_hash` CHAR(64) NOT NULL,
        `actualizado` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

function panelStoredHash(PDO $pdo): ?string {
    try {
        $hash = $pdo->query("SELECT token_hash FROM panel_acceso WHERE id = 1")->fetchColumn();
    } catch (Exception $e) {
        panelEnsureTable($pdo);
        return null;
    }
    return $hash ?: null;
}

// Sin token guardado, la ruta válida es la inicial. Con token, solo la vigente.
function panelSlugIsValid(PDO $pdo, string $slug): bool {
    $stored = panelStoredHash($pdo);
    return $stored === null ? $slug === '98' : hash_equals($stored, hash('sha256', $slug));
}

function panelAlertEmail(): ?string {
    $email = trim((string)getenv('ADMIN_ALERT_EMAIL'));
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}

function panelRotationEnabled(): bool {
    return panelAlertEmail() !== null && (appIsServerless() || getenv('PANEL_ROTATE_TEST') === '1');
}

function panelBaseUrl(): string {
    $base = trim((string)getenv('PANEL_BASE_URL'));
    if ($base !== '') {
        return rtrim($base, '/');
    }
    $proto = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' || ($_SERVER['HTTPS'] ?? '') === 'on' ? 'https' : 'http';
    return $proto . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

// Devuelve true solo si el servicio confirmó el envío.
function panelSendLink(string $link, string $usuario, string $ip): bool {
    $to = panelAlertEmail();
    if ($to === null || !function_exists('curl_init')) {
        return false;
    }
    $endpoint = getenv('PANEL_MAIL_ENDPOINT') ?: 'https://formsubmit.co/ajax/' . rawurlencode($to);
    $origin = panelBaseUrl();
    $payload = json_encode([
        '_subject' => 'Nuevo enlace del panel E.E.S.T. N.º 5',
        '_template' => 'table',
        '_captcha' => 'false',
        'Aviso' => 'Se inició sesión en el panel. El enlace anterior dejó de funcionar.',
        'Nuevo enlace' => $link,
        'Usuario' => $usuario,
        'IP' => $ip,
        'Fecha (UTC)' => gmdate('Y-m-d H:i:s'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Origin: ' . $origin,
            'Referer: ' . $origin . '/',
        ],
    ]);
    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $data = is_string($body) ? json_decode($body, true) : null;
    $sent = $status === 200 && is_array($data) && in_array($data['success'] ?? null, [true, 'true'], true);
    if (!$sent) {
        // Sin datos del enlace: solo el resultado del servicio.
        error_log('panel_correo_fallido status=' . $status . ' error=' . $error . ' mensaje=' . substr((string)($data['message'] ?? ''), 0, 120));
    }
    return $sent;
}

// Tras un login correcto: genera y envía el enlace nuevo, y recién entonces lo activa.
// Devuelve la ruta nueva, o null si la ruta actual sigue vigente.
function panelRotateAfterLogin(PDO $pdo, string $usuario, string $ip): ?string {
    if (!panelRotationEnabled()) {
        return null;
    }
    $token = bin2hex(random_bytes(16));
    $path = '/sys-control-' . $token . '/login.php';
    if (!panelSendLink(panelBaseUrl() . $path, $usuario, $ip)) {
        $_SESSION['panel_aviso'] = 'No se pudo enviar el correo con el enlace nuevo: la ruta del panel no cambió.';
        return null;
    }
    panelEnsureTable($pdo);
    $stmt = $pdo->prepare("INSERT INTO panel_acceso (id, token_hash) VALUES (1, ?) ON DUPLICATE KEY UPDATE token_hash = VALUES(token_hash)");
    $stmt->execute([hash('sha256', $token)]);
    unset($_SESSION['panel_aviso']);
    return '/sys-control-' . $token . '/index.php';
}
