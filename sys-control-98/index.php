<?php
require_once __DIR__ . '/config.php';
checkAdminAuth();

$user = $_SESSION['admin_user'];
$pdo = getDB();

// Cargar datos actuales de contenido_institucional
$stmt = $pdo->query("SELECT clave, valor FROM contenido_institucional");
$contenido = [];
while ($row = $stmt->fetch()) {
    $contenido[$row['clave']] = $row['valor'];
}

// Especialidades
$stmtEsp = $pdo->query("SELECT * FROM especialidades ORDER BY id ASC");
$especialidades = $stmtEsp->fetchAll();

// Hitos
$stmtHitos = $pdo->query("SELECT * FROM hitos_historicos ORDER BY anio ASC, orden ASC");
$hitos = $stmtHitos->fetchAll();

// Proyectos (ExpoTec)
$stmtProy = $pdo->query("SELECT p.*, e.nombre as especialidad_nombre FROM proyectos p LEFT JOIN especialidades e ON p.especialidad_id = e.id ORDER BY p.id DESC");
$proyectos = $stmtProy->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Panel de Administración | E.E.S.T. N.º 5</title>
  <link rel="icon" type="image/png" href="../favicon.png" />
  <link rel="stylesheet" href="../css/styles.css?v=3" />
  <link rel="stylesheet" href="css/admin.css?v=2" />
</head>
<body>
  <div class="admin-layout">
    <!-- BARRA LATERAL -->
    <aside class="admin-sidebar" id="adminSidebar">
      <div class="sidebar-brand">
        <img src="../img/logo.png" alt="EEST5" />
        <div class="brand-text-wrap">
          <h3>EEST N.º 5</h3>
          <span>Panel Administrador</span>
        </div>
        <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Cerrar menú">&times;</button>
      </div>

      <nav class="sidebar-nav">
        <!-- 1. Landing -->
        <a href="#tab-landing" class="nav-item active" data-tab="tab-landing">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
          Landing (Inicio)
        </a>
        <!-- 2. La Escuela -->
        <a href="#tab-escuela" class="nav-item" data-tab="tab-escuela">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
          La Escuela
        </a>
        <!-- 3. Anuncios -->
        <a href="#tab-anuncios" class="nav-item" data-tab="tab-anuncios">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
          Anuncios (Instagram)
        </a>
        <!-- 4. Especialidades -->
        <a href="#tab-especialidades" class="nav-item" data-tab="tab-especialidades">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
          Especialidades
        </a>
        <!-- 5. Hitos Históricos -->
        <a href="#tab-hitos" class="nav-item" data-tab="tab-hitos">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
          Hitos Históricos
        </a>
        <!-- 6. ExpoTec -->
        <a href="#tab-expotec" class="nav-item" data-tab="tab-expotec">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
          ExpoTec
        </a>
        <!-- 7. Institucionales -->
        <a href="#tab-institucionales" class="nav-item" data-tab="tab-institucionales">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
          Institucionales
        </a>
        <!-- 8. Seguridad y Acceso -->
        <a href="#tab-seguridad" class="nav-item" data-tab="tab-seguridad">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
          Seguridad & Acceso
        </a>
      </nav>

      <div class="sidebar-footer">
        <a href="../" target="_blank" class="footer-link">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
          Ver Sitio Web
        </a>
        <a href="logout.php" class="footer-link text-danger">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
          Cerrar Sesión
        </a>
      </div>
    </aside>

    <!-- BACKDROP PARA MÓVILES -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="admin-main">
      <!-- HEADER SUPERIOR -->
      <header class="admin-topbar">
        <div class="topbar-left">
          <button type="button" class="admin-menu-toggle" id="adminMenuToggle" aria-label="Abrir menú de navegación" title="Menú">
            <span></span><span></span><span></span>
          </button>
          <div class="topbar-titles">
            <h2 id="section-title">Landing (Página de Inicio)</h2>
            <p class="text-muted topbar-subtitle">Modifica los contenidos en vivo guardados en phpMyAdmin (MySQL).</p>
          </div>
        </div>
        <div class="user-pill">
          <span class="user-badge">Admin</span>
          <span class="user-name"><?= htmlspecialchars($user['nombre']) ?></span>
        </div>
      </header>

      <!-- SELECTOR DE SECCIÓN RESPONSIVE PARA EL PANEL ADMIN -->
      <div class="admin-mobile-nav-bar">
        <label for="adminSectionSelect">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
          <span>Elegir sección:</span>
        </label>
        <div class="admin-select-wrapper">
          <select id="adminSectionSelect" class="admin-mobile-select" onchange="switchTab(this.value)">
            <option value="tab-landing">1. Landing (Inicio)</option>
            <option value="tab-escuela">2. La Escuela</option>
            <option value="tab-anuncios">3. Anuncios (Instagram)</option>
            <option value="tab-especialidades">4. Especialidades</option>
            <option value="tab-hitos">5. Hitos Históricos</option>
            <option value="tab-expotec">6. ExpoTec</option>
            <option value="tab-institucionales">7. Institucionales</option>
            <option value="tab-seguridad">8. Seguridad & Acceso</option>
          </select>
        </div>
      </div>

      <div class="admin-content-area">
        <!-- NOTIFICACIÓN TOAST -->
        <div id="toast" class="toast">Guardado exitosamente</div>

        <!-- ================= 1. PESTAÑA: LANDING (PORTADA & HERO) ================= -->
        <section id="tab-landing" class="admin-tab active">
          <form class="content-form" onsubmit="saveGeneralContent(event)">
            <div class="card">
              <h3 class="card-title">Sección Hero (Portada de Inicio)</h3>
              <p class="text-muted">Textos principales e imagen que dan la bienvenida al visitante en la cabecera principal.</p>
              
              <div class="grid-2 mt-3">
                <div class="field">
                  <label>Título Principal de la Escuela</label>
                  <input type="text" name="hero_titulo" value="<?= htmlspecialchars($contenido['hero_titulo'] ?? 'E.E.S.T. N.º 5 "Amancio Williams"') ?>" required />
                </div>
                <div class="field">
                  <label>Slogan de Portada</label>
                  <input type="text" name="hero_slogan" value="<?= htmlspecialchars($contenido['hero_slogan'] ?? 'Aprender haciendo. Construir el futuro.') ?>" />
                </div>
              </div>

              <div class="field mt-3">
                <label>Subtítulo / Bajada Institucional</label>
                <textarea name="hero_subtitulo" rows="2" required><?= htmlspecialchars($contenido['hero_subtitulo'] ?? 'Siete años de formación técnica en Construcciones, Informática y Electrónica, con taller desde el primer día.') ?></textarea>
              </div>

              <div class="grid-2 mt-3">
                <div class="field">
                  <label>Foto de Portada / Hero Principal</label>
                  <?php $heroFoto = $contenido['hero_foto'] ?? 'hero.jpg'; ?>
                  <div class="img-upload-box">
                    <img src="../img/<?= htmlspecialchars($heroFoto) ?>" class="img-upload-preview" alt="Hero actual" onerror="this.src='../img/hero.jpg'" />
                    <div class="img-upload-info">
                      <span class="upload-label">Cambiar foto de cabecera</span>
                      <span class="upload-filename">Actual: <?= htmlspecialchars($heroFoto) ?></span>
                      <input type="file" name="hero_foto" accept="image/*" />
                    </div>
                  </div>
                </div>

                <div class="field">
                  <label>Escudo / Logo Institucional</label>
                  <?php $siteLogo = $contenido['site_logo'] ?? 'logo.png'; ?>
                  <div class="img-upload-box">
                    <img src="../img/<?= htmlspecialchars($siteLogo) ?>" class="img-upload-preview" alt="Logo actual" onerror="this.src='../img/logo.png'" style="object-fit: contain; background: #fff; padding: 4px;" />
                    <div class="img-upload-info">
                      <span class="upload-label">Subir nuevo escudo/logo</span>
                      <span class="upload-filename">Actual: <?= htmlspecialchars($siteLogo) ?></span>
                      <input type="file" name="site_logo" accept="image/*" />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card mt-4">
              <h3 class="card-title">Datos Institucionales & Footer de la Web</h3>
              <p class="text-muted">Información visible en la parte inferior y páginas de contacto.</p>
              <div class="grid-2 mt-3">
                <div class="field">
                  <label>Dirección</label>
                  <input type="text" name="direccion" value="<?= htmlspecialchars($contenido['direccion'] ?? 'Av. Juan B. Justo 4287, Mar del Plata') ?>" />
                </div>
                <div class="field">
                  <label>Teléfono Institucional</label>
                  <input type="text" name="contacto_telefono" value="<?= htmlspecialchars($contenido['contacto_telefono'] ?? '(0223) 472-2408') ?>" />
                </div>
                <div class="field mt-3">
                  <label>Correo Electrónico Oficial</label>
                  <input type="email" name="contacto_email" value="<?= htmlspecialchars($contenido['contacto_email'] ?? 'infoT5@eest5mdp.edu.ar') ?>" />
                </div>
                <div class="field mt-3">
                  <label>WhatsApp de Contacto (ej: 5492235928268)</label>
                  <input type="text" name="contacto_whatsapp" value="<?= htmlspecialchars($contenido['contacto_whatsapp'] ?? '5492235928268') ?>" />
                </div>
              </div>
            </div>

            <div class="form-actions mt-4">
              <button type="submit" class="btn btn-primary">Guardar Cambios de Landing</button>
            </div>
          </form>
        </section>

        <!-- ================= 2. PESTAÑA: LA ESCUELA ================= -->
        <section id="tab-escuela" class="admin-tab">
          <form class="content-form" onsubmit="saveGeneralContent(event)">
            <!-- 1. Encabezado de Página (Hero) -->
            <div class="card">
              <h3 class="card-title">Encabezado de la Página ("La Escuela")</h3>
              <p class="text-muted">Título y etiqueta visible al inicio de la página escuela.html.</p>
              <div class="grid-2 mt-3">
                <div class="field">
                  <label>Etiqueta / Tag Superior</label>
                  <input type="text" name="escuela_hero_tag" value="<?= htmlspecialchars($contenido['escuela_hero_tag'] ?? 'La Escuela') ?>" />
                </div>
                <div class="field">
                  <label>Título Principal de la Sección</label>
                  <input type="text" name="escuela_hero_titulo" value="<?= htmlspecialchars($contenido['escuela_hero_titulo'] ?? 'Una institución que enseña con las manos y con la cabeza.') ?>" />
                </div>
              </div>
            </div>

            <!-- 2. Presentación e Identidad Pedagógica -->
            <div class="card mt-4">
              <h3 class="card-title">Presentación e Identidad Institucional</h3>
              <p class="text-muted">Misión, perfil del egresado, foto de la fachada y ficha técnica.</p>
              
              <div class="field mt-3">
                <label>Descripción Institucional / Párrafo 1 (Misión)</label>
                <textarea name="sobre_nosotros" rows="3"><?= htmlspecialchars($contenido['sobre_nosotros'] ?? 'Somos una escuela técnica pública de Mar del Plata. La formación combina la educación secundaria común con talleres, laboratorios y prácticas profesionalizantes: los estudiantes miden, dibujan, programan, sueldan, calculan y construyen.') ?></textarea>
              </div>

              <div class="field mt-3">
                <label>Párrafo 2 (Perfil del Egresado Técnico)</label>
                <textarea name="escuela_egresado_desc" rows="3"><?= htmlspecialchars($contenido['escuela_egresado_desc'] ?? 'El resultado es un egresado técnico con criterio propio, hábitos de trabajo y un oficio real, preparado tanto para continuar estudios superiores como para insertarse en el mundo del trabajo.') ?></textarea>
              </div>

              <div class="grid-2 mt-4">
                <div class="field">
                  <label>Foto de la Fachada Institucional</label>
                  <?php $escuelaFoto = $contenido['escuela_foto_fachada'] ?? 'escuela.jpg'; ?>
                  <div class="img-upload-box">
                    <img src="../img/<?= htmlspecialchars($escuelaFoto) ?>" class="img-upload-preview" alt="Fachada actual" onerror="this.src='../img/escuela.jpg'" />
                    <div class="img-upload-info">
                      <span class="upload-label">Cambiar foto de fachada</span>
                      <span class="upload-filename">Actual: <?= htmlspecialchars($escuelaFoto) ?></span>
                      <input type="file" name="escuela_foto_fachada" accept="image/*" />
                    </div>
                  </div>
                </div>

                <div class="field">
                  <label>Epígrafe / Texto al pie de la foto</label>
                  <input type="text" name="escuela_foto_caption" value="<?= htmlspecialchars($contenido['escuela_foto_caption'] ?? 'Fachada · Juan B. Justo 4287, Mar del Plata') ?>" />
                </div>
              </div>

              <h4 class="card-subtitle mt-4">Ficha Técnica de Datos Institucionales</h4>
              <div class="grid-2 mt-3">
                <div class="field">
                  <label>Nivel Educativo</label>
                  <input type="text" name="escuela_nivel" value="<?= htmlspecialchars($contenido['escuela_nivel'] ?? 'Secundaria técnica (7 años)') ?>" />
                </div>
                <div class="field">
                  <label>Tipo de Gestión</label>
                  <input type="text" name="escuela_gestion" value="<?= htmlspecialchars($contenido['escuela_gestion'] ?? 'Pública · estatal') ?>" />
                </div>
                <div class="field mt-3">
                  <label>Título Otorgado</label>
                  <input type="text" name="escuela_titulo" value="<?= htmlspecialchars($contenido['escuela_titulo'] ?? 'Técnico / Maestro Mayor de Obras') ?>" />
                </div>
                <div class="field mt-3">
                  <label>Localidad y Provincia</label>
                  <input type="text" name="escuela_ciudad" value="<?= htmlspecialchars($contenido['escuela_ciudad'] ?? 'Mar del Plata, Buenos Aires') ?>" />
                </div>
              </div>
            </div>

            <!-- 3. Sección El Taller ("Aprender haciendo") -->
            <div class="card mt-4">
              <h3 class="card-title">Sección El Taller ("Aprender haciendo")</h3>
              <p class="text-muted">Configuración integral de la metodología de talleres, foto y los 4 pilares formativos.</p>

              <div class="grid-2 mt-3">
                <div class="field">
                  <label>Etiqueta / Tag</label>
                  <input type="text" name="escuela_taller_tag" value="<?= htmlspecialchars($contenido['escuela_taller_tag'] ?? 'Aprender haciendo') ?>" />
                </div>
                <div class="field">
                  <label>Título del Taller</label>
                  <input type="text" name="escuela_taller_titulo" value="<?= htmlspecialchars($contenido['escuela_taller_titulo'] ?? 'Acá el conocimiento se prueba, se mide y se arma.') ?>" />
                </div>
              </div>

              <div class="field mt-3">
                <label>Descripción / Metodología de Trabajo en Taller</label>
                <textarea name="escuela_taller_desc" rows="3"><?= htmlspecialchars($contenido['escuela_taller_desc'] ?? 'El taller es el corazón de la escuela. Cada contenido teórico termina en una pieza, un plano, un programa o un circuito que funciona —o que hay que volver a corregir hasta que funcione.') ?></textarea>
              </div>

              <div class="field mt-3">
                <label>Foto de Fondo / Actividad del Taller</label>
                <?php $tallerFoto = $contenido['escuela_foto_taller'] ?? 'taller.jpg'; ?>
                <div class="img-upload-box">
                  <img src="../img/<?= htmlspecialchars($tallerFoto) ?>" class="img-upload-preview" alt="Taller actual" onerror="this.src='../img/taller.jpg'" />
                  <div class="img-upload-info">
                    <span class="upload-label">Cambiar foto del taller</span>
                    <span class="upload-filename">Actual: <?= htmlspecialchars($tallerFoto) ?></span>
                    <input type="file" name="escuela_foto_taller" accept="image/*" />
                  </div>
                </div>
              </div>

              <h4 class="card-subtitle mt-4">Los 4 Pilares del Taller (Pasos 01 al 04)</h4>
              <div class="grid-2 mt-3">
                <div class="field">
                  <label>Paso 01</label>
                  <input type="text" name="escuela_paso_1" value="<?= htmlspecialchars($contenido['escuela_paso_1'] ?? 'Taller y laboratorio desde los primeros años') ?>" />
                </div>
                <div class="field">
                  <label>Paso 02</label>
                  <input type="text" name="escuela_paso_2" value="<?= htmlspecialchars($contenido['escuela_paso_2'] ?? 'Proyectos integradores por especialidad') ?>" />
                </div>
                <div class="field mt-3">
                  <label>Paso 03</label>
                  <input type="text" name="escuela_paso_3" value="<?= htmlspecialchars($contenido['escuela_paso_3'] ?? 'Trabajo en equipo, normas y seguridad') ?>" />
                </div>
                <div class="field mt-3">
                  <label>Paso 04</label>
                  <input type="text" name="escuela_paso_4" value="<?= htmlspecialchars($contenido['escuela_paso_4'] ?? 'Prácticas profesionalizantes en el último año') ?>" />
                </div>
              </div>
            </div>

            <!-- 4. Sección Autoridades / Equipo de Gestión -->
            <div class="card mt-4">
              <h3 class="card-title">Autoridades & Equipo de Gestión Institucional</h3>
              <p class="text-muted">Nombres y cargos del equipo directivo, vicedirecciones, jefaturas y secretaría.</p>

              <div class="grid-2 mt-3">
                <div class="field">
                  <label>Etiqueta de Sección</label>
                  <input type="text" name="escuela_auth_tag" value="<?= htmlspecialchars($contenido['escuela_auth_tag'] ?? 'Autoridades') ?>" />
                </div>
                <div class="field">
                  <label>Título de la Sección</label>
                  <input type="text" name="escuela_auth_titulo" value="<?= htmlspecialchars($contenido['escuela_auth_titulo'] ?? 'Equipo de gestión institucional') ?>" />
                </div>
              </div>

              <!-- Dirección General -->
              <div class="auth-subgroup-title">1. Dirección General</div>
              <div class="grid-2">
                <div class="field">
                  <label>Nombre del Director</label>
                  <input type="text" name="escuela_dir_nombre" value="<?= htmlspecialchars($contenido['escuela_dir_nombre'] ?? 'Héctor Scaglione') ?>" />
                </div>
                <div class="field">
                  <label>Cargo / Rol</label>
                  <input type="text" name="escuela_dir_cargo" value="<?= htmlspecialchars($contenido['escuela_dir_cargo'] ?? 'Director Institucional') ?>" />
                </div>
              </div>

              <!-- Vicedirección (3 Turnos) -->
              <div class="auth-subgroup-title">2. Vicedirección (3 Turnos)</div>
              <div class="grid-3">
                <div class="field">
                  <label>Turno Mañana</label>
                  <input type="text" name="escuela_vice_tm" value="<?= htmlspecialchars($contenido['escuela_vice_tm'] ?? 'Paula Iuzzolino') ?>" />
                </div>
                <div class="field">
                  <label>Turno Tarde</label>
                  <input type="text" name="escuela_vice_tt" value="<?= htmlspecialchars($contenido['escuela_vice_tt'] ?? 'Maximiliano Scarión') ?>" />
                </div>
                <div class="field">
                  <label>Turno Noche</label>
                  <input type="text" name="escuela_vice_tn" value="<?= htmlspecialchars($contenido['escuela_vice_tn'] ?? 'Lorena Cometti') ?>" />
                </div>
              </div>

              <!-- Jefatura de Área (3 Turnos) -->
              <div class="auth-subgroup-title">3. Jefatura de Área (3 Turnos)</div>
              <div class="grid-3">
                <div class="field">
                  <label>Turno Mañana</label>
                  <input type="text" name="escuela_jefe_tm" value="<?= htmlspecialchars($contenido['escuela_jefe_tm'] ?? 'Gustavo Girardi') ?>" />
                </div>
                <div class="field">
                  <label>Turno Tarde</label>
                  <input type="text" name="escuela_jefe_tt" value="<?= htmlspecialchars($contenido['escuela_jefe_tt'] ?? 'Diego Álvarez') ?>" />
                </div>
                <div class="field">
                  <label>Turno Noche</label>
                  <input type="text" name="escuela_jefe_tn" value="<?= htmlspecialchars($contenido['escuela_jefe_tn'] ?? 'Esteban Marinone') ?>" />
                </div>
              </div>

              <!-- Secretaría (2 Turnos) -->
              <div class="auth-subgroup-title">4. Secretaría (2 Turnos)</div>
              <div class="grid-2">
                <div class="field">
                  <label>Turno Mañana</label>
                  <input type="text" name="escuela_sec_tm" value="<?= htmlspecialchars($contenido['escuela_sec_tm'] ?? 'Julia Domecq') ?>" />
                </div>
                <div class="field">
                  <label>Turno Tarde</label>
                  <input type="text" name="escuela_sec_tt" value="<?= htmlspecialchars($contenido['escuela_sec_tt'] ?? 'Mac Flores') ?>" />
                </div>
              </div>
            </div>

            <!-- 5. Marco Normativo Institucional -->
            <div class="card mt-4">
              <h3 class="card-title">Marco Normativo Institucional (Tarjetas de Enlace)</h3>
              <p class="text-muted">Enlaces de acceso a las resoluciones del Régimen Académico y Acuerdo Institucional de Convivencia (AIC).</p>

              <div class="grid-2 mt-3">
                <!-- Enlace 1: Régimen Académico -->
                <div class="field" style="background: var(--crema-suave); padding: 16px; border-radius: 10px; border: 1px solid var(--linea);">
                  <div style="display: flex; gap: 14px; align-items: center; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; background: var(--verde); color: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem;">
                      RA
                    </div>
                    <div>
                      <strong style="color: var(--verde);">01. Régimen Académico (RA)</strong><br />
                      <small class="text-muted">Res. Provincial Nº 1650/24</small>
                    </div>
                  </div>
                  <label>Enlace / URL de Destino</label>
                  <input type="text" name="escuela_qr_ra_url" value="<?= htmlspecialchars($contenido['escuela_qr_ra_url'] ?? 'institucionales.html?seccion=regimen-academico') ?>" />
                </div>

                <!-- Enlace 2: Acuerdo Institucional de Convivencia -->
                <div class="field" style="background: var(--crema-suave); padding: 16px; border-radius: 10px; border: 1px solid var(--linea);">
                  <div style="display: flex; gap: 14px; align-items: center; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; background: var(--tinta); color: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem;">
                      AIC
                    </div>
                    <div>
                      <strong style="color: var(--verde);">02. Acuerdo de Convivencia (AIC)</strong><br />
                      <small class="text-muted">Pautas de Convivencia Escolar</small>
                    </div>
                  </div>
                  <label>Enlace / URL de Destino</label>
                  <input type="text" name="escuela_qr_aic_url" value="<?= htmlspecialchars($contenido['escuela_qr_aic_url'] ?? 'institucionales.html?seccion=acuerdo-institucional') ?>" />
                </div>
              </div>
            </div>

            <div class="form-actions mt-4">
              <button type="submit" class="btn btn-primary">Guardar Todos los Cambios de La Escuela</button>
            </div>
          </form>
        </section>

        <!-- ================= 3. PESTAÑA: ANUNCIOS (BEHOLD / INSTAGRAM) ================= -->
        <section id="tab-anuncios" class="admin-tab">
          <div class="card">
            <h3 class="card-title">Feed de Noticias y Comunicados (Instagram / Behold)</h3>
            <p class="text-muted">
              El carrusel de novedades en la página principal se sincroniza automáticamente con Instagram a través del gestor <strong>Behold</strong>.
            </p>

            <div class="behold-banner mt-4">
              <div class="behold-banner-content">
                <div class="behold-badge">Sincronización Automática</div>
                <h4>¿Querés modificar, añadir o gestionar qué publicaciones se muestran?</h4>
                <p>
                  Podés acceder al panel de control oficial de Behold para conectar nuevas publicaciones, filtrar hashtags o actualizar el token de Instagram.
                </p>
                <div class="behold-actions mt-3">
                  <a href="<?= htmlspecialchars($contenido['behold_admin_link'] ?? 'https://behold.so/dashboard') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-behold">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    Abrir Dashboard de Behold (behold.so) ↗
                  </a>
                  <a href="<?= htmlspecialchars($contenido['instagram_url'] ?? 'https://www.instagram.com/tecnicacinco_mdq/') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-secondary">
                    Ver Instagram de la Escuela ↗
                  </a>
                </div>
              </div>
            </div>

            <form class="content-form mt-4" onsubmit="saveGeneralContent(event)">
              <h4 class="card-subtitle">Configuración de la Sección en la Web</h4>
              <div class="grid-2 mt-3">
                <div class="field">
                  <label>Título del Encabezado de Anuncios</label>
                  <input type="text" name="anuncios_titulo" value="<?= htmlspecialchars($contenido['anuncios_titulo'] ?? 'Novedades e Instagram Oficial') ?>" />
                </div>
                <div class="field">
                  <label>Subtítulo / Bajada</label>
                  <input type="text" name="anuncios_subtitulo" value="<?= htmlspecialchars($contenido['anuncios_subtitulo'] ?? 'Seguí el día a día de nuestros talleres, proyectos y actividades en las redes de la escuela.') ?>" />
                </div>
                <div class="field mt-3">
                  <label>Usuario Oficial de Instagram</label>
                  <input type="text" name="instagram_handle" value="<?= htmlspecialchars($contenido['instagram_handle'] ?? '@tecnicacinco_mdq') ?>" />
                </div>
                <div class="field mt-3">
                  <label>Enlace Perfil de Instagram</label>
                  <input type="url" name="instagram_url" value="<?= htmlspecialchars($contenido['instagram_url'] ?? 'https://www.instagram.com/tecnicacinco_mdq/') ?>" />
                </div>
                <div class="field mt-3">
                  <label>URL de Feed API Behold</label>
                  <input type="text" name="behold_feed_url" value="<?= htmlspecialchars($contenido['behold_feed_url'] ?? 'https://feeds.behold.so/kYahsnwH7WymWcYH98BW') ?>" />
                </div>
                <div class="field mt-3">
                  <label>Enlace Directo al Panel de Behold</label>
                  <input type="url" name="behold_admin_link" value="<?= htmlspecialchars($contenido['behold_admin_link'] ?? 'https://behold.so/dashboard') ?>" />
                </div>
              </div>

              <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">Guardar Configuración de Anuncios</button>
              </div>
            </form>
          </div>
        </section>

        <!-- ================= 4. PESTAÑA: ESPECIALIDADES ================= -->
        <section id="tab-especialidades" class="admin-tab">
          <!-- Configuración de Encabezado de la Sección -->
          <div class="card" style="margin-bottom: 24px;">
            <h3 class="card-title">Encabezado de la Sección</h3>
            <p class="text-muted">Lema introductorio visible en la sección de Especialidades de la página principal.</p>
            <form onsubmit="saveGeneralContent(event)" class="mt-3">
              <div class="field">
                <label>Subtítulo / Lema de la Sección</label>
                <input type="text" name="especialidades_tagline" value="<?= htmlspecialchars($contenido['especialidades_tagline'] ?? 'Tres caminos técnicos. Un mismo modo de trabajar.') ?>" />
              </div>
              <div class="form-actions mt-3">
                <button type="submit" class="btn btn-primary btn-sm">Guardar Encabezado</button>
              </div>
            </form>
          </div>

          <div class="card">
            <div class="card-header-flex">
              <div>
                <h3 class="card-title">Especialidades Técnicas</h3>
                <p class="text-muted">Gestioná las carreras técnicas, fotos, títulos profesionales y descripciones de la escuela.</p>
              </div>
              <button class="btn btn-primary" onclick="openEspecialidadModal()">+ Nueva Especialidad</button>
            </div>

            <div class="table-responsive">
            <table class="admin-table mt-4">
              <thead>
                <tr>
                  <th style="width: 80px;">Foto</th>
                  <th>Nombre</th>
                  <th>Título Profesional</th>
                  <th>Duración</th>
                  <th>Descripción</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($especialidades as $esp): ?>
                  <tr>
                    <td>
                      <?php if (!empty($esp['imagen'])): ?>
                        <img src="../img/<?= htmlspecialchars($esp['imagen']) ?>" class="table-thumb" alt="Miniatura" onerror="this.src='../img/hero.jpg'" style="width: 64px; height: 64px; object-fit: cover; border-radius: 8px; border: 1px solid var(--linea);" />
                      <?php else: ?>
                        <span class="no-img" style="width: 64px; height: 64px; display: inline-flex; align-items: center; justify-content: center; background: #eee; border-radius: 8px;">—</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <strong><?= htmlspecialchars($esp['nombre']) ?></strong>
                      <div class="text-muted" style="font-size: 0.8rem; margin-top: 2px;">
                        Icono: <code><?= htmlspecialchars($esp['icono']) ?></code>
                      </div>
                    </td>
                    <td>
                      <span class="badge-status status-en-expotec" style="font-size: 0.85rem;"><?= htmlspecialchars($esp['titulo'] ?? '—') ?></span>
                    </td>
                    <td><?= htmlspecialchars($esp['duracion_anios']) ?> años</td>
                    <td>
                      <div style="max-width: 320px; font-size: 0.85rem; line-height: 1.4; color: var(--texto-suave);">
                        <?= htmlspecialchars($esp['descripcion']) ?>
                      </div>
                    </td>
                    <td style="white-space: nowrap;">
                      <button class="btn-action edit" onclick='editEspecialidad(<?= json_encode($esp, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'>Editar</button>
                      <button class="btn-action delete" onclick="deleteEspecialidad(<?= $esp['id'] ?>)">Eliminar</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            </div>
          </div>
        </section>

        <!-- ================= 5. PESTAÑA: HITOS HISTÓRICOS (PROYECTOS EMBLEMÁTICOS) ================= -->
        <section id="tab-hitos" class="admin-tab">
          <!-- Configuración de Textos de la Sección Hitos -->
          <div class="card mb-4">
            <h3 class="card-title">Encabezado de la Línea de Tiempo</h3>
            <p class="text-muted">Personaliza el título y la descripción que encabezan la sección de hitos en la portada principal.</p>
            <form class="content-form mt-3" onsubmit="saveGeneralContent(event)">
              <div class="grid-2">
                <div class="field">
                  <label>Etiqueta / Tag</label>
                  <input type="text" name="hitos_tag" value="<?= htmlspecialchars($contenido['hitos_tag'] ?? 'HITOS HISTÓRICOS') ?>" />
                </div>
                <div class="field">
                  <label>Título de la Sección</label>
                  <input type="text" name="hitos_titulo" value="<?= htmlspecialchars($contenido['hitos_titulo'] ?? 'Una línea de tiempo hecha de proyectos.') ?>" />
                </div>
              </div>
              <div class="field mt-3">
                <label>Descripción / Bajada</label>
                <textarea name="hitos_descripcion" rows="2"><?= htmlspecialchars($contenido['hitos_descripcion'] ?? 'Cada año deja múltiples proyectos construidos. Tocá cada hito para ver más información, imágenes y detalles del proyecto.') ?></textarea>
              </div>
              <div class="form-actions mt-3">
                <button type="submit" class="btn btn-primary">Guardar Encabezado de Hitos</button>
              </div>
            </form>
          </div>

          <div class="card">
            <div class="card-header-flex">
              <div>
                <h3 class="card-title">Hitos Históricos y Proyectos Emblemáticos</h3>
                <p class="text-muted">Proyectos reales con trayectoria de la escuela que componen la línea de tiempo.</p>
              </div>
              <button class="btn btn-primary" onclick="openHitoModal()">+ Nuevo Hito / Proyecto</button>
            </div>

            <div class="table-responsive">
            <table class="admin-table mt-4">
              <thead>
                <tr>
                  <th>Imagen</th>
                  <th>Año</th>
                  <th>Título del Proyecto</th>
                  <th>Especialidad</th>
                  <th>Destino / Premio</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($hitos as $hito): ?>
                  <tr>
                    <td>
                      <?php if (!empty($hito['imagen'])): ?>
                        <img src="../img/<?= htmlspecialchars($hito['imagen']) ?>" class="table-thumb" alt="Miniatura" onerror="this.src='../img/hero.jpg'" />
                      <?php else: ?>
                        <span class="no-img">—</span>
                      <?php endif; ?>
                    </td>
                    <td><strong><?= htmlspecialchars($hito['anio']) ?></strong></td>
                    <td>
                      <strong><?= htmlspecialchars($hito['titulo']) ?></strong><br />
                      <small class="text-muted text-truncate" style="display:block;"><?= htmlspecialchars($hito['resumen'] ?? '') ?></small>
                    </td>
                    <td><span class="badge-status status-en-expotec"><?= htmlspecialchars($hito['especialidad'] ?? 'General') ?></span></td>
                    <td>
                      <small><strong>Destino:</strong> <?= htmlspecialchars($hito['destino'] ?? '—') ?></small><br />
                      <small class="text-muted"><strong>Reconocimiento:</strong> <?= htmlspecialchars($hito['premio'] ?? '—') ?></small>
                    </td>
                    <td style="white-space: nowrap;">
                      <button class="btn-action edit" onclick='editHito(<?= json_encode($hito) ?>)'>Editar</button>
                      <button class="btn-action delete" onclick="deleteHito(<?= $hito['id'] ?>)">Eliminar</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            </div>
          </div>
        </section>

        <!-- ================= 6. PESTAÑA: EXPOTEC ================= -->
        <section id="tab-expotec" class="admin-tab">
          <form class="content-form" onsubmit="saveGeneralContent(event)">
            <!-- Bloque 1: Introducción y Descripción General -->
            <div class="card">
              <h3 class="card-title">Presentación de la Muestra ExpoTec</h3>
              <p class="text-muted">Textos principales de la cabecera e introducción.</p>
              <div class="grid-2 mt-3">
                <div class="field">
                  <label>Título Principal de la Página (Hero)</label>
                  <input type="text" name="expotec_titulo" value="<?= htmlspecialchars($contenido['expotec_titulo'] ?? 'Una vez al año, la escuela se convierte en muestra.') ?>" />
                </div>
                <div class="field">
                  <label>Anuncio Breve en Portada de Inicio</label>
                  <input type="text" name="expotec_anuncio" value="<?= htmlspecialchars($contenido['expotec_anuncio'] ?? 'ExpoTec 2026: Exposición anual de proyectos de Construcciones, Informática y Electrónica abierta a la comunidad.') ?>" />
                </div>
              </div>
              <div class="field mt-3">
                <label>Párrafo Principal (Lead)</label>
                <textarea name="expotec_desc" rows="3"><?= htmlspecialchars($contenido['expotec_desc'] ?? 'La ExpoTec es la exposición anual en la que las tres especialidades —Construcciones, Informática y Electrónica— muestran los proyectos desarrollados durante el ciclo lectivo. Las aulas y los talleres se abren a las familias, a otras escuelas y a la comunidad de Mar del Plata.') ?></textarea>
              </div>
              <div class="field mt-3">
                <label>Párrafo Secundario (Explicación de stands y proyectos)</label>
                <textarea name="expotec_desc_secundaria" rows="3"><?= htmlspecialchars($contenido['expotec_desc_secundaria'] ?? 'Cada equipo explica su proyecto: de dónde surgió la idea, qué problema resuelve, cómo se calculó, se dibujó, se programó o se montó, y qué aprendieron en el camino. No hay stand sin prototipo funcionando, plano terminado o demo en pantalla.') ?></textarea>
              </div>
            </div>

            <!-- Bloque 2: Ficha Técnica / Datos Rápidos -->
            <div class="card mt-4">
              <h3 class="card-title">Datos Rápidos de la ExpoTec</h3>
              <p class="text-muted">Ficha informativa lateral que acompaña a la introducción.</p>
              <div class="grid-2 mt-3">
                <div class="field">
                  <label>Frecuencia</label>
                  <input type="text" name="expotec_frecuencia" value="<?= htmlspecialchars($contenido['expotec_frecuencia'] ?? 'Una vez al año') ?>" />
                </div>
                <div class="field">
                  <label>Participan</label>
                  <input type="text" name="expotec_participan" value="<?= htmlspecialchars($contenido['expotec_participan'] ?? 'Las 3 especialidades') ?>" />
                </div>
                <div class="field mt-3">
                  <label>Abierta a</label>
                  <input type="text" name="expotec_abierta" value="<?= htmlspecialchars($contenido['expotec_abierta'] ?? 'Familias, escuelas y comunidad') ?>" />
                </div>
                <div class="field mt-3">
                  <label>Sede</label>
                  <input type="text" name="expotec_sede" value="<?= htmlspecialchars($contenido['expotec_sede'] ?? 'Juan B. Justo 4287') ?>" />
                </div>
              </div>
            </div>

            <!-- Bloque 3: Qué se puede ver en cada sector -->
            <div class="card mt-4">
              <h3 class="card-title">Sectores y Especialidades en Exposición</h3>
              <p class="text-muted">Configuración de los 3 sectores de la muestra.</p>
              
              <div class="grid-2 mt-3">
                <div class="field">
                  <label>Título de la Sección</label>
                  <input type="text" name="expotec_sectores_titulo" value="<?= htmlspecialchars($contenido['expotec_sectores_titulo'] ?? 'Qué se puede ver en cada sector.') ?>" />
                </div>
                <div class="field">
                  <label>Etiqueta / Tag</label>
                  <input type="text" name="expotec_sectores_tag" value="<?= htmlspecialchars($contenido['expotec_sectores_tag'] ?? 'Proyectos en exposición') ?>" />
                </div>
              </div>

              <!-- Sector 01: Construcciones -->
              <div class="mt-4" style="border-top: 1px solid var(--linea); padding-top: 16px;">
                <h4 style="margin: 0 0 10px 0; color: var(--negro);">01. Sector Construcciones</h4>
                <div class="grid-2">
                  <div class="field">
                    <label>Título del Sector</label>
                    <input type="text" name="expotec_s1_titulo" value="<?= htmlspecialchars($contenido['expotec_s1_titulo'] ?? 'Construcciones') ?>" />
                  </div>
                  <div class="field">
                    <label>Descripción del Sector</label>
                    <input type="text" name="expotec_s1_desc" value="<?= htmlspecialchars($contenido['expotec_s1_desc'] ?? 'Maquetas de viviendas y obras, planos, cómputo de materiales y propuestas de intervención urbana.') ?>" />
                  </div>
                </div>
                <div class="field mt-3">
                  <label>Fotografía del Sector Construcciones</label>
                  <?php $s1Foto = $contenido['expotec_s1_img'] ?? 'construcciones_nueva.jpg'; ?>
                  <div class="img-upload-box">
                    <img src="../img/<?= htmlspecialchars($s1Foto) ?>" class="img-upload-preview" alt="Sector Construcciones" onerror="this.src='../img/construcciones_nueva.jpg'" />
                    <div class="img-upload-info">
                      <span class="upload-label">Cambiar foto de Construcciones</span>
                      <span class="upload-filename">Actual: <?= htmlspecialchars($s1Foto) ?></span>
                      <input type="file" name="expotec_s1_img" accept="image/*" />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sector 02: Informática -->
              <div class="mt-4" style="border-top: 1px solid var(--linea); padding-top: 16px;">
                <h4 style="margin: 0 0 10px 0; color: var(--negro);">02. Sector Informática</h4>
                <div class="grid-2">
                  <div class="field">
                    <label>Título del Sector</label>
                    <input type="text" name="expotec_s2_titulo" value="<?= htmlspecialchars($contenido['expotec_s2_titulo'] ?? 'Informática') ?>" />
                  </div>
                  <div class="field">
                    <label>Descripción del Sector</label>
                    <input type="text" name="expotec_s2_desc" value="<?= htmlspecialchars($contenido['expotec_s2_desc'] ?? 'Aplicaciones web y de escritorio, videojuegos, redes, armado de equipos y proyectos de datos.') ?>" />
                  </div>
                </div>
                <div class="field mt-3">
                  <label>Fotografía del Sector Informática</label>
                  <?php $s2Foto = $contenido['expotec_s2_img'] ?? 'informatica.jpg'; ?>
                  <div class="img-upload-box">
                    <img src="../img/<?= htmlspecialchars($s2Foto) ?>" class="img-upload-preview" alt="Sector Informática" onerror="this.src='../img/informatica.jpg'" />
                    <div class="img-upload-info">
                      <span class="upload-label">Cambiar foto de Informática</span>
                      <span class="upload-filename">Actual: <?= htmlspecialchars($s2Foto) ?></span>
                      <input type="file" name="expotec_s2_img" accept="image/*" />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sector 03: Electrónica -->
              <div class="mt-4" style="border-top: 1px solid var(--linea); padding-top: 16px;">
                <h4 style="margin: 0 0 10px 0; color: var(--negro);">03. Sector Electrónica</h4>
                <div class="grid-2">
                  <div class="field">
                    <label>Título del Sector</label>
                    <input type="text" name="expotec_s3_titulo" value="<?= htmlspecialchars($contenido['expotec_s3_titulo'] ?? 'Electrónica') ?>" />
                  </div>
                  <div class="field">
                    <label>Descripción del Sector</label>
                    <input type="text" name="expotec_s3_desc" value="<?= htmlspecialchars($contenido['expotec_s3_desc'] ?? 'Placas, sistemas de control y automatización, sensores, robótica y proyectos con microcontroladores.') ?>" />
                  </div>
                </div>
                <div class="field mt-3">
                  <label>Fotografía del Sector Electrónica</label>
                  <?php $s3Foto = $contenido['expotec_s3_img'] ?? 'electronica.jpg'; ?>
                  <div class="img-upload-box">
                    <img src="../img/<?= htmlspecialchars($s3Foto) ?>" class="img-upload-preview" alt="Sector Electrónica" onerror="this.src='../img/electronica.jpg'" />
                    <div class="img-upload-info">
                      <span class="upload-label">Cambiar foto de Electrónica</span>
                      <span class="upload-filename">Actual: <?= htmlspecialchars($s3Foto) ?></span>
                      <input type="file" name="expotec_s3_img" accept="image/*" />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-actions mt-4">
              <button type="submit" class="btn btn-primary">Guardar Todos los Cambios de ExpoTec</button>
            </div>
          </form>
        </section>

        <!-- ================= 7. PESTAÑA: INSTITUCIONALES ================= -->
        <section id="tab-institucionales" class="admin-tab">
          <form class="content-form" onsubmit="saveGeneralContent(event)">
            <!-- 1. Encabezado / Hero Institucional -->
            <div class="card">
              <h3 class="card-title">Encabezado y Portada de Institucionales</h3>
              <p class="text-muted">Personaliza los títulos, subtítulos y nombres de pestañas en <code>pages/institucionales.html</code>.</p>

              <div class="grid-2 mt-3">
                <div class="field">
                  <label>Etiqueta Superior (Tag)</label>
                  <input type="text" name="inst_hero_tag" value="<?= htmlspecialchars($contenido['inst_hero_tag'] ?? 'Marco Institucional') ?>" />
                </div>
                <div class="field">
                  <label>Título Principal</label>
                  <input type="text" name="inst_hero_titulo" value="<?= htmlspecialchars($contenido['inst_hero_titulo'] ?? 'Normativas Oficiales y Acuerdos de Convivencia') ?>" />
                </div>
              </div>

              <div class="field mt-3">
                <label>Subtítulo o Resumen del Encabezado</label>
                <textarea name="inst_hero_subtitulo" rows="2"><?= htmlspecialchars($contenido['inst_hero_subtitulo'] ?? 'Reglamentaciones vigentes, pautas de convivencia escolar y marco legal que rigen la actividad académica y técnica de la E.E.S.T. N.º 5 “Amancio Williams”.') ?></textarea>
              </div>

              <div class="grid-2 mt-3">
                <div class="field">
                  <label>Etiqueta Pestaña 1 (AIC)</label>
                  <input type="text" name="inst_tab_aic_label" value="<?= htmlspecialchars($contenido['inst_tab_aic_label'] ?? 'Acuerdo Institucional de Convivencia (AIC)') ?>" />
                </div>
                <div class="field">
                  <label>Etiqueta Pestaña 2 (Régimen Académico)</label>
                  <input type="text" name="inst_tab_ra_label" value="<?= htmlspecialchars($contenido['inst_tab_ra_label'] ?? 'Régimen Académico (Res. 1650/24)') ?>" />
                </div>
              </div>
            </div>

            <!-- 2. Pestaña: Régimen Académico -->
            <div class="card mt-4">
              <h3 class="card-title">Sección Régimen Académico (Resolución Provincial N.º 1650/24)</h3>
              <p class="text-muted">Textos, videos explicativos y normativas vigentes sobre evaluación y acreditación.</p>

              <div class="grid-2 mt-3">
                <div class="field">
                  <label>Título de la Sección</label>
                  <input type="text" name="inst_ra_titulo" value="<?= htmlspecialchars($contenido['inst_ra_titulo'] ?? 'Nuevo Régimen Académico — Resolución N.º 1650/24 DGCyE') ?>" />
                </div>
                <div class="field">
                  <label>Fecha o Estado de Actualización</label>
                  <input type="text" name="inst_ra_fecha" value="<?= htmlspecialchars($contenido['inst_ra_fecha'] ?? 'Actualización institucional: 5/12/24') ?>" />
                </div>
              </div>

              <div class="field mt-3">
                <label>Párrafo de Introducción</label>
                <textarea name="inst_ra_desc" rows="3"><?= htmlspecialchars($contenido['inst_ra_desc'] ?? 'Dejamos a disposición la información sobre el nuevo régimen académico y los videos explicativos elaborados por estudiantes y docentes que ayudan a comprender los cambios vigentes en la educación secundaria técnica:') ?></textarea>
              </div>

              <!-- Video Explicativo -->
              <div class="field mt-4" style="background: var(--crema-suave); padding: 20px; border-radius: 12px; border: 1px solid var(--linea);">
                <h4 style="margin: 0 0 10px 0; color: var(--verde); font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
                  Video Explicativo del Régimen Académico
                </h4>
                <div class="field mt-2">
                  <label>URL directa del Video (MP4 o enlace externo)</label>
                  <input type="url" name="inst_ra_video_url" value="<?= htmlspecialchars($contenido['inst_ra_video_url'] ?? 'https://eest5mdp.edu.ar/assets/regimen%20acad%C3%A9mico%20por%20estudiantes%203-CCaRwghv.mp4') ?>" placeholder="https://..." />
                </div>
                <div class="field mt-3">
                  <label>O subir nuevo archivo de video MP4</label>
                  <input type="file" name="inst_ra_video_file" accept="video/mp4,video/webm" />
                  <?php if (!empty($contenido['inst_ra_video_file'])): ?>
                    <small class="text-muted" style="display: block; margin-top: 4px;">Video local activo: <strong><?= htmlspecialchars($contenido['inst_ra_video_file']) ?></strong></small>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Pilares del Régimen Académico -->
              <h4 class="card-subtitle mt-4">Pilares Formativos del Régimen Académico</h4>
              
              <div class="field mt-3">
                <label>Pilar 1: Título</label>
                <input type="text" name="inst_ra_p1_titulo" value="<?= htmlspecialchars($contenido['inst_ra_p1_titulo'] ?? '1. Organización Temporal y Calificación Cuatrimestral') ?>" />
                <label class="mt-2">Pilar 1: Contenido / Criterios</label>
                <textarea name="inst_ra_p1_desc" rows="3"><?= htmlspecialchars($contenido['inst_ra_p1_desc'] ?? "El ciclo lectivo se organiza en dos cuatrimestres con informe valorativo RITE (TEA, TEP y TED). La calificación final es numérica del 1 al 10, requiriéndose 7 puntos mínimos para la acreditación.") ?></textarea>
              </div>

              <div class="field mt-3">
                <label>Pilar 2: Título</label>
                <input type="text" name="inst_ra_p2_titulo" value="<?= htmlspecialchars($contenido['inst_ra_p2_titulo'] ?? '2. Acreditación por Espacio Curricular (Materias)') ?>" />
                <label class="mt-2">Pilar 2: Contenido</label>
                <textarea name="inst_ra_p2_desc" rows="3"><?= htmlspecialchars($contenido['inst_ra_p2_desc'] ?? "Se consolida el principio de aprobación por materia: el estudiante acredita saberes logrados y no recursa materias ya aprobadas. Aquellas pendientes ingresan a períodos programados de intensificación pedagógica.") ?></textarea>
              </div>

              <div class="field mt-3">
                <label>Pilar 3: Título</label>
                <input type="text" name="inst_ra_p3_titulo" value="<?= htmlspecialchars($contenido['inst_ra_p3_titulo'] ?? '3. Períodos de Intensificación de la Enseñanza') ?>" />
                <label class="mt-2">Pilar 3: Contenido</label>
                <textarea name="inst_ra_p3_desc" rows="3"><?= htmlspecialchars($contenido['inst_ra_p3_desc'] ?? "Instancias específicas de acompañamiento para afianzar saberes pendientes: Período Diciembre (cierre de ciclo), Período Febrero/Marzo (previo al inicio de clases) y Período Julio/Agosto (intermedio).") ?></textarea>
              </div>

              <div class="field mt-3">
                <label>Pilar 4: Título</label>
                <input type="text" name="inst_ra_p4_titulo" value="<?= htmlspecialchars($contenido['inst_ra_p4_titulo'] ?? '4. Marco Específico para la Escuela Técnica (Anexo 8 ETP)') ?>" />
                <label class="mt-2">Pilar 4: Contenido</label>
                <textarea name="inst_ra_p4_desc" rows="3"><?= htmlspecialchars($contenido['inst_ra_p4_desc'] ?? "Formación Científico-Tecnológica, Técnico-Específica en Talleres y Laboratorios donde la aprobación se sustenta en capacidades profesionales y seguridad, y Prácticas Profesionalizantes obligatorias en 7.º año.") ?></textarea>
              </div>

              <div class="field mt-3">
                <label>Pilar 5: Título</label>
                <input type="text" name="inst_ra_p5_titulo" value="<?= htmlspecialchars($contenido['inst_ra_p5_titulo'] ?? '5. Equipo de Definición de Trayectorias Educativas (EDTE)') ?>" />
                <label class="mt-2">Pilar 5: Contenido</label>
                <textarea name="inst_ra_p5_desc" rows="3"><?= htmlspecialchars($contenido['inst_ra_p5_desc'] ?? "Equipo integrado por directivos, jefes de área, preceptores y profesores para diseñar trayectorias personalizadas y realizar el seguimiento pedagógico cercano de cada estudiante.") ?></textarea>
              </div>
            </div>

            <!-- 3. Pestaña: Acuerdo Institucional de Convivencia (AIC) -->
            <div class="card mt-4">
              <h3 class="card-title">Sección Acuerdo Institucional de Convivencia (AIC)</h3>
              <p class="text-muted">Pautas, derechos, deberes, compromisos formativos y resoluciones del Consejo de Convivencia.</p>

              <div class="field mt-3">
                <label>Título de Presentación</label>
                <input type="text" name="inst_aic_pres_titulo" value="<?= htmlspecialchars($contenido['inst_aic_pres_titulo'] ?? 'Presentación del AIC') ?>" />
              </div>

              <div class="field mt-3">
                <label>Texto de Presentación e Identidad de la Educación Técnica</label>
                <textarea name="inst_aic_pres_texto" rows="4"><?= htmlspecialchars($contenido['inst_aic_pres_texto'] ?? 'La educación técnico profesional introduce a los estudiantes en un recorrido de profesionalización a partir del acceso a conocimientos y habilidades profesionales, formando personas con criterio democrático y capacidad resolutiva.') ?></textarea>
              </div>

              <div class="field mt-3">
                <label>Mensaje a Familias, Docentes y Estudiantes</label>
                <textarea name="inst_aic_familias_texto" rows="4"><?= htmlspecialchars($contenido['inst_aic_familias_texto'] ?? 'La redacción de este AIC sostiene en primer lugar la finalidad educativa como referente principal de las normas internas. Conforme a la Res. 1709/09, valoramos a la escuela como unidad reflexiva que promueve que todos son sujetos de respeto y diálogo.') ?></textarea>
              </div>

              <div class="field mt-3">
                <label>Principios Fundacionales del Acuerdo (Cooperativismo, Respeto, Comunicación)</label>
                <textarea name="inst_aic_principios_texto" rows="4"><?= htmlspecialchars($contenido['inst_aic_principios_texto'] ?? 'Cooperativismo y solidaridad, comunicación asertiva, aprecio por la diversidad, expresión constructiva de emociones y resolución colaborativa y pacífica de situaciones conflictivas.') ?></textarea>
              </div>

              <div class="field mt-3">
                <label>Marco Legal (Leyes Nacionales y Provinciales de Referencia)</label>
                <textarea name="inst_aic_marco_legal_texto" rows="4"><?= htmlspecialchars($contenido['inst_aic_marco_legal_texto'] ?? 'Ley Nacional 26.206, Ley Provincial 13.688, Ley de ETP 26.058, Higiene y Seguridad Laboral 19.587, Ley de Protección de Derechos 26.061 / 13.298, Ley ESI 26.150 y Prevención del Grooming 27.590.') ?></textarea>
              </div>

              <h4 class="card-subtitle mt-4">Capítulos Normativos del AIC</h4>

              <div class="field mt-3">
                <label>Capítulo I: Introducción y Normas Generales de Asistencia y Salida</label>
                <textarea name="inst_aic_cap1_texto" rows="3"><?= htmlspecialchars($contenido['inst_aic_cap1_texto'] ?? 'Reglas de asistencia, puntualidad, ingreso y egreso institucional, validez de constancias y autorizaciones de retiro para mayores de 18 años.') ?></textarea>
              </div>

              <div class="field mt-3">
                <label>Capítulo II: Derechos y Deberes de los Estudiantes (Incluye Taller, Vestimenta y Celulares)</label>
                <textarea name="inst_aic_cap2_texto" rows="5"><?= htmlspecialchars($contenido['inst_aic_cap2_texto'] ?? 'Derecho a un ambiente respetuoso y seguro. Deberes de cuidado del edificio, asistencia con guardapolvo o indumentaria técnica obligatoria para talleres de acuerdo al Decreto Ley 19.587, y uso regulado de dispositivos móviles exclusivamente con fines pedagógicos autorizados.') ?></textarea>
              </div>

              <div class="field mt-3">
                <label>Capítulo III: Derechos y Deberes de Padres, Madres y Tutores</label>
                <textarea name="inst_aic_cap3_texto" rows="3"><?= htmlspecialchars($contenido['inst_aic_cap3_texto'] ?? 'Acompañamiento escolar, notificación mediante Cuaderno de Comunicaciones, acreditación de tutelas y asistencia a entrevistas institucionales programadas.') ?></textarea>
              </div>

              <div class="field mt-3">
                <label>Capítulo IV: Personal Docente y No Docente</label>
                <textarea name="inst_aic_cap4_texto" rows="3"><?= htmlspecialchars($contenido['inst_aic_cap4_texto'] ?? 'Compromiso pedagógico, trato digno, rol orientador y preservación activa del bienestar y las trayectorias formativas de los estudiantes técnicos.') ?></textarea>
              </div>

              <div class="field mt-3">
                <label>Capítulo V: Faltas, Medidas Reparadoras y Sanciones Pedagógicas</label>
                <textarea name="inst_aic_cap5_texto" rows="4"><?= htmlspecialchars($contenido['inst_aic_cap5_texto'] ?? 'Criterios de gradualidad, diálogo reflexivo, actas de compromiso, reparación del daño material o comunitario y sanciones pedagógicas acordes al régimen provincial.') ?></textarea>
              </div>

              <div class="field mt-3">
                <label>Consejo Institucional de Convivencia (CIC)</label>
                <textarea name="inst_aic_cic_texto" rows="3"><?= htmlspecialchars($contenido['inst_aic_cic_texto'] ?? 'Órgano consultivo integrado por estudiantes, docentes, directivos y familias para analizar situaciones complejas y asesorar en la adopción de medidas formativas.') ?></textarea>
              </div>

              <!-- Descarga de Documento PDF Oficial -->
              <div class="field mt-4" style="background: var(--crema-suave); padding: 18px; border-radius: 12px; border: 1px solid var(--linea);">
                <label>Enlace o Archivo PDF Oficial del AIC Completo</label>
                <input type="text" name="inst_aic_pdf_url" value="<?= htmlspecialchars($contenido['inst_aic_pdf_url'] ?? '') ?>" placeholder="https://... o nombre del archivo cargado" />
                <div class="mt-2">
                  <label>O subir documento PDF del AIC</label>
                  <input type="file" name="inst_aic_pdf_file" accept=".pdf,application/pdf" />
                  <?php if (!empty($contenido['inst_aic_pdf_file'])): ?>
                    <small class="text-muted" style="display: block; margin-top: 4px;">PDF activo: <strong><?= htmlspecialchars($contenido['inst_aic_pdf_file']) ?></strong></small>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Botón Guardar -->
            <div class="form-actions mt-4">
              <button type="submit" class="btn btn-primary" style="font-size: 1.05rem; padding: 12px 28px;">
                Guardar Todos los Cambios de Institucionales
              </button>
            </div>
          </form>
        </section>

        <!-- ================= 8. PESTAÑA: SEGURIDAD & ACCESO ================= -->
        <section id="tab-seguridad" class="admin-tab">
          <div class="card" style="max-width: 560px;">
            <h3 class="card-title">Seguridad y Acceso al Panel</h3>
            <p class="text-muted">Actualiza la contraseña segura con la que ingresas al panel de gestión.</p>
            
            <form onsubmit="changePassword(event)" class="mt-4">
              <div class="field">
                <label>Nueva Contraseña</label>
                <input type="password" id="new_admin_password" required minlength="6" placeholder="Mínimo 6 caracteres" />
              </div>
              <div class="field mt-3">
                <label>Confirmar Nueva Contraseña</label>
                <input type="password" id="confirm_admin_password" required minlength="6" placeholder="Repite la contraseña" />
              </div>
              <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
              </div>
            </form>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- MODAL ESPECIALIDAD -->
  <div id="modal-especialidad" class="modal">
    <div class="modal-content" style="max-width: 650px;">
      <div class="modal-header">
        <h3 id="modal-esp-title">Especialidad</h3>
        <button class="modal-close" onclick="closeModal('modal-especialidad')">&times;</button>
      </div>
      <form onsubmit="submitEspecialidad(event)" enctype="multipart/form-data">
        <input type="hidden" name="id" id="esp_id" />
        <input type="hidden" name="imagen" id="esp_imagen" />

        <div class="grid-2">
          <div class="field">
            <label>Nombre de la Especialidad *</label>
            <input type="text" name="nombre" id="esp_nombre" placeholder="Ej: Construcciones, Informática..." required />
          </div>
          <div class="field">
            <label>Título Profesional / Habilitante</label>
            <input type="text" name="titulo" id="esp_titulo" placeholder="Ej: Maestro Mayor de Obras..." />
          </div>
        </div>

        <div class="grid-2 mt-3">
          <div class="field">
            <label>Años de Duración</label>
            <input type="number" name="duracion_anios" id="esp_duracion" value="7" required />
          </div>
          <div class="field">
            <label>Icono (hammer, laptop, hardware-chip, etc.)</label>
            <input type="text" name="icono" id="esp_icono" value="laptop" required />
          </div>
        </div>

        <!-- FOTO / IMAGEN DE LA ESPECIALIDAD -->
        <div class="field mt-3" style="background: var(--crema-suave); padding: 16px; border-radius: 10px; border: 1px solid var(--linea);">
          <label style="font-weight: 700; color: var(--carbon); margin-bottom: 8px; display: block;">Foto de la Especialidad</label>
          <div style="display: flex; gap: 16px; align-items: center;">
            <img id="esp_img_preview" src="../img/hero.jpg" alt="Vista previa" style="width: 88px; height: 88px; object-fit: cover; border-radius: 8px; border: 1px solid var(--linea); background: #fff;" onerror="this.src='../img/hero.jpg'" />
            <div style="flex: 1;">
              <input type="file" name="imagen_archivo" id="esp_file_input" accept="image/*" onchange="previewEspecialidadImg(event)" style="font-size: 0.85rem;" />
              <small class="text-muted" style="display: block; margin-top: 6px;">Seleccioná una nueva imagen (JPG, PNG o WebP) para reemplazar la foto actual en la página web.</small>
              <div id="esp_img_name_display" style="font-size: 0.8rem; color: var(--verde); font-weight: 600; margin-top: 4px;"></div>
            </div>
          </div>
        </div>

        <div class="field mt-3">
          <label>Descripción de la Carrera / Especialidad *</label>
          <textarea name="descripcion" id="esp_descripcion" rows="4" placeholder="Describí los contenidos, proyectos, talleres y campo ocupacional de la especialidad..." required></textarea>
        </div>

        <div class="modal-footer mt-4">
          <button type="button" class="btn btn-secondary" onclick="closeModal('modal-especialidad')">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar Especialidad</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL HITO / PROYECTO EMBLEMÁTICO -->
  <div id="modal-hito" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="modal-hito-title">Hito / Proyecto Emblemático</h3>
        <button class="modal-close" onclick="closeModal('modal-hito')">&times;</button>
      </div>
      <form onsubmit="submitHito(event)" enctype="multipart/form-data">
        <input type="hidden" name="id" id="hito_id" />
        <div class="grid-2">
          <div class="field">
            <label>Año (ej: 2018 o 2023-2024)</label>
            <input type="text" name="anio" id="hito_anio" required />
          </div>
          <div class="field">
            <label>Especialidad (ej: Electrónica y 3D, Informática)</label>
            <input type="text" name="especialidad" id="hito_especialidad" placeholder="Electrónica y 3D" />
          </div>
        </div>
        <div class="field mt-3">
          <label>Título del Proyecto / Hito</label>
          <input type="text" name="titulo" id="hito_titulo" required />
        </div>
        <div class="field mt-3">
          <label>Resumen Corto (visible en la fila)</label>
          <textarea name="resumen" id="hito_resumen" rows="2" required></textarea>
        </div>
        <div class="field mt-3">
          <label>Descripción Completa (visible al expandir la tarjeta)</label>
          <textarea name="descripcion" id="hito_descripcion" rows="4" required></textarea>
        </div>
        <div class="grid-2 mt-3">
          <div class="field">
            <label>Destino o Beneficiario (ej: Hospital Materno Infantil)</label>
            <input type="text" name="destino" id="hito_destino" />
          </div>
          <div class="field">
            <label>Premio o Reconocimiento (ej: Docentes Innovadores 2018)</label>
            <input type="text" name="premio" id="hito_premio" />
          </div>
        </div>
        <div class="grid-2 mt-3">
          <div class="field">
            <label>Subir Imagen del Proyecto</label>
            <input type="file" name="imagen_archivo" accept="image/*" />
          </div>
          <div class="field">
            <label>O nombre de archivo existente (ej: faciliart.jpg)</label>
            <input type="text" name="imagen" id="hito_imagen" />
          </div>
        </div>
        <div class="grid-2 mt-3">
          <div class="field">
            <label>Enlace Externo (opcional)</label>
            <input type="url" name="enlace" id="hito_enlace" placeholder="https://..." />
          </div>
          <div class="field">
            <label>Orden de Posición</label>
            <input type="number" name="orden" id="hito_orden" value="1" />
          </div>
        </div>
        <div class="modal-footer mt-4">
          <button type="button" class="btn btn-secondary" onclick="closeModal('modal-hito')">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar Hito</button>
        </div>
      </form>
    </div>
  </div>

  <script src="js/admin.js?v=2"></script>
</body>
</html>
