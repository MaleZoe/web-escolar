<?php
// Servidor de imágenes dinámicas y archivos subidos desde TiDB Cloud o disco local
header('X-Content-Type-Options: nosniff');
require_once __DIR__ . '/db.php';

$filename = isset($_GET['file']) ? basename($_GET['file']) : '';
if (empty($filename)) {
    http_response_code(400);
    exit('Nombre de archivo no especificado');
}

$safeMimes = [
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'webp' => 'image/webp',
    'gif'  => 'image/gif',
    'mp4'  => 'video/mp4',
    'webm' => 'video/webm',
    'pdf'  => 'application/pdf'
];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
if (!array_key_exists($ext, $safeMimes)) {
    http_response_code(403);
    exit('Tipo de archivo no permitido');
}

// 1. Si existe físicamente en /img/ en disco, redirigir o servir
$localPath = __DIR__ . '/../img/' . $filename;
if (file_exists($localPath) && is_file($localPath)) {
    $detectedMime = mime_content_type($localPath) ?: $safeMimes[$ext];
    header('Content-Type: ' . $detectedMime);
    header('Cache-Control: public, max-age=86400');
    readfile($localPath);
    exit;
}

// 2. Buscar en la base de datos (archivos_subidos)
try {
    $pdo = getPublicDB();
    $stmt = $pdo->prepare("SELECT mime, datos FROM archivos_subidos WHERE nombre = :nom LIMIT 1");
    $stmt->execute(['nom' => $filename]);
    $row = $stmt->fetch();

    if ($row && !empty($row['datos'])) {
        $servedMime = in_array($row['mime'], $safeMimes, true) ? $row['mime'] : $safeMimes[$ext];
        header('Content-Type: ' . $servedMime);
        header('Cache-Control: public, max-age=86400, stale-while-revalidate=604800');
        header('Content-Length: ' . strlen($row['datos']));
        echo $row['datos'];
        exit;
    }
} catch (Exception $e) {
    // Silencio en caso de error
}

// 3. Si no existe, 404
http_response_code(404);
header('Content-Type: text/plain');
echo 'Archivo no encontrado';
