<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/includes/cms.php';
require_once dirname(__DIR__,3).'/includes/database_backups.php';
require_once dirname(__DIR__,3).'/includes/admin_users.php';
exigirPermiso('backups.manage');
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf((string)($_POST['csrf_token']??''))){cmsFlash('error','La solicitud venció o no es válida.');header('Location:index.php');exit;}
$accion=(string)($_POST['accion']??'');$id=cmsId($_POST['id']??0);$actor=(int)$_SESSION['usuario_id'];
try{
    if($accion==='crear'){set_time_limit(0);crearRespaldoBaseDatos($actor);cmsFlash('exito','La copia de seguridad se creó correctamente.');}
    elseif($accion==='eliminar'&&$id){eliminarRespaldoBaseDatos($id,$actor);cmsFlash('exito','La copia fue eliminada de forma controlada.');}
    elseif($accion==='restaurar'&&$id){exigirPermiso('backups.restore');$frase=trim((string)($_POST['frase']??''));if(!hash_equals('RESTAURAR '.$id,$frase)||($_POST['confirmar_riesgo']??'')!=='1')throw new DomainException('La doble confirmación no coincide con la copia seleccionada.');if(!reautenticacionAdminValida((string)($_POST['password']??''),false))throw new DomainException('La contraseña actual no es válida.');set_time_limit(0);restaurarRespaldoBaseDatos($id,$actor);cmsFlash('exito','La base de datos fue restaurada y la copia previa quedó registrada.');}
    else throw new DomainException('La acción solicitada no es válida.');
}catch(DomainException $e){cmsFlash('error',$e->getMessage());}catch(Throwable $e){error_log('Acción de respaldos Atenea: '.$e->getMessage());cmsFlash('error','No fue posible completar la operación. Consulta el historial para conocer su estado.');}
header('Location:index.php');
