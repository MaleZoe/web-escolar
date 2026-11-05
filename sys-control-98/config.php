<?php
// Configuración y Seguridad Central de Acceso con soporte de variables de entorno (Vercel / Nube)
require_once __DIR__ . '/../api/common.php';
appSecurityHeaders();

if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'web_escolar_db');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ] + appDbSslOptions(DB_HOST);
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Error seguro de conexión a la base de datos']));
        }
    }
    return $pdo;
}

// Tiempo que una sesión se conserva en la base. Es mayor que los 30 minutos de inactividad
// de checkAdminAuth(), para que el recolector de PHP no la borre antes de ese vencimiento.
if (!defined('SESSION_STORE_TTL')) define('SESSION_STORE_TTL', 3600);

// Manejador de sesiones en base de datos para entornos serverless (Vercel / Lambda)
class PdoSessionHandler implements SessionHandlerInterface {
    private $pdo;
    private static $tableChecked = false;
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureTable();
    }
    private function ensureTable(): void {
        if (self::$tableChecked) return;
        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `sesiones_php` (
                `id` VARCHAR(128) NOT NULL,
                `datos` MEDIUMTEXT NOT NULL,
                `ultimo_acceso` INT(11) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_ultimo_acceso` (`ultimo_acceso`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
            self::$tableChecked = true;
        } catch (Exception $e) {
            // Continuar en caso de restricciones de privilegios DDL
        }
    }
    public function open($savePath, $sessionName): bool {
        $this->ensureTable();
        return true;
    }
    public function close(): bool {
        return true;
    }
    public function read($id): string|false {
        try {
            $stmt = $this->pdo->prepare("SELECT datos FROM sesiones_php WHERE id = :id AND ultimo_acceso > :exp");
            $stmt->execute(['id' => $id, 'exp' => time() - SESSION_STORE_TTL]);
            $row = $stmt->fetch();
            return $row ? (string)$row['datos'] : '';
        } catch (Exception $e) {
            return '';
        }
    }
    public function write($id, $data): bool {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO sesiones_php (id, datos, ultimo_acceso) VALUES (:id, :d, :t) ON DUPLICATE KEY UPDATE datos = :d2, ultimo_acceso = :t2");
            return $stmt->execute(['id' => $id, 'd' => $data, 't' => time(), 'd2' => $data, 't2' => time()]);
        } catch (Exception $e) {
            $this->ensureTable();
            try {
                $stmt = $this->pdo->prepare("INSERT INTO sesiones_php (id, datos, ultimo_acceso) VALUES (:id, :d, :t) ON DUPLICATE KEY UPDATE datos = :d2, ultimo_acceso = :t2");
                return $stmt->execute(['id' => $id, 'd' => $data, 't' => time(), 'd2' => $data, 't2' => time()]);
            } catch (Exception $e2) {
                return false;
            }
        }
    }
    public function destroy($id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM sesiones_php WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            return false;
        }
    }
    public function gc($maxlifetime): int|false {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM sesiones_php WHERE ultimo_acceso < :exp");
            $stmt->execute(['exp' => time() - $maxlifetime]);
            return $stmt->rowCount();
        } catch (Exception $e) {
            return false;
        }
    }
}

// Configuración reforzada de cookies de sesión
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', (string)SESSION_STORE_TTL);

    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
               (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    if ($isHttps) {
        ini_set('session.cookie_secure', 1);
    }

    try {
        $db = getDB();
        $handler = new PdoSessionHandler($db);
        session_set_save_handler($handler, true);
    } catch (Exception $e) {
        if (getenv('VERCEL') || (!empty(session_save_path()) && !is_writable(session_save_path()))) {
            @session_save_path('/tmp');
        }
    }
    session_start();
}

// Obtener IP del cliente de forma segura
function getClientIP() {
    return appClientIP();
}

// Generador de Token CSRF
function getCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validación de Token CSRF
function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Verificación de autenticación y timeout de sesión
function checkAdminAuth() {
    if (empty($_SESSION['admin_user'])) {
        header('Location: login.php');
        exit;
    }

    // Inactividad máxima: 30 minutos (1800 seg)
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
        header('Location: login.php?msg=timeout');
        exit;
    }
    $_SESSION['last_activity'] = time();
}
