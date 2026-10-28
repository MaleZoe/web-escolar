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

    $pdo = getPublicDB();

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
