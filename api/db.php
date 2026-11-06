<?php
// ARCHIVO: api/db.php
// QUÉ HACE: abre la conexión a la base de datos para los archivos públicos de la API.
// USAN ESTE ARCHIVO: get_public_data.php, contact.php e image.php.
// Conexion PDO de los endpoints publicos, con soporte de variables de entorno para Vercel / Nube
require_once __DIR__ . '/common.php';

// Los datos de conexión vienen de variables de entorno, nunca escritos en el código.
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'web_escolar_db');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');

// MOSTRAR-BD: conexión con PDO. static hace que se reutilice durante el mismo pedido.
function getPublicDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Las consultas preparadas las hace el motor de la base, no PHP.
            PDO::ATTR_EMULATE_PREPARES => false,
        ] + appDbSslOptions(DB_HOST);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}
