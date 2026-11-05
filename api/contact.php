<?php
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

require_once __DIR__ . '/db.php';

try {
    $raw = file_get_contents('php://input');
    $input = [];
    if (!empty($raw)) {
        $rawClean = preg_replace('/^\xEF\xBB\xBF/', '', trim($raw));
        $input = json_decode($rawClean, true) ?: [];
    }
    if (empty($input) && !empty($_POST)) {
        $input = $_POST;
    }

    // 1. Detección de Bots con Honeypot
    if (!empty($input['_honey']) || !empty($_POST['_honey'])) {
        echo json_encode(['success' => true, 'message' => 'Consulta procesada']);
        exit;
    }

    $nombre  = trim($input['nombre'] ?? '');
    $email   = trim($input['email'] ?? '');
    $asunto  = trim($input['asunto'] ?? 'Consulta general');
    $mensaje = trim($input['mensaje'] ?? '');

    if (empty($nombre) || empty($email) || empty($mensaje)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Campos obligatorios incompletos']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Formato de correo electrónico no válido']);
        exit;
    }

    // 2. Límites de longitud para prevenir DoS / payloads desmedidos
    if (mb_strlen($nombre) > 150 || mb_strlen($email) > 150 || mb_strlen($asunto) > 200 || mb_strlen($mensaje) > 5000) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'La longitud de los campos supera el límite máximo permitido']);
        exit;
    }

    $pdo = getPublicDB();

    // 3. Control de tasa de envíos (Rate Limiting por IP: máx 5 mensajes cada 15 min)
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if (strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
    }

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `rate_limit_contacto` (
            `ip` VARCHAR(45) NOT NULL,
            `intentos` INT NOT NULL DEFAULT 1,
            `primera_peticion` INT NOT NULL,
            PRIMARY KEY (`ip`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $ahora = time();
        $stmtRL = $pdo->prepare("SELECT intentos, primera_peticion FROM rate_limit_contacto WHERE ip = ?");
        $stmtRL->execute([$ip]);
        $rlRow = $stmtRL->fetch();

        if ($rlRow) {
            if ($ahora - $rlRow['primera_peticion'] < 900) {
                if ($rlRow['intentos'] >= 5) {
                    http_response_code(429);
                    echo json_encode(['success' => false, 'error' => 'Has enviado varios mensajes recientemente. Por favor, aguarda 15 minutos antes de enviar otro.']);
                    exit;
                }
                $pdo->prepare("UPDATE rate_limit_contacto SET intentos = intentos + 1 WHERE ip = ?")->execute([$ip]);
            } else {
                $pdo->prepare("UPDATE rate_limit_contacto SET intentos = 1, primera_peticion = ? WHERE ip = ?")->execute([$ahora, $ip]);
            }
        } else {
            $pdo->prepare("INSERT INTO rate_limit_contacto (ip, intentos, primera_peticion) VALUES (?, 1, ?)")->execute([$ip, $ahora]);
        }
    } catch (Exception $eRL) {}

    // Crear tabla si aún no existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS `mensajes_contacto` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nombre` VARCHAR(150) NOT NULL,
        `email` VARCHAR(150) NOT NULL,
        `asunto` VARCHAR(200) DEFAULT 'Consulta general',
        `mensaje` TEXT NOT NULL,
        `fecha_envio` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `leido` TINYINT(1) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $pdo->prepare("INSERT INTO `mensajes_contacto` (`nombre`, `email`, `asunto`, `mensaje`) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre, $email, $asunto, $mensaje]);

    // Intento de envío vía PHP mail() si está soportado en el servidor
    $destinatario = 'infot5@eest5mdp.edu.ar';
    try {
        $stmtMail = $pdo->query("SELECT valor FROM contenido_institucional WHERE clave = 'contacto_email' LIMIT 1");
        $dbMail = $stmtMail ? $stmtMail->fetchColumn() : null;
        if (!empty($dbMail) && filter_var($dbMail, FILTER_VALIDATE_EMAIL)) {
            $destinatario = trim($dbMail);
        }
    } catch (Exception $em) {}
    $mailAsunto = "Nuevo mensaje web E.E.S.T. N.º 5: " . $asunto . " (" . $nombre . ")";
    $mailBody = "Se ha recibido una nueva consulta desde la página web oficial:\n\n"
              . "Nombre: $nombre\n"
              . "Correo: $email\n"
              . "Asunto: $asunto\n"
              . "Fecha: " . date('Y-m-d H:i:s') . "\n\n"
              . "Mensaje:\n$mensaje\n\n"
              . "--------------------------------------------------\n"
              . "Podés responder directamente a este correo para contestarle a $nombre.";
    $headers = "From: web@eest5mdp.edu.ar\r\n"
             . "Reply-To: $email\r\n"
             . "X-Mailer: PHP/" . phpversion();

    @mail($destinatario, $mailAsunto, $mailBody, $headers);

    echo json_encode([
        'success' => true,
        'message' => 'Mensaje recibido y registrado correctamente'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'No se pudo guardar la consulta: ' . $e->getMessage()
    ]);
}
