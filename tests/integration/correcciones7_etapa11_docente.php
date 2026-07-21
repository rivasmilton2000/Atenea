<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/academico_flujo.php';
$pdo=obtenerConexion();$ok=[];$ids=['usuarios'=>[],'asignaturas'=>[],'secciones'=>[]];
$assert=static function(bool $condicion,string $mensaje)use(&$ok):void{if(!$condicion)throw new RuntimeException('FALLO: '.$mensaje);$ok[]=$mensaje;};
$tag='c7e11_'.bin2hex(random_bytes(4));
try{
  $admin=(int)$pdo->query("SELECT id FROM usuarios WHERE rol='admin' AND estado='activo' AND deleted_at IS NULL ORDER BY id LIMIT 1")->fetchColumn();
  foreach([1,2]as$n){$q=$pdo->prepare("INSERT INTO usuarios(nombre,apellido,nombre_usuario,correo,password,rol,estado,email_verificado,perfil_estado,terminos_aceptados_at) VALUES('Docente',:apellido,:usuario,:correo,:password,'docente','activo',1,'completo',NOW())");$usuario=$tag.'_'.$n;$q->execute(['apellido'=>'Prueba '.$n,'usuario'=>$usuario,'correo'=>$usuario.'@example.invalid','password'=>password_hash('Temporal123!',PASSWORD_DEFAULT)]);$ids['usuarios'][]=(int)$pdo->lastInsertId();}
  foreach([1,2]as$n){$q=$pdo->prepare("INSERT INTO asignaturas(codigo,nombre,slug,descripcion,tipo,precio,estado_capacitacion,cupo_seccion,modalidad,activo,estado) VALUES(:codigo,:nombre,:slug,'Aislamiento docente etapa 11','capacitacion',0,'publicada',20,'virtual',1,'activo')");$q->execute(['codigo'=>strtoupper($tag.'_'.$n),'nombre'=>'Clase docente '.$n,'slug'=>$tag.'-'.$n]);$asignatura=(int)$pdo->lastInsertId();$ids['asignaturas'][]=$asignatura;$pdo->prepare("INSERT INTO docentes_asignaturas(docente_id,asignatura_id,estado,asignado_por) VALUES(:d,:a,'activo',:admin)")->execute(['d'=>$ids['usuarios'][$n-1],'a'=>$asignatura,'admin'=>$admin?:null]);$q=$pdo->prepare("INSERT INTO capacitacion_secciones(asignatura_id,docente_id,codigo,nombre,capacidad_maxima,estado,creada_por) VALUES(:a,:d,:codigo,:nombre,20,'abierta',:admin)");$q->execute(['a'=>$asignatura,'d'=>$ids['usuarios'][$n-1],'codigo'=>'SEC-'.strtoupper($tag.'-'.$n),'nombre'=>'Sección '.$n,'admin'=>$admin?:null]);$ids['secciones'][]=(int)$pdo->lastInsertId();}
  [$docente1,$docente2]=$ids['usuarios'];[$curso1,$curso2]=$ids['asignaturas'];[$seccion1,$seccion2]=$ids['secciones'];
  $assert(docentePuedeCurso($pdo,$docente1,$curso1),'El docente consulta su curso asignado');
  $assert(!docentePuedeCurso($pdo,$docente1,$curso2),'El docente no consulta el curso de otro docente');
  $assert(docentePoseeSeccion($pdo,$docente1,$seccion1),'El docente consulta su sección asignada');
  $assert(!docentePoseeSeccion($pdo,$docente1,$seccion2),'El docente no consulta la sección de otro docente');
  $root=dirname(__DIR__,2);$sidebar=(string)file_get_contents($root.'/src/docente/partials/_sidebar.php');
  foreach(['index.php','cursos.php','estudiantes.php','contenidos.php','tareas.php','evaluaciones.php','calificaciones.php','src/comunicaciones/chat.php','calendario.php','src/notificaciones/index.php','perfil.php']as$r){$assert(str_contains($sidebar,$r)&&($r[0]==='s'||is_file($root.'/src/docente/'.$r)),'La navegación docente incluye '.$r);}
  $assert(str_contains($sidebar,'aria-current="page"'),'La navegación identifica la sección activa para accesibilidad');
  $css=(string)file_get_contents($root.'/src/website/assets/css/perfil-modal.css');$assert(str_contains($css,'object-fit: cover !important')&&str_contains($css,'max-height: calc(100dvh - 2rem)'),'El modal limita la fotografía y su altura a la pantalla');
  $assert(str_contains($css,'.cuenta-modal-backdrop')&&str_contains($css,'overflow-y: auto'),'El modal dispone de overlay y desplazamiento interno controlado');
  $assert(str_contains($css,'@media (max-width: 767.98px)')&&str_contains($css,'height: 96px !important'),'El avatar y el modal se adaptan a móviles');
  $modal=(string)file_get_contents($root.'/includes/perfil_modal.php');$assert(str_contains($modal,'cuenta-modal-cerrar')&&str_contains($modal,'data-avatar-canvas'),'El componente compartido incorpora cierre y vista previa de recorte');
  foreach(['tareas.php','calificaciones.php','calendario.php']as$f){$contenido=(string)file_get_contents($root.'/src/docente/'.$f);$assert(str_contains($contenido,'docente_id')||str_contains($contenido,'docenteId'),'El módulo '.$f.' filtra la información por docente');}
  $entregas=(string)file_get_contents($root.'/src/docente/entregas.php');$assert(str_contains($entregas,'exigirSeccionDocente')&&str_contains($entregas,'exigirContenidoDocente'),'Los filtros directos de entregas rechazan recursos ajenos');
  echo 'OK '.count($ok)." pruebas\n";foreach($ok as$m)echo '- '.$m."\n";
}finally{
  try{foreach($ids['secciones']as$id)$pdo->prepare('DELETE FROM capacitacion_secciones WHERE id=?')->execute([$id]);foreach($ids['asignaturas']as$id){$pdo->prepare('DELETE FROM docentes_asignaturas WHERE asignatura_id=?')->execute([$id]);$pdo->prepare('DELETE FROM asignaturas WHERE id=?')->execute([$id]);}foreach($ids['usuarios']as$id)$pdo->prepare('DELETE FROM usuarios WHERE id=?')->execute([$id]);}catch(Throwable$e){fwrite(STDERR,'Limpieza etapa 11: '.$e->getMessage()."\n");}
}
