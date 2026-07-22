USE db_atenea;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE asignaturas
  ADD COLUMN IF NOT EXISTS precio_anterior DECIMAL(10,2) NULL AFTER precio,
  ADD COLUMN IF NOT EXISTS promocion_etiqueta VARCHAR(100) NULL AFTER precio_anterior,
  ADD COLUMN IF NOT EXISTS promocion_desde DATETIME NULL AFTER promocion_etiqueta,
  ADD COLUMN IF NOT EXISTS promocion_hasta DATETIME NULL AFTER promocion_desde;

CREATE TABLE IF NOT EXISTS newsletter_suscriptores (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  nombre VARCHAR(150) NULL,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  token_baja CHAR(64) NOT NULL,
  origen VARCHAR(80) NOT NULL DEFAULT 'website_footer',
  ip_hash CHAR(64) NULL,
  fecha_suscripcion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_baja DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_newsletter_email (email),
  UNIQUE KEY uq_newsletter_token_baja (token_baja),
  KEY idx_newsletter_estado_fecha (estado,fecha_suscripcion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS newsletter_campanas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(180) NOT NULL,
  asunto VARCHAR(190) NOT NULL,
  preencabezado VARCHAR(255) NULL,
  contenido MEDIUMTEXT NOT NULL,
  texto_boton VARCHAR(80) NULL,
  url_destino VARCHAR(500) NULL,
  tipo ENUM('promocion','capacitacion','actualizacion','producto','oferta','educativa') NOT NULL DEFAULT 'actualizacion',
  estado ENUM('borrador','programada','encolada','procesando','enviada','cancelada') NOT NULL DEFAULT 'borrador',
  evento_clave VARCHAR(190) NULL,
  programada_at DATETIME NULL,
  aprobada_at DATETIME NULL,
  aprobada_por INT UNSIGNED NULL,
  creada_por INT UNSIGNED NULL,
  actualizada_por INT UNSIGNED NULL,
  destinatarios_total INT UNSIGNED NOT NULL DEFAULT 0,
  enviados_total INT UNSIGNED NOT NULL DEFAULT 0,
  fallidos_total INT UNSIGNED NOT NULL DEFAULT 0,
  cancelados_total INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_newsletter_evento (evento_clave),
  KEY idx_newsletter_campana_cola (estado,programada_at,id),
  CONSTRAINT fk_newsletter_campana_aprobada FOREIGN KEY (aprobada_por) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT fk_newsletter_campana_creada FOREIGN KEY (creada_por) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT fk_newsletter_campana_actualizada FOREIGN KEY (actualizada_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS newsletter_envios (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  campana_id BIGINT UNSIGNED NOT NULL,
  suscriptor_id BIGINT UNSIGNED NOT NULL,
  correo_envio_id BIGINT UNSIGNED NULL,
  estado ENUM('pendiente','procesando','enviado','fallido','cancelado') NOT NULL DEFAULT 'pendiente',
  intentos TINYINT UNSIGNED NOT NULL DEFAULT 0,
  error_sanitizado VARCHAR(500) NULL,
  procesando_desde DATETIME NULL,
  enviado_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_newsletter_campana_suscriptor (campana_id,suscriptor_id),
  UNIQUE KEY uq_newsletter_correo_envio (correo_envio_id),
  KEY idx_newsletter_envio_cola (estado,created_at,id),
  CONSTRAINT fk_newsletter_envio_campana FOREIGN KEY (campana_id) REFERENCES newsletter_campanas(id) ON DELETE CASCADE,
  CONSTRAINT fk_newsletter_envio_suscriptor FOREIGN KEY (suscriptor_id) REFERENCES newsletter_suscriptores(id),
  CONSTRAINT fk_newsletter_envio_correo FOREIGN KEY (correo_envio_id) REFERENCES correo_envios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
