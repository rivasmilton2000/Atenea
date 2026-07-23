USE db_atenea;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comprobante_documentos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id BIGINT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL,
  numero VARCHAR(32) NOT NULL,
  codigo_generacion CHAR(36) NOT NULL,
  pdf_relpath VARCHAR(500) NOT NULL,
  json_relpath VARCHAR(500) NOT NULL,
  pdf_sha256 CHAR(64) NOT NULL,
  json_sha256 CHAR(64) NOT NULL,
  generado_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_comprobante_pedido (pedido_id),
  UNIQUE KEY uq_comprobante_numero (numero),
  UNIQUE KEY uq_comprobante_codigo (codigo_generacion),
  INDEX idx_comprobante_usuario_fecha (usuario_id, generado_at),
  CONSTRAINT fk_comprobante_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
  CONSTRAINT fk_comprobante_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE correo_envios
  ADD COLUMN IF NOT EXISTS message_id VARCHAR(255) NULL AFTER enviado_at,
  ADD COLUMN IF NOT EXISTS reenvio_manual TINYINT(1) NOT NULL DEFAULT 0 AFTER message_id,
  ADD COLUMN IF NOT EXISTS reenviado_por INT UNSIGNED NULL AFTER reenvio_manual,
  ADD INDEX IF NOT EXISTS idx_correo_compra_estado (pedido_id, tipo, estado, created_at);

