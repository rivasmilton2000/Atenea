<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/stripe_finalizacion.php';
$config=configuracionStripe();$autoload=dirname(__DIR__,2).'/includes/stripe/vendor/autoload.php';if(!stripeConfigurado($config)||!is_file($autoload)){error_log('Webhook Stripe: configuración incompleta.');http_response_code(503);exit;}require_once$autoload;
$raw=file_get_contents('php://input');$firma=(string)($_SERVER['HTTP_STRIPE_SIGNATURE']??'');if(!is_string($raw)||$raw===''||$firma===''){http_response_code(400);exit;}
try{$evento=Stripe\Webhook::constructEvent($raw,$firma,$config['webhook_secret']);}catch(Throwable){http_response_code(400);exit;}
$confirmaciones=['checkout.session.completed','checkout.session.async_payment_succeeded','payment_intent.succeeded'];
try{
 if(in_array((string)$evento->type,$confirmaciones,true)){
  $objeto=$evento->data->object;
  if($evento->type==='payment_intent.succeeded'){$lista=(new Stripe\StripeClient($config['secret_key']))->checkout->sessions->all(['payment_intent'=>(string)$objeto->id,'limit'=>1]);$objeto=$lista->data[0]??throw new RuntimeException('Checkout Session no localizada.');}
  finalizarCompraStripe((string)$objeto->id,null,(string)$evento->id,(string)$evento->type,$objeto);http_response_code(200);exit;
 }
 if(in_array((string)$evento->type,['checkout.session.expired','checkout.session.async_payment_failed','payment_intent.payment_failed','charge.refunded'],true)){
  $objeto=$evento->data->object;$esCap=(string)($objeto->metadata->tipo??'')==='capacitacion';
  if($esCap){procesarEstadoWebhookCapacitacion($evento,$objeto);http_response_code(200);exit;}
  $pdo=obtenerConexion();$pdo->beginTransaction();$pdo->prepare('INSERT IGNORE INTO stripe_eventos(stripe_event_id,tipo) VALUES(:id,:t)')->execute(['id'=>(string)$evento->id,'t'=>(string)$evento->type]);$q=$pdo->prepare('SELECT procesado FROM stripe_eventos WHERE stripe_event_id=:id FOR UPDATE');$q->execute(['id'=>(string)$evento->id]);if((int)$q->fetchColumn()===1){$pdo->commit();http_response_code(200);exit;}
  $pedidoId=(int)($objeto->metadata->pedido_id??0);
  if($evento->type==='charge.refunded'){
   $intent=substr((string)($objeto->payment_intent??''),0,255);if($intent!==''){$q=$pdo->prepare('SELECT id FROM pedidos WHERE stripe_payment_intent_id=:pi FOR UPDATE');$q->execute(['pi'=>$intent]);$pedidoId=(int)$q->fetchColumn();if($pedidoId){$pdo->prepare("UPDATE pedidos SET estado='reembolsado',payment_status='refunded',es_intencion_checkout=0 WHERE id=:id AND payment_status='paid'")->execute(['id'=>$pedidoId]);$pdo->prepare("UPDATE pagos SET estado='reembolsado' WHERE pedido_id=:id")->execute(['id'=>$pedidoId]);}}
  }elseif($pedidoId){$q=$pdo->prepare('SELECT * FROM pedidos WHERE id=:id FOR UPDATE');$q->execute(['id'=>$pedidoId]);if($p=$q->fetch()){if($p['payment_status']!=='paid'){foreach($pdo->query('SELECT producto_id,cantidad FROM pedido_detalles WHERE pedido_id='.(int)$pedidoId)as$d)$pdo->prepare('UPDATE productos SET stock_reservado=GREATEST(stock_reservado-:q,0) WHERE id=:id')->execute(['q'=>$d['cantidad'],'id'=>$d['producto_id']]);$estado=$evento->type==='checkout.session.expired'?'cancelado':'pago_fallido';$pdo->prepare("UPDATE pedidos SET estado=:e,payment_status=:p WHERE id=:id AND payment_status<>'paid'")->execute(['e'=>$estado,'p'=>$estado==='cancelado'?'expired':'failed','id'=>$pedidoId]);}}}
  $pdo->prepare('UPDATE stripe_eventos SET procesado=1,procesado_at=NOW() WHERE stripe_event_id=:id')->execute(['id'=>(string)$evento->id]);$pdo->commit();http_response_code(200);exit;
 }
 http_response_code(200);
}catch(Throwable$e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();error_log('Webhook Stripe: '.preg_replace('/[\r\n\t]+/',' ',$e->getMessage()));try{registrarErrorSistemaAtenea('webhook','stripe_finalizacion',$e->getMessage(),['stripe_event_id'=>(string)$evento->id]);}catch(Throwable){}http_response_code(500);}
