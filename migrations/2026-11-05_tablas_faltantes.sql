-- MIGRACIÓN: crea las tablas que falten, sin borrar datos.

-- Migración segura para bases existentes (producción incluida).
-- Solo crea lo que falta: no usa DROP, no borra filas y se puede ejecutar más de una vez.
-- Nunca importes database.sql sobre una base con datos reales: contiene DROP TABLE.

CREATE TABLE IF NOT EXISTS `sesiones_php` (
  `id` varchar(128) NOT NULL,
  `datos` mediumtext NOT NULL,
  `ultimo_acceso` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ultimo_acceso` (`ultimo_acceso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `archivos_subidos` (
  `nombre` varchar(255) NOT NULL,
  `mime` varchar(100) NOT NULL,
  `datos` mediumblob NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `rate_limit_contacto` (
  `ip` varchar(45) NOT NULL,
  `intentos` int(11) NOT NULL DEFAULT 1,
  `primera_peticion` int(11) NOT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `intentos_login` (
  `ip` varchar(45) NOT NULL,
  `intentos` int(11) NOT NULL DEFAULT 1,
  `ultimo_intento` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `bloqueado_hasta` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mensajes_contacto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `asunto` varchar(200) DEFAULT 'Consulta general',
  `mensaje` text NOT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
