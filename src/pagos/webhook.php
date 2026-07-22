<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/pedidos_pago.php';
require_once dirname(__DIR__, 2) . '/includes/stripe_config.php';
require_once dirname(__DIR__, 2) . '/includes/audit.php';
require_once dirname(__DIR__, 2) . '/includes/carrito.php';
require_once dirname(__DIR__, 2) . '/includes/dte.php';
require_once dirname(__DIR__, 2) . '/includes/admin_notification_service.php';
require_once dirname(__DIR__, 2) . '/includes/errores_sistema.php';

function liberarReservaPedido(PDO $pdo, array $pedido, string $estado, string $nota): void
{
    if (!in_array($pedido['estado'], ['pendiente_pago', 'pago_fallido'], true) || (int) $pedido['stock_procesado'] === 1) return;
    $detalles = $pdo->prepare('SELECT producto_id,cantidad FROM pedido_detalles WHERE pedido_id=:pedido');
    $detalles->execute(['pedido' => $pedido['id']]);
    foreach ($detalles->fetchAll() as $detalle) {
        $pdo->prepare('UPDATE productos SET stock_reservado=GREATEST(stock_reservado-:cantidad,0) WHERE id=:producto')
            ->execute(['cantidad' => $detalle['cantidad'], 'producto' => $detalle['producto_id']]);
    }
    $pdo->prepare('UPDATE pedidos SET estado=:estado,payment_status=:pago WHERE id=:id')
        ->execute(['estado' => $estado, 'pago' => $estado === 'cancelado' ? 'expired' : 'failed', 'id' => $pedido['id']]);
    registrarHistorialPedido($pdo, (int) $pedido['id'], (string) $pedido['estado'], $estado, 'stripe', null, $nota);
}

function datosMetodoPagoStripe(object $sesion, array $configuracion): array
{
    $resultado = ['payment_method_id' => null, 'brand' => null, 'last4' => null];
    $paymentIntentId = is_string($sesion->payment_intent ?? null) ? $sesion->payment_intent : '';
    if ($paymentIntentId === '') return $resultado;
    try {
        $stripe = new Stripe\StripeClient($configuracion['secret_key']);
        $intent = $stripe->paymentIntents->retrieve($paymentIntentId, ['expand' => ['payment_method']]);
        $metodo = $intent->payment_method ?? null;
        if ($metodo instanceof Stripe\PaymentMethod) {
            $resultado['payment_method_id'] = (string) $metodo->id;
            if (($metodo->type ?? '') === 'card' && isset($metodo->card)) {
                $resultado['brand'] = substr((string) ($metodo->card->brand ?? ''), 0, 32) ?: null;
                $ultimos = (string) ($metodo->card->last4 ?? '');
                $resultado['last4'] = preg_match('/^\d{4}$/', $ultimos) ? $ultimos : null;
            }
        } elseif (is_string($metodo) && $metodo !== '') {
            $resultado['payment_method_id'] = substr($metodo, 0, 255);
        }
    } catch (Throwable $error) {
        error_log('Método de pago Stripe no disponible: ' . $error->getMessage());
    }
    return $resultado;
}

$configuracion = configuracionStripe();
$autoload = dirname(__DIR__, 2) . '/includes/stripe/vendor/autoload.php';
if (!stripeConfigurado($configuracion) || !is_file($autoload)) {
    error_log('Webhook Stripe no disponible: configuración o SDK incompletos.');
    http_response_code(503);
    exit;
}
require_once $autoload;

$raw = file_get_contents('php://input');
$firma = (string) ($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');
if (!is_string($raw) || $raw === '' || $firma === '') {
    error_log('Webhook Stripe rechazado: cuerpo crudo o firma ausente.');
    http_response_code(400);
    exit;
}
try {
    $evento = Stripe\Webhook::constructEvent($raw, $firma, $configuracion['webhook_secret']);
} catch (Throwable $error) {
    error_log('Webhook Stripe rechazado: firma o payload inválido (' . get_class($error) . ').');
    http_response_code(400);
    exit;
}

$objeto = $evento->data->object;
if ($evento->type === 'payment_intent.succeeded') {
    try {
        $sesiones = (new Stripe\StripeClient($configuracion['secret_key']))->checkout->sessions->all([
            'payment_intent' => (string) ($objeto->id ?? ''), 'limit' => 1,
        ]);
        $objeto = $sesiones->data[0] ?? throw new RuntimeException('Checkout Session no localizada para el Payment Intent.');
    } catch (Throwable $error) {
        error_log('Webhook Stripe payment_intent.succeeded: ' . preg_replace('/[\r\n\t]+/', ' ', $error->getMessage()));
        http_response_code(500); exit;
    }
}
$esPagoCapacitacion = (string) ($objeto->metadata->tipo ?? '') === 'capacitacion';
if ($esPagoCapacitacion) {
    require_once dirname(__DIR__, 2) . '/includes/capacitaciones.php';
    try {
        if (in_array($evento->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded', 'payment_intent.succeeded'], true)) procesarWebhookCapacitacion($evento, $objeto);
        else procesarEstadoWebhookCapacitacion($evento, $objeto);
        http_response_code(200);
    } catch (Throwable $error) {
        error_log('Webhook capacitación: ' . $error->getMessage());
        http_response_code(500);
    }
    exit;
}
$pedidoId = (int) ($objeto->metadata->pedido_id ?? 0);
$esConfirmacion = in_array($evento->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded', 'payment_intent.succeeded'], true);
$metodoPago = $esConfirmacion ? datosMetodoPagoStripe($objeto, $configuracion) : ['payment_method_id' => null, 'brand' => null, 'last4' => null];
$pdo = obtenerConexion();
$enviarCorreoPedido = null;

try {
    $pdo->beginTransaction();
    $pdo->prepare('INSERT IGNORE INTO stripe_eventos(stripe_event_id,tipo) VALUES(:id,:tipo)')->execute(['id' => $evento->id, 'tipo' => $evento->type]);
    $consulta = $pdo->prepare('SELECT procesado FROM stripe_eventos WHERE stripe_event_id=:id FOR UPDATE');
    $consulta->execute(['id' => $evento->id]);
    if ((int) $consulta->fetchColumn() === 1) {
        $pdo->commit();
        if ($esConfirmacion && $pedidoId > 0) { try { $dte=generarDtePedidoSeguro($pedidoId); crearNotificacionAtenea(['rol'=>'admin','tipo'=>'dte_generado','categoria'=>'dte','nivel'=>'exito','titulo'=>'DTE generado','descripcion'=>'Documento generado para el pedido #'.$pedidoId.'.','url'=>atenea_url('src/dashboard/facturas/detalle.php?id='.$dte['id']),'pedido_id'=>$pedidoId,'idempotency_key'=>'dte:generado:'.$dte['id']]); } catch(Throwable $e) { registrarErrorSistemaAtenea('dte','generar_dte',$e->getMessage(),['pedido_id'=>$pedidoId]); } enviarConfirmacionCompraAtenea($pedidoId); }
        http_response_code(200);
        exit;
    }

    if ($esConfirmacion && $pedidoId < 1) throw new RuntimeException('El evento no contiene una referencia de pedido válida.');

    if ($esConfirmacion && $pedidoId > 0) {
        $consulta = $pdo->prepare('SELECT * FROM pedidos WHERE id=:id FOR UPDATE');
        $consulta->execute(['id' => $pedidoId]);
        $pedido = $consulta->fetch();
        if (!$pedido) throw new RuntimeException('Pedido de Stripe no localizado.');

        $importeEsperado = dineroCentavos((string) $pedido['total']);
        $sesionCoincide = hash_equals((string) ($pedido['stripe_checkout_session_id'] ?? ''), (string) ($objeto->id ?? ''));
        $referenciaCoincide = hash_equals((string) $pedidoId, (string) ($objeto->client_reference_id ?? ''));
        $numeroCoincide = hash_equals((string) $pedido['numero'], (string) ($objeto->metadata->numero ?? ''));
        $importeCoincide = (int) ($objeto->amount_total ?? -1) === $importeEsperado;
        $monedaCoincide = strtolower((string) ($objeto->currency ?? '')) === strtolower((string) $pedido['moneda']);
        $pagoConfirmado = (string) ($objeto->payment_status ?? '') === 'paid';

        if (!$sesionCoincide || !$referenciaCoincide || !$numeroCoincide || !$importeCoincide || !$monedaCoincide) {
            throw new RuntimeException('La sesión, referencia, importe o moneda no coincide con el pedido.');
        }
        if (!$pagoConfirmado) {
            $pdo->prepare("UPDATE pedidos SET payment_status='pending',last_stripe_event_id=:evento WHERE id=:id")
                ->execute(['evento' => $evento->id, 'id' => $pedidoId]);
        } elseif ($pedido['estado'] !== 'pagado') {
            $detalles = $pdo->prepare('SELECT * FROM pedido_detalles WHERE pedido_id=:pedido');
            $detalles->execute(['pedido' => $pedidoId]);
            foreach ($detalles->fetchAll() as $detalle) {
                $producto = $pdo->prepare('SELECT stock,stock_reservado FROM productos WHERE id=:id FOR UPDATE');
                $producto->execute(['id' => $detalle['producto_id']]);
                $stock = $producto->fetch();
                if (!$stock || (int) $stock['stock'] < (int) $detalle['cantidad']) throw new RuntimeException('Stock insuficiente al confirmar el pedido.');
                $nuevoStock = (int) $stock['stock'] - (int) $detalle['cantidad'];
                $pdo->prepare('UPDATE productos SET stock=:stock,stock_reservado=GREATEST(stock_reservado-:cantidad,0) WHERE id=:id')
                    ->execute(['stock' => $nuevoStock, 'cantidad' => $detalle['cantidad'], 'id' => $detalle['producto_id']]);
                $pdo->prepare("INSERT INTO inventario_movimientos(producto_id,pedido_id,tipo,cantidad,stock_anterior,stock_nuevo,nota) VALUES(:producto,:pedido,'venta',:cantidad,:anterior,:nuevo,'Venta confirmada por webhook Stripe')")
                    ->execute(['producto' => $detalle['producto_id'], 'pedido' => $pedidoId, 'cantidad' => -(int) $detalle['cantidad'], 'anterior' => $stock['stock'], 'nuevo' => $nuevoStock]);
            }
            $paymentIntent = substr((string) ($objeto->payment_intent ?? ''), 0, 255);
            $pdo->prepare("UPDATE pedidos SET estado='pagado',payment_status='paid',estado_pedido=COALESCE(estado_pedido,'pagado'),paid_at=COALESCE(paid_at,NOW()),stripe_payment_intent_id=:intent,payment_brand=:marca,payment_last4=:ultimos,stripe_payment_method_id=:metodo,last_stripe_event_id=:evento,receipt_generated_at=COALESCE(receipt_generated_at,NOW()),stock_procesado=1 WHERE id=:id")
                ->execute(['intent' => $paymentIntent ?: null, 'marca' => $metodoPago['brand'], 'ultimos' => $metodoPago['last4'], 'metodo' => $metodoPago['payment_method_id'], 'evento' => $evento->id, 'id' => $pedidoId]);
            $referencia = json_encode(['checkout_session' => (string) $objeto->id, 'stripe_event' => (string) $evento->id], JSON_THROW_ON_ERROR);
            $pdo->prepare("INSERT INTO pagos(pedido_id,stripe_payment_intent_id,importe,moneda,estado,datos_referencia) VALUES(:pedido,:intent,:importe,:moneda,'pagado',:referencia) ON DUPLICATE KEY UPDATE estado='pagado',stripe_payment_intent_id=VALUES(stripe_payment_intent_id),importe=VALUES(importe),moneda=VALUES(moneda),datos_referencia=VALUES(datos_referencia)")
                ->execute(['pedido' => $pedidoId, 'intent' => $paymentIntent ?: null, 'importe' => $pedido['total'], 'moneda' => $pedido['moneda'], 'referencia' => $referencia]);
            registrarHistorialPedido($pdo, $pedidoId, (string) $pedido['estado'], 'pagado', 'stripe', null, 'Pago, importe y moneda confirmados por webhook; stock descontado.');
            registrarAuditoria(['target_user_id'=>(int)$pedido['usuario_id'],'event_type'=>'payment.approved','module'=>'payments','entity_type'=>'order','entity_id'=>$pedidoId,'action'=>'confirm','result'=>'success','description'=>'Stripe confirmo el pago por webhook verificado; importe y moneda coincidieron.','metadata'=>['currency'=>$pedido['moneda'],'brand'=>$metodoPago['brand'],'last4'=>$metodoPago['last4']]],$pdo);
            $enviarCorreoPedido = $pedidoId;
        } else {
            $enviarCorreoPedido = $pedidoId;
        }
    } elseif ($evento->type === 'checkout.session.expired' && $pedidoId > 0) {
        $consulta = $pdo->prepare('SELECT * FROM pedidos WHERE id=:id FOR UPDATE');
        $consulta->execute(['id' => $pedidoId]);
        if ($pedido = $consulta->fetch()) liberarReservaPedido($pdo, $pedido, 'cancelado', 'Checkout expirado; reserva liberada.');
    } elseif ($evento->type === 'checkout.session.async_payment_failed' && $pedidoId > 0) {
        $consulta = $pdo->prepare('SELECT * FROM pedidos WHERE id=:id FOR UPDATE');
        $consulta->execute(['id' => $pedidoId]);
        if ($pedido = $consulta->fetch()) liberarReservaPedido($pdo, $pedido, 'pago_fallido', 'Pago asíncrono rechazado; reserva liberada.');
    } elseif ($evento->type === 'payment_intent.payment_failed' && $pedidoId > 0) {
        $consulta = $pdo->prepare("UPDATE pedidos SET estado='pago_fallido',payment_status='failed',stripe_payment_intent_id=:intent,last_stripe_event_id=:evento WHERE id=:id AND estado<>'pagado'");
        $consulta->execute(['intent' => substr((string) $objeto->id, 0, 255), 'evento' => $evento->id, 'id' => $pedidoId]);
        if ($consulta->rowCount() === 1) registrarHistorialPedido($pdo, $pedidoId, 'pendiente_pago', 'pago_fallido', 'stripe', null, 'Intento de pago rechazado por Stripe; la reserva se conserva hasta expirar el Checkout.');
        if ($consulta->rowCount() === 1) registrarAuditoria(['event_type'=>'payment.failed','module'=>'payments','entity_type'=>'order','entity_id'=>$pedidoId,'action'=>'confirm','result'=>'failure','description'=>'Stripe informo un intento de pago fallido.'], $pdo);
    } elseif ($evento->type === 'charge.refunded') {
        $paymentIntent = substr((string) ($objeto->payment_intent ?? ''), 0, 255);
        if ($paymentIntent !== '') {
            $consulta = $pdo->prepare('SELECT id,estado FROM pedidos WHERE stripe_payment_intent_id=:intent FOR UPDATE');
            $consulta->execute(['intent' => $paymentIntent]);
            if ($pedido = $consulta->fetch()) {
                $pdo->prepare("UPDATE pedidos SET estado='reembolsado',payment_status='refunded',last_stripe_event_id=:evento WHERE id=:id")
                    ->execute(['evento' => $evento->id, 'id' => $pedido['id']]);
                $pdo->prepare("UPDATE pagos SET estado='reembolsado' WHERE pedido_id=:id")->execute(['id' => $pedido['id']]);
                registrarHistorialPedido($pdo, (int) $pedido['id'], (string) $pedido['estado'], 'reembolsado', 'stripe', null, 'Reembolso informado por Stripe; inventario no repuesto automáticamente.');
            }
        }
    }

    $pdo->prepare('UPDATE stripe_eventos SET procesado=1,error_mensaje=NULL,procesado_at=NOW() WHERE stripe_event_id=:id')->execute(['id' => $evento->id]);
    $pdo->commit();
    if ($pedidoId > 0) {
        $mapa = [
            'checkout.session.completed'=>['pago_confirmado','Pago confirmado','exito'],
            'checkout.session.async_payment_succeeded'=>['pago_confirmado','Pago confirmado','exito'],
            'payment_intent.succeeded'=>['pago_confirmado','Pago confirmado','exito'],
            'checkout.session.async_payment_failed'=>['pago_fallido','Pago fallido','error'],
            'payment_intent.payment_failed'=>['pago_fallido','Pago fallido','error'],
            'checkout.session.expired'=>['pedido_cancelado','Pedido cancelado','advertencia'],
            'charge.refunded'=>['pedido_reembolsado','Pedido reembolsado','advertencia'],
        ];
        if(isset($mapa[$evento->type])){$n=$mapa[$evento->type];notificarAdministracionAtenea($n[0],$n[1],'Stripe procesó el evento del pedido #'.$pedidoId.'.',$n[2]==='exito'?'informacion':$n[2],null,atenea_url('src/dashboard/pedidos/detalle.php?id='.$pedidoId),'stripe:notificacion:'.$evento->id,['category'=>'pagos','pedido_id'=>$pedidoId]);}
    }
    if ($enviarCorreoPedido) { try { $dte=generarDtePedidoSeguro($enviarCorreoPedido); crearNotificacionAtenea(['rol'=>'admin','tipo'=>'dte_generado','categoria'=>'dte','nivel'=>'exito','titulo'=>'DTE generado','descripcion'=>'Documento generado para el pedido #'.$enviarCorreoPedido.'.','url'=>atenea_url('src/dashboard/facturas/detalle.php?id='.$dte['id']),'pedido_id'=>$enviarCorreoPedido,'idempotency_key'=>'dte:generado:'.$dte['id']]); } catch(Throwable $e) { registrarErrorSistemaAtenea('dte','generar_dte',$e->getMessage(),['pedido_id'=>$enviarCorreoPedido]); } enviarConfirmacionCompraAtenea($enviarCorreoPedido); }
    http_response_code(200);
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    try {
        $mensaje = mb_substr(preg_replace('/[\r\n\t]+/', ' ', $error->getMessage()) ?: 'Error de procesamiento', 0, 500);
        $pdo->prepare('INSERT INTO stripe_eventos(stripe_event_id,tipo,procesado,error_mensaje) VALUES(:id,:tipo,0,:error) ON DUPLICATE KEY UPDATE procesado=0,error_mensaje=VALUES(error_mensaje)')
            ->execute(['id' => $evento->id, 'tipo' => $evento->type, 'error' => $mensaje]);
    } catch (Throwable) {}
    try { $categoriaError=str_contains(mb_strtolower($error->getMessage()),'stock')?'stock':'webhook';registrarErrorSistemaAtenea($categoriaError,'stripe_webhook',$error->getMessage(),['pedido_id'=>$pedidoId?:null,'stripe_event_id'=>(string)$evento->id]); } catch (Throwable) {}
    error_log('Webhook Stripe: ' . $error->getMessage());
    http_response_code(500);
}
