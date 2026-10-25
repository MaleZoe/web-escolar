<?php
require_once __DIR__ . '/config.php';

$error = '';
$ip = getClientIP();
$pdo = getDB();

// Asegurar tabla de control de intentos
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `intentos_login` (
      `ip` VARCHAR(45) NOT NULL,
      `intentos` INT NOT NULL DEFAULT 1,
      `ultimo_intento` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `bloqueado_hasta` TIMESTAMP NULL DEFAULT NULL,
      PRIMARY KEY (`ip`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Exception $e) {}

// Comprobar si la IP está bloqueada por fuerza bruta
$isBlocked = false;
try {
    $stmtCheck = $pdo->prepare("SELECT * FROM intentos_login WHERE ip = :ip");
    $stmtCheck->execute(['ip' => $ip]);
    $intentoRow = $stmtCheck->fetch();

    if ($intentoRow && !empty($intentoRow['bloqueado_hasta'])) {
        $bloqueoTimestamp = strtotime($intentoRow['bloqueado_hasta']);
        if ($bloqueoTimestamp > time()) {
            $isBlocked = true;
            $minutosRestantes = ceil(($bloqueoTimestamp - time()) / 60);
            $error = "Acceso bloqueado por reiterados intentos fallidos. Espera {$minutosRestantes} minuto(s) antes de volver a intentar.";
        }
    }
} catch (Exception $e) {}

if (isset($_GET['msg']) && $_GET['msg'] === 'timeout') {
    $error = "Tu sesión ha expirado por inactividad. Por favor, ingresa tus credenciales nuevamente.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isBlocked) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($token)) {
        $error = 'Validación de seguridad expirada. Por favor, recarga la página.';
    } else {
        $usuario = trim($_POST['usuario'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!empty($usuario) && !empty($password)) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE (usuario = :u OR email = :e) AND rol_id = 1 LIMIT 1");
                $stmt->execute(['u' => $usuario, 'e' => $usuario]);
                $user = $stmt->fetch();

                // Mitigación de timing attack con hash de relleno si no existe el usuario
                $dummyHash = '$2y$12$Nz/d8vd4s0NnWKSFbW0uK.8R0d8vd4s0NnWKSFbW0uK.8R0d8vd4s';
                $hashToVerify = $user ? $user['password'] : $dummyHash;
                $passwordMatch = password_verify($password, $hashToVerify);

                if ($user && $passwordMatch) {
                    // Éxito: limpiar intentos fallidos de esta IP
                    $pdo->prepare("DELETE FROM intentos_login WHERE ip = :ip")->execute(['ip' => $ip]);
                    session_regenerate_id(true);

                    $_SESSION['admin_user'] = [
                        'id' => $user['id'],
                        'usuario' => $user['usuario'],
                        'nombre' => $user['nombre_completo'],
                        'email' => $user['email']
                    ];
                    $_SESSION['last_activity'] = time();
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    header('Location: index.php');
                    exit;
                } else {
                    // Fallo: registrar intento y aplicar bloqueo gradual
                    $intentos = ($intentoRow['intentos'] ?? 0) + 1;
                    $bloqueadoHasta = null;

                    if ($intentos >= 5) {
                        $bloqueadoHasta = date('Y-m-d H:i:s', time() + (15 * 60));
                        $error = "Has superado el límite de 5 intentos permitidos. El acceso ha sido bloqueado por 15 minutos.";
                    } else {
                        $restantes = 5 - $intentos;
                        $error = "Credenciales incorrectas o acceso no autorizado. Intentos restantes antes del bloqueo: {$restantes}.";
                    }

                    $stmtLog = $pdo->prepare("INSERT INTO intentos_login (ip, intentos, ultimo_intento, bloqueado_hasta) 
                        VALUES (:ip, :intentos, NOW(), :bloq) 
                        ON DUPLICATE KEY UPDATE intentos = :intentos2, bloqueado_hasta = :bloq2");
                    $stmtLog->execute([
                        'ip' => $ip,
                        'intentos' => $intentos,
                        'bloq' => $bloqueadoHasta,
                        'intentos2' => $intentos,
                        'bloq2' => $bloqueadoHasta
                    ]);
                }
            } catch (Exception $e) {
                $error = 'Error seguro en la autenticación. Intente más tarde.';
            }
        } else {
            $error = 'Por favor complete todos los campos de acceso.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Acceso al Sistema | Gestión Central</title>
  <link rel="icon" type="image/png" href="../favicon.png" />
  <link rel="stylesheet" href="../css/styles.css?v=3" />
  <style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--carbon);
      padding: 20px;
    }
    .login-card {
      width: 100%;
      max-width: 440px;
      background: var(--blanco);
      border-radius: 14px;
      padding: 40px 32px;
      box-shadow: 0 25px 50px rgba(0,0,0,0.5);
      border-top: 5px solid var(--verde);
    }
    @media (max-width: 480px) {
      body {
        padding: 14px;
      }
      .login-card {
        padding: 28px 20px;
        border-radius: 12px;
      }
      .login-header h1 {
        font-size: 19px;
      }
    }
    .login-header {
      text-align: center;
      margin-bottom: 28px;
    }
    .login-header img {
      width: auto;
      height: 52px;
      max-width: 120px;
      object-fit: contain;
      margin-bottom: 12px;
    }
    .login-header h1 {
      font-family: var(--display);
      font-size: 22px;
      color: var(--negro);
      margin: 0 0 4px 0;
    }
    .login-header p {
      color: var(--texto-suave);
      font-size: 13.5px;
      margin: 0;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      font-size: 12.5px;
      font-weight: 700;
      color: var(--negro);
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .form-group input {
      width: 100%;
      padding: 12px 14px;
      border: 1.5px solid var(--linea);
      border-radius: 6px;
      font-size: 15px;
      outline: none;
      transition: border-color 0.2s;
    }
    .form-group input:focus {
      border-color: var(--verde);
      box-shadow: 0 0 0 3px rgba(52, 100, 1, 0.15);
    }
    .btn-submit {
      width: 100%;
      background: var(--verde);
      color: var(--blanco);
      border: none;
      padding: 14px;
      font-size: 15px;
      font-weight: 700;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.2s, transform 0.1s;
    }
    .btn-submit:hover:not(:disabled) {
      background: var(--verde-claro);
    }
    .btn-submit:disabled {
      background: #888;
      cursor: not-allowed;
      opacity: 0.7;
    }
    .alert-error {
      background: #ffe5e5;
      color: var(--rojo);
      border: 1px solid rgba(253, 0, 2, 0.25);
      padding: 13px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 13.5px;
      line-height: 1.4;
    }
    .security-badge {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      margin-top: 24px;
      color: var(--texto-suave);
      font-size: 12px;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-header">
      <img src="../img/logo.png" alt="Escudo Institucional" />
      <h1>Gestión y Administración</h1>
      <p>Acceso restringido a personal autorizado</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert-error">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>" />

      <div class="form-group">
        <label for="usuario">Identificador de Usuario</label>
        <input type="text" id="usuario" name="usuario" required placeholder="Usuario autorizado" autofocus <?= $isBlocked ? 'disabled' : '' ?> />
      </div>

      <div class="form-group">
        <label for="password">Clave de Seguridad</label>
        <input type="password" id="password" name="password" required placeholder="••••••••••••" <?= $isBlocked ? 'disabled' : '' ?> />
      </div>

      <button type="submit" class="btn-submit" <?= $isBlocked ? 'disabled' : '' ?>>Ingresar al Sistema</button>
    </form>

    <div class="security-badge">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
      <span>Conexión protegida con cifrado y control de intentos</span>
    </div>
  </div>
</body>
</html>
