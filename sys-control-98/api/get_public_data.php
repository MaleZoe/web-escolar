<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
require_once __DIR__ . '/../config.php';

try {
    $pdo = getDB();

    // Contenido general
    $stmt = $pdo->query("SELECT clave, valor FROM contenido_institucional");
    $contenido = [];
    while ($row = $stmt->fetch()) {
        $contenido[$row['clave']] = $row['valor'];
    }

    // Especialidades
    $stmtEsp = $pdo->query("SELECT * FROM especialidades ORDER BY id ASC");
    $especialidades = $stmtEsp->fetchAll();

    // Hitos Históricos
    $stmtHitos = $pdo->query("SELECT * FROM hitos_historicos ORDER BY anio ASC, orden ASC");
    $hitos = $stmtHitos->fetchAll();

    // Proyectos
    $stmtProy = $pdo->query("SELECT p.*, e.nombre as especialidad_nombre FROM proyectos p LEFT JOIN especialidades e ON p.especialidad_id = e.id ORDER BY p.id DESC");
    $proyectos = $stmtProy->fetchAll();

    echo json_encode([
        'success' => true,
        'contenido' => $contenido,
        'especialidades' => $especialidades,
        'hitos' => $hitos,
        'proyectos' => $proyectos
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
