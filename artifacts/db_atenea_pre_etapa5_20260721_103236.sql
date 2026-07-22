-- Copia SQL de Atenea
-- Fecha: 2026-07-21 10:32:35 (America/El_Salvador)
-- Base de datos: db_atenea
-- Tipo de exportación: Base de datos completa: estructura e información
-- Generado desde PHP sin incluir archivos del servidor ni variables de entorno.

SET NAMES utf8mb4;
SET @OLD_SQL_MODE=@@SQL_MODE;
SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS=0;

CREATE DATABASE IF NOT EXISTS `db_atenea` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_atenea`;

DROP TABLE IF EXISTS `comunicacion_mensajes`;
DROP TABLE IF EXISTS `admin_notices`;
DROP TABLE IF EXISTS `pedido_historial`;
DROP TABLE IF EXISTS `pedido_detalles`;
DROP TABLE IF EXISTS `pagos`;
DROP TABLE IF EXISTS `menu_formulario_envios`;
DROP TABLE IF EXISTS `inventario_movimientos`;
DROP TABLE IF EXISTS `errores_sistema`;
DROP TABLE IF EXISTS `dte_eventos`;
DROP TABLE IF EXISTS `dte_documentos`;
DROP TABLE IF EXISTS `correo_envios`;
DROP TABLE IF EXISTS `correo_centro_adjuntos`;
DROP TABLE IF EXISTS `comunicacion_hilos`;
DROP TABLE IF EXISTS `chat_lecturas`;
DROP TABLE IF EXISTS `chat_adjuntos`;
DROP TABLE IF EXISTS `certificados_capacitacion`;
DROP TABLE IF EXISTS `carrito_items`;
DROP TABLE IF EXISTS `user_deletions`;
DROP TABLE IF EXISTS `respaldos_base_datos`;
DROP TABLE IF EXISTS `promociones`;
DROP TABLE IF EXISTS `progreso_contenido`;
DROP TABLE IF EXISTS `producto_imagenes`;
DROP TABLE IF EXISTS `productos`;
DROP TABLE IF EXISTS `personalizaciones_visuales_historial`;
DROP TABLE IF EXISTS `personalizaciones_visuales`;
DROP TABLE IF EXISTS `pedidos`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `notificacion_preferencias`;
DROP TABLE IF EXISTS `noticias`;
DROP TABLE IF EXISTS `notas_historial`;
DROP TABLE IF EXISTS `notas`;
DROP TABLE IF EXISTS `menu_sitio`;
DROP TABLE IF EXISTS `inscripcion_movimientos`;
DROP TABLE IF EXISTS `inscripciones_capacitacion`;
DROP TABLE IF EXISTS `historial_cambios_cuenta`;
DROP TABLE IF EXISTS `ev_entregadas`;
DROP TABLE IF EXISTS `evaluaciones`;
DROP TABLE IF EXISTS `estudiantes_docentes`;
DROP TABLE IF EXISTS `entrega_revisiones`;
DROP TABLE IF EXISTS `entrega_evidencias`;
DROP TABLE IF EXISTS `entregas_contenido`;
DROP TABLE IF EXISTS `dte_configuracion`;
DROP TABLE IF EXISTS `docentes_asignaturas`;
DROP TABLE IF EXISTS `direcciones_usuario`;
DROP TABLE IF EXISTS `correo_centro_mensajes`;
DROP TABLE IF EXISTS `correo_centro_hilos`;
DROP TABLE IF EXISTS `contenidos`;
DROP TABLE IF EXISTS `chat_reportes`;
DROP TABLE IF EXISTS `chat_participantes`;
DROP TABLE IF EXISTS `chat_mensajes`;
DROP TABLE IF EXISTS `chat_conversaciones`;
DROP TABLE IF EXISTS `chat_bloqueos`;
DROP TABLE IF EXISTS `categorias_producto`;
DROP TABLE IF EXISTS `carritos`;
DROP TABLE IF EXISTS `capacitacion_seccion_historial`;
DROP TABLE IF EXISTS `capacitacion_secciones`;
DROP TABLE IF EXISTS `capacitacion_pagos`;
DROP TABLE IF EXISTS `auth_remember_tokens`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `assisted_password_resets`;
DROP TABLE IF EXISTS `asignaturas`;
DROP TABLE IF EXISTS `account_cleanup_notifications`;
DROP TABLE IF EXISTS `website_versiones`;
DROP TABLE IF EXISTS `website_publicaciones`;
DROP TABLE IF EXISTS `website_preview_tokens`;
DROP TABLE IF EXISTS `verificaciones_cuenta`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `elementos_seccion`;
DROP TABLE IF EXISTS `distritos`;
DROP TABLE IF EXISTS `stripe_eventos`;
DROP TABLE IF EXISTS `secciones`;
DROP TABLE IF EXISTS `respaldo_index_secciones_20260717`;
DROP TABLE IF EXISTS `respaldo_index_elementos_20260717`;
DROP TABLE IF EXISTS `respaldo_index_configuracion_20260717`;
DROP TABLE IF EXISTS `municipios`;
DROP TABLE IF EXISTS `dte_correlativos`;
DROP TABLE IF EXISTS `departamentos`;
DROP TABLE IF EXISTS `correo_imap_estado`;
DROP TABLE IF EXISTS `configuracion_sitio`;
DROP TABLE IF EXISTS `configuracion_portal_estudiante`;

-- Estructura de configuracion_portal_estudiante
CREATE TABLE `configuracion_portal_estudiante` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `tipo` enum('texto','imagen') NOT NULL DEFAULT 'texto',
  `grupo` enum('login','registro','panel','general') NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de configuracion_sitio
CREATE TABLE `configuracion_sitio` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `tipo` enum('texto','email','telefono','url','imagen') NOT NULL DEFAULT 'texto',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de correo_imap_estado
CREATE TABLE `correo_imap_estado` (
  `carpeta` varchar(120) NOT NULL,
  `ultimo_uid` bigint(20) unsigned NOT NULL DEFAULT 0,
  `ultima_sincronizacion_at` datetime DEFAULT NULL,
  `ultimo_error` varchar(500) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`carpeta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de departamentos
CREATE TABLE `departamentos` (
  `id` smallint(5) unsigned NOT NULL,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de dte_correlativos
CREATE TABLE `dte_correlativos` (
  `tipo_dte` char(2) NOT NULL,
  `establecimiento` char(4) NOT NULL,
  `punto_venta` char(4) NOT NULL,
  `ultimo` bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`tipo_dte`,`establecimiento`,`punto_venta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de municipios
CREATE TABLE `municipios` (
  `id` smallint(5) unsigned NOT NULL,
  `departamento_id` smallint(5) unsigned NOT NULL,
  `nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_municipio_departamento_nombre` (`departamento_id`,`nombre`),
  CONSTRAINT `fk_municipios_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de respaldo_index_configuracion_20260717
CREATE TABLE `respaldo_index_configuracion_20260717` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `tipo` enum('texto','email','telefono','url','imagen') NOT NULL DEFAULT 'texto',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de respaldo_index_elementos_20260717
CREATE TABLE `respaldo_index_elementos_20260717` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `seccion_id` int(10) unsigned NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `subtitulo` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `enlace` varchar(500) DEFAULT NULL,
  `texto_boton` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_elementos_seccion_activo_orden` (`seccion_id`,`activo`,`orden`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de respaldo_index_secciones_20260717
CREATE TABLE `respaldo_index_secciones_20260717` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `subtitulo` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `boton_texto` varchar(100) DEFAULT NULL,
  `boton_url` varchar(500) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`),
  KEY `idx_secciones_activo_orden` (`activo`,`orden`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de secciones
CREATE TABLE `secciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `subtitulo` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `boton_texto` varchar(100) DEFAULT NULL,
  `boton_url` varchar(500) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`),
  KEY `idx_secciones_activo_orden` (`activo`,`orden`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de stripe_eventos
CREATE TABLE `stripe_eventos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stripe_event_id` varchar(255) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `procesado` tinyint(1) NOT NULL DEFAULT 0,
  `error_mensaje` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `procesado_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stripe_event_id` (`stripe_event_id`),
  KEY `idx_stripe_evento_estado` (`procesado`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=236 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de distritos
CREATE TABLE `distritos` (
  `id` smallint(5) unsigned NOT NULL,
  `municipio_id` smallint(5) unsigned NOT NULL,
  `nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_distrito_municipio_nombre` (`municipio_id`,`nombre`),
  CONSTRAINT `fk_distritos_municipio` FOREIGN KEY (`municipio_id`) REFERENCES `municipios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de elementos_seccion
CREATE TABLE `elementos_seccion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `seccion_id` int(10) unsigned NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `subtitulo` varchar(255) DEFAULT NULL,
  `tipo` varchar(80) DEFAULT NULL,
  `nivel` varchar(80) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `duracion` varchar(120) DEFAULT NULL,
  `instructor` varchar(180) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `enlace` varchar(500) DEFAULT NULL,
  `texto_boton` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_elementos_seccion_activo_orden` (`seccion_id`,`activo`,`orden`),
  CONSTRAINT `fk_elementos_seccion` FOREIGN KEY (`seccion_id`) REFERENCES `secciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de usuarios
CREATE TABLE `usuarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `nombre_usuario` varchar(80) DEFAULT NULL,
  `correo` varchar(190) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `proveedor` enum('local','google','mixto') NOT NULL DEFAULT 'local',
  `email_verificado` tinyint(1) NOT NULL DEFAULT 0,
  `perfil_estado` enum('completo','pendiente') NOT NULL DEFAULT 'completo',
  `terminos_aceptados_at` datetime DEFAULT NULL,
  `google_registro_iniciado_at` datetime DEFAULT NULL,
  `rol` enum('admin','usuario','docente') NOT NULL DEFAULT 'usuario',
  `es_superadmin` tinyint(1) NOT NULL DEFAULT 0,
  `foto` varchar(500) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `dui` char(10) DEFAULT NULL,
  `codigo_telefono` varchar(5) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `departamento_id` smallint(5) unsigned DEFAULT NULL,
  `municipio_id` smallint(5) unsigned DEFAULT NULL,
  `distrito_id` smallint(5) unsigned DEFAULT NULL,
  `direccion` varchar(500) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `ultimo_acceso` datetime DEFAULT NULL,
  `last_activity_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  `deletion_reason` varchar(500) DEFAULT NULL,
  `deletion_scheduled_at` datetime DEFAULT NULL,
  `anonymized_at` datetime DEFAULT NULL,
  `retention_hold` tinyint(1) NOT NULL DEFAULT 0,
  `under_investigation` tinyint(1) NOT NULL DEFAULT 0,
  `session_version` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`),
  UNIQUE KEY `uq_usuarios_google_id` (`google_id`),
  UNIQUE KEY `uq_usuarios_dui` (`dui`),
  UNIQUE KEY `uq_usuarios_nombre_usuario` (`nombre_usuario`),
  KEY `idx_usuarios_rol_estado` (`rol`,`estado`),
  KEY `fk_usuarios_departamento` (`departamento_id`),
  KEY `fk_usuarios_municipio` (`municipio_id`),
  KEY `fk_usuarios_distrito` (`distrito_id`),
  KEY `idx_usuarios_ciclo_vida` (`estado`,`deleted_at`,`deletion_scheduled_at`),
  KEY `idx_usuarios_inactividad` (`rol`,`last_activity_at`,`ultimo_acceso`),
  KEY `fk_usuario_deleted_by` (`deleted_by`),
  KEY `idx_usuarios_busqueda_nombre` (`nombre`,`apellido`),
  CONSTRAINT `fk_usuario_deleted_by` FOREIGN KEY (`deleted_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_usuarios_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`),
  CONSTRAINT `fk_usuarios_distrito` FOREIGN KEY (`distrito_id`) REFERENCES `distritos` (`id`),
  CONSTRAINT `fk_usuarios_municipio` FOREIGN KEY (`municipio_id`) REFERENCES `municipios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=614 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de verificaciones_cuenta
CREATE TABLE `verificaciones_cuenta` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `tipo` enum('cambio_password','cambio_correo','vincular_google','desvincular_google','eliminar_cuenta') NOT NULL,
  `codigo_hash` char(64) NOT NULL,
  `datos_pendientes` longtext NOT NULL,
  `intentos` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `max_intentos` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `expira_at` datetime NOT NULL,
  `usado_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_verificacion_usuario_tipo` (`usuario_id`,`tipo`,`usado_at`,`expira_at`),
  CONSTRAINT `fk_verificacion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de website_preview_tokens
CREATE TABLE `website_preview_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `token_hash` char(64) NOT NULL,
  `administrador_id` int(10) unsigned NOT NULL,
  `session_hash` char(64) NOT NULL,
  `expira_at` datetime NOT NULL,
  `ultimo_uso_at` datetime DEFAULT NULL,
  `revocado_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_website_preview_token` (`token_hash`),
  KEY `idx_website_preview_expira` (`expira_at`,`revocado_at`),
  KEY `fk_website_preview_admin` (`administrador_id`),
  CONSTRAINT `fk_website_preview_admin` FOREIGN KEY (`administrador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de website_publicaciones
CREATE TABLE `website_publicaciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `estado` enum('borrador','publicado','archivado') NOT NULL,
  `contenido_json` longtext NOT NULL,
  `comentario` varchar(500) DEFAULT NULL,
  `creado_por` int(10) unsigned NOT NULL,
  `publicado_por` int(10) unsigned DEFAULT NULL,
  `publicado_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_website_publicacion_estado` (`estado`,`updated_at`),
  KEY `fk_website_publicacion_creador` (`creado_por`),
  KEY `fk_website_publicacion_publicador` (`publicado_por`),
  CONSTRAINT `fk_website_publicacion_creador` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_website_publicacion_publicador` FOREIGN KEY (`publicado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_website_publicacion_json` CHECK (json_valid(`contenido_json`))
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de website_versiones
CREATE TABLE `website_versiones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `administrador_id` int(10) unsigned NOT NULL,
  `seccion_modificada` varchar(120) NOT NULL,
  `datos_anteriores` longtext NOT NULL,
  `datos_nuevos` longtext NOT NULL,
  `estado` enum('borrador','publicado','archivado','restaurado','descartado') NOT NULL,
  `comentario` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_website_version_fecha` (`created_at`),
  KEY `idx_website_version_seccion` (`seccion_modificada`,`created_at`),
  KEY `idx_website_version_admin` (`administrador_id`,`created_at`),
  CONSTRAINT `fk_website_version_admin` FOREIGN KEY (`administrador_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `chk_website_version_anterior` CHECK (json_valid(`datos_anteriores`)),
  CONSTRAINT `chk_website_version_nuevo` CHECK (json_valid(`datos_nuevos`))
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de account_cleanup_notifications
CREATE TABLE `account_cleanup_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `notice_days` smallint(5) unsigned NOT NULL,
  `cycle_key` date NOT NULL,
  `inactivity_reference_at` datetime NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pendiente',
  `email_sent_at` datetime DEFAULT NULL,
  `error_sanitized` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cleanup_notice` (`user_id`,`notice_days`,`cycle_key`),
  KEY `idx_cleanup_state` (`status`,`created_at`),
  CONSTRAINT `fk_cleanup_user` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de asignaturas
CREATE TABLE `asignaturas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `elemento_seccion_id` int(10) unsigned DEFAULT NULL,
  `codigo` varchar(40) NOT NULL,
  `nombre` varchar(180) NOT NULL,
  `slug` varchar(190) DEFAULT NULL,
  `descripcion_corta` varchar(500) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `descripcion_completa` mediumtext DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `tipo` enum('curso','capacitacion','certificacion') NOT NULL DEFAULT 'capacitacion',
  `nivel` varchar(80) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `duracion` varchar(120) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_finalizacion` date DEFAULT NULL,
  `estado_capacitacion` enum('borrador','publicada','cerrada','archivada') NOT NULL DEFAULT 'borrador',
  `cupo_seccion` tinyint(3) unsigned NOT NULL DEFAULT 20,
  `asignacion_automatica` tinyint(1) NOT NULL DEFAULT 1,
  `requisitos` text DEFAULT NULL,
  `objetivos` text DEFAULT NULL,
  `modalidad` enum('presencial','virtual','hibrida') NOT NULL DEFAULT 'presencial',
  `certificado_disponible` tinyint(1) NOT NULL DEFAULT 0,
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` datetime DEFAULT NULL,
  `eliminado_por` int(10) unsigned DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `creado_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_asignatura_codigo` (`codigo`),
  UNIQUE KEY `uq_asignatura_slug` (`slug`),
  UNIQUE KEY `uq_asignatura_elemento` (`elemento_seccion_id`),
  KEY `idx_asignatura_estado_nombre` (`estado`,`nombre`),
  KEY `fk_asignatura_admin` (`creado_por`),
  KEY `idx_asignatura_publica` (`estado_capacitacion`,`activo`,`deleted_at`,`orden`),
  KEY `fk_asignatura_elemento` (`elemento_seccion_id`),
  KEY `fk_asignatura_eliminado_por` (`eliminado_por`),
  CONSTRAINT `fk_asignatura_admin` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_asignatura_elemento` FOREIGN KEY (`elemento_seccion_id`) REFERENCES `elementos_seccion` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_asignatura_eliminado_por` FOREIGN KEY (`eliminado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_asignatura_cupo` CHECK (`cupo_seccion` between 1 and 30),
  CONSTRAINT `chk_asignatura_precio` CHECK (`precio` >= 0),
  CONSTRAINT `chk_asignatura_fechas` CHECK (`fecha_finalizacion` is null or `fecha_inicio` is null or `fecha_finalizacion` >= `fecha_inicio`)
) ENGINE=InnoDB AUTO_INCREMENT=225 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de assisted_password_resets
CREATE TABLE `assisted_password_resets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `initiated_by` int(10) unsigned NOT NULL,
  `verified_by` int(10) unsigned DEFAULT NULL,
  `email_hash` char(64) NOT NULL,
  `code_hash` char(64) NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `max_attempts` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `expires_at` datetime NOT NULL,
  `locked_until` datetime DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `recovery_token_hash` char(64) DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `token_used_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_assisted_token` (`recovery_token_hash`),
  KEY `idx_assisted_user_state` (`user_id`,`cancelled_at`,`expires_at`),
  KEY `idx_assisted_admin` (`initiated_by`,`created_at`),
  KEY `fk_assisted_verifier` (`verified_by`),
  CONSTRAINT `fk_assisted_initiator` FOREIGN KEY (`initiated_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_assisted_user` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_assisted_verifier` FOREIGN KEY (`verified_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de audit_logs
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `actor_user_id` int(10) unsigned DEFAULT NULL,
  `target_user_id` int(10) unsigned DEFAULT NULL,
  `event_type` varchar(100) NOT NULL,
  `module` varchar(80) NOT NULL,
  `entity_type` varchar(80) DEFAULT NULL,
  `entity_id` varchar(100) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `result` varchar(30) NOT NULL,
  `description` varchar(500) NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `request_id` char(32) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_fecha` (`created_at`),
  KEY `idx_audit_actor_fecha` (`actor_user_id`,`created_at`),
  KEY `idx_audit_target_fecha` (`target_user_id`,`created_at`),
  KEY `idx_audit_filtros` (`event_type`,`module`,`result`,`created_at`),
  CONSTRAINT `fk_audit_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_audit_target` FOREIGN KEY (`target_user_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=440 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de auth_remember_tokens
CREATE TABLE `auth_remember_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `selector` char(24) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `session_version` int(10) unsigned NOT NULL,
  `user_agent_hash` char(64) NOT NULL,
  `ip_hash` char(64) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_auth_remember_selector` (`selector`),
  KEY `idx_auth_remember_usuario` (`usuario_id`,`revoked_at`,`expires_at`),
  CONSTRAINT `fk_auth_remember_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de capacitacion_pagos
CREATE TABLE `capacitacion_pagos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `asignatura_id` int(10) unsigned NOT NULL,
  `checkout_key` char(64) NOT NULL,
  `stripe_checkout_session_id` varchar(255) DEFAULT NULL,
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `importe` decimal(10,2) NOT NULL,
  `moneda` char(3) NOT NULL DEFAULT 'usd',
  `estado` enum('pendiente','pagado','fallido','expirado','reembolsado') NOT NULL DEFAULT 'pendiente',
  `last_stripe_event_id` varchar(255) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cap_pago_checkout_key` (`checkout_key`),
  UNIQUE KEY `uq_cap_pago_session` (`stripe_checkout_session_id`),
  UNIQUE KEY `uq_cap_pago_intent` (`stripe_payment_intent_id`),
  KEY `idx_cap_pago_usuario` (`usuario_id`,`created_at`),
  KEY `idx_cap_pago_asignatura` (`asignatura_id`,`estado`),
  KEY `idx_cap_pago_evento` (`last_stripe_event_id`),
  CONSTRAINT `fk_cap_pago_asignatura` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`),
  CONSTRAINT `fk_cap_pago_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `chk_cap_pago_importe` CHECK (`importe` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=254 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de capacitacion_secciones
CREATE TABLE `capacitacion_secciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asignatura_id` int(10) unsigned NOT NULL,
  `docente_id` int(10) unsigned NOT NULL,
  `codigo` varchar(80) NOT NULL,
  `nombre` varchar(180) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_finalizacion` date DEFAULT NULL,
  `capacidad_maxima` tinyint(3) unsigned NOT NULL DEFAULT 20,
  `cantidad_actual` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `estado` enum('abierta','cerrada','finalizada','inactiva') NOT NULL DEFAULT 'abierta',
  `horario` varchar(255) DEFAULT NULL,
  `creada_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cap_seccion_codigo` (`codigo`),
  KEY `idx_cap_seccion_disponible` (`asignatura_id`,`estado`,`cantidad_actual`,`capacidad_maxima`),
  KEY `idx_cap_seccion_docente` (`docente_id`,`estado`),
  KEY `fk_cap_seccion_creador` (`creada_por`),
  CONSTRAINT `fk_cap_seccion_asignatura` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`),
  CONSTRAINT `fk_cap_seccion_creador` FOREIGN KEY (`creada_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_cap_seccion_docente` FOREIGN KEY (`docente_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `chk_cap_seccion_fechas` CHECK (`fecha_finalizacion` is null or `fecha_inicio` is null or `fecha_finalizacion` >= `fecha_inicio`),
  CONSTRAINT `chk_cap_seccion_cupo` CHECK (`capacidad_maxima` between 1 and 30)
) ENGINE=InnoDB AUTO_INCREMENT=238 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de capacitacion_seccion_historial
CREATE TABLE `capacitacion_seccion_historial` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `seccion_id` bigint(20) unsigned NOT NULL,
  `accion` enum('creada','editada','abierta','cerrada','docente_cambiado','asignacion_automatica','asignacion_manual','estudiante_movido') NOT NULL,
  `datos_anteriores` longtext DEFAULT NULL,
  `datos_nuevos` longtext DEFAULT NULL,
  `motivo` varchar(500) DEFAULT NULL,
  `realizado_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_seccion_historial_fecha` (`seccion_id`,`created_at`),
  KEY `idx_seccion_historial_admin` (`realizado_por`,`created_at`),
  CONSTRAINT `fk_seccion_historial_admin` FOREIGN KEY (`realizado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_seccion_historial_seccion` FOREIGN KEY (`seccion_id`) REFERENCES `capacitacion_secciones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de carritos
CREATE TABLE `carritos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `estado` enum('activo','convertido','abandonado') NOT NULL DEFAULT 'activo',
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_carrito_usuario_estado` (`usuario_id`,`estado`),
  CONSTRAINT `fk_carrito_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de categorias_producto
CREATE TABLE `categorias_producto` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `imagen` varchar(500) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `eliminado_at` datetime DEFAULT NULL,
  `creado_por` int(10) unsigned DEFAULT NULL,
  `actualizado_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_categoria_estado_nombre` (`eliminado_at`,`activo`,`nombre`),
  KEY `idx_categoria_creado_por` (`creado_por`),
  KEY `idx_categoria_actualizado_por` (`actualizado_por`),
  CONSTRAINT `fk_categoria_actualizado` FOREIGN KEY (`actualizado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_categoria_creado` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de chat_bloqueos
CREATE TABLE `chat_bloqueos` (
  `usuario_id` int(10) unsigned NOT NULL,
  `bloqueado_por` int(10) unsigned NOT NULL,
  `motivo` varchar(500) NOT NULL,
  `bloqueado_at` datetime NOT NULL DEFAULT current_timestamp(),
  `desbloqueado_at` datetime DEFAULT NULL,
  PRIMARY KEY (`usuario_id`),
  KEY `fk_chat_bloqueo_admin` (`bloqueado_por`),
  CONSTRAINT `fk_chat_bloqueo_admin` FOREIGN KEY (`bloqueado_por`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_chat_bloqueo_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de chat_conversaciones
CREATE TABLE `chat_conversaciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tipo` enum('individual') NOT NULL DEFAULT 'individual',
  `clave_individual` char(64) NOT NULL,
  `creado_por` int(10) unsigned NOT NULL,
  `estado` enum('activa','cerrada') NOT NULL DEFAULT 'activa',
  `ultimo_mensaje_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_chat_individual` (`clave_individual`),
  KEY `idx_chat_actividad` (`estado`,`ultimo_mensaje_at`),
  KEY `fk_chat_creador` (`creado_por`),
  CONSTRAINT `fk_chat_creador` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de chat_mensajes
CREATE TABLE `chat_mensajes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversacion_id` bigint(20) unsigned NOT NULL,
  `remitente_id` int(10) unsigned NOT NULL,
  `respuesta_a_id` bigint(20) unsigned DEFAULT NULL,
  `contenido` text NOT NULL,
  `idempotency_key` char(64) DEFAULT NULL,
  `estado` enum('activo','moderado','eliminado') NOT NULL DEFAULT 'activo',
  `entregado_at` datetime DEFAULT NULL,
  `eliminado_at` datetime DEFAULT NULL,
  `eliminado_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_chat_mensaje_idempotencia` (`idempotency_key`),
  KEY `idx_chat_mensaje_conversacion` (`conversacion_id`,`id`),
  KEY `idx_chat_mensaje_remitente` (`remitente_id`,`created_at`),
  KEY `idx_chat_mensaje_respuesta` (`respuesta_a_id`),
  KEY `fk_chat_mensaje_eliminado_por` (`eliminado_por`),
  CONSTRAINT `fk_chat_mensaje_conversacion` FOREIGN KEY (`conversacion_id`) REFERENCES `chat_conversaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_chat_mensaje_eliminado_por` FOREIGN KEY (`eliminado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_chat_mensaje_remitente` FOREIGN KEY (`remitente_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_chat_mensaje_respuesta` FOREIGN KEY (`respuesta_a_id`) REFERENCES `chat_mensajes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de chat_participantes
CREATE TABLE `chat_participantes` (
  `conversacion_id` bigint(20) unsigned NOT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `ultimo_leido_mensaje_id` bigint(20) unsigned DEFAULT NULL,
  `unido_at` datetime NOT NULL DEFAULT current_timestamp(),
  `archivado_at` datetime DEFAULT NULL,
  PRIMARY KEY (`conversacion_id`,`usuario_id`),
  KEY `idx_chat_participante_usuario` (`usuario_id`,`archivado_at`),
  CONSTRAINT `fk_chat_part_conversacion` FOREIGN KEY (`conversacion_id`) REFERENCES `chat_conversaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_chat_part_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de chat_reportes
CREATE TABLE `chat_reportes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mensaje_id` bigint(20) unsigned NOT NULL,
  `reportado_por` int(10) unsigned NOT NULL,
  `motivo` varchar(500) NOT NULL,
  `estado` enum('pendiente','revisado','descartado','moderado') NOT NULL DEFAULT 'pendiente',
  `revisado_por` int(10) unsigned DEFAULT NULL,
  `revisado_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_chat_reporte_usuario` (`mensaje_id`,`reportado_por`),
  KEY `idx_chat_reporte_estado` (`estado`,`created_at`),
  KEY `fk_chat_reporte_usuario` (`reportado_por`),
  KEY `fk_chat_reporte_admin` (`revisado_por`),
  CONSTRAINT `fk_chat_reporte_admin` FOREIGN KEY (`revisado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_chat_reporte_mensaje` FOREIGN KEY (`mensaje_id`) REFERENCES `chat_mensajes` (`id`),
  CONSTRAINT `fk_chat_reporte_usuario` FOREIGN KEY (`reportado_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de contenidos
CREATE TABLE `contenidos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asignatura_id` int(10) unsigned NOT NULL,
  `seccion_id` bigint(20) unsigned DEFAULT NULL,
  `docente_id` int(10) unsigned NOT NULL,
  `modulo` varchar(120) NOT NULL DEFAULT 'Unidad 1',
  `tipo` enum('video','texto','documento','enlace','actividad','evaluacion','recurso_descargable') NOT NULL DEFAULT 'texto',
  `titulo` varchar(190) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `video_url` varchar(500) DEFAULT NULL,
  `archivo_relpath` varchar(255) DEFAULT NULL,
  `archivo_nombre` varchar(190) DEFAULT NULL,
  `archivo_mime` varchar(100) DEFAULT NULL,
  `archivo_tamano` int(10) unsigned DEFAULT NULL,
  `fecha_publicacion` datetime DEFAULT NULL,
  `fecha_limite` datetime DEFAULT NULL,
  `estado` enum('borrador','activo','inactivo') NOT NULL DEFAULT 'borrador',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `obligatorio` tinyint(1) NOT NULL DEFAULT 1,
  `peso_progreso` decimal(5,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_contenido_docente_curso` (`docente_id`,`asignatura_id`,`estado`,`created_at`),
  KEY `fk_contenido_asignatura` (`asignatura_id`),
  KEY `idx_contenido_seccion_publicacion` (`seccion_id`,`activo`,`fecha_publicacion`,`modulo`,`orden`),
  KEY `fk_contenido_seccion` (`seccion_id`),
  CONSTRAINT `fk_contenido_asignatura` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`),
  CONSTRAINT `fk_contenido_docente` FOREIGN KEY (`docente_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_contenido_seccion` FOREIGN KEY (`seccion_id`) REFERENCES `capacitacion_secciones` (`id`),
  CONSTRAINT `chk_contenido_peso` CHECK (`peso_progreso` between 0 and 100),
  CONSTRAINT `chk_contenido_fechas` CHECK (`fecha_limite` is null or `fecha_publicacion` is null or `fecha_limite` >= `fecha_publicacion`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de correo_centro_hilos
CREATE TABLE `correo_centro_hilos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asunto` varchar(190) NOT NULL,
  `usuario_relacionado_id` int(10) unsigned DEFAULT NULL,
  `ultimo_mensaje_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_correo_centro_actividad` (`ultimo_mensaje_at`),
  KEY `idx_correo_centro_usuario` (`usuario_relacionado_id`,`ultimo_mensaje_at`),
  CONSTRAINT `fk_correo_centro_usuario` FOREIGN KEY (`usuario_relacionado_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de correo_centro_mensajes
CREATE TABLE `correo_centro_mensajes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hilo_id` bigint(20) unsigned NOT NULL,
  `direccion` enum('entrada','salida') NOT NULL,
  `autor_usuario_id` int(10) unsigned DEFAULT NULL,
  `remitente` varchar(190) NOT NULL,
  `destinatario` varchar(190) NOT NULL,
  `reply_to` varchar(190) DEFAULT NULL,
  `asunto` varchar(190) NOT NULL,
  `contenido_texto` mediumtext NOT NULL,
  `message_id_servidor` varchar(255) DEFAULT NULL,
  `uid_imap` bigint(20) unsigned DEFAULT NULL,
  `carpeta_imap` varchar(120) DEFAULT NULL,
  `in_reply_to` varchar(255) DEFAULT NULL,
  `estado` enum('pendiente','enviado','recibido','fallido') NOT NULL,
  `leido_at` datetime DEFAULT NULL,
  `error_sanitizado` varchar(500) DEFAULT NULL,
  `enviado_recibido_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_correo_message_id` (`message_id_servidor`),
  UNIQUE KEY `uq_correo_imap` (`carpeta_imap`,`uid_imap`),
  KEY `idx_correo_hilo_fecha` (`hilo_id`,`enviado_recibido_at`),
  KEY `idx_correo_autor` (`autor_usuario_id`,`enviado_recibido_at`),
  KEY `idx_correo_estado` (`estado`,`enviado_recibido_at`),
  KEY `idx_correo_no_leido` (`direccion`,`leido_at`,`enviado_recibido_at`),
  CONSTRAINT `fk_correo_mensaje_autor` FOREIGN KEY (`autor_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_correo_mensaje_hilo` FOREIGN KEY (`hilo_id`) REFERENCES `correo_centro_hilos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de direcciones_usuario
CREATE TABLE `direcciones_usuario` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `etiqueta` enum('casa','oficina','otra') NOT NULL,
  `etiqueta_personalizada` varchar(60) DEFAULT NULL,
  `etiqueta_normalizada` varchar(80) NOT NULL,
  `receptor` varchar(160) NOT NULL,
  `telefono` varchar(30) NOT NULL,
  `departamento_id` smallint(5) unsigned NOT NULL,
  `municipio_id` smallint(5) unsigned NOT NULL,
  `distrito_id` smallint(5) unsigned DEFAULT NULL,
  `direccion_detallada` varchar(500) NOT NULL,
  `referencias` varchar(500) DEFAULT NULL,
  `predeterminada` tinyint(1) NOT NULL DEFAULT 0,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_direccion_etiqueta` (`usuario_id`,`etiqueta_normalizada`),
  KEY `idx_direccion_usuario` (`usuario_id`,`activa`,`predeterminada`),
  KEY `fk_direccion_departamento` (`departamento_id`),
  KEY `fk_direccion_municipio` (`municipio_id`),
  KEY `fk_direccion_distrito` (`distrito_id`),
  CONSTRAINT `fk_direccion_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`),
  CONSTRAINT `fk_direccion_distrito` FOREIGN KEY (`distrito_id`) REFERENCES `distritos` (`id`),
  CONSTRAINT `fk_direccion_municipio` FOREIGN KEY (`municipio_id`) REFERENCES `municipios` (`id`),
  CONSTRAINT `fk_direccion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de docentes_asignaturas
CREATE TABLE `docentes_asignaturas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `docente_id` int(10) unsigned NOT NULL,
  `asignatura_id` int(10) unsigned NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `asignado_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_docente_asignatura` (`docente_id`,`asignatura_id`),
  KEY `idx_docente_asignacion` (`docente_id`,`estado`,`asignatura_id`),
  KEY `idx_asignatura_docente` (`asignatura_id`,`estado`,`docente_id`),
  KEY `fk_da_admin` (`asignado_por`),
  CONSTRAINT `fk_da_admin` FOREIGN KEY (`asignado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_da_asignatura` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`),
  CONSTRAINT `fk_da_docente` FOREIGN KEY (`docente_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=167 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de dte_configuracion
CREATE TABLE `dte_configuracion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ambiente` enum('simulation','test','production') NOT NULL DEFAULT 'simulation',
  `nit` varchar(20) NOT NULL,
  `nrc` varchar(20) NOT NULL,
  `nombre_comercial` varchar(180) NOT NULL,
  `razon_social` varchar(220) NOT NULL,
  `actividad_codigo` varchar(10) NOT NULL,
  `actividad_descripcion` varchar(250) NOT NULL,
  `direccion` varchar(500) NOT NULL,
  `departamento_codigo` char(2) NOT NULL DEFAULT '06',
  `municipio_codigo` char(2) NOT NULL DEFAULT '14',
  `telefono` varchar(30) NOT NULL,
  `correo` varchar(180) NOT NULL,
  `codigo_establecimiento` char(4) NOT NULL DEFAULT 'M001',
  `punto_venta` char(4) NOT NULL DEFAULT 'P001',
  `schema_version` varchar(20) NOT NULL DEFAULT '1',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `actualizado_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_dte_config_activa` (`activo`,`ambiente`),
  KEY `fk_dte_config_admin` (`actualizado_por`),
  CONSTRAINT `fk_dte_config_admin` FOREIGN KEY (`actualizado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de entregas_contenido
CREATE TABLE `entregas_contenido` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `contenido_id` bigint(20) unsigned NOT NULL,
  `estudiante_id` int(10) unsigned NOT NULL,
  `asignatura_id` int(10) unsigned NOT NULL,
  `seccion_id` bigint(20) unsigned NOT NULL,
  `intento` smallint(5) unsigned NOT NULL DEFAULT 1,
  `comentario` text DEFAULT NULL,
  `resultado` text DEFAULT NULL,
  `estado` enum('pendiente','enviada','en_revision','aprobada','rechazada','requiere_correccion') NOT NULL DEFAULT 'enviada',
  `nota` decimal(4,2) DEFAULT NULL,
  `retroalimentacion` text DEFAULT NULL,
  `enviado_at` datetime NOT NULL DEFAULT current_timestamp(),
  `revisado_at` datetime DEFAULT NULL,
  `revisado_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_entrega_intento` (`contenido_id`,`estudiante_id`,`intento`),
  KEY `idx_entrega_revision` (`seccion_id`,`estado`,`enviado_at`),
  KEY `idx_entrega_estudiante` (`estudiante_id`,`asignatura_id`,`estado`),
  KEY `fk_entrega_contenido_asignatura` (`asignatura_id`),
  KEY `fk_entrega_contenido_revisor` (`revisado_por`),
  CONSTRAINT `fk_entrega_contenido` FOREIGN KEY (`contenido_id`) REFERENCES `contenidos` (`id`),
  CONSTRAINT `fk_entrega_contenido_asignatura` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`),
  CONSTRAINT `fk_entrega_contenido_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_entrega_contenido_revisor` FOREIGN KEY (`revisado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_entrega_contenido_seccion` FOREIGN KEY (`seccion_id`) REFERENCES `capacitacion_secciones` (`id`),
  CONSTRAINT `chk_entrega_intento` CHECK (`intento` >= 1),
  CONSTRAINT `chk_entrega_nota` CHECK (`nota` is null or `nota` between 0.00 and 10.00)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de entrega_evidencias
CREATE TABLE `entrega_evidencias` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entrega_id` bigint(20) unsigned NOT NULL,
  `archivo_relpath` varchar(255) NOT NULL,
  `archivo_nombre` varchar(190) NOT NULL,
  `archivo_mime` varchar(100) NOT NULL,
  `archivo_tamano` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_evidencia_entrega` (`entrega_id`),
  CONSTRAINT `fk_evidencia_entrega` FOREIGN KEY (`entrega_id`) REFERENCES `entregas_contenido` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de entrega_revisiones
CREATE TABLE `entrega_revisiones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entrega_id` bigint(20) unsigned NOT NULL,
  `docente_id` int(10) unsigned NOT NULL,
  `estado_anterior` varchar(30) NOT NULL,
  `estado_nuevo` enum('en_revision','aprobada','rechazada','requiere_correccion') NOT NULL,
  `nota_anterior` decimal(4,2) DEFAULT NULL,
  `nota_nueva` decimal(4,2) DEFAULT NULL,
  `retroalimentacion` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_revision_entrega_fecha` (`entrega_id`,`created_at`),
  KEY `idx_revision_docente` (`docente_id`,`created_at`),
  CONSTRAINT `fk_revision_docente` FOREIGN KEY (`docente_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_revision_entrega` FOREIGN KEY (`entrega_id`) REFERENCES `entregas_contenido` (`id`),
  CONSTRAINT `chk_revision_nota_anterior` CHECK (`nota_anterior` is null or `nota_anterior` between 0.00 and 10.00),
  CONSTRAINT `chk_revision_nota_nueva` CHECK (`nota_nueva` is null or `nota_nueva` between 0.00 and 10.00)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de estudiantes_docentes
CREATE TABLE `estudiantes_docentes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `estudiante_id` int(10) unsigned NOT NULL,
  `docente_id` int(10) unsigned NOT NULL,
  `asignatura_id` int(10) unsigned NOT NULL,
  `estado` enum('activo','retirado','finalizado') NOT NULL DEFAULT 'activo',
  `matriculado_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_matricula_academica` (`estudiante_id`,`docente_id`,`asignatura_id`),
  KEY `idx_matricula_docente_curso` (`docente_id`,`asignatura_id`,`estado`),
  KEY `idx_matricula_estudiante` (`estudiante_id`,`estado`,`asignatura_id`),
  KEY `fk_ed_asignatura` (`asignatura_id`),
  KEY `fk_ed_admin` (`matriculado_por`),
  CONSTRAINT `fk_ed_admin` FOREIGN KEY (`matriculado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ed_asignatura` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`),
  CONSTRAINT `fk_ed_docente` FOREIGN KEY (`docente_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_ed_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de evaluaciones
CREATE TABLE `evaluaciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asignatura_id` int(10) unsigned NOT NULL,
  `docente_id` int(10) unsigned NOT NULL,
  `titulo` varchar(190) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_apertura` datetime DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `nota_maxima` decimal(6,2) NOT NULL DEFAULT 10.00,
  `estado` enum('borrador','publicada','cerrada','inactiva') NOT NULL DEFAULT 'borrador',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_evaluacion_docente_curso` (`docente_id`,`asignatura_id`,`estado`,`fecha_cierre`),
  KEY `fk_evaluacion_asignatura` (`asignatura_id`),
  CONSTRAINT `fk_evaluacion_asignatura` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`),
  CONSTRAINT `fk_evaluacion_docente` FOREIGN KEY (`docente_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de ev_entregadas
CREATE TABLE `ev_entregadas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `evaluacion_id` bigint(20) unsigned NOT NULL,
  `estudiante_id` int(10) unsigned NOT NULL,
  `archivo_relpath` varchar(255) DEFAULT NULL,
  `archivo_nombre` varchar(190) DEFAULT NULL,
  `archivo_mime` varchar(100) DEFAULT NULL,
  `archivo_tamano` int(10) unsigned DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `estado` enum('pendiente','revisada','tardia') NOT NULL DEFAULT 'pendiente',
  `entregado_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_entrega_evaluacion_estudiante` (`evaluacion_id`,`estudiante_id`),
  KEY `idx_entrega_estado_fecha` (`estado`,`entregado_at`),
  KEY `idx_entrega_estudiante` (`estudiante_id`,`evaluacion_id`),
  CONSTRAINT `fk_entrega_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_entrega_evaluacion` FOREIGN KEY (`evaluacion_id`) REFERENCES `evaluaciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de historial_cambios_cuenta
CREATE TABLE `historial_cambios_cuenta` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `accion` varchar(60) NOT NULL,
  `campos_modificados` varchar(500) NOT NULL,
  `ip_hash` char(64) NOT NULL,
  `sesion_hash` char(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_historial_usuario_fecha` (`usuario_id`,`created_at`),
  CONSTRAINT `fk_cuenta_historial_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de inscripciones_capacitacion
CREATE TABLE `inscripciones_capacitacion` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `asignatura_id` int(10) unsigned NOT NULL,
  `pago_id` bigint(20) unsigned NOT NULL,
  `seccion_id` bigint(20) unsigned DEFAULT NULL,
  `docente_id` int(10) unsigned DEFAULT NULL,
  `estado` enum('pendiente_asignacion','inscrito','retirado','finalizado') NOT NULL DEFAULT 'pendiente_asignacion',
  `asignacion_limite_at` datetime DEFAULT NULL,
  `ultimo_intento_asignacion_at` datetime DEFAULT NULL,
  `asignado_por` int(10) unsigned DEFAULT NULL,
  `metodo_asignacion` enum('automatica','manual') DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `finalizacion_confirmada_por` int(10) unsigned DEFAULT NULL,
  `finalizacion_confirmada_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_inscripcion_pago` (`pago_id`),
  UNIQUE KEY `uq_inscripcion_usuario_capacitacion` (`usuario_id`,`asignatura_id`),
  KEY `idx_inscripcion_seccion` (`seccion_id`,`estado`),
  KEY `idx_inscripcion_docente` (`docente_id`,`estado`),
  KEY `idx_inscripcion_busqueda` (`asignatura_id`,`estado`,`usuario_id`),
  KEY `fk_inscripcion_asignador` (`asignado_por`),
  KEY `fk_inscripcion_confirmador` (`finalizacion_confirmada_por`),
  KEY `idx_inscripcion_asignacion_pendiente` (`estado`,`asignacion_limite_at`,`asignatura_id`),
  CONSTRAINT `fk_inscripcion_asignador` FOREIGN KEY (`asignado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_inscripcion_asignatura` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`),
  CONSTRAINT `fk_inscripcion_confirmador` FOREIGN KEY (`finalizacion_confirmada_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_inscripcion_docente` FOREIGN KEY (`docente_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_inscripcion_pago` FOREIGN KEY (`pago_id`) REFERENCES `capacitacion_pagos` (`id`),
  CONSTRAINT `fk_inscripcion_seccion` FOREIGN KEY (`seccion_id`) REFERENCES `capacitacion_secciones` (`id`),
  CONSTRAINT `fk_inscripcion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=250 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de inscripcion_movimientos
CREATE TABLE `inscripcion_movimientos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inscripcion_id` bigint(20) unsigned NOT NULL,
  `seccion_origen_id` bigint(20) unsigned DEFAULT NULL,
  `seccion_destino_id` bigint(20) unsigned DEFAULT NULL,
  `docente_origen_id` int(10) unsigned DEFAULT NULL,
  `docente_destino_id` int(10) unsigned DEFAULT NULL,
  `motivo` varchar(500) NOT NULL,
  `realizado_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_movimiento_inscripcion` (`inscripcion_id`,`created_at`),
  KEY `fk_movimiento_origen` (`seccion_origen_id`),
  KEY `fk_movimiento_destino` (`seccion_destino_id`),
  KEY `fk_movimiento_docente_origen` (`docente_origen_id`),
  KEY `fk_movimiento_docente_destino` (`docente_destino_id`),
  KEY `fk_movimiento_admin` (`realizado_por`),
  CONSTRAINT `fk_movimiento_admin` FOREIGN KEY (`realizado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_movimiento_destino` FOREIGN KEY (`seccion_destino_id`) REFERENCES `capacitacion_secciones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_movimiento_docente_destino` FOREIGN KEY (`docente_destino_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_movimiento_docente_origen` FOREIGN KEY (`docente_origen_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_movimiento_inscripcion` FOREIGN KEY (`inscripcion_id`) REFERENCES `inscripciones_capacitacion` (`id`),
  CONSTRAINT `fk_movimiento_origen` FOREIGN KEY (`seccion_origen_id`) REFERENCES `capacitacion_secciones` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de menu_sitio
CREATE TABLE `menu_sitio` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `padre_id` int(10) unsigned DEFAULT NULL,
  `texto` varchar(100) NOT NULL,
  `slug` varchar(190) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `url` varchar(500) NOT NULL,
  `nueva_pestana` tinyint(1) NOT NULL DEFAULT 0,
  `visibilidad` enum('publica','autenticada') NOT NULL DEFAULT 'publica',
  `roles_json` longtext DEFAULT NULL,
  `tipo_contenido` enum('pagina_informativa','texto_enriquecido','imagenes','galeria','noticias','productos','capacitaciones','formulario','video','archivo_descargable','enlace_interno','enlace_externo','bloques_reutilizables') NOT NULL DEFAULT 'enlace_interno',
  `contenido_html` mediumtext DEFAULT NULL,
  `contenido_json` longtext DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `eliminado_at` datetime DEFAULT NULL,
  `eliminado_por` int(10) unsigned DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_menu_slug` (`slug`),
  KEY `idx_menu_activo_orden` (`activo`,`orden`),
  KEY `idx_menu_padre_orden` (`padre_id`,`orden`),
  KEY `idx_menu_papelera` (`eliminado_at`),
  KEY `fk_menu_eliminado_por` (`eliminado_por`),
  CONSTRAINT `fk_menu_eliminado_por` FOREIGN KEY (`eliminado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_menu_padre` FOREIGN KEY (`padre_id`) REFERENCES `menu_sitio` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_menu_roles_json` CHECK (`roles_json` is null or json_valid(`roles_json`)),
  CONSTRAINT `chk_menu_contenido_json` CHECK (`contenido_json` is null or json_valid(`contenido_json`))
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de notas
CREATE TABLE `notas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entrega_id` bigint(20) unsigned NOT NULL,
  `evaluacion_id` bigint(20) unsigned NOT NULL,
  `estudiante_id` int(10) unsigned NOT NULL,
  `docente_id` int(10) unsigned NOT NULL,
  `nota` decimal(6,2) NOT NULL,
  `observacion` varchar(1000) DEFAULT NULL,
  `calificado_por` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nota_entrega` (`entrega_id`),
  KEY `idx_nota_evaluacion_estudiante` (`evaluacion_id`,`estudiante_id`),
  KEY `idx_nota_docente_fecha` (`docente_id`,`updated_at`),
  KEY `fk_nota_estudiante` (`estudiante_id`),
  KEY `fk_nota_calificador` (`calificado_por`),
  CONSTRAINT `fk_nota_calificador` FOREIGN KEY (`calificado_por`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_nota_docente` FOREIGN KEY (`docente_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_nota_entrega` FOREIGN KEY (`entrega_id`) REFERENCES `ev_entregadas` (`id`),
  CONSTRAINT `fk_nota_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_nota_evaluacion` FOREIGN KEY (`evaluacion_id`) REFERENCES `evaluaciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de notas_historial
CREATE TABLE `notas_historial` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nota_id` bigint(20) unsigned NOT NULL,
  `nota_anterior` decimal(6,2) DEFAULT NULL,
  `nota_nueva` decimal(6,2) NOT NULL,
  `observacion_anterior` varchar(1000) DEFAULT NULL,
  `observacion_nueva` varchar(1000) DEFAULT NULL,
  `cambiado_por` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_historial_nota_fecha` (`nota_id`,`created_at`),
  KEY `fk_nh_usuario` (`cambiado_por`),
  CONSTRAINT `fk_nh_nota` FOREIGN KEY (`nota_id`) REFERENCES `notas` (`id`),
  CONSTRAINT `fk_nh_usuario` FOREIGN KEY (`cambiado_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de noticias
CREATE TABLE `noticias` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(190) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `resumen` varchar(500) NOT NULL,
  `contenido` mediumtext NOT NULL,
  `imagen_portada` varchar(255) DEFAULT NULL,
  `fecha_publicacion` datetime DEFAULT NULL,
  `autor` varchar(180) DEFAULT NULL,
  `estado` enum('borrador','publicado') NOT NULL DEFAULT 'borrador',
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` datetime DEFAULT NULL,
  `eliminado_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_noticias_slug` (`slug`),
  KEY `idx_noticias_publicacion` (`estado`,`activo`,`deleted_at`,`fecha_publicacion`),
  KEY `idx_noticias_destacadas` (`destacado`,`estado`,`activo`),
  KEY `fk_noticias_eliminado_por` (`eliminado_por`),
  CONSTRAINT `fk_noticias_eliminado_por` FOREIGN KEY (`eliminado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de notificacion_preferencias
CREATE TABLE `notificacion_preferencias` (
  `usuario_id` int(10) unsigned NOT NULL,
  `categoria` varchar(80) NOT NULL,
  `correo_habilitado` tinyint(1) NOT NULL DEFAULT 1,
  `agrupar_habilitado` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`usuario_id`,`categoria`),
  CONSTRAINT `fk_preferencia_notificacion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de password_reset_tokens
CREATE TABLE `password_reset_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `email_hash` char(64) NOT NULL,
  `token_hash` char(64) DEFAULT NULL,
  `request_ip_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `idx_reset_usuario_estado` (`user_id`,`used_at`,`expires_at`),
  KEY `idx_reset_email_fecha` (`email_hash`,`created_at`),
  KEY `idx_reset_ip_fecha` (`request_ip_hash`,`created_at`),
  CONSTRAINT `fk_password_reset_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de pedidos
CREATE TABLE `pedidos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(32) NOT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `carrito_id` bigint(20) unsigned DEFAULT NULL,
  `direccion_id` bigint(20) unsigned DEFAULT NULL,
  `direccion_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`direccion_snapshot`)),
  `subtotal` decimal(12,2) NOT NULL,
  `descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `envio` decimal(12,2) NOT NULL DEFAULT 0.00,
  `impuestos` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL,
  `moneda` char(3) NOT NULL DEFAULT 'usd',
  `checkout_key` char(64) DEFAULT NULL,
  `estado` enum('carrito','pendiente_pago','pagado','preparando','enviado','entregado','cancelado','pago_fallido','reembolsado') NOT NULL DEFAULT 'pendiente_pago',
  `payment_status` varchar(40) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `stripe_checkout_session_id` varchar(255) DEFAULT NULL,
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `payment_brand` varchar(32) DEFAULT NULL,
  `payment_last4` char(4) DEFAULT NULL,
  `stripe_payment_method_id` varchar(255) DEFAULT NULL,
  `last_stripe_event_id` varchar(255) DEFAULT NULL,
  `receipt_generated_at` datetime DEFAULT NULL,
  `email_sent_at` datetime DEFAULT NULL,
  `stock_procesado` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  UNIQUE KEY `stripe_checkout_session_id` (`stripe_checkout_session_id`),
  UNIQUE KEY `stripe_payment_intent_id` (`stripe_payment_intent_id`),
  UNIQUE KEY `uq_pedido_checkout_key` (`checkout_key`),
  KEY `idx_pedido_usuario_fecha` (`usuario_id`,`created_at`),
  KEY `idx_pedido_estado` (`estado`),
  KEY `idx_pedido_pago_fecha` (`payment_status`,`paid_at`),
  KEY `idx_pedido_evento_stripe` (`last_stripe_event_id`),
  KEY `idx_pedido_carrito` (`carrito_id`),
  KEY `fk_pedido_direccion` (`direccion_id`),
  KEY `idx_pedido_estado_fecha` (`estado`,`created_at`),
  CONSTRAINT `fk_pedido_carrito` FOREIGN KEY (`carrito_id`) REFERENCES `carritos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pedido_direccion` FOREIGN KEY (`direccion_id`) REFERENCES `direcciones_usuario` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `chk_pedido_total` CHECK (`total` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de personalizaciones_visuales
CREATE TABLE `personalizaciones_visuales` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `area` enum('website','dashboard','estudiantes','docente') NOT NULL,
  `configuracion_json` longtext NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  `actualizado_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_personalizacion_area` (`area`),
  KEY `fk_personalizacion_actualizador` (`actualizado_por`),
  CONSTRAINT `fk_personalizacion_actualizador` FOREIGN KEY (`actualizado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de personalizaciones_visuales_historial
CREATE TABLE `personalizaciones_visuales_historial` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `personalizacion_id` int(10) unsigned DEFAULT NULL,
  `area` enum('website','dashboard','estudiantes','docente') NOT NULL,
  `accion` enum('publicar','restaurar_original') NOT NULL,
  `configuracion_json` longtext NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `realizado_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_personalizacion_historial_area` (`area`,`created_at`),
  KEY `idx_personalizacion_historial_admin` (`realizado_por`,`created_at`),
  KEY `fk_personalizacion_historial_config` (`personalizacion_id`),
  CONSTRAINT `fk_personalizacion_historial_admin` FOREIGN KEY (`realizado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_personalizacion_historial_config` FOREIGN KEY (`personalizacion_id`) REFERENCES `personalizaciones_visuales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de productos
CREATE TABLE `productos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `categoria_id` int(10) unsigned DEFAULT NULL,
  `sku` varchar(80) DEFAULT NULL,
  `nombre` varchar(180) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `descripcion_corta` varchar(500) NOT NULL,
  `descripcion` text NOT NULL,
  `tipo_producto` varchar(40) NOT NULL DEFAULT 'producto',
  `caracteristicas` text DEFAULT NULL,
  `informacion_entrega` varchar(1000) DEFAULT NULL,
  `precio` decimal(12,2) NOT NULL,
  `stock` int(10) unsigned NOT NULL DEFAULT 0,
  `stock_reservado` int(10) unsigned NOT NULL DEFAULT 0,
  `stock_minimo` int(10) unsigned NOT NULL DEFAULT 0,
  `disponible` tinyint(1) NOT NULL DEFAULT 1,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `imagen_principal` varchar(500) DEFAULT NULL,
  `eliminado_at` datetime DEFAULT NULL,
  `creado_por` int(10) unsigned DEFAULT NULL,
  `actualizado_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `sku` (`sku`),
  KEY `idx_productos_publicos` (`activo`,`disponible`,`eliminado_at`),
  KEY `idx_productos_stock` (`stock`,`stock_minimo`),
  KEY `fk_productos_categoria` (`categoria_id`),
  KEY `fk_productos_creado` (`creado_por`),
  KEY `fk_productos_actualizado` (`actualizado_por`),
  CONSTRAINT `fk_productos_actualizado` FOREIGN KEY (`actualizado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_producto` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_productos_creado` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_producto_precio` CHECK (`precio` >= 0),
  CONSTRAINT `chk_producto_reserva` CHECK (`stock_reservado` <= `stock`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de producto_imagenes
CREATE TABLE `producto_imagenes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` int(10) unsigned NOT NULL,
  `ruta` varchar(500) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_imagen_producto` (`producto_id`,`orden`),
  CONSTRAINT `fk_imagen_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de progreso_contenido
CREATE TABLE `progreso_contenido` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inscripcion_id` bigint(20) unsigned NOT NULL,
  `contenido_id` bigint(20) unsigned NOT NULL,
  `visto_at` datetime DEFAULT NULL,
  `completado_at` datetime DEFAULT NULL,
  `ultima_actividad_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_progreso_inscripcion_contenido` (`inscripcion_id`,`contenido_id`),
  KEY `idx_progreso_actividad` (`inscripcion_id`,`ultima_actividad_at`),
  KEY `fk_progreso_contenido` (`contenido_id`),
  CONSTRAINT `fk_progreso_contenido` FOREIGN KEY (`contenido_id`) REFERENCES `contenidos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_progreso_inscripcion` FOREIGN KEY (`inscripcion_id`) REFERENCES `inscripciones_capacitacion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de promociones
CREATE TABLE `promociones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` int(10) unsigned NOT NULL,
  `etiqueta` varchar(100) DEFAULT NULL,
  `precio_promocional` decimal(12,2) NOT NULL,
  `porcentaje_descuento` decimal(5,2) DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `creado_por` int(10) unsigned DEFAULT NULL,
  `actualizado_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_promocion_vigente` (`producto_id`,`activa`,`fecha_inicio`,`fecha_fin`),
  KEY `fk_promocion_creado` (`creado_por`),
  KEY `fk_promocion_actualizado` (`actualizado_por`),
  CONSTRAINT `fk_promocion_actualizado` FOREIGN KEY (`actualizado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_promocion_creado` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_promocion_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_promocion_fechas` CHECK (`fecha_fin` > `fecha_inicio`),
  CONSTRAINT `chk_promocion_precio` CHECK (`precio_promocional` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de respaldos_base_datos
CREATE TABLE `respaldos_base_datos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `creado_por` int(10) unsigned DEFAULT NULL,
  `restaurado_por` int(10) unsigned DEFAULT NULL,
  `respaldo_previo_id` bigint(20) unsigned DEFAULT NULL,
  `tipo` enum('manual','previo_restauracion') NOT NULL DEFAULT 'manual',
  `nombre_archivo` varchar(180) NOT NULL,
  `ruta_relativa` varchar(255) NOT NULL,
  `tamano_bytes` bigint(20) unsigned DEFAULT NULL,
  `sha256` char(64) DEFAULT NULL,
  `estado` enum('creando','disponible','restaurando','restaurado','fallido','eliminado') NOT NULL DEFAULT 'creando',
  `tablas_incluidas` smallint(5) unsigned NOT NULL DEFAULT 0,
  `filas_incluidas` bigint(20) unsigned NOT NULL DEFAULT 0,
  `error_sanitizado` varchar(500) DEFAULT NULL,
  `restaurado_at` datetime DEFAULT NULL,
  `eliminado_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_respaldo_ruta` (`ruta_relativa`),
  KEY `idx_respaldo_estado_fecha` (`estado`,`created_at`),
  KEY `idx_respaldo_creador` (`creado_por`,`created_at`),
  KEY `fk_respaldo_restaurador` (`restaurado_por`),
  KEY `fk_respaldo_previo` (`respaldo_previo_id`),
  CONSTRAINT `fk_respaldo_creador` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_respaldo_previo` FOREIGN KEY (`respaldo_previo_id`) REFERENCES `respaldos_base_datos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_respaldo_restaurador` FOREIGN KEY (`restaurado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de user_deletions
CREATE TABLE `user_deletions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `requested_by` int(10) unsigned DEFAULT NULL,
  `restored_by` int(10) unsigned DEFAULT NULL,
  `reason` varchar(500) NOT NULL,
  `email_hash` char(64) DEFAULT NULL,
  `request_ip_hash` char(64) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'periodo_gracia',
  `effective_at` datetime NOT NULL,
  `restored_at` datetime DEFAULT NULL,
  `anonymized_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_deletion_state` (`status`,`effective_at`),
  KEY `idx_deletion_user` (`user_id`,`created_at`),
  KEY `fk_deletion_restorer` (`restored_by`),
  KEY `fk_deletion_requester` (`requested_by`),
  KEY `idx_deletion_email_hash` (`email_hash`,`status`,`effective_at`),
  CONSTRAINT `fk_deletion_requester` FOREIGN KEY (`requested_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_deletion_restorer` FOREIGN KEY (`restored_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_deletion_user` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de carrito_items
CREATE TABLE `carrito_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `carrito_id` bigint(20) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `cantidad` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_carrito_producto` (`carrito_id`,`producto_id`),
  KEY `idx_carrito_item` (`carrito_id`),
  KEY `fk_item_producto` (`producto_id`),
  CONSTRAINT `fk_item_carrito` FOREIGN KEY (`carrito_id`) REFERENCES `carritos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_item_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `chk_item_cantidad` CHECK (`cantidad` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de certificados_capacitacion
CREATE TABLE `certificados_capacitacion` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inscripcion_id` bigint(20) unsigned NOT NULL,
  `estudiante_id` int(10) unsigned NOT NULL,
  `asignatura_id` int(10) unsigned NOT NULL,
  `numero` varchar(80) NOT NULL,
  `token_verificacion` char(64) NOT NULL,
  `plantilla_relpath` varchar(255) NOT NULL,
  `pdf_relpath` varchar(255) NOT NULL,
  `emitido_at` datetime NOT NULL DEFAULT current_timestamp(),
  `finalizado_at` date NOT NULL,
  `emitido_por` int(10) unsigned DEFAULT NULL,
  `estado` enum('emitido','revocado') NOT NULL DEFAULT 'emitido',
  `motivo_revocacion` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_certificado_inscripcion` (`inscripcion_id`),
  UNIQUE KEY `uq_certificado_numero` (`numero`),
  UNIQUE KEY `uq_certificado_token` (`token_verificacion`),
  KEY `idx_certificado_estudiante` (`estudiante_id`,`emitido_at`),
  KEY `idx_certificado_asignatura` (`asignatura_id`,`estado`),
  KEY `fk_certificado_emisor` (`emitido_por`),
  CONSTRAINT `fk_certificado_asignatura` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`),
  CONSTRAINT `fk_certificado_emisor` FOREIGN KEY (`emitido_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_certificado_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_certificado_inscripcion` FOREIGN KEY (`inscripcion_id`) REFERENCES `inscripciones_capacitacion` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de chat_adjuntos
CREATE TABLE `chat_adjuntos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mensaje_id` bigint(20) unsigned NOT NULL,
  `archivo_relpath` varchar(255) NOT NULL,
  `archivo_nombre` varchar(190) NOT NULL,
  `archivo_mime` varchar(100) NOT NULL,
  `archivo_tamano` int(10) unsigned NOT NULL,
  `es_imagen` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chat_adjunto_mensaje` (`mensaje_id`),
  CONSTRAINT `fk_chat_adjunto_mensaje` FOREIGN KEY (`mensaje_id`) REFERENCES `chat_mensajes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de chat_lecturas
CREATE TABLE `chat_lecturas` (
  `mensaje_id` bigint(20) unsigned NOT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `leido_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`mensaje_id`,`usuario_id`),
  KEY `idx_chat_lectura_usuario` (`usuario_id`,`leido_at`),
  CONSTRAINT `fk_chat_lectura_mensaje` FOREIGN KEY (`mensaje_id`) REFERENCES `chat_mensajes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_chat_lectura_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de comunicacion_hilos
CREATE TABLE `comunicacion_hilos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `canal` enum('contacto','soporte','pedido','plataforma') NOT NULL,
  `asunto` varchar(190) NOT NULL,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `nombre_contacto` varchar(180) DEFAULT NULL,
  `correo_contacto` varchar(190) DEFAULT NULL,
  `pedido_id` bigint(20) unsigned DEFAULT NULL,
  `asignatura_id` int(10) unsigned DEFAULT NULL,
  `docente_id` int(10) unsigned DEFAULT NULL,
  `estado` enum('recibido','pendiente','respondido','cerrado') NOT NULL DEFAULT 'recibido',
  `ultimo_mensaje_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_hilo_estado_fecha` (`estado`,`ultimo_mensaje_at`),
  KEY `idx_hilo_usuario_fecha` (`usuario_id`,`ultimo_mensaje_at`),
  KEY `idx_hilo_pedido` (`pedido_id`),
  KEY `idx_hilo_academico` (`asignatura_id`,`docente_id`,`ultimo_mensaje_at`),
  KEY `fk_hilo_docente` (`docente_id`),
  CONSTRAINT `fk_hilo_asignatura` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hilo_docente` FOREIGN KEY (`docente_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hilo_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hilo_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de correo_centro_adjuntos
CREATE TABLE `correo_centro_adjuntos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mensaje_id` bigint(20) unsigned NOT NULL,
  `archivo_relpath` varchar(255) NOT NULL,
  `archivo_nombre` varchar(190) NOT NULL,
  `archivo_mime` varchar(100) NOT NULL,
  `archivo_tamano` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_correo_adjunto_mensaje` (`mensaje_id`),
  CONSTRAINT `fk_correo_adjunto_mensaje` FOREIGN KEY (`mensaje_id`) REFERENCES `correo_centro_mensajes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de correo_envios
CREATE TABLE `correo_envios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tipo` varchar(80) NOT NULL,
  `asunto` varchar(190) DEFAULT NULL,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `pedido_id` bigint(20) unsigned DEFAULT NULL,
  `hilo_id` bigint(20) unsigned DEFAULT NULL,
  `destinatario_enmascarado` varchar(255) NOT NULL,
  `destinatario_hash` char(64) NOT NULL,
  `destinatario_email` varchar(190) DEFAULT NULL,
  `destinatario_nombre` varchar(190) DEFAULT NULL,
  `contenido_html` mediumtext DEFAULT NULL,
  `contenido_texto` mediumtext DEFAULT NULL,
  `opciones_json` longtext DEFAULT NULL,
  `idempotency_key` varchar(190) NOT NULL,
  `evento_id` varchar(190) DEFAULT NULL,
  `grupo_clave` varchar(190) DEFAULT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'pendiente',
  `disponible_at` datetime NOT NULL DEFAULT current_timestamp(),
  `intento` int(10) unsigned NOT NULL DEFAULT 0,
  `max_intentos` tinyint(3) unsigned NOT NULL DEFAULT 3,
  `es_modo_prueba` tinyint(1) NOT NULL DEFAULT 0,
  `permitir_envio_prueba` tinyint(1) NOT NULL DEFAULT 0,
  `agrupados` int(10) unsigned NOT NULL DEFAULT 1,
  `error_sanitizado` varchar(500) DEFAULT NULL,
  `procesando_desde` datetime DEFAULT NULL,
  `enviado_at` datetime DEFAULT NULL,
  `cancelado_at` datetime DEFAULT NULL,
  `cancelado_motivo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_correo_idempotencia` (`idempotency_key`),
  UNIQUE KEY `uq_correo_evento` (`evento_id`),
  KEY `idx_correo_reintento` (`estado`,`procesando_desde`),
  KEY `idx_correo_pedido` (`pedido_id`,`tipo`),
  KEY `idx_correo_usuario` (`usuario_id`,`created_at`),
  KEY `idx_correo_estado_fecha` (`estado`,`created_at`),
  KEY `idx_correo_hilo` (`hilo_id`),
  KEY `idx_correo_cola` (`estado`,`disponible_at`,`intento`),
  KEY `idx_correo_grupo` (`usuario_id`,`grupo_clave`,`estado`),
  CONSTRAINT `fk_correo_hilo` FOREIGN KEY (`hilo_id`) REFERENCES `comunicacion_hilos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_correo_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_correo_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=375 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de dte_documentos
CREATE TABLE `dte_documentos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint(20) unsigned NOT NULL,
  `tipo_dte` char(2) NOT NULL DEFAULT '01',
  `ambiente` enum('simulation','test','production') NOT NULL,
  `codigo_generacion` char(36) NOT NULL,
  `numero_control` varchar(31) NOT NULL,
  `sello_recepcion` varchar(255) DEFAULT NULL,
  `estado` varchar(40) NOT NULL,
  `schema_version` varchar(20) NOT NULL,
  `json_documento` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json_documento`)),
  `json_sha256` char(64) NOT NULL,
  `pdf_relpath` varchar(255) DEFAULT NULL,
  `pdf_sha256` char(64) DEFAULT NULL,
  `observaciones` varchar(1000) DEFAULT NULL,
  `emitido_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dte_pedido` (`pedido_id`),
  UNIQUE KEY `uq_dte_codigo` (`codigo_generacion`),
  UNIQUE KEY `uq_dte_control` (`numero_control`),
  KEY `idx_dte_estado_fecha` (`estado`,`emitido_at`),
  CONSTRAINT `fk_dte_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de dte_eventos
CREATE TABLE `dte_eventos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dte_id` bigint(20) unsigned DEFAULT NULL,
  `pedido_id` bigint(20) unsigned NOT NULL,
  `operacion` varchar(40) NOT NULL,
  `ambiente` varchar(20) NOT NULL,
  `resultado` varchar(40) NOT NULL,
  `request_sanitizado` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_sanitizado`)),
  `response_sanitizado` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_sanitizado`)),
  `codigo` varchar(80) DEFAULT NULL,
  `observaciones` varchar(1000) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_dte_evento` (`dte_id`,`created_at`),
  KEY `idx_dte_pedido_evento` (`pedido_id`,`created_at`),
  CONSTRAINT `fk_dte_evento_doc` FOREIGN KEY (`dte_id`) REFERENCES `dte_documentos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_dte_evento_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de errores_sistema
CREATE TABLE `errores_sistema` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fingerprint` char(64) NOT NULL,
  `categoria` enum('pago','webhook','dte','correo','stock','base_datos','sistema') NOT NULL,
  `modulo` varchar(80) NOT NULL,
  `nivel` enum('advertencia','error','critico') NOT NULL DEFAULT 'error',
  `mensaje` varchar(500) NOT NULL,
  `contexto_sanitizado` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`contexto_sanitizado`)),
  `ocurrencias` int(10) unsigned NOT NULL DEFAULT 1,
  `estado` enum('nuevo','revisando','resuelto') NOT NULL DEFAULT 'nuevo',
  `pedido_id` bigint(20) unsigned DEFAULT NULL,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `observacion_resolucion` varchar(1000) DEFAULT NULL,
  `actualizado_por` int(10) unsigned DEFAULT NULL,
  `primera_ocurrencia_at` datetime NOT NULL,
  `ultima_ocurrencia_at` datetime NOT NULL,
  `resuelto_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_error_fingerprint` (`fingerprint`),
  KEY `idx_error_estado_fecha` (`estado`,`ultima_ocurrencia_at`),
  KEY `idx_error_categoria_fecha` (`categoria`,`ultima_ocurrencia_at`),
  KEY `idx_error_pedido` (`pedido_id`),
  KEY `fk_error_usuario` (`usuario_id`),
  KEY `fk_error_admin` (`actualizado_por`),
  CONSTRAINT `fk_error_admin` FOREIGN KEY (`actualizado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_error_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_error_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1279 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de inventario_movimientos
CREATE TABLE `inventario_movimientos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` int(10) unsigned NOT NULL,
  `pedido_id` bigint(20) unsigned DEFAULT NULL,
  `usuario_admin_id` int(10) unsigned DEFAULT NULL,
  `tipo` enum('entrada','salida','ajuste','venta') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `stock_anterior` int(10) unsigned NOT NULL,
  `stock_nuevo` int(10) unsigned NOT NULL,
  `nota` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mov_producto_fecha` (`producto_id`,`created_at`),
  KEY `fk_mov_pedido` (`pedido_id`),
  KEY `fk_mov_admin` (`usuario_admin_id`),
  CONSTRAINT `fk_mov_admin` FOREIGN KEY (`usuario_admin_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_mov_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_mov_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de menu_formulario_envios
CREATE TABLE `menu_formulario_envios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` int(10) unsigned NOT NULL,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `datos_json` longtext NOT NULL,
  `estado` enum('nuevo','revisado','cerrado') NOT NULL DEFAULT 'nuevo',
  `ip_hash` char(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_menu_formulario_estado` (`menu_id`,`estado`,`created_at`),
  KEY `fk_menu_formulario_usuario` (`usuario_id`),
  CONSTRAINT `fk_menu_formulario_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu_sitio` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_menu_formulario_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_menu_formulario_json` CHECK (json_valid(`datos_json`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de pagos
CREATE TABLE `pagos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint(20) unsigned NOT NULL,
  `proveedor` varchar(30) NOT NULL DEFAULT 'stripe',
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `importe` decimal(12,2) NOT NULL,
  `moneda` char(3) NOT NULL,
  `estado` varchar(40) NOT NULL,
  `datos_referencia` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_referencia`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pago_pedido_proveedor` (`pedido_id`,`proveedor`),
  KEY `idx_pago_estado_fecha` (`estado`,`created_at`),
  CONSTRAINT `fk_pago_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de pedido_detalles
CREATE TABLE `pedido_detalles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint(20) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `nombre_producto` varchar(180) NOT NULL,
  `sku` varchar(80) DEFAULT NULL,
  `cantidad` int(10) unsigned NOT NULL,
  `precio_normal` decimal(12,2) NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL,
  `descuento_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL,
  `promocion_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_detalle_pedido` (`pedido_id`),
  KEY `fk_detalle_promocion` (`promocion_id`),
  KEY `idx_detalle_producto_pedido` (`producto_id`,`pedido_id`),
  CONSTRAINT `fk_detalle_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `fk_detalle_promocion` FOREIGN KEY (`promocion_id`) REFERENCES `promociones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_detalle_cantidad` CHECK (`cantidad` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de pedido_historial
CREATE TABLE `pedido_historial` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint(20) unsigned NOT NULL,
  `estado_anterior` varchar(30) DEFAULT NULL,
  `estado_nuevo` varchar(30) NOT NULL,
  `origen` enum('sistema','stripe','admin','usuario','dte') NOT NULL,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `pago_id` bigint(20) unsigned DEFAULT NULL,
  `nota` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_historial_pedido` (`pedido_id`,`created_at`),
  KEY `fk_historial_usuario` (`usuario_id`),
  KEY `idx_historial_estado_fecha` (`estado_nuevo`,`created_at`),
  KEY `fk_historial_pago` (`pago_id`),
  CONSTRAINT `fk_historial_pago` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_historial_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  CONSTRAINT `fk_historial_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de admin_notices
CREATE TABLE `admin_notices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'sistema',
  `level` enum('informacion','exito','advertencia','error') NOT NULL DEFAULT 'informacion',
  `title` varchar(180) NOT NULL,
  `message` varchar(2000) NOT NULL,
  `target_section` varchar(100) DEFAULT NULL,
  `action_url` varchar(500) DEFAULT NULL,
  `idempotency_key` varchar(190) DEFAULT NULL,
  `pedido_id` bigint(20) unsigned DEFAULT NULL,
  `correo_envio_id` bigint(20) unsigned DEFAULT NULL,
  `hilo_id` bigint(20) unsigned DEFAULT NULL,
  `error_id` bigint(20) unsigned DEFAULT NULL,
  `priority` varchar(20) NOT NULL DEFAULT 'normal',
  `status` varchar(20) NOT NULL DEFAULT 'pendiente',
  `due_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `email_sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_notice_idempotencia` (`idempotency_key`),
  KEY `idx_notice_user_status` (`user_id`,`status`,`created_at`),
  KEY `idx_notice_creator` (`created_by`,`created_at`),
  KEY `idx_notice_campana` (`user_id`,`status`,`created_at`),
  KEY `idx_notice_categoria_fecha` (`category`,`created_at`),
  KEY `idx_notice_pedido` (`pedido_id`),
  KEY `fk_notice_correo` (`correo_envio_id`),
  KEY `fk_notice_hilo` (`hilo_id`),
  KEY `fk_notice_error` (`error_id`),
  CONSTRAINT `fk_notice_correo` FOREIGN KEY (`correo_envio_id`) REFERENCES `correo_envios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_notice_creator` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_notice_error` FOREIGN KEY (`error_id`) REFERENCES `errores_sistema` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_notice_hilo` FOREIGN KEY (`hilo_id`) REFERENCES `comunicacion_hilos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_notice_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_notice_user` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2445 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de comunicacion_mensajes
CREATE TABLE `comunicacion_mensajes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hilo_id` bigint(20) unsigned NOT NULL,
  `direccion` enum('entrada','salida','interno') NOT NULL,
  `autor_usuario_id` int(10) unsigned DEFAULT NULL,
  `autor_nombre` varchar(180) DEFAULT NULL,
  `autor_correo` varchar(190) DEFAULT NULL,
  `contenido` text NOT NULL,
  `correo_envio_id` bigint(20) unsigned DEFAULT NULL,
  `resultado_envio` enum('no_aplica','pendiente','enviado','fallido') NOT NULL DEFAULT 'no_aplica',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mensaje_hilo_fecha` (`hilo_id`,`created_at`),
  KEY `idx_mensaje_correo` (`correo_envio_id`),
  KEY `fk_mensaje_autor` (`autor_usuario_id`),
  CONSTRAINT `fk_mensaje_autor` FOREIGN KEY (`autor_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_mensaje_correo` FOREIGN KEY (`correo_envio_id`) REFERENCES `correo_envios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_mensaje_hilo` FOREIGN KEY (`hilo_id`) REFERENCES `comunicacion_hilos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Información de configuracion_portal_estudiante
INSERT INTO `configuracion_portal_estudiante` (`id`,`clave`,`valor`,`tipo`,`grupo`,`updated_at`) VALUES
(1,'login_titulo','Iniciar sesion','texto','login','2026-07-12 11:08:49'),
(2,'login_subtitulo','Accede a tu cuenta de estudiante de Atenea','texto','login','2026-07-12 10:39:56'),
(3,'login_texto_boton','Iniciar sesion','texto','login','2026-07-12 11:08:49'),
(4,'login_imagen_fondo','','imagen','login','2026-07-12 10:39:56'),
(5,'login_imagen_lateral','src/estudiantes/assets/images/auth/01.png','imagen','login','2026-07-12 10:39:56'),
(6,'registro_titulo','Crear una cuenta','texto','registro','2026-07-12 10:39:56'),
(7,'registro_subtitulo','Reg├¡strate como estudiante de Atenea','texto','registro','2026-07-12 10:39:56'),
(8,'registro_texto_boton','Crear cuenta','texto','registro','2026-07-12 10:39:56'),
(9,'registro_imagen_fondo','','imagen','registro','2026-07-12 10:39:56'),
(10,'registro_imagen_lateral','src/estudiantes/assets/images/auth/02.png','imagen','registro','2026-07-12 10:39:56'),
(11,'panel_titulo','Portal del estudiante','texto','panel','2026-07-12 10:39:56'),
(12,'panel_subtitulo','Tu espacio de aprendizaje en Atenea','texto','panel','2026-07-12 10:39:56'),
(13,'panel_texto_bienvenida','Bienvenido a tu portal','texto','panel','2026-07-12 10:39:56'),
(14,'panel_imagen_banner','','imagen','panel','2026-07-12 10:39:56'),
(15,'panel_imagen_fondo','','imagen','panel','2026-07-12 10:39:56'),
(16,'portal_logo','img/atenea-logo.png','imagen','general','2026-07-12 10:39:56'),
(17,'avatar_predeterminado','src/estudiantes/assets/images/avatars/01.png','imagen','general','2026-07-12 10:39:56'),
(18,'texto_pie_pagina','Atenea Escuela de Naturopat├¡a Hol├¡stica','texto','general','2026-07-12 10:39:56');

-- Información de configuracion_sitio
INSERT INTO `configuracion_sitio` (`id`,`clave`,`valor`,`tipo`,`updated_at`) VALUES
(1,'nombre_sitio','Atenea Escuela de Naturopatía Holística','texto','2026-07-16 22:46:38'),
(2,'logo','img/atenea-logo.png','imagen','2026-07-12 09:44:33'),
(3,'favicon','img/atenea-logo.png','imagen','2026-07-12 09:44:33'),
(4,'correo','ateneanaturopatia@gmail.com','email','2026-07-16 22:46:38'),
(5,'telefono','','telefono','2026-07-12 09:47:01'),
(6,'direccion','Av. El Níspero Final, Huizúcar','texto','2026-07-16 22:46:38'),
(7,'facebook','#','url','2026-07-12 09:44:33'),
(8,'instagram','#','url','2026-07-12 09:44:33'),
(9,'whatsapp','','url','2026-07-12 09:44:33');

-- Información de correo_imap_estado
-- Información de departamentos
INSERT INTO `departamentos` (`id`,`nombre`) VALUES
(1,'Ahuachapán'),
(6,'Cabañas'),
(4,'Chalatenango'),
(5,'Cuscatlán'),
(3,'La Libertad'),
(7,'La Paz'),
(8,'La Unión'),
(14,'Morazán'),
(13,'San Miguel'),
(2,'San Salvador'),
(12,'San Vicente'),
(11,'Santa Ana'),
(10,'Sonsonate'),
(9,'Usulután');

-- Información de dte_correlativos
-- Información de municipios
INSERT INTO `municipios` (`id`,`departamento_id`,`nombre`) VALUES
(2,1,'Ahuachapán Centro'),
(1,1,'Ahuachapán Norte'),
(3,1,'Ahuachapán Sur'),
(7,2,'San Salvador Centro'),
(6,2,'San Salvador Este'),
(4,2,'San Salvador Norte'),
(5,2,'San Salvador Oeste'),
(8,2,'San Salvador Sur'),
(10,3,'La Libertad Centro'),
(13,3,'La Libertad Costa'),
(12,3,'La Libertad Este'),
(9,3,'La Libertad Norte'),
(11,3,'La Libertad Oeste'),
(14,3,'La Libertad Sur'),
(16,4,'Chalatenango Centro'),
(15,4,'Chalatenango Norte'),
(17,4,'Chalatenango Sur'),
(18,5,'Cuscatlán Norte'),
(19,5,'Cuscatlán Sur'),
(20,6,'Cabañas Este'),
(21,6,'Cabañas Oeste'),
(23,7,'La Paz Centro'),
(24,7,'La Paz Este'),
(22,7,'La Paz Oeste'),
(25,8,'La Unión Norte'),
(26,8,'La Unión Sur'),
(28,9,'Usulután Este'),
(27,9,'Usulután Norte'),
(29,9,'Usulután Oeste'),
(31,10,'Sonsonate Centro'),
(32,10,'Sonsonate Este'),
(30,10,'Sonsonate Norte'),
(33,10,'Sonsonate Oeste'),
(35,11,'Santa Ana Centro'),
(36,11,'Santa Ana Este'),
(34,11,'Santa Ana Norte'),
(37,11,'Santa Ana Oeste'),
(38,12,'San Vicente Norte'),
(39,12,'San Vicente Sur'),
(41,13,'San Miguel Centro'),
(40,13,'San Miguel Norte'),
(42,13,'San Miguel Oeste'),
(43,14,'Morazán Norte'),
(44,14,'Morazán Sur');

-- Información de respaldo_index_configuracion_20260717
INSERT INTO `respaldo_index_configuracion_20260717` (`id`,`clave`,`valor`,`tipo`,`updated_at`) VALUES
(1,'nombre_sitio','Atenea Escuela de Naturopatía Holística','texto','2026-07-12 09:44:33'),
(2,'logo','img/atenea-logo.png','imagen','2026-07-12 09:44:33'),
(3,'favicon','img/atenea-logo.png','imagen','2026-07-12 09:44:33'),
(4,'correo','info@atenea.edu.sv','email','2026-07-12 09:44:33'),
(5,'telefono','','telefono','2026-07-12 09:47:01'),
(6,'direccion','El Salvador','texto','2026-07-12 09:44:33'),
(7,'facebook','#','url','2026-07-12 09:44:33'),
(8,'instagram','#','url','2026-07-12 09:44:33'),
(9,'whatsapp','','url','2026-07-12 09:44:33');

-- Información de respaldo_index_elementos_20260717
INSERT INTO `respaldo_index_elementos_20260717` (`id`,`seccion_id`,`titulo`,`subtitulo`,`descripcion`,`imagen`,`icono`,`enlace`,`texto_boton`,`activo`,`orden`,`created_at`,`updated_at`) VALUES
(1,2,'Programas orientados al cuidado integral y preventivo.',NULL,NULL,NULL,'bi-check-circle',NULL,NULL,1,10,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(2,2,'Docentes con experiencia en terapias naturales y bienestar.',NULL,NULL,NULL,'bi-check-circle',NULL,NULL,1,20,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(3,2,'Aprendizaje aplicable a la vida personal y al desarrollo profesional.',NULL,NULL,NULL,'bi-check-circle',NULL,NULL,1,30,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(4,3,'Estudiantes','1200',NULL,NULL,NULL,NULL,NULL,1,10,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(5,3,'Capacitaciones','64',NULL,NULL,NULL,NULL,NULL,1,20,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(6,3,'Eventos','42',NULL,NULL,NULL,NULL,NULL,1,30,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(7,3,'Docentes','24',NULL,NULL,NULL,NULL,NULL,1,40,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(8,4,'Formación integral',NULL,'Contenidos que relacionan conocimientos tradicionales, hábitos saludables y práctica consciente.',NULL,'bi-mortarboard',NULL,NULL,1,10,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(9,4,'Acompañamiento',NULL,'Docentes comprometidos con un proceso de aprendizaje cercano y orientado a resultados.',NULL,'bi-people',NULL,NULL,1,20,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(10,4,'Visión holística',NULL,'Herramientas para promover equilibrio físico, emocional y ambiental de forma responsable.',NULL,'bi-flower1',NULL,NULL,1,30,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(11,5,'Fundamentos de naturopatía',NULL,NULL,NULL,'bi-flower2','src/website/courses.php',NULL,1,10,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(12,5,'Bienestar integral',NULL,NULL,NULL,'bi-heart-pulse','src/website/courses.php',NULL,1,20,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(13,5,'Nutrición consciente',NULL,NULL,NULL,'bi-cup-hot','src/website/courses.php',NULL,1,30,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(14,5,'Plantas y recursos naturales',NULL,NULL,NULL,'bi-tree','src/website/courses.php',NULL,1,40,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(15,5,'Terapias manuales',NULL,NULL,NULL,'bi-person-arms-up','src/website/courses.php',NULL,1,50,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(16,5,'Equilibrio energético',NULL,NULL,NULL,'bi-wind','src/website/courses.php',NULL,1,60,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(17,5,'Certificaciones',NULL,NULL,NULL,'bi-journal-check','src/website/courses.php',NULL,1,70,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(18,5,'Comunidad de aprendizaje',NULL,NULL,NULL,'bi-people','src/website/courses.php',NULL,1,80,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(19,6,'Fundamentos de Naturopatía','Naturopatía','Bases para comprender el bienestar y el cuidado natural desde una perspectiva integral.','src/website/assets/img/course-1.jpg',NULL,'src/website/course-details.php','Ver detalles',1,10,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(20,6,'Bienestar y Equilibrio','Terapias holísticas','Herramientas prácticas para acompañar procesos de autocuidado y hábitos saludables.','src/website/assets/img/course-2.jpg',NULL,'src/website/course-details.php','Ver detalles',1,20,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(21,6,'Recursos Naturales Aplicados','Especialización','Conocimientos para utilizar recursos naturales de manera informada, ética y responsable.','src/website/assets/img/course-3.jpg',NULL,'src/website/course-details.php','Ver detalles',1,30,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(22,7,'Nuevas oportunidades de formación',NULL,'Conoce nuestros próximos programas, talleres y actividades para la comunidad.',NULL,'bi-megaphone','src/website/events.php','Leer más',1,10,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(23,7,'Agenda de eventos holísticos',NULL,'Participa en encuentros diseñados para compartir conocimientos y experiencias de bienestar.',NULL,'bi-calendar-event','src/website/events.php','Leer más',1,20,'2026-07-12 09:44:33','2026-07-12 09:44:33');

-- Información de respaldo_index_secciones_20260717
INSERT INTO `respaldo_index_secciones_20260717` (`id`,`clave`,`nombre`,`titulo`,`subtitulo`,`descripcion`,`imagen`,`boton_texto`,`boton_url`,`activo`,`orden`,`created_at`,`updated_at`) VALUES
(1,'hero','Hero principal','Formación integral para transformar tu bienestar','Capacitaciones, certificaciones y conocimientos enfocados en naturopatía y bienestar holístico.',NULL,'src/website/assets/img/hero-bg.jpg','Ver capacitaciones','src/website/courses.php',1,10,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(2,'nosotros','Nosotros','Conocimiento natural para una vida en equilibrio','Atenea Escuela de Naturopatía Holística impulsa una formación responsable, práctica y humana.',NULL,'src/website/assets/img/about.jpg','Conocer más','src/website/about.php',1,20,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(3,'cifras','Cifras',NULL,NULL,NULL,NULL,NULL,NULL,1,30,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(4,'propuesta','Propuesta de valor','¿Por qué formarte con Atenea?',NULL,'Integramos fundamentos de naturopatía, acompañamiento docente y experiencias prácticas para ayudarte a comprender el bienestar desde una visión completa.',NULL,'Conocer más','src/website/about.php',1,40,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(5,'areas','Áreas de formación',NULL,NULL,NULL,NULL,NULL,NULL,1,50,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(6,'capacitaciones','Capacitaciones','Capacitaciones','Programas destacados',NULL,NULL,'Ver todas las capacitaciones','src/website/courses.php',1,60,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(7,'noticias','Noticias','Noticias','Actualidad de Atenea',NULL,NULL,NULL,NULL,1,70,'2026-07-12 09:44:33','2026-07-12 09:44:33');

-- Información de secciones
INSERT INTO `secciones` (`id`,`clave`,`nombre`,`titulo`,`subtitulo`,`descripcion`,`imagen`,`boton_texto`,`boton_url`,`activo`,`orden`,`created_at`,`updated_at`) VALUES
(1,'hero','Hero principal','“La salud se aprende, el cuerpo sana”','Atenea Escuela de Naturopatía Holística','Atenea Escuela de Naturopatía Holística es una institución enfocada en la capacitación, la divulgación del conocimiento en salud natural y la comercialización de productos alineados con un estilo de vida saludable. Su propuesta combina una escuela online de naturopatía, cursos y certificaciones especializadas y la comercialización de productos naturopáticos, creando un entorno armónico entre salud, capacitación y bienestar.','uploads/contenido/15eb11af85cbcbf6ff9ac747447016de.png','','',1,10,'2026-07-12 09:44:33','2026-07-16 23:03:02'),
(2,'nosotros','Nosotros','¡La mejor opción para tu capacitación!','DESCUBRE MÁS SOBRE NOSOTROS','En ATENEA Escuela, somos una opción educativa comprometida con la formación integral en Naturopatía Holística. Brindamos educación de calidad con un enfoque consciente, ético y humano, creando un entorno de aprendizaje que impulsa el conocimiento, el crecimiento personal y el compromiso con la salud natural y el bienestar integral.','uploads/contenido/migrado/Cara.jpeg','Más información','src/website/about.php',1,30,'2026-07-12 09:44:33','2026-07-16 22:46:38'),
(3,'cifras','Cifras',NULL,NULL,NULL,NULL,NULL,NULL,0,999,'2026-07-12 09:44:33','2026-07-17 21:20:56'),
(4,'propuesta','Nuestros servicios','Lo que ofrecemos en Atenea Escuela','NUESTROS SERVICIOS',NULL,NULL,NULL,NULL,1,20,'2026-07-12 09:44:33','2026-07-16 22:46:38'),
(5,'areas','Galería','Conoce nuestras actividades','GALERÍA',NULL,NULL,'Ver toda la galería','index.php#galeria',1,50,'2026-07-12 09:44:33','2026-07-16 22:46:38'),
(6,'capacitaciones','Capacitaciones destacadas','Formación integral en Naturopatía','CAPACITACIÓN DESTACADA',NULL,NULL,'Ver todas las capacitaciones','src/website/courses.php',1,40,'2026-07-12 09:44:33','2026-07-16 22:46:38'),
(7,'noticias','Noticias','Sección de noticias','ÚLTIMAS NOTICIAS',NULL,NULL,'Ver todas las noticias','src/website/noticias.php',1,60,'2026-07-12 09:44:33','2026-07-17 09:15:23');

-- Información de stripe_eventos
INSERT INTO `stripe_eventos` (`id`,`stripe_event_id`,`tipo`,`procesado`,`error_mensaje`,`created_at`,`procesado_at`) VALUES
(11,'evt_1Tu2yVE8YH5P1jJkiWsofEHs','checkout.session.completed',1,NULL,'2026-07-16 22:02:16','2026-07-16 22:02:16'),
(38,'evt_1TuEarE8YH5P1jJkkFK9gW3A','checkout.session.completed',1,NULL,'2026-07-17 10:26:34','2026-07-17 10:26:34');

-- Información de distritos
INSERT INTO `distritos` (`id`,`municipio_id`,`nombre`) VALUES
(1,1,'Atiquizaya'),
(2,1,'El Refugio'),
(3,1,'San Lorenzo'),
(4,1,'Turín'),
(5,2,'Ahuachapán'),
(6,2,'Apaneca'),
(7,2,'Concepción de Ataco'),
(8,2,'Tacuba'),
(9,3,'Guaymango'),
(10,3,'Jujutla'),
(11,3,'San Francisco Menéndez'),
(12,3,'San Pedro Puxtla'),
(13,4,'Aguilares'),
(14,4,'El Paisnal'),
(15,4,'Guazapa'),
(16,5,'Apopa'),
(17,5,'Nejapa'),
(18,6,'Ilopango'),
(19,6,'San Martín'),
(20,6,'Soyapango'),
(21,6,'Tonacatepeque'),
(22,7,'Ayutuxtepeque'),
(26,7,'Ciudad Delgado'),
(25,7,'Cuscatancingo'),
(23,7,'Mejicanos'),
(24,7,'San Salvador'),
(27,8,'Panchimalco'),
(28,8,'Rosario de Mora'),
(29,8,'San Marcos'),
(31,8,'Santiago Texacuangos'),
(30,8,'Santo Tomás'),
(32,9,'Quezaltepeque'),
(33,9,'San Matías'),
(34,9,'San Pablo Tacachico'),
(36,10,'Ciudad Arce'),
(35,10,'San Juan Opico'),
(37,11,'Colón'),
(38,11,'Jayaque'),
(39,11,'Sacacoyo'),
(41,11,'Talnique'),
(40,11,'Tepecoyo'),
(42,12,'Antiguo Cuscatlán'),
(43,12,'Huizúcar'),
(44,12,'Nuevo Cuscatlán'),
(45,12,'San José Villanueva'),
(46,12,'Zaragoza'),
(47,13,'Chiltuipán'),
(48,13,'Jicalapa'),
(49,13,'La Libertad'),
(50,13,'Tamanique'),
(51,13,'Teotepeque'),
(52,14,'Comasagua'),
(53,14,'Santa Tecla'),
(55,15,'Citalá'),
(54,15,'La Palma'),
(56,15,'San Ignacio'),
(60,16,'Agua Caliente'),
(61,16,'Dulce Nombre de María'),
(62,16,'El Paraíso'),
(59,16,'La Reina'),
(57,16,'Nueva Concepción'),
(66,16,'San Fernando'),
(63,16,'San Francisco Morazán'),
(64,16,'San Rafael'),
(65,16,'Santa Rita'),
(58,16,'Tejutla'),
(68,17,'Arcatao'),
(69,17,'Azacualpa'),
(67,17,'Chalatenango'),
(70,17,'Comalapa'),
(71,17,'Concepción Quezaltepeque'),
(72,17,'El Carrizal'),
(73,17,'La Laguna'),
(74,17,'Las Vueltas'),
(75,17,'Nombre de Jesús'),
(76,17,'Nueva Trinidad'),
(77,17,'Ojos de Agua'),
(78,17,'Potonico'),
(79,17,'San Antonio de La Cruz'),
(80,17,'San Antonio Los Ranchos'),
(81,17,'San Francisco Lempa'),
(82,17,'San Isidro Labrador'),
(83,17,'San José Cancasque'),
(85,17,'San José Las Flores'),
(86,17,'San Luis del Carmen'),
(84,17,'San Miguel de Mercedes'),
(89,18,'Oratorio de Concepción'),
(90,18,'San Bartolomé Perulapán'),
(88,18,'San José Guayabal'),
(91,18,'San Pedro Perulapán'),
(87,18,'Suchitoto'),
(94,19,'Candelaria'),
(92,19,'Cojutepeque'),
(96,19,'El Carmen'),
(100,19,'El Rosario'),
(95,19,'Monte San Juan'),
(97,19,'San Cristóbal'),
(93,19,'San Rafael Cedros'),
(99,19,'San Ramón'),
(101,19,'Santa Cruz Analquito'),
(98,19,'Santa Cruz Michapa'),
(102,19,'Tenancingo'),
(105,20,'Dolores'),
(106,20,'Guacotecti'),
(107,20,'San Isidro'),
(103,20,'Sensuntepeque'),
(104,20,'Victoria'),
(111,21,'Cinquera'),
(110,21,'Jutiapa'),
(108,21,'llobasco'),
(109,21,'Tejutepeque'),
(112,22,'Cuyultitán'),
(113,22,'Olocuilta'),
(118,22,'San Francisco Chinameca'),
(114,22,'San Juan Talpa'),
(115,22,'San Luis Talpa'),
(116,22,'San Pedro Masahuat'),
(117,22,'Tapalhuaca'),
(119,23,'El Rosario'),
(120,23,'Jerusalén'),
(121,23,'Mercedes La Ceiba'),
(122,23,'Paraíso de Osorio'),
(123,23,'San Antonio Masahuat'),
(124,23,'San Emigdio'),
(125,23,'San Juan Tepezontes'),
(126,23,'San Luis La Herradura'),
(127,23,'San Miguel Tepezontes'),
(128,23,'San Pedro Nonualco'),
(129,23,'Santa María Ostuma'),
(130,23,'Santiago Nonualco'),
(131,24,'San Juan Nonualco'),
(132,24,'San Rafael Obrajuelo'),
(133,24,'Zacatecoluca'),
(134,25,'Anamorós'),
(135,25,'Bolívar'),
(136,25,'Concepción de Oriente'),
(137,25,'El Sauce'),
(138,25,'Lislique'),
(139,25,'Nueva Esparta'),
(140,25,'Pasaquina'),
(141,25,'Polorós'),
(142,25,'San José La Fuente'),
(143,25,'Santa Rosa de Lima'),
(144,26,'Conchagua'),
(145,26,'El Carmen'),
(146,26,'Intipucá'),
(147,26,'La Unión'),
(148,26,'Meanguera del Golfo'),
(149,26,'San Alejo'),
(150,26,'Yayantique'),
(151,26,'Yucuaiquín'),
(153,27,'Alegría'),
(154,27,'Berlín'),
(157,27,'El Triunfo'),
(158,27,'Estanzuelas'),
(156,27,'Jucuapa'),
(155,27,'Mercedes Umaña'),
(160,27,'Nueva Granada'),
(159,27,'San Buenaventura'),
(152,27,'Santiago de María'),
(169,28,'California'),
(164,28,'Concepción Batres'),
(170,28,'Ereguayquín'),
(162,28,'Jucuarán'),
(166,28,'Ozatlán'),
(163,28,'San Dionisio'),
(168,28,'Santa Elena'),
(165,28,'Santa María'),
(167,28,'Tecapán'),
(161,28,'Usulután'),
(171,29,'Jiquilisco'),
(172,29,'Puerto El Triunfo'),
(173,29,'San Agustín'),
(174,29,'San Francisco Javier'),
(175,30,'Juayúa'),
(176,30,'Nahuizalco'),
(177,30,'Salcoatitán'),
(178,30,'Santa Catarina Masahuat'),
(181,31,'Nahulingo'),
(182,31,'San Antonio del Monte'),
(183,31,'Santo Domingo de Guzmán'),
(179,31,'Sonsonate'),
(180,31,'Sonzacate'),
(185,32,'Armenia'),
(186,32,'Caluco'),
(188,32,'Cuisnahuat'),
(184,32,'Izalco'),
(187,32,'San Julián'),
(189,32,'Santa Isabel Ishuatán'),
(190,33,'Acajutla'),
(191,34,'Masahuat'),
(192,34,'Metapán'),
(193,34,'Santa Rosa Guachipilín'),
(194,34,'Texistepeque'),
(195,35,'Santa Ana'),
(196,36,'Coatepeque'),
(197,36,'El Congo'),
(198,37,'Candelaria de la Frontera'),
(199,37,'Chalchuapa'),
(200,37,'El Porvenir'),
(201,37,'San Antonio Pajonal'),
(202,37,'San Sebastián Salitrillo'),
(203,37,'Santiago de La Frontera'),
(204,38,'Apastepeque'),
(207,38,'San Esteban Catarina'),
(206,38,'San Ildefonso'),
(209,38,'San Lorenzo'),
(208,38,'San Sebastián'),
(205,38,'Santa Clara'),
(210,38,'Santo Domingo'),
(212,39,'Guadalupe'),
(216,39,'San Cayetano lstepeque'),
(211,39,'San Vicente'),
(215,39,'Tecoluca'),
(214,39,'Tepetitán'),
(213,39,'Verapaz'),
(222,40,'Carolina'),
(224,40,'Chapeltique'),
(217,40,'Ciudad Barrios'),
(219,40,'Nuevo Edén de San Juan'),
(223,40,'San Antonio del Mosco'),
(220,40,'San Gerardo'),
(221,40,'San Luis de La Reina'),
(218,40,'Sesori'),
(230,41,'Chirilagua'),
(226,41,'Comacarán'),
(228,41,'Moncagua'),
(229,41,'Quelepa'),
(225,41,'San Miguel'),
(227,41,'Uluazapa'),
(231,42,'Chinameca'),
(236,42,'El Tránsito'),
(233,42,'Lolotique'),
(232,42,'Nueva Guadalupe'),
(234,42,'San Jorge'),
(235,42,'San Rafael Oriente'),
(237,43,'Arambala'),
(238,43,'Cacaopera'),
(239,43,'Corinto'),
(240,43,'El Rosario'),
(241,43,'Joateca'),
(242,43,'Jocoaitique'),
(243,43,'Meanguera'),
(244,43,'Perquín'),
(245,43,'San Fernando'),
(246,43,'San Isidro'),
(247,43,'Torola'),
(248,44,'Chilanga'),
(249,44,'Delicias de Concepción'),
(250,44,'El Divisadero');
INSERT INTO `distritos` (`id`,`municipio_id`,`nombre`) VALUES
(251,44,'Gualococti'),
(252,44,'Guatajiagua'),
(253,44,'Jocoro'),
(254,44,'Lolotiquillo'),
(255,44,'Osicala'),
(256,44,'San Carlos'),
(257,44,'San Francisco Gotera'),
(258,44,'San Simón'),
(259,44,'Sensembra'),
(260,44,'Sociedad'),
(261,44,'Yamabal'),
(262,44,'Yoloaiquín');

-- Información de elementos_seccion
INSERT INTO `elementos_seccion` (`id`,`seccion_id`,`titulo`,`subtitulo`,`tipo`,`nivel`,`precio`,`duracion`,`instructor`,`descripcion`,`imagen`,`icono`,`enlace`,`texto_boton`,`activo`,`orden`,`created_at`,`updated_at`) VALUES
(4,3,'Estudiantes','1200',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,10,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(5,3,'Capacitaciones','64',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,20,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(6,3,'Eventos','42',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,30,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(7,3,'Docentes','24',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,40,'2026-07-12 09:44:33','2026-07-12 09:44:33'),
(34,4,'Visión',NULL,NULL,NULL,NULL,NULL,NULL,'Ser una institución educativa referente en la formación de profesionales en Naturopatía Holística, promoviendo el conocimiento responsable, ético y consciente de las terapias naturales, con una visión integral del ser humano y respeto por la salud y la vida.',NULL,'bi-eye',NULL,NULL,1,10,'2026-07-16 22:46:38','2026-07-16 22:46:38'),
(35,4,'Misión',NULL,NULL,NULL,NULL,NULL,NULL,'Formar profesionales en Naturopatía Holística con una visión integral del ser humano, brindando educación ética, consciente y de calidad en terapias naturales. Nuestra misión es transmitir conocimiento sólido, responsable y aplicable, que contribuya al bienestar, la prevención y el cuidado de la salud desde un enfoque natural y humano.',NULL,'bi-bullseye',NULL,NULL,1,20,'2026-07-16 22:46:38','2026-07-16 22:46:38'),
(36,4,'Valores',NULL,NULL,NULL,NULL,NULL,NULL,'Nos guiamos por valores fundamentales que constituyen el núcleo de nuestra formación. Promovemos el respeto por la vida y la naturaleza, fomentamos una visión integral del ser humano y cultivamos la ética, la conciencia y la responsabilidad en el ejercicio de las terapias naturales. En nuestra comunidad impulsamos el conocimiento con sentido humano, el respeto mutuo y el compromiso con una salud natural, consciente y digna.',NULL,'bi-heart',NULL,NULL,1,30,'2026-07-16 22:46:38','2026-07-16 22:46:38'),
(37,4,'Servicios',NULL,NULL,NULL,NULL,NULL,NULL,'Ofrecemos formación integral en Naturopatía Holística mediante programas académicos, cursos y capacitaciones terapéuticas, orientados al desarrollo profesional y humano del estudiante. Brindamos educación teórica y práctica en terapias naturales, acompañada de formación ética, legal y deontológica, promoviendo un aprendizaje consciente en un entorno de respeto, responsabilidad y compromiso con la salud integral.',NULL,'bi-journal-medical',NULL,NULL,1,40,'2026-07-16 22:46:38','2026-07-16 22:46:38'),
(38,4,'Historia',NULL,NULL,NULL,NULL,NULL,NULL,'ATENEA Escuela de Naturopatía Holística nace como resultado de un proceso de búsqueda, aprendizaje y evolución en el campo de la salud natural. Desde sus inicios, surge con el propósito de ofrecer una formación consciente y responsable en terapias naturales, integrando conocimiento, ética y una visión holística del ser humano. Cada paso de su creación ha sido parte de un crecimiento constante orientado al bienestar integral y a la profesionalización de la naturopatía.',NULL,'bi-clock-history',NULL,NULL,1,50,'2026-07-16 22:46:38','2026-07-16 22:46:38'),
(39,4,'Equipo Educativo',NULL,NULL,NULL,NULL,NULL,NULL,'Nuestro equipo educativo está conformado por profesionales capacitados en diversas áreas de la Naturopatía y las terapias holísticas, comprometidos con una enseñanza integral, ética y consciente. Trabajamos de manera cercana para acompañar a cada estudiante en su proceso de aprendizaje, promoviendo el conocimiento, la responsabilidad profesional y el respeto por la salud y la vida.',NULL,'bi-people',NULL,NULL,1,60,'2026-07-16 22:46:38','2026-07-16 22:46:38'),
(40,2,'Formación Integral Holística.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'bi-check-circle',NULL,NULL,1,10,'2026-07-16 22:46:38','2026-07-16 22:46:38'),
(41,2,'Excelencia Académica en Naturopatía.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'bi-check-circle',NULL,NULL,1,20,'2026-07-16 22:46:38','2026-07-16 22:46:38'),
(42,2,'Ética, Conciencia y Salud Natural.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'bi-check-circle',NULL,NULL,1,30,'2026-07-16 22:46:38','2026-07-16 22:46:38'),
(43,6,'Introducción a la Naturopatía','CURSO · Básico · $100.00','CURSO','Básico',100.00,'por definir','Dra. María Rodríguez','Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural','img/programa_6976e84aafd1d_1769400394.jpg',NULL,'src/website/capacitacion.php?slug=introduccion-naturopatia','Ver detalles y pagar',1,10,'2026-07-16 22:46:38','2026-07-17 09:58:36'),
(44,6,'Terapias Naturales Avanzadas','CURSO · Intermedio · $100.00','CURSO','Intermedio',100.00,'por definir','Lic. Carlos Méndez','Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos','img/programa_6976e886e2b54_1769400454.jpg',NULL,'src/website/capacitacion.php?slug=terapias-naturales-avanzadas','Ver detalles y pagar',1,20,'2026-07-16 22:46:38','2026-07-17 09:58:36'),
(45,6,'Especialización en Naturopatía Holística','CERTIFICACIÓN · Avanzado · $100.00','CERTIFICACIÓN','Avanzado',100.00,'por definir','Dr. Juan Pérez','Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral','uploads/contenido/migrado/programa_6976e8bae2ba5_1769400506.jpg',NULL,'src/website/capacitacion.php?slug=especializacion-naturopatia-holistica','Ver detalles y pagar',1,30,'2026-07-16 22:46:38','2026-07-17 09:58:36'),
(46,5,'Conoterapia','Terapias',NULL,NULL,NULL,NULL,NULL,NULL,'uploads/contenido/migrado/conoterapia_cajuela.jpeg',NULL,NULL,NULL,1,10,'2026-07-16 22:46:38','2026-07-16 22:46:38'),
(47,5,'Masaje Terapéutico','Terapias',NULL,NULL,NULL,NULL,NULL,NULL,'uploads/contenido/migrado/Masaje.jpeg',NULL,NULL,NULL,1,20,'2026-07-16 22:46:38','2026-07-16 22:46:38'),
(48,5,'Nutrición','Nutrición',NULL,NULL,NULL,NULL,NULL,NULL,'uploads/contenido/migrado/69725ec2808fa_1769103042.jpg',NULL,NULL,NULL,1,30,'2026-07-16 22:46:38','2026-07-16 22:59:49'),
(49,5,'Naturismo','General',NULL,NULL,NULL,NULL,NULL,NULL,'uploads/contenido/migrado/Naturismo.jpeg',NULL,NULL,NULL,1,40,'2026-07-16 22:46:38','2026-07-16 22:46:38'),
(50,5,'Digitopuntura','General',NULL,NULL,NULL,NULL,NULL,NULL,'uploads/contenido/migrado/69725fb23467c_1769103282.jpg',NULL,NULL,NULL,0,50,'2026-07-16 22:46:38','2026-07-17 09:06:36'),
(51,7,'Escuela Atenea','21 de enero de 2026',NULL,NULL,NULL,NULL,NULL,'La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.','uploads/contenido/migrado/noticia_6972a485aa6e4_1769120901.jpg',NULL,'src/website/events.php','Ver más',0,10,'2026-07-16 22:46:38','2026-07-17 09:06:36'),
(52,7,'Conoterapia','15 de mayo de 2024',NULL,NULL,NULL,NULL,NULL,'Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.','uploads/contenido/migrado/noticia_6972a39b01db9_1769120667.jpg',NULL,'src/website/events.php','Ver más',0,20,'2026-07-16 22:46:38','2026-07-17 09:06:36'),
(53,7,'Naturopatía','22 de enero de 2026',NULL,NULL,NULL,NULL,NULL,'En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.','uploads/contenido/migrado/noticia_6972a279886dc_1769120377.jpg',NULL,'src/website/events.php','Ver más',0,30,'2026-07-16 22:46:38','2026-07-17 09:06:36');

-- Información de usuarios
INSERT INTO `usuarios` (`id`,`nombre`,`apellido`,`nombre_usuario`,`correo`,`password`,`google_id`,`proveedor`,`email_verificado`,`perfil_estado`,`terminos_aceptados_at`,`google_registro_iniciado_at`,`rol`,`es_superadmin`,`foto`,`fecha_nacimiento`,`dui`,`codigo_telefono`,`telefono`,`departamento_id`,`municipio_id`,`distrito_id`,`direccion`,`estado`,`ultimo_acceso`,`last_activity_at`,`deleted_at`,`deleted_by`,`deletion_reason`,`deletion_scheduled_at`,`anonymized_at`,`retention_hold`,`under_investigation`,`session_version`,`created_at`,`updated_at`) VALUES
(1,'Administrador','Atenea','usuario1','admin@atenea.local','$2y$10$tZYxPvtkyhKnO/2VpQnQc.YYpwQeE7AN9TZD15bQbhvMjIv34Qd3a',NULL,'local',0,'completo',NULL,NULL,'admin',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'activo','2026-07-20 21:11:06','2026-07-20 21:19:10',NULL,NULL,NULL,NULL,NULL,0,0,1,'2026-07-12 00:10:24','2026-07-20 21:19:10'),
(2,'Estudiante','Prueba','usuario2','usuario@atenea.local','$2y$10$LvTAn24ohthhMmndRScxueAOqiAkJBkt8PO/F2U2qZHh0AV3s/qhy',NULL,'local',0,'completo',NULL,NULL,'usuario',0,NULL,'2005-08-24','06956257-2','+503','61156808',3,14,53,NULL,'activo','2026-07-20 21:27:36','2026-07-20 21:28:57',NULL,NULL,NULL,NULL,NULL,0,0,1,'2026-07-12 00:10:24','2026-07-20 21:28:57'),
(3,'Docente','Prueba','usuario3','docente@atenea.local','$2y$10$TnN5bvlQa/Z3ACzvbVQLHu0fUJ5.Xt20sj1uSb/XgEu6ulKTyBj..',NULL,'local',0,'completo',NULL,NULL,'docente',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'activo','2026-07-20 21:29:16','2026-07-20 21:31:18',NULL,NULL,NULL,NULL,NULL,0,0,1,'2026-07-12 00:10:24','2026-07-20 21:31:18'),
(4,'Usuario','Inactivo','usuario4','inactivo@atenea.local','$2y$10$RKMdNPYoO3k7QyezTPt4ueD8SL1bQHccw6UAGEFivxKTI6t78RVXe',NULL,'local',0,'completo',NULL,NULL,'usuario',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'inactivo',NULL,'2026-07-12 00:10:24',NULL,NULL,NULL,NULL,NULL,0,0,1,'2026-07-12 00:10:24','2026-07-15 12:25:14'),
(51,'Milton','Rivas','milton','rivasmilton513@gmail.com',NULL,'113123385244599858097','google',1,'completo',NULL,NULL,'usuario',0,'https://lh3.googleusercontent.com/a/ACg8ocKTggxBgonu2QRp48xR8ZzMqtOhcm8P4HW95jIViumsRd9xbGpXDA=s96-c',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'activo','2026-07-20 21:19:53','2026-07-20 21:26:49',NULL,NULL,NULL,NULL,NULL,0,0,1,'2026-07-17 00:11:36','2026-07-20 21:26:49');

-- Información de verificaciones_cuenta
-- Información de website_preview_tokens
-- Información de website_publicaciones
INSERT INTO `website_publicaciones` (`id`,`estado`,`contenido_json`,`comentario`,`creado_por`,`publicado_por`,`publicado_at`,`created_at`,`updated_at`) VALUES
(1,'publicado','{\"configuracion_sitio\":[{\"id\":1,\"clave\":\"nombre_sitio\",\"valor\":\"Atenea Escuela de Naturopatía Holística\",\"tipo\":\"texto\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":2,\"clave\":\"logo\",\"valor\":\"img/atenea-logo.png\",\"tipo\":\"imagen\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":3,\"clave\":\"favicon\",\"valor\":\"img/atenea-logo.png\",\"tipo\":\"imagen\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":4,\"clave\":\"correo\",\"valor\":\"ateneanaturopatia@gmail.com\",\"tipo\":\"email\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":5,\"clave\":\"telefono\",\"valor\":\"\",\"tipo\":\"telefono\",\"updated_at\":\"2026-07-12 09:47:01\"},{\"id\":6,\"clave\":\"direccion\",\"valor\":\"Av. El Níspero Final, Huizúcar\",\"tipo\":\"texto\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":7,\"clave\":\"facebook\",\"valor\":\"#\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":8,\"clave\":\"instagram\",\"valor\":\"#\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":9,\"clave\":\"whatsapp\",\"valor\":\"\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"}],\"menu_sitio\":[{\"id\":1,\"texto\":\"Inicio\",\"url\":\"index.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":2,\"texto\":\"Nosotros\",\"url\":\"src/website/about.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":3,\"texto\":\"Capacitaciones\",\"url\":\"src/website/courses.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":4,\"texto\":\"Docentes\",\"url\":\"src/website/trainers.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":5,\"texto\":\"Eventos\",\"url\":\"src/website/events.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":6,\"texto\":\"Productos\",\"url\":\"src/website/pricing.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":7,\"texto\":\"Noticias\",\"url\":\"src/website/noticias.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":70,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":8,\"texto\":\"Contacto\",\"url\":\"src/website/contact.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":80,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"}],\"secciones\":[{\"id\":1,\"clave\":\"hero\",\"nombre\":\"Hero principal\",\"titulo\":\"“La salud se aprende, el cuerpo sana”\",\"subtitulo\":\"Atenea Escuela de Naturopatía Holística\",\"descripcion\":\"Atenea Escuela de Naturopatía Holística es una institución enfocada en la capacitación, la divulgación del conocimiento en salud natural y la comercialización de productos alineados con un estilo de vida saludable. Su propuesta combina una escuela online de naturopatía, cursos y certificaciones especializadas y la comercialización de productos naturopáticos, creando un entorno armónico entre salud, capacitación y bienestar.\",\"imagen\":\"uploads/contenido/15eb11af85cbcbf6ff9ac747447016de.png\",\"boton_texto\":\"\",\"boton_url\":\"\",\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 23:03:02\"},{\"id\":2,\"clave\":\"nosotros\",\"nombre\":\"Nosotros\",\"titulo\":\"¡La mejor opción para tu capacitación!\",\"subtitulo\":\"DESCUBRE MÁS SOBRE NOSOTROS\",\"descripcion\":\"En ATENEA Escuela, somos una opción educativa comprometida con la formación integral en Naturopatía Holística. Brindamos educación de calidad con un enfoque consciente, ético y humano, creando un entorno de aprendizaje que impulsa el conocimiento, el crecimiento personal y el compromiso con la salud natural y el bienestar integral.\",\"imagen\":\"uploads/contenido/migrado/Cara.jpeg\",\"boton_texto\":\"Más información\",\"boton_url\":\"src/website/about.php\",\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":3,\"clave\":\"cifras\",\"nombre\":\"Cifras\",\"titulo\":null,\"subtitulo\":null,\"descripcion\":null,\"imagen\":null,\"boton_texto\":null,\"boton_url\":null,\"activo\":0,\"orden\":999,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":4,\"clave\":\"propuesta\",\"nombre\":\"Nuestros servicios\",\"titulo\":\"Lo que ofrecemos en Atenea Escuela\",\"subtitulo\":\"NUESTROS SERVICIOS\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":null,\"boton_url\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":5,\"clave\":\"areas\",\"nombre\":\"Galería\",\"titulo\":\"Conoce nuestras actividades\",\"subtitulo\":\"GALERÍA\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver toda la galería\",\"boton_url\":\"index.php#galeria\",\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":6,\"clave\":\"capacitaciones\",\"nombre\":\"Capacitaciones destacadas\",\"titulo\":\"Formación integral en Naturopatía\",\"subtitulo\":\"CAPACITACIÓN DESTACADA\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver todas las capacitaciones\",\"boton_url\":\"src/website/courses.php\",\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":7,\"clave\":\"noticias\",\"nombre\":\"Noticias\",\"titulo\":\"Sección de noticias\",\"subtitulo\":\"ÚLTIMAS NOTICIAS\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver todas las noticias\",\"boton_url\":\"src/website/noticias.php\",\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-17 09:15:23\"}],\"elementos_seccion\":[{\"id\":4,\"seccion_id\":3,\"titulo\":\"Estudiantes\",\"subtitulo\":\"1200\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":5,\"seccion_id\":3,\"titulo\":\"Capacitaciones\",\"subtitulo\":\"64\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":6,\"seccion_id\":3,\"titulo\":\"Eventos\",\"subtitulo\":\"42\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":7,\"seccion_id\":3,\"titulo\":\"Docentes\",\"subtitulo\":\"24\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":34,\"seccion_id\":4,\"titulo\":\"Visión\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Ser una institución educativa referente en la formación de profesionales en Naturopatía Holística, promoviendo el conocimiento responsable, ético y consciente de las terapias naturales, con una visión integral del ser humano y respeto por la salud y la vida.\",\"imagen\":null,\"icono\":\"bi-eye\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":35,\"seccion_id\":4,\"titulo\":\"Misión\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Formar profesionales en Naturopatía Holística con una visión integral del ser humano, brindando educación ética, consciente y de calidad en terapias naturales. Nuestra misión es transmitir conocimiento sólido, responsable y aplicable, que contribuya al bienestar, la prevención y el cuidado de la salud desde un enfoque natural y humano.\",\"imagen\":null,\"icono\":\"bi-bullseye\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":36,\"seccion_id\":4,\"titulo\":\"Valores\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Nos guiamos por valores fundamentales que constituyen el núcleo de nuestra formación. Promovemos el respeto por la vida y la naturaleza, fomentamos una visión integral del ser humano y cultivamos la ética, la conciencia y la responsabilidad en el ejercicio de las terapias naturales. En nuestra comunidad impulsamos el conocimiento con sentido humano, el respeto mutuo y el compromiso con una salud natural, consciente y digna.\",\"imagen\":null,\"icono\":\"bi-heart\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":37,\"seccion_id\":4,\"titulo\":\"Servicios\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Ofrecemos formación integral en Naturopatía Holística mediante programas académicos, cursos y capacitaciones terapéuticas, orientados al desarrollo profesional y humano del estudiante. Brindamos educación teórica y práctica en terapias naturales, acompañada de formación ética, legal y deontológica, promoviendo un aprendizaje consciente en un entorno de respeto, responsabilidad y compromiso con la salud integral.\",\"imagen\":null,\"icono\":\"bi-journal-medical\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":38,\"seccion_id\":4,\"titulo\":\"Historia\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"ATENEA Escuela de Naturopatía Holística nace como resultado de un proceso de búsqueda, aprendizaje y evolución en el campo de la salud natural. Desde sus inicios, surge con el propósito de ofrecer una formación consciente y responsable en terapias naturales, integrando conocimiento, ética y una visión holística del ser humano. Cada paso de su creación ha sido parte de un crecimiento constante orientado al bienestar integral y a la profesionalización de la naturopatía.\",\"imagen\":null,\"icono\":\"bi-clock-history\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":39,\"seccion_id\":4,\"titulo\":\"Equipo Educativo\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Nuestro equipo educativo está conformado por profesionales capacitados en diversas áreas de la Naturopatía y las terapias holísticas, comprometidos con una enseñanza integral, ética y consciente. Trabajamos de manera cercana para acompañar a cada estudiante en su proceso de aprendizaje, promoviendo el conocimiento, la responsabilidad profesional y el respeto por la salud y la vida.\",\"imagen\":null,\"icono\":\"bi-people\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":40,\"seccion_id\":2,\"titulo\":\"Formación Integral Holística.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":41,\"seccion_id\":2,\"titulo\":\"Excelencia Académica en Naturopatía.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":42,\"seccion_id\":2,\"titulo\":\"Ética, Conciencia y Salud Natural.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":43,\"seccion_id\":6,\"titulo\":\"Introducción a la Naturopatía\",\"subtitulo\":\"CURSO · Básico · $100.00\",\"tipo\":\"CURSO\",\"nivel\":\"Básico\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Dra. María Rodríguez\",\"descripcion\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"imagen\":\"img/programa_6976e84aafd1d_1769400394.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=introduccion-naturopatia\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":44,\"seccion_id\":6,\"titulo\":\"Terapias Naturales Avanzadas\",\"subtitulo\":\"CURSO · Intermedio · $100.00\",\"tipo\":\"CURSO\",\"nivel\":\"Intermedio\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Lic. Carlos Méndez\",\"descripcion\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"imagen\":\"img/programa_6976e886e2b54_1769400454.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=terapias-naturales-avanzadas\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":45,\"seccion_id\":6,\"titulo\":\"Especialización en Naturopatía Holística\",\"subtitulo\":\"CERTIFICACIÓN · Avanzado · $100.00\",\"tipo\":\"CERTIFICACIÓN\",\"nivel\":\"Avanzado\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Dr. Juan Pérez\",\"descripcion\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"imagen\":\"uploads/contenido/migrado/programa_6976e8bae2ba5_1769400506.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=especializacion-naturopatia-holistica\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":46,\"seccion_id\":5,\"titulo\":\"Conoterapia\",\"subtitulo\":\"Terapias\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/conoterapia_cajuela.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":47,\"seccion_id\":5,\"titulo\":\"Masaje Terapéutico\",\"subtitulo\":\"Terapias\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/Masaje.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":48,\"seccion_id\":5,\"titulo\":\"Nutrición\",\"subtitulo\":\"Nutrición\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/69725ec2808fa_1769103042.jpg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:59:49\"},{\"id\":49,\"seccion_id\":5,\"titulo\":\"Naturismo\",\"subtitulo\":\"General\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/Naturismo.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":50,\"seccion_id\":5,\"titulo\":\"Digitopuntura\",\"subtitulo\":\"General\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/69725fb23467c_1769103282.jpg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":0,\"orden\":50,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":51,\"seccion_id\":7,\"titulo\":\"Escuela Atenea\",\"subtitulo\":\"21 de enero de 2026\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a485aa6e4_1769120901.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":52,\"seccion_id\":7,\"titulo\":\"Conoterapia\",\"subtitulo\":\"15 de mayo de 2024\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a39b01db9_1769120667.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":53,\"seccion_id\":7,\"titulo\":\"Naturopatía\",\"subtitulo\":\"22 de enero de 2026\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a279886dc_1769120377.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"}],\"noticias\":[{\"id\":1,\"titulo\":\"Escuela Atenea\",\"slug\":\"escuela-atenea\",\"resumen\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"contenido\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"imagen_portada\":\"uploads/contenido/migrado/noticia_6972a485aa6e4_1769120901.jpg\",\"fecha_publicacion\":\"2026-01-21 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":2,\"titulo\":\"Conoterapia\",\"slug\":\"conoterapia\",\"resumen\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"contenido\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"imagen_portada\":\"img/noticia_6972a39b01db9_1769120667.jpg\",\"fecha_publicacion\":\"2024-05-15 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:30:06\"},{\"id\":3,\"titulo\":\"Naturopatía\",\"slug\":\"naturopatia\",\"resumen\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"contenido\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"imagen_portada\":\"img/noticia_6972a279886dc_1769120377.jpg\",\"fecha_publicacion\":\"2026-01-22 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:30:06\"}],\"asignaturas\":[{\"id\":6,\"elemento_seccion_id\":43,\"codigo\":\"CAP-43\",\"nombre\":\"Introducción a la Naturopatía\",\"slug\":\"introduccion-naturopatia\",\"descripcion_corta\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"descripcion\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"descripcion_completa\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"imagen\":\"img/programa_6976e84aafd1d_1769400394.jpg\",\"tipo\":\"curso\",\"nivel\":\"Básico\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":0,\"orden\":10,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":7,\"elemento_seccion_id\":44,\"codigo\":\"CAP-44\",\"nombre\":\"Terapias Naturales Avanzadas\",\"slug\":\"terapias-naturales-avanzadas\",\"descripcion_corta\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"descripcion\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"descripcion_completa\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"imagen\":\"img/programa_6976e886e2b54_1769400454.jpg\",\"tipo\":\"curso\",\"nivel\":\"Intermedio\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":0,\"orden\":20,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":8,\"elemento_seccion_id\":45,\"codigo\":\"CAP-45\",\"nombre\":\"Especialización en Naturopatía Holística\",\"slug\":\"especializacion-naturopatia-holistica\",\"descripcion_corta\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"descripcion\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"descripcion_completa\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"imagen\":\"uploads/contenido/migrado/programa_6976e8bae2ba5_1769400506.jpg\",\"tipo\":\"certificacion\",\"nivel\":\"Avanzado\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":1,\"orden\":30,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"}],\"categorias_producto\":[{\"id\":16,\"nombre\":\"Comida\",\"slug\":\"comida\",\"descripcion\":null,\"imagen\":null,\"activo\":1,\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-14 15:37:46\",\"updated_at\":\"2026-07-14 15:37:46\"}],\"productos\":[{\"id\":4,\"categoria_id\":16,\"sku\":null,\"nombre\":\"Dulces de uva\",\"slug\":\"ulces-de-uva-d8bb60c4\",\"descripcion_corta\":\"dulces de uva\",\"descripcion\":\"dulces de uva por unidad de 100\",\"tipo_producto\":\"producto\",\"caracteristicas\":null,\"informacion_entrega\":null,\"precio\":\"95.00\",\"stock\":78,\"stock_reservado\":2,\"stock_minimo\":0,\"disponible\":1,\"activo\":1,\"imagen_principal\":\"uploads/contenido/a47e78dee59243f8dc71c59f3a07ed00.png\",\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-14 15:38:46\",\"updated_at\":\"2026-07-16 22:02:16\"},{\"id\":6,\"categoria_id\":16,\"sku\":null,\"nombre\":\"Prueba34\",\"slug\":\"rueba34-b5932993\",\"descripcion_corta\":\"grgrsrgrg\",\"descripcion\":\"wgrgergeegr\",\"tipo_producto\":\"producto\",\"caracteristicas\":null,\"informacion_entrega\":null,\"precio\":\"100.00\",\"stock\":0,\"stock_reservado\":0,\"stock_minimo\":0,\"disponible\":1,\"activo\":1,\"imagen_principal\":null,\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-15 14:31:30\",\"updated_at\":\"2026-07-15 14:31:30\"}],\"producto_imagenes\":[]}','Versión inicial antes de habilitar borradores',1,1,'2026-07-17 19:13:05','2026-07-17 19:13:05','2026-07-20 22:14:35'),
(2,'borrador','{\"configuracion_sitio\":[{\"id\":1,\"clave\":\"nombre_sitio\",\"valor\":\"Atenea Escuela de Naturopatía Holística\",\"tipo\":\"texto\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":2,\"clave\":\"logo\",\"valor\":\"img/atenea-logo.png\",\"tipo\":\"imagen\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":3,\"clave\":\"favicon\",\"valor\":\"img/atenea-logo.png\",\"tipo\":\"imagen\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":4,\"clave\":\"correo\",\"valor\":\"ateneanaturopatia@gmail.com\",\"tipo\":\"email\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":5,\"clave\":\"telefono\",\"valor\":\"\",\"tipo\":\"telefono\",\"updated_at\":\"2026-07-12 09:47:01\"},{\"id\":6,\"clave\":\"direccion\",\"valor\":\"Av. El Níspero Final, Huizúcar\",\"tipo\":\"texto\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":7,\"clave\":\"facebook\",\"valor\":\"#\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":8,\"clave\":\"instagram\",\"valor\":\"#\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":9,\"clave\":\"whatsapp\",\"valor\":\"\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"}],\"menu_sitio\":[{\"id\":1,\"padre_id\":null,\"texto\":\"Inicio\",\"slug\":\"menu-1\",\"icono\":null,\"url\":\"index.php\",\"nueva_pestana\":0,\"visibilidad\":\"publica\",\"roles_json\":null,\"tipo_contenido\":\"enlace_interno\",\"contenido_html\":null,\"contenido_json\":null,\"activo\":1,\"eliminado_at\":null,\"eliminado_por\":null,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-20 11:43:19\"},{\"id\":2,\"padre_id\":null,\"texto\":\"Nosotros\",\"slug\":\"menu-2\",\"icono\":null,\"url\":\"src/website/about.php\",\"nueva_pestana\":0,\"visibilidad\":\"publica\",\"roles_json\":null,\"tipo_contenido\":\"enlace_interno\",\"contenido_html\":null,\"contenido_json\":null,\"activo\":1,\"eliminado_at\":null,\"eliminado_por\":null,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-20 11:43:19\"},{\"id\":3,\"padre_id\":null,\"texto\":\"Capacitaciones\",\"slug\":\"menu-3\",\"icono\":null,\"url\":\"src/website/courses.php\",\"nueva_pestana\":0,\"visibilidad\":\"publica\",\"roles_json\":null,\"tipo_contenido\":\"enlace_interno\",\"contenido_html\":null,\"contenido_json\":null,\"activo\":1,\"eliminado_at\":null,\"eliminado_por\":null,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-20 11:43:19\"},{\"id\":4,\"padre_id\":null,\"texto\":\"Docentes\",\"slug\":\"menu-4\",\"icono\":null,\"url\":\"src/website/trainers.php\",\"nueva_pestana\":0,\"visibilidad\":\"publica\",\"roles_json\":null,\"tipo_contenido\":\"enlace_interno\",\"contenido_html\":null,\"contenido_json\":null,\"activo\":1,\"eliminado_at\":null,\"eliminado_por\":null,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-20 11:43:19\"},{\"id\":5,\"padre_id\":null,\"texto\":\"Eventos\",\"slug\":\"menu-5\",\"icono\":null,\"url\":\"src/website/events.php\",\"nueva_pestana\":0,\"visibilidad\":\"publica\",\"roles_json\":null,\"tipo_contenido\":\"enlace_interno\",\"contenido_html\":null,\"contenido_json\":null,\"activo\":1,\"eliminado_at\":null,\"eliminado_por\":null,\"orden\":50,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-20 11:43:19\"},{\"id\":6,\"padre_id\":null,\"texto\":\"Productos\",\"slug\":\"menu-6\",\"icono\":null,\"url\":\"src/website/pricing.php\",\"nueva_pestana\":0,\"visibilidad\":\"publica\",\"roles_json\":null,\"tipo_contenido\":\"enlace_interno\",\"contenido_html\":null,\"contenido_json\":null,\"activo\":1,\"eliminado_at\":null,\"eliminado_por\":null,\"orden\":60,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-20 11:43:19\"},{\"id\":7,\"padre_id\":null,\"texto\":\"Noticias\",\"slug\":\"menu-7\",\"icono\":null,\"url\":\"src/website/noticias.php\",\"nueva_pestana\":0,\"visibilidad\":\"publica\",\"roles_json\":null,\"tipo_contenido\":\"enlace_interno\",\"contenido_html\":null,\"contenido_json\":null,\"activo\":1,\"eliminado_at\":null,\"eliminado_por\":null,\"orden\":70,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-20 11:43:19\"},{\"id\":8,\"padre_id\":null,\"texto\":\"Contacto\",\"slug\":\"menu-8\",\"icono\":null,\"url\":\"src/website/contact.php\",\"nueva_pestana\":0,\"visibilidad\":\"publica\",\"roles_json\":null,\"tipo_contenido\":\"enlace_interno\",\"contenido_html\":null,\"contenido_json\":null,\"activo\":1,\"eliminado_at\":null,\"eliminado_por\":null,\"orden\":80,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-20 11:43:19\"}],\"secciones\":[{\"id\":1,\"clave\":\"hero\",\"nombre\":\"Hero principal\",\"titulo\":\"“La salud se aprende, el cuerpo sana”\",\"subtitulo\":\"Atenea Escuela de Naturopatía Holística\",\"descripcion\":\"Atenea Escuela de Naturopatía Holística es una institución enfocada en la capacitación, la divulgación del conocimiento en salud natural y la comercialización de productos alineados con un estilo de vida saludable. Su propuesta combina una escuela online de naturopatía, cursos y certificaciones especializadas y la comercialización de productos naturopáticos, creando un entorno armónico entre salud, capacitación y bienestar.\",\"imagen\":\"uploads/contenido/15eb11af85cbcbf6ff9ac747447016de.png\",\"boton_texto\":\"\",\"boton_url\":\"\",\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 23:03:02\"},{\"id\":2,\"clave\":\"nosotros\",\"nombre\":\"Nosotros\",\"titulo\":\"¡La mejor opción para tu capacitación!\",\"subtitulo\":\"DESCUBRE MÁS SOBRE NOSOTROS\",\"descripcion\":\"En ATENEA Escuela, somos una opción educativa comprometida con la formación integral en Naturopatía Holística. Brindamos educación de calidad con un enfoque consciente, ético y humano, creando un entorno de aprendizaje que impulsa el conocimiento, el crecimiento personal y el compromiso con la salud natural y el bienestar integral.\",\"imagen\":\"uploads/contenido/migrado/Cara.jpeg\",\"boton_texto\":\"Más información\",\"boton_url\":\"src/website/about.php\",\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":3,\"clave\":\"cifras\",\"nombre\":\"Cifras\",\"titulo\":null,\"subtitulo\":null,\"descripcion\":null,\"imagen\":null,\"boton_texto\":null,\"boton_url\":null,\"activo\":0,\"orden\":999,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-17 21:20:56\"},{\"id\":4,\"clave\":\"propuesta\",\"nombre\":\"Nuestros servicios\",\"titulo\":\"Lo que ofrecemos en Atenea Escuela\",\"subtitulo\":\"NUESTROS SERVICIOS\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":null,\"boton_url\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":5,\"clave\":\"areas\",\"nombre\":\"Galería\",\"titulo\":\"Conoce nuestras actividades\",\"subtitulo\":\"GALERÍA\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver toda la galería\",\"boton_url\":\"index.php#galeria\",\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":6,\"clave\":\"capacitaciones\",\"nombre\":\"Capacitaciones destacadas\",\"titulo\":\"Formación integral en Naturopatía\",\"subtitulo\":\"CAPACITACIÓN DESTACADA\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver todas las capacitaciones\",\"boton_url\":\"src/website/courses.php\",\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":7,\"clave\":\"noticias\",\"nombre\":\"Noticias\",\"titulo\":\"Sección de noticias\",\"subtitulo\":\"ÚLTIMAS NOTICIAS\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver todas las noticias\",\"boton_url\":\"src/website/noticias.php\",\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-17 09:15:23\"}],\"elementos_seccion\":[{\"id\":4,\"seccion_id\":3,\"titulo\":\"Estudiantes\",\"subtitulo\":\"1200\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":5,\"seccion_id\":3,\"titulo\":\"Capacitaciones\",\"subtitulo\":\"64\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":6,\"seccion_id\":3,\"titulo\":\"Eventos\",\"subtitulo\":\"42\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":7,\"seccion_id\":3,\"titulo\":\"Docentes\",\"subtitulo\":\"24\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":34,\"seccion_id\":4,\"titulo\":\"Visión\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Ser una institución educativa referente en la formación de profesionales en Naturopatía Holística, promoviendo el conocimiento responsable, ético y consciente de las terapias naturales, con una visión integral del ser humano y respeto por la salud y la vida.\",\"imagen\":null,\"icono\":\"bi-eye\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":35,\"seccion_id\":4,\"titulo\":\"Misión\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Formar profesionales en Naturopatía Holística con una visión integral del ser humano, brindando educación ética, consciente y de calidad en terapias naturales. Nuestra misión es transmitir conocimiento sólido, responsable y aplicable, que contribuya al bienestar, la prevención y el cuidado de la salud desde un enfoque natural y humano.\",\"imagen\":null,\"icono\":\"bi-bullseye\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":36,\"seccion_id\":4,\"titulo\":\"Valores\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Nos guiamos por valores fundamentales que constituyen el núcleo de nuestra formación. Promovemos el respeto por la vida y la naturaleza, fomentamos una visión integral del ser humano y cultivamos la ética, la conciencia y la responsabilidad en el ejercicio de las terapias naturales. En nuestra comunidad impulsamos el conocimiento con sentido humano, el respeto mutuo y el compromiso con una salud natural, consciente y digna.\",\"imagen\":null,\"icono\":\"bi-heart\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":37,\"seccion_id\":4,\"titulo\":\"Servicios\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Ofrecemos formación integral en Naturopatía Holística mediante programas académicos, cursos y capacitaciones terapéuticas, orientados al desarrollo profesional y humano del estudiante. Brindamos educación teórica y práctica en terapias naturales, acompañada de formación ética, legal y deontológica, promoviendo un aprendizaje consciente en un entorno de respeto, responsabilidad y compromiso con la salud integral.\",\"imagen\":null,\"icono\":\"bi-journal-medical\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":38,\"seccion_id\":4,\"titulo\":\"Historia\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"ATENEA Escuela de Naturopatía Holística nace como resultado de un proceso de búsqueda, aprendizaje y evolución en el campo de la salud natural. Desde sus inicios, surge con el propósito de ofrecer una formación consciente y responsable en terapias naturales, integrando conocimiento, ética y una visión holística del ser humano. Cada paso de su creación ha sido parte de un crecimiento constante orientado al bienestar integral y a la profesionalización de la naturopatía.\",\"imagen\":null,\"icono\":\"bi-clock-history\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":39,\"seccion_id\":4,\"titulo\":\"Equipo Educativo\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Nuestro equipo educativo está conformado por profesionales capacitados en diversas áreas de la Naturopatía y las terapias holísticas, comprometidos con una enseñanza integral, ética y consciente. Trabajamos de manera cercana para acompañar a cada estudiante en su proceso de aprendizaje, promoviendo el conocimiento, la responsabilidad profesional y el respeto por la salud y la vida.\",\"imagen\":null,\"icono\":\"bi-people\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":40,\"seccion_id\":2,\"titulo\":\"Formación Integral Holística.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":41,\"seccion_id\":2,\"titulo\":\"Excelencia Académica en Naturopatía.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":42,\"seccion_id\":2,\"titulo\":\"Ética, Conciencia y Salud Natural.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":43,\"seccion_id\":6,\"titulo\":\"Introducción a la Naturopatía\",\"subtitulo\":\"CURSO · Básico · $100.00\",\"tipo\":\"CURSO\",\"nivel\":\"Básico\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Dra. María Rodríguez\",\"descripcion\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"imagen\":\"img/programa_6976e84aafd1d_1769400394.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=introduccion-naturopatia\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":44,\"seccion_id\":6,\"titulo\":\"Terapias Naturales Avanzadas\",\"subtitulo\":\"CURSO · Intermedio · $100.00\",\"tipo\":\"CURSO\",\"nivel\":\"Intermedio\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Lic. Carlos Méndez\",\"descripcion\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"imagen\":\"img/programa_6976e886e2b54_1769400454.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=terapias-naturales-avanzadas\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":45,\"seccion_id\":6,\"titulo\":\"Especialización en Naturopatía Holística\",\"subtitulo\":\"CERTIFICACIÓN · Avanzado · $100.00\",\"tipo\":\"CERTIFICACIÓN\",\"nivel\":\"Avanzado\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Dr. Juan Pérez\",\"descripcion\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"imagen\":\"uploads/contenido/migrado/programa_6976e8bae2ba5_1769400506.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=especializacion-naturopatia-holistica\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":46,\"seccion_id\":5,\"titulo\":\"Conoterapia\",\"subtitulo\":\"Terapias\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/conoterapia_cajuela.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":47,\"seccion_id\":5,\"titulo\":\"Masaje Terapéutico\",\"subtitulo\":\"Terapias\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/Masaje.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":48,\"seccion_id\":5,\"titulo\":\"Nutrición\",\"subtitulo\":\"Nutrición\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/69725ec2808fa_1769103042.jpg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:59:49\"},{\"id\":49,\"seccion_id\":5,\"titulo\":\"Naturismo\",\"subtitulo\":\"General\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/Naturismo.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":50,\"seccion_id\":5,\"titulo\":\"Digitopuntura\",\"subtitulo\":\"General\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/69725fb23467c_1769103282.jpg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":0,\"orden\":50,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":51,\"seccion_id\":7,\"titulo\":\"Escuela Atenea\",\"subtitulo\":\"21 de enero de 2026\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a485aa6e4_1769120901.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":52,\"seccion_id\":7,\"titulo\":\"Conoterapia\",\"subtitulo\":\"15 de mayo de 2024\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a39b01db9_1769120667.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":53,\"seccion_id\":7,\"titulo\":\"Naturopatía\",\"subtitulo\":\"22 de enero de 2026\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a279886dc_1769120377.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"}],\"noticias\":[{\"id\":1,\"titulo\":\"Escuela Atenea\",\"slug\":\"escuela-atenea\",\"resumen\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"contenido\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"imagen_portada\":\"uploads/contenido/migrado/noticia_6972a485aa6e4_1769120901.jpg\",\"fecha_publicacion\":\"2026-01-21 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":2,\"titulo\":\"Conoterapia\",\"slug\":\"conoterapia\",\"resumen\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"contenido\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"imagen_portada\":\"img/noticia_6972a39b01db9_1769120667.jpg\",\"fecha_publicacion\":\"2024-05-15 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:30:06\"},{\"id\":3,\"titulo\":\"Naturopatía\",\"slug\":\"naturopatia\",\"resumen\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"contenido\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"imagen_portada\":\"img/noticia_6972a279886dc_1769120377.jpg\",\"fecha_publicacion\":\"2026-01-22 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:30:06\"}],\"asignaturas\":[{\"id\":6,\"elemento_seccion_id\":43,\"codigo\":\"CAP-43\",\"nombre\":\"Introducción a la Naturopatía\",\"slug\":\"introduccion-naturopatia\",\"descripcion_corta\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"descripcion\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"descripcion_completa\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"imagen\":\"img/programa_6976e84aafd1d_1769400394.jpg\",\"tipo\":\"curso\",\"nivel\":\"Básico\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"asignacion_automatica\":1,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":0,\"orden\":10,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":7,\"elemento_seccion_id\":44,\"codigo\":\"CAP-44\",\"nombre\":\"Terapias Naturales Avanzadas\",\"slug\":\"terapias-naturales-avanzadas\",\"descripcion_corta\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"descripcion\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"descripcion_completa\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"imagen\":\"img/programa_6976e886e2b54_1769400454.jpg\",\"tipo\":\"curso\",\"nivel\":\"Intermedio\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"asignacion_automatica\":1,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":0,\"orden\":20,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":8,\"elemento_seccion_id\":45,\"codigo\":\"CAP-45\",\"nombre\":\"Especialización en Naturopatía Holística\",\"slug\":\"especializacion-naturopatia-holistica\",\"descripcion_corta\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"descripcion\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"descripcion_completa\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"imagen\":\"uploads/contenido/migrado/programa_6976e8bae2ba5_1769400506.jpg\",\"tipo\":\"certificacion\",\"nivel\":\"Avanzado\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"asignacion_automatica\":1,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":1,\"orden\":30,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"}],\"categorias_producto\":[{\"id\":16,\"nombre\":\"Comida\",\"slug\":\"comida\",\"descripcion\":null,\"imagen\":null,\"activo\":1,\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-14 15:37:46\",\"updated_at\":\"2026-07-14 15:37:46\"}],\"productos\":[{\"id\":4,\"categoria_id\":16,\"sku\":null,\"nombre\":\"Dulces de uva\",\"slug\":\"ulces-de-uva-d8bb60c4\",\"descripcion_corta\":\"dulces de uva\",\"descripcion\":\"dulces de uva por unidad de 100\",\"tipo_producto\":\"producto\",\"caracteristicas\":null,\"informacion_entrega\":null,\"precio\":\"95.00\",\"stock\":78,\"stock_reservado\":2,\"stock_minimo\":0,\"disponible\":1,\"activo\":1,\"imagen_principal\":\"uploads/contenido/a47e78dee59243f8dc71c59f3a07ed00.png\",\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-14 15:38:46\",\"updated_at\":\"2026-07-16 22:02:16\"},{\"id\":6,\"categoria_id\":16,\"sku\":null,\"nombre\":\"Prueba34\",\"slug\":\"rueba34-b5932993\",\"descripcion_corta\":\"grgrsrgrg\",\"descripcion\":\"wgrgergeegr\",\"tipo_producto\":\"producto\",\"caracteristicas\":null,\"informacion_entrega\":null,\"precio\":\"100.00\",\"stock\":0,\"stock_reservado\":0,\"stock_minimo\":0,\"disponible\":1,\"activo\":1,\"imagen_principal\":null,\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-15 14:31:30\",\"updated_at\":\"2026-07-15 14:31:30\"}],\"producto_imagenes\":[]}','Restauración de versión 45',1,NULL,NULL,'2026-07-17 19:13:05','2026-07-20 22:14:35');

-- Información de website_versiones
INSERT INTO `website_versiones` (`id`,`administrador_id`,`seccion_modificada`,`datos_anteriores`,`datos_nuevos`,`estado`,`comentario`,`created_at`) VALUES
(17,1,'secciones','{\"configuracion_sitio\":[{\"id\":1,\"clave\":\"nombre_sitio\",\"valor\":\"Atenea Escuela de Naturopatía Holística\",\"tipo\":\"texto\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":2,\"clave\":\"logo\",\"valor\":\"img/atenea-logo.png\",\"tipo\":\"imagen\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":3,\"clave\":\"favicon\",\"valor\":\"img/atenea-logo.png\",\"tipo\":\"imagen\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":4,\"clave\":\"correo\",\"valor\":\"ateneanaturopatia@gmail.com\",\"tipo\":\"email\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":5,\"clave\":\"telefono\",\"valor\":\"\",\"tipo\":\"telefono\",\"updated_at\":\"2026-07-12 09:47:01\"},{\"id\":6,\"clave\":\"direccion\",\"valor\":\"Av. El Níspero Final, Huizúcar\",\"tipo\":\"texto\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":7,\"clave\":\"facebook\",\"valor\":\"#\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":8,\"clave\":\"instagram\",\"valor\":\"#\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":9,\"clave\":\"whatsapp\",\"valor\":\"\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"}],\"menu_sitio\":[{\"id\":1,\"texto\":\"Inicio\",\"url\":\"index.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":2,\"texto\":\"Nosotros\",\"url\":\"src/website/about.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":3,\"texto\":\"Capacitaciones\",\"url\":\"src/website/courses.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":4,\"texto\":\"Docentes\",\"url\":\"src/website/trainers.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":5,\"texto\":\"Eventos\",\"url\":\"src/website/events.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":6,\"texto\":\"Productos\",\"url\":\"src/website/pricing.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":7,\"texto\":\"Noticias\",\"url\":\"src/website/noticias.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":70,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":8,\"texto\":\"Contacto\",\"url\":\"src/website/contact.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":80,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"}],\"secciones\":[{\"id\":1,\"clave\":\"hero\",\"nombre\":\"Hero principal\",\"titulo\":\"“La salud se aprende, el cuerpo sana”\",\"subtitulo\":\"Atenea Escuela de Naturopatía Holística\",\"descripcion\":\"Atenea Escuela de Naturopatía Holística es una institución enfocada en la capacitación, la divulgación del conocimiento en salud natural y la comercialización de productos alineados con un estilo de vida saludable. Su propuesta combina una escuela online de naturopatía, cursos y certificaciones especializadas y la comercialización de productos naturopáticos, creando un entorno armónico entre salud, capacitación y bienestar.\",\"imagen\":\"uploads/contenido/15eb11af85cbcbf6ff9ac747447016de.png\",\"boton_texto\":\"\",\"boton_url\":\"\",\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 23:03:02\"},{\"id\":2,\"clave\":\"nosotros\",\"nombre\":\"Nosotros\",\"titulo\":\"¡La mejor opción para tu capacitación!\",\"subtitulo\":\"DESCUBRE MÁS SOBRE NOSOTROS\",\"descripcion\":\"En ATENEA Escuela, somos una opción educativa comprometida con la formación integral en Naturopatía Holística. Brindamos educación de calidad con un enfoque consciente, ético y humano, creando un entorno de aprendizaje que impulsa el conocimiento, el crecimiento personal y el compromiso con la salud natural y el bienestar integral.\",\"imagen\":\"uploads/contenido/migrado/Cara.jpeg\",\"boton_texto\":\"Más información\",\"boton_url\":\"src/website/about.php\",\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":3,\"clave\":\"cifras\",\"nombre\":\"Cifras\",\"titulo\":null,\"subtitulo\":null,\"descripcion\":null,\"imagen\":null,\"boton_texto\":null,\"boton_url\":null,\"activo\":0,\"orden\":999,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":4,\"clave\":\"propuesta\",\"nombre\":\"Nuestros servicios\",\"titulo\":\"Lo que ofrecemos en Atenea Escuela\",\"subtitulo\":\"NUESTROS SERVICIOS\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":null,\"boton_url\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":5,\"clave\":\"areas\",\"nombre\":\"Galería\",\"titulo\":\"Conoce nuestras actividades\",\"subtitulo\":\"GALERÍA\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver toda la galería\",\"boton_url\":\"index.php#galeria\",\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":6,\"clave\":\"capacitaciones\",\"nombre\":\"Capacitaciones destacadas\",\"titulo\":\"Formación integral en Naturopatía\",\"subtitulo\":\"CAPACITACIÓN DESTACADA\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver todas las capacitaciones\",\"boton_url\":\"src/website/courses.php\",\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":7,\"clave\":\"noticias\",\"nombre\":\"Noticias\",\"titulo\":\"Sección de noticias\",\"subtitulo\":\"ÚLTIMAS NOTICIAS\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver todas las noticias\",\"boton_url\":\"src/website/noticias.php\",\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-17 09:15:23\"}],\"elementos_seccion\":[{\"id\":4,\"seccion_id\":3,\"titulo\":\"Estudiantes\",\"subtitulo\":\"1200\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":5,\"seccion_id\":3,\"titulo\":\"Capacitaciones\",\"subtitulo\":\"64\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":6,\"seccion_id\":3,\"titulo\":\"Eventos\",\"subtitulo\":\"42\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":7,\"seccion_id\":3,\"titulo\":\"Docentes\",\"subtitulo\":\"24\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":34,\"seccion_id\":4,\"titulo\":\"Visión\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Ser una institución educativa referente en la formación de profesionales en Naturopatía Holística, promoviendo el conocimiento responsable, ético y consciente de las terapias naturales, con una visión integral del ser humano y respeto por la salud y la vida.\",\"imagen\":null,\"icono\":\"bi-eye\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":35,\"seccion_id\":4,\"titulo\":\"Misión\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Formar profesionales en Naturopatía Holística con una visión integral del ser humano, brindando educación ética, consciente y de calidad en terapias naturales. Nuestra misión es transmitir conocimiento sólido, responsable y aplicable, que contribuya al bienestar, la prevención y el cuidado de la salud desde un enfoque natural y humano.\",\"imagen\":null,\"icono\":\"bi-bullseye\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":36,\"seccion_id\":4,\"titulo\":\"Valores\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Nos guiamos por valores fundamentales que constituyen el núcleo de nuestra formación. Promovemos el respeto por la vida y la naturaleza, fomentamos una visión integral del ser humano y cultivamos la ética, la conciencia y la responsabilidad en el ejercicio de las terapias naturales. En nuestra comunidad impulsamos el conocimiento con sentido humano, el respeto mutuo y el compromiso con una salud natural, consciente y digna.\",\"imagen\":null,\"icono\":\"bi-heart\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":37,\"seccion_id\":4,\"titulo\":\"Servicios\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Ofrecemos formación integral en Naturopatía Holística mediante programas académicos, cursos y capacitaciones terapéuticas, orientados al desarrollo profesional y humano del estudiante. Brindamos educación teórica y práctica en terapias naturales, acompañada de formación ética, legal y deontológica, promoviendo un aprendizaje consciente en un entorno de respeto, responsabilidad y compromiso con la salud integral.\",\"imagen\":null,\"icono\":\"bi-journal-medical\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":38,\"seccion_id\":4,\"titulo\":\"Historia\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"ATENEA Escuela de Naturopatía Holística nace como resultado de un proceso de búsqueda, aprendizaje y evolución en el campo de la salud natural. Desde sus inicios, surge con el propósito de ofrecer una formación consciente y responsable en terapias naturales, integrando conocimiento, ética y una visión holística del ser humano. Cada paso de su creación ha sido parte de un crecimiento constante orientado al bienestar integral y a la profesionalización de la naturopatía.\",\"imagen\":null,\"icono\":\"bi-clock-history\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":39,\"seccion_id\":4,\"titulo\":\"Equipo Educativo\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Nuestro equipo educativo está conformado por profesionales capacitados en diversas áreas de la Naturopatía y las terapias holísticas, comprometidos con una enseñanza integral, ética y consciente. Trabajamos de manera cercana para acompañar a cada estudiante en su proceso de aprendizaje, promoviendo el conocimiento, la responsabilidad profesional y el respeto por la salud y la vida.\",\"imagen\":null,\"icono\":\"bi-people\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":40,\"seccion_id\":2,\"titulo\":\"Formación Integral Holística.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":41,\"seccion_id\":2,\"titulo\":\"Excelencia Académica en Naturopatía.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":42,\"seccion_id\":2,\"titulo\":\"Ética, Conciencia y Salud Natural.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":43,\"seccion_id\":6,\"titulo\":\"Introducción a la Naturopatía\",\"subtitulo\":\"CURSO · Básico · $100.00\",\"tipo\":\"CURSO\",\"nivel\":\"Básico\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Dra. María Rodríguez\",\"descripcion\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"imagen\":\"img/programa_6976e84aafd1d_1769400394.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=introduccion-naturopatia\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":44,\"seccion_id\":6,\"titulo\":\"Terapias Naturales Avanzadas\",\"subtitulo\":\"CURSO · Intermedio · $100.00\",\"tipo\":\"CURSO\",\"nivel\":\"Intermedio\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Lic. Carlos Méndez\",\"descripcion\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"imagen\":\"img/programa_6976e886e2b54_1769400454.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=terapias-naturales-avanzadas\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":45,\"seccion_id\":6,\"titulo\":\"Especialización en Naturopatía Holística\",\"subtitulo\":\"CERTIFICACIÓN · Avanzado · $100.00\",\"tipo\":\"CERTIFICACIÓN\",\"nivel\":\"Avanzado\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Dr. Juan Pérez\",\"descripcion\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"imagen\":\"uploads/contenido/migrado/programa_6976e8bae2ba5_1769400506.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=especializacion-naturopatia-holistica\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":46,\"seccion_id\":5,\"titulo\":\"Conoterapia\",\"subtitulo\":\"Terapias\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/conoterapia_cajuela.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":47,\"seccion_id\":5,\"titulo\":\"Masaje Terapéutico\",\"subtitulo\":\"Terapias\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/Masaje.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":48,\"seccion_id\":5,\"titulo\":\"Nutrición\",\"subtitulo\":\"Nutrición\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/69725ec2808fa_1769103042.jpg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:59:49\"},{\"id\":49,\"seccion_id\":5,\"titulo\":\"Naturismo\",\"subtitulo\":\"General\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/Naturismo.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":50,\"seccion_id\":5,\"titulo\":\"Digitopuntura\",\"subtitulo\":\"General\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/69725fb23467c_1769103282.jpg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":0,\"orden\":50,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":51,\"seccion_id\":7,\"titulo\":\"Escuela Atenea\",\"subtitulo\":\"21 de enero de 2026\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a485aa6e4_1769120901.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":52,\"seccion_id\":7,\"titulo\":\"Conoterapia\",\"subtitulo\":\"15 de mayo de 2024\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a39b01db9_1769120667.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":53,\"seccion_id\":7,\"titulo\":\"Naturopatía\",\"subtitulo\":\"22 de enero de 2026\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a279886dc_1769120377.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"}],\"noticias\":[{\"id\":1,\"titulo\":\"Escuela Atenea\",\"slug\":\"escuela-atenea\",\"resumen\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"contenido\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"imagen_portada\":\"uploads/contenido/migrado/noticia_6972a485aa6e4_1769120901.jpg\",\"fecha_publicacion\":\"2026-01-21 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":2,\"titulo\":\"Conoterapia\",\"slug\":\"conoterapia\",\"resumen\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"contenido\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"imagen_portada\":\"img/noticia_6972a39b01db9_1769120667.jpg\",\"fecha_publicacion\":\"2024-05-15 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:30:06\"},{\"id\":3,\"titulo\":\"Naturopatía\",\"slug\":\"naturopatia\",\"resumen\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"contenido\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"imagen_portada\":\"img/noticia_6972a279886dc_1769120377.jpg\",\"fecha_publicacion\":\"2026-01-22 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:30:06\"}],\"asignaturas\":[{\"id\":6,\"elemento_seccion_id\":43,\"codigo\":\"CAP-43\",\"nombre\":\"Introducción a la Naturopatía\",\"slug\":\"introduccion-naturopatia\",\"descripcion_corta\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"descripcion\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"descripcion_completa\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"imagen\":\"img/programa_6976e84aafd1d_1769400394.jpg\",\"tipo\":\"curso\",\"nivel\":\"Básico\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":0,\"orden\":10,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":7,\"elemento_seccion_id\":44,\"codigo\":\"CAP-44\",\"nombre\":\"Terapias Naturales Avanzadas\",\"slug\":\"terapias-naturales-avanzadas\",\"descripcion_corta\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"descripcion\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"descripcion_completa\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"imagen\":\"img/programa_6976e886e2b54_1769400454.jpg\",\"tipo\":\"curso\",\"nivel\":\"Intermedio\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":0,\"orden\":20,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":8,\"elemento_seccion_id\":45,\"codigo\":\"CAP-45\",\"nombre\":\"Especialización en Naturopatía Holística\",\"slug\":\"especializacion-naturopatia-holistica\",\"descripcion_corta\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"descripcion\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"descripcion_completa\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"imagen\":\"uploads/contenido/migrado/programa_6976e8bae2ba5_1769400506.jpg\",\"tipo\":\"certificacion\",\"nivel\":\"Avanzado\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":1,\"orden\":30,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"}],\"categorias_producto\":[{\"id\":16,\"nombre\":\"Comida\",\"slug\":\"comida\",\"descripcion\":null,\"imagen\":null,\"activo\":1,\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-14 15:37:46\",\"updated_at\":\"2026-07-14 15:37:46\"}],\"productos\":[{\"id\":4,\"categoria_id\":16,\"sku\":null,\"nombre\":\"Dulces de uva\",\"slug\":\"ulces-de-uva-d8bb60c4\",\"descripcion_corta\":\"dulces de uva\",\"descripcion\":\"dulces de uva por unidad de 100\",\"tipo_producto\":\"producto\",\"caracteristicas\":null,\"informacion_entrega\":null,\"precio\":\"95.00\",\"stock\":78,\"stock_reservado\":2,\"stock_minimo\":0,\"disponible\":1,\"activo\":1,\"imagen_principal\":\"uploads/contenido/a47e78dee59243f8dc71c59f3a07ed00.png\",\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-14 15:38:46\",\"updated_at\":\"2026-07-16 22:02:16\"},{\"id\":6,\"categoria_id\":16,\"sku\":null,\"nombre\":\"Prueba34\",\"slug\":\"rueba34-b5932993\",\"descripcion_corta\":\"grgrsrgrg\",\"descripcion\":\"wgrgergeegr\",\"tipo_producto\":\"producto\",\"caracteristicas\":null,\"informacion_entrega\":null,\"precio\":\"100.00\",\"stock\":0,\"stock_reservado\":0,\"stock_minimo\":0,\"disponible\":1,\"activo\":1,\"imagen_principal\":null,\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-15 14:31:30\",\"updated_at\":\"2026-07-15 14:31:30\"}],\"producto_imagenes\":[]}','{\"configuracion_sitio\":[{\"id\":1,\"clave\":\"nombre_sitio\",\"valor\":\"Atenea Escuela de Naturopatía Holística\",\"tipo\":\"texto\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":2,\"clave\":\"logo\",\"valor\":\"img/atenea-logo.png\",\"tipo\":\"imagen\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":3,\"clave\":\"favicon\",\"valor\":\"img/atenea-logo.png\",\"tipo\":\"imagen\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":4,\"clave\":\"correo\",\"valor\":\"ateneanaturopatia@gmail.com\",\"tipo\":\"email\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":5,\"clave\":\"telefono\",\"valor\":\"\",\"tipo\":\"telefono\",\"updated_at\":\"2026-07-12 09:47:01\"},{\"id\":6,\"clave\":\"direccion\",\"valor\":\"Av. El Níspero Final, Huizúcar\",\"tipo\":\"texto\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":7,\"clave\":\"facebook\",\"valor\":\"#\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":8,\"clave\":\"instagram\",\"valor\":\"#\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":9,\"clave\":\"whatsapp\",\"valor\":\"\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"}],\"menu_sitio\":[{\"id\":1,\"texto\":\"Inicio\",\"url\":\"index.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":2,\"texto\":\"Nosotros\",\"url\":\"src/website/about.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":3,\"texto\":\"Capacitaciones\",\"url\":\"src/website/courses.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":4,\"texto\":\"Docentes\",\"url\":\"src/website/trainers.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":5,\"texto\":\"Eventos\",\"url\":\"src/website/events.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":6,\"texto\":\"Productos\",\"url\":\"src/website/pricing.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":7,\"texto\":\"Noticias\",\"url\":\"src/website/noticias.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":70,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":8,\"texto\":\"Contacto\",\"url\":\"src/website/contact.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":80,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"}],\"secciones\":[{\"id\":1,\"clave\":\"hero\",\"nombre\":\"Hero principal\",\"titulo\":\"“La salud se aprende, el cuerpo sana”\",\"subtitulo\":\"Atenea Escuela de Naturopatía Holística\",\"descripcion\":\"Atenea Escuela de Naturopatía Holística es una institución enfocada en la capacitación, la divulgación del conocimiento en salud natural y la comercialización de productos alineados con un estilo de vida saludable. Su propuesta combina una escuela online de naturopatía, cursos y certificaciones especializadas y la comercialización de productos naturopáticos, creando un entorno armónico entre salud, capacitación y bienestar.\",\"imagen\":\"uploads/contenido/15eb11af85cbcbf6ff9ac747447016de.png\",\"boton_texto\":\"\",\"boton_url\":\"\",\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 23:03:02\"},{\"id\":2,\"clave\":\"nosotros\",\"nombre\":\"Nosotros\",\"titulo\":\"¡La mejor opción para tu capacitación!\",\"subtitulo\":\"DESCUBRE MÁS SOBRE NOSOTROS\",\"descripcion\":\"En ATENEA Escuela, somos una opción educativa comprometida con la formación integral en Naturopatía Holística. Brindamos educación de calidad con un enfoque consciente, ético y humano, creando un entorno de aprendizaje que impulsa el conocimiento, el crecimiento personal y el compromiso con la salud natural y el bienestar integral.\",\"imagen\":\"uploads/contenido/migrado/Cara.jpeg\",\"boton_texto\":\"Más información\",\"boton_url\":\"src/website/about.php\",\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":3,\"clave\":\"cifras\",\"nombre\":\"Cifras\",\"titulo\":null,\"subtitulo\":null,\"descripcion\":null,\"imagen\":null,\"boton_texto\":null,\"boton_url\":null,\"activo\":1,\"orden\":999,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-17 21:20:27\"},{\"id\":4,\"clave\":\"propuesta\",\"nombre\":\"Nuestros servicios\",\"titulo\":\"Lo que ofrecemos en Atenea Escuela\",\"subtitulo\":\"NUESTROS SERVICIOS\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":null,\"boton_url\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":5,\"clave\":\"areas\",\"nombre\":\"Galería\",\"titulo\":\"Conoce nuestras actividades\",\"subtitulo\":\"GALERÍA\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver toda la galería\",\"boton_url\":\"index.php#galeria\",\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":6,\"clave\":\"capacitaciones\",\"nombre\":\"Capacitaciones destacadas\",\"titulo\":\"Formación integral en Naturopatía\",\"subtitulo\":\"CAPACITACIÓN DESTACADA\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver todas las capacitaciones\",\"boton_url\":\"src/website/courses.php\",\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":7,\"clave\":\"noticias\",\"nombre\":\"Noticias\",\"titulo\":\"Sección de noticias\",\"subtitulo\":\"ÚLTIMAS NOTICIAS\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver todas las noticias\",\"boton_url\":\"src/website/noticias.php\",\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-17 09:15:23\"}],\"elementos_seccion\":[{\"id\":4,\"seccion_id\":3,\"titulo\":\"Estudiantes\",\"subtitulo\":\"1200\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":5,\"seccion_id\":3,\"titulo\":\"Capacitaciones\",\"subtitulo\":\"64\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":6,\"seccion_id\":3,\"titulo\":\"Eventos\",\"subtitulo\":\"42\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":7,\"seccion_id\":3,\"titulo\":\"Docentes\",\"subtitulo\":\"24\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":34,\"seccion_id\":4,\"titulo\":\"Visión\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Ser una institución educativa referente en la formación de profesionales en Naturopatía Holística, promoviendo el conocimiento responsable, ético y consciente de las terapias naturales, con una visión integral del ser humano y respeto por la salud y la vida.\",\"imagen\":null,\"icono\":\"bi-eye\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":35,\"seccion_id\":4,\"titulo\":\"Misión\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Formar profesionales en Naturopatía Holística con una visión integral del ser humano, brindando educación ética, consciente y de calidad en terapias naturales. Nuestra misión es transmitir conocimiento sólido, responsable y aplicable, que contribuya al bienestar, la prevención y el cuidado de la salud desde un enfoque natural y humano.\",\"imagen\":null,\"icono\":\"bi-bullseye\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":36,\"seccion_id\":4,\"titulo\":\"Valores\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Nos guiamos por valores fundamentales que constituyen el núcleo de nuestra formación. Promovemos el respeto por la vida y la naturaleza, fomentamos una visión integral del ser humano y cultivamos la ética, la conciencia y la responsabilidad en el ejercicio de las terapias naturales. En nuestra comunidad impulsamos el conocimiento con sentido humano, el respeto mutuo y el compromiso con una salud natural, consciente y digna.\",\"imagen\":null,\"icono\":\"bi-heart\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":37,\"seccion_id\":4,\"titulo\":\"Servicios\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Ofrecemos formación integral en Naturopatía Holística mediante programas académicos, cursos y capacitaciones terapéuticas, orientados al desarrollo profesional y humano del estudiante. Brindamos educación teórica y práctica en terapias naturales, acompañada de formación ética, legal y deontológica, promoviendo un aprendizaje consciente en un entorno de respeto, responsabilidad y compromiso con la salud integral.\",\"imagen\":null,\"icono\":\"bi-journal-medical\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":38,\"seccion_id\":4,\"titulo\":\"Historia\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"ATENEA Escuela de Naturopatía Holística nace como resultado de un proceso de búsqueda, aprendizaje y evolución en el campo de la salud natural. Desde sus inicios, surge con el propósito de ofrecer una formación consciente y responsable en terapias naturales, integrando conocimiento, ética y una visión holística del ser humano. Cada paso de su creación ha sido parte de un crecimiento constante orientado al bienestar integral y a la profesionalización de la naturopatía.\",\"imagen\":null,\"icono\":\"bi-clock-history\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":39,\"seccion_id\":4,\"titulo\":\"Equipo Educativo\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Nuestro equipo educativo está conformado por profesionales capacitados en diversas áreas de la Naturopatía y las terapias holísticas, comprometidos con una enseñanza integral, ética y consciente. Trabajamos de manera cercana para acompañar a cada estudiante en su proceso de aprendizaje, promoviendo el conocimiento, la responsabilidad profesional y el respeto por la salud y la vida.\",\"imagen\":null,\"icono\":\"bi-people\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":40,\"seccion_id\":2,\"titulo\":\"Formación Integral Holística.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":41,\"seccion_id\":2,\"titulo\":\"Excelencia Académica en Naturopatía.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":42,\"seccion_id\":2,\"titulo\":\"Ética, Conciencia y Salud Natural.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":43,\"seccion_id\":6,\"titulo\":\"Introducción a la Naturopatía\",\"subtitulo\":\"CURSO · Básico · $100.00\",\"tipo\":\"CURSO\",\"nivel\":\"Básico\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Dra. María Rodríguez\",\"descripcion\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"imagen\":\"img/programa_6976e84aafd1d_1769400394.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=introduccion-naturopatia\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":44,\"seccion_id\":6,\"titulo\":\"Terapias Naturales Avanzadas\",\"subtitulo\":\"CURSO · Intermedio · $100.00\",\"tipo\":\"CURSO\",\"nivel\":\"Intermedio\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Lic. Carlos Méndez\",\"descripcion\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"imagen\":\"img/programa_6976e886e2b54_1769400454.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=terapias-naturales-avanzadas\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":45,\"seccion_id\":6,\"titulo\":\"Especialización en Naturopatía Holística\",\"subtitulo\":\"CERTIFICACIÓN · Avanzado · $100.00\",\"tipo\":\"CERTIFICACIÓN\",\"nivel\":\"Avanzado\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Dr. Juan Pérez\",\"descripcion\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"imagen\":\"uploads/contenido/migrado/programa_6976e8bae2ba5_1769400506.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=especializacion-naturopatia-holistica\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":46,\"seccion_id\":5,\"titulo\":\"Conoterapia\",\"subtitulo\":\"Terapias\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/conoterapia_cajuela.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":47,\"seccion_id\":5,\"titulo\":\"Masaje Terapéutico\",\"subtitulo\":\"Terapias\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/Masaje.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":48,\"seccion_id\":5,\"titulo\":\"Nutrición\",\"subtitulo\":\"Nutrición\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/69725ec2808fa_1769103042.jpg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:59:49\"},{\"id\":49,\"seccion_id\":5,\"titulo\":\"Naturismo\",\"subtitulo\":\"General\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/Naturismo.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":50,\"seccion_id\":5,\"titulo\":\"Digitopuntura\",\"subtitulo\":\"General\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/69725fb23467c_1769103282.jpg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":0,\"orden\":50,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":51,\"seccion_id\":7,\"titulo\":\"Escuela Atenea\",\"subtitulo\":\"21 de enero de 2026\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a485aa6e4_1769120901.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":52,\"seccion_id\":7,\"titulo\":\"Conoterapia\",\"subtitulo\":\"15 de mayo de 2024\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a39b01db9_1769120667.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":53,\"seccion_id\":7,\"titulo\":\"Naturopatía\",\"subtitulo\":\"22 de enero de 2026\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a279886dc_1769120377.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"}],\"noticias\":[{\"id\":1,\"titulo\":\"Escuela Atenea\",\"slug\":\"escuela-atenea\",\"resumen\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"contenido\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"imagen_portada\":\"uploads/contenido/migrado/noticia_6972a485aa6e4_1769120901.jpg\",\"fecha_publicacion\":\"2026-01-21 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":2,\"titulo\":\"Conoterapia\",\"slug\":\"conoterapia\",\"resumen\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"contenido\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"imagen_portada\":\"img/noticia_6972a39b01db9_1769120667.jpg\",\"fecha_publicacion\":\"2024-05-15 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:30:06\"},{\"id\":3,\"titulo\":\"Naturopatía\",\"slug\":\"naturopatia\",\"resumen\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"contenido\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"imagen_portada\":\"img/noticia_6972a279886dc_1769120377.jpg\",\"fecha_publicacion\":\"2026-01-22 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:30:06\"}],\"asignaturas\":[{\"id\":6,\"elemento_seccion_id\":43,\"codigo\":\"CAP-43\",\"nombre\":\"Introducción a la Naturopatía\",\"slug\":\"introduccion-naturopatia\",\"descripcion_corta\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"descripcion\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"descripcion_completa\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"imagen\":\"img/programa_6976e84aafd1d_1769400394.jpg\",\"tipo\":\"curso\",\"nivel\":\"Básico\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":0,\"orden\":10,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":7,\"elemento_seccion_id\":44,\"codigo\":\"CAP-44\",\"nombre\":\"Terapias Naturales Avanzadas\",\"slug\":\"terapias-naturales-avanzadas\",\"descripcion_corta\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"descripcion\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"descripcion_completa\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"imagen\":\"img/programa_6976e886e2b54_1769400454.jpg\",\"tipo\":\"curso\",\"nivel\":\"Intermedio\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":0,\"orden\":20,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":8,\"elemento_seccion_id\":45,\"codigo\":\"CAP-45\",\"nombre\":\"Especialización en Naturopatía Holística\",\"slug\":\"especializacion-naturopatia-holistica\",\"descripcion_corta\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"descripcion\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"descripcion_completa\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"imagen\":\"uploads/contenido/migrado/programa_6976e8bae2ba5_1769400506.jpg\",\"tipo\":\"certificacion\",\"nivel\":\"Avanzado\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":1,\"orden\":30,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"}],\"categorias_producto\":[{\"id\":16,\"nombre\":\"Comida\",\"slug\":\"comida\",\"descripcion\":null,\"imagen\":null,\"activo\":1,\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-14 15:37:46\",\"updated_at\":\"2026-07-14 15:37:46\"}],\"productos\":[{\"id\":4,\"categoria_id\":16,\"sku\":null,\"nombre\":\"Dulces de uva\",\"slug\":\"ulces-de-uva-d8bb60c4\",\"descripcion_corta\":\"dulces de uva\",\"descripcion\":\"dulces de uva por unidad de 100\",\"tipo_producto\":\"producto\",\"caracteristicas\":null,\"informacion_entrega\":null,\"precio\":\"95.00\",\"stock\":78,\"stock_reservado\":2,\"stock_minimo\":0,\"disponible\":1,\"activo\":1,\"imagen_principal\":\"uploads/contenido/a47e78dee59243f8dc71c59f3a07ed00.png\",\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-14 15:38:46\",\"updated_at\":\"2026-07-16 22:02:16\"},{\"id\":6,\"categoria_id\":16,\"sku\":null,\"nombre\":\"Prueba34\",\"slug\":\"rueba34-b5932993\",\"descripcion_corta\":\"grgrsrgrg\",\"descripcion\":\"wgrgergeegr\",\"tipo_producto\":\"producto\",\"caracteristicas\":null,\"informacion_entrega\":null,\"precio\":\"100.00\",\"stock\":0,\"stock_reservado\":0,\"stock_minimo\":0,\"disponible\":1,\"activo\":1,\"imagen_principal\":null,\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-15 14:31:30\",\"updated_at\":\"2026-07-15 14:31:30\"}],\"producto_imagenes\":[]}','borrador','Cambio guardado como borrador','2026-07-17 21:20:27'),
(18,1,'secciones','{\"configuracion_sitio\":[{\"id\":1,\"clave\":\"nombre_sitio\",\"valor\":\"Atenea Escuela de Naturopatía Holística\",\"tipo\":\"texto\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":2,\"clave\":\"logo\",\"valor\":\"img/atenea-logo.png\",\"tipo\":\"imagen\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":3,\"clave\":\"favicon\",\"valor\":\"img/atenea-logo.png\",\"tipo\":\"imagen\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":4,\"clave\":\"correo\",\"valor\":\"ateneanaturopatia@gmail.com\",\"tipo\":\"email\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":5,\"clave\":\"telefono\",\"valor\":\"\",\"tipo\":\"telefono\",\"updated_at\":\"2026-07-12 09:47:01\"},{\"id\":6,\"clave\":\"direccion\",\"valor\":\"Av. El Níspero Final, Huizúcar\",\"tipo\":\"texto\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":7,\"clave\":\"facebook\",\"valor\":\"#\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":8,\"clave\":\"instagram\",\"valor\":\"#\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":9,\"clave\":\"whatsapp\",\"valor\":\"\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"}],\"menu_sitio\":[{\"id\":1,\"texto\":\"Inicio\",\"url\":\"index.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":2,\"texto\":\"Nosotros\",\"url\":\"src/website/about.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":3,\"texto\":\"Capacitaciones\",\"url\":\"src/website/courses.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":4,\"texto\":\"Docentes\",\"url\":\"src/website/trainers.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":5,\"texto\":\"Eventos\",\"url\":\"src/website/events.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":6,\"texto\":\"Productos\",\"url\":\"src/website/pricing.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":7,\"texto\":\"Noticias\",\"url\":\"src/website/noticias.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":70,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":8,\"texto\":\"Contacto\",\"url\":\"src/website/contact.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":80,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"}],\"secciones\":[{\"id\":1,\"clave\":\"hero\",\"nombre\":\"Hero principal\",\"titulo\":\"“La salud se aprende, el cuerpo sana”\",\"subtitulo\":\"Atenea Escuela de Naturopatía Holística\",\"descripcion\":\"Atenea Escuela de Naturopatía Holística es una institución enfocada en la capacitación, la divulgación del conocimiento en salud natural y la comercialización de productos alineados con un estilo de vida saludable. Su propuesta combina una escuela online de naturopatía, cursos y certificaciones especializadas y la comercialización de productos naturopáticos, creando un entorno armónico entre salud, capacitación y bienestar.\",\"imagen\":\"uploads/contenido/15eb11af85cbcbf6ff9ac747447016de.png\",\"boton_texto\":\"\",\"boton_url\":\"\",\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 23:03:02\"},{\"id\":2,\"clave\":\"nosotros\",\"nombre\":\"Nosotros\",\"titulo\":\"¡La mejor opción para tu capacitación!\",\"subtitulo\":\"DESCUBRE MÁS SOBRE NOSOTROS\",\"descripcion\":\"En ATENEA Escuela, somos una opción educativa comprometida con la formación integral en Naturopatía Holística. Brindamos educación de calidad con un enfoque consciente, ético y humano, creando un entorno de aprendizaje que impulsa el conocimiento, el crecimiento personal y el compromiso con la salud natural y el bienestar integral.\",\"imagen\":\"uploads/contenido/migrado/Cara.jpeg\",\"boton_texto\":\"Más información\",\"boton_url\":\"src/website/about.php\",\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":3,\"clave\":\"cifras\",\"nombre\":\"Cifras\",\"titulo\":null,\"subtitulo\":null,\"descripcion\":null,\"imagen\":null,\"boton_texto\":null,\"boton_url\":null,\"activo\":1,\"orden\":999,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-17 21:20:27\"},{\"id\":4,\"clave\":\"propuesta\",\"nombre\":\"Nuestros servicios\",\"titulo\":\"Lo que ofrecemos en Atenea Escuela\",\"subtitulo\":\"NUESTROS SERVICIOS\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":null,\"boton_url\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":5,\"clave\":\"areas\",\"nombre\":\"Galería\",\"titulo\":\"Conoce nuestras actividades\",\"subtitulo\":\"GALERÍA\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver toda la galería\",\"boton_url\":\"index.php#galeria\",\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":6,\"clave\":\"capacitaciones\",\"nombre\":\"Capacitaciones destacadas\",\"titulo\":\"Formación integral en Naturopatía\",\"subtitulo\":\"CAPACITACIÓN DESTACADA\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver todas las capacitaciones\",\"boton_url\":\"src/website/courses.php\",\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":7,\"clave\":\"noticias\",\"nombre\":\"Noticias\",\"titulo\":\"Sección de noticias\",\"subtitulo\":\"ÚLTIMAS NOTICIAS\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver todas las noticias\",\"boton_url\":\"src/website/noticias.php\",\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-17 09:15:23\"}],\"elementos_seccion\":[{\"id\":4,\"seccion_id\":3,\"titulo\":\"Estudiantes\",\"subtitulo\":\"1200\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":5,\"seccion_id\":3,\"titulo\":\"Capacitaciones\",\"subtitulo\":\"64\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":6,\"seccion_id\":3,\"titulo\":\"Eventos\",\"subtitulo\":\"42\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":7,\"seccion_id\":3,\"titulo\":\"Docentes\",\"subtitulo\":\"24\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":34,\"seccion_id\":4,\"titulo\":\"Visión\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Ser una institución educativa referente en la formación de profesionales en Naturopatía Holística, promoviendo el conocimiento responsable, ético y consciente de las terapias naturales, con una visión integral del ser humano y respeto por la salud y la vida.\",\"imagen\":null,\"icono\":\"bi-eye\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":35,\"seccion_id\":4,\"titulo\":\"Misión\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Formar profesionales en Naturopatía Holística con una visión integral del ser humano, brindando educación ética, consciente y de calidad en terapias naturales. Nuestra misión es transmitir conocimiento sólido, responsable y aplicable, que contribuya al bienestar, la prevención y el cuidado de la salud desde un enfoque natural y humano.\",\"imagen\":null,\"icono\":\"bi-bullseye\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":36,\"seccion_id\":4,\"titulo\":\"Valores\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Nos guiamos por valores fundamentales que constituyen el núcleo de nuestra formación. Promovemos el respeto por la vida y la naturaleza, fomentamos una visión integral del ser humano y cultivamos la ética, la conciencia y la responsabilidad en el ejercicio de las terapias naturales. En nuestra comunidad impulsamos el conocimiento con sentido humano, el respeto mutuo y el compromiso con una salud natural, consciente y digna.\",\"imagen\":null,\"icono\":\"bi-heart\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":37,\"seccion_id\":4,\"titulo\":\"Servicios\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Ofrecemos formación integral en Naturopatía Holística mediante programas académicos, cursos y capacitaciones terapéuticas, orientados al desarrollo profesional y humano del estudiante. Brindamos educación teórica y práctica en terapias naturales, acompañada de formación ética, legal y deontológica, promoviendo un aprendizaje consciente en un entorno de respeto, responsabilidad y compromiso con la salud integral.\",\"imagen\":null,\"icono\":\"bi-journal-medical\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":38,\"seccion_id\":4,\"titulo\":\"Historia\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"ATENEA Escuela de Naturopatía Holística nace como resultado de un proceso de búsqueda, aprendizaje y evolución en el campo de la salud natural. Desde sus inicios, surge con el propósito de ofrecer una formación consciente y responsable en terapias naturales, integrando conocimiento, ética y una visión holística del ser humano. Cada paso de su creación ha sido parte de un crecimiento constante orientado al bienestar integral y a la profesionalización de la naturopatía.\",\"imagen\":null,\"icono\":\"bi-clock-history\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":39,\"seccion_id\":4,\"titulo\":\"Equipo Educativo\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Nuestro equipo educativo está conformado por profesionales capacitados en diversas áreas de la Naturopatía y las terapias holísticas, comprometidos con una enseñanza integral, ética y consciente. Trabajamos de manera cercana para acompañar a cada estudiante en su proceso de aprendizaje, promoviendo el conocimiento, la responsabilidad profesional y el respeto por la salud y la vida.\",\"imagen\":null,\"icono\":\"bi-people\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":40,\"seccion_id\":2,\"titulo\":\"Formación Integral Holística.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":41,\"seccion_id\":2,\"titulo\":\"Excelencia Académica en Naturopatía.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":42,\"seccion_id\":2,\"titulo\":\"Ética, Conciencia y Salud Natural.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":43,\"seccion_id\":6,\"titulo\":\"Introducción a la Naturopatía\",\"subtitulo\":\"CURSO · Básico · $100.00\",\"tipo\":\"CURSO\",\"nivel\":\"Básico\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Dra. María Rodríguez\",\"descripcion\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"imagen\":\"img/programa_6976e84aafd1d_1769400394.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=introduccion-naturopatia\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":44,\"seccion_id\":6,\"titulo\":\"Terapias Naturales Avanzadas\",\"subtitulo\":\"CURSO · Intermedio · $100.00\",\"tipo\":\"CURSO\",\"nivel\":\"Intermedio\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Lic. Carlos Méndez\",\"descripcion\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"imagen\":\"img/programa_6976e886e2b54_1769400454.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=terapias-naturales-avanzadas\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":45,\"seccion_id\":6,\"titulo\":\"Especialización en Naturopatía Holística\",\"subtitulo\":\"CERTIFICACIÓN · Avanzado · $100.00\",\"tipo\":\"CERTIFICACIÓN\",\"nivel\":\"Avanzado\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Dr. Juan Pérez\",\"descripcion\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"imagen\":\"uploads/contenido/migrado/programa_6976e8bae2ba5_1769400506.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=especializacion-naturopatia-holistica\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":46,\"seccion_id\":5,\"titulo\":\"Conoterapia\",\"subtitulo\":\"Terapias\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/conoterapia_cajuela.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":47,\"seccion_id\":5,\"titulo\":\"Masaje Terapéutico\",\"subtitulo\":\"Terapias\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/Masaje.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":48,\"seccion_id\":5,\"titulo\":\"Nutrición\",\"subtitulo\":\"Nutrición\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/69725ec2808fa_1769103042.jpg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:59:49\"},{\"id\":49,\"seccion_id\":5,\"titulo\":\"Naturismo\",\"subtitulo\":\"General\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/Naturismo.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":50,\"seccion_id\":5,\"titulo\":\"Digitopuntura\",\"subtitulo\":\"General\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/69725fb23467c_1769103282.jpg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":0,\"orden\":50,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":51,\"seccion_id\":7,\"titulo\":\"Escuela Atenea\",\"subtitulo\":\"21 de enero de 2026\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a485aa6e4_1769120901.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":52,\"seccion_id\":7,\"titulo\":\"Conoterapia\",\"subtitulo\":\"15 de mayo de 2024\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a39b01db9_1769120667.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":53,\"seccion_id\":7,\"titulo\":\"Naturopatía\",\"subtitulo\":\"22 de enero de 2026\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a279886dc_1769120377.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"}],\"noticias\":[{\"id\":1,\"titulo\":\"Escuela Atenea\",\"slug\":\"escuela-atenea\",\"resumen\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"contenido\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"imagen_portada\":\"uploads/contenido/migrado/noticia_6972a485aa6e4_1769120901.jpg\",\"fecha_publicacion\":\"2026-01-21 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":2,\"titulo\":\"Conoterapia\",\"slug\":\"conoterapia\",\"resumen\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"contenido\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"imagen_portada\":\"img/noticia_6972a39b01db9_1769120667.jpg\",\"fecha_publicacion\":\"2024-05-15 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:30:06\"},{\"id\":3,\"titulo\":\"Naturopatía\",\"slug\":\"naturopatia\",\"resumen\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"contenido\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"imagen_portada\":\"img/noticia_6972a279886dc_1769120377.jpg\",\"fecha_publicacion\":\"2026-01-22 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:30:06\"}],\"asignaturas\":[{\"id\":6,\"elemento_seccion_id\":43,\"codigo\":\"CAP-43\",\"nombre\":\"Introducción a la Naturopatía\",\"slug\":\"introduccion-naturopatia\",\"descripcion_corta\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"descripcion\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"descripcion_completa\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"imagen\":\"img/programa_6976e84aafd1d_1769400394.jpg\",\"tipo\":\"curso\",\"nivel\":\"Básico\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":0,\"orden\":10,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":7,\"elemento_seccion_id\":44,\"codigo\":\"CAP-44\",\"nombre\":\"Terapias Naturales Avanzadas\",\"slug\":\"terapias-naturales-avanzadas\",\"descripcion_corta\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"descripcion\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"descripcion_completa\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"imagen\":\"img/programa_6976e886e2b54_1769400454.jpg\",\"tipo\":\"curso\",\"nivel\":\"Intermedio\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":0,\"orden\":20,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":8,\"elemento_seccion_id\":45,\"codigo\":\"CAP-45\",\"nombre\":\"Especialización en Naturopatía Holística\",\"slug\":\"especializacion-naturopatia-holistica\",\"descripcion_corta\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"descripcion\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"descripcion_completa\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"imagen\":\"uploads/contenido/migrado/programa_6976e8bae2ba5_1769400506.jpg\",\"tipo\":\"certificacion\",\"nivel\":\"Avanzado\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":1,\"orden\":30,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"}],\"categorias_producto\":[{\"id\":16,\"nombre\":\"Comida\",\"slug\":\"comida\",\"descripcion\":null,\"imagen\":null,\"activo\":1,\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-14 15:37:46\",\"updated_at\":\"2026-07-14 15:37:46\"}],\"productos\":[{\"id\":4,\"categoria_id\":16,\"sku\":null,\"nombre\":\"Dulces de uva\",\"slug\":\"ulces-de-uva-d8bb60c4\",\"descripcion_corta\":\"dulces de uva\",\"descripcion\":\"dulces de uva por unidad de 100\",\"tipo_producto\":\"producto\",\"caracteristicas\":null,\"informacion_entrega\":null,\"precio\":\"95.00\",\"stock\":78,\"stock_reservado\":2,\"stock_minimo\":0,\"disponible\":1,\"activo\":1,\"imagen_principal\":\"uploads/contenido/a47e78dee59243f8dc71c59f3a07ed00.png\",\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-14 15:38:46\",\"updated_at\":\"2026-07-16 22:02:16\"},{\"id\":6,\"categoria_id\":16,\"sku\":null,\"nombre\":\"Prueba34\",\"slug\":\"rueba34-b5932993\",\"descripcion_corta\":\"grgrsrgrg\",\"descripcion\":\"wgrgergeegr\",\"tipo_producto\":\"producto\",\"caracteristicas\":null,\"informacion_entrega\":null,\"precio\":\"100.00\",\"stock\":0,\"stock_reservado\":0,\"stock_minimo\":0,\"disponible\":1,\"activo\":1,\"imagen_principal\":null,\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-15 14:31:30\",\"updated_at\":\"2026-07-15 14:31:30\"}],\"producto_imagenes\":[]}','{\"configuracion_sitio\":[{\"id\":1,\"clave\":\"nombre_sitio\",\"valor\":\"Atenea Escuela de Naturopatía Holística\",\"tipo\":\"texto\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":2,\"clave\":\"logo\",\"valor\":\"img/atenea-logo.png\",\"tipo\":\"imagen\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":3,\"clave\":\"favicon\",\"valor\":\"img/atenea-logo.png\",\"tipo\":\"imagen\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":4,\"clave\":\"correo\",\"valor\":\"ateneanaturopatia@gmail.com\",\"tipo\":\"email\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":5,\"clave\":\"telefono\",\"valor\":\"\",\"tipo\":\"telefono\",\"updated_at\":\"2026-07-12 09:47:01\"},{\"id\":6,\"clave\":\"direccion\",\"valor\":\"Av. El Níspero Final, Huizúcar\",\"tipo\":\"texto\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":7,\"clave\":\"facebook\",\"valor\":\"#\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":8,\"clave\":\"instagram\",\"valor\":\"#\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":9,\"clave\":\"whatsapp\",\"valor\":\"\",\"tipo\":\"url\",\"updated_at\":\"2026-07-12 09:44:33\"}],\"menu_sitio\":[{\"id\":1,\"texto\":\"Inicio\",\"url\":\"index.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":2,\"texto\":\"Nosotros\",\"url\":\"src/website/about.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":3,\"texto\":\"Capacitaciones\",\"url\":\"src/website/courses.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":4,\"texto\":\"Docentes\",\"url\":\"src/website/trainers.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":5,\"texto\":\"Eventos\",\"url\":\"src/website/events.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":6,\"texto\":\"Productos\",\"url\":\"src/website/pricing.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":7,\"texto\":\"Noticias\",\"url\":\"src/website/noticias.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":70,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":8,\"texto\":\"Contacto\",\"url\":\"src/website/contact.php\",\"nueva_pestana\":0,\"activo\":1,\"orden\":80,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"}],\"secciones\":[{\"id\":1,\"clave\":\"hero\",\"nombre\":\"Hero principal\",\"titulo\":\"“La salud se aprende, el cuerpo sana”\",\"subtitulo\":\"Atenea Escuela de Naturopatía Holística\",\"descripcion\":\"Atenea Escuela de Naturopatía Holística es una institución enfocada en la capacitación, la divulgación del conocimiento en salud natural y la comercialización de productos alineados con un estilo de vida saludable. Su propuesta combina una escuela online de naturopatía, cursos y certificaciones especializadas y la comercialización de productos naturopáticos, creando un entorno armónico entre salud, capacitación y bienestar.\",\"imagen\":\"uploads/contenido/15eb11af85cbcbf6ff9ac747447016de.png\",\"boton_texto\":\"\",\"boton_url\":\"\",\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 23:03:02\"},{\"id\":2,\"clave\":\"nosotros\",\"nombre\":\"Nosotros\",\"titulo\":\"¡La mejor opción para tu capacitación!\",\"subtitulo\":\"DESCUBRE MÁS SOBRE NOSOTROS\",\"descripcion\":\"En ATENEA Escuela, somos una opción educativa comprometida con la formación integral en Naturopatía Holística. Brindamos educación de calidad con un enfoque consciente, ético y humano, creando un entorno de aprendizaje que impulsa el conocimiento, el crecimiento personal y el compromiso con la salud natural y el bienestar integral.\",\"imagen\":\"uploads/contenido/migrado/Cara.jpeg\",\"boton_texto\":\"Más información\",\"boton_url\":\"src/website/about.php\",\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":3,\"clave\":\"cifras\",\"nombre\":\"Cifras\",\"titulo\":null,\"subtitulo\":null,\"descripcion\":null,\"imagen\":null,\"boton_texto\":null,\"boton_url\":null,\"activo\":0,\"orden\":999,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-17 21:20:56\"},{\"id\":4,\"clave\":\"propuesta\",\"nombre\":\"Nuestros servicios\",\"titulo\":\"Lo que ofrecemos en Atenea Escuela\",\"subtitulo\":\"NUESTROS SERVICIOS\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":null,\"boton_url\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":5,\"clave\":\"areas\",\"nombre\":\"Galería\",\"titulo\":\"Conoce nuestras actividades\",\"subtitulo\":\"GALERÍA\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver toda la galería\",\"boton_url\":\"index.php#galeria\",\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":6,\"clave\":\"capacitaciones\",\"nombre\":\"Capacitaciones destacadas\",\"titulo\":\"Formación integral en Naturopatía\",\"subtitulo\":\"CAPACITACIÓN DESTACADA\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver todas las capacitaciones\",\"boton_url\":\"src/website/courses.php\",\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":7,\"clave\":\"noticias\",\"nombre\":\"Noticias\",\"titulo\":\"Sección de noticias\",\"subtitulo\":\"ÚLTIMAS NOTICIAS\",\"descripcion\":null,\"imagen\":null,\"boton_texto\":\"Ver todas las noticias\",\"boton_url\":\"src/website/noticias.php\",\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-17 09:15:23\"}],\"elementos_seccion\":[{\"id\":4,\"seccion_id\":3,\"titulo\":\"Estudiantes\",\"subtitulo\":\"1200\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":5,\"seccion_id\":3,\"titulo\":\"Capacitaciones\",\"subtitulo\":\"64\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":6,\"seccion_id\":3,\"titulo\":\"Eventos\",\"subtitulo\":\"42\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":7,\"seccion_id\":3,\"titulo\":\"Docentes\",\"subtitulo\":\"24\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-12 09:44:33\",\"updated_at\":\"2026-07-12 09:44:33\"},{\"id\":34,\"seccion_id\":4,\"titulo\":\"Visión\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Ser una institución educativa referente en la formación de profesionales en Naturopatía Holística, promoviendo el conocimiento responsable, ético y consciente de las terapias naturales, con una visión integral del ser humano y respeto por la salud y la vida.\",\"imagen\":null,\"icono\":\"bi-eye\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":35,\"seccion_id\":4,\"titulo\":\"Misión\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Formar profesionales en Naturopatía Holística con una visión integral del ser humano, brindando educación ética, consciente y de calidad en terapias naturales. Nuestra misión es transmitir conocimiento sólido, responsable y aplicable, que contribuya al bienestar, la prevención y el cuidado de la salud desde un enfoque natural y humano.\",\"imagen\":null,\"icono\":\"bi-bullseye\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":36,\"seccion_id\":4,\"titulo\":\"Valores\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Nos guiamos por valores fundamentales que constituyen el núcleo de nuestra formación. Promovemos el respeto por la vida y la naturaleza, fomentamos una visión integral del ser humano y cultivamos la ética, la conciencia y la responsabilidad en el ejercicio de las terapias naturales. En nuestra comunidad impulsamos el conocimiento con sentido humano, el respeto mutuo y el compromiso con una salud natural, consciente y digna.\",\"imagen\":null,\"icono\":\"bi-heart\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":37,\"seccion_id\":4,\"titulo\":\"Servicios\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Ofrecemos formación integral en Naturopatía Holística mediante programas académicos, cursos y capacitaciones terapéuticas, orientados al desarrollo profesional y humano del estudiante. Brindamos educación teórica y práctica en terapias naturales, acompañada de formación ética, legal y deontológica, promoviendo un aprendizaje consciente en un entorno de respeto, responsabilidad y compromiso con la salud integral.\",\"imagen\":null,\"icono\":\"bi-journal-medical\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":38,\"seccion_id\":4,\"titulo\":\"Historia\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"ATENEA Escuela de Naturopatía Holística nace como resultado de un proceso de búsqueda, aprendizaje y evolución en el campo de la salud natural. Desde sus inicios, surge con el propósito de ofrecer una formación consciente y responsable en terapias naturales, integrando conocimiento, ética y una visión holística del ser humano. Cada paso de su creación ha sido parte de un crecimiento constante orientado al bienestar integral y a la profesionalización de la naturopatía.\",\"imagen\":null,\"icono\":\"bi-clock-history\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":50,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":39,\"seccion_id\":4,\"titulo\":\"Equipo Educativo\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Nuestro equipo educativo está conformado por profesionales capacitados en diversas áreas de la Naturopatía y las terapias holísticas, comprometidos con una enseñanza integral, ética y consciente. Trabajamos de manera cercana para acompañar a cada estudiante en su proceso de aprendizaje, promoviendo el conocimiento, la responsabilidad profesional y el respeto por la salud y la vida.\",\"imagen\":null,\"icono\":\"bi-people\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":60,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":40,\"seccion_id\":2,\"titulo\":\"Formación Integral Holística.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":41,\"seccion_id\":2,\"titulo\":\"Excelencia Académica en Naturopatía.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":42,\"seccion_id\":2,\"titulo\":\"Ética, Conciencia y Salud Natural.\",\"subtitulo\":null,\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":null,\"icono\":\"bi-check-circle\",\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":43,\"seccion_id\":6,\"titulo\":\"Introducción a la Naturopatía\",\"subtitulo\":\"CURSO · Básico · $100.00\",\"tipo\":\"CURSO\",\"nivel\":\"Básico\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Dra. María Rodríguez\",\"descripcion\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"imagen\":\"img/programa_6976e84aafd1d_1769400394.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=introduccion-naturopatia\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":44,\"seccion_id\":6,\"titulo\":\"Terapias Naturales Avanzadas\",\"subtitulo\":\"CURSO · Intermedio · $100.00\",\"tipo\":\"CURSO\",\"nivel\":\"Intermedio\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Lic. Carlos Méndez\",\"descripcion\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"imagen\":\"img/programa_6976e886e2b54_1769400454.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=terapias-naturales-avanzadas\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":45,\"seccion_id\":6,\"titulo\":\"Especialización en Naturopatía Holística\",\"subtitulo\":\"CERTIFICACIÓN · Avanzado · $100.00\",\"tipo\":\"CERTIFICACIÓN\",\"nivel\":\"Avanzado\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"instructor\":\"Dr. Juan Pérez\",\"descripcion\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"imagen\":\"uploads/contenido/migrado/programa_6976e8bae2ba5_1769400506.jpg\",\"icono\":null,\"enlace\":\"src/website/capacitacion.php?slug=especializacion-naturopatia-holistica\",\"texto_boton\":\"Ver detalles y pagar\",\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":46,\"seccion_id\":5,\"titulo\":\"Conoterapia\",\"subtitulo\":\"Terapias\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/conoterapia_cajuela.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":47,\"seccion_id\":5,\"titulo\":\"Masaje Terapéutico\",\"subtitulo\":\"Terapias\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/Masaje.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":48,\"seccion_id\":5,\"titulo\":\"Nutrición\",\"subtitulo\":\"Nutrición\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/69725ec2808fa_1769103042.jpg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:59:49\"},{\"id\":49,\"seccion_id\":5,\"titulo\":\"Naturismo\",\"subtitulo\":\"General\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/Naturismo.jpeg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":1,\"orden\":40,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":50,\"seccion_id\":5,\"titulo\":\"Digitopuntura\",\"subtitulo\":\"General\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":null,\"imagen\":\"uploads/contenido/migrado/69725fb23467c_1769103282.jpg\",\"icono\":null,\"enlace\":null,\"texto_boton\":null,\"activo\":0,\"orden\":50,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":51,\"seccion_id\":7,\"titulo\":\"Escuela Atenea\",\"subtitulo\":\"21 de enero de 2026\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a485aa6e4_1769120901.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":10,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":52,\"seccion_id\":7,\"titulo\":\"Conoterapia\",\"subtitulo\":\"15 de mayo de 2024\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a39b01db9_1769120667.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":20,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"},{\"id\":53,\"seccion_id\":7,\"titulo\":\"Naturopatía\",\"subtitulo\":\"22 de enero de 2026\",\"tipo\":null,\"nivel\":null,\"precio\":null,\"duracion\":null,\"instructor\":null,\"descripcion\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"imagen\":\"uploads/contenido/migrado/noticia_6972a279886dc_1769120377.jpg\",\"icono\":null,\"enlace\":\"src/website/events.php\",\"texto_boton\":\"Ver más\",\"activo\":0,\"orden\":30,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:06:36\"}],\"noticias\":[{\"id\":1,\"titulo\":\"Escuela Atenea\",\"slug\":\"escuela-atenea\",\"resumen\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"contenido\":\"La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.\",\"imagen_portada\":\"uploads/contenido/migrado/noticia_6972a485aa6e4_1769120901.jpg\",\"fecha_publicacion\":\"2026-01-21 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-16 22:46:38\"},{\"id\":2,\"titulo\":\"Conoterapia\",\"slug\":\"conoterapia\",\"resumen\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"contenido\":\"Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.\",\"imagen_portada\":\"img/noticia_6972a39b01db9_1769120667.jpg\",\"fecha_publicacion\":\"2024-05-15 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:30:06\"},{\"id\":3,\"titulo\":\"Naturopatía\",\"slug\":\"naturopatia\",\"resumen\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"contenido\":\"En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.\",\"imagen_portada\":\"img/noticia_6972a279886dc_1769120377.jpg\",\"fecha_publicacion\":\"2026-01-22 08:00:00\",\"autor\":null,\"estado\":\"publicado\",\"destacado\":0,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"created_at\":\"2026-07-16 22:46:38\",\"updated_at\":\"2026-07-17 09:30:06\"}],\"asignaturas\":[{\"id\":6,\"elemento_seccion_id\":43,\"codigo\":\"CAP-43\",\"nombre\":\"Introducción a la Naturopatía\",\"slug\":\"introduccion-naturopatia\",\"descripcion_corta\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"descripcion\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"descripcion_completa\":\"Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural\",\"imagen\":\"img/programa_6976e84aafd1d_1769400394.jpg\",\"tipo\":\"curso\",\"nivel\":\"Básico\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":0,\"orden\":10,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":7,\"elemento_seccion_id\":44,\"codigo\":\"CAP-44\",\"nombre\":\"Terapias Naturales Avanzadas\",\"slug\":\"terapias-naturales-avanzadas\",\"descripcion_corta\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"descripcion\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"descripcion_completa\":\"Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos\",\"imagen\":\"img/programa_6976e886e2b54_1769400454.jpg\",\"tipo\":\"curso\",\"nivel\":\"Intermedio\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":0,\"orden\":20,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"},{\"id\":8,\"elemento_seccion_id\":45,\"codigo\":\"CAP-45\",\"nombre\":\"Especialización en Naturopatía Holística\",\"slug\":\"especializacion-naturopatia-holistica\",\"descripcion_corta\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"descripcion\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"descripcion_completa\":\"Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral\",\"imagen\":\"uploads/contenido/migrado/programa_6976e8bae2ba5_1769400506.jpg\",\"tipo\":\"certificacion\",\"nivel\":\"Avanzado\",\"precio\":\"100.00\",\"duracion\":\"por definir\",\"fecha_inicio\":null,\"fecha_finalizacion\":null,\"estado_capacitacion\":\"publicada\",\"cupo_seccion\":30,\"requisitos\":null,\"objetivos\":null,\"modalidad\":\"presencial\",\"certificado_disponible\":1,\"orden\":30,\"activo\":1,\"deleted_at\":null,\"eliminado_por\":null,\"estado\":\"activo\",\"creado_por\":null,\"created_at\":\"2026-07-17 09:58:36\",\"updated_at\":\"2026-07-17 09:58:36\"}],\"categorias_producto\":[{\"id\":16,\"nombre\":\"Comida\",\"slug\":\"comida\",\"descripcion\":null,\"imagen\":null,\"activo\":1,\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-14 15:37:46\",\"updated_at\":\"2026-07-14 15:37:46\"}],\"productos\":[{\"id\":4,\"categoria_id\":16,\"sku\":null,\"nombre\":\"Dulces de uva\",\"slug\":\"ulces-de-uva-d8bb60c4\",\"descripcion_corta\":\"dulces de uva\",\"descripcion\":\"dulces de uva por unidad de 100\",\"tipo_producto\":\"producto\",\"caracteristicas\":null,\"informacion_entrega\":null,\"precio\":\"95.00\",\"stock\":78,\"stock_reservado\":2,\"stock_minimo\":0,\"disponible\":1,\"activo\":1,\"imagen_principal\":\"uploads/contenido/a47e78dee59243f8dc71c59f3a07ed00.png\",\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-14 15:38:46\",\"updated_at\":\"2026-07-16 22:02:16\"},{\"id\":6,\"categoria_id\":16,\"sku\":null,\"nombre\":\"Prueba34\",\"slug\":\"rueba34-b5932993\",\"descripcion_corta\":\"grgrsrgrg\",\"descripcion\":\"wgrgergeegr\",\"tipo_producto\":\"producto\",\"caracteristicas\":null,\"informacion_entrega\":null,\"precio\":\"100.00\",\"stock\":0,\"stock_reservado\":0,\"stock_minimo\":0,\"disponible\":1,\"activo\":1,\"imagen_principal\":null,\"eliminado_at\":null,\"creado_por\":1,\"actualizado_por\":1,\"created_at\":\"2026-07-15 14:31:30\",\"updated_at\":\"2026-07-15 14:31:30\"}],\"producto_imagenes\":[]}','borrador','Cambio guardado como borrador','2026-07-17 21:20:56');

-- Información de account_cleanup_notifications
-- Información de asignaturas
INSERT INTO `asignaturas` (`id`,`elemento_seccion_id`,`codigo`,`nombre`,`slug`,`descripcion_corta`,`descripcion`,`descripcion_completa`,`imagen`,`tipo`,`nivel`,`precio`,`duracion`,`fecha_inicio`,`fecha_finalizacion`,`estado_capacitacion`,`cupo_seccion`,`asignacion_automatica`,`requisitos`,`objetivos`,`modalidad`,`certificado_disponible`,`orden`,`activo`,`deleted_at`,`eliminado_por`,`estado`,`creado_por`,`created_at`,`updated_at`) VALUES
(6,43,'CAP-43','Introducción a la Naturopatía','introduccion-naturopatia','Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural','Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural','Curso fundamental que introduce los principios básicos de la naturopatía holística y las bases del autocuidado natural','img/programa_6976e84aafd1d_1769400394.jpg','curso','Básico',100.00,'por definir',NULL,NULL,'publicada',30,1,NULL,NULL,'presencial',0,10,1,NULL,NULL,'activo',NULL,'2026-07-17 09:58:36','2026-07-17 09:58:36'),
(7,44,'CAP-44','Terapias Naturales Avanzadas','terapias-naturales-avanzadas','Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos','Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos','Programa integral que profundiza en técnicas terapéuticas naturales, fitoterapia y tratamientos holísticos','img/programa_6976e886e2b54_1769400454.jpg','curso','Intermedio',100.00,'por definir',NULL,NULL,'publicada',30,1,NULL,NULL,'presencial',0,20,1,NULL,NULL,'activo',NULL,'2026-07-17 09:58:36','2026-07-17 09:58:36'),
(8,45,'CAP-45','Especialización en Naturopatía Holística','especializacion-naturopatia-holistica','Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral','Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral','Formación profesional avanzada en naturopatía, desarrollando habilidades de diagnóstico y tratamiento integral','uploads/contenido/migrado/programa_6976e8bae2ba5_1769400506.jpg','certificacion','Avanzado',100.00,'por definir',NULL,NULL,'publicada',30,1,NULL,NULL,'presencial',1,30,1,NULL,NULL,'activo',NULL,'2026-07-17 09:58:36','2026-07-17 09:58:36');

-- Información de assisted_password_resets
-- Información de audit_logs
INSERT INTO `audit_logs` (`id`,`actor_user_id`,`target_user_id`,`event_type`,`module`,`entity_type`,`entity_id`,`action`,`result`,`description`,`metadata`,`ip_address`,`user_agent`,`request_id`,`created_at`) VALUES
(1,1,1,'auth.login_success','auth','user','1','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','d37515b445edef706b70effed79fe121','2026-07-14 23:53:50'),
(2,1,1,'auth.logout','auth','user','1','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','80f2702e4d6c0fefa0fefdf98d22cb5a','2026-07-14 23:57:49'),
(3,2,2,'auth.login_success','auth','user','2','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','d6fdd9dd99a96f8696f75d33bbf4c2d0','2026-07-14 23:57:56'),
(4,2,2,'auth.logout','auth','user','2','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','a78963626bbe39a9f7ad3e2f224739a9','2026-07-15 00:04:02'),
(5,2,2,'auth.login_success','auth','user','2','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','5488661f04415eb7b6e88650dbded72d','2026-07-15 00:14:14'),
(6,2,2,'auth.logout','auth','user','2','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','e8b3a58ba17c1f3d4b29d6d7a6e4f2f2','2026-07-15 00:14:20'),
(7,1,1,'auth.login_success','auth','user','1','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','f5dcd106cfeb393038beca07c3142ed1','2026-07-15 00:14:25'),
(8,1,1,'auth.logout','auth','user','1','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','6a1fb5a1060e71ba029ef92015de7964','2026-07-15 00:16:12'),
(9,3,3,'auth.login_success','auth','user','3','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','ef68f6f42e35a0430c5bb5ecd1ba86c9','2026-07-15 11:21:36'),
(10,3,3,'auth.logout','auth','user','3','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','c410c367d6c05c0e298df851b8bdc41b','2026-07-15 11:21:50'),
(11,1,1,'auth.login_success','auth','user','1','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','41f36fea6c1c4ecfd7f3586fa68e59a5','2026-07-15 11:38:19'),
(12,1,1,'auth.logout','auth','user','1','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','b4af84d2873bd266602ed124fce92e1f','2026-07-15 11:52:17'),
(13,2,2,'auth.login_success','auth','user','2','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','e8697ef2e340a86b5e5f440692a47e14','2026-07-15 11:54:19'),
(14,2,2,'auth.logout','auth','user','2','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','bb6094eddbaa732d8235bdee682f5ee5','2026-07-15 11:54:27'),
(15,1,1,'user.admin_action_failed','users','user','1','eliminar_logico','failure','No fue posible completar una accion administrativa.','{\"error_class\":\"RuntimeException\"}','::1','Otro navegador / SO no identificado','e19b30ca4510a6a2d69e95ff1e4c7672','2026-07-15 12:27:20'),
(16,1,3,'user.admin_action_failed','users','user','3','eliminar_logico','failure','No fue posible completar una accion administrativa.','{\"error_class\":\"RuntimeException\"}','::1','Otro navegador / SO no identificado','ba6c44113b20d06ceff6b3489bf12825','2026-07-15 12:27:20'),
(21,NULL,NULL,'auth.login_failed','auth',NULL,NULL,'login','failure','Fallo un inicio de sesion por credenciales o estado de cuenta no validos.',NULL,'::1','Chrome / Windows','17055df2ea1946bf626758f5c08eaff8','2026-07-15 12:35:40'),
(22,1,1,'auth.login_success','auth','user','1','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','f20f5aea9d3053e32c30d2ae9b0431a7','2026-07-15 12:35:52'),
(23,1,1,'auth.logout','auth','user','1','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','27ecaf88e3b48abf6bb5e593f7378767','2026-07-15 12:36:24'),
(24,NULL,NULL,'auth.login_success','auth','user','44','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Otro navegador / Windows','a796fda9f533dd54bc68aca5755bcf7c','2026-07-15 13:08:42'),
(25,NULL,NULL,'auth.logout','auth','user','44','logout','success','Cierre de sesion exitoso.',NULL,'::1','Otro navegador / Windows','3b8b9f77161d9e4c8d523a3d3bbd131c','2026-07-15 13:08:42'),
(26,NULL,NULL,'user.created','users','user','45','create','success','Se creo una cuenta mediante registro tradicional.','{\"provider\":\"local\",\"role\":\"usuario\"}','::1','Otro navegador / Windows','7216f4f8a4f737eeb6beadf9e9caeba3','2026-07-15 13:09:20'),
(27,NULL,NULL,'user.created','users','user','46','create','success','Se creo una cuenta mediante registro tradicional.','{\"provider\":\"local\",\"role\":\"usuario\"}','::1','Otro navegador / Windows','97e6ebc65a71dda8c338192251b7b68b','2026-07-15 13:16:55'),
(28,NULL,NULL,'auth.login_success','auth','user','47','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Otro navegador / Windows','6e754877329e317bc38015b4f52f3f3c','2026-07-15 13:17:12'),
(29,NULL,NULL,'auth.login_success','auth','user','48','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Otro navegador / Windows','36e76f71e85ec730d8b664f98eba7f4a','2026-07-15 13:17:13'),
(30,1,1,'auth.login_success','auth','user','1','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','fa6262ef6317b4b4eaed0667e60dd9a1','2026-07-15 14:30:45'),
(31,1,1,'auth.logout','auth','user','1','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','9a2673ef20a6aafbc7ff1c402f89d07a','2026-07-15 14:31:41'),
(33,1,1,'auth.login_success','auth','user','1','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','e7dd0979088c249ecdf07160c3e5c381','2026-07-15 15:55:33'),
(34,1,1,'auth.logout','auth','user','1','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','1ce16639d02ad75b34d1006ae7d6f73d','2026-07-15 15:56:06'),
(35,3,3,'auth.login_success','auth','user','3','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','8bb493ad561f47f5d0b460da97eac77d','2026-07-15 15:56:11'),
(36,3,3,'auth.logout','auth','user','3','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','780ce28c754ed35211b1f3b97d72bae2','2026-07-15 15:56:32'),
(37,2,2,'auth.login_success','auth','user','2','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','f91263411c6f695891c21306c61ae3a6','2026-07-16 21:59:12'),
(38,NULL,2,'payment.approved','payments','order','15','confirm','success','Stripe confirmo el pago por webhook verificado; importe y moneda coincidieron.','{\"currency\":\"usd\",\"brand\":\"visa\",\"last4\":\"4242\"}','::1','Otro navegador / SO no identificado','16e6fbb2d3156bf1b283e8952bd85f74','2026-07-16 22:02:16'),
(39,2,2,'receipt.downloaded','payments','order','15','download','success','El propietario descargo su comprobante interno de compra.',NULL,'::1','Chrome / Windows','0a9bb384f5a764edab0734610ac47a8a','2026-07-16 22:02:29'),
(40,2,2,'auth.logout','auth','user','2','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','80c0b094c3a9d45d270b85359f488b3e','2026-07-16 22:04:20'),
(41,1,1,'auth.login_success','auth','user','1','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','db94af7a71e04d8128656480c8de93d3','2026-07-16 22:58:46'),
(42,1,NULL,'system_error.reviewed','system_errors','system_error','3','update','success','Un administrador actualizó el estado de un error operativo.','{\"status\":\"nuevo\"}','::1','Chrome / Windows','8075b5484caea8acf78afeed1533ff34','2026-07-16 23:03:56'),
(43,1,1,'auth.logout','auth','user','1','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','66d5f16ac888b24275c4517d4707e9a9','2026-07-16 23:06:17'),
(44,1,1,'auth.login_success','auth','user','1','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','c117a99921c3c9bcb0fb7aae9d0d593d','2026-07-16 23:48:30'),
(47,1,1,'auth.logout','auth','user','1','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','76ca314d581bc96b71c7fc044f28c281','2026-07-17 00:09:02'),
(48,51,51,'user.created','users','user','51','create','success','Se creo una cuenta mediante Google.','{\"provider\":\"google\",\"role\":\"usuario\"}','::1','Chrome / Windows','bf7c04c44117c6d608b4ae697bdc4018','2026-07-17 00:11:36'),
(49,51,51,'auth.google_login','auth','user','51','login','success','Inicio de sesion exitoso mediante Google.',NULL,'::1','Chrome / Windows','bf7c04c44117c6d608b4ae697bdc4018','2026-07-17 00:11:36'),
(50,51,51,'auth.logout','auth','user','51','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','380030baeca17f11520fc5d1fed2c775','2026-07-17 00:12:10'),
(51,1,1,'auth.login_success','auth','user','1','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','c5d3d5b6440975719d88a11d66eb9c7c','2026-07-17 00:12:14'),
(52,1,51,'user.admin_notice_created','notices','admin_notice','12','create','success','Se envio un aviso administrativo a una cuenta.','{\"type\":\"documentacion\",\"priority\":\"urgente\",\"target_section\":\"TODO\"}','::1','Chrome / Windows','d741d7349fc481172dd0a9fa01fdffbf','2026-07-17 00:13:24'),
(53,1,1,'auth.logout','auth','user','1','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','3f792a161318159facdabc55969c021b','2026-07-17 00:14:01'),
(54,51,51,'auth.google_login','auth','user','51','login','success','Inicio de sesion exitoso mediante Google.',NULL,'::1','Chrome / Windows','01565e386b8afa98ba1c047d421fae64','2026-07-17 00:14:08'),
(55,51,51,'auth.logout','auth','user','51','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','7fba5623248bfc840f022a541d794fe6','2026-07-17 00:15:39'),
(64,51,51,'auth.google_login','auth','user','51','login','success','Inicio de sesion exitoso mediante Google.',NULL,'::1','Chrome / Windows','d4a49be87c126d5ae50aabf35cd90042','2026-07-17 10:25:36'),
(65,51,51,'auth.logout','auth','user','51','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','e76a0e9bbf8542926f191412cf3f7201','2026-07-17 10:26:54'),
(66,3,3,'auth.login_success','auth','user','3','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','ebdac1f333e7f2e218aedd134a37d4c8','2026-07-17 10:27:01'),
(67,3,3,'auth.logout','auth','user','3','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','06982c95a074d510d0f512f32027b340','2026-07-17 10:27:24'),
(68,NULL,NULL,'chat.message.sent','communications','chat_message','1','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','a0d82c6a74ad2fa34790a043d6538fd7','2026-07-17 11:31:36'),
(69,NULL,NULL,'chat.message.sent','communications','chat_message','2','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','a0d82c6a74ad2fa34790a043d6538fd7','2026-07-17 11:31:36'),
(70,1,NULL,'chat.message.sent','communications','chat_message','3','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','a0d82c6a74ad2fa34790a043d6538fd7','2026-07-17 11:31:36'),
(71,NULL,NULL,'chat.message.sent','communications','chat_message','4','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','a0d82c6a74ad2fa34790a043d6538fd7','2026-07-17 11:31:37'),
(72,NULL,NULL,'chat.message.sent','communications','chat_message','5','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','546c632aadeeeb178affac454b9af173','2026-07-17 11:36:09'),
(73,NULL,NULL,'chat.message.sent','communications','chat_message','6','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','546c632aadeeeb178affac454b9af173','2026-07-17 11:36:09'),
(74,1,NULL,'chat.message.sent','communications','chat_message','7','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','546c632aadeeeb178affac454b9af173','2026-07-17 11:36:09'),
(75,NULL,NULL,'chat.message.sent','communications','chat_message','8','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','546c632aadeeeb178affac454b9af173','2026-07-17 11:36:09'),
(76,NULL,NULL,'chat.message.sent','communications','chat_message','9','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','ad9435236bdcd8ee4077d8d487bd31f7','2026-07-17 11:38:28'),
(77,NULL,NULL,'chat.message.sent','communications','chat_message','10','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','ad9435236bdcd8ee4077d8d487bd31f7','2026-07-17 11:38:28'),
(78,1,NULL,'chat.message.sent','communications','chat_message','11','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','ad9435236bdcd8ee4077d8d487bd31f7','2026-07-17 11:38:28'),
(79,NULL,NULL,'chat.message.sent','communications','chat_message','12','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','ad9435236bdcd8ee4077d8d487bd31f7','2026-07-17 11:38:28'),
(80,NULL,NULL,'chat.message.sent','communications','chat_message','13','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','74f68417f192f891b7f4afc699e1b6ae','2026-07-17 11:43:15'),
(81,NULL,NULL,'chat.message.sent','communications','chat_message','14','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','74f68417f192f891b7f4afc699e1b6ae','2026-07-17 11:43:15'),
(82,1,NULL,'chat.message.sent','communications','chat_message','15','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','74f68417f192f891b7f4afc699e1b6ae','2026-07-17 11:43:15'),
(83,NULL,NULL,'chat.message.sent','communications','chat_message','16','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','74f68417f192f891b7f4afc699e1b6ae','2026-07-17 11:43:15'),
(84,NULL,NULL,'chat.message.sent','communications','chat_message','17','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','772d62395acf3a46e94f7fc090ec1cc1','2026-07-17 11:45:03'),
(85,NULL,NULL,'chat.message.sent','communications','chat_message','18','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','772d62395acf3a46e94f7fc090ec1cc1','2026-07-17 11:45:03'),
(86,1,NULL,'chat.message.sent','communications','chat_message','19','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','772d62395acf3a46e94f7fc090ec1cc1','2026-07-17 11:45:03'),
(87,NULL,NULL,'chat.message.sent','communications','chat_message','20','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','772d62395acf3a46e94f7fc090ec1cc1','2026-07-17 11:45:03'),
(90,NULL,NULL,'chat.message.sent','communications','chat_message','21','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','1e3c3688aa05a7957e54182b03daa701','2026-07-17 19:19:37'),
(91,NULL,NULL,'chat.message.sent','communications','chat_message','22','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','1e3c3688aa05a7957e54182b03daa701','2026-07-17 19:19:37'),
(92,1,NULL,'chat.message.sent','communications','chat_message','23','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','1e3c3688aa05a7957e54182b03daa701','2026-07-17 19:19:37'),
(93,NULL,NULL,'chat.message.sent','communications','chat_message','24','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','1e3c3688aa05a7957e54182b03daa701','2026-07-17 19:19:37'),
(98,NULL,NULL,'chat.message.sent','communications','chat_message','25','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','32fe4c5ec5e8edcdd93b6ba6a5db6ce8','2026-07-17 19:26:02'),
(99,NULL,NULL,'chat.message.sent','communications','chat_message','26','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','32fe4c5ec5e8edcdd93b6ba6a5db6ce8','2026-07-17 19:26:02'),
(100,1,NULL,'chat.message.sent','communications','chat_message','27','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','32fe4c5ec5e8edcdd93b6ba6a5db6ce8','2026-07-17 19:26:02'),
(101,NULL,NULL,'chat.message.sent','communications','chat_message','28','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','32fe4c5ec5e8edcdd93b6ba6a5db6ce8','2026-07-17 19:26:02'),
(104,3,3,'auth.login_success','auth','user','3','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','0d416fbc2025eef71f4bb914121e9a9b','2026-07-17 19:43:02'),
(105,3,3,'auth.logout','auth','user','3','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','1c3622005e749b63fbeab590ef58ebe1','2026-07-17 19:44:39'),
(106,1,1,'auth.login_success','auth','user','1','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','6ba1175a6f71061d0720176810ea4fb7','2026-07-17 19:44:53'),
(107,1,1,'auth.logout','auth','user','1','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','f4c79f98b99eec4ca3bb332a84a58d36','2026-07-17 21:17:03'),
(108,1,1,'auth.login_success','auth','user','1','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','5311598b1c87e5dfb5b35954a5e58959','2026-07-17 21:17:08'),
(109,1,NULL,'chat.message.sent','communications','chat_message','29','create','success','Mensaje interno enviado.',NULL,'::1','Chrome / Windows','bbe1fea8df6bbb12246ba071af0ff070','2026-07-17 21:17:49'),
(110,1,NULL,'chat.message.sent','communications','chat_message','30','create','success','Mensaje interno enviado.',NULL,'::1','Chrome / Windows','cf54b9bb8ddfc35cfe287cdc03fab656','2026-07-17 21:18:17'),
(111,1,1,'auth.logout','auth','user','1','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','fdd16d0a1f85ac08f9b6f952c117ab65','2026-07-17 21:18:22'),
(112,51,51,'auth.google_login','auth','user','51','login','success','Inicio de sesion exitoso mediante Google.',NULL,'::1','Chrome / Windows','e8f563bfcb7723e01a4484927d8b0abe','2026-07-17 21:18:31'),
(113,51,51,'auth.logout','auth','user','51','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','ad4e4ded40806fbf5443cd6d81d8c5d5','2026-07-17 21:19:35'),
(114,1,1,'auth.login_success','auth','user','1','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','88a6fdc8f243fab4939101fa5daafefe','2026-07-17 21:19:40'),
(115,1,1,'auth.logout','auth','user','1','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','d0ee4f31ea6713df345ad00d07517ff5','2026-07-17 21:21:03'),
(116,51,51,'auth.google_login','auth','user','51','login','success','Inicio de sesion exitoso mediante Google.',NULL,'::1','Chrome / Windows','73b7e5d1ef2b4f6dc34b7aeb64af95ae','2026-07-17 21:31:36'),
(117,51,51,'auth.logout','auth','user','51','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','efd6e0a708699ddec183a1c4444af1b0','2026-07-17 21:32:03'),
(118,1,1,'auth.login_success','auth','user','1','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','65737c03dce23f9a05559d8998051076','2026-07-17 21:38:08'),
(119,1,1,'auth.logout','auth','user','1','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','afd4bbf6b3c5dada75250d94fa337e48','2026-07-17 21:50:36'),
(120,2,2,'auth.login_success','auth','user','2','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','e1e9f368dbb23665122a013715238dcc','2026-07-17 21:51:18'),
(121,2,2,'auth.logout','auth','user','2','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','b6f61165802a54d84759ff8f3db2e850','2026-07-17 22:05:46'),
(122,3,3,'auth.login_success','auth','user','3','login','success','Inicio de sesion exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','7d4bce579ed383eab75b3af7631fdc41','2026-07-17 22:12:44'),
(123,3,3,'auth.logout','auth','user','3','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','9975eb8c647b61718117339aca1f4a3b','2026-07-17 22:17:35'),
(124,NULL,NULL,'chat.message.sent','communications','chat_message','31','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','904c7d387bc3c97844c2fc4a7b875667','2026-07-17 22:26:44'),
(125,NULL,NULL,'chat.message.sent','communications','chat_message','32','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','904c7d387bc3c97844c2fc4a7b875667','2026-07-17 22:26:44'),
(126,1,NULL,'chat.message.sent','communications','chat_message','33','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','904c7d387bc3c97844c2fc4a7b875667','2026-07-17 22:26:44'),
(127,NULL,NULL,'chat.message.sent','communications','chat_message','34','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','904c7d387bc3c97844c2fc4a7b875667','2026-07-17 22:26:44'),
(130,NULL,NULL,'chat.message.sent','communications','chat_message','35','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','86c9e017049140f863a1956e960066be','2026-07-17 22:45:16'),
(131,NULL,NULL,'chat.message.sent','communications','chat_message','36','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','86c9e017049140f863a1956e960066be','2026-07-17 22:45:16'),
(132,1,NULL,'chat.message.sent','communications','chat_message','37','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','86c9e017049140f863a1956e960066be','2026-07-17 22:45:16'),
(133,NULL,NULL,'chat.message.sent','communications','chat_message','38','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','86c9e017049140f863a1956e960066be','2026-07-17 22:45:16'),
(167,NULL,NULL,'chat.message.sent','communications','chat_message','49','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','d188ae4c8463bca54721217aabcac446','2026-07-20 16:55:19'),
(168,NULL,NULL,'chat.message.sent','communications','chat_message','50','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','d188ae4c8463bca54721217aabcac446','2026-07-20 16:55:19'),
(169,1,NULL,'chat.message.sent','communications','chat_message','51','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','d188ae4c8463bca54721217aabcac446','2026-07-20 16:55:19'),
(170,NULL,NULL,'chat.message.sent','communications','chat_message','52','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','d188ae4c8463bca54721217aabcac446','2026-07-20 16:55:19'),
(254,51,51,'auth.google_login','auth','user','51','login','success','Inicio de sesion exitoso mediante Google.',NULL,'::1','Chrome / Windows','cc0731b6c5f10116895e9828f7bae823','2026-07-20 20:41:33'),
(255,51,51,'auth.logout','auth','user','51','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','aa037124cec84ceeb23d280a380b3187','2026-07-20 20:41:54'),
(256,3,3,'auth.login_success','auth','user','3','login','success','Inicio de sesión exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','109638fd2b710bc364bb179495f64ff6','2026-07-20 20:42:28'),
(257,3,3,'auth.logout','auth','user','3','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','f056d83eb58efe502ebf3969ff22098c','2026-07-20 20:42:55'),
(258,1,1,'auth.login_success','auth','user','1','login','success','Inicio de sesión exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','caaa4ceb65378575144f78c6cfdec283','2026-07-20 20:43:01'),
(259,1,1,'auth.logout','auth','user','1','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','567d1e903db3c13e52dc73958d504e98','2026-07-20 20:43:42'),
(260,1,1,'auth.login_success','auth','user','1','login','success','Inicio de sesión exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','3d65b6f7697a177287c51521a40d4a11','2026-07-20 20:44:09'),
(261,1,NULL,'database.backup.created','backups','database_backup','10','create','success','Se creó una copia privada de la base de datos.','{\"type\":\"manual\",\"size_bytes\":62058,\"tables\":79,\"rows\":696}','::1','Chrome / Windows','e0719e9e6c9a46bedae36e039d1e7583','2026-07-20 20:44:27'),
(262,1,NULL,'database.backup.downloaded','backups','database_backup','10','download','success','Un administrador descargó una copia protegida de la base de datos.',NULL,'::1','Chrome / Windows','aed0eb637034d8f8ca7a9e5b22984f53','2026-07-20 20:44:31'),
(263,1,1,'auth.login_success','auth','user','1','login','success','Inicio de sesión exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','71dccf0df76be1fe632d66a10383fff7','2026-07-20 21:11:06'),
(264,1,1,'auth.logout','auth','user','1','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','4b4a35a3f5928aae67522f716a4c2c60','2026-07-20 21:19:40'),
(265,51,51,'auth.google_login','auth','user','51','login','success','Inicio de sesion exitoso mediante Google.',NULL,'::1','Chrome / Windows','33f729addb79d2077b353a0ad40260bc','2026-07-20 21:19:53'),
(266,51,51,'auth.logout','auth','user','51','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','a46aecbb14262c98bda4a35571056207','2026-07-20 21:27:26'),
(267,2,2,'auth.login_success','auth','user','2','login','success','Inicio de sesión exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','dc1d8f4ebfefe287ae7b979855abe8cb','2026-07-20 21:27:36'),
(268,2,2,'receipt.downloaded','payments','order','15','download','success','El propietario descargo su comprobante interno de compra.',NULL,'::1','Chrome / Windows','421ac2b1362a5677c8a044eebc1d2c5e','2026-07-20 21:28:21'),
(269,2,2,'auth.logout','auth','user','2','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','0fa243cca7baa815c2d3f349adb190e5','2026-07-20 21:28:57'),
(270,3,3,'auth.login_success','auth','user','3','login','success','Inicio de sesión exitoso.','{\"credential_upgraded\":false}','::1','Chrome / Windows','e25330288b5575788c72afaa0e4235aa','2026-07-20 21:29:16'),
(271,1,NULL,'database.backup.created','backups','database_backup','11','create','success','Se creó una copia privada de la base de datos.','{\"type\":\"manual\",\"size_bytes\":62763,\"tables\":79,\"rows\":711}',NULL,'CLI','f26ad6fbe4c6098dbe79ee1d3d871b84','2026-07-20 21:40:00'),
(281,3,3,'auth.logout','auth','user','3','logout','success','Cierre de sesion exitoso.',NULL,'::1','Chrome / Windows','dbf9771ee1824dab0404e96c3c3e2299','2026-07-20 21:41:59'),
(288,NULL,NULL,'auth.login_failed','auth',NULL,NULL,'login','failure','Falló un inicio de sesión por credenciales o estado de cuenta no válidos.',NULL,'::1','Edge / Windows','637afe54edc4aa975864ae694527f96d','2026-07-20 21:42:41'),
(298,NULL,NULL,'chat.message.sent','communications','chat_message','69','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','e97fa8cffbbb2ce6dbca9e0354736562','2026-07-20 21:47:04'),
(299,NULL,NULL,'chat.message.sent','communications','chat_message','70','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','e97fa8cffbbb2ce6dbca9e0354736562','2026-07-20 21:47:04'),
(300,1,NULL,'chat.message.sent','communications','chat_message','71','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','e97fa8cffbbb2ce6dbca9e0354736562','2026-07-20 21:47:04'),
(301,NULL,NULL,'chat.message.sent','communications','chat_message','72','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','e97fa8cffbbb2ce6dbca9e0354736562','2026-07-20 21:47:04'),
(302,NULL,NULL,'chat.message.sent','communications','chat_message','73','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','1133a96d090ee6c0354ee16799af4969','2026-07-20 21:47:41'),
(303,NULL,NULL,'chat.message.sent','communications','chat_message','74','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','1133a96d090ee6c0354ee16799af4969','2026-07-20 21:47:41'),
(304,1,NULL,'chat.message.sent','communications','chat_message','75','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','1133a96d090ee6c0354ee16799af4969','2026-07-20 21:47:41'),
(305,NULL,NULL,'chat.message.sent','communications','chat_message','76','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','1133a96d090ee6c0354ee16799af4969','2026-07-20 21:47:41'),
(306,NULL,NULL,'chat.message.sent','communications','chat_message','77','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','59ad4c1793ef745ee43fba6c9656e517','2026-07-20 21:48:29'),
(307,NULL,NULL,'chat.message.sent','communications','chat_message','78','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','59ad4c1793ef745ee43fba6c9656e517','2026-07-20 21:48:29'),
(308,1,NULL,'chat.message.sent','communications','chat_message','79','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','59ad4c1793ef745ee43fba6c9656e517','2026-07-20 21:48:29'),
(309,NULL,NULL,'chat.message.sent','communications','chat_message','80','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','59ad4c1793ef745ee43fba6c9656e517','2026-07-20 21:48:29'),
(312,NULL,NULL,'chat.message.sent','communications','chat_message','81','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','6d7a2a052ada763d1665f79b287268d4','2026-07-20 21:48:48'),
(313,NULL,NULL,'chat.message.sent','communications','chat_message','82','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','6d7a2a052ada763d1665f79b287268d4','2026-07-20 21:48:48'),
(314,1,NULL,'chat.message.sent','communications','chat_message','83','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','6d7a2a052ada763d1665f79b287268d4','2026-07-20 21:48:48'),
(315,NULL,NULL,'chat.message.sent','communications','chat_message','84','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','6d7a2a052ada763d1665f79b287268d4','2026-07-20 21:48:48'),
(346,NULL,NULL,'chat.message.sent','communications','chat_message','87','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','d7443911d6f4251d44c1690271c3b4ca','2026-07-20 22:14:33'),
(347,NULL,NULL,'chat.message.sent','communications','chat_message','88','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','d7443911d6f4251d44c1690271c3b4ca','2026-07-20 22:14:33'),
(348,1,NULL,'chat.message.sent','communications','chat_message','89','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','d7443911d6f4251d44c1690271c3b4ca','2026-07-20 22:14:33'),
(349,NULL,NULL,'chat.message.sent','communications','chat_message','90','create','success','Mensaje interno enviado.',NULL,NULL,'CLI','d7443911d6f4251d44c1690271c3b4ca','2026-07-20 22:14:33');

-- Información de auth_remember_tokens
-- Información de capacitacion_pagos
INSERT INTO `capacitacion_pagos` (`id`,`usuario_id`,`asignatura_id`,`checkout_key`,`stripe_checkout_session_id`,`stripe_payment_intent_id`,`importe`,`moneda`,`estado`,`last_stripe_event_id`,`paid_at`,`created_at`,`updated_at`) VALUES
(22,51,7,'1c084a11b728c4747e87a3f85cefd254d56c1fad5918b07a3b88e4740b8506b9','cs_test_a1JiFXFw6PKkoKfYJ5yMOazIgHnWiKEj3RLBBnXyzvgt1aPmoTRgOwUjzl','pi_3TuEapE8YH5P1jJk0cJQBbq2',100.00,'usd','pagado','evt_1TuEarE8YH5P1jJkkFK9gW3A','2026-07-17 10:26:34','2026-07-17 10:25:57','2026-07-17 10:26:34');

-- Información de capacitacion_secciones
-- Información de capacitacion_seccion_historial
-- Información de carritos
INSERT INTO `carritos` (`id`,`usuario_id`,`estado`,`version`,`created_at`,`updated_at`) VALUES
(8,2,'convertido',3,'2026-07-16 21:59:42','2026-07-16 22:01:34'),
(9,2,'activo',2,'2026-07-17 21:58:51','2026-07-20 21:27:59'),
(25,51,'activo',2,'2026-07-20 21:20:02','2026-07-20 21:20:16');

-- Información de categorias_producto
INSERT INTO `categorias_producto` (`id`,`nombre`,`slug`,`descripcion`,`imagen`,`activo`,`eliminado_at`,`creado_por`,`actualizado_por`,`created_at`,`updated_at`) VALUES
(16,'Comida','comida',NULL,NULL,1,NULL,1,1,'2026-07-14 15:37:46','2026-07-14 15:37:46');

-- Información de chat_bloqueos
-- Información de chat_conversaciones
INSERT INTO `chat_conversaciones` (`id`,`tipo`,`clave_individual`,`creado_por`,`estado`,`ultimo_mensaje_at`,`created_at`,`updated_at`) VALUES
(29,'individual','85f2ef987b76f4c3fc081acef84e0a730f5df8a2488a5bb7ddae4f7dee721ed8',3,'activa','2026-07-17 21:18:17','2026-07-17 19:43:16','2026-07-17 21:18:17'),
(30,'individual','471321f3a601b378ef278ff9e5ab9e68e18c76158dfaf3bef8fab230ff285cb7',1,'activa','2026-07-17 21:17:49','2026-07-17 21:17:26','2026-07-17 21:17:49'),
(31,'individual','673aeeb08cfbb00b91e5e3c60b5ba31896d55b522b1be2dccb0645a59bae8775',2,'activa',NULL,'2026-07-17 22:02:13','2026-07-17 22:02:13');

-- Información de chat_mensajes
INSERT INTO `chat_mensajes` (`id`,`conversacion_id`,`remitente_id`,`respuesta_a_id`,`contenido`,`idempotency_key`,`estado`,`entregado_at`,`eliminado_at`,`eliminado_por`,`created_at`,`updated_at`) VALUES
(29,30,1,NULL,'Bro pon tus datos correctamente',NULL,'activo',NULL,NULL,NULL,'2026-07-17 21:17:49','2026-07-17 21:17:49'),
(30,29,1,NULL,'TRABAJE MAITRO',NULL,'activo','2026-07-20 20:42:43',NULL,NULL,'2026-07-17 21:18:17','2026-07-20 20:42:43');

-- Información de chat_participantes
INSERT INTO `chat_participantes` (`conversacion_id`,`usuario_id`,`ultimo_leido_mensaje_id`,`unido_at`,`archivado_at`) VALUES
(29,1,30,'2026-07-17 19:43:16',NULL),
(29,3,30,'2026-07-17 19:43:16',NULL),
(30,1,29,'2026-07-17 21:17:26',NULL),
(30,51,NULL,'2026-07-17 21:17:26',NULL),
(31,1,NULL,'2026-07-17 22:02:13',NULL),
(31,2,NULL,'2026-07-17 22:02:13',NULL);

-- Información de chat_reportes
-- Información de contenidos
-- Información de correo_centro_hilos
INSERT INTO `correo_centro_hilos` (`id`,`asunto`,`usuario_relacionado_id`,`ultimo_mensaje_at`,`created_at`,`updated_at`) VALUES
(1,'Prueba de comunicacion',1,'2026-07-17 19:44:08','2026-07-17 19:44:08','2026-07-17 19:44:08');

-- Información de correo_centro_mensajes
INSERT INTO `correo_centro_mensajes` (`id`,`hilo_id`,`direccion`,`autor_usuario_id`,`remitente`,`destinatario`,`reply_to`,`asunto`,`contenido_texto`,`message_id_servidor`,`uid_imap`,`carpeta_imap`,`in_reply_to`,`estado`,`leido_at`,`error_sanitizado`,`enviado_recibido_at`,`created_at`) VALUES
(1,1,'salida',3,'ateneanaturopatia@gmail.com','admin@atenea.local','docente@atenea.local','Prueba de comunicacion','prueba de comunicacion entre usuarios',NULL,NULL,NULL,NULL,'enviado',NULL,NULL,'2026-07-17 19:44:08','2026-07-17 19:44:08');

-- Información de direcciones_usuario
INSERT INTO `direcciones_usuario` (`id`,`usuario_id`,`etiqueta`,`etiqueta_personalizada`,`etiqueta_normalizada`,`receptor`,`telefono`,`departamento_id`,`municipio_id`,`distrito_id`,`direccion_detallada`,`referencias`,`predeterminada`,`activa`,`created_at`,`updated_at`) VALUES
(5,2,'casa',NULL,'casa','santa tecla','61156808',3,14,53,'Santa tecla','Por el palo de espino',0,1,'2026-07-16 22:01:26','2026-07-16 22:01:26');

-- Información de docentes_asignaturas
-- Información de dte_configuracion
-- Información de entregas_contenido
-- Información de entrega_evidencias
-- Información de entrega_revisiones
-- Información de estudiantes_docentes
-- Información de evaluaciones
-- Información de ev_entregadas
-- Información de historial_cambios_cuenta
-- Información de inscripciones_capacitacion
INSERT INTO `inscripciones_capacitacion` (`id`,`usuario_id`,`asignatura_id`,`pago_id`,`seccion_id`,`docente_id`,`estado`,`asignacion_limite_at`,`ultimo_intento_asignacion_at`,`asignado_por`,`metodo_asignacion`,`assigned_at`,`finalizacion_confirmada_por`,`finalizacion_confirmada_at`,`created_at`,`updated_at`) VALUES
(22,51,7,22,NULL,NULL,'pendiente_asignacion',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-17 10:26:34','2026-07-17 10:26:34');

-- Información de inscripcion_movimientos
-- Información de menu_sitio
INSERT INTO `menu_sitio` (`id`,`padre_id`,`texto`,`slug`,`icono`,`url`,`nueva_pestana`,`visibilidad`,`roles_json`,`tipo_contenido`,`contenido_html`,`contenido_json`,`activo`,`eliminado_at`,`eliminado_por`,`orden`,`created_at`,`updated_at`) VALUES
(1,NULL,'Inicio','menu-1',NULL,'index.php',0,'publica',NULL,'enlace_interno',NULL,NULL,1,NULL,NULL,10,'2026-07-12 09:44:33','2026-07-20 11:43:19'),
(2,NULL,'Nosotros','menu-2',NULL,'src/website/about.php',0,'publica',NULL,'enlace_interno',NULL,NULL,1,NULL,NULL,20,'2026-07-12 09:44:33','2026-07-20 11:43:19'),
(3,NULL,'Capacitaciones','menu-3',NULL,'src/website/courses.php',0,'publica',NULL,'enlace_interno',NULL,NULL,1,NULL,NULL,30,'2026-07-12 09:44:33','2026-07-20 11:43:19'),
(4,NULL,'Docentes','menu-4',NULL,'src/website/trainers.php',0,'publica',NULL,'enlace_interno',NULL,NULL,1,NULL,NULL,40,'2026-07-12 09:44:33','2026-07-20 11:43:19'),
(5,NULL,'Eventos','menu-5',NULL,'src/website/events.php',0,'publica',NULL,'enlace_interno',NULL,NULL,1,NULL,NULL,50,'2026-07-12 09:44:33','2026-07-20 11:43:19'),
(6,NULL,'Productos','menu-6',NULL,'src/website/pricing.php',0,'publica',NULL,'enlace_interno',NULL,NULL,1,NULL,NULL,60,'2026-07-12 09:44:33','2026-07-20 11:43:19'),
(7,NULL,'Noticias','menu-7',NULL,'src/website/noticias.php',0,'publica',NULL,'enlace_interno',NULL,NULL,1,NULL,NULL,70,'2026-07-12 09:44:33','2026-07-20 11:43:19'),
(8,NULL,'Contacto','menu-8',NULL,'src/website/contact.php',0,'publica',NULL,'enlace_interno',NULL,NULL,1,NULL,NULL,80,'2026-07-12 09:44:33','2026-07-20 11:43:19');

-- Información de notas
-- Información de notas_historial
-- Información de noticias
INSERT INTO `noticias` (`id`,`titulo`,`slug`,`resumen`,`contenido`,`imagen_portada`,`fecha_publicacion`,`autor`,`estado`,`destacado`,`activo`,`deleted_at`,`eliminado_por`,`created_at`,`updated_at`) VALUES
(1,'Escuela Atenea','escuela-atenea','La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.','La naturopatía holística es un sistema de medicina alternativa que aborda a la persona de manera integral: mente, cuerpo y espíritu. Se enfoca en hábitos, métodos naturales y prevención para apoyar el bienestar general.','uploads/contenido/migrado/noticia_6972a485aa6e4_1769120901.jpg','2026-01-21 08:00:00',NULL,'publicado',0,1,NULL,NULL,'2026-07-16 22:46:38','2026-07-16 22:46:38'),
(2,'Conoterapia','conoterapia','Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.','Información sobre la conoterapia y el cuidado de los oídos. Ante molestias, exceso de cerumen o dolor, se recomienda acudir a un profesional de salud para una valoración segura.','img/noticia_6972a39b01db9_1769120667.jpg','2024-05-15 08:00:00',NULL,'publicado',0,1,NULL,NULL,'2026-07-16 22:46:38','2026-07-17 09:30:06'),
(3,'Naturopatía','naturopatia','En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.','En ATENEA Escuela de Naturopatía Holística brindamos formación integral en terapias naturales y salud holística, combinando conocimiento académico, conciencia humana y ética profesional.','img/noticia_6972a279886dc_1769120377.jpg','2026-01-22 08:00:00',NULL,'publicado',0,1,NULL,NULL,'2026-07-16 22:46:38','2026-07-17 09:30:06');

-- Información de notificacion_preferencias
-- Información de password_reset_tokens
-- Información de pedidos
INSERT INTO `pedidos` (`id`,`numero`,`usuario_id`,`carrito_id`,`direccion_id`,`direccion_snapshot`,`subtotal`,`descuento`,`envio`,`impuestos`,`total`,`moneda`,`checkout_key`,`estado`,`payment_status`,`paid_at`,`stripe_checkout_session_id`,`stripe_payment_intent_id`,`payment_brand`,`payment_last4`,`stripe_payment_method_id`,`last_stripe_event_id`,`receipt_generated_at`,`email_sent_at`,`stock_procesado`,`created_at`,`updated_at`) VALUES
(5,'AT-20260714-58ED796B',2,NULL,NULL,NULL,95.00,0.00,0.00,0.00,95.00,'usd',NULL,'pendiente_pago','pending',NULL,'cs_test_a1FAlXjqydC4BAIvLSwQSmHpRRsaI6XDLJEKagozJSiHl1xlI0XUD6tB0V',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-07-14 15:39:41','2026-07-15 12:46:03'),
(6,'AT-20260714-D7F5B0AE',2,NULL,NULL,NULL,95.00,0.00,0.00,0.00,95.00,'usd',NULL,'pendiente_pago','pending',NULL,'cs_test_a1XyTOrYjv6EgfLKcNcG2ut6qJ16qedswJGCCWKKL2bV8YzFySsM8LTCEH',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-07-14 20:54:49','2026-07-15 12:46:03'),
(15,'AT-20260716-CF73FD86',2,8,5,'{\"id\":5,\"etiqueta\":\"casa\",\"etiqueta_personalizada\":null,\"receptor\":\"santa tecla\",\"telefono\":\"61156808\",\"departamento_id\":3,\"municipio_id\":14,\"distrito_id\":53,\"direccion_detallada\":\"Santa tecla\",\"referencias\":\"Por el palo de espino\",\"departamento\":\"La Libertad\",\"municipio\":\"La Libertad Sur\",\"distrito\":\"Santa Tecla\"}',190.00,0.00,0.00,0.00,190.00,'usd','e5de5e5df167f224417b18c5f1048b2219615bce39b697b38f7f8032f7cd6e77','pagado','paid','2026-07-16 22:02:16','cs_test_a1oj6bH9GSfOXemVd836wX0uLV8dreqszTKjJ05YEU2YVDqOnDgXeZDIGU','pi_3Tu2yUE8YH5P1jJk0L2sIvMl','visa','4242','pm_1Tu2yTE8YH5P1jJkGmVw1PMt','evt_1Tu2yVE8YH5P1jJkiWsofEHs','2026-07-16 22:02:16','2026-07-16 22:02:19',1,'2026-07-16 22:01:34','2026-07-16 22:02:19');

-- Información de personalizaciones_visuales
-- Información de personalizaciones_visuales_historial
-- Información de productos
INSERT INTO `productos` (`id`,`categoria_id`,`sku`,`nombre`,`slug`,`descripcion_corta`,`descripcion`,`tipo_producto`,`caracteristicas`,`informacion_entrega`,`precio`,`stock`,`stock_reservado`,`stock_minimo`,`disponible`,`activo`,`imagen_principal`,`eliminado_at`,`creado_por`,`actualizado_por`,`created_at`,`updated_at`) VALUES
(4,16,NULL,'Dulces de uva','ulces-de-uva-d8bb60c4','dulces de uva','dulces de uva por unidad de 100','producto',NULL,NULL,95.00,78,2,0,1,1,'uploads/contenido/a47e78dee59243f8dc71c59f3a07ed00.png',NULL,1,1,'2026-07-14 15:38:46','2026-07-16 22:02:16'),
(6,16,NULL,'Prueba34','rueba34-b5932993','grgrsrgrg','wgrgergeegr','producto',NULL,NULL,100.00,0,0,0,1,1,NULL,NULL,1,1,'2026-07-15 14:31:30','2026-07-15 14:31:30');

-- Información de producto_imagenes
-- Información de progreso_contenido
-- Información de promociones
-- Información de respaldos_base_datos
INSERT INTO `respaldos_base_datos` (`id`,`creado_por`,`restaurado_por`,`respaldo_previo_id`,`tipo`,`nombre_archivo`,`ruta_relativa`,`tamano_bytes`,`sha256`,`estado`,`tablas_incluidas`,`filas_incluidas`,`error_sanitizado`,`restaurado_at`,`eliminado_at`,`created_at`,`updated_at`) VALUES
(10,1,NULL,NULL,'manual','atenea-db-20260720-204427-552a419ded6d.atenea-db.gz','atenea-db-20260720-204427-552a419ded6d.atenea-db.gz',62058,'f25deb31da9db3fc3d7cfb6be24a4fa0ecafa766d18bd199d984c1365f915b58','disponible',79,696,NULL,NULL,NULL,'2026-07-20 20:44:27','2026-07-20 20:44:27'),
(11,1,NULL,NULL,'manual','atenea-db-20260720-213959-5df7966c5e1f.atenea-db.gz','atenea-db-20260720-213959-5df7966c5e1f.atenea-db.gz',62763,'6d058c9b591e89b5e5808362c27dca0793c6b93ed7d5d4cdbce1e3dc4ab50cf0','disponible',79,711,NULL,NULL,NULL,'2026-07-20 21:39:59','2026-07-20 21:40:00');

-- Información de user_deletions
-- Información de carrito_items
INSERT INTO `carrito_items` (`id`,`carrito_id`,`producto_id`,`cantidad`,`created_at`,`updated_at`) VALUES
(6,8,4,2,'2026-07-16 21:59:42','2026-07-16 21:59:52'),
(32,22,4,1,'2026-07-20 18:27:22','2026-07-20 18:27:22'),
(33,23,4,1,'2026-07-20 18:28:03','2026-07-20 18:28:03'),
(38,25,4,5,'2026-07-20 21:20:16','2026-07-20 21:20:16'),
(39,9,4,8,'2026-07-20 21:27:59','2026-07-20 21:27:59'),
(42,27,4,1,'2026-07-20 21:42:36','2026-07-20 21:42:37'),
(45,28,4,1,'2026-07-20 21:45:17','2026-07-20 21:45:18'),
(50,30,4,1,'2026-07-20 21:51:03','2026-07-20 21:51:04'),
(53,31,4,1,'2026-07-20 21:52:01','2026-07-20 21:52:02'),
(58,33,4,1,'2026-07-20 22:15:32','2026-07-20 22:15:33');

-- Información de certificados_capacitacion
-- Información de chat_adjuntos
-- Información de chat_lecturas
INSERT INTO `chat_lecturas` (`mensaje_id`,`usuario_id`,`leido_at`) VALUES
(29,1,'2026-07-17 21:17:49'),
(30,1,'2026-07-17 21:18:17'),
(30,3,'2026-07-17 22:17:22');

-- Información de comunicacion_hilos
-- Información de correo_centro_adjuntos
-- Información de correo_envios
INSERT INTO `correo_envios` (`id`,`tipo`,`asunto`,`usuario_id`,`pedido_id`,`hilo_id`,`destinatario_enmascarado`,`destinatario_hash`,`destinatario_email`,`destinatario_nombre`,`contenido_html`,`contenido_texto`,`opciones_json`,`idempotency_key`,`evento_id`,`grupo_clave`,`estado`,`disponible_at`,`intento`,`max_intentos`,`es_modo_prueba`,`permitir_envio_prueba`,`agrupados`,`error_sanitizado`,`procesando_desde`,`enviado_at`,`cancelado_at`,`cancelado_motivo`,`created_at`,`updated_at`) VALUES
(5,'contacto_recibido',NULL,NULL,NULL,NULL,'a********@gmail.com','90a6fd1924ceccac41c2ae7af0fd6bfaaea41c26feb1a31ee4780a7c84dd5fcf',NULL,NULL,NULL,NULL,NULL,'contacto:9eff2a395a8c59e77a5e0485fa565e850dcb9f4b2850ffb796e24cf5a7c7c3c5','contacto:9eff2a395a8c59e77a5e0485fa565e850dcb9f4b2850ffb796e24cf5a7c7c3c5',NULL,'enviado','2026-07-17 23:06:44',1,3,0,0,1,NULL,NULL,'2026-07-14 23:53:19',NULL,NULL,'2026-07-14 23:53:18','2026-07-17 23:06:44'),
(6,'contacto_recibido',NULL,NULL,NULL,NULL,'a********@gmail.com','90a6fd1924ceccac41c2ae7af0fd6bfaaea41c26feb1a31ee4780a7c84dd5fcf',NULL,NULL,NULL,NULL,NULL,'contacto:8ee6e3e12e3d9d355af71499b8f9d86c31f8004abc879c8d496b6b670c9d434a','contacto:8ee6e3e12e3d9d355af71499b8f9d86c31f8004abc879c8d496b6b670c9d434a',NULL,'enviado','2026-07-17 23:06:44',1,3,0,0,1,NULL,NULL,'2026-07-15 00:18:14',NULL,NULL,'2026-07-15 00:18:10','2026-07-17 23:06:44'),
(9,'compra_confirmada','Pago confirmado · Pedido AT-20260716-CF73FD86',2,15,NULL,'u******@atenea.local','161719a2674722977180eca96ff5b8c9b0b15a50ea9c5f799eccb540410704ee',NULL,NULL,NULL,NULL,NULL,'compra-confirmada:pedido:15','compra-confirmada:pedido:15',NULL,'enviado','2026-07-17 23:06:44',1,3,0,0,1,NULL,NULL,'2026-07-16 22:02:19',NULL,NULL,'2026-07-16 22:02:16','2026-07-17 23:06:44'),
(10,'aviso_administrativo','Corrige tus campos',51,NULL,NULL,'r********@gmail.com','86941e8c55a52781f2ad246ea03c5e3e4ef6ea09db401a3a61601f82db8cdbfa',NULL,NULL,NULL,NULL,NULL,'admin-notice:12','admin-notice:12',NULL,'enviado','2026-07-17 23:06:44',1,3,0,0,1,NULL,NULL,'2026-07-17 00:13:27',NULL,NULL,'2026-07-17 00:13:24','2026-07-17 23:06:44'),
(37,'aviso_administrativo','Pago e inscripción confirmados · Terapias Naturales Avanzadas',51,NULL,NULL,'r********@gmail.com','86941e8c55a52781f2ad246ea03c5e3e4ef6ea09db401a3a61601f82db8cdbfa',NULL,NULL,NULL,NULL,NULL,'capacitacion-confirmada:pago:22','capacitacion-confirmada:pago:22',NULL,'enviado','2026-07-17 23:06:44',1,3,0,0,1,NULL,NULL,'2026-07-17 10:26:36',NULL,NULL,'2026-07-17 10:26:34','2026-07-17 23:06:44'),
(83,'centro_comunicaciones','Prueba de comunicacion',1,NULL,NULL,'a****@atenea.local','53fa98c28cc4933ff2958cd82c3bb683c160fa72f2dff28886418c1a44ce5b69',NULL,NULL,NULL,NULL,NULL,'centro-correo:1','centro-correo:1',NULL,'enviado','2026-07-17 23:06:44',1,3,0,0,1,NULL,NULL,'2026-07-17 19:44:10',NULL,NULL,'2026-07-17 19:44:08','2026-07-17 23:06:44'),
(224,'test_etapa5','Prueba SMTP',NULL,NULL,NULL,'d******@example.invalid','9adb704ba90d2860dfeb4737da9fd6100833e35b68a3e0d1d14b5307ab2115e3','destino@example.invalid','Destino','<p>x</p>','x','{\"tipo\":\"test_etapa5\",\"idempotency_key\":\"c6e5_55ea9982:smtp\"}','c6e5_55ea9982:smtp','c6e5_55ea9982:smtp',NULL,'cancelado','2026-07-20 16:55:19',0,3,1,0,1,NULL,NULL,NULL,'2026-07-20 16:55:19','Conservado sin envío por MAIL_TEST_MODE.','2026-07-20 16:55:19','2026-07-20 16:55:19'),
(312,'aviso_administrativo','Asignación académica completada · Temporal A',NULL,NULL,NULL,'c********@example.invalid','fe91b45f2ea4e3ab7107ec6907ef167ad194a81868e5836482c39b7c76b30eb4','c6e3_513b16532@example.invalid','Tmp2 Integración','<!doctype html><html lang=\"es\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"></head><body style=\"margin:0;padding:0;background:#f7f4ec;color:#20251f;font-family:Arial,Helvetica,sans-serif;\"><div style=\"display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;\">Tu sección y docente ya fueron asignados.</div><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"background:#f7f4ec;\"><tr><td align=\"center\" style=\"padding:24px 12px;\"><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"max-width:640px;background:#ffffff;border:1px solid #e2dccd;border-radius:12px;overflow:hidden;\"><tr><td align=\"center\" style=\"padding:24px;background:#173f35;\"><img data-atenea-email-logo=\"1\" src=\"cid:atenea-logo\" width=\"150\" alt=\"Atenea Escuela de Naturopatía Holística\" style=\"display:block;margin:0 auto;max-width:150px;height:auto;border:0;outline:none;text-decoration:none;\"></td></tr><tr><td style=\"padding:32px 28px;\"><h1 style=\"margin:0 0 20px;color:#173f35;font-size:26px;line-height:1.25;\">Asignación académica completada · Temporal A</h1><p style=\"margin:0 0 16px;line-height:1.65;\">Hola, Tmp2 Integración.</p><p style=\"margin:0 0 16px;line-height:1.65;\">Sección: Sección llena (c6e3_513b1653-S29). Docente: Tmp1 Integración. Horario: por confirmar. Inicio: por confirmar.</p><p style=\"margin:24px 0;\"><a href=\"http://localhost/Atenea/src/estudiantes/clase.php\" style=\"display:inline-block;padding:13px 22px;background:#c49a3a;color:#ffffff;text-decoration:none;border-radius:7px;font-weight:700;\">Ver mi clase</a></p></td></tr><tr><td style=\"padding:20px 28px;background:#f7f4ec;border-top:1px solid #e2dccd;text-align:center;color:#5a625b;font-size:12px;line-height:1.6;\">Atenea Escuela de Naturopatía Holística<br>Av. El Níspero Final, Huizúcar · ateneanaturopatia@gmail.com<br>Enviado el 20/07/2026 21:47 (hora de El Salvador)</td></tr></table></td></tr></table></body></html>','Asignación académica completada · Temporal A\n\nHola, Tmp2 Integración.\n\nSección: Sección llena (c6e3_513b1653-S29). Docente: Tmp1 Integración. Horario: por confirmar. Inicio: por confirmar.\n\nAtenea Escuela de Naturopatía Holística\nAv. El Níspero Final, Huizúcar\nEnviado el 20/07/2026 21:47 (hora de El Salvador)','{\"usuario_id\":491,\"idempotency_key\":\"capacitacion-asignada:inscripcion:215\",\"tipo\":\"aviso_administrativo\"}','capacitacion-asignada:inscripcion:215','capacitacion-asignada:inscripcion:215',NULL,'cancelado','2026-07-20 21:47:03',0,3,1,0,1,NULL,NULL,NULL,'2026-07-20 21:47:03','Conservado sin envío por MAIL_TEST_MODE.','2026-07-20 21:47:03','2026-07-20 21:47:03'),
(315,'aviso_administrativo','Asignación académica completada · Temporal CONC',NULL,NULL,NULL,'c********@example.invalid','fe91b45f2ea4e3ab7107ec6907ef167ad194a81868e5836482c39b7c76b30eb4','c6e3_513b16532@example.invalid','Tmp2 Integración','<!doctype html><html lang=\"es\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"></head><body style=\"margin:0;padding:0;background:#f7f4ec;color:#20251f;font-family:Arial,Helvetica,sans-serif;\"><div style=\"display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;\">Tu sección y docente ya fueron asignados.</div><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"background:#f7f4ec;\"><tr><td align=\"center\" style=\"padding:24px 12px;\"><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"max-width:640px;background:#ffffff;border:1px solid #e2dccd;border-radius:12px;overflow:hidden;\"><tr><td align=\"center\" style=\"padding:24px;background:#173f35;\"><img data-atenea-email-logo=\"1\" src=\"cid:atenea-logo\" width=\"150\" alt=\"Atenea Escuela de Naturopatía Holística\" style=\"display:block;margin:0 auto;max-width:150px;height:auto;border:0;outline:none;text-decoration:none;\"></td></tr><tr><td style=\"padding:32px 28px;\"><h1 style=\"margin:0 0 20px;color:#173f35;font-size:26px;line-height:1.25;\">Asignación académica completada · Temporal CONC</h1><p style=\"margin:0 0 16px;line-height:1.65;\">Hola, Tmp2 Integración.</p><p style=\"margin:0 0 16px;line-height:1.65;\">Sección: Último cupo concurrente (c6e3_513b1653-CONC). Docente: Tmp5 Integración. Horario: por confirmar. Inicio: por confirmar.</p><p style=\"margin:24px 0;\"><a href=\"http://localhost/Atenea/src/estudiantes/clase.php\" style=\"display:inline-block;padding:13px 22px;background:#c49a3a;color:#ffffff;text-decoration:none;border-radius:7px;font-weight:700;\">Ver mi clase</a></p></td></tr><tr><td style=\"padding:20px 28px;background:#f7f4ec;border-top:1px solid #e2dccd;text-align:center;color:#5a625b;font-size:12px;line-height:1.6;\">Atenea Escuela de Naturopatía Holística<br>Av. El Níspero Final, Huizúcar · ateneanaturopatia@gmail.com<br>Enviado el 20/07/2026 21:47 (hora de El Salvador)</td></tr></table></td></tr></table></body></html>','Asignación académica completada · Temporal CONC\n\nHola, Tmp2 Integración.\n\nSección: Último cupo concurrente (c6e3_513b1653-CONC). Docente: Tmp5 Integración. Horario: por confirmar. Inicio: por confirmar.\n\nAtenea Escuela de Naturopatía Holística\nAv. El Níspero Final, Huizúcar\nEnviado el 20/07/2026 21:47 (hora de El Salvador)','{\"usuario_id\":491,\"idempotency_key\":\"capacitacion-asignada:inscripcion:217\",\"tipo\":\"aviso_administrativo\"}','capacitacion-asignada:inscripcion:217','capacitacion-asignada:inscripcion:217',NULL,'cancelado','2026-07-20 21:47:03',0,3,1,0,1,NULL,NULL,NULL,'2026-07-20 21:47:03','Conservado sin envío por MAIL_TEST_MODE.','2026-07-20 21:47:03','2026-07-20 21:47:03'),
(317,'aviso_administrativo','Asignación académica completada · Temporal A',NULL,NULL,NULL,'c********@example.invalid','916380545c7603cd3a03c76d0c96542b2f1a10ae42f555b85c5eae0efb6ba92d','c6e3_513b16533@example.invalid','Tmp3 Integración','<!doctype html><html lang=\"es\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"></head><body style=\"margin:0;padding:0;background:#f7f4ec;color:#20251f;font-family:Arial,Helvetica,sans-serif;\"><div style=\"display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;\">Tu sección y docente ya fueron asignados.</div><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"background:#f7f4ec;\"><tr><td align=\"center\" style=\"padding:24px 12px;\"><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"max-width:640px;background:#ffffff;border:1px solid #e2dccd;border-radius:12px;overflow:hidden;\"><tr><td align=\"center\" style=\"padding:24px;background:#173f35;\"><img data-atenea-email-logo=\"1\" src=\"cid:atenea-logo\" width=\"150\" alt=\"Atenea Escuela de Naturopatía Holística\" style=\"display:block;margin:0 auto;max-width:150px;height:auto;border:0;outline:none;text-decoration:none;\"></td></tr><tr><td style=\"padding:32px 28px;\"><h1 style=\"margin:0 0 20px;color:#173f35;font-size:26px;line-height:1.25;\">Asignación académica completada · Temporal A</h1><p style=\"margin:0 0 16px;line-height:1.65;\">Hola, Tmp3 Integración.</p><p style=\"margin:0 0 16px;line-height:1.65;\">Sección: Destino manual (c6e3_513b1653-DEST). Docente: Tmp1 Integración. Horario: por confirmar. Inicio: por confirmar.</p><p style=\"margin:24px 0;\"><a href=\"http://localhost/Atenea/src/estudiantes/clase.php\" style=\"display:inline-block;padding:13px 22px;background:#c49a3a;color:#ffffff;text-decoration:none;border-radius:7px;font-weight:700;\">Ver mi clase</a></p></td></tr><tr><td style=\"padding:20px 28px;background:#f7f4ec;border-top:1px solid #e2dccd;text-align:center;color:#5a625b;font-size:12px;line-height:1.6;\">Atenea Escuela de Naturopatía Holística<br>Av. El Níspero Final, Huizúcar · ateneanaturopatia@gmail.com<br>Enviado el 20/07/2026 21:47 (hora de El Salvador)</td></tr></table></td></tr></table></body></html>','Asignación académica completada · Temporal A\n\nHola, Tmp3 Integración.\n\nSección: Destino manual (c6e3_513b1653-DEST). Docente: Tmp1 Integración. Horario: por confirmar. Inicio: por confirmar.\n\nAtenea Escuela de Naturopatía Holística\nAv. El Níspero Final, Huizúcar\nEnviado el 20/07/2026 21:47 (hora de El Salvador)','{\"usuario_id\":492,\"idempotency_key\":\"capacitacion-asignada:inscripcion:216\",\"tipo\":\"aviso_administrativo\"}','capacitacion-asignada:inscripcion:216','capacitacion-asignada:inscripcion:216',NULL,'cancelado','2026-07-20 21:47:04',0,3,1,0,1,NULL,NULL,NULL,'2026-07-20 21:47:04','Conservado sin envío por MAIL_TEST_MODE.','2026-07-20 21:47:04','2026-07-20 21:47:04'),
(318,'test_etapa5','Prueba SMTP',NULL,NULL,NULL,'d******@example.invalid','9adb704ba90d2860dfeb4737da9fd6100833e35b68a3e0d1d14b5307ab2115e3','destino@example.invalid','Destino','<p>x</p>','x','{\"tipo\":\"test_etapa5\",\"idempotency_key\":\"c6e5_8e0ca573:smtp\"}','c6e5_8e0ca573:smtp','c6e5_8e0ca573:smtp',NULL,'cancelado','2026-07-20 21:47:04',0,3,1,0,1,NULL,NULL,NULL,'2026-07-20 21:47:04','Conservado sin envío por MAIL_TEST_MODE.','2026-07-20 21:47:04','2026-07-20 21:47:04'),
(319,'test_etapa5','Prueba SMTP',NULL,NULL,NULL,'d******@example.invalid','9adb704ba90d2860dfeb4737da9fd6100833e35b68a3e0d1d14b5307ab2115e3','destino@example.invalid','Destino','<p>x</p>','x','{\"tipo\":\"test_etapa5\",\"idempotency_key\":\"c6e5_9e7154c8:smtp\"}','c6e5_9e7154c8:smtp','c6e5_9e7154c8:smtp',NULL,'pendiente','2026-07-20 21:47:41',0,3,0,0,1,NULL,NULL,NULL,NULL,NULL,'2026-07-20 21:47:41','2026-07-20 21:47:41'),
(321,'aviso_administrativo','Asignación académica completada · Temporal A',NULL,NULL,NULL,'c********@example.invalid','5450136098202d449275839440bbf88efd2558ffde2c267de06206b867479602','c6e3_7c2d60762@example.invalid','Tmp2 Integración','<!doctype html><html lang=\"es\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"></head><body style=\"margin:0;padding:0;background:#f7f4ec;color:#20251f;font-family:Arial,Helvetica,sans-serif;\"><div style=\"display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;\">Tu sección y docente ya fueron asignados.</div><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"background:#f7f4ec;\"><tr><td align=\"center\" style=\"padding:24px 12px;\"><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"max-width:640px;background:#ffffff;border:1px solid #e2dccd;border-radius:12px;overflow:hidden;\"><tr><td align=\"center\" style=\"padding:24px;background:#173f35;\"><img data-atenea-email-logo=\"1\" src=\"cid:atenea-logo\" width=\"150\" alt=\"Atenea Escuela de Naturopatía Holística\" style=\"display:block;margin:0 auto;max-width:150px;height:auto;border:0;outline:none;text-decoration:none;\"></td></tr><tr><td style=\"padding:32px 28px;\"><h1 style=\"margin:0 0 20px;color:#173f35;font-size:26px;line-height:1.25;\">Asignación académica completada · Temporal A</h1><p style=\"margin:0 0 16px;line-height:1.65;\">Hola, Tmp2 Integración.</p><p style=\"margin:0 0 16px;line-height:1.65;\">Sección: Sección llena (c6e3_7c2d6076-S29). Docente: Tmp1 Integración. Horario: por confirmar. Inicio: por confirmar.</p><p style=\"margin:24px 0;\"><a href=\"http://localhost/Atenea/src/estudiantes/clase.php\" style=\"display:inline-block;padding:13px 22px;background:#c49a3a;color:#ffffff;text-decoration:none;border-radius:7px;font-weight:700;\">Ver mi clase</a></p></td></tr><tr><td style=\"padding:20px 28px;background:#f7f4ec;border-top:1px solid #e2dccd;text-align:center;color:#5a625b;font-size:12px;line-height:1.6;\">Atenea Escuela de Naturopatía Holística<br>Av. El Níspero Final, Huizúcar · ateneanaturopatia@gmail.com<br>Enviado el 20/07/2026 21:48 (hora de El Salvador)</td></tr></table></td></tr></table></body></html>','Asignación académica completada · Temporal A\n\nHola, Tmp2 Integración.\n\nSección: Sección llena (c6e3_7c2d6076-S29). Docente: Tmp1 Integración. Horario: por confirmar. Inicio: por confirmar.\n\nAtenea Escuela de Naturopatía Holística\nAv. El Níspero Final, Huizúcar\nEnviado el 20/07/2026 21:48 (hora de El Salvador)','{\"usuario_id\":511,\"idempotency_key\":\"capacitacion-asignada:inscripcion:227\",\"tipo\":\"aviso_administrativo\"}','capacitacion-asignada:inscripcion:227','capacitacion-asignada:inscripcion:227',NULL,'cancelado','2026-07-20 21:48:46',0,3,1,0,1,NULL,NULL,NULL,'2026-07-20 21:48:46','Conservado sin envío por MAIL_TEST_MODE.','2026-07-20 21:48:46','2026-07-20 21:48:46'),
(324,'aviso_administrativo','Asignación académica completada · Temporal CONC',NULL,NULL,NULL,'c********@example.invalid','e069e04fec73862f2357ad2bc1b8d99c91642047ae0b60cce8d0dc837f37eaf3','c6e3_7c2d60763@example.invalid','Tmp3 Integración','<!doctype html><html lang=\"es\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"></head><body style=\"margin:0;padding:0;background:#f7f4ec;color:#20251f;font-family:Arial,Helvetica,sans-serif;\"><div style=\"display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;\">Tu sección y docente ya fueron asignados.</div><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"background:#f7f4ec;\"><tr><td align=\"center\" style=\"padding:24px 12px;\"><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"max-width:640px;background:#ffffff;border:1px solid #e2dccd;border-radius:12px;overflow:hidden;\"><tr><td align=\"center\" style=\"padding:24px;background:#173f35;\"><img data-atenea-email-logo=\"1\" src=\"cid:atenea-logo\" width=\"150\" alt=\"Atenea Escuela de Naturopatía Holística\" style=\"display:block;margin:0 auto;max-width:150px;height:auto;border:0;outline:none;text-decoration:none;\"></td></tr><tr><td style=\"padding:32px 28px;\"><h1 style=\"margin:0 0 20px;color:#173f35;font-size:26px;line-height:1.25;\">Asignación académica completada · Temporal CONC</h1><p style=\"margin:0 0 16px;line-height:1.65;\">Hola, Tmp3 Integración.</p><p style=\"margin:0 0 16px;line-height:1.65;\">Sección: Último cupo concurrente (c6e3_7c2d6076-CONC). Docente: Tmp5 Integración. Horario: por confirmar. Inicio: por confirmar.</p><p style=\"margin:24px 0;\"><a href=\"http://localhost/Atenea/src/estudiantes/clase.php\" style=\"display:inline-block;padding:13px 22px;background:#c49a3a;color:#ffffff;text-decoration:none;border-radius:7px;font-weight:700;\">Ver mi clase</a></p></td></tr><tr><td style=\"padding:20px 28px;background:#f7f4ec;border-top:1px solid #e2dccd;text-align:center;color:#5a625b;font-size:12px;line-height:1.6;\">Atenea Escuela de Naturopatía Holística<br>Av. El Níspero Final, Huizúcar · ateneanaturopatia@gmail.com<br>Enviado el 20/07/2026 21:48 (hora de El Salvador)</td></tr></table></td></tr></table></body></html>','Asignación académica completada · Temporal CONC\n\nHola, Tmp3 Integración.\n\nSección: Último cupo concurrente (c6e3_7c2d6076-CONC). Docente: Tmp5 Integración. Horario: por confirmar. Inicio: por confirmar.\n\nAtenea Escuela de Naturopatía Holística\nAv. El Níspero Final, Huizúcar\nEnviado el 20/07/2026 21:48 (hora de El Salvador)','{\"usuario_id\":512,\"idempotency_key\":\"capacitacion-asignada:inscripcion:229\",\"tipo\":\"aviso_administrativo\"}','capacitacion-asignada:inscripcion:229','capacitacion-asignada:inscripcion:229',NULL,'cancelado','2026-07-20 21:48:47',0,3,1,0,1,NULL,NULL,NULL,'2026-07-20 21:48:47','Conservado sin envío por MAIL_TEST_MODE.','2026-07-20 21:48:47','2026-07-20 21:48:47'),
(326,'aviso_administrativo','Asignación académica completada · Temporal A',NULL,NULL,NULL,'c********@example.invalid','e069e04fec73862f2357ad2bc1b8d99c91642047ae0b60cce8d0dc837f37eaf3','c6e3_7c2d60763@example.invalid','Tmp3 Integración','<!doctype html><html lang=\"es\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"></head><body style=\"margin:0;padding:0;background:#f7f4ec;color:#20251f;font-family:Arial,Helvetica,sans-serif;\"><div style=\"display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;\">Tu sección y docente ya fueron asignados.</div><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"background:#f7f4ec;\"><tr><td align=\"center\" style=\"padding:24px 12px;\"><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"max-width:640px;background:#ffffff;border:1px solid #e2dccd;border-radius:12px;overflow:hidden;\"><tr><td align=\"center\" style=\"padding:24px;background:#173f35;\"><img data-atenea-email-logo=\"1\" src=\"cid:atenea-logo\" width=\"150\" alt=\"Atenea Escuela de Naturopatía Holística\" style=\"display:block;margin:0 auto;max-width:150px;height:auto;border:0;outline:none;text-decoration:none;\"></td></tr><tr><td style=\"padding:32px 28px;\"><h1 style=\"margin:0 0 20px;color:#173f35;font-size:26px;line-height:1.25;\">Asignación académica completada · Temporal A</h1><p style=\"margin:0 0 16px;line-height:1.65;\">Hola, Tmp3 Integración.</p><p style=\"margin:0 0 16px;line-height:1.65;\">Sección: Destino manual (c6e3_7c2d6076-DEST). Docente: Tmp1 Integración. Horario: por confirmar. Inicio: por confirmar.</p><p style=\"margin:24px 0;\"><a href=\"http://localhost/Atenea/src/estudiantes/clase.php\" style=\"display:inline-block;padding:13px 22px;background:#c49a3a;color:#ffffff;text-decoration:none;border-radius:7px;font-weight:700;\">Ver mi clase</a></p></td></tr><tr><td style=\"padding:20px 28px;background:#f7f4ec;border-top:1px solid #e2dccd;text-align:center;color:#5a625b;font-size:12px;line-height:1.6;\">Atenea Escuela de Naturopatía Holística<br>Av. El Níspero Final, Huizúcar · ateneanaturopatia@gmail.com<br>Enviado el 20/07/2026 21:48 (hora de El Salvador)</td></tr></table></td></tr></table></body></html>','Asignación académica completada · Temporal A\n\nHola, Tmp3 Integración.\n\nSección: Destino manual (c6e3_7c2d6076-DEST). Docente: Tmp1 Integración. Horario: por confirmar. Inicio: por confirmar.\n\nAtenea Escuela de Naturopatía Holística\nAv. El Níspero Final, Huizúcar\nEnviado el 20/07/2026 21:48 (hora de El Salvador)','{\"usuario_id\":512,\"idempotency_key\":\"capacitacion-asignada:inscripcion:228\",\"tipo\":\"aviso_administrativo\"}','capacitacion-asignada:inscripcion:228','capacitacion-asignada:inscripcion:228',NULL,'cancelado','2026-07-20 21:48:47',0,3,1,0,1,NULL,NULL,NULL,'2026-07-20 21:48:47','Conservado sin envío por MAIL_TEST_MODE.','2026-07-20 21:48:47','2026-07-20 21:48:47'),
(345,'aviso_administrativo','Asignación académica completada · Temporal A',NULL,NULL,NULL,'c********@example.invalid','bf6d3fd24d7bf43482d05ca4e19f85b3a0dc2f4fd54cbea3d25375b0b89f00aa','c6e3_560aeac82@example.invalid','Tmp2 Integración','<!doctype html><html lang=\"es\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"></head><body style=\"margin:0;padding:0;background:#f7f4ec;color:#20251f;font-family:Arial,Helvetica,sans-serif;\"><div style=\"display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;\">Tu sección y docente ya fueron asignados.</div><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"background:#f7f4ec;\"><tr><td align=\"center\" style=\"padding:24px 12px;\"><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"max-width:640px;background:#ffffff;border:1px solid #e2dccd;border-radius:12px;overflow:hidden;\"><tr><td align=\"center\" style=\"padding:24px;background:#173f35;\"><img data-atenea-email-logo=\"1\" src=\"cid:atenea-logo\" width=\"150\" alt=\"Atenea Escuela de Naturopatía Holística\" style=\"display:block;margin:0 auto;max-width:150px;height:auto;border:0;outline:none;text-decoration:none;\"></td></tr><tr><td style=\"padding:32px 28px;\"><h1 style=\"margin:0 0 20px;color:#173f35;font-size:26px;line-height:1.25;\">Asignación académica completada · Temporal A</h1><p style=\"margin:0 0 16px;line-height:1.65;\">Hola, Tmp2 Integración.</p><p style=\"margin:0 0 16px;line-height:1.65;\">Sección: Sección llena (c6e3_560aeac8-S29). Docente: Tmp1 Integración. Horario: por confirmar. Inicio: por confirmar.</p><p style=\"margin:24px 0;\"><a href=\"http://localhost/Atenea/src/estudiantes/clase.php\" style=\"display:inline-block;padding:13px 22px;background:#c49a3a;color:#ffffff;text-decoration:none;border-radius:7px;font-weight:700;\">Ver mi clase</a></p></td></tr><tr><td style=\"padding:20px 28px;background:#f7f4ec;border-top:1px solid #e2dccd;text-align:center;color:#5a625b;font-size:12px;line-height:1.6;\">Atenea Escuela de Naturopatía Holística<br>Av. El Níspero Final, Huizúcar · ateneanaturopatia@gmail.com<br>Enviado el 20/07/2026 22:14 (hora de El Salvador)</td></tr></table></td></tr></table></body></html>','Asignación académica completada · Temporal A\n\nHola, Tmp2 Integración.\n\nSección: Sección llena (c6e3_560aeac8-S29). Docente: Tmp1 Integración. Horario: por confirmar. Inicio: por confirmar.\n\nAtenea Escuela de Naturopatía Holística\nAv. El Níspero Final, Huizúcar\nEnviado el 20/07/2026 22:14 (hora de El Salvador)','{\"usuario_id\":552,\"idempotency_key\":\"capacitacion-asignada:inscripcion:239\",\"tipo\":\"aviso_administrativo\"}','capacitacion-asignada:inscripcion:239','capacitacion-asignada:inscripcion:239',NULL,'cancelado','2026-07-20 22:14:32',0,3,1,0,1,NULL,NULL,NULL,'2026-07-20 22:14:32','Conservado sin envío por MAIL_TEST_MODE.','2026-07-20 22:14:32','2026-07-20 22:14:32'),
(348,'aviso_administrativo','Asignación académica completada · Temporal CONC',NULL,NULL,NULL,'c********@example.invalid','bf6d3fd24d7bf43482d05ca4e19f85b3a0dc2f4fd54cbea3d25375b0b89f00aa','c6e3_560aeac82@example.invalid','Tmp2 Integración','<!doctype html><html lang=\"es\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"></head><body style=\"margin:0;padding:0;background:#f7f4ec;color:#20251f;font-family:Arial,Helvetica,sans-serif;\"><div style=\"display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;\">Tu sección y docente ya fueron asignados.</div><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"background:#f7f4ec;\"><tr><td align=\"center\" style=\"padding:24px 12px;\"><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"max-width:640px;background:#ffffff;border:1px solid #e2dccd;border-radius:12px;overflow:hidden;\"><tr><td align=\"center\" style=\"padding:24px;background:#173f35;\"><img data-atenea-email-logo=\"1\" src=\"cid:atenea-logo\" width=\"150\" alt=\"Atenea Escuela de Naturopatía Holística\" style=\"display:block;margin:0 auto;max-width:150px;height:auto;border:0;outline:none;text-decoration:none;\"></td></tr><tr><td style=\"padding:32px 28px;\"><h1 style=\"margin:0 0 20px;color:#173f35;font-size:26px;line-height:1.25;\">Asignación académica completada · Temporal CONC</h1><p style=\"margin:0 0 16px;line-height:1.65;\">Hola, Tmp2 Integración.</p><p style=\"margin:0 0 16px;line-height:1.65;\">Sección: Último cupo concurrente (c6e3_560aeac8-CONC). Docente: Tmp5 Integración. Horario: por confirmar. Inicio: por confirmar.</p><p style=\"margin:24px 0;\"><a href=\"http://localhost/Atenea/src/estudiantes/clase.php\" style=\"display:inline-block;padding:13px 22px;background:#c49a3a;color:#ffffff;text-decoration:none;border-radius:7px;font-weight:700;\">Ver mi clase</a></p></td></tr><tr><td style=\"padding:20px 28px;background:#f7f4ec;border-top:1px solid #e2dccd;text-align:center;color:#5a625b;font-size:12px;line-height:1.6;\">Atenea Escuela de Naturopatía Holística<br>Av. El Níspero Final, Huizúcar · ateneanaturopatia@gmail.com<br>Enviado el 20/07/2026 22:14 (hora de El Salvador)</td></tr></table></td></tr></table></body></html>','Asignación académica completada · Temporal CONC\n\nHola, Tmp2 Integración.\n\nSección: Último cupo concurrente (c6e3_560aeac8-CONC). Docente: Tmp5 Integración. Horario: por confirmar. Inicio: por confirmar.\n\nAtenea Escuela de Naturopatía Holística\nAv. El Níspero Final, Huizúcar\nEnviado el 20/07/2026 22:14 (hora de El Salvador)','{\"usuario_id\":552,\"idempotency_key\":\"capacitacion-asignada:inscripcion:241\",\"tipo\":\"aviso_administrativo\"}','capacitacion-asignada:inscripcion:241','capacitacion-asignada:inscripcion:241',NULL,'cancelado','2026-07-20 22:14:32',0,3,1,0,1,NULL,NULL,NULL,'2026-07-20 22:14:32','Conservado sin envío por MAIL_TEST_MODE.','2026-07-20 22:14:32','2026-07-20 22:14:32'),
(350,'aviso_administrativo','Asignación académica completada · Temporal A',NULL,NULL,NULL,'c********@example.invalid','b8a928dbea08686575a59484988fbca72502c64b41667a499e86d1776f8818fd','c6e3_560aeac83@example.invalid','Tmp3 Integración','<!doctype html><html lang=\"es\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"></head><body style=\"margin:0;padding:0;background:#f7f4ec;color:#20251f;font-family:Arial,Helvetica,sans-serif;\"><div style=\"display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;\">Tu sección y docente ya fueron asignados.</div><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"background:#f7f4ec;\"><tr><td align=\"center\" style=\"padding:24px 12px;\"><table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"max-width:640px;background:#ffffff;border:1px solid #e2dccd;border-radius:12px;overflow:hidden;\"><tr><td align=\"center\" style=\"padding:24px;background:#173f35;\"><img data-atenea-email-logo=\"1\" src=\"cid:atenea-logo\" width=\"150\" alt=\"Atenea Escuela de Naturopatía Holística\" style=\"display:block;margin:0 auto;max-width:150px;height:auto;border:0;outline:none;text-decoration:none;\"></td></tr><tr><td style=\"padding:32px 28px;\"><h1 style=\"margin:0 0 20px;color:#173f35;font-size:26px;line-height:1.25;\">Asignación académica completada · Temporal A</h1><p style=\"margin:0 0 16px;line-height:1.65;\">Hola, Tmp3 Integración.</p><p style=\"margin:0 0 16px;line-height:1.65;\">Sección: Destino manual (c6e3_560aeac8-DEST). Docente: Tmp1 Integración. Horario: por confirmar. Inicio: por confirmar.</p><p style=\"margin:24px 0;\"><a href=\"http://localhost/Atenea/src/estudiantes/clase.php\" style=\"display:inline-block;padding:13px 22px;background:#c49a3a;color:#ffffff;text-decoration:none;border-radius:7px;font-weight:700;\">Ver mi clase</a></p></td></tr><tr><td style=\"padding:20px 28px;background:#f7f4ec;border-top:1px solid #e2dccd;text-align:center;color:#5a625b;font-size:12px;line-height:1.6;\">Atenea Escuela de Naturopatía Holística<br>Av. El Níspero Final, Huizúcar · ateneanaturopatia@gmail.com<br>Enviado el 20/07/2026 22:14 (hora de El Salvador)</td></tr></table></td></tr></table></body></html>','Asignación académica completada · Temporal A\n\nHola, Tmp3 Integración.\n\nSección: Destino manual (c6e3_560aeac8-DEST). Docente: Tmp1 Integración. Horario: por confirmar. Inicio: por confirmar.\n\nAtenea Escuela de Naturopatía Holística\nAv. El Níspero Final, Huizúcar\nEnviado el 20/07/2026 22:14 (hora de El Salvador)','{\"usuario_id\":553,\"idempotency_key\":\"capacitacion-asignada:inscripcion:240\",\"tipo\":\"aviso_administrativo\"}','capacitacion-asignada:inscripcion:240','capacitacion-asignada:inscripcion:240',NULL,'cancelado','2026-07-20 22:14:32',0,3,1,0,1,NULL,NULL,NULL,'2026-07-20 22:14:32','Conservado sin envío por MAIL_TEST_MODE.','2026-07-20 22:14:32','2026-07-20 22:14:32');

-- Información de dte_documentos
-- Información de dte_eventos
INSERT INTO `dte_eventos` (`id`,`dte_id`,`pedido_id`,`operacion`,`ambiente`,`resultado`,`request_sanitizado`,`response_sanitizado`,`codigo`,`observaciones`,`created_at`) VALUES
(1,NULL,15,'generate','unknown','failure',NULL,NULL,NULL,'Configure los datos del emisor DTE antes de emitir.','2026-07-16 22:02:16');

-- Información de errores_sistema
INSERT INTO `errores_sistema` (`id`,`fingerprint`,`categoria`,`modulo`,`nivel`,`mensaje`,`contexto_sanitizado`,`ocurrencias`,`estado`,`pedido_id`,`usuario_id`,`observacion_resolucion`,`actualizado_por`,`primera_ocurrencia_at`,`ultima_ocurrencia_at`,`resuelto_at`,`created_at`,`updated_at`) VALUES
(3,'1f25a24271c4b4824078231805b7877777652e7994e67ddd22fb955a801323a5','dte','generar_dte','error','Configure los datos del emisor DTE antes de emitir.','{\"pedido_id\":\"15\"}',1,'nuevo',15,NULL,NULL,1,'2026-07-16 22:02:16','2026-07-16 22:02:16',NULL,'2026-07-16 22:02:16','2026-07-16 23:03:56'),
(4,'4824e1b172ea5b2dede39f7e64520a43209ba8a81fd31f576c1bbc2fb5dcb977','correo','mailer','error','SMTP Error: Could not connect to SMTP host. Failed to connect to server','{\"correo_envio_id\":\"95\",\"pedido_id\":\"\",\"usuario_id\":\"\"}',7,'nuevo',NULL,NULL,NULL,NULL,'2026-07-17 11:38:30','2026-07-17 22:45:18',NULL,'2026-07-17 11:38:30','2026-07-17 22:45:18'),
(10,'7d4e5d8464b208a1e39db2a4e7bf21261d4bd7d815a7f515aab571e2e93354ea','sistema','global','advertencia','Acceso denegado por el servidor web.','{\"tracking_id\":\"284286867EFA\",\"status\":\"403\",\"uri\":\"\\/Atenea\\/src\\/errors\\/403.php\",\"method\":\"GET\",\"usuario_id\":\"\"}',8,'nuevo',NULL,NULL,NULL,NULL,'2026-07-17 22:38:24','2026-07-20 11:25:02',NULL,'2026-07-17 22:38:24','2026-07-20 11:25:02'),
(11,'1c644712955f4fdc70c5529c257e9cec50d072085136d3c2d416c1edcb650a30','sistema','global','advertencia','Ruta no encontrada.','{\"tracking_id\":\"7C8CCF68F3A6\",\"status\":\"404\",\"uri\":\"\\/Atenea\\/src\\/docente\\/images\\/arrow-down.svg\",\"method\":\"GET\",\"usuario_id\":\"\"}',53,'nuevo',NULL,NULL,NULL,NULL,'2026-07-17 22:38:24','2026-07-20 23:21:01',NULL,'2026-07-17 22:38:24','2026-07-20 23:21:01'),
(12,'79bba1f493cdbb755903e09d3b7d626e4e33ec95bb3bd131535786cb8970b6ff','sistema','global','advertencia','Sesión de seguridad vencida.','{\"tracking_id\":\"E4655AA6B29B\",\"status\":\"419\",\"uri\":\"\\/Atenea\\/src\\/errors\\/419.php\",\"method\":\"GET\",\"usuario_id\":\"\"}',7,'nuevo',NULL,NULL,NULL,NULL,'2026-07-17 22:38:24','2026-07-20 11:25:02',NULL,'2026-07-17 22:38:24','2026-07-20 11:25:02'),
(13,'dd11bd712bbb31608b398a0c8ab778f400d3cc43377d6d7712c547d8d49810ef','sistema','global','critico','Error interno gestionado por el servidor web.','{\"tracking_id\":\"9A8606F1442E\",\"status\":\"500\",\"uri\":\"\\/Atenea\\/src\\/errors\\/500.php\",\"method\":\"GET\",\"usuario_id\":\"\"}',8,'nuevo',NULL,NULL,NULL,NULL,'2026-07-17 22:38:24','2026-07-20 11:25:02',NULL,'2026-07-17 22:38:24','2026-07-20 11:25:02'),
(14,'e574dd460bffcf7ad36a37d3e69ba3113233e1ad0d2c0b4da025350e5b124116','sistema','global','critico','Modo de mantenimiento.','{\"tracking_id\":\"FCB0B909C145\",\"status\":\"503\",\"uri\":\"\\/Atenea\\/src\\/errors\\/503.php\",\"method\":\"GET\",\"usuario_id\":\"\"}',5,'nuevo',NULL,NULL,NULL,NULL,'2026-07-17 22:38:24','2026-07-20 11:25:02',NULL,'2026-07-17 22:38:24','2026-07-20 11:25:02'),
(43,'deec491c12ddb4a96ccbdbf77c58329dfc361232fe3993b8b792a53962bf1c7d','sistema','global','critico','Conexión a la base de datos no disponible.','{\"tracking_id\":\"14CC01A029A8\",\"status\":\"503\",\"uri\":\"\\/Atenea\\/src\\/errors\\/database.php\",\"method\":\"GET\",\"usuario_id\":\"\"}',4,'nuevo',NULL,NULL,NULL,NULL,'2026-07-18 09:28:18','2026-07-20 11:25:02',NULL,'2026-07-18 09:28:18','2026-07-20 11:25:02'),
(59,'b16c579b3b469454884f60b77e1ad14fadf39bb638c893321cde2ba1c81e3d15','sistema','global','critico','Undefined array key \"icono\"','{\"tracking_id\":\"D04E7CBC5BE9\",\"status\":\"500\",\"uri\":\"\\/Atenea\\/index.php\",\"method\":\"GET\",\"usuario_id\":\"\"}',1176,'nuevo',NULL,NULL,NULL,NULL,'2026-07-20 11:51:31','2026-07-21 10:29:32',NULL,'2026-07-20 11:51:31','2026-07-21 10:29:32'),
(470,'30f4f3f3018f96ab3338d9675b7965e50fb15580f56c8da920fa1895c617fdce','sistema','global','critico','atenea_e(): Argument #1 ($value) must be of type string, null given, called in C:\\xampp\\htdocs\\Atenea\\src\\dashboard\\capacitaciones\\editar.php on line 27','{\"tracking_id\":\"32D0867B1F3D\",\"status\":\"500\",\"uri\":\"\\/Atenea\\/src\\/dashboard\\/capacitaciones\\/editar.php?id=6\",\"method\":\"GET\",\"usuario_id\":\"1\"}',1,'nuevo',NULL,1,NULL,NULL,'2026-07-20 20:43:12','2026-07-20 20:43:12',NULL,'2026-07-20 20:43:12','2026-07-20 20:43:12'),
(913,'0c4fd5c89a141495fab2b2e2eed3682716a6672feed46b5526f54bc35f4a0990','correo','cola','error','SMTP Error: Could not connect to SMTP host. Failed to connect to server','{\"correo_envio_id\":\"351\"}',3,'nuevo',NULL,NULL,NULL,NULL,'2026-07-20 21:48:31','2026-07-20 22:14:35',NULL,'2026-07-20 21:48:31','2026-07-20 22:14:35');

-- Información de inventario_movimientos
INSERT INTO `inventario_movimientos` (`id`,`producto_id`,`pedido_id`,`usuario_admin_id`,`tipo`,`cantidad`,`stock_anterior`,`stock_nuevo`,`nota`,`created_at`) VALUES
(3,4,NULL,1,'ajuste',80,0,80,'Ajuste desde edición de producto','2026-07-14 15:38:46'),
(5,4,15,NULL,'venta',-2,80,78,'Venta confirmada por webhook Stripe','2026-07-16 22:02:16');

-- Información de menu_formulario_envios
-- Información de pagos
INSERT INTO `pagos` (`id`,`pedido_id`,`proveedor`,`stripe_payment_intent_id`,`importe`,`moneda`,`estado`,`datos_referencia`,`created_at`,`updated_at`) VALUES
(3,15,'stripe','pi_3Tu2yUE8YH5P1jJk0L2sIvMl',190.00,'usd','pagado','{\"checkout_session\":\"cs_test_a1oj6bH9GSfOXemVd836wX0uLV8dreqszTKjJ05YEU2YVDqOnDgXeZDIGU\",\"stripe_event\":\"evt_1Tu2yVE8YH5P1jJkiWsofEHs\"}','2026-07-16 22:02:16','2026-07-16 22:02:16');

-- Información de pedido_detalles
INSERT INTO `pedido_detalles` (`id`,`pedido_id`,`producto_id`,`nombre_producto`,`sku`,`cantidad`,`precio_normal`,`precio_unitario`,`descuento_unitario`,`subtotal`,`promocion_id`,`created_at`) VALUES
(5,5,4,'Dulces de uva',NULL,1,95.00,95.00,0.00,95.00,NULL,'2026-07-14 15:39:41'),
(6,6,4,'Dulces de uva',NULL,1,95.00,95.00,0.00,95.00,NULL,'2026-07-14 20:54:49'),
(13,15,4,'Dulces de uva',NULL,2,95.00,95.00,0.00,190.00,NULL,'2026-07-16 22:01:34');

-- Información de pedido_historial
INSERT INTO `pedido_historial` (`id`,`pedido_id`,`estado_anterior`,`estado_nuevo`,`origen`,`usuario_id`,`pago_id`,`nota`,`created_at`) VALUES
(2,5,NULL,'pendiente','sistema',2,NULL,'Pedido creado; stock reservado.','2026-07-14 15:39:41'),
(3,5,'pendiente','esperando_pago','stripe',NULL,NULL,'Checkout creado.','2026-07-14 15:39:42'),
(4,6,NULL,'pendiente','sistema',2,NULL,'Pedido creado; stock reservado.','2026-07-14 20:54:49'),
(5,6,'pendiente','esperando_pago','stripe',NULL,NULL,'Checkout creado.','2026-07-14 20:54:50'),
(17,15,NULL,'pendiente_pago','usuario',2,NULL,'Pedido creado desde carrito; precio, stock y dirección validados.','2026-07-16 22:01:34'),
(18,15,'pendiente_pago','pendiente_pago','stripe',NULL,NULL,'Checkout Stripe creado.','2026-07-16 22:01:35'),
(19,15,'pendiente_pago','pagado','stripe',NULL,NULL,'Pago, importe y moneda confirmados por webhook; stock descontado.','2026-07-16 22:02:16');

-- Información de admin_notices
INSERT INTO `admin_notices` (`id`,`user_id`,`created_by`,`type`,`category`,`level`,`title`,`message`,`target_section`,`action_url`,`idempotency_key`,`pedido_id`,`correo_envio_id`,`hilo_id`,`error_id`,`priority`,`status`,`due_at`,`read_at`,`resolved_at`,`cancelled_at`,`email_sent_at`,`created_at`,`updated_at`) VALUES
(9,1,NULL,'compra_nueva','pedidos','informacion','Nueva compra pendiente','Pedido AT-20260716-CF73FD86 creado y pendiente de pago.',NULL,'/Atenea/src/dashboard/pedidos/detalle.php?id=15','pedido:nuevo:15:u:1',15,NULL,NULL,NULL,'normal','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-16 22:01:34','2026-07-16 22:01:34'),
(10,1,NULL,'pago_confirmado','pagos','exito','Pago confirmado','Stripe procesó el evento del pedido #15.',NULL,'/Atenea/src/dashboard/pedidos/detalle.php?id=15','stripe:notificacion:evt_1Tu2yVE8YH5P1jJkiWsofEHs:u:1',15,NULL,NULL,NULL,'normal','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-16 22:02:16','2026-07-16 22:02:16'),
(11,1,NULL,'error_dte','dte','error','Error operativo: generar_dte','Configure los datos del emisor DTE antes de emitir.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=3','error:1f25a24271c4b4824078231805b7877777652e7994e67ddd22fb955a801323a5:2026-07-16-22:u:1',15,NULL,NULL,3,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-16 22:02:16','2026-07-16 22:02:16'),
(12,51,1,'documentacion','sistema','informacion','Corrige tus campos','Tienes que agregar informaciona tu perfil correctamente','TODO',NULL,NULL,NULL,NULL,NULL,NULL,'urgente','pendiente','2026-07-17 23:59:59',NULL,NULL,NULL,'2026-07-17 00:13:27','2026-07-17 00:13:24','2026-07-17 00:13:27'),
(25,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=15','capacitacion:admin:6:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 10:09:49','2026-07-17 10:09:49'),
(36,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=18','capacitacion:admin:11:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 10:10:52','2026-07-17 10:10:52'),
(47,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=25','capacitacion:admin:16:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 10:18:21','2026-07-17 10:18:21'),
(58,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=30','capacitacion:admin:21:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 10:20:50','2026-07-17 10:20:50'),
(60,51,NULL,'inscripcion_confirmada','capacitaciones','advertencia','Pago confirmado, asignación pendiente','Tu pago está confirmado. Atenea asignará docente y sección en cuanto exista disponibilidad.',NULL,'/Atenea/src/estudiantes/cursos.php','capacitacion:inscripcion:22:u:51',NULL,NULL,NULL,NULL,'media','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 10:26:34','2026-07-17 10:26:34'),
(61,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/Atenea/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=7','capacitacion:admin:22:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 10:26:34','2026-07-17 10:26:34'),
(78,1,NULL,'error_correo','correo','error','Error operativo: mailer','SMTP Error: Could not connect to SMTP host. Failed to connect to server',NULL,'/src/dashboard/errores/detalle.php?id=4','error:4824e1b172ea5b2dede39f7e64520a43209ba8a81fd31f576c1bbc2fb5dcb977:2026-07-17-11:u:1',NULL,NULL,NULL,4,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 11:38:30','2026-07-17 11:38:30'),
(104,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=50','capacitacion:admin:48:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 19:18:15','2026-07-17 19:18:15'),
(115,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=54','capacitacion:admin:53:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 19:18:32','2026-07-17 19:18:32'),
(126,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=58','capacitacion:admin:58:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 19:19:00','2026-07-17 19:19:00'),
(137,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=62','capacitacion:admin:63:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 19:19:10','2026-07-17 19:19:10'),
(148,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=66','capacitacion:admin:68:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 19:19:21','2026-07-17 19:19:21'),
(155,1,NULL,'error_correo','correo','error','Error operativo: mailer','SMTP Error: Could not connect to SMTP host. Failed to connect to server',NULL,'/src/dashboard/errores/detalle.php?id=4','error:4824e1b172ea5b2dede39f7e64520a43209ba8a81fd31f576c1bbc2fb5dcb977:2026-07-17-19:u:1',NULL,NULL,NULL,4,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 19:19:39','2026-07-17 19:19:39'),
(165,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=72','capacitacion:admin:76:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 19:25:58','2026-07-17 19:25:58'),
(173,1,3,'correo_plataforma','comunicaciones','informacion','Correo enviado desde Atenea','Prueba de comunicacion',NULL,'/Atenea/src/comunicaciones/correo.php?vista=entrada','centro:correo:1:u:1',NULL,NULL,NULL,NULL,'normal','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 19:44:10','2026-07-17 19:44:10'),
(174,51,1,'chat_nuevo','comunicaciones','informacion','Nuevo mensaje interno','Bro pon tus datos correctamente',NULL,'/Atenea/src/comunicaciones/chat.php?id=30','chat:mensaje:29:u:51',NULL,NULL,NULL,NULL,'normal','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 21:17:49','2026-07-17 21:17:49'),
(175,3,1,'chat_nuevo','comunicaciones','informacion','Nuevo mensaje interno','TRABAJE MAITRO',NULL,'/Atenea/src/comunicaciones/chat.php?id=29','chat:mensaje:30:u:3',NULL,NULL,NULL,NULL,'normal','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 21:18:17','2026-07-17 21:18:17'),
(185,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=78','capacitacion:admin:84:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:26:41','2026-07-17 22:26:41'),
(192,1,NULL,'error_correo','correo','error','Error operativo: mailer','SMTP Error: Could not connect to SMTP host. Failed to connect to server',NULL,'/src/dashboard/errores/detalle.php?id=4','error:4824e1b172ea5b2dede39f7e64520a43209ba8a81fd31f576c1bbc2fb5dcb977:2026-07-17-22:u:1',NULL,NULL,NULL,4,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:26:47','2026-07-17 22:26:47'),
(193,1,NULL,'error_sistema','sistema','error','Error operativo: global','Acceso denegado por el servidor web.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=10','error:7d4e5d8464b208a1e39db2a4e7bf21261d4bd7d815a7f515aab571e2e93354ea:2026-07-17-22:u:1',NULL,NULL,NULL,10,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:38:24','2026-07-17 22:38:24'),
(194,1,NULL,'error_sistema','sistema','error','Error operativo: global','Ruta no encontrada.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=11','error:1c644712955f4fdc70c5529c257e9cec50d072085136d3c2d416c1edcb650a30:2026-07-17-22:u:1',NULL,NULL,NULL,11,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:38:24','2026-07-17 22:38:24'),
(195,1,NULL,'error_sistema','sistema','error','Error operativo: global','Sesión de seguridad vencida.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=12','error:79bba1f493cdbb755903e09d3b7d626e4e33ec95bb3bd131535786cb8970b6ff:2026-07-17-22:u:1',NULL,NULL,NULL,12,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:38:24','2026-07-17 22:38:24'),
(196,1,NULL,'error_sistema','sistema','error','Error operativo: global','Error interno gestionado por el servidor web.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=13','error:dd11bd712bbb31608b398a0c8ab778f400d3cc43377d6d7712c547d8d49810ef:2026-07-17-22:u:1',NULL,NULL,NULL,13,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:38:24','2026-07-17 22:38:24'),
(197,1,NULL,'error_sistema','sistema','error','Error operativo: global','Modo de mantenimiento.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=14','error:e574dd460bffcf7ad36a37d3e69ba3113233e1ad0d2c0b4da025350e5b124116:2026-07-17-22:u:1',NULL,NULL,NULL,14,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:38:24','2026-07-17 22:38:24'),
(237,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=90','capacitacion:admin:98:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:45:40','2026-07-17 22:45:40'),
(248,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=94','capacitacion:admin:103:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:45:52','2026-07-17 22:45:52'),
(259,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=98','capacitacion:admin:108:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:46:06','2026-07-17 22:46:06'),
(270,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=102','capacitacion:admin:113:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:46:16','2026-07-17 22:46:16'),
(287,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=110','capacitacion:admin:121:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:48:10','2026-07-17 22:48:10'),
(298,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=114','capacitacion:admin:126:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:48:21','2026-07-17 22:48:21'),
(309,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=118','capacitacion:admin:131:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:48:32','2026-07-17 22:48:32'),
(326,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=126','capacitacion:admin:139:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:49:22','2026-07-17 22:49:22'),
(337,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=130','capacitacion:admin:144:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:49:33','2026-07-17 22:49:33'),
(348,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=134','capacitacion:admin:149:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:49:45','2026-07-17 22:49:45'),
(359,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=138','capacitacion:admin:154:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:49:57','2026-07-17 22:49:57'),
(370,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=142','capacitacion:admin:159:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:50:07','2026-07-17 22:50:07'),
(381,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=146','capacitacion:admin:164:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:50:19','2026-07-17 22:50:19'),
(392,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=150','capacitacion:admin:169:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:50:30','2026-07-17 22:50:30'),
(403,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=154','capacitacion:admin:174:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:50:41','2026-07-17 22:50:41'),
(414,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=158','capacitacion:admin:179:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:50:53','2026-07-17 22:50:53'),
(425,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=162','capacitacion:admin:184:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-17 22:51:04','2026-07-17 22:51:04'),
(427,1,NULL,'error_sistema','sistema','error','Error operativo: global','Ruta no encontrada.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=11','error:1c644712955f4fdc70c5529c257e9cec50d072085136d3c2d416c1edcb650a30:2026-07-18-09:u:1',NULL,NULL,NULL,11,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-18 09:20:57','2026-07-18 09:20:57'),
(431,1,NULL,'error_sistema','sistema','error','Error operativo: global','Acceso denegado por el servidor web.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=10','error:7d4e5d8464b208a1e39db2a4e7bf21261d4bd7d815a7f515aab571e2e93354ea:2026-07-18-09:u:1',NULL,NULL,NULL,10,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-18 09:28:17','2026-07-18 09:28:17'),
(433,1,NULL,'error_sistema','sistema','error','Error operativo: global','Sesión de seguridad vencida.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=12','error:79bba1f493cdbb755903e09d3b7d626e4e33ec95bb3bd131535786cb8970b6ff:2026-07-18-09:u:1',NULL,NULL,NULL,12,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-18 09:28:18','2026-07-18 09:28:18'),
(434,1,NULL,'error_sistema','sistema','error','Error operativo: global','Error interno gestionado por el servidor web.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=13','error:dd11bd712bbb31608b398a0c8ab778f400d3cc43377d6d7712c547d8d49810ef:2026-07-18-09:u:1',NULL,NULL,NULL,13,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-18 09:28:18','2026-07-18 09:28:18'),
(435,1,NULL,'error_sistema','sistema','error','Error operativo: global','Modo de mantenimiento.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=14','error:e574dd460bffcf7ad36a37d3e69ba3113233e1ad0d2c0b4da025350e5b124116:2026-07-18-09:u:1',NULL,NULL,NULL,14,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-18 09:28:18','2026-07-18 09:28:18'),
(436,1,NULL,'error_sistema','sistema','error','Error operativo: global','Conexión a la base de datos no disponible.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=43','error:deec491c12ddb4a96ccbdbf77c58329dfc361232fe3993b8b792a53962bf1c7d:2026-07-18-09:u:1',NULL,NULL,NULL,43,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-18 09:28:18','2026-07-18 09:28:18'),
(437,1,NULL,'error_sistema','sistema','error','Error operativo: global','Acceso denegado por el servidor web.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=10','error:7d4e5d8464b208a1e39db2a4e7bf21261d4bd7d815a7f515aab571e2e93354ea:2026-07-18-15:u:1',NULL,NULL,NULL,10,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-18 15:19:02','2026-07-18 15:19:02'),
(438,1,NULL,'error_sistema','sistema','error','Error operativo: global','Ruta no encontrada.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=11','error:1c644712955f4fdc70c5529c257e9cec50d072085136d3c2d416c1edcb650a30:2026-07-18-15:u:1',NULL,NULL,NULL,11,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-18 15:19:02','2026-07-18 15:19:02'),
(439,1,NULL,'error_sistema','sistema','error','Error operativo: global','Sesión de seguridad vencida.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=12','error:79bba1f493cdbb755903e09d3b7d626e4e33ec95bb3bd131535786cb8970b6ff:2026-07-18-15:u:1',NULL,NULL,NULL,12,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-18 15:19:02','2026-07-18 15:19:02'),
(440,1,NULL,'error_sistema','sistema','error','Error operativo: global','Error interno gestionado por el servidor web.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=13','error:dd11bd712bbb31608b398a0c8ab778f400d3cc43377d6d7712c547d8d49810ef:2026-07-18-15:u:1',NULL,NULL,NULL,13,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-18 15:19:02','2026-07-18 15:19:02'),
(441,1,NULL,'error_sistema','sistema','error','Error operativo: global','Modo de mantenimiento.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=14','error:e574dd460bffcf7ad36a37d3e69ba3113233e1ad0d2c0b4da025350e5b124116:2026-07-18-15:u:1',NULL,NULL,NULL,14,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-18 15:19:02','2026-07-18 15:19:02'),
(442,1,NULL,'error_sistema','sistema','error','Error operativo: global','Conexión a la base de datos no disponible.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=43','error:deec491c12ddb4a96ccbdbf77c58329dfc361232fe3993b8b792a53962bf1c7d:2026-07-18-15:u:1',NULL,NULL,NULL,43,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-18 15:19:02','2026-07-18 15:19:02'),
(444,1,NULL,'error_sistema','sistema','error','Error operativo: global','Conexión a la base de datos no disponible.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=43','error:deec491c12ddb4a96ccbdbf77c58329dfc361232fe3993b8b792a53962bf1c7d:2026-07-19-17:u:1',NULL,NULL,NULL,43,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-19 17:04:23','2026-07-19 17:04:23'),
(445,1,NULL,'error_sistema','sistema','error','Error operativo: global','Sesión de seguridad vencida.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=12','error:79bba1f493cdbb755903e09d3b7d626e4e33ec95bb3bd131535786cb8970b6ff:2026-07-19-17:u:1',NULL,NULL,NULL,12,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-19 17:04:43','2026-07-19 17:04:43'),
(446,1,NULL,'error_sistema','sistema','error','Error operativo: global','Acceso denegado por el servidor web.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=10','error:7d4e5d8464b208a1e39db2a4e7bf21261d4bd7d815a7f515aab571e2e93354ea:2026-07-20-11:u:1',NULL,NULL,NULL,10,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 11:25:02','2026-07-20 11:25:02'),
(447,1,NULL,'error_sistema','sistema','error','Error operativo: global','Ruta no encontrada.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=11','error:1c644712955f4fdc70c5529c257e9cec50d072085136d3c2d416c1edcb650a30:2026-07-20-11:u:1',NULL,NULL,NULL,11,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 11:25:02','2026-07-20 11:25:02'),
(448,1,NULL,'error_sistema','sistema','error','Error operativo: global','Sesión de seguridad vencida.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=12','error:79bba1f493cdbb755903e09d3b7d626e4e33ec95bb3bd131535786cb8970b6ff:2026-07-20-11:u:1',NULL,NULL,NULL,12,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 11:25:02','2026-07-20 11:25:02'),
(449,1,NULL,'error_sistema','sistema','error','Error operativo: global','Error interno gestionado por el servidor web.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=13','error:dd11bd712bbb31608b398a0c8ab778f400d3cc43377d6d7712c547d8d49810ef:2026-07-20-11:u:1',NULL,NULL,NULL,13,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 11:25:02','2026-07-20 11:25:02'),
(450,1,NULL,'error_sistema','sistema','error','Error operativo: global','Modo de mantenimiento.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=14','error:e574dd460bffcf7ad36a37d3e69ba3113233e1ad0d2c0b4da025350e5b124116:2026-07-20-11:u:1',NULL,NULL,NULL,14,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 11:25:02','2026-07-20 11:25:02'),
(451,1,NULL,'error_sistema','sistema','error','Error operativo: global','Conexión a la base de datos no disponible.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=43','error:deec491c12ddb4a96ccbdbf77c58329dfc361232fe3993b8b792a53962bf1c7d:2026-07-20-11:u:1',NULL,NULL,NULL,43,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 11:25:02','2026-07-20 11:25:02'),
(452,1,NULL,'error_sistema','sistema','error','Error operativo: global','Undefined array key \"icono\"',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=59','error:b16c579b3b469454884f60b77e1ad14fadf39bb638c893321cde2ba1c81e3d15:2026-07-20-11:u:1',NULL,NULL,NULL,59,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 11:51:31','2026-07-20 11:51:31'),
(486,1,NULL,'error_sistema','sistema','error','Error operativo: global','Undefined array key \"icono\"',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=59','error:b16c579b3b469454884f60b77e1ad14fadf39bb638c893321cde2ba1c81e3d15:2026-07-20-12:u:1',NULL,NULL,NULL,59,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 12:13:41','2026-07-20 12:13:41'),
(543,1,NULL,'inscripcion_pendiente','capacitaciones','error','Inscripción sin docente disponible','El pago fue confirmado, pero la inscripción requiere asignación manual.',NULL,'/src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=166','capacitacion:admin:189:u:1',NULL,NULL,NULL,NULL,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 16:55:18','2026-07-20 16:55:18'),
(558,1,NULL,'error_sistema','sistema','error','Error operativo: global','Undefined array key \"icono\"',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=59','error:b16c579b3b469454884f60b77e1ad14fadf39bb638c893321cde2ba1c81e3d15:2026-07-20-17:u:1',NULL,NULL,NULL,59,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 17:39:43','2026-07-20 17:39:43'),
(591,1,NULL,'error_sistema','sistema','error','Error operativo: global','Undefined array key \"icono\"',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=59','error:b16c579b3b469454884f60b77e1ad14fadf39bb638c893321cde2ba1c81e3d15:2026-07-20-18:u:1',NULL,NULL,NULL,59,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 18:15:49','2026-07-20 18:15:49'),
(647,1,NULL,'error_sistema','sistema','error','Error operativo: global','Ruta no encontrada.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=11','error:1c644712955f4fdc70c5529c257e9cec50d072085136d3c2d416c1edcb650a30:2026-07-20-18:u:1',NULL,NULL,NULL,11,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 18:22:17','2026-07-20 18:22:17'),
(1074,1,NULL,'error_sistema','sistema','error','Error operativo: global','Undefined array key \"icono\"',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=59','error:b16c579b3b469454884f60b77e1ad14fadf39bb638c893321cde2ba1c81e3d15:2026-07-20-20:u:1',NULL,NULL,NULL,59,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 20:41:23','2026-07-20 20:41:23'),
(1154,1,NULL,'error_sistema','sistema','error','Error operativo: global','Ruta no encontrada.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=11','error:1c644712955f4fdc70c5529c257e9cec50d072085136d3c2d416c1edcb650a30:2026-07-20-20:u:1',NULL,NULL,NULL,11,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 20:42:35','2026-07-20 20:42:35'),
(1165,1,NULL,'error_sistema','sistema','error','Error operativo: global','atenea_e(): Argument #1 ($value) must be of type string, null given, called in C:\\xampp\\htdocs\\Atenea\\src\\dashboard\\capacitaciones\\editar.php on line 27',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=470','error:30f4f3f3018f96ab3338d9675b7965e50fb15580f56c8da920fa1895c617fdce:2026-07-20-20:u:1',NULL,NULL,NULL,470,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 20:43:12','2026-07-20 20:43:12'),
(1263,1,NULL,'error_sistema','sistema','error','Error operativo: global','Undefined array key \"icono\"',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=59','error:b16c579b3b469454884f60b77e1ad14fadf39bb638c893321cde2ba1c81e3d15:2026-07-20-21:u:1',NULL,NULL,NULL,59,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 21:08:52','2026-07-20 21:08:52'),
(1391,1,NULL,'error_sistema','sistema','error','Error operativo: global','Ruta no encontrada.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=11','error:1c644712955f4fdc70c5529c257e9cec50d072085136d3c2d416c1edcb650a30:2026-07-20-21:u:1',NULL,NULL,NULL,11,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 21:29:54','2026-07-20 21:29:54'),
(1738,1,NULL,'error_correo','correo','error','Error operativo: cola','SMTP Error: Could not connect to SMTP host. Failed to connect to server',NULL,'/src/dashboard/errores/detalle.php?id=913','error:0c4fd5c89a141495fab2b2e2eed3682716a6672feed46b5526f54bc35f4a0990:2026-07-20-21:u:1',NULL,NULL,NULL,913,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 21:48:31','2026-07-20 21:48:31'),
(2120,1,NULL,'error_sistema','sistema','error','Error operativo: global','Undefined array key \"icono\"',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=59','error:b16c579b3b469454884f60b77e1ad14fadf39bb638c893321cde2ba1c81e3d15:2026-07-20-22:u:1',NULL,NULL,NULL,59,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 22:12:30','2026-07-20 22:12:30'),
(2168,1,NULL,'error_correo','correo','error','Error operativo: cola','SMTP Error: Could not connect to SMTP host. Failed to connect to server',NULL,'/src/dashboard/errores/detalle.php?id=913','error:0c4fd5c89a141495fab2b2e2eed3682716a6672feed46b5526f54bc35f4a0990:2026-07-20-22:u:1',NULL,NULL,NULL,913,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 22:14:35','2026-07-20 22:14:35'),
(2225,1,NULL,'error_sistema','sistema','error','Error operativo: global','Ruta no encontrada.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=11','error:1c644712955f4fdc70c5529c257e9cec50d072085136d3c2d416c1edcb650a30:2026-07-20-22:u:1',NULL,NULL,NULL,11,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 22:15:28','2026-07-20 22:15:28'),
(2391,1,NULL,'error_sistema','sistema','error','Error operativo: global','Intento de acceso a una ruta no autorizada.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=1246','error:7fe227dea9a424d82122cdb98b530e501c2cd5c92c98e4fa8d622dc52a15831f:2026-07-20-22:u:1',NULL,NULL,NULL,1246,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 22:52:46','2026-07-20 22:52:46'),
(2412,1,NULL,'error_sistema','sistema','error','Error operativo: global','Ruta no encontrada.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=11','error:1c644712955f4fdc70c5529c257e9cec50d072085136d3c2d416c1edcb650a30:2026-07-20-23:u:1',NULL,NULL,NULL,11,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 23:20:58','2026-07-20 23:20:58'),
(2419,1,NULL,'error_sistema','sistema','error','Error operativo: global','Undefined array key \"icono\"',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=59','error:b16c579b3b469454884f60b77e1ad14fadf39bb638c893321cde2ba1c81e3d15:2026-07-20-23:u:1',NULL,NULL,NULL,59,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 23:23:05','2026-07-20 23:23:05'),
(2435,1,NULL,'error_sistema','sistema','error','Error operativo: global','Intento de acceso a una ruta no autorizada.',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=1270','error:7fe227dea9a424d82122cdb98b530e501c2cd5c92c98e4fa8d622dc52a15831f:2026-07-20-23:u:1',NULL,NULL,NULL,1270,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-20 23:23:06','2026-07-20 23:23:06'),
(2437,1,NULL,'error_sistema','sistema','error','Error operativo: global','Undefined array key \"icono\"',NULL,'/Atenea/src/dashboard/errores/detalle.php?id=59','error:b16c579b3b469454884f60b77e1ad14fadf39bb638c893321cde2ba1c81e3d15:2026-07-21-10:u:1',NULL,NULL,NULL,59,'alta','pendiente',NULL,NULL,NULL,NULL,NULL,'2026-07-21 10:29:32','2026-07-21 10:29:32');

-- Información de comunicacion_mensajes
DROP TRIGGER IF EXISTS `trg_capacitacion_publicacion_carga`;
DELIMITER $$
CREATE TRIGGER trg_capacitacion_publicacion_carga
BEFORE UPDATE ON asignaturas
FOR EACH ROW
BEGIN
  DECLARE docentes_sobrecargados INT DEFAULT 0;
  IF NEW.activo=1 AND NEW.estado_capacitacion IN('publicada','cerrada') AND NEW.deleted_at IS NULL THEN
    SELECT COUNT(*) INTO docentes_sobrecargados
    FROM docentes_asignaturas objetivo
    WHERE objetivo.asignatura_id=NEW.id AND objetivo.estado='activo'
      AND (SELECT COUNT(DISTINCT da.asignatura_id)
           FROM docentes_asignaturas da
           INNER JOIN asignaturas a ON a.id=da.asignatura_id
           WHERE da.docente_id=objetivo.docente_id AND da.asignatura_id<>NEW.id
             AND da.estado='activo' AND a.activo=1
             AND a.estado_capacitacion IN('publicada','cerrada') AND a.deleted_at IS NULL)>=2;
    IF docentes_sobrecargados>0 THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La publicaci?n superar?a dos capacitaciones activas para un docente.';
    END IF;
  END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS `trg_docente_capacitacion_max_insert`;
DELIMITER $$
CREATE TRIGGER trg_docente_capacitacion_max_insert
BEFORE INSERT ON docentes_asignaturas
FOR EACH ROW
BEGIN
  DECLARE carga INT DEFAULT 0;
  IF NEW.estado='activo' THEN
    SELECT COUNT(DISTINCT da.asignatura_id) INTO carga
    FROM docentes_asignaturas da
    INNER JOIN asignaturas a ON a.id=da.asignatura_id
    WHERE da.docente_id=NEW.docente_id AND da.estado='activo' AND a.activo=1
      AND a.estado_capacitacion IN('publicada','cerrada') AND a.deleted_at IS NULL;
    IF carga>=2 THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El docente ya tiene dos capacitaciones activas.';
    END IF;
  END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS `trg_docente_capacitacion_max_update`;
DELIMITER $$
CREATE TRIGGER trg_docente_capacitacion_max_update
BEFORE UPDATE ON docentes_asignaturas
FOR EACH ROW
BEGIN
  DECLARE carga INT DEFAULT 0;
  IF NEW.estado='activo' THEN
    SELECT COUNT(DISTINCT da.asignatura_id) INTO carga
    FROM docentes_asignaturas da
    INNER JOIN asignaturas a ON a.id=da.asignatura_id
    WHERE da.docente_id=NEW.docente_id AND da.estado='activo' AND a.activo=1
      AND a.estado_capacitacion IN('publicada','cerrada') AND a.deleted_at IS NULL
      AND da.id<>OLD.id;
    IF carga>=2 THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El docente ya tiene dos capacitaciones activas.';
    END IF;
  END IF;
END$$
DELIMITER ;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET SQL_MODE=@OLD_SQL_MODE;
-- Fin de la copia SQL de Atenea.
