<?php
// ARCHIVO: sys-control-98/api/manage.php
// QUÉ HACE: recibe TODAS las operaciones del panel: guardar, editar, borrar y subir archivos.
// QUIÉN LO LLAMA: sys-control-98/js/admin.js.
// ORDEN: sesión (401), inactividad, token CSRF (403), acción pedida, validación y consulta preparada.
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

// Error con un mensaje pensado para mostrarle al administrador.
class UserFacingError extends RuntimeException {}

// MOSTRAR-SESION: primera barrera. Sin sesión iniciada responde 401.
if (empty($_SESSION['admin_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Acceso denegado: sesión no iniciada']);
    exit;
}

// Timeout por inactividad de 30 minutos
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Tu sesión ha expirado por inactividad. Recarga la página para ingresar.']);
    exit;
}
$_SESSION['last_activity'] = time();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si el cuerpo supera post_max_size, PHP vacía $_POST y parecería un fallo de CSRF.
    if (empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0
        && stripos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false) {
        http_response_code(413);
        echo json_encode(['success' => false, 'error' => 'El archivo es demasiado grande para el servidor.']);
        exit;
    }

    // MOSTRAR-CSRF: segunda barrera. Sin token válido responde 403.
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!verifyCSRFToken($token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Validación de seguridad CSRF fallida. Recarga la página.']);
        exit;
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$pdo = getDB();

// Lee un texto de $_POST validando obligatoriedad y largo máximo.
// Lee un texto del formulario y controla que no falte ni sea demasiado largo.
function postText(string $name, string $label, int $max, bool $required = false, string $default = ''): string {
    $value = $_POST[$name] ?? $default;
    if (!is_string($value)) {
        throw new UserFacingError("El campo \"$label\" no es válido.");
    }
    $value = trim($value);
    if ($required && $value === '') {
        throw new UserFacingError("El campo \"$label\" es obligatorio.");
    }
    if (mb_strlen($value) > $max) {
        throw new UserFacingError("El campo \"$label\" supera los $max caracteres.");
    }
    return $value;
}

// Referencia a una imagen: nombre de archivo simple o URL http(s).
function validImageRef(string $value): string {
    if ($value === '' || preg_match('/^[A-Za-z0-9._ -]+$/', $value) || preg_match('#^https?://#i', $value)) {
        return $value;
    }
    throw new UserFacingError('El nombre de archivo de la imagen no es válido.');
}

// Solo acepta enlaces http o https. Rechaza, por ejemplo, javascript:
function validExternalUrl(string $value): string {
    if ($value === '') {
        return '';
    }
    if (filter_var($value, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $value)) {
        return $value;
    }
    throw new UserFacingError('El enlace debe ser una dirección http:// o https:// válida.');
}

function requireId(): int {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        throw new UserFacingError('Identificador no válido.');
    }
    return $id;
}

function insertUploadedFile(PDO $db, string $name, string $mime, string $data): void {
    $stmt = $db->prepare("INSERT INTO archivos_subidos (nombre, mime, datos) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE mime = VALUES(mime), datos = VALUES(datos)");
    $stmt->execute([$name, $mime, $data]);
}

// Valida y guarda un archivo. Devuelve null si no se eligió ninguno.
// Un archivo rechazado detiene la operación: el administrador tiene que enterarse.
// MOSTRAR-ARCHIVOS: controla y guarda un archivo subido.
function saveUploadedFile($file, $prefix = 'img') {
    if (!is_array($file) || !is_string($file['name'] ?? null) || $file['name'] === '' || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    // MOSTRAR-ARCHIVOS: control 1, tamaño. 12 MB, o 4 MB en Vercel.
    $maxBytes = appMaxUploadBytes();
    $maxMb = (int)round($maxBytes / 1048576);
    $displayName = basename($file['name']);

    if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || $file['size'] > $maxBytes) {
        throw new UserFacingError("\"$displayName\" supera el máximo de $maxMb MB.");
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new UserFacingError("No se pudo recibir \"$displayName\". Volvé a intentarlo.");
    }

    // MOSTRAR-ARCHIVOS: control 2, lista de extensiones permitidas.
    $allowed = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'webp' => ['image/webp'],
        'gif'  => ['image/gif'],
        'mp4'  => ['video/mp4', 'application/mp4'],
        'webm' => ['video/webm'],
        'pdf'  => ['application/pdf']
    ];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!array_key_exists($ext, $allowed)) {
        throw new UserFacingError("El tipo de archivo de \"$displayName\" no está permitido.");
    }

    // Tipo real según los bytes del archivo. Si no se puede comprobar, no se acepta.
    // MOSTRAR-ARCHIVOS: control 3, finfo lee el tipo real en los bytes del archivo.
    $realMime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = (string)finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    } elseif (function_exists('mime_content_type')) {
        $realMime = (string)mime_content_type($file['tmp_name']);
    }
    if ($realMime === '') {
        throw new UserFacingError('El servidor no puede verificar el tipo de archivo.');
    }
    if (!in_array($realMime, $allowed[$ext], true)) {
        throw new UserFacingError("\"$displayName\" no es realmente un archivo .$ext.");
    }

    // MOSTRAR-ARCHIVOS: control 4, el nombre lo genera el servidor.
    $newFileName = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $fileData = file_get_contents($file['tmp_name']);
    if ($fileData === false) {
        throw new UserFacingError("No se pudo leer \"$displayName\".");
    }

    // Base de datos: es lo único que persiste en serverless.
    $db = getDB();
    $savedInDb = false;
    try {
        insertUploadedFile($db, $newFileName, $realMime, $fileData);
        $savedInDb = true;
    // Si algo falla, el detalle queda en el log y el navegador recibe un mensaje general.
    } catch (Exception $e) {
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `archivos_subidos` (
              `nombre` VARCHAR(255) NOT NULL,
              `mime` VARCHAR(100) NOT NULL,
              `datos` MEDIUMBLOB NOT NULL,
              `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`nombre`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            insertUploadedFile($db, $newFileName, $realMime, $fileData);
            $savedInDb = true;
        } catch (Exception $e2) {
            error_log('saveUploadedFile: ' . $e2->getMessage());
        }
    }

    // Disco: solo donde es escribible (XAMPP / servidor dedicado).
    $savedOnDisk = false;
    $targetDir = __DIR__ . '/../../img';
    if (is_dir($targetDir) && is_writable($targetDir)) {
        $savedOnDisk = @file_put_contents($targetDir . '/' . $newFileName, $fileData) !== false;
    }

    if (!$savedInDb && !$savedOnDisk) {
        throw new UserFacingError("No se pudo guardar \"$displayName\". Probá con un archivo más liviano.");
    }

    return $newFileName;
}

try {
    // MOSTRAR-PANEL: según la acción pedida se ejecuta un solo bloque (case).
    switch ($action) {
        // --- TEXTOS E IMÁGENES INSTITUCIONALES ---
        // La base de datos es la única fuente de verdad: las páginas públicas la leen por la API.
        // Guarda los textos e imágenes de las pestañas en contenido_institucional.
        case 'save_content':
            $data = $_POST['content'] ?? [];
            if (!is_array($data)) {
                throw new UserFacingError('Contenido no válido.');
            }

            if (!empty($_FILES)) {
                foreach ($_FILES as $key => $file) {
                    $saved = saveUploadedFile($file, 'sec_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $key));
                    if ($saved) {
                        $data[$key] = $saved;
                    }
                }
            }

            foreach ($data as $clave => $valor) {
                if (!is_string($clave) || !preg_match('/^[a-z][a-z0-9_]{0,99}$/', $clave) || !is_string($valor)) {
                    throw new UserFacingError('Se recibió un campo de contenido no válido.');
                }
            }

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO contenido_institucional (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
            foreach ($data as $clave => $valor) {
                $stmt->execute([$clave, $valor]);
            }
            $pdo->commit();

            echo json_encode(['success' => true, 'message' => 'Contenido actualizado correctamente', 'data' => $data]);
            break;

        // --- ESPECIALIDADES ---
        // Crea o edita una especialidad. Si llega un id edita; si no, crea.
        case 'save_especialidad':
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nombre = postText('nombre', 'Nombre', 100, true);
            $titulo = postText('titulo', 'Título', 150);
            $descripcion = postText('descripcion', 'Descripción', 65000, true);
            $icono = postText('icono', 'Icono', 50, false, 'hammer') ?: 'hammer';
            $duracion = (int)($_POST['duracion_anios'] ?? 7);
            if ($duracion < 1 || $duracion > 10) {
                throw new UserFacingError('Los años de duración deben estar entre 1 y 10.');
            }
            $imagen = validImageRef(postText('imagen', 'Imagen', 255));

            if (!empty($_FILES['imagen_archivo']['name'])) {
                $saved = saveUploadedFile($_FILES['imagen_archivo'], 'especialidad');
                if ($saved) {
                    $imagen = $saved;
                }
            }

            // Esquemas antiguos en la nube pueden no tener todas las columnas.
            try {
                $cols = $pdo->query("SHOW COLUMNS FROM especialidades")->fetchAll(PDO::FETCH_COLUMN);
                $missing = [
                    'titulo' => "ADD COLUMN titulo VARCHAR(150) NULL DEFAULT '' AFTER nombre",
                    'imagen' => "ADD COLUMN imagen VARCHAR(255) NULL DEFAULT '' AFTER descripcion",
                    'color'  => "ADD COLUMN color VARCHAR(50) NULL DEFAULT 'esp--verde'",
                    'numero' => "ADD COLUMN numero VARCHAR(10) NULL DEFAULT '01'",
                ];
                foreach ($missing as $col => $ddl) {
                    if (!in_array($col, $cols, true)) {
                        $pdo->exec("ALTER TABLE especialidades $ddl");
                    }
                }
            } catch (Exception $e) {
                error_log('save_especialidad columnas: ' . $e->getMessage());
            }

            if ($id) {
                $sql = "UPDATE especialidades SET nombre = :nombre, titulo = :titulo, descripcion = :descripcion, icono = :icono, duracion_anios = :duracion" . ($imagen !== '' ? ", imagen = :imagen" : "") . " WHERE id = :id";
                $params = ['nombre' => $nombre, 'titulo' => $titulo, 'descripcion' => $descripcion, 'icono' => $icono, 'duracion' => $duracion, 'id' => $id];
                if ($imagen !== '') {
                    $params['imagen'] = $imagen;
                }
                $pdo->prepare($sql)->execute($params);
            } else {
                $count = (int)$pdo->query("SELECT COUNT(*) FROM especialidades")->fetchColumn();
                $maxNumero = (int)$pdo->query("SELECT COALESCE(MAX(CAST(numero AS UNSIGNED)), 0) FROM especialidades")->fetchColumn();
                $colores = ['esp--verde', 'esp--rojo', 'esp--azul'];
                $stmt = $pdo->prepare("INSERT INTO especialidades (nombre, titulo, descripcion, icono, duracion_anios, imagen, color, numero) VALUES (:nombre, :titulo, :descripcion, :icono, :duracion, :imagen, :color, :numero)");
                $stmt->execute([
                    'nombre' => $nombre, 'titulo' => $titulo, 'descripcion' => $descripcion, 'icono' => $icono,
                    'duracion' => $duracion, 'imagen' => $imagen,
                    'color' => $colores[$count % 3], 'numero' => sprintf('%02d', $maxNumero + 1)
                ]);
            }

            echo json_encode(['success' => true, 'imagen' => $imagen]);
            break;

        case 'delete_especialidad':
            $stmt = $pdo->prepare("DELETE FROM especialidades WHERE id = :id");
            $stmt->execute(['id' => requireId()]);
            echo json_encode(['success' => true]);
            break;

        // --- HITOS HISTÓRICOS ---
        // Crea o edita un hito de la línea de tiempo.
        case 'save_hito':
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $anio = postText('anio', 'Año', 50, true);
            $titulo = postText('titulo', 'Título', 200, true);
            $resumen = postText('resumen', 'Resumen', 65000, true);
            $descripcion = postText('descripcion', 'Descripción', 65000, true);
            $especialidad = postText('especialidad', 'Especialidad', 100);
            $destino = postText('destino', 'Destino', 150);
            $premio = postText('premio', 'Premio', 150);
            $enlace = validExternalUrl(postText('enlace', 'Enlace', 255));
            $orden = (int)($_POST['orden'] ?? 1);
            $imagen = validImageRef(postText('imagen', 'Imagen', 255));

            if (!empty($_FILES['imagen_archivo']['name'])) {
                $saved = saveUploadedFile($_FILES['imagen_archivo'], 'hito');
                if ($saved) {
                    $imagen = $saved;
                }
            }

            $params = ['a' => $anio, 't' => $titulo, 'r' => $resumen, 'd' => $descripcion, 'esp' => $especialidad, 'des' => $destino, 'pre' => $premio, 'enl' => $enlace, 'ord' => $orden];
            if ($id) {
                $sql = "UPDATE hitos_historicos SET anio = :a, titulo = :t, resumen = :r, descripcion = :d, especialidad = :esp, destino = :des, premio = :pre, enlace = :enl, orden = :ord" . ($imagen !== '' ? ", imagen = :img" : "") . " WHERE id = :id";
                $params['id'] = $id;
                if ($imagen !== '') {
                    $params['img'] = $imagen;
                }
                $pdo->prepare($sql)->execute($params);
            } else {
                $params['img'] = $imagen;
                $pdo->prepare("INSERT INTO hitos_historicos (anio, titulo, resumen, descripcion, imagen, especialidad, destino, premio, enlace, orden) VALUES (:a, :t, :r, :d, :img, :esp, :des, :pre, :enl, :ord)")->execute($params);
            }
            echo json_encode(['success' => true]);
            break;

        case 'delete_hito':
            $stmt = $pdo->prepare("DELETE FROM hitos_historicos WHERE id = :id");
            $stmt->execute(['id' => requireId()]);
            echo json_encode(['success' => true]);
            break;

        // --- PROYECTOS ---
        // Crea o edita un proyecto de ExpoTec.
        case 'save_proyecto':
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $titulo = postText('titulo', 'Título', 150, true);
            $subtitulo = postText('subtitulo', 'Subtítulo', 255);
            $descripcion = postText('descripcion', 'Descripción', 65000, true);
            $especialidad_id = (int)($_POST['especialidad_id'] ?? 0);
            $anio = (int)($_POST['anio'] ?? date('Y'));
            $estado = postText('estado', 'Estado', 20, false, 'En desarrollo');
            $destacado = !empty($_POST['destacado']) ? 1 : 0;
            $imagen = validImageRef(postText('imagen', 'Imagen', 255));

            if (!in_array($estado, ['En desarrollo', 'Finalizado', 'En ExpoTec'], true)) {
                throw new UserFacingError('El estado del proyecto no es válido.');
            }
            if ($anio < 1900 || $anio > 2100) {
                throw new UserFacingError('El año del proyecto no es válido.');
            }
            $check = $pdo->prepare("SELECT COUNT(*) FROM especialidades WHERE id = ?");
            $check->execute([$especialidad_id]);
            if (!(int)$check->fetchColumn()) {
                throw new UserFacingError('La especialidad seleccionada no existe.');
            }

            if (!empty($_FILES['imagen_archivo']['name'])) {
                $saved = saveUploadedFile($_FILES['imagen_archivo'], 'proy');
                if ($saved) {
                    $imagen = $saved;
                }
            }

            $params = ['t' => $titulo, 's' => $subtitulo, 'd' => $descripcion, 'e' => $especialidad_id, 'a' => $anio, 'es' => $estado, 'des' => $destacado];
            if ($id) {
                $sql = "UPDATE proyectos SET titulo = :t, subtitulo = :s, descripcion = :d, especialidad_id = :e, anio = :a, estado = :es, destacado = :des" . ($imagen !== '' ? ", imagen = :img" : "") . " WHERE id = :id";
                $params['id'] = $id;
                if ($imagen !== '') {
                    $params['img'] = $imagen;
                }
                $pdo->prepare($sql)->execute($params);
            } else {
                $params['img'] = $imagen;
                $params['aut'] = $_SESSION['admin_user']['id'];
                $pdo->prepare("INSERT INTO proyectos (titulo, subtitulo, descripcion, especialidad_id, anio, imagen, estado, autor_id, destacado) VALUES (:t, :s, :d, :e, :a, :img, :es, :aut, :des)")->execute($params);
            }
            echo json_encode(['success' => true]);
            break;

        case 'delete_proyecto':
            $stmt = $pdo->prepare("DELETE FROM proyectos WHERE id = :id");
            $stmt->execute(['id' => requireId()]);
            echo json_encode(['success' => true]);
            break;

        // --- SUBIR IMAGEN GENERAL ---
        case 'upload_image':
            $saved = saveUploadedFile($_FILES['image'] ?? null, 'img');
            if (!$saved) {
                throw new UserFacingError('No se seleccionó ningún archivo.');
            }
            echo json_encode(['success' => true, 'filename' => $saved, 'url' => '/img/' . $saved]);
            break;

        // --- CAMBIAR CONTRASEÑA ADMIN ---
        case 'change_password':
            $current = (string)($_POST['current_password'] ?? '');
            $newPass = (string)($_POST['new_password'] ?? '');
            if (strlen($newPass) < 8) {
                throw new UserFacingError('La contraseña debe tener al menos 8 caracteres.');
            }
            if (strlen($newPass) > 72) {
                throw new UserFacingError('La contraseña no puede superar los 72 caracteres.');
            }
            $adminId = $_SESSION['admin_user']['id'];
            $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = :id");
            $stmt->execute(['id' => $adminId]);
            $storedHash = (string)$stmt->fetchColumn();
            if ($current === '' || !password_verify($current, $storedHash)) {
                throw new UserFacingError('La contraseña actual no es correcta.');
            }
            // MOSTRAR-BCRYPT: guarda la clave nueva como hash bcrypt de costo 12.
            $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare("UPDATE usuarios SET password = :hash WHERE id = :id");
            $stmt->execute(['hash' => $hash, 'id' => $adminId]);
            session_regenerate_id(true);
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada con éxito']);
            break;

        // --- MENSAJES DE CONTACTO RECIBIDOS ---
        case 'get_mensajes_contacto':
            $stmt = $pdo->query("SELECT * FROM mensajes_contacto ORDER BY fecha_envio DESC");
            echo json_encode(['success' => true, 'mensajes' => $stmt->fetchAll()]);
            break;

        // Marca un mensaje de contacto como leído o no leído.
        case 'toggle_mensaje_leido':
            $id = requireId();
            $stmt = $pdo->prepare("UPDATE mensajes_contacto SET leido = 1 - COALESCE(leido, 0) WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $stmt = $pdo->prepare("SELECT leido FROM mensajes_contacto WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['success' => true, 'leido' => (int)$stmt->fetchColumn()]);
            break;

        case 'delete_mensaje_contacto':
            $stmt = $pdo->prepare("DELETE FROM mensajes_contacto WHERE id = :id");
            $stmt->execute(['id' => requireId()]);
            echo json_encode(['success' => true]);
            break;

        default:
            throw new UserFacingError('Acción no válida.');
    }
} catch (UserFacingError $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // El detalle queda en el log: nunca se le envía SQL al navegador.
    error_log('manage.php [' . $action . ']: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'No se pudo completar la operación. Intentá de nuevo.']);
}
