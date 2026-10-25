<?php
// Servidor de imágenes dinámicas y archivos subidos desde TiDB Cloud o disco local
require_once __DIR__ . '/db.php';

$filename = isset($_GET['file']) ? basename($_GET['file']) : '';
if (empty($filename)) {
    http_response_code(400);
    exit('Nombre de archivo no especificado');
}

// 1. Si existe físicamente en /img/ en disco, redirigir o servir
$localPath = __DIR__ . '/../img/' . $filename;
if (file_exists($localPath) && is_file($localPath)) {
    $mime = mime_content_type($localPath) ?: 'image/jpeg';
    header('Content-Type: ' . $mime);
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
        header('Content-Type: ' . $row['mime']);
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
