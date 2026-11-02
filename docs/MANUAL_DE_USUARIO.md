# 📘 Manual de Usuario y Navegación del Sistema
## Escuela de Educación Secundaria Técnica N.º 5 "Amancio Williams"

Este documento proporciona una guía exhaustiva e intuitiva para que estudiantes, docentes, directivos y administradores aprendan a utilizar todas las funcionalidades del sitio web institucional y su panel de gestión administrativa.

---

## 📑 Tabla de Contenidos
1. [Introducción al Sistema](#1-introducción-al-sistema)
2. [Navegación del Portal Público](#2-navegación-del-portal-público)
   - [2.1 Cabecera y Menú de Navegación](#21-cabecera-y-menú-de-navegación)
   - [2.2 Portada (Inicio / Landing)](#22-portada-inicio--landing)
   - [2.3 Sección "La Escuela"](#23-sección-la-escuela)
   - [2.4 Sección "ExpoTec"](#24-sección-expotec)
   - [2.5 Sección "Marco Institucional"](#25-sección-marco-institucional)
   - [2.6 Sección "Contacto y Ubicación"](#26-sección-contacto-y-ubicación)
3. [Panel de Administración (`sys-control-98`)](#3-panel-de-administración-sys-control-98)
   - [3.1 Acceso Seguro y Autenticación](#31-acceso-seguro-y-autenticación)
   - [3.2 Medidas Anti Fuerza Bruta](#32-medidas-anti-fuerza-bruta)
   - [3.3 Estructura del Panel de Control](#33-estructura-del-panel-de-control)
   - [3.4 Gestión de Contenidos por Módulo](#34-gestión-de-contenidos-por-módulo)
     - [Módulo 1: Portada (Landing)](#módulo-1-portada-landing)
     - [Módulo 2: La Escuela](#módulo-2-la-escuela)
     - [Módulo 3: Anuncios e Instagram](#módulo-3-anuncios-e-instagram)
     - [Módulo 4: Especialidades](#módulo-4-especialidades)
     - [Módulo 5: Hitos Históricos](#módulo-5-hitos-históricos)
     - [Módulo 6: ExpoTec (Proyectos)](#módulo-6-expotec-proyectos)
     - [Módulo 7: Institucionales](#módulo-7-institucionales)
     - [Módulo 8: Bandeja de Contacto](#módulo-8-bandeja-de-contacto)
     - [Módulo 9: Seguridad y Cambio de Contraseña](#módulo-9-seguridad-y-cambio-de-contraseña)
   - [3.5 Cierre de Sesión y Tiempo de Espera (Timeout)](#35-cierre-de-sesión-y-tiempo-de-espera-timeout)

---

## 1. Introducción al Sistema

El sitio web de la **E.E.S.T. N.º 5 "Amancio Williams"** está diseñado con una arquitectura doble:
1. **Portal Público:** Un sitio interactivo, accesible y adaptable a teléfonos, tablets y computadoras, orientado a toda la comunidad educativa de Mar del Plata.
2. **Backoffice / Panel de Gestión (`sys-control-98`):** Un panel protegido que permite al equipo de gestión y directivos modificar textos, imágenes, proyectos, autoridades y consultar mensajes de contacto en tiempo real sin necesidad de editar código.

> [!NOTE]
> Por políticas de ciberseguridad, la ruta tradicional `/admin/` funciona como trampa (honeypot) simulando un error 404. El panel legítimo de administración se encuentra en la ruta segura `/sys-control-98/login.php`.

---

## 2. Navegación del Portal Público

### 2.1 Cabecera y Menú de Navegación
En la parte superior de todas las páginas encontrarás la barra de navegación:
- **Logo Institucional:** Al hacer clic, te redirige a la página de inicio.
- **Enlaces Principales:**
  - **Inicio:** Portada principal.
  - **La Escuela:** Historia, equipo de gestión y talleres.
  - **Especialidades:** Construcciones, Informática y Electrónica.
  - **ExpoTec:** Muestra anual de proyectos.
  - **Institucionales:** Marco normativo, régimen académico y convivencia.
  - **Contacto:** Formulario y vías de comunicación oficial.
- **Menú Móvil:** En dispositivos móviles, se despliega un botón tipo hamburguesa (`☰`) para acceder cómodamente a todas las secciones.

---

### 2.2 Portada (Inicio / Landing)
Archivo: `index.html`

- **Sección Hero:** Presenta el nombre de la institución, el lema central (*"Aprender haciendo. Construir el futuro."*) y una fotografía representativa.
- **Sobre Nosotros:** Resumen de la propuesta pedagógica técnica de 7 años.
- **Especialidades Técnicas:** Tarjetas interactivas con información de:
  - **01. Maestro Mayor de Obras (Construcciones)**
  - **02. Técnico en Informática Profesional y Personal**
  - **03. Técnico en Electrónica**
- **Línea de Tiempo de Hitos Históricos:** Muestra los grandes logros de la escuela (Picosatélite MDQube-SAT1 con SpaceX, SatDuino, App Recordame, FaciliArt 3D, Casco Iron Man). Al tocar cualquier hito se abre un panel interactivo con fotos y detalles.
- **Novedades e Instagram Oficial:** Feed sincronizado con las publicaciones oficiales de `@tecnicacinco_mdq`.
- **Pie de Página (Footer):** Enlaces rápidos, dirección física, horarios de atención y redes sociales.

---

### 2.3 Sección "La Escuela"
Archivo: `pages/escuela.html`

- **Equipo Directivo y Autoridades:** Listado completo organizado por turnos:
  - **Turno Mañana (TM):** Vicedirección, Secretaría y Jefatura de Área.
  - **Turno Tarde (TT):** Vicedirección, Secretaría y Jefatura de Área.
  - **Turno Noche (TN):** Vicedirección y Jefatura de Área.
- **Aprender Haciendo (Talleres):** Explicación del trabajo en talleres y laboratorios desde el 1.° año hasta las Prácticas Profesionalizantes del 7.° año.
- **Perfil del Egresado Técnico:** Competencias laborales y académicas que adquieren los estudiantes.
- **Galería Institucional:** Fotografías de aulas, máquinas, proyectos y actividades.

---

### 2.4 Sección "ExpoTec"
Archivo: `pages/expotec.html`

- **Descripción de la Muestra:** Información sobre la exposición anual abierta a familias y empresas.
- **Sectores por Especialidad:**
  - *Construcciones:* Maquetas, planos de obras civiles y cómputo de materiales.
  - *Informática:* Aplicaciones móviles, videojuegos, desarrollo web y redes.
  - *Electrónica:* Automatización, robótica, sensores y microcontroladores.
- **Catálogo de Proyectos:** Tarjetas con el estado de cada desarrollo (*En desarrollo*, *Finalizado* o *En ExpoTec*).

---

### 2.5 Sección "Marco Institucional"
Archivo: `pages/institucionales.html`

- **Normativas Oficiales:**
  - **Acuerdo Institucional de Convivencia (AIC):** Pautas de respeto mutuo, derechos y deberes.
  - **Régimen Académico (RA):** Criterios de evaluación, acreditación y correlatividades.
- **Acceso por Código QR y Descarga:** Enlaces directos a los documentos en PDF para familias y alumnos.
- **Video Explicativo:** Reproductor integrado con el material audiovisual informativo sobre el régimen académico.

---

### 2.6 Sección "Contacto y Ubicación"
Archivo: `pages/contacto.html`

- **Formulario de Mensajes:** Permite enviar consultas a secretaría ingresando:
  - Nombre completo.
  - Correo electrónico de contacto.
  - Asunto del mensaje.
  - Mensaje detallado.
  - *Protección inteligente:* Cuenta con validación anti-spam y token de seguridad CSRF.
- **Ubicación en el Mapa:** Mapa dinámico con la ubicación exacta en **Av. Juan B. Justo 4287, Mar del Plata**.
- **Canales Directos:** Correo electrónico institucional (`infot5@eest5mdp.edu.ar`), horarios de atención y redes oficiales.

---

## 3. Panel de Administración (`sys-control-98`)

El panel de administración permite mantener la web actualizada sin tocar una sola línea de código.

### 3.1 Acceso Seguro y Autenticación
1. Abre tu navegador web e ingresa a:
   ```text
   http://localhost/web-escolar/sys-control-98/login.php
   ```
   *(O la URL del dominio donde esté alojado el sitio + `/sys-control-98/login.php`)*.
2. Ingresa tus credenciales:
   - **Usuario o Correo:** Usuario asignado (por ej. `gestor_sys98` o el correo del administrador).
   - **Contraseña:** Contraseña segura establecida.
3. Presiona el botón **Iniciar Sesión**.

### 3.2 Medidas Anti Fuerza Bruta
- El sistema cuenta con limitador de intentos por dirección IP.
- Si se ingresan credenciales incorrectas **5 veces consecutivas**, la IP quedará automáticamente bloqueada durante **15 minutos**.
- La pantalla informará cuántos intentos quedan antes del bloqueo.

### 3.3 Estructura del Panel de Control
Una vez autenticado, verás:
- **Barra Lateral Izquierda (Sidebar):** Navegación rápida entre los 9 módulos de contenido.
- **Barra Superior (Topbar):** Selector móvil de pestañas, usuario actual logueado y botón directo para *"Ver Sitio Web"*.
- **Área Central:** Formularios interactivos con botones de guardado y previsualización de imágenes.
- **Notificaciones Toast:** Cada vez que guardes cambios, aparecerá un aviso verde flotante confirmando el éxito.

---

### 3.4 Gestión de Contenidos por Módulo

#### Módulo 1: Portada (Landing)
- **Título Principal:** Nombre visible de la escuela en la portada.
- **Slogan:** Frase destacada institucional.
- **Subtítulo y Descripción:** Párrafo explicativo debajo del título.
- **Imagen del Hero:** Puedes seleccionar y subir una nueva fotografía de portada.

#### Módulo 2: La Escuela
- **Autoridades por Turno:** Edita nombres del Director, Vicedirectores (TM, TT, TN), Secretarios y Jefes de Área.
- **Textos Institucionales:** Párrafos sobre la pedagogía de taller y el perfil del egresado.
- **Imágenes:** Actualiza la foto de fachada y la foto de talleres.

#### Módulo 3: Anuncios e Instagram
- Permite configurar el feed embebido de Instagram vía la plataforma Behold (`behold_feed_url` y enlace a cuenta oficial).

#### Módulo 4: Especialidades
- Lista las 3 carreras técnicas (Construcciones, Informática, Electrónica).
- Al pulsar **"Editar"** en cualquiera:
  - Cambia el título otorgado (ej. *Maestro Mayor de Obras*).
  - Modifica la descripción y competencias formativas.
  - Cambia la imagen representativa de la especialidad.
  - Guarda los cambios con un clic.

#### Módulo 5: Hitos Históricos
- Permite ordenar y dar a conocer los proyectos premiados de la institución.
- **Crear Nuevo Hito:** Haz clic en *"Agregar Hito"*, define el año, título, resumen, premio obtenido, enlace externo y fotografía.
- **Editar o Eliminar:** Modifica hitos existentes o elimínalos si quedan desactualizados.

#### Módulo 6: ExpoTec (Proyectos)
- Administra los proyectos creados por los estudiantes para la muestra técnica.
- Define el estado del proyecto:
  - `En desarrollo`
  - `Finalizado`
  - `En ExpoTec`
- Asocia el proyecto a la especialidad correspondiente y añade foto del prototipo.

#### Módulo 7: Institucionales
- Actualiza los enlaces a las resoluciones ministeriales.
- Actualiza los enlaces para los códigos QR del Acuerdo Institucional de Convivencia y del Régimen Académico.

#### Módulo 8: Bandeja de Contacto
- Muestra en tiempo real todas las consultas enviadas por el formulario web público.
- Columnas: Nombre, Correo, Asunto, Mensaje, Fecha y Estado.
- Puedes marcar mensajes como **Leídos** o eliminarlos una vez respondidos.

#### Módulo 9: Seguridad y Cambio de Contraseña
- Permite al administrador cambiar su contraseña actual por una nueva.
- Requiere ingresar la nueva clave y su confirmación (mínimo 6 caracteres).
- La contraseña se guarda con cifrado seguro hash `BCRYPT`.

---

### 3.5 Cierre de Sesión y Tiempo de Espera (Timeout)
- **Cierre Manual:** Haz clic en **"Cerrar Sesión"** en la parte inferior de la barra lateral.
- **Cierre Automático por Inactividad:** Si dejas la sesión abierta sin realizar ninguna acción durante más de **30 minutos**, el sistema invalidará la sesión automáticamente por seguridad y te solicitará ingresar nuevamente.

---

> [!TIP]
> Recuerda siempre hacer clic en el botón **Guardar** de la sección correspondiente antes de cambiar de pestaña para asegurar que los cambios se reflejen de inmediato en el sitio público.
