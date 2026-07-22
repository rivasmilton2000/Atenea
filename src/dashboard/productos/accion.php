<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/includes/cms.php';
require_once dirname(__DIR__,3).'/includes/audit.php';
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf($_POST['csrf_token']??null)){cmsFlash('error','Solicitud inválida.');header('Location:index.php');exit;}
$id=cmsId($_POST['id']??0);$accion=(string)($_POST['accion']??'');$pdo=obtenerConexion();
try{
 if($accion==='toggle')$pdo->prepare('UPDATE productos SET activo=1-activo WHERE id=:id')->execute(['id'=>$id]);
 elseif($accion==='eliminar'){$q=$pdo->prepare('SELECT COUNT(*) FROM pedido_detalles WHERE producto_id=:id');$q->execute(['id'=>$id]);if((int)$q->fetchColumn()>0)$pdo->prepare('UPDATE productos SET activo=0,disponible=0,eliminado_at=NOW() WHERE id=:id')->execute(['id'=>$id]);else$pdo->prepare('DELETE FROM productos WHERE id=:id')->execute(['id'=>$id]);}
 elseif($accion==='eliminar_imagen'){$q=$pdo->prepare('SELECT ruta FROM producto_imagenes WHERE id=:id');$q->execute(['id'=>$id]);$ruta=$q->fetchColumn();$pdo->prepare('DELETE FROM producto_imagenes WHERE id=:id')->execute(['id'=>$id]);registrarAuditoria(['actor_user_id'=>(int)$_SESSION['usuario_id'],'event_type'=>'product.image.deleted','module'=>'products','entity_type'=>'product_image','entity_id'=>$id,'action'=>'delete','result'=>'success','description'=>'Imagen de producto eliminada.'],$pdo);cmsEliminarImagenSiNoSeUsa($ruta?:null);cmsFlash('exito','Imagen eliminada.');header('Location:editar.php?id='.cmsId($_POST['producto_id']??0));exit;}
 else throw new DomainException('Acción no permitida.');
 registrarAuditoria(['actor_user_id'=>(int)$_SESSION['usuario_id'],'event_type'=>'product.'.$accion,'module'=>'products','entity_type'=>'product','entity_id'=>$id,'action'=>$accion,'result'=>'success','description'=>'Acción administrativa ejecutada sobre un producto.'],$pdo);cmsFlash('exito','Acción completada.');
}catch(Throwable$error){error_log('Producto acción: '.$error->getMessage());registrarAuditoria(['actor_user_id'=>(int)($_SESSION['usuario_id']??0),'event_type'=>'product.action.failed','module'=>'products','entity_type'=>'product','entity_id'=>$id,'action'=>$accion?:'unknown','result'=>'failure','description'=>'No fue posible completar una acción sobre un producto.']);cmsFlash('error','No fue posible completar la acción.');}
header('Location:index.php');exit;
