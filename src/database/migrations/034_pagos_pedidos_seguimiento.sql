USE db_atenea;

-- El pago continúa en payment_status/estado. Este campo representa únicamente la entrega.
ALTER TABLE pedidos
  ADD COLUMN IF NOT EXISTS estado_pedido ENUM('pagado','en_proceso_envio','saliendo_almacen','entregado') NULL AFTER payment_status;

UPDATE pedidos
SET estado_pedido = CASE estado
  WHEN 'preparando' THEN 'en_proceso_envio'
  WHEN 'enviado' THEN 'saliendo_almacen'
  WHEN 'entregado' THEN 'entregado'
  WHEN 'pagado' THEN 'pagado'
  ELSE estado_pedido
END
WHERE payment_status = 'paid' AND estado_pedido IS NULL;

ALTER TABLE pedidos
  ADD INDEX IF NOT EXISTS idx_pedido_seguimiento (estado_pedido, updated_at);
