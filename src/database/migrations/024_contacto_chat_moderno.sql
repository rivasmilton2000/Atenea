USE db_atenea;

ALTER TABLE chat_mensajes
  ADD COLUMN respuesta_a_id BIGINT UNSIGNED NULL AFTER remitente_id,
  ADD COLUMN idempotency_key CHAR(64) NULL AFTER contenido,
  ADD COLUMN entregado_at DATETIME NULL AFTER estado,
  ADD COLUMN eliminado_at DATETIME NULL AFTER entregado_at,
  ADD COLUMN eliminado_por INT UNSIGNED NULL AFTER eliminado_at,
  ADD UNIQUE KEY uq_chat_mensaje_idempotencia (idempotency_key),
  ADD INDEX idx_chat_mensaje_respuesta (respuesta_a_id),
  ADD CONSTRAINT fk_chat_mensaje_respuesta FOREIGN KEY (respuesta_a_id) REFERENCES chat_mensajes(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_chat_mensaje_eliminado_por FOREIGN KEY (eliminado_por) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE chat_adjuntos
  ADD COLUMN es_imagen TINYINT(1) NOT NULL DEFAULT 0 AFTER archivo_tamano;

UPDATE chat_adjuntos SET es_imagen = archivo_mime IN ('image/jpeg','image/png','image/webp');
