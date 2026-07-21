<?php
declare(strict_types=1);

require_once __DIR__.'/academico_flujo.php';

function aulaInscripcionesEstudiante(PDO $pdo,int $usuarioId): array
{
    $q=$pdo->prepare("SELECT i.id inscripcion_id,i.asignatura_id,i.seccion_id,i.estado,a.codigo,a.nombre,a.descripcion,a.duracion,a.modalidad,a.fecha_inicio curso_inicio,a.fecha_finalizacion curso_fin,s.codigo seccion,s.nombre seccion_nombre,s.fecha_inicio,s.fecha_finalizacion,s.horario,s.estado seccion_estado,CONCAT_WS(' ',d.nombre,d.apellido) docente,d.foto docente_foto FROM inscripciones_capacitacion i JOIN asignaturas a ON a.id=i.asignatura_id LEFT JOIN capacitacion_secciones s ON s.id=i.seccion_id LEFT JOIN usuarios d ON d.id=i.docente_id WHERE i.usuario_id=:u AND i.estado IN('pendiente_asignacion','inscrito','finalizado') ORDER BY FIELD(i.estado,'inscrito','pendiente_asignacion','finalizado'),COALESCE(s.fecha_inicio,a.fecha_inicio),a.nombre");
    $q->execute(['u'=>$usuarioId]);return$q->fetchAll();
}

function aulaContenidosEstudiante(PDO $pdo,int $usuarioId,array $tipos=[],int $limite=100): array
{
    $limite=max(1,min(250,$limite));$params=['u_entrega'=>$usuarioId,'u_nota'=>$usuarioId,'u_inscripcion'=>$usuarioId];$tipoSql='';
    if($tipos){$validos=array_values(array_intersect($tipos,['video','texto','documento','enlace','actividad','evaluacion','recurso_descargable']));if($validos){$marcas=[];foreach($validos as$k=>$tipo){$clave='tipo'.$k;$marcas[]=':'.$clave;$params[$clave]=$tipo;}$tipoSql=' AND c.tipo IN('.implode(',',$marcas).')';}}
    $sql="SELECT c.id,c.asignatura_id,c.seccion_id,c.modulo,c.tipo,c.titulo,c.descripcion,c.fecha_publicacion,c.fecha_limite,c.obligatorio,c.video_url,c.archivo_relpath,a.nombre capacitacion,a.codigo,s.codigo seccion,i.id inscripcion_id,(SELECT e.estado FROM entregas_contenido e WHERE e.contenido_id=c.id AND e.estudiante_id=:u_entrega ORDER BY e.intento DESC LIMIT 1) entrega_estado,(SELECT e.nota FROM entregas_contenido e WHERE e.contenido_id=c.id AND e.estudiante_id=:u_nota ORDER BY e.intento DESC LIMIT 1) nota,p.visto_at,p.completado_at FROM inscripciones_capacitacion i JOIN contenidos c ON c.asignatura_id=i.asignatura_id AND c.seccion_id=i.seccion_id JOIN asignaturas a ON a.id=c.asignatura_id LEFT JOIN capacitacion_secciones s ON s.id=c.seccion_id LEFT JOIN progreso_contenido p ON p.inscripcion_id=i.id AND p.contenido_id=c.id WHERE i.usuario_id=:u_inscripcion AND i.estado IN('inscrito','finalizado') AND c.estado='activo' AND c.activo=1 AND (c.fecha_publicacion IS NULL OR c.fecha_publicacion<=NOW())".$tipoSql." ORDER BY COALESCE(c.fecha_limite,'9999-12-31'),a.nombre,c.modulo,c.orden,c.id LIMIT ".$limite;
    $q=$pdo->prepare($sql);$q->execute($params);return$q->fetchAll();
}

function aulaFechasEstudiante(PDO $pdo,int $usuarioId,int $limite=30): array
{
    $limite=max(1,min(100,$limite));$q=$pdo->prepare("SELECT * FROM (SELECT c.id referencia_id,'contenido' origen,c.titulo,CONCAT(a.nombre,' · ',c.modulo) descripcion,c.fecha_limite fecha,CASE c.tipo WHEN 'actividad' THEN 'tarea' WHEN 'evaluacion' THEN 'evaluacion' ELSE 'contenido' END tipo FROM inscripciones_capacitacion i JOIN contenidos c ON c.seccion_id=i.seccion_id AND c.asignatura_id=i.asignatura_id JOIN asignaturas a ON a.id=c.asignatura_id WHERE i.usuario_id=:u1 AND i.estado IN('inscrito','finalizado') AND c.estado='activo' AND c.activo=1 AND c.fecha_limite IS NOT NULL UNION ALL SELECT s.id,'seccion',CONCAT('Inicio de ',a.nombre),CONCAT('Sección ',s.codigo,IF(s.horario IS NULL,'',CONCAT(' · ',s.horario))),CAST(s.fecha_inicio AS DATETIME),'clase' FROM inscripciones_capacitacion i JOIN capacitacion_secciones s ON s.id=i.seccion_id JOIN asignaturas a ON a.id=i.asignatura_id WHERE i.usuario_id=:u2 AND i.estado='inscrito' AND s.fecha_inicio IS NOT NULL UNION ALL SELECT e.id,'evaluacion',e.titulo,a.nombre,e.fecha_cierre,'evaluacion' FROM evaluaciones e JOIN asignaturas a ON a.id=e.asignatura_id WHERE e.estado='publicada' AND e.fecha_cierre IS NOT NULL AND EXISTS(SELECT 1 FROM inscripciones_capacitacion i WHERE i.usuario_id=:u3 AND i.asignatura_id=e.asignatura_id AND i.estado IN('inscrito','finalizado'))) fechas WHERE fecha>=DATE_SUB(NOW(),INTERVAL 1 DAY) ORDER BY fecha LIMIT ".$limite);
    $q->execute(['u1'=>$usuarioId,'u2'=>$usuarioId,'u3'=>$usuarioId]);return$q->fetchAll();
}

function aulaResumenEstudiante(PDO $pdo,int $usuarioId): array
{
    $inscripciones=aulaInscripcionesEstudiante($pdo,$usuarioId);$progresos=[];$suma=0.0;
    foreach($inscripciones as $i){if(empty($i['inscripcion_id'])||$i['estado']==='pendiente_asignacion')continue;try{$p=progresoInscripcion($pdo,(int)$i['inscripcion_id']);$progresos[(int)$i['inscripcion_id']]=$p;$suma+=(float)$p['porcentaje'];}catch(Throwable){}}
    $tareas=aulaContenidosEstudiante($pdo,$usuarioId,['actividad','evaluacion'],8);$tareas=array_values(array_filter($tareas,static fn(array $t):bool=>!in_array($t['entrega_estado']??null,['enviada','en_revision','aprobada'],true)));
    $contenidos=aulaContenidosEstudiante($pdo,$usuarioId,[],6);$fechas=aulaFechasEstudiante($pdo,$usuarioId,8);
    $q=$pdo->prepare("SELECT id,title,message,action_url,category,level,status,created_at FROM admin_notices WHERE user_id=:u AND status IN('pendiente','visto') ORDER BY created_at DESC,id DESC LIMIT 6");$q->execute(['u'=>$usuarioId]);$notificaciones=$q->fetchAll();
    $proxima=null;foreach($inscripciones as$i){if($i['estado']==='inscrito'&&!empty($i['seccion_id'])){$proxima=$i;break;}}
    return ['inscripciones'=>$inscripciones,'progresos'=>$progresos,'progreso_promedio'=>$progresos?round($suma/count($progresos),1):0,'proxima_clase'=>$proxima,'tareas'=>$tareas,'contenidos'=>$contenidos,'fechas'=>$fechas,'notificaciones'=>$notificaciones];
}

function aulaEstadoContenido(array $contenido): array
{
    if(($contenido['entrega_estado']??'')==='aprobada'||!empty($contenido['completado_at']))return['Completado','success'];
    if(in_array($contenido['entrega_estado']??'', ['enviada','en_revision'],true))return['En revisión','info'];
    if(in_array($contenido['entrega_estado']??'', ['rechazada','requiere_correccion'],true))return['Requiere corrección','danger'];
    if(!empty($contenido['fecha_limite'])&&strtotime((string)$contenido['fecha_limite'])<time())return['Vencido','danger'];
    if(!empty($contenido['visto_at']))return['En progreso','warning'];
    return['Pendiente','secondary'];
}

function renderizarListadoContenidosAula(array $contenidos,string $vacio): void
{
    echo '<div class="row g-3">';
    foreach($contenidos as$c){$estado=aulaEstadoContenido($c);$url=atenea_url('src/estudiantes/contenido.php?id='.(int)$c['id']);echo '<div class="col-md-6 col-xl-4"><a class="aula-list-item h-100" href="'.atenea_e($url).'"><div class="d-flex justify-content-between gap-2 mb-2"><span class="badge bg-light text-dark">'.atenea_e(ucfirst((string)$c['tipo'])).'</span><span class="badge bg-'.$estado[1].'">'.$estado[0].'</span></div><h2 class="h6 mb-1">'.atenea_e((string)$c['titulo']).'</h2><p class="small text-muted mb-2">'.atenea_e((string)$c['capacitacion'].' · '.(string)$c['modulo']).'</p>'.(!empty($c['fecha_limite'])?'<small><i class="bi bi-clock"></i> '.date('d/m/Y h:i A',strtotime((string)$c['fecha_limite'])).'</small>':'').'</a></div>';}
    if(!$contenidos)echo '<div class="col-12"><div class="card"><div class="aula-empty"><i class="bi bi-journal-x"></i><p class="mt-3 mb-0">'.atenea_e($vacio).'</p></div></div></div>';
    echo '</div>';
}
