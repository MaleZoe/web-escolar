<?php
// Enrutador serverless para el panel de administración sys-control-98 en Vercel
$uri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($uri, PHP_URL_PATH);

// Normalizar la subruta solicitada dentro de /sys-control-98
$subpath = preg_replace('#^/sys-control-98/?#', '', $path);
$subpath = trim($subpath, '/');

// Manejar rutas internas
if ($subpath === '' || $subpath === 'index.php') {
    require __DIR__ . '/../sys-control-98/index.php';
} elseif ($subpath === 'login.php' || $subpath === 'login') {
    require __DIR__ . '/../sys-control-98/login.php';
} elseif ($subpath === 'logout.php' || $subpath === 'logout') {
    require __DIR__ . '/../sys-control-98/logout.php';
} elseif (strpos($subpath, 'manage.php') !== false) {
    require __DIR__ . '/../sys-control-98/api/manage.php';
} else {
    $targetFile = __DIR__ . '/../sys-control-98/' . $subpath;
    if (file_exists($targetFile) && is_file($targetFile)) {
        require $targetFile;
    } else {
        require __DIR__ . '/../sys-control-98/login.php';
    }
}
