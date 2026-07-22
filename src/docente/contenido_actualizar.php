<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';
require_once dirname(__DIR__, 2) . '/includes/contenido_clase.php';
require_once dirname(__DIR__, 2) . '/includes/audit.php';

exigirPermiso('academic.content.manage');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !validarTokenCsrf((string) ($_POST['csrf_token'] ?? ''))) { http_response_code(400); exit; }

$pdo=obtenerConexion();$id=docenteId($_POST['id']??0);$seccionId=docenteId($_POST['seccion_id']??0);$nuevoArchivo=null;$archivoAnterior=null;
try {
    $pdo->beginTransaction();
    $q=$pdo->prepare('SELECT * FROM contenidos WHERE id=:id AND eliminado_at IS NULL FOR UPDATE');$q->execute(['id'=>$id]);$contenido=$q->fetch();
    if(!$contenido||!contenidoClasePuedeAdministrar($pdo,$contenido,(int)$_SESSION['usuario_id'],(string)$_SESSION['usuario_rol']))throw new DomainException('No puedes modificar esta publicación.');
    $docenteId=(int)$contenido['docente_id'];
    if(!docentePoseeSeccion($pdo,$docenteId,$seccionId))throw new DomainException('La sección seleccionada no pertenece al docente autor.');
    $q=$pdo->prepare('SELECT asignatura_id FROM capacitacion_secciones WHERE id=:id AND docente_id=:d');$q->execute(['id'=>$seccionId,'d'=>$docenteId]);$asignaturaId=(int)$q->fetchColumn();if(!$asignaturaId)throw new DomainException('La sección no es válida.');
    $modulo=textoPlanoContenido($_POST['modulo']??'',120,true);$titulo=textoPlanoContenido($_POST['titulo']??'',190,true);$descripcion=textoPlanoContenido($_POST['descripcion']??'',10000);
    $tipoRecurso=(string)($_POST['tipo_recurso']??'ninguno');$url=trim((string)($_POST['recurso_url']??''));$tieneArchivo=archivoPresente();$tipoActual=tipoRecursoDeContenido($contenido);$mantieneArchivo=in_array($tipoRecurso,['video_archivo','documento'],true)&&$tipoActual===$tipoRecurso&&!empty($contenido['archivo_relpath']);
    validarEntradaRecursoContenido($tipoRecurso,$url,$tieneArchivo,$mantieneArchivo);
    $fechaPublicacion=fechaHoraContenido($_POST['fecha_publicacion']??null);$estado=($_POST['estado']??'')==='publicado'?'activo':'borrador';
    if($tieneArchivo)$nuevoArchivo=guardarArchivoAcademico('archivo','contenidos',$tipoRecurso==='video_archivo'?'video':'contenido');
    $tipo=match($tipoRecurso){'video_archivo','youtube'=>'video','documento'=>'documento','google_drive','enlace'=>'enlace',default=>'texto'};
    $usaArchivo=in_array($tipoRecurso,['video_archivo','documento'],true);$archivoAnterior=(string)($contenido['archivo_relpath']??'');
    $ruta=$usaArchivo?($nuevoArchivo['relpath']??$contenido['archivo_relpath']):null;$nombre=$usaArchivo?($nuevoArchivo['nombre']??$contenido['archivo_nombre']):null;$mime=$usaArchivo?($nuevoArchivo['mime']??$contenido['archivo_mime']):null;$tamano=$usaArchivo?($nuevoArchivo['tamano']??$contenido['archivo_tamano']):null;
    $q=$pdo->prepare("UPDATE contenidos SET asignatura_id=:a,seccion_id=:s,modulo=:modulo,tipo=:tipo,titulo=:titulo,descripcion=:descripcion,video_url=:url,archivo_relpath=:ruta,archivo_nombre=:nombre,archivo_mime=:mime,archivo_tamano=:tamano,fecha_publicacion=:fecha,publicado_at=CASE WHEN :estado='activo' AND publicado_at IS NULL THEN NOW() ELSE publicado_at END,estado=:estado2,activo=:activo WHERE id=:id");
    $q->execute(['a'=>$asignaturaId,'s'=>$seccionId,'modulo'=>$modulo,'tipo'=>$tipo,'titulo'=>$titulo,'descripcion'=>$descripcion?:null,'url'=>$usaArchivo?null:($url?:null),'ruta'=>$ruta,'nombre'=>$nombre,'mime'=>$mime,'tamano'=>$tamano,'fecha'=>$fechaPublicacion,'estado'=>$estado,'estado2'=>$estado,'activo'=>$estado==='activo'?1:0,'id'=>$id]);
    if($contenido['estado']!=='activo'&&$estado==='activo')notificarPublicacionContenido($pdo,['id'=>$id,'seccion_id'=>$seccionId,'asignatura_id'=>$asignaturaId,'docente_id'=>$docenteId,'titulo'=>$titulo]);
    registrarAuditoria(['actor_user_id'=>(int)$_SESSION['usuario_id'],'target_user_id'=>$docenteId,'event_type'=>'academic.content.updated','module'=>'academic','entity_type'=>'content','entity_id'=>$id,'action'=>'update','result'=>'success','description'=>'Se actualizó una publicación de clase.','metadata'=>['estado'=>$estado,'tipo_recurso'=>$tipoRecurso]],$pdo);
    $pdo->commit();
    if($archivoAnterior&&(!$usaArchivo||$nuevoArchivo)){$anterior=rutaPrivadaAcademica($archivoAnterior);if($anterior)@unlink($anterior);}
    docenteFlash('exito','Publicación actualizada.');
}catch(Throwable$error){if($pdo->inTransaction())$pdo->rollBack();if($nuevoArchivo){$ruta=rutaPrivadaAcademica((string)$nuevoArchivo['relpath']);if($ruta)@unlink($ruta);}error_log('Contenido de clase actualizar: '.$error->getMessage());docenteFlash('error',$error instanceof DomainException?$error->getMessage():'No fue posible actualizar la publicación.');}
header('Location:'.docenteUrl('contenidos.php',['seccion'=>$seccionId]));
