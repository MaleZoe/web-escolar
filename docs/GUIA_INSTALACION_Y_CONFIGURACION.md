# 🛠️ Guía de Instalación y Configuración del Entorno de Desarrollo
## Sitio Web Institucional y Panel de Administración - E.E.S.T. N.º 5

Esta guía contiene las instrucciones técnicas para poner en marcha el proyecto tanto en un entorno local de desarrollo (Windows, Linux o macOS) como en plataformas de despliegue en la nube.

---

## 📋 Requisitos Previos del Sistema

Antes de comenzar, asegúrate de contar con las siguientes herramientas instaladas:

1. **Servidor Web y PHP:**
   - **PHP:** Versión `8.0` o superior (se recomienda PHP 8.1 o 8.2).
   - **Extensiones de PHP requeridas:**
     - `pdo` y `pdo_mysql` (comunicación con la base de datos).
     - `mbstring` (manipulación segura de cadenas UTF-8).
     - `openssl` (generación de tokens criptográficos CSRF y sesiones).
     - `curl` (solicitudes HTTPS y certificados SSL).
     - `session` (manejo de sesiones de usuario).
2. **Motor de Base de Datos:**
   - **MySQL 5.7+** o **MariaDB 10.4+**.
3. **Servidor Web Local (Recomendado para Windows):**
   - **XAMPP** (con Apache y MySQL) o **Laragon**.
4. **Navegador Web:** Google Chrome, Mozilla Firefox, Microsoft Edge o Safari (versiones actualizadas).
5. **Git (Opcional):** Para clonar y versionar el proyecto.

---

## 🏗️ Arquitectura del Proyecto

```text
web-escolar/
├── index.html                   # Portada / Home pública
├── pages/                       # Páginas internas del sitio público
│   ├── contacto.html            # Formulario de contacto y mapa
│   ├── escuela.html             # Historia, directivos y talleres
│   ├── expotec.html             # Exposición anual de proyectos
│   └── institucionales.html     # Normativas oficiales (AIC / RA)
├── css/
│   └── styles.css               # Estilos globales y responsive
├── js/
│   ├── script.js                # Lógica de navegación, menús y modales
│   └── dynamic_content.js       # Sincronización dinámica de datos con la API
├── api/                         # Endpoints REST públicos en PHP
│   ├── db.php                   # Conexión PDO a MySQL (con soporte SSL)
│   ├── get_public_data.php      # Suministra los contenidos a la web pública
│   ├── contact.php              # Procesa el formulario de contacto con anti-spam
│   ├── manage.php               # Rutas de administración
│   └── image.php                # Servidor y visor de imágenes
├── sys-control-98/              # Backoffice / Panel de control administrativo
│   ├── config.php               # Seguridad, sesiones en BD y validación CSRF
│   ├── login.php                # Autenticación con control de fuerza bruta
│   ├── logout.php               # Cierre de sesión seguro
│   ├── index.php                # Interfaz principal de gestión de contenidos
│   ├── css/                     # Estilos exclusivos del panel
│   └── js/                      # Lógica interactiva del panel
├── img/                         # Galería de imágenes y uploads de contenidos
├── database.sql                 # Estructura y datos iniciales de la base de datos
├── .env.example                 # Plantilla de variables de entorno
└── vercel.json                  # Configuración para despliegue serverless
```

---

## 🚀 Paso 1: Clonar o Ubicar el Proyecto

### Si usas XAMPP (Windows):
Copia o clona el repositorio dentro de la carpeta `htdocs`:
```bash
# Ruta típica: C:\xampp\htdocs\web-escolar
cd C:\xampp\htdocs
git clone https://github.com/tu-usuario/web-escolar.git
```

### Si usas Laragon:
Copia el proyecto en `C:\laragon\www\web-escolar`.

---

## 🗄️ Paso 2: Configuración de la Base de Datos

1. Abre el panel de control de **XAMPP** o **Laragon** e inicia los servicios de **Apache** y **MySQL**.
2. Ingresa a **phpMyAdmin** desde tu navegador:
   ```text
   http://localhost/phpmyadmin
   ```
3. Crea una nueva base de datos:
   - **Nombre:** `web_escolar_db`
   - **Cotejamiento (Collation):** `utf8mb4_general_ci` o `utf8mb4_unicode_ci`.
4. Ve a la pestaña **Importar**:
   - Selecciona el archivo `database.sql` ubicado en la raíz del proyecto.
   - Presiona **Importar** / **Continuar**.
   - Esto creará todas las tablas (`contenido_institucional`, `especialidades`, `hitos_historicos`, `proyectos`, `usuarios`, `roles`, `mensajes_contacto`, `intentos_login`, etc.) y cargará la información institucional predefinida.

### Método alternativo por Consola (CLI):
```bash
mysql -u root -p -e "CREATE DATABASE web_escolar_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
mysql -u root -p web_escolar_db < database.sql
```

---

> [!WARNING]
> `database.sql` contiene `DROP TABLE`: úsalo solo en una instalación nueva. En una base con datos reales ejecuta los archivos de `migrations/`, que no borran nada.

## ⚙️ Paso 3: Configuración de Variables de Entorno

El sistema incluye valores predeterminados seguros para entornos locales estándar de XAMPP / Laragon:
- **Host:** `localhost`
- **Usuario:** `root`
- **Contraseña:** *(vacía por defecto en XAMPP)*
- **Nombre BD:** `web_escolar_db`
- **Puerto:** `3306`

Si tu entorno local requiere credenciales diferentes, crea un archivo `.env` en la raíz copiando `.env.example`:

```ini
# .env
DB_HOST=localhost
DB_USER=root
DB_PASS=tu_password_aqui
DB_NAME=web_escolar_db
DB_PORT=3306
```

> [!NOTE]
> Tanto `api/db.php` como `sys-control-98/config.php` verifican automáticamente las variables del entorno mediante `getenv()`. Si no están definidas, toman los valores de fallback locales (`localhost`, `root`, ``, `web_escolar_db`).

---

## 🌐 Paso 4: Ejecución y Pruebas en el Entorno Local

### Método A: A través de Apache (XAMPP / Laragon)
1. Con Apache encendido, abre el navegador en:
   - **Portal Público:**
     ```text
     http://localhost/web-escolar/
     ```
   - **Panel Administrativo:**
     ```text
     http://localhost/web-escolar/sys-control-98/login.php
     ```

### Método B: Servidor Integrado de PHP (Sin Apache)
Si no deseas usar XAMPP y ya tienes PHP y MySQL instalados:
1. Abre una terminal en la raíz de `web-escolar`:
   ```bash
   php -S localhost:8000
   ```
2. Accede a:
   - **Portal Público:** `http://localhost:8000/`
   - **Panel Administrativo:** `http://localhost:8000/sys-control-98/login.php`

---

## 🔑 Paso 5: Credenciales de Acceso Iniciales

El archivo `database.sql` incluye un usuario administrador listo para operar:

- **Usuario:** `gestor_sys98`  *(o correo: `acceso.seguro@gestion-interna.net`)*
- **Rol:** `Administrador (rol_id: 1)`

### ¿Cómo generar o restablecer una contraseña?
Las contraseñas se almacenan mediante el algoritmo `password_hash()` de PHP con algoritmo BCRYPT.
Si deseas generar una nueva clave manualmente o crear un usuario administrador personalizado:

1. Ejecuta este comando en tu terminal para obtener el hash de tu contraseña deseada:
   ```bash
   php -r "echo password_hash('MiClaveSegura2026', PASSWORD_BCRYPT);"
   ```
   *(Ejemplo de salida: `$2y$10$abcdef...`)*

2. Actualiza la tabla `usuarios` en MySQL:
   ```sql
   UPDATE usuarios 
   SET password = 'AQUI_TU_HASH_GENERADO' 
   WHERE usuario = 'gestor_sys98';
   ```

Una vez que inicies sesión en el panel, podrás cambiarla cómodamente desde la pestaña **"Seguridad & Acceso"**.

---

## ☁️ Paso 6: Despliegue en la Nube (Vercel + TiDB / Railway / Render)

El proyecto está preparado para ejecutarse en entornos serverless modernos como **Vercel**:

1. **Base de Datos en la Nube:**
   - Puedes usar servicios MySQL gratuitos en la nube como **TiDB Cloud Serverless**, **Aiven MySQL** o **Railway**.
   - Importa `database.sql` en dicha base de datos remota.
2. **Variables de Entorno en Vercel:**
   - En el panel de Vercel (Project Settings > Environment Variables), agrega:
     - `DB_HOST`: Host proporcionado por el proveedor (ej. `gateway01...tidbcloud.com`)
     - `DB_USER`: Usuario de la base remota
     - `DB_PASS`: Contraseña de la base remota
     - `DB_NAME`: `web_escolar_db`
     - `DB_PORT`: Puerto de conexión (habitualmente `4000` o `3306`)
3. **Certificados SSL:**
   - El código en `api/db.php` y `sys-control-98/config.php` detecta automáticamente conexiones remotas y activa el soporte de certificados CA SSL sin requerir configuración adicional.
4. **Despliegue:**
   - Conecta el repositorio de GitHub a Vercel. El archivo `vercel.json` ya se encuentra configurado para enrutar los scripts PHP y servir el frontend estático.

---

## 🔁 Ruta rotativa del panel (Vercel)

Con la variable `ADMIN_ALERT_EMAIL`, cada inicio de sesión cambia la ruta del panel a `/sys-control-<token>/` y envía el enlace nuevo por FormSubmit. Hay que activar la casilla una vez, con el correo de confirmación de FormSubmit.

- Si el correo falla, la ruta no cambia y el panel muestra un aviso.
- Si se pierde el enlace, ejecutá `DELETE FROM panel_acceso;` en la base: vuelve la ruta `/sys-control-98/`.

---

## 🩺 Solución de Problemas Frecuentes (Troubleshooting)

### 1. "Error seguro de conexión a la base de datos"
- **Causa:** MySQL no está iniciado o las credenciales no coinciden.
- **Solución:** Comprueba en el panel de XAMPP que el botón de MySQL esté en verde. Verifica en `.env` o en `api/db.php` que el usuario sea `root` y la base de datos se llame `web_escolar_db`.

### 2. "Acceso bloqueado por reiterados intentos fallidos"
- **Causa:** Se ingresó una contraseña errónea 5 veces consecutivas y el módulo anti fuerza bruta bloqueó la IP por 15 minutos.
- **Solución rápida en desarrollo:** Entra a phpMyAdmin y vacía la tabla de intentos:
  ```sql
  TRUNCATE TABLE intentos_login;
  ```

### 3. Las imágenes subidas no se guardan en el servidor
- **Causa:** Falta de permisos de escritura en el directorio `img/`.
- **Solución (Linux / Mac):**
  ```bash
  chmod -R 775 img/
  ```
  En Windows, asegúrate de que la carpeta no esté marcada como "Solo lectura".

### 4. El formulario de contacto no envía mensajes
- **Causa:** La tabla `mensajes_contacto` no existe o no tiene permisos de inserción.
- **Solución:** Verifica en phpMyAdmin que la tabla `mensajes_contacto` exista. El script la creará automáticamente si la base de datos tiene permisos de `CREATE TABLE`.

---

¡Listo! Ya tienes el entorno de desarrollo y ejecución completamente configurado y funcional.
