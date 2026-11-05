<?php
// Enrutador serverless del panel sys-control-98 en Vercel.
// Solo se incluyen los archivos de esta lista: nunca una ruta armada con la URL.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

// Sin la barra final, los redireccionamientos relativos (login.php) saldrian del panel.
if ($path === '/sys-control-98') {
    header('Location: /sys-control-98/', true, 301);
    exit;
}

$subpath = trim((string)preg_replace('#^/sys-control-98/?#', '', (string)$path), '/');

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

if (!isset($routes[$subpath])) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Not Found';
    exit;
}

require __DIR__ . '/../sys-control-98/' . $routes[$subpath];
