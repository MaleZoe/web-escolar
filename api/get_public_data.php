<?php
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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
            'dise?o' => 'diseño',
            'dise?ad' => 'diseñad',
            'planificaci?n' => 'planificación',
            'representaci?n' => 'representación',
            'ejecuci?n' => 'ejecución',
            'construcci?n' => 'construcción',
            't?cnico' => 'técnico',
            'T?cnico' => 'Técnico',
            'c?mputo' => 'cómputo',
            'direcci?n' => 'dirección',
            'Direcci?n' => 'Dirección',
            'electr?nic' => 'electrónic',
            'Electr?nic' => 'Electrónic',
            'inform?tic' => 'informátic',
            'Inform?tic' => 'Informátic',
            'a?o' => 'año',
            'A?o' => 'Año',
            'secretar?a' => 'secretaría',
            'instituci?n' => 'institución',
            'Instituci?n' => 'Institución',
            'documentaci?n' => 'documentación',
            'gesti?n' => 'gestión',
            'Gesti?n' => 'Gestión',
            'H?ctor' => 'Héctor',
            'P?blica' => 'Pública',
            'p?blica' => 'pública',
            'ense?a' => 'enseña',
            'Pr?cticas' => 'Prácticas',
            'pr?cticas' => 'prácticas',
            '?ltimo' => 'último',
            'coraz?n' => 'corazón',
            'te?rico' => 'teórico',
            'Ac?' => 'Acá',
            'Scari?n' => 'Scarión',
            'Exposici?n' => 'Exposición',
            'exposici?n' => 'exposición',
            'd?nde' => 'dónde',
            'surgi?' => 'surgió',
            'qu?' => 'qué',
            'c?mo' => 'cómo',
            'calcul?' => 'calculó',
            'dibuj?' => 'dibujó',
            'program?' => 'programó',
            'mont?' => 'montó',
            'aprendi?' => 'aprendió',
            'formaci?n' => 'formación',
            'ven?' => 'vení',
            'm?vil' => 'móvil',
            'M?vil' => 'Móvil',
            'orientaci?n' => 'orientación',
            'prevenci?n' => 'prevención',
            'tem?ticas' => 'temáticas',
            'interact?' => 'interactú',
            'l?neas' => 'líneas',
            'm?scara' => 'máscara',
            'rob?tica' => 'robótica',
            'Rob?tica' => 'Robótica',
            'bi?nica' => 'biónica',
            'Bi?nica' => 'Biónica',
            'pr?tesis' => 'prótesis',
            'Pr?tesis' => 'Prótesis',
            'mioel?ctric' => 'mioeléctric',
            'precisi?n' => 'precisión',
            'sat?lite' => 'satélite',
            'Sat?lite' => 'Satélite',
            'telemetr?a' => 'telemetría',
            'atmosf?ric' => 'atmosféric',
            'transmisi?n' => 'transmisión',
            '?ntegramente' => 'íntegramente',
            'ergon?mic' => 'ergonómic',
            'expresi?n' => 'expresión',
            'art?stic' => 'artístic'
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $text);
    };

    foreach ($contenido as $k => $v) {
        $contenido[$k] = $cleanBadEnc($v);
    }

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
    foreach ($hitos as &$hito) {
        if (isset($hito['titulo'])) $hito['titulo'] = $cleanBadEnc($hito['titulo']);
        if (isset($hito['resumen'])) $hito['resumen'] = $cleanBadEnc($hito['resumen']);
        if (isset($hito['descripcion'])) $hito['descripcion'] = $cleanBadEnc($hito['descripcion']);
        if (isset($hito['especialidad'])) $hito['especialidad'] = $cleanBadEnc($hito['especialidad']);
        if (isset($hito['premio'])) $hito['premio'] = $cleanBadEnc($hito['premio']);
        if (isset($hito['destino'])) $hito['destino'] = $cleanBadEnc($hito['destino']);
    }
    unset($hito);

    // Proyectos
    // Solo columnas públicas: autor_id no se expone.
    $stmtProy = $pdo->query("SELECT p.id, p.titulo, p.subtitulo, p.descripcion, p.especialidad_id, p.anio, p.imagen, p.estado, p.destacado, e.nombre AS especialidad_nombre FROM proyectos p LEFT JOIN especialidades e ON p.especialidad_id = e.id ORDER BY p.destacado DESC, p.anio DESC, p.id DESC");
    $proyectos = $stmtProy->fetchAll();
    foreach ($proyectos as &$proy) {
        if (isset($proy['titulo'])) $proy['titulo'] = $cleanBadEnc($proy['titulo']);
        if (isset($proy['subtitulo'])) $proy['subtitulo'] = $cleanBadEnc($proy['subtitulo']);
        if (isset($proy['descripcion'])) $proy['descripcion'] = $cleanBadEnc($proy['descripcion']);
        if (isset($proy['especialidad_nombre'])) $proy['especialidad_nombre'] = $cleanBadEnc($proy['especialidad_nombre']);
    }
    unset($proy);

    echo json_encode([
        'success' => true,
        'contenido' => $contenido,
        'especialidades' => $especialidades,
        'hitos' => $hitos,
        'proyectos' => $proyectos
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('get_public_data: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al obtener datos públicos']);
}
