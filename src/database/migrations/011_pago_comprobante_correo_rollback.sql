USE db_atenea;

DROP TABLE IF EXISTS correo_envios;

ALTER TABLE pedidos
  DROP INDEX idx_pedido_pago_fecha,
  DROP INDEX idx_pedido_evento_stripe,
  DROP COLUMN email_sent_at,
  DROP COLUMN receipt_generated_at,
  DROP COLUMN last_stripe_event_id,
  DROP COLUMN stripe_payment_method_id,
  DROP COLUMN payment_last4,
  DROP COLUMN payment_brand,
  DROP COLUMN paid_at,
  DROP COLUMN payment_status;

ALTER TABLE productos
  DROP COLUMN informacion_entrega,
  DROP COLUMN caracteristicas,
  DROP COLUMN tipo_producto;
