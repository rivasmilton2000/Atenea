<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/auth.php';
require_once dirname(__DIR__,2).'/includes/carrito.php';
require_once dirname(__DIR__,2).'/includes/alerts.php';
exigirRol(['usuario']);
$destino=atenea_url('src/estudiantes/carrito.php');
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf((string)($_POST['csrf_token']??''))){ateneaFlash('error','Solicitud expirada','Recarga la página e inténtalo de nuevo.');header('Location:'.$destino);exit;}
$accion=(string)($_POST['accion']??''); $productoId=filter_var($_POST['producto_id']??0,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:0;
$cantidad=filter_var($_POST['cantidad']??0,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>99]])?:0;
$pdo=obtenerConexion();
try{
 $pdo->beginTransaction(); $carrito=carritoActivo($pdo,(int)$_SESSION['usuario_id'],true,true);
 if($accion==='vaciar'){$pdo->prepare('DELETE FROM carrito_items WHERE carrito_id=:id')->execute(['id'=>$carrito['id']]);}
 elseif(in_array($accion,['agregar','actualizar'],true)&&$productoId&&$cantidad){
  $q=$pdo->prepare('SELECT id,stock,stock_reservado FROM productos WHERE id=:id AND activo=1 AND disponible=1 AND eliminado_at IS NULL FOR UPDATE');$q->execute(['id'=>$productoId]);$p=$q->fetch();
  if(!$p)throw new DomainException('El producto no está disponible.'); if($cantidad>(int)$p['stock']-(int)$p['stock_reservado'])throw new DomainException('La cantidad supera el stock disponible.');
  $pdo->prepare('INSERT INTO carrito_items(carrito_id,producto_id,cantidad) VALUES(:carrito,:producto,:cantidad) ON DUPLICATE KEY UPDATE cantidad=:cantidad2')->execute(['carrito'=>$carrito['id'],'producto'=>$productoId,'cantidad'=>$cantidad,'cantidad2'=>$cantidad]);
 } elseif($accion==='eliminar'&&$productoId){$pdo->prepare('DELETE FROM carrito_items WHERE carrito_id=:carrito AND producto_id=:producto')->execute(['carrito'=>$carrito['id'],'producto'=>$productoId]);}
 else throw new DomainException('Acción de carrito no válida.');
 $pdo->prepare('UPDATE carritos SET version=version+1 WHERE id=:id')->execute(['id'=>$carrito['id']]);$pdo->commit();ateneaFlash('success','Carrito actualizado','Los cambios se guardaron en tu cuenta.');
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();ateneaFlash('warning','No fue posible actualizar',$e instanceof DomainException?$e->getMessage():'Inténtalo nuevamente.');}
header('Location:'.$destino);exit;
