<?php
// ARCHIVO: sys-control-98/panel_acceso.php
// QUÉ HACE: cambia la dirección del panel en cada inicio de sesión y envía la nueva por correo.
// Ruta rotativa del panel: /sys-control-<token>/. Cambia en cada inicio de sesión y el enlace nuevo se envía por correo.
// El token solo oculta la ruta: el acceso sigue exigiendo usuario, contraseña, sesión y CSRF.
// Se activa cuando ADMIN_ALERT_EMAIL está definido en un entorno serverless.
// El correo sale por Resend si RESEND_API_KEY está definida; si no, por FormSubmit.
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
// MOSTRAR-ROTACION: decide si la dirección pedida es la vigente. En la base solo se guarda el hash.
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

// POST JSON al servicio de correo. Devuelve el estado HTTP y la respuesta decodificada.
function panelPostJson(string $url, array $payload, array $headers): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json', 'Accept: application/json'], $headers),
    ]);
    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    $data = is_string($body) ? json_decode($body, true) : null;
    return [$status, is_array($data) ? $data : [], $error];
}

// Devuelve true solo si el servicio confirmó el envío.
// Con RESEND_API_KEY se usa solo Resend; el enlace nunca pasa por FormSubmit.
// MOSTRAR-ROTACION: envía el enlace por Resend. Devuelve true solo si el servicio confirma el envío.
function panelSendLink(string $link, string $usuario, string $ip): bool {
    $to = panelAlertEmail();
    if ($to === null || !function_exists('curl_init')) {
        return false;
    }
    $fecha = gmdate('Y-m-d H:i:s');
    $asunto = 'Nuevo enlace del panel E.E.S.T. N.º 5';
    $apiKey = trim((string)getenv('RESEND_API_KEY'));

    if ($apiKey !== '') {
        $texto = "Se inició sesión en el panel. El enlace anterior dejó de funcionar.\n\n"
               . "Nuevo enlace: $link\n\nUsuario: $usuario\nIP: $ip\nFecha (UTC): $fecha\n";
        [$status, $data, $error] = panelPostJson(
            getenv('PANEL_MAIL_ENDPOINT') ?: 'https://api.resend.com/emails',
            ['from' => getenv('RESEND_FROM') ?: 'Panel EEST5 <onboarding@resend.dev>', 'to' => [$to], 'subject' => $asunto, 'text' => $texto],
            ['Authorization: Bearer ' . $apiKey]
        );
        $sent = $status >= 200 && $status < 300 && !empty($data['id']);
    } else {
        $origin = panelBaseUrl();
        [$status, $data, $error] = panelPostJson(
            getenv('PANEL_MAIL_ENDPOINT') ?: 'https://formsubmit.co/ajax/' . rawurlencode($to),
            [
                '_subject' => $asunto, '_template' => 'table', '_captcha' => 'false',
                'Aviso' => 'Se inició sesión en el panel. El enlace anterior dejó de funcionar.',
                'Nuevo enlace' => $link, 'Usuario' => $usuario, 'IP' => $ip, 'Fecha (UTC)' => $fecha,
            ],
            ['Origin: ' . $origin, 'Referer: ' . $origin . '/']
        );
        $sent = $status === 200 && in_array($data['success'] ?? null, [true, 'true'], true);
    }

    if (!$sent) {
        // Sin datos del enlace ni de la clave: solo el resultado del servicio.
        error_log('panel_correo_fallido status=' . $status . ' error=' . $error . ' mensaje=' . substr((string)($data['message'] ?? ''), 0, 120));
    }
    return $sent;
}

// Tras un login correcto: genera y envía el enlace nuevo, y recién entonces lo activa.
// Devuelve la ruta nueva, o null si la ruta actual sigue vigente.
// MOSTRAR-ROTACION: primero envía el correo y después activa la ruta. Así un fallo no deja a nadie afuera.
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
