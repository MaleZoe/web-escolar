# E.E.S.T. N.º 5 "Amancio Williams" - Sitio Institucional y Panel de Gestión

Sitio web institucional y panel administrativo interactivo de la Escuela de Educación Secundaria Técnica N.º 5 "Amancio Williams" de Mar del Plata.

---

## 📚 Documentación Oficial

- 🎓 **Para recorrer el código:** en VS Code, buscá `MOSTRAR-` (por ejemplo `MOSTRAR-CSRF`) con Ctrl + Shift + F. Cada resultado explica esa parte del código.
- 📘 [**Manual de Usuario y Navegación**](file:///docs/MANUAL_DE_USUARIO.md): Guía paso a paso sobre cómo usar el portal público, navegar entre pantallas y operar el panel de gestión administrativa (`sys-control-98`).
- 🛠️ [**Guía de Instalación y Configuración**](file:///docs/GUIA_INSTALACION_Y_CONFIGURACION.md): Instrucciones detalladas para configurar el entorno de desarrollo local (XAMPP, Laragon, PHP 8+, MySQL) y en la nube (Vercel, TiDB Cloud).

---

## ✨ Características Principales

- **Frontend Rápido y Accesible:** Desarrollado con HTML5 semántico, CSS3 moderno con variables de diseño y JavaScript ES6 modular.
- **Sincronización Dinámica:** Datos institucionales, especialidades, proyectos y fotos consumidos en tiempo real mediante `api/get_public_data.php`.
- **Panel Administrativo Integral (`sys-control-98`):**
  - Gestión de Portada (Hero, slogans, imágenes).
  - Actualización del Equipo Directivo en los 3 turnos (TM, TT, TN).
  - Catálogo de Proyectos técnicos para ExpoTec.
  - Línea de tiempo interactiva de Hitos Históricos.
  - Bandeja de mensajes de contacto con validación anti-spam y CSRF.
  - Modificación de credenciales de seguridad.
- **Seguridad Reforzada:**
  - Enrutamiento seguro (honeypot en `/admin/` para despistar bots).
  - Protección contra ataques de fuerza bruta con bloqueo por IP.
  - Sesiones almacenadas en base de datos (`sesiones_php`) preparadas para entornos serverless.
  - Cifrado de contraseñas con `password_hash()` BCRYPT.

---

## 🗂️ Arquitectura del Repositorio

```text
/
├── index.html                   # Home: portada, especialidades, hitos e Instagram
├── pages/
│   ├── escuela.html             # Autoridades, talleres y perfil del egresado
│   ├── expotec.html             # Muestra anual y catálogo de proyectos
│   ├── institucionales.html     # Marco normativo (AIC y Régimen Académico)
│   └── contacto.html            # Formulario de consultas y mapa interactivo
├── css/
│   └── styles.css               # Estilos globales y diseño responsive
├── js/
│   ├── script.js                # Interacciones de UI (menús, modales)
│   └── dynamic_content.js       # Consumo asíncrono de la API PHP
├── api/                         # Backend REST en PHP
│   ├── db.php                   # Conexión PDO MySQL con soporte SSL
│   ├── get_public_data.php      # Suministro de datos públicos
│   └── contact.php              # Procesamiento seguro de mensajes
├── sys-control-98/              # Backoffice / Panel de control administrativo
│   ├── login.php                # Inicio de sesión con mitigación de ataques
│   ├── index.php                # Panel de control por módulos
│   └── config.php               # Control de sesiones, tokens CSRF y timeouts
├── docs/                        # Manuales y guías del sistema
│   ├── MANUAL_DE_USUARIO.md     # Manual de uso y navegación
│   └── GUIA_INSTALACION_Y_CONFIGURACION.md # Guía técnica de instalación
├── img/                         # Archivos multimedia institucionales
├── database.sql                 # Volcado completo de la base de datos MySQL
└── .env.example                 # Variables de entorno para base de datos
```

---

## 🎨 Identidad Visual
Paleta de colores basada en el escudo institucional:
- **Verde Institucional:** `#346401`
- **Rojo:** `#FD0002`
- **Negro:** `#111111`
- **Crema:** `#F6F2EC`
