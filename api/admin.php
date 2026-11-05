<?php
// Enrutador serverless del panel en Vercel: /sys-control-<ruta>/...
// Solo se incluyen los archivos de esta lista: nunca una ruta armada con la URL.
$path = (string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

function adminNotFound(): void {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Not Found';
    exit;
}

if (!preg_match('#^/sys-control-([a-z0-9]+)(/.*)?$#', $path, $m)) {
    adminNotFound();
}
$slug = $m[1];
$rest = $m[2] ?? '';

$routes = [
    ''                        => 'index.php',
    'index.php'               => 'index.php',
    'login'                   => 'login.php',
    'login.php'               => 'login.php',
    'logout'                  => 'logout.php',
    'logout.php'              => 'logout.php',
    'manage.php'              => 'api/manage.php',
    'api/manage.php'          => 'api/manage.php',
    'api/get_public_data.php' => 'api/get_public_data.php',
];

require_once __DIR__ . '/../sys-control-98/config.php';

// Una ruta vencida responde igual que una que nunca existió.
if (!panelSlugIsValid(getDB(), $slug)) {
    adminNotFound();
}

// Sin la barra final, los redireccionamientos relativos (login.php) saldrian del panel.
if ($rest === '') {
    header('Location: /sys-control-' . $slug . '/', true, 301);
    exit;
}

$subpath = trim($rest, '/');
if (!isset($routes[$subpath])) {
    adminNotFound();
}

require __DIR__ . '/../sys-control-98/' . $routes[$subpath];
