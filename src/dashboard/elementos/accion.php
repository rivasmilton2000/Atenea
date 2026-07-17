<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/cms.php';
if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST'||!validarTokenCsrf(isset($_POST['csrf_token'])?(string)$_POST['csrf_token']:null)){cmsFlash('error','Solicitud o token CSRF inválido.');header('Location: index.php');exit;}
$id=cmsId($_POST['id']??0);$accion=(string)($_POST['accion']??'');
if(!$id){cmsFlash('error','ID inválido.');header('Location: index.php');exit;}
$pdo=obtenerConexion();
try{
    if($accion==='toggle'){
        $pdo->beginTransaction();
        $q=$pdo->prepare('SELECT seccion_id,activo FROM elementos_seccion WHERE id=:id FOR UPDATE');$q->execute(['id'=>$id]);$elemento=$q->fetch();
        if(!$elemento)throw new RuntimeException('El elemento no existe.');
        if(!(int)$elemento['activo'])cmsValidarLimiteAreas($pdo,(int)$elemento['seccion_id'],$id);
        $pdo->prepare('UPDATE elementos_seccion SET activo=1-activo WHERE id=:id')->execute(['id'=>$id]);
        $pdo->commit();cmsFlash('exito','Estado actualizado.');
    }elseif($accion==='eliminar'){
        $q=$pdo->prepare('SELECT imagen FROM elementos_seccion WHERE id=:id');$q->execute(['id'=>$id]);$imagen=$q->fetchColumn();
        $pdo->prepare('DELETE FROM elementos_seccion WHERE id=:id')->execute(['id'=>$id]);
        cmsEliminarImagenSiNoSeUsa($imagen?:null);cmsFlash('exito','Elemento eliminado.');
    }else cmsFlash('error','Acción no válida.');
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Acción elemento: '.$e->getMessage());cmsFlash('error',$e instanceof DomainException?$e->getMessage():'No fue posible completar la acción.');}
header('Location: index.php');exit;
