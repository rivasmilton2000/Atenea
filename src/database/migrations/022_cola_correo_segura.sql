USE db_atenea;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE correo_envios
  ADD COLUMN evento_id VARCHAR(190) NULL AFTER idempotency_key,
  ADD COLUMN grupo_clave VARCHAR(190) NULL AFTER evento_id,
  ADD COLUMN destinatario_email VARCHAR(190) NULL AFTER destinatario_hash,
  ADD COLUMN destinatario_nombre VARCHAR(190) NULL AFTER destinatario_email,
  ADD COLUMN contenido_html MEDIUMTEXT NULL AFTER destinatario_nombre,
  ADD COLUMN contenido_texto MEDIUMTEXT NULL AFTER contenido_html,
  ADD COLUMN opciones_json LONGTEXT NULL AFTER contenido_texto,
  ADD COLUMN disponible_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER estado,
  ADD COLUMN max_intentos TINYINT UNSIGNED NOT NULL DEFAULT 3 AFTER intento,
  ADD COLUMN es_modo_prueba TINYINT(1) NOT NULL DEFAULT 0 AFTER max_intentos,
  ADD COLUMN permitir_envio_prueba TINYINT(1) NOT NULL DEFAULT 0 AFTER es_modo_prueba,
  ADD COLUMN agrupados INT UNSIGNED NOT NULL DEFAULT 1 AFTER permitir_envio_prueba,
  ADD COLUMN cancelado_at DATETIME NULL AFTER enviado_at,
  ADD COLUMN cancelado_motivo VARCHAR(255) NULL AFTER cancelado_at,
  ADD INDEX idx_correo_cola (estado, disponible_at, intento),
  ADD UNIQUE KEY uq_correo_evento (evento_id),
  ADD INDEX idx_correo_grupo (usuario_id, grupo_clave, estado);

UPDATE correo_envios
SET evento_id=idempotency_key,
    max_intentos=3
WHERE evento_id IS NULL;

UPDATE correo_envios
SET estado='cancelado',cancelado_at=NOW(),cancelado_motivo='Registro anterior sin contenido procesable.'
WHERE destinatario_email IS NULL AND estado IN ('pendiente','procesando','fallido');

CREATE TABLE notificacion_preferencias (
  usuario_id INT UNSIGNED NOT NULL,
  categoria VARCHAR(80) NOT NULL,
  correo_habilitado TINYINT(1) NOT NULL DEFAULT 1,
  agrupar_habilitado TINYINT(1) NOT NULL DEFAULT 1,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (usuario_id, categoria),
  CONSTRAINT fk_preferencia_notificacion_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
