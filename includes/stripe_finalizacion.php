<?php
declare(strict_types=1);

require_once __DIR__.'/stripe_config.php';
require_once __DIR__.'/pedidos_pago.php';
require_once __DIR__.'/capacitaciones.php';
require_once __DIR__.'/comprobantes.php';
require_once __DIR__.'/comprobantes_capacitacion.php';
require_once __DIR__.'/errores_sistema.php';

function estadoPagoAtenea(?string $estado): string
{
    return in_array(strtolower(trim((string)$estado)), ['paid','pagado','completado','completed','success','succeeded'], true) ? 'pagado' : strtolower(trim((string)$estado));
}

function etiquetaEstadoPagoAtenea(?string $estado): string
{
    return estadoPagoAtenea($estado)==='pagado' ? 'Pago completado' : 'No confirmado';
}

function obtenerCheckoutSessionStripe(string $sessionId): object
{
    if (!preg_match('/^cs_(?:test_|live_)?[A-Za-z0-9]+$/', $sessionId)) throw new DomainException('La referencia de Stripe no es válida.');
    $config=configuracionStripe();$autoload=__DIR__.'/stripe/vendor/autoload.php';
    if(!stripeConfigurado($config)||!is_file($autoload))throw new RuntimeException('Stripe no está disponible para sincronizar el pago.');
    require_once $autoload;
    return (new Stripe\StripeClient($config['secret_key']))->checkout->sessions->retrieve($sessionId,[]);
}

function postprocesarCompraStripe(string $tipo, int $id): array
{
    $resultado=['documentos'=>false,'correo'=>false];
    try {
        if($tipo==='capacitacion'){
            generarComprobantesCapacitacionAtenea($id);$resultado['documentos']=true;
            $resultado['correo']=enviarConfirmacionCompraCapacitacionAtenea($id);
        } else {
            generarComprobantesCompraAtenea($id);$resultado['documentos']=true;
            $resultado['correo']=enviarConfirmacionCompraAtenea($id);
        }
    } catch(Throwable $e) {
        registrarErrorSistemaAtenea('comprobante','postprocesar_compra_stripe',$e->getMessage(),[$tipo.'_id'=>$id]);
        error_log('Postproceso Stripe '.$tipo.' #'.$id.': '.preg_replace('/[\r\n\t]+/',' ',$e->getMessage()));
    }
    return $resultado;
}

/** Único punto de finalización para retorno, webhook y sincronización administrativa. */
function finalizarCompraStripe(string $checkoutSessionId, ?int $usuarioEsperado=null, ?string $stripeEventId=null, string $eventType='server.checkout.sync', ?object $sesionVerificada=null): array
{
    $sesion=$sesionVerificada ?? obtenerCheckoutSessionStripe($checkoutSessionId);
    if(!hash_equals($checkoutSessionId,(string)($sesion->id??'')))throw new RuntimeException('Stripe devolvió una sesión diferente.');
    if((string)($sesion->payment_status??'')!=='paid' || (isset($sesion->status)&&!in_array((string)$sesion->status,['complete'],true)))throw new DomainException('Stripe todavía no confirma este pago como completado.');
    $tipo=(string)($sesion->metadata->tipo??'')==='capacitacion'?'capacitacion':'producto';
    $referencia=$stripeEventId ?: 'server_sync_'.hash('sha256',$checkoutSessionId);

    if($tipo==='capacitacion'){
        $pagoId=(int)($sesion->metadata->capacitacion_pago_id??0);$pdo=obtenerConexion();
        if($usuarioEsperado!==null){$q=$pdo->prepare('SELECT usuario_id FROM capacitacion_pagos WHERE id=:id AND stripe_checkout_session_id=:s');$q->execute(['id'=>$pagoId,'s'=>$checkoutSessionId]);if((int)$q->fetchColumn()!==$usuarioEsperado)throw new DomainException('La operación no pertenece a tu cuenta.');}
        procesarWebhookCapacitacion((object)['id'=>$referencia,'type'=>$eventType],$sesion);
        $pdo->prepare("UPDATE capacitacion_pagos SET es_intencion_checkout=0,oficializado_at=COALESCE(oficializado_at,NOW()) WHERE id=:id AND estado='pagado'")->execute(['id'=>$pagoId]);
        $post=postprocesarCompraStripe('capacitacion',$pagoId);
        return ['tipo'=>'capacitacion','id'=>$pagoId,'pago_id'=>$pagoId,'estado_pago'=>'pagado']+$post;
    }

    $pedidoId=(int)($sesion->metadata->pedido_id??$sesion->client_reference_id??0);$pdo=obtenerConexion();
    if($usuarioEsperado!==null){$q=$pdo->prepare('SELECT usuario_id FROM pedidos WHERE id=:id AND stripe_checkout_session_id=:s');$q->execute(['id'=>$pedidoId,'s'=>$checkoutSessionId]);if((int)$q->fetchColumn()!==$usuarioEsperado)throw new DomainException('La operación no pertenece a tu cuenta.');}
    $config=configuracionStripe();$metodo=['payment_method_id'=>null,'brand'=>null,'last4'=>null];
    if(function_exists('datosMetodoPagoStripe'))$metodo=datosMetodoPagoStripe($sesion,$config);
    $pdo->beginTransaction();
    try{
        $pdo->prepare('INSERT IGNORE INTO stripe_eventos(stripe_event_id,tipo) VALUES(:id,:tipo)')->execute(['id'=>$referencia,'tipo'=>$eventType]);
        $r=confirmarPedidoDesdeCheckoutStripe($pdo,$sesion,$referencia,$metodo);
        $pdo->prepare("UPDATE pedidos SET es_intencion_checkout=0,oficializado_at=COALESCE(oficializado_at,NOW()) WHERE id=:id AND payment_status='paid'")->execute(['id'=>$pedidoId]);
        $pdo->prepare("UPDATE carritos c JOIN pedidos p ON p.carrito_id=c.id SET c.estado='convertido' WHERE p.id=:id AND p.payment_status='paid'")->execute(['id'=>$pedidoId]);
        $pdo->prepare('UPDATE stripe_eventos SET procesado=1,error_mensaje=NULL,procesado_at=COALESCE(procesado_at,NOW()) WHERE stripe_event_id=:id')->execute(['id'=>$referencia]);
        $pdo->commit();
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw$e;}
    $post=postprocesarCompraStripe('producto',$pedidoId);
    return ['tipo'=>'producto','id'=>$pedidoId,'pedido_id'=>$pedidoId,'estado_pago'=>'pagado','cambio'=>(bool)$r['cambio']]+$post;
}
