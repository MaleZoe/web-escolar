-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: web_escolar_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `contenido_institucional`
--

DROP TABLE IF EXISTS `contenido_institucional`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contenido_institucional` (
  `clave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contenido_institucional`
--

LOCK TABLES `contenido_institucional` WRITE;
/*!40000 ALTER TABLE `contenido_institucional` DISABLE KEYS */;
INSERT INTO `contenido_institucional` VALUES ('anuncios_subtitulo','Enterate al instante de los comunicados institucionales, actividades y fechas importantes publicadas en nuestro Instagram.'),('anuncios_titulo','Novedades e Instagram Oficial'),('behold_admin_link','https://behold.so'),('behold_feed_url','https://feeds.behold.so/kYahsnwH7WymWcYH98BW'),('contacto_email','infot5@eest5mdp.edu.ar'),('contacto_hero_tag','Contacto'),('contacto_hero_titulo','Escribinos o vení a conocer la escuela.'),('contacto_horarios','Horarios de atención de 7.30 a 22 hs'),('contacto_mapa_nota','Juan B. Justo 4287 · Mar del Plata'),('contacto_mapa_url','https://www.openstreetmap.org/export/embed.html?bbox=-57.585%2C-38.015%2C-57.555%2C-37.995&layer=mapnik'),('contacto_nota','Para inscripciones y documentación, consultar por correo electrónico antes de acercarse.'),('contacto_seccion_titulo','Datos institucionales'),('direccion','Av. Juan B. Justo 4287, Mar del Plata'),('escuela_auth_tag','Autoridades'),('escuela_auth_titulo','Equipo de gestión institucional'),('escuela_ciudad','Mar del Plata, Buenos Aires'),('escuela_dir_cargo','Director Institucional'),('escuela_dir_nombre','Héctor Scaglione'),('escuela_egresado_desc','El resultado es un egresado técnico con criterio propio, hábitos de trabajo y un oficio real, preparado tanto para continuar estudios superiores como para insertarse en el mundo del trabajo.'),('escuela_foto_caption','Fachada · Juan B. Justo 4287, Mar del Plata'),('escuela_foto_fachada','escuela.jpg'),('escuela_foto_taller','taller.jpg'),('escuela_gestion','Pública · estatal'),('escuela_hero_tag','La Escuela'),('escuela_hero_titulo','Una institución que enseña con las manos y con la cabeza.'),('escuela_jefe_tm','Gustavo Girardi'),('escuela_jefe_tn','Esteban Marinone'),('escuela_jefe_tt','Diego Álvarez'),('escuela_nivel','Secundaria técnica (7 años)'),('escuela_paso_1','Taller y laboratorio desde los primeros años'),('escuela_paso_2','Proyectos integradores por especialidad'),('escuela_paso_3','Trabajo en equipo, normas y seguridad'),('escuela_paso_4','Prácticas profesionalizantes en el último año'),('escuela_prosec','Mariana Palomar'),('escuela_qr_aic_url','institucionales.html?seccion=acuerdo-institucional'),('escuela_qr_ra_url','institucionales.html?seccion=regimen-academico'),('escuela_sec_tm','Julia Domecq'),('escuela_sec_tt','Mac Flores'),('escuela_taller_desc','El taller es el corazón de la escuela. Cada contenido teórico termina en una pieza, un plano, un programa o un circuito que funciona —o que hay que volver a corregir hasta que funcione.'),('escuela_taller_tag','Aprender haciendo'),('escuela_taller_titulo','Acá el conocimiento se prueba, se mide y se arma.'),('escuela_titulo','Técnico '),('escuela_vice_tm','Paula Iuzzolino'),('escuela_vice_tn','Lorena Cometti'),('escuela_vice_tt','Maximiliano Scarión'),('especialidades_tagline','Tres caminos técnicos. Un mismo modo de trabajar.'),('expotec_abierta','Familias, escuelas y comunidad'),('expotec_anuncio','ExpoTec 2026: Exposición anual de proyectos de Construcciones, Informática y Electrónica abierta a la comunidad.'),('expotec_desc','La ExpoTec es la exposición anual en la que las tres especialidades —Construcciones, Informática y Electrónica— muestran los proyectos desarrollados durante el ciclo lectivo. Las aulas y los talleres se abren a las familias, a otras escuelas y a la comunidad de Mar del Plata.'),('expotec_desc_secundaria','Cada equipo explica su proyecto: de dónde surgió la idea, qué problema resuelve, cómo se calculó, se dibujó, se programó o se montó, y qué aprendieron en el camino. No hay stand sin prototipo funcionando, plano terminado o demo en pantalla.'),('expotec_frecuencia','Una vez al año'),('expotec_participan','Las 3 especialidades'),('expotec_s1_desc','Maquetas de viviendas y obras, planos, cómputo de materiales y propuestas de intervención urbana.'),('expotec_s1_img','construcciones_nueva.jpg'),('expotec_s1_titulo','Construcciones'),('expotec_s2_desc','Aplicaciones web y de escritorio, videojuegos, redes, armado de equipos y proyectos de datos.'),('expotec_s2_img','informatica.jpg?v=2'),('expotec_s2_titulo','Informática'),('expotec_s3_desc','Placas, sistemas de control y automatización, sensores, robótica y proyectos con microcontroladores.'),('expotec_s3_img','sec_expotec_s3_img_1792959935.png'),('expotec_s3_titulo','Electrónica'),('expotec_sectores_tag','Proyectos en exposición'),('expotec_sectores_titulo','Qué se puede ver en cada sector.'),('expotec_sede','Juan B. Justo 4287'),('expotec_tag','ExpoTec'),('expotec_titulo','Una vez al año, la escuela se convierte en muestra.'),('facebook_url','https://www.facebook.com/EESTN5MDP'),('hero_foto','hero.jpg'),('hero_slogan','Aprender haciendo. Construir el futuro.'),('hero_subtitulo','Siete años de formación técnica en Construcciones, Informática y Electrónica, con taller desde el primer día.'),('hero_titulo','E.E.S.T. N.º 5 \"Amancio Williams\"'),('hitos_descripcion','Cada año deja múltiples proyectos construidos. Tocá cada hito para ver más información, imágenes y detalles del proyecto.'),('hitos_titulo','Una línea de tiempo hecha de proyectos.'),('instagram_handle','@tecnicacinco_mdq'),('instagram_url','https://www.instagram.com/tecnicacinco_mdq/'),('inst_hero_subtitulo','Reglamentaciones vigentes, pautas de convivencia escolar y marco legal que rigen la actividad académica y técnica de la E.E.S.T. N.º 5 “Amancio Williams”.'),('inst_hero_tag','Marco Institucional'),('inst_hero_titulo','Normativas Oficiales y Acuerdos de Convivencia'),('site_logo','logo.png'),('sobre_nosotros','Somos una escuela técnica pública de Mar del Plata. La formación combina la educación secundaria con talleres, laboratorios y prácticas profesionalizantes: los estudiantes miden, dibujan, programan, sueldan, calculan y construyen.');
/*!40000 ALTER TABLE `contenido_institucional` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `especialidades`
--

DROP TABLE IF EXISTS `especialidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `especialidades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `titulo` varchar(150) DEFAULT '',
  `icono` varchar(50) NOT NULL,
  `descripcion` text NOT NULL,
  `imagen` varchar(255) DEFAULT '',
  `color` varchar(50) DEFAULT 'esp--verde',
  `numero` varchar(10) DEFAULT '01',
  `duracion_anios` int(11) DEFAULT 7,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `especialidades`
--

LOCK TABLES `especialidades` WRITE;
/*!40000 ALTER TABLE `especialidades` DISABLE KEYS */;
INSERT INTO `especialidades` VALUES (1,'Construcciones','Maestro Mayor de Obras','hammer','Diseño, planificación, representación y ejecución de proyectos vinculados con la construcción y las obras civiles: dibujo técnico, materiales, estructuras, cómputo y dirección de obra.','especialidad_1792960238_695.png','esp--verde','01',7),(2,'Informática','Técnico en Informática Profesional y Personal','laptop','Tecnologías de la información, programación, sistemas, redes y desarrollo de soluciones informáticas, con laboratorio, hardware y proyectos de software aplicados.','especialidad_1792959435_436.png','esp--rojo','02',7),(3,'Electrónica','Técnico en Electrónica','hardware-chip','Diseño, análisis, montaje y aplicación de sistemas y dispositivos electrónicos: circuitos, instrumentación, medición, control y automatización.','electronica.jpg','esp--azul','03',7);
/*!40000 ALTER TABLE `especialidades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hitos_historicos`
--

DROP TABLE IF EXISTS `hitos_historicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hitos_historicos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anio` varchar(50) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text NOT NULL,
  `icono` varchar(50) DEFAULT 'flag',
  `destacado` tinyint(1) DEFAULT 0,
  `orden` int(11) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `resumen` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `destino` varchar(150) DEFAULT NULL,
  `premio` varchar(150) DEFAULT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hitos_historicos`
--

LOCK TABLES `hitos_historicos` WRITE;
/*!40000 ALTER TABLE `hitos_historicos` DISABLE KEYS */;
INSERT INTO `hitos_historicos` VALUES (1,'2018','FaciliArt · Impresión 3D solidaria para la salud','Estudiantes y docentes desarrollaron FaciliArt, un proyecto solidario utilizando impresoras 3D para la confección de prótesis de miembros superiores, férulas inmovilizadoras (reemplazo higiénico del yeso) y bio-réplicas anatómicas a partir de tomografías computadas. Las piezas fueron donadas al Hospital Materno Infantil (HIEMI) de Mar del Plata para permitir a los médicos planificar cirugías complejas. El proyecto fue distinguido en el certamen Docentes Innovadores 2018.','flag',0,1,'2026-10-22 23:14:31','Estudiantes y docentes desarrollaron FaciliArt, un proyecto solidario utilizando impresoras 3D para la confección de prótesis de miembros superiores, férulas inmovilizadoras y bio-réplicas anatómicas a partir de tomografías computadas.','faciliart.jpg','Electrónica y 3D','Hospital Materno Infantil','Docentes Innovadores 2018',''),(2,'2019','SatDuino · primer nano satélite escolar de Sudamérica','Alumnos y docentes del área de Electrónica de la escuela diseñaron, construyeron y lanzaron el SatDuino, el primer nano satélite escolar de Sudamérica. El dispositivo fue puesto en órbita a través de un globo estratosférico en la localidad bonaerense de Olavarría, alcanzando una altitud de 25.000 metros para medir variables climáticas, presión y radiación ultravioleta. Proyecto pionero de la escuela técnica en materia espacial.','flag',0,2,'2026-10-22 23:14:31','Desarrollo y lanzamiento del primer nano satélite escolar de Sudamérica, integrando conocimientos de electrónica, programación y comunicaciones.','satduino.jpg','Electrónica y Telecom.','Estratosfera (25.000 m)','Primer satélite escolar',''),(3,'2022','Picosatélite MDQube-SAT1 \"General San Martín\"','Un hito histórico para la educación técnica pública: nació en las aulas de la EEST N° 5 de la mano del profesor Alejandro Cordero y estudiantes de Electrónica, dando origen a la startup espacial marplatense Innova Space. El MDQube-SAT1 \"General San Martín\", de apenas 10x5x5 cm y 500 gramos, fue lanzado al espacio desde Cabo Cañaveral a bordo del cohete Falcon 9 de SpaceX, convirtiéndose en el satélite más pequeño desarrollado en América Latina.','flag',0,3,'2026-10-22 23:14:31','Diseño y desarrollo de un picosatélite educativo con el objetivo de fomentar la investigación tecnológica y el trabajo interdisciplinario en la escuela.','mdqube-sat1.png','Electrónica y Aeroespacial','Órbita Terrestre (SpaceX)','Lanzado desde Cabo Cañaveral',''),(4,'2022','\"Recordame\" · App con IA para personas con Alzheimer','Desarrollada por alumnos de 7° año de la especialidad Informática, Recordame es una app diseñada para personas con Alzheimer y deterioro cognitivo leve. Incorpora reconocimiento facial, recordatorios auditivos de medicación y rutinas, localización en tiempo real para cuidadores y un botón de auxilio inteligente. El proyecto fue presentado en ferias provinciales de innovación y galardonado por su impacto social y comunitario.','flag',0,4,'2026-10-22 23:14:31','Aplicación móvil que utiliza inteligencia artificial para ayudar a personas con Alzheimer a recordar rutinas, eventos y contactos importantes.','recordame.jpg','Informática Personal y Prof.','Adultos mayores y cuidadores','Impacto Social Mar del Plata',''),(5,'2023-2024','\"Tranqui\" · App de prevención y orientación','Creada íntegramente por alumnos de Informática, Tranqui es una aplicación orientada a adolescentes y jóvenes que busca dar herramientas de contención emocional, ejercicios de respiración, registro de emociones y líneas directas de asistencia gratuita ante situaciones de crisis. El desarrollo combinó diseño UI/UX, bases de datos y trabajo junto a profesionales de la salud mental.','flag',0,5,'2026-10-22 23:14:31','Desarrollo de una aplicación orientada a la prevención y orientación en temáticas de salud mental, con recursos, contactos y herramientas interactivas.','tranqui.png','Informática y Diseño UI/UX','Adolescentes y Comunidad','Mención Especial en Innovación',''),(6,'2025','Casco Iron Man y Asistente JARVIS','Proyecto interdisciplinario integral: modelado 3D, posprocesamiento e impresión, integración con servomotores, apertura motorizada, luces LED en la máscara y un asistente de voz JARVIS interactivo programado por estudiantes de Informática y Electrónica. Expuesto con gran éxito en la última edición de ExpoTec.','flag',0,6,'2026-10-22 23:14:31','Construcción del casco de Iron Man mediante modelado e impresión 3D, ensamblado y pintura, integrando hardware electrónico con servomotores y luces LED.','ironman.png','Robótica, 3D e Informática','ExpoTec y Divulgación','Proyecto Integral Destacado','https://dardansoft.com/projects/hardware/ironman');
/*!40000 ALTER TABLE `hitos_historicos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intentos_login`
--

DROP TABLE IF EXISTS `intentos_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `intentos_login` (
  `ip` varchar(45) NOT NULL,
  `intentos` int(11) NOT NULL DEFAULT 1,
  `ultimo_intento` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `bloqueado_hasta` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intentos_login`
--

LOCK TABLES `intentos_login` WRITE;
/*!40000 ALTER TABLE `intentos_login` DISABLE KEYS */;
/*!40000 ALTER TABLE `intentos_login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proyectos`
--

DROP TABLE IF EXISTS `proyectos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proyectos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  `subtitulo` varchar(255) DEFAULT NULL,
  `descripcion` text NOT NULL,
  `especialidad_id` int(11) NOT NULL,
  `anio` int(11) NOT NULL DEFAULT 2026,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` enum('En desarrollo','Finalizado','En ExpoTec') DEFAULT 'En desarrollo',
  `autor_id` int(11) NOT NULL,
  `destacado` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `especialidad_id` (`especialidad_id`),
  KEY `autor_id` (`autor_id`),
  CONSTRAINT `proyectos_ibfk_1` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `proyectos_ibfk_2` FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proyectos`
--

LOCK TABLES `proyectos` WRITE;
/*!40000 ALTER TABLE `proyectos` DISABLE KEYS */;
INSERT INTO `proyectos` VALUES (1,'Satduino & CubeSat EEST5','Pico-satélite de telemetría y sensores atmosféricos','Proyecto de pico-satélite educativo equipado con sensores ambientales y enlace de transmisión VHF/UHF desarrollado íntegramente por alumnos.',3,2026,'satduino.jpg','En ExpoTec',2,1,'2026-10-19 22:38:49'),(2,'MDQube-SAT1','Cansat escolar para competencias de la CONAE','Diseño e integración de carga útil en una lata de refresco para medir presión barométrica, temperatura y radiación UV en descenso.',3,2026,'mdqube-sat1.png','Finalizado',2,1,'2026-10-19 22:38:49'),(3,'Ironman & Biónica Escolar','Prótesis funcional e interactiva impresa en 3D','Brazo robótico articulado con control mediante señales mioeléctricas y actuadores servo de alta precisión.',2,2026,'ironman.png','En desarrollo',1,1,'2026-10-19 22:38:49'),(4,'FacilArt & Inclusión','Dispositivo de asistencia para personas con discapacidad motriz','Herramienta ergonómica y software interactivo para permitir expresión artística accesible en entornos de aula.',1,2026,'faciliart.jpg','Finalizado',2,0,'2026-10-19 22:38:49'),(5,'Recordame App','Aplicación de asistencia cognitiva para adultos mayores','Sistema móvil de recordatorios y estimulación cognitiva desarrollado por alumnos de informática.',2,2026,'recordame.jpg','En ExpoTec',3,0,'2026-10-19 22:38:49');
/*!40000 ALTER TABLE `proyectos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensajes_contacto`
--

DROP TABLE IF EXISTS `mensajes_contacto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mensajes_contacto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `asunto` varchar(200) DEFAULT 'Consulta general',
  `mensaje` text NOT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `refresh_tokens`
--

DROP TABLE IF EXISTS `refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refresh_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(500) NOT NULL,
  `expira_en` datetime NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `refresh_tokens_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refresh_tokens`
--

LOCK TABLES `refresh_tokens` WRITE;
/*!40000 ALTER TABLE `refresh_tokens` DISABLE KEYS */;
INSERT INTO `refresh_tokens` VALUES (1,1,'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwidXN1YXJpbyI6ImFkbWluIiwiZW1haWwiOiJhZG1pbkBlZXN0NS5lZHUuYXIiLCJub21icmVfY29tcGxldG8iOiJEaXJlY3RvciBHZW5lcmFsIEVFU1Q1Iiwicm9sIjoiQWRtaW5pc3RyYWRvciIsInJvbF9pZCI6MSwiYXZhdGFyX3VybCI6Imh0dHBzOi8vdWktYXZhdGFycy5jb20vYXBpLz9uYW1lPURpcmVjdG9yK0VFU1Q1JmJhY2tncm91bmQ9MzQ2NDAxJmNvbG9yPWZmZiIsImlhdCI6MTc5MjYyMDgyMywiZXhwIjoxNzkzMjI1NjIzfQ.hw3MfJ9-T1T4m7gXPOpom3RpFPB7YURZVE97hdJRyyk','2026-10-28 19:13:43','2026-10-21 22:13:43'),(6,1,'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwidXN1YXJpbyI6ImFkbWluIiwiZW1haWwiOiJhZG1pbkBlZXN0NS5lZHUuYXIiLCJub21icmVfY29tcGxldG8iOiJEaXJlY3RvciBHZW5lcmFsIEVFU1Q1Iiwicm9sIjoiQWRtaW5pc3RyYWRvciIsInJvbF9pZCI6MSwiYXZhdGFyX3VybCI6Imh0dHBzOi8vdWktYXZhdGFycy5jb20vYXBpLz9uYW1lPURpcmVjdG9yK0VFU1Q1JmJhY2tncm91bmQ9MzQ2NDAxJmNvbG9yPWZmZiIsImlhdCI6MTc5MjYyNTExOSwiZXhwIjoxNzkzMjI5OTE5fQ.4u69X6aXvxFXvdLDkxi04FEvHBVr5oHcassPJ1iGs5c','2026-10-28 20:25:19','2026-10-21 23:25:19'),(7,1,'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwidXN1YXJpbyI6ImFkbWluIiwiZW1haWwiOiJhZG1pbkBlZXN0NS5lZHUuYXIiLCJub21icmVfY29tcGxldG8iOiJEaXJlY3RvciBHZW5lcmFsIEVFU1Q1Iiwicm9sIjoiQWRtaW5pc3RyYWRvciIsInJvbF9pZCI6MSwiYXZhdGFyX3VybCI6Imh0dHBzOi8vdWktYXZhdGFycy5jb20vYXBpLz9uYW1lPURpcmVjdG9yK0VFU1Q1JmJhY2tncm91bmQ9MzQ2NDAxJmNvbG9yPWZmZiIsImlhdCI6MTc5MjY3Mjc1MywiZXhwIjoxNzkzMjc3NTUzfQ.IvYFhxSIiAqvSn9dIl5zoLqQ3rC6MVanSy_kMumlq30','2026-10-29 09:39:13','2026-10-22 12:39:13');
/*!40000 ALTER TABLE `refresh_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrador','Control total: gestión de usuarios, roles, proyectos y configuración institucional'),(2,'Docente','Gestión y supervisión de proyectos escolares y publicaciones técnicas'),(3,'Estudiante','Propuesta de proyectos escolares, participación en ExpoTec y consultas');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `rol_id` int(11) NOT NULL DEFAULT 3,
  `proveedor_auth` varchar(50) DEFAULT 'local',
  `avatar_url` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`),
  UNIQUE KEY `email` (`email`),
  KEY `rol_id` (`rol_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'gestor_sys98','acceso.seguro@gestion-interna.net','$2y$12$Nz/d8vd4s0NnWKSFbW8T7O1YoePcvuODAwkHC/9GCiarr5S0s19FS','Administrador de Gestión',1,'local','https://ui-avatars.com/api/?name=Director+EEST5&background=346401&color=fff','2026-10-19 22:38:49'),(2,'profesor.garcia','prof.garcia@eest5.edu.ar','$2a$10$wG2s31Wc/bKkX3a5oD.Jie3XQ6p0h4gXwVkW8vWfDqmTqVqX6h4i2','Prof. Roberto García',2,'local','https://ui-avatars.com/api/?name=Roberto+Garcia&background=111111&color=fff','2026-10-19 22:38:49'),(3,'alumno.martin','alumno.martin@eest5.edu.ar','$2a$10$wG2s31Wc/bKkX3a5oD.Jie3XQ6p0h4gXwVkW8vWfDqmTqVqX6h4i2','Martín Soler (Informática 7mo)',3,'local','https://ui-avatars.com/api/?name=Martin+Soler&background=FD0002&color=fff','2026-10-19 22:38:49'),(4,'alumnogoogle_goo','alumno.google@gmail.com','$2a$10$L3eUIPktJMS34Hjlcsojc.8Mqgt0HjdQt/N2BrvV3qKKeoibw1H.K','Estudiante Google',3,'google','https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150','2026-10-21 22:36:12'),(5,'alumnonuevo_goo','alumno.nuevo@gmail.com','$2a$10$HSUMxaCQrsR/X8BB..uFBeG.U6pmxJidRM/Z8vBRcUz44egvlmeWm','Estudiante Google',3,'google','https://ui-avatars.com/api/?name=Estudiante%20Google&background=346401&color=fff','2026-10-21 23:23:00');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-25 18:59:12
