<?php
// QUÉ HACE: cierra la sesión del panel. La borra de la base y elimina la cookie.
require_once __DIR__ . '/config.php';

// Cerrar sesion cambia estado: solo por POST con token, para que otro sitio no pueda forzarlo.
// MOSTRAR-CSRF: también exige el token, para que otro sitio no pueda cerrar la sesión.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: index.php');
    exit;
}

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header('Location: login.php?msg=logout');
exit;
