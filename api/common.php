<?php
// Utilidades compartidas entre la API publica (db.php) y el panel (config.php).

// Carga .env en entornos locales. getenv() no lo lee por si solo.
// Una variable ya definida en el entorno (Vercel, Apache) nunca se pisa.
if (!function_exists('appLoadEnv')) {
    function appLoadEnv(string $path): void {
        if (!is_readable($path)) {
            return;
        }
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            if ($key !== '' && getenv($key) === false) {
                putenv($key . '=' . trim($value, "\"'"));
            }
        }
    }

    function appIsServerless(): bool {
        return (bool)(getenv('VERCEL') || getenv('VERCEL_ENV') || getenv('AWS_LAMBDA_FUNCTION_NAME'));
    }

    // Tamano maximo de subida. Vercel limita el cuerpo de la peticion a unos 4,5 MB.
    function appMaxUploadBytes(): int {
        return (appIsServerless() ? 4 : 12) * 1024 * 1024;
    }

    // IP real del cliente. Las cabeceras de proxy las puede enviar cualquiera, asi que solo
    // se usan cuando la plataforma las controla. Sin esto se puede esquivar el bloqueo por IP.
    function appClientIP(): string {
        $candidates = [];
        if (appIsServerless()) {
            // Vercel sobrescribe estas cabeceras con la IP real del visitante.
            $candidates[] = $_SERVER['HTTP_X_VERCEL_FORWARDED_FOR'] ?? '';
            $candidates[] = $_SERVER['HTTP_X_REAL_IP'] ?? '';
            $candidates[] = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        } elseif (getenv('TRUST_CLOUDFLARE') === '1') {
            $candidates[] = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';
        }
        foreach ($candidates as $value) {
            $ip = trim(explode(',', $value)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    // Opciones TLS de PDO para bases remotas (TiDB Cloud, Aiven).
    // DB_SSL_VERIFY=1 exige verificar el certificado del servidor. Hay que activarlo
    // y probarlo en el entorno real: si el certificado no valida, la conexion falla.
    function appDbSslOptions(string $host): array {
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return [];
        }
        $options = [];
        $caPaths = [
            getenv('DB_SSL_CA') ?: '',
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/pki/tls/certs/ca-bundle.crt',
            '/etc/ssl/ca-bundle.pem',
            'C:\\xampp\\apache\\bin\\curl-ca-bundle.crt'
        ];
        foreach ($caPaths as $ca) {
            if ($ca !== '' && file_exists($ca)) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $ca;
                break;
            }
        }
        if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = getenv('DB_SSL_VERIFY') === '1';
        }
        return $options;
    }

    function appSecurityHeaders(): void {
        if (headers_sent()) {
            return;
        }
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header("Content-Security-Policy: frame-ancestors 'none'");
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }

    appLoadEnv(__DIR__ . '/../.env');
}
