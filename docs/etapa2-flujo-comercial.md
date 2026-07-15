# Etapa 2: flujo comercial y DTE

## Flujo encontrado

Antes de esta etapa, la ficha de producto enviaba directamente producto y cantidad a `src/pagos/crear-checkout.php`. Ese endpoint recalculaba el precio, reservaba stock y creaba un pedido de una sola línea. Stripe confirmaba por webhook, descontaba stock una sola vez mediante `stock_procesado`, registraba el pago y encolaba el correo. Ya existían `pedidos`, `pedido_detalles`, `pagos`, `pedido_historial`, `stripe_eventos`, `inventario_movimientos` y `correo_envios`; se amplían, no se duplican.

No existían carrito persistente, libreta de direcciones ni documentos DTE. Los dos pedidos previos conservan sus detalles y reservas; sus estados se normalizan a `pendiente_pago`.

## Flujo ampliado

`producto -> carrito persistente -> dirección propia -> confirmación -> pedido inmutable -> Stripe -> webhook idempotente -> stock/pago -> DTE -> PDF/JSON privados -> correo reintentable -> historial del usuario`.

El precio, promociones, stock, envío e impuestos se recalculan en el servidor. La dirección se copia como JSON inmutable al pedido. El DTE solamente se genera tras un pago confirmado. En `simulation` no se llama a Hacienda y todos los artefactos advierten que carecen de validez fiscal. En `test` o `production` la operación se bloquea si faltan credenciales o certificado.

## Persistencia y seguridad

- Carritos e ítems pertenecen al usuario autenticado.
- El `checkout_key` impide crear dos pedidos para la misma confirmación.
- Los eventos Stripe y el DTE tienen restricciones únicas.
- PDF y JSON se sirven por endpoints que validan propietario o permiso administrativo; el almacenamiento queda fuera del directorio público.
- Los secretos y certificados proceden del entorno local y no se guardan en Git.
