<?php
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Acceso denegado: sesión no iniciada']);
    exit;
}

// Timeout por inactividad de 30 minutos
$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
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

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$pdo = getDB();

// Función de subida segura y multiplataforma (compatible con Serverless / Vercel y disco local)
function saveUploadedFile($file, $prefix = 'img') {
    if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'webm', 'pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return null;
    }

    $newFileName = $prefix . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
    $fileData = file_get_contents($file['tmp_name']);
    if ($fileData === false) {
        return null;
    }

    $mime = !empty($file['type']) ? $file['type'] : 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);

    // 1. Guardar en TiDB Cloud / MySQL (archivos_subidos)
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO archivos_subidos (nombre, mime, datos) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE mime = ?, datos = ?");
        $stmt->execute([$newFileName, $mime, $fileData, $mime, $fileData]);
    } catch (Exception $e) {
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `archivos_subidos` (
              `nombre` VARCHAR(255) NOT NULL,
              `mime` VARCHAR(100) NOT NULL,
              `datos` MEDIUMBLOB NOT NULL,
              `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`nombre`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            $stmt = $db->prepare("INSERT INTO archivos_subidos (nombre, mime, datos) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE mime = ?, datos = ?");
            $stmt->execute([$newFileName, $mime, $fileData, $mime, $fileData]);
        } catch (Exception $e2) {}
    }

    // 2. Si el sistema de archivos local es escribible (XAMPP / Servidor dedicado), guardar también en disco
    $targetDir = __DIR__ . '/../../img';
    if (is_dir($targetDir) && is_writable($targetDir)) {
        @file_put_contents($targetDir . '/' . $newFileName, $fileData);
    }

    return $newFileName;
}

try {
    switch ($action) {
        // --- GUARDAR TEXTOS E IMÁGENES INSTITUCIONALES ---
        case 'save_content':
            $data = $_POST['content'] ?? [];
            if (!is_array($data)) {
                $raw = file_get_contents('php://input');
                $json = json_decode($raw, true);
                $data = $json['content'] ?? [];
            }

            // Procesar archivos de imagen, video o documentos subidos en formularios multipart
            if (!empty($_FILES)) {
                foreach ($_FILES as $key => $file) {
                    $saved = saveUploadedFile($file, 'sec_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $key));
                    if ($saved) {
                        $data[$key] = $saved;
                    }
                }
            }

            $stmt = $pdo->prepare("INSERT INTO contenido_institucional (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
            foreach ($data as $clave => $valor) {
                $stmt->execute([$clave, $valor, $valor]);
            }

            // Sincronización estática opcional en disco (solo si es escribible, ej: local XAMPP)
            // 1. ExpoTec (pages/expotec.html)
            $expotecPath = __DIR__ . '/../../pages/expotec.html';
            if (file_exists($expotecPath) && is_writable($expotecPath)) {
                $expoHtml = file_get_contents($expotecPath);
                if (!empty($data['expotec_tag'])) {
                    $expoHtml = preg_replace('/(<section class="page-hero">[\s\S]*?<p class="section-tag[^>]*>)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($data['expotec_tag']) . '$3', $expoHtml);
                }
                if (!empty($data['expotec_titulo'])) {
                    $expoHtml = preg_replace('/(<section class="page-hero">[\s\S]*?<h1 class="page-title">)(.*?)(<\/h1>)/s', '$1' . htmlspecialchars($data['expotec_titulo']) . '$3', $expoHtml);
                }
                if (!empty($data['expotec_desc'])) {
                    $expoHtml = preg_replace('/(<div class="shell expo-intro">[\s\S]*?<p class="lead">)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($data['expotec_desc']) . '$3', $expoHtml);
                }
                if (!empty($data['expotec_desc_secundaria'])) {
                    $expoHtml = preg_replace('/(<div class="shell expo-intro">[\s\S]*?<p class="body">)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($data['expotec_desc_secundaria']) . '$3', $expoHtml);
                }
                if (!empty($data['expotec_frecuencia'])) {
                    $expoHtml = preg_replace('/(<li><span>Frecuencia<\/span><p>)(.*?)(<\/p><\/li>)/s', '$1' . htmlspecialchars($data['expotec_frecuencia']) . '$3', $expoHtml);
                }
                if (!empty($data['expotec_participan'])) {
                    $expoHtml = preg_replace('/(<li><span>Participan<\/span><p>)(.*?)(<\/p><\/li>)/s', '$1' . htmlspecialchars($data['expotec_participan']) . '$3', $expoHtml);
                }
                if (!empty($data['expotec_abierta'])) {
                    $expoHtml = preg_replace('/(<li><span>Abierta a<\/span><p>)(.*?)(<\/p><\/li>)/s', '$1' . htmlspecialchars($data['expotec_abierta']) . '$3', $expoHtml);
                }
                if (!empty($data['expotec_sede'])) {
                    $expoHtml = preg_replace('/(<li><span>Sede<\/span><p>)(.*?)(<\/p><\/li>)/s', '$1' . htmlspecialchars($data['expotec_sede']) . '$3', $expoHtml);
                }
                if (!empty($data['expotec_sectores_tag'])) {
                    $expoHtml = preg_replace('/(<section class="section especialidades">[\s\S]*?<p class="section-tag[^>]*>)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($data['expotec_sectores_tag']) . '$3', $expoHtml);
                }
                if (!empty($data['expotec_sectores_titulo'])) {
                    $expoHtml = preg_replace('/(<section class="section especialidades">[\s\S]*?<h2 class="h2[^>]*>)(.*?)(<\/h2>)/s', '$1' . htmlspecialchars($data['expotec_sectores_titulo']) . '$3', $expoHtml);
                }
                if (!empty($data['expotec_s1_img'])) {
                    $expoHtml = preg_replace('/(<article class="expo-card[^>]*>\s*<img src=")[^"]*("[\s\S]*?<span class="expo-num">01<\/span>)/s', '$1../img/' . $data['expotec_s1_img'] . '$2', $expoHtml);
                }
                if (!empty($data['expotec_s1_titulo'])) {
                    $expoHtml = preg_replace('/(<article class="expo-card[^>]*>[\s\S]*?<span class="expo-num">01<\/span>\s*<h3>)(.*?)(<\/h3>)/s', '$1' . htmlspecialchars($data['expotec_s1_titulo']) . '$3', $expoHtml);
                }
                if (!empty($data['expotec_s1_desc'])) {
                    $expoHtml = preg_replace('/(<article class="expo-card[^>]*>[\s\S]*?<span class="expo-num">01<\/span>[\s\S]*?<h3>.*?<\/h3>\s*<p>)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($data['expotec_s1_desc']) . '$3', $expoHtml);
                }
                if (!empty($data['expotec_s2_img'])) {
                    $expoHtml = preg_replace('/(<article class="expo-card[^>]*>\s*<img src=")[^"]*("[\s\S]*?<span class="expo-num">02<\/span>)/s', '$1../img/' . $data['expotec_s2_img'] . '$2', $expoHtml);
                }
                if (!empty($data['expotec_s2_titulo'])) {
                    $expoHtml = preg_replace('/(<article class="expo-card[^>]*>[\s\S]*?<span class="expo-num">02<\/span>\s*<h3>)(.*?)(<\/h3>)/s', '$1' . htmlspecialchars($data['expotec_s2_titulo']) . '$3', $expoHtml);
                }
                if (!empty($data['expotec_s2_desc'])) {
                    $expoHtml = preg_replace('/(<article class="expo-card[^>]*>[\s\S]*?<span class="expo-num">02<\/span>[\s\S]*?<h3>.*?<\/h3>\s*<p>)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($data['expotec_s2_desc']) . '$3', $expoHtml);
                }
                if (!empty($data['expotec_s3_img'])) {
                    $expoHtml = preg_replace('/(<article class="expo-card[^>]*>\s*<img src=")[^"]*("[\s\S]*?<span class="expo-num">03<\/span>)/s', '$1../img/' . $data['expotec_s3_img'] . '$2', $expoHtml);
                }
                if (!empty($data['expotec_s3_titulo'])) {
                    $expoHtml = preg_replace('/(<article class="expo-card[^>]*>[\s\S]*?<span class="expo-num">03<\/span>\s*<h3>)(.*?)(<\/h3>)/s', '$1' . htmlspecialchars($data['expotec_s3_titulo']) . '$3', $expoHtml);
                }
                if (!empty($data['expotec_s3_desc'])) {
                    $expoHtml = preg_replace('/(<article class="expo-card[^>]*>[\s\S]*?<span class="expo-num">03<\/span>[\s\S]*?<h3>.*?<\/h3>\s*<p>)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($data['expotec_s3_desc']) . '$3', $expoHtml);
                }
                @file_put_contents($expotecPath, $expoHtml);
            }

            // 2. La Escuela (pages/escuela.html)
            $escuelaPath = __DIR__ . '/../../pages/escuela.html';
            if (file_exists($escuelaPath) && is_writable($escuelaPath)) {
                $escHtml = file_get_contents($escuelaPath);
                if (!empty($data['escuela_hero_tag'])) {
                    $escHtml = preg_replace('/(<section class="page-hero">[\s\S]*?<p class="section-tag[^>]*>)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($data['escuela_hero_tag']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_hero_titulo'])) {
                    $escHtml = preg_replace('/(<section class="page-hero">[\s\S]*?<h1 class="page-title">)(.*?)(<\/h1>)/s', '$1' . htmlspecialchars($data['escuela_hero_titulo']) . '$3', $escHtml);
                }
                if (!empty($data['sobre_nosotros'])) {
                    $escHtml = preg_replace('/(<div class="escuela-copy[^>]*>\s*<p class="lead">)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($data['sobre_nosotros']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_egresado_desc'])) {
                    $escHtml = preg_replace('/(<div class="escuela-copy[^>]*>[\s\S]*?<p class="lead">[\s\S]*?<\/p>\s*<p class="body">)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($data['escuela_egresado_desc']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_nivel'])) {
                    $escHtml = preg_replace('/(<dt>Nivel<\/dt><dd>)(.*?)(<\/dd>)/s', '$1' . htmlspecialchars($data['escuela_nivel']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_gestion'])) {
                    $escHtml = preg_replace('/(<dt>Gestión<\/dt><dd>)(.*?)(<\/dd>)/s', '$1' . htmlspecialchars($data['escuela_gestion']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_titulo'])) {
                    $escHtml = preg_replace('/(<dt>Título<\/dt><dd>)(.*?)(<\/dd>)/s', '$1' . htmlspecialchars($data['escuela_titulo']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_ciudad'])) {
                    $escHtml = preg_replace('/(<dt>Ciudad<\/dt><dd>)(.*?)(<\/dd>)/s', '$1' . htmlspecialchars($data['escuela_ciudad']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_foto_fachada'])) {
                    $escHtml = preg_replace('/(<figure class="escuela-foto[^>]*>\s*<img src=")[^"]*(")/s', '$1../img/' . $data['escuela_foto_fachada'] . '$2', $escHtml);
                }
                if (!empty($data['escuela_foto_caption'])) {
                    $escHtml = preg_replace('/(<figcaption><span class="dot" aria-hidden="true"><\/span>)(.*?)(<\/figcaption>)/s', '$1' . htmlspecialchars($data['escuela_foto_caption']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_taller_tag'])) {
                    $escHtml = preg_replace('/(<div class="taller-panel[^>]*>\s*<p class="section-tag">)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($data['escuela_taller_tag']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_taller_titulo'])) {
                    $escHtml = preg_replace('/(<div class="taller-panel[^>]*>[\s\S]*?<h2 class="h2">)(.*?)(<\/h2>)/s', '$1' . htmlspecialchars($data['escuela_taller_titulo']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_taller_desc'])) {
                    $escHtml = preg_replace('/(<div class="taller-panel[^>]*>[\s\S]*?<p class="body">)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($data['escuela_taller_desc']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_foto_taller'])) {
                    $escHtml = preg_replace('/(<div class="taller-media[^>]*>\s*<img src=")[^"]*(")/s', '$1../img/' . $data['escuela_foto_taller'] . '$2', $escHtml);
                }
                if (!empty($data['escuela_paso_1'])) {
                    $escHtml = preg_replace('/(<li><span>01<\/span><p>)(.*?)(<\/p><\/li>)/s', '$1' . htmlspecialchars($data['escuela_paso_1']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_paso_2'])) {
                    $escHtml = preg_replace('/(<li><span>02<\/span><p>)(.*?)(<\/p><\/li>)/s', '$1' . htmlspecialchars($data['escuela_paso_2']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_paso_3'])) {
                    $escHtml = preg_replace('/(<li><span>03<\/span><p>)(.*?)(<\/p><\/li>)/s', '$1' . htmlspecialchars($data['escuela_paso_3']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_paso_4'])) {
                    $escHtml = preg_replace('/(<li><span>04<\/span><p>)(.*?)(<\/p><\/li>)/s', '$1' . htmlspecialchars($data['escuela_paso_4']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_dir_nombre'])) {
                    $escHtml = preg_replace('/(<h3 class="auth-director-name">)(.*?)(<\/h3>)/s', '$1' . htmlspecialchars($data['escuela_dir_nombre']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_dir_cargo'])) {
                    $escHtml = preg_replace('/(<p class="auth-director-role">)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($data['escuela_dir_cargo']) . '$3', $escHtml);
                }
                if (!empty($data['escuela_qr_ra_url'])) {
                    $escHtml = preg_replace('/(href=")[^"]*(" class="btn btn-primary btn-sm">\s*Ver Régimen Académico)/s', '$1' . htmlspecialchars($data['escuela_qr_ra_url']) . '$2', $escHtml);
                }
                if (!empty($data['escuela_qr_aic_url'])) {
                    $escHtml = preg_replace('/(href=")[^"]*(" class="btn btn-primary btn-sm">\s*Ver Acuerdo de Convivencia)/s', '$1' . htmlspecialchars($data['escuela_qr_aic_url']) . '$2', $escHtml);
                }
                @file_put_contents($escuelaPath, $escHtml);
            }

            // 3. Portada (index.html)
            $indexPath = __DIR__ . '/../../index.html';
            if (file_exists($indexPath) && is_writable($indexPath)) {
                $indexHtml = file_get_contents($indexPath);
                if (!empty($data['hero_foto'])) {
                    $indexHtml = preg_replace('/(<div class="hero-media[^>]*>\s*<img src=")[^"]*(")/s', '$1img/' . $data['hero_foto'] . '$2', $indexHtml);
                }
                if (!empty($data['hero_slogan'])) {
                    $indexHtml = preg_replace('/(<p class="hero-slogan">)(.*?)(<\/p>)/s', '$1' . $data['hero_slogan'] . '$3', $indexHtml);
                }
                if (!empty($data['hero_subtitulo'])) {
                    $indexHtml = preg_replace('/(<p class="hero-sub">)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($data['hero_subtitulo']) . '$3', $indexHtml);
                }
                if (!empty($data['anuncios_titulo'])) {
                    $indexHtml = preg_replace('/(<section class="section anuncios[^>]*>[\s\S]*?<h2 class="h2">)(.*?)(<\/h2>)/s', '$1' . htmlspecialchars($data['anuncios_titulo']) . '$3', $indexHtml);
                }
                if (!empty($data['anuncios_subtitulo'])) {
                    $indexHtml = preg_replace('/(<section class="section anuncios[^>]*>[\s\S]*?<p class="anuncios-sub">)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($data['anuncios_subtitulo']) . '$3', $indexHtml);
                }
                if (!empty($data['hitos_titulo'])) {
                    $indexHtml = preg_replace('/(<h2 class="hitos-title">)(.*?)(<\/h2>)/s', '$1' . htmlspecialchars($data['hitos_titulo']) . '$3', $indexHtml);
                }
                if (!empty($data['hitos_descripcion'])) {
                    $indexHtml = preg_replace('/(<p class="hitos-sub">)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($data['hitos_descripcion']) . '$3', $indexHtml);
                }
                if (!empty($data['direccion']) || !empty($data['contacto_email'])) {
                    $dir = $data['direccion'] ?? 'Juan B. Justo 4287';
                    $em = $data['contacto_email'] ?? 'infoT5@eest5mdp.edu.ar';
                    $indexHtml = preg_replace('/(<p class="footer-info">)(.*?)(<\/p>)/s', '$1' . htmlspecialchars($dir . ' · ' . $em) . '$3', $indexHtml);
                }
                @file_put_contents($indexPath, $indexHtml);
            }

            // 4. Contacto (pages/contacto.html)
            $contactoPath = __DIR__ . '/../../pages/contacto.html';
            if (file_exists($contactoPath) && is_writable($contactoPath)) {
                $conHtml = file_get_contents($contactoPath);
                if (!empty($data['contacto_email'])) {
                    $conHtml = preg_replace('/(<p class="rot">Correo<\/p>\s*<p><a href="mailto:)[^"]*(">\s*)(.*?)(<\/a><\/p>)/s', '$1' . htmlspecialchars($data['contacto_email']) . '$2' . htmlspecialchars($data['contacto_email']) . '$4', $conHtml);
                }
                @file_put_contents($contactoPath, $conHtml);
            }

            echo json_encode(['success' => true, 'message' => 'Contenido actualizado correctamente', 'data' => $data]);
            break;

        // --- ESPECIALIDADES ---
        case 'save_especialidad':
            $id = !empty($_POST['id']) ? intval($_POST['id']) : null;
            $nombre = trim($_POST['nombre'] ?? '');
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $icono = trim($_POST['icono'] ?? 'hammer');
            $duracion = intval($_POST['duracion_anios'] ?? 7);
            $imagen = trim($_POST['imagen'] ?? '');

            // Subida de imagen
            if (!empty($_FILES['imagen_archivo']['name'])) {
                $saved = saveUploadedFile($_FILES['imagen_archivo'], 'especialidad');
                if ($saved) {
                    $imagen = $saved;
                }
            }

            // Asegurar columnas en la base de datos
            try {
                $cols = $pdo->query("SHOW COLUMNS FROM especialidades")->fetchAll(PDO::FETCH_COLUMN);
                if (!in_array('titulo', $cols)) {
                    $pdo->exec("ALTER TABLE especialidades ADD COLUMN titulo VARCHAR(150) NULL DEFAULT '' AFTER nombre");
                }
                if (!in_array('imagen', $cols)) {
                    $pdo->exec("ALTER TABLE especialidades ADD COLUMN imagen VARCHAR(255) NULL DEFAULT '' AFTER descripcion");
                }
            } catch (Exception $e) {}

            if ($id) {
                if (!empty($imagen)) {
                    $stmt = $pdo->prepare("UPDATE especialidades SET nombre = :nombre, titulo = :titulo, descripcion = :descripcion, icono = :icono, duracion_anios = :duracion, imagen = :imagen WHERE id = :id");
                    $stmt->execute(['nombre' => $nombre, 'titulo' => $titulo, 'descripcion' => $descripcion, 'icono' => $icono, 'duracion' => $duracion, 'imagen' => $imagen, 'id' => $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE especialidades SET nombre = :nombre, titulo = :titulo, descripcion = :descripcion, icono = :icono, duracion_anios = :duracion WHERE id = :id");
                    $stmt->execute(['nombre' => $nombre, 'titulo' => $titulo, 'descripcion' => $descripcion, 'icono' => $icono, 'duracion' => $duracion, 'id' => $id]);
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO especialidades (nombre, titulo, descripcion, icono, duracion_anios, imagen) VALUES (:nombre, :titulo, :descripcion, :icono, :duracion, :imagen)");
                $stmt->execute(['nombre' => $nombre, 'titulo' => $titulo, 'descripcion' => $descripcion, 'icono' => $icono, 'duracion' => $duracion, 'imagen' => $imagen]);
            }

            // Sincronizar index.html en disco local si es escribible
            if (!empty($imagen) && $id) {
                $indexPath = __DIR__ . '/../../index.html';
                if (file_exists($indexPath) && is_writable($indexPath)) {
                    $indexHtml = file_get_contents($indexPath);
                    $espNums = [1 => '01', 2 => '02', 3 => '03'];
                    if (isset($espNums[$id])) {
                        $num = $espNums[$id];
                        $pattern = '/(<div class="esp-num">' . $num . '<\/div>.*?<div class="esp-foto">\s*<img src=")[^"]*(")/s';
                        $replacement = '$1img/' . $imagen . '$2';
                        $updatedHtml = preg_replace($pattern, $replacement, $indexHtml, 1);
                        if ($updatedHtml && $updatedHtml !== $indexHtml) {
                            @file_put_contents($indexPath, $updatedHtml);
                        }
                    }
                }
            }

            echo json_encode(['success' => true, 'imagen' => $imagen]);
            break;

        case 'delete_especialidad':
            $id = intval($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM especialidades WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['success' => true]);
            break;

        // --- HITOS HISTORICOS (PROYECTOS EMBLEMÁTICOS) ---
        case 'save_hito':
            $id = !empty($_POST['id']) ? intval($_POST['id']) : null;
            $anio = trim($_POST['anio'] ?? date('Y'));
            $titulo = trim($_POST['titulo'] ?? '');
            $resumen = trim($_POST['resumen'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $especialidad = trim($_POST['especialidad'] ?? '');
            $destino = trim($_POST['destino'] ?? '');
            $premio = trim($_POST['premio'] ?? '');
            $enlace = trim($_POST['enlace'] ?? '');
            $orden = intval($_POST['orden'] ?? 1);
            $imagen = trim($_POST['imagen'] ?? '');

            if (!empty($_FILES['imagen_archivo']['name'])) {
                $saved = saveUploadedFile($_FILES['imagen_archivo'], 'hito');
                if ($saved) {
                    $imagen = $saved;
                }
            }

            if ($id) {
                if (!empty($imagen)) {
                    $stmt = $pdo->prepare("UPDATE hitos_historicos SET anio = :a, titulo = :t, resumen = :r, descripcion = :d, imagen = :img, especialidad = :esp, destino = :des, premio = :pre, enlace = :enl, orden = :ord WHERE id = :id");
                    $stmt->execute(['a' => $anio, 't' => $titulo, 'r' => $resumen, 'd' => $descripcion, 'img' => $imagen, 'esp' => $especialidad, 'des' => $destino, 'pre' => $premio, 'enl' => $enlace, 'ord' => $orden, 'id' => $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE hitos_historicos SET anio = :a, titulo = :t, resumen = :r, descripcion = :d, especialidad = :esp, destino = :des, premio = :pre, enlace = :enl, orden = :ord WHERE id = :id");
                    $stmt->execute(['a' => $anio, 't' => $titulo, 'r' => $resumen, 'd' => $descripcion, 'esp' => $especialidad, 'des' => $destino, 'pre' => $premio, 'enl' => $enlace, 'ord' => $orden, 'id' => $id]);
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO hitos_historicos (anio, titulo, resumen, descripcion, imagen, especialidad, destino, premio, enlace, orden) VALUES (:a, :t, :r, :d, :img, :esp, :des, :pre, :enl, :ord)");
                $stmt->execute(['a' => $anio, 't' => $titulo, 'r' => $resumen, 'd' => $descripcion, 'img' => $imagen, 'esp' => $especialidad, 'des' => $destino, 'pre' => $premio, 'enl' => $enlace, 'ord' => $orden]);
            }
            echo json_encode(['success' => true]);
            break;

        case 'delete_hito':
            $id = intval($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM hitos_historicos WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['success' => true]);
            break;

        // --- PROYECTOS ---
        case 'save_proyecto':
            $id = !empty($_POST['id']) ? intval($_POST['id']) : null;
            $titulo = trim($_POST['titulo'] ?? '');
            $subtitulo = trim($_POST['subtitulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $especialidad_id = intval($_POST['especialidad_id'] ?? 1);
            $anio = intval($_POST['anio'] ?? date('Y'));
            $estado = trim($_POST['estado'] ?? 'En desarrollo');
            $destacado = !empty($_POST['destacado']) ? 1 : 0;
            $imagen = trim($_POST['imagen'] ?? '');

            if (!empty($_FILES['imagen_archivo']['name'])) {
                $saved = saveUploadedFile($_FILES['imagen_archivo'], 'proy');
                if ($saved) {
                    $imagen = $saved;
                }
            }

            if ($id) {
                if (!empty($imagen)) {
                    $stmt = $pdo->prepare("UPDATE proyectos SET titulo = :t, subtitulo = :s, descripcion = :d, especialidad_id = :e, anio = :a, estado = :es, destacado = :des, imagen = :img WHERE id = :id");
                    $stmt->execute(['t' => $titulo, 's' => $subtitulo, 'd' => $descripcion, 'e' => $especialidad_id, 'a' => $anio, 'es' => $estado, 'des' => $destacado, 'img' => $imagen, 'id' => $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE proyectos SET titulo = :t, subtitulo = :s, descripcion = :d, especialidad_id = :e, anio = :a, estado = :es, destacado = :des WHERE id = :id");
                    $stmt->execute(['t' => $titulo, 's' => $subtitulo, 'd' => $descripcion, 'e' => $especialidad_id, 'a' => $anio, 'es' => $estado, 'des' => $destacado, 'id' => $id]);
                }
            } else {
                $autor_id = $_SESSION['admin_user']['id'] ?? 1;
                $stmt = $pdo->prepare("INSERT INTO proyectos (titulo, subtitulo, descripcion, especialidad_id, anio, imagen, estado, autor_id, destacado) VALUES (:t, :s, :d, :e, :a, :img, :es, :aut, :des)");
                $stmt->execute(['t' => $titulo, 's' => $subtitulo, 'd' => $descripcion, 'e' => $especialidad_id, 'a' => $anio, 'img' => $imagen, 'es' => $estado, 'aut' => $autor_id, 'des' => $destacado]);
            }
            echo json_encode(['success' => true]);
            break;

        case 'delete_proyecto':
            $id = intval($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM proyectos WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['success' => true]);
            break;

        // --- SUBIR IMAGEN GENERAL ---
        case 'upload_image':
            if (!empty($_FILES['image']['name'])) {
                $saved = saveUploadedFile($_FILES['image'], 'img');
                if ($saved) {
                    echo json_encode(['success' => true, 'filename' => $saved, 'url' => '/img/' . $saved]);
                    exit;
                }
            }
            echo json_encode(['success' => false, 'error' => 'No se pudo subir la imagen']);
            break;

        // --- CAMBIAR CONTRASEÑA ADMIN ---
        case 'change_password':
            $newPass = trim($_POST['new_password'] ?? '');
            if (strlen($newPass) < 6) {
                echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres']);
                exit;
            }
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            $adminId = $_SESSION['admin_user']['id'];
            $stmt = $pdo->prepare("UPDATE usuarios SET password = :hash WHERE id = :id");
            $stmt->execute(['hash' => $hash, 'id' => $adminId]);
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada con éxito']);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
