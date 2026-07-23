USE db_atenea;

-- Los registros previos al cobro son intenciones técnicas y no compras oficiales.
ALTER TABLE pedidos
  ADD COLUMN IF NOT EXISTS es_intencion_checkout TINYINT(1) NOT NULL DEFAULT 0 AFTER checkout_key,
  ADD COLUMN IF NOT EXISTS oficializado_at DATETIME NULL AFTER paid_at,
  ADD INDEX IF NOT EXISTS idx_pedidos_visibilidad (usuario_id, es_intencion_checkout, payment_status, created_at);

UPDATE pedidos
SET es_intencion_checkout = CASE WHEN payment_status IN ('paid','refunded') THEN 0 ELSE 1 END,
    oficializado_at = CASE WHEN payment_status IN ('paid','refunded') THEN COALESCE(oficializado_at, paid_at, updated_at) ELSE oficializado_at END;

ALTER TABLE capacitacion_pagos
  ADD COLUMN IF NOT EXISTS es_intencion_checkout TINYINT(1) NOT NULL DEFAULT 0 AFTER checkout_key,
  ADD COLUMN IF NOT EXISTS oficializado_at DATETIME NULL AFTER paid_at,
  ADD INDEX IF NOT EXISTS idx_cap_pago_visibilidad (usuario_id, es_intencion_checkout, estado, created_at);

UPDATE capacitacion_pagos
SET es_intencion_checkout = CASE WHEN estado IN ('pagado','reembolsado') THEN 0 ELSE 1 END,
    oficializado_at = CASE WHEN estado IN ('pagado','reembolsado') THEN COALESCE(oficializado_at, paid_at, updated_at) ELSE oficializado_at END;

CREATE TABLE IF NOT EXISTS capacitacion_comprobante_documentos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  pago_id BIGINT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL,
  numero VARCHAR(40) NOT NULL,
  codigo_generacion CHAR(36) NOT NULL,
  pdf_relpath VARCHAR(500) NOT NULL,
  json_relpath VARCHAR(500) NOT NULL,
  pdf_sha256 CHAR(64) NOT NULL,
  json_sha256 CHAR(64) NOT NULL,
  generado_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cap_comprobante_pago (pago_id),
  UNIQUE KEY uq_cap_comprobante_numero (numero),
  UNIQUE KEY uq_cap_comprobante_codigo (codigo_generacion),
  CONSTRAINT fk_cap_comprobante_pago FOREIGN KEY (pago_id) REFERENCES capacitacion_pagos(id),
  CONSTRAINT fk_cap_comprobante_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
