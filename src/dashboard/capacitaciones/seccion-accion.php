<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/includes/cms.php';
require_once dirname(__DIR__,3).'/includes/capacitaciones.php';
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf((string)($_POST['csrf_token']??''))){http_response_code(400);exit;}
$accion=(string)($_POST['accion']??'');$capId=cmsId($_POST['capacitacion_id']??0);$seccionId=cmsId($_POST['seccion_id']??0);$pdo=obtenerConexion();
try{
 $pdo->beginTransaction();$q=$pdo->prepare('SELECT id FROM asignaturas WHERE id=:id FOR UPDATE');$q->execute(['id'=>$capId]);if(!$q->fetchColumn())throw new DomainException('La capacitación no existe.');
 if($accion==='crear'){
  $docente=cmsId($_POST['docente_id']??0);$codigo=strtoupper(trim((string)($_POST['codigo']??'')));$nombre=trim((string)($_POST['nombre']??''));$capacidad=(int)($_POST['capacidad']??30);$inicio=trim((string)($_POST['fecha_inicio']??''))?:null;$fin=trim((string)($_POST['fecha_finalizacion']??''))?:null;$horario=trim((string)($_POST['horario']??''))?:null;
  if($codigo===''||$nombre===''||$capacidad<1||$capacidad>30||($inicio&&$fin&&$fin<$inicio))throw new DomainException('Datos de sección inválidos; el cupo máximo es 30.');
  $q=$pdo->prepare("SELECT 1 FROM docentes_asignaturas WHERE docente_id=:d AND asignatura_id=:a AND estado='activo'");$q->execute(['d'=>$docente,'a'=>$capId]);if(!$q->fetchColumn()||!docentePuedeAsumirCapacitacion($pdo,$docente,$capId))throw new DomainException('El docente no está autorizado o ya alcanzó su límite.');
  $pdo->prepare('INSERT INTO capacitacion_secciones(asignatura_id,docente_id,codigo,nombre,fecha_inicio,fecha_finalizacion,capacidad_maxima,horario,creada_por) VALUES(:a,:d,:codigo,:nombre,:inicio,:fin,:capacidad,:horario,:admin)')->execute(['a'=>$capId,'d'=>$docente,'codigo'=>$codigo,'nombre'=>$nombre,'inicio'=>$inicio,'fin'=>$fin,'capacidad'=>$capacidad,'horario'=>$horario,'admin'=>$_SESSION['usuario_id']]);$seccionId=(int)$pdo->lastInsertId();$mensaje='Sección creada.';
 }elseif(in_array($accion,['cerrar','abrir'],true)){
  $q=$pdo->prepare('SELECT * FROM capacitacion_secciones WHERE id=:id AND asignatura_id=:a FOR UPDATE');$q->execute(['id'=>$seccionId,'a'=>$capId]);if(!$q->fetch())throw new DomainException('La sección no existe.');$pdo->prepare('UPDATE capacitacion_secciones SET estado=:estado WHERE id=:id')->execute(['estado'=>$accion==='cerrar'?'cerrada':'abierta','id'=>$seccionId]);$mensaje='Estado de sección actualizado.';
 }elseif($accion==='docente'){
  $docente=cmsId($_POST['docente_id']??0);$q=$pdo->prepare('SELECT * FROM capacitacion_secciones WHERE id=:id AND asignatura_id=:a FOR UPDATE');$q->execute(['id'=>$seccionId,'a'=>$capId]);$seccion=$q->fetch();if(!$seccion)throw new DomainException('La sección no existe.');
  $q=$pdo->prepare("SELECT 1 FROM docentes_asignaturas WHERE docente_id=:d AND asignatura_id=:a AND estado='activo'");$q->execute(['d'=>$docente,'a'=>$capId]);if(!$q->fetchColumn()||!docentePuedeAsumirCapacitacion($pdo,$docente,$capId))throw new DomainException('El docente no es elegible.');
  if((int)$seccion['docente_id']!==$docente){
   $pdo->prepare("INSERT INTO inscripcion_movimientos(inscripcion_id,seccion_origen_id,seccion_destino_id,docente_origen_id,docente_destino_id,motivo,realizado_por) SELECT id,seccion_id,seccion_id,docente_id,:nuevo,'Cambio administrativo de docente de la sección',:admin FROM inscripciones_capacitacion WHERE seccion_id=:seccion")->execute(['nuevo'=>$docente,'admin'=>$_SESSION['usuario_id'],'seccion'=>$seccionId]);
   $pdo->prepare('UPDATE capacitacion_secciones SET docente_id=:d WHERE id=:id')->execute(['d'=>$docente,'id'=>$seccionId]);$pdo->prepare('UPDATE inscripciones_capacitacion SET docente_id=:d,asignado_por=:admin,assigned_at=NOW() WHERE seccion_id=:s')->execute(['d'=>$docente,'admin'=>$_SESSION['usuario_id'],'s'=>$seccionId]);
   $pdo->prepare("UPDATE estudiantes_docentes SET estado='retirado' WHERE asignatura_id=:a AND docente_id=:d AND estudiante_id IN(SELECT usuario_id FROM inscripciones_capacitacion WHERE seccion_id=:s)")->execute(['a'=>$capId,'d'=>$seccion['docente_id'],'s'=>$seccionId]);
   $pdo->prepare("INSERT INTO estudiantes_docentes(estudiante_id,docente_id,asignatura_id,estado,matriculado_por) SELECT usuario_id,:d,asignatura_id,'activo',:admin FROM inscripciones_capacitacion WHERE seccion_id=:s ON DUPLICATE KEY UPDATE estado='activo',matriculado_por=VALUES(matriculado_por)")->execute(['d'=>$docente,'admin'=>$_SESSION['usuario_id'],'s'=>$seccionId]);
  }$mensaje='Docente actualizado con historial; progreso y notas se conservaron.';
 }else throw new DomainException('Acción inválida.');
 registrarAuditoria(['actor_user_id'=>$_SESSION['usuario_id'],'event_type'=>'section.'.$accion,'module'=>'academic','entity_type'=>'training_section','entity_id'=>$seccionId?:null,'action'=>$accion,'result'=>'success','description'=>$mensaje],$pdo);$pdo->commit();cmsFlash('exito',$mensaje);
}catch(Throwable$e){if($pdo->inTransaction())$pdo->rollBack();cmsFlash('error',$e instanceof DomainException?$e->getMessage():'No fue posible actualizar la sección.');}
header('Location:secciones.php?capacitacion_id='.$capId);
