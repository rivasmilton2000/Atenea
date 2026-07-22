<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/includes/cms.php';
require_once dirname(__DIR__,3).'/includes/comercio.php';
require_once dirname(__DIR__,3).'/includes/audit.php';
require_once dirname(__DIR__,3).'/includes/notificaciones.php';
exigirPermiso('orders.manage');
$id=cmsId($_POST['id']??0);$nuevo=(string)($_POST['estado']??'');$nota=trim((string)($_POST['nota']??''));$reversion=($_POST['confirmar_reversion_entregado']??'')==='1';$permitidos=['en_proceso_envio','saliendo_almacen','entregado'];
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf((string)($_POST['csrf_token']??''))||!in_array($nuevo,$permitidos,true)||mb_strlen($nota)>500){cmsFlash('error','Solicitud inválida.');header('Location:index.php');exit;}
$pdo=obtenerConexion();
try{
    $pdo->beginTransaction();$q=$pdo->prepare('SELECT numero,estado,estado_pedido,payment_status FROM pedidos WHERE id=:id FOR UPDATE');$q->execute(['id'=>$id]);$pedido=$q->fetch();$anterior=$pedido['estado_pedido']??null;
    if(in_array($nuevo,['en_proceso_envio','saliendo_almacen','entregado'],true)){
        if(($pedido['payment_status']??'')!=='paid')throw new DomainException('El seguimiento solo puede iniciar después del pago.');
        $anterior=$anterior?:'pagado';$trans=['pagado'=>['en_proceso_envio'],'en_proceso_envio'=>['saliendo_almacen'],'saliendo_almacen'=>['entregado'],'entregado'=>$reversion?['saliendo_almacen','en_proceso_envio']:[]];
        if(!in_array($nuevo,$trans[$anterior]??[],true))throw new DomainException($anterior==='entregado'?'Confirma expresamente la reversión de un pedido entregado.':'La transición de seguimiento no está permitida.');
        $legacy=['en_proceso_envio'=>'preparando','saliendo_almacen'=>'enviado','entregado'=>'entregado'];$pdo->prepare('UPDATE pedidos SET estado_pedido=:s,estado=:e WHERE id=:id')->execute(['s'=>$nuevo,'e'=>$legacy[$nuevo],'id'=>$id]);
    }else{
        $anterior=(string)$pedido['estado'];$trans=['pagado'=>['reembolsado'],'preparando'=>['cancelado','reembolsado'],'enviado'=>['reembolsado'],'entregado'=>['reembolsado']];if(!in_array($nuevo,$trans[$anterior]??[],true))throw new DomainException('La transición de estado no está permitida.');$pdo->prepare('UPDATE pedidos SET estado=:e WHERE id=:id')->execute(['e'=>$nuevo,'id'=>$id]);
    }
    registrarHistorialPedido($pdo,$id,$anterior,$nuevo,'admin',(int)$_SESSION['usuario_id'],$nota?:'Actualización administrativa.');
    registrarAuditoria(['actor_user_id'=>(int)$_SESSION['usuario_id'],'event_type'=>'order.status.changed','module'=>'orders','entity_type'=>'order','entity_id'=>$id,'action'=>'update','result'=>'success','description'=>'Un administrador cambió el estado del pedido.','metadata'=>['from'=>$anterior,'to'=>$nuevo]],$pdo);
    crearNotificacionAtenea(['rol'=>'admin','created_by'=>$_SESSION['usuario_id'],'tipo'=>'pedido_'.$nuevo,'categoria'=>'pedidos','nivel'=>in_array($nuevo,['cancelado','reembolsado'],true)?'advertencia':'exito','titulo'=>'Pedido '.$pedido['numero'].' '.$nuevo,'descripcion'=>$nota?:'Estado actualizado por administración.','url'=>atenea_url('src/dashboard/pedidos/detalle.php?id='.$id),'pedido_id'=>$id,'idempotency_key'=>'pedido:'.$id.':estado:'.$nuevo],$pdo);
    $pdo->commit();cmsFlash('exito','Estado actualizado.');
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();cmsFlash('error',$e instanceof DomainException?$e->getMessage():'No fue posible actualizar.');}
header('Location:detalle.php?id='.$id);
