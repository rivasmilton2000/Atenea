<?php
declare(strict_types=1);
require_once __DIR__ . '/_categorias.php';
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf(isset($_POST['csrf_token'])?(string)$_POST['csrf_token']:null)){cmsFlash('error','La solicitud expiró. Intenta nuevamente.');header('Location:index.php');exit;}
$id=cmsId($_POST['id']??0);$accion=(string)($_POST['accion']??'');$destinoId=cmsId($_POST['destino_id']??0);$pdo=obtenerConexion();
try{
    $pdo->beginTransaction();
    $consulta=$pdo->prepare('SELECT * FROM categorias_producto WHERE id=:id AND eliminado_at IS NULL LIMIT 1 FOR UPDATE');$consulta->execute(['id'=>$id]);$categoria=$consulta->fetch();
    if(!$categoria)throw new CategoriaOperacionException('Categoría no encontrada.');
    $productos=$pdo->prepare('SELECT COUNT(*) FROM productos WHERE categoria_id=:id');$productos->execute(['id'=>$id]);$totalProductos=(int)$productos->fetchColumn();
    if($accion==='toggle'){$pdo->prepare('UPDATE categorias_producto SET activo=1-activo,actualizado_por=:usuario WHERE id=:id')->execute(['usuario'=>$_SESSION['usuario_id'],'id'=>$id]);$mensaje='Estado de la categoría actualizado.';}
    elseif($accion==='desactivar'){$pdo->prepare('UPDATE categorias_producto SET activo=0,actualizado_por=:usuario WHERE id=:id')->execute(['usuario'=>$_SESSION['usuario_id'],'id'=>$id]);$mensaje='La categoría fue desactivada sin modificar sus productos.';}
    elseif($accion==='eliminar'){
        if($totalProductos>0)throw new CategoriaOperacionException('La categoría tiene productos. Reasígnalos o desactiva la categoría.');
        $slugEliminado=mb_substr((string)$categoria['slug'],0,115).'-eliminada-'.$id.'-'.bin2hex(random_bytes(3));
        $pdo->prepare('UPDATE categorias_producto SET activo=0,slug=:slug,eliminado_at=NOW(),actualizado_por=:usuario WHERE id=:id')->execute(['slug'=>$slugEliminado,'usuario'=>$_SESSION['usuario_id'],'id'=>$id]);$mensaje='Categoría eliminada correctamente.';
    }
    elseif($accion==='reasignar'){
        if($totalProductos<1)throw new CategoriaOperacionException('La categoría no tiene productos para reasignar.');
        if($destinoId<1||$destinoId===$id)throw new CategoriaOperacionException('Selecciona una categoría de destino diferente.');
        $destino=$pdo->prepare('SELECT id FROM categorias_producto WHERE id=:id AND activo=1 AND eliminado_at IS NULL LIMIT 1 FOR UPDATE');$destino->execute(['id'=>$destinoId]);if(!$destino->fetch())throw new CategoriaOperacionException('La categoría de destino no está disponible.');
        $pdo->prepare('UPDATE productos SET categoria_id=:destino,actualizado_por=:usuario WHERE categoria_id=:origen')->execute(['destino'=>$destinoId,'usuario'=>$_SESSION['usuario_id'],'origen'=>$id]);
        $slugEliminado=mb_substr((string)$categoria['slug'],0,115).'-eliminada-'.$id.'-'.bin2hex(random_bytes(3));
        $pdo->prepare('UPDATE categorias_producto SET activo=0,slug=:slug,eliminado_at=NOW(),actualizado_por=:usuario WHERE id=:id')->execute(['slug'=>$slugEliminado,'usuario'=>$_SESSION['usuario_id'],'id'=>$id]);$mensaje='Productos reasignados y categoría eliminada correctamente.';
    } else throw new CategoriaOperacionException('Acción no válida.');
    $pdo->commit();
    if(in_array($accion,['eliminar','reasignar'],true)&&!empty($categoria['imagen']))cmsEliminarImagenSiNoSeUsa((string)$categoria['imagen']);
    cmsFlash('exito',$mensaje);
}catch(Throwable$e){if($pdo->inTransaction())$pdo->rollBack();if(!$e instanceof CategoriaOperacionException)error_log('Acción de categoría Atenea: '.$e->getMessage());cmsFlash('error',$e instanceof CategoriaOperacionException?$e->getMessage():'No fue posible completar la acción.');}
header('Location:index.php');exit;
