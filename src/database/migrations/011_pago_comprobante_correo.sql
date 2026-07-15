USE db_atenea;

ALTER TABLE productos
  ADD COLUMN tipo_producto VARCHAR(40) NOT NULL DEFAULT 'producto' AFTER descripcion,
  ADD COLUMN caracteristicas TEXT NULL AFTER tipo_producto,
  ADD COLUMN informacion_entrega VARCHAR(1000) NULL AFTER caracteristicas;

ALTER TABLE pedidos
  ADD COLUMN payment_status VARCHAR(40) NULL AFTER estado,
  ADD COLUMN paid_at DATETIME NULL AFTER payment_status,
  ADD COLUMN payment_brand VARCHAR(32) NULL AFTER stripe_payment_intent_id,
  ADD COLUMN payment_last4 CHAR(4) NULL AFTER payment_brand,
  ADD COLUMN stripe_payment_method_id VARCHAR(255) NULL AFTER payment_last4,
  ADD COLUMN last_stripe_event_id VARCHAR(255) NULL AFTER stripe_payment_method_id,
  ADD COLUMN receipt_generated_at DATETIME NULL AFTER last_stripe_event_id,
  ADD COLUMN email_sent_at DATETIME NULL AFTER receipt_generated_at,
  ADD INDEX idx_pedido_pago_fecha (payment_status, paid_at),
  ADD INDEX idx_pedido_evento_stripe (last_stripe_event_id);

UPDATE pedidos SET
  payment_status = CASE estado
    WHEN 'pagado' THEN 'paid'
    WHEN 'reembolsado' THEN 'refunded'
    WHEN 'fallido' THEN 'failed'
    WHEN 'cancelado' THEN 'expired'
    ELSE 'pending'
  END,
  paid_at = CASE WHEN estado IN ('pagado','reembolsado') THEN updated_at ELSE NULL END,
  receipt_generated_at = CASE WHEN estado IN ('pagado','reembolsado') THEN updated_at ELSE NULL END;

CREATE TABLE correo_envios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tipo VARCHAR(80) NOT NULL,
  usuario_id INT UNSIGNED NULL,
  pedido_id BIGINT UNSIGNED NULL,
  destinatario_enmascarado VARCHAR(255) NOT NULL,
  destinatario_hash CHAR(64) NOT NULL,
  idempotency_key VARCHAR(190) NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',
  intento INT UNSIGNED NOT NULL DEFAULT 0,
  error_sanitizado VARCHAR(500) NULL,
  procesando_desde DATETIME NULL,
  enviado_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_correo_idempotencia (idempotency_key),
  INDEX idx_correo_reintento (estado, procesando_desde),
  INDEX idx_correo_pedido (pedido_id, tipo),
  INDEX idx_correo_usuario (usuario_id, created_at),
  CONSTRAINT fk_correo_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT fk_correo_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
