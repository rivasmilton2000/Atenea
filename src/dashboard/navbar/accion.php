<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/cms.php';
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf((string)($_POST['csrf_token']??''))){cmsFlash('error','La solicitud venció.');header('Location:index.php');exit;}
$id=cmsId($_POST['id']??0);$accion=(string)($_POST['accion']??'');$pdo=obtenerConexion();
try{
 if(!$id)throw new DomainException('La sección seleccionada no es válida.');
 if($accion==='toggle'){$q=$pdo->prepare('UPDATE menu_sitio SET activo=1-activo WHERE id=:id AND eliminado_at IS NULL');$q->execute(['id'=>$id]);if(!$q->rowCount())throw new DomainException('La sección no existe.');$mensaje='Estado actualizado en el borrador.';}
 elseif($accion==='eliminar'){$ids=[$id];for($i=0;$i<count($ids);$i++){$q=$pdo->prepare('SELECT id FROM menu_sitio WHERE padre_id=:id AND eliminado_at IS NULL');$q->execute(['id'=>$ids[$i]]);foreach($q->fetchAll(PDO::FETCH_COLUMN)as$h)$ids[]=(int)$h;}$marcas=implode(',',array_fill(0,count($ids),'?'));$params=array_merge([$_SESSION['usuario_id']],$ids);$pdo->prepare("UPDATE menu_sitio SET activo=0,eliminado_at=NOW(),eliminado_por=? WHERE id IN($marcas)")->execute($params);$mensaje=count($ids)>1?'La sección y sus submenús se movieron a la papelera.':'La sección se movió a la papelera.';}
 elseif($accion==='restaurar'){$q=$pdo->prepare('SELECT eliminado_at FROM menu_sitio WHERE id=:id AND eliminado_at IS NOT NULL');$q->execute(['id'=>$id]);$fecha=(string)$q->fetchColumn();if($fecha==='')throw new DomainException('La sección no está en la papelera.');$ids=[$id];for($i=0;$i<count($ids);$i++){$q=$pdo->prepare('SELECT id FROM menu_sitio WHERE padre_id=:id AND eliminado_at=:fecha');$q->execute(['id'=>$ids[$i],'fecha'=>$fecha]);foreach($q->fetchAll(PDO::FETCH_COLUMN)as$h)$ids[]=(int)$h;}$marcas=implode(',',array_fill(0,count($ids),'?'));$pdo->prepare("UPDATE menu_sitio SET eliminado_at=NULL,eliminado_por=NULL WHERE id IN($marcas)")->execute($ids);$mensaje=count($ids)>1?'La sección y sus submenús se restauraron como inactivos.':'Sección restaurada como inactiva. Revísala antes de activarla.';}
 elseif($accion==='eliminar_definitivo'){$q=$pdo->prepare('DELETE FROM menu_sitio WHERE id=:id AND eliminado_at IS NOT NULL');$q->execute(['id'=>$id]);if(!$q->rowCount())throw new DomainException('Solo se puede eliminar definitivamente desde la papelera.');$mensaje='Sección eliminada definitivamente.';}
 else throw new DomainException('La acción solicitada no es válida.');
 registrarAuditoria(['actor_user_id'=>(int)$_SESSION['usuario_id'],'event_type'=>'website.navbar.'.$accion,'module'=>'website','entity_type'=>'menu_item','entity_id'=>$id,'action'=>$accion,'result'=>'success','description'=>$mensaje],$pdo);cmsFlash('exito',$mensaje);
}catch(Throwable$e){cmsFlash('error',$e instanceof DomainException?$e->getMessage():'No fue posible completar la acción.');}
header('Location:'.(in_array($accion,['restaurar','eliminar_definitivo'],true)?'papelera.php':'index.php'));exit;
