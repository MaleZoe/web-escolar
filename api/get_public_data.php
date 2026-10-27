<?php
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
require_once __DIR__ . '/db.php';

try {
    $pdo = getPublicDB();

    // Contenido general
    $stmt = $pdo->query("SELECT clave, valor FROM contenido_institucional");
    $contenido = [];
    while ($row = $stmt->fetch()) {
        $contenido[$row['clave']] = $row['valor'];
    }
    unset($contenido['contacto_telefono']);

    $cleanBadEnc = function($text) {
        if (!is_string($text)) return $text;
        $replacements = [
            'Dise?o' => 'Diseño',
            'planificaci?n' => 'planificación',
            'representaci?n' => 'representación',
            'ejecuci?n' => 'ejecución',
            'construcci?n' => 'construcción',
            't?cnico' => 'técnico',
            'T?cnico' => 'Técnico',
            'c?mputo' => 'cómputo',
            'direcci?n' => 'dirección',
            'electr?nic' => 'electrónic',
            'Electr?nic' => 'Electrónic',
            'inform?tic' => 'informátic',
            'Inform?tic' => 'Informátic',
            'a?o' => 'año',
            'A?o' => 'Año'
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $text);
    };

    // Especialidades
    $stmtEsp = $pdo->query("SELECT * FROM especialidades ORDER BY id ASC");
    $especialidades = $stmtEsp->fetchAll();
    foreach ($especialidades as &$esp) {
        if (isset($esp['descripcion'])) $esp['descripcion'] = $cleanBadEnc($esp['descripcion']);
        if (isset($esp['nombre'])) $esp['nombre'] = $cleanBadEnc($esp['nombre']);
        if (isset($esp['titulo'])) $esp['titulo'] = $cleanBadEnc($esp['titulo']);
    }
    unset($esp);

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
    echo json_encode(['success' => false, 'error' => 'Error al obtener datos públicos']);
}
