<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/mailer.php';
$pdo=obtenerConexion();$ok=[];$assert=static function(bool$c,string$m)use(&$ok):void{if(!$c)throw new RuntimeException('FALLO: '.$m);$ok[]=$m;};$tag='c7e12mail:'.bin2hex(random_bytes(8));$original=[];
foreach(['MAIL_TEST_MODE','MAIL_TEST_RECIPIENT','MAIL_MAX_PER_MINUTE','MAIL_MAX_PER_USER_PER_HOUR']as$clave)$original[$clave]=getenv($clave);
try{
  $assert(correoModoPruebaAtenea(),'La entrega se valida con MAIL_TEST_MODE activo');
  $assert(correoDestinatarioPruebaAtenea()==='','No existe una dirección autorizada, por lo que ninguna prueba puede salir por SMTP');
  putenv('MAIL_TEST_MODE=true');putenv('MAIL_TEST_RECIPIENT=etapa12@example.invalid');putenv('MAIL_MAX_PER_MINUTE=3');putenv('MAIL_MAX_PER_USER_PER_HOUR=5');
  $ids=[];for($i=0;$i<3;$i++){$clave=$tag.':global:'.$i;$ids[]=(int)encolarCorreoAtenea('etapa12@example.invalid','Prueba','Control global','<p>Prueba</p>','Prueba',['tipo'=>'prueba_limite','evento_id'=>$clave,'idempotency_key'=>$clave]);}
  $in=implode(',',array_fill(0,count($ids),'?'));$pdo->prepare("UPDATE correo_envios SET estado='enviado',enviado_at=NOW(),cancelado_at=NULL,cancelado_motivo=NULL WHERE id IN($in)")->execute($ids);
  $clave=$tag.':global:pendiente';$global=(int)encolarCorreoAtenea('etapa12@example.invalid','Prueba','Control global pendiente','<p>Prueba</p>','Prueba',['tipo'=>'prueba_limite','evento_id'=>$clave,'idempotency_key'=>$clave,'permitir_envio_prueba'=>true]);$assert(procesarCorreoEnColaAtenea($global)==='limitado','El límite global de 3 correos por minuto aplaza el cuarto intento sin enviarlo');
  $q=$pdo->prepare('SELECT intento,estado FROM correo_envios WHERE id=:id');$q->execute(['id'=>$global]);$fila=$q->fetch();$assert((int)$fila['intento']===0&&$fila['estado']==='pendiente','El intento limitado no abre SMTP ni consume un reintento');

  putenv('MAIL_MAX_PER_MINUTE=1000');$usuario=(int)$pdo->query("SELECT id FROM usuarios WHERE estado='activo' AND deleted_at IS NULL ORDER BY id LIMIT 1")->fetchColumn();$idsUsuario=[];for($i=0;$i<5;$i++){$clave=$tag.':usuario:'.$i;$idsUsuario[]=(int)encolarCorreoAtenea('etapa12@example.invalid','Prueba','Control usuario','<p>Prueba</p>','Prueba',['tipo'=>'prueba_limite','usuario_id'=>$usuario,'evento_id'=>$clave,'idempotency_key'=>$clave]);}
  $in=implode(',',array_fill(0,count($idsUsuario),'?'));$pdo->prepare("UPDATE correo_envios SET estado='enviado',enviado_at=DATE_SUB(NOW(),INTERVAL 5 MINUTE),cancelado_at=NULL,cancelado_motivo=NULL WHERE id IN($in)")->execute($idsUsuario);
  $clave=$tag.':usuario:pendiente';$porUsuario=(int)encolarCorreoAtenea('etapa12@example.invalid','Prueba','Control usuario pendiente','<p>Prueba</p>','Prueba',['tipo'=>'prueba_limite','usuario_id'=>$usuario,'evento_id'=>$clave,'idempotency_key'=>$clave,'permitir_envio_prueba'=>true]);$assert(procesarCorreoEnColaAtenea($porUsuario)==='limitado','El límite de 5 correos por usuario y hora aplaza el sexto intento sin enviarlo');
  $q->execute(['id'=>$porUsuario]);$fila=$q->fetch();$assert((int)$fila['intento']===0&&$fila['estado']==='pendiente','El límite por usuario también actúa antes de SMTP');

  $root=dirname(__DIR__,2);$ui=(string)file_get_contents($root.'/src/dashboard/comunicaciones/correos.php');$accion=(string)file_get_contents($root.'/src/dashboard/comunicaciones/correo-accion.php');$assert(str_contains($ui,'Probar un solo correo')&&str_contains($ui,'Confirmar correo de prueba')&&str_contains($ui,'ENVIAR PRUEBA'),'La interfaz muestra resumen y exige confirmación antes de una prueba real');
  $assert(str_contains($ui,'MAIL_TEST_RECIPIENT')&&!str_contains($accion,'SELECT')&&str_contains($accion,'hash_equals($autorizado,$correo)'),'La prueba admite solo la dirección autorizada y nunca recorre la base de usuarios');
  $assert(str_contains($accion,'registrarAuditoria')&&str_contains($accion,'mail.authorized_test'),'Cada intento administrativo de prueba queda auditado');
  $assert(str_contains((string)file_get_contents($root.'/includes/mailer.php'),"SHOW TABLES LIKE 'correo_envios'")&&stripos($ui,'historial')!==false,'La cola y su historial administrativo permanecen disponibles');

  $roles=$pdo->query("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='usuarios' AND COLUMN_NAME='rol'")->fetchColumn();$assert(str_contains((string)$roles,"'admin'")&&str_contains((string)$roles,"'usuario'")&&str_contains((string)$roles,"'docente'"),'La revisión usa los tres roles persistidos por la arquitectura actual');
  $assert(str_contains((string)file_get_contents($root.'/src/dashboard/includes/cms.php'),'exigirRol'),'El dashboard administrativo exige un rol autorizado');
  $assert(str_contains((string)file_get_contents($root.'/src/docente/_guard.php'),'exigirRol')&&str_contains((string)file_get_contents($root.'/includes/docente_academico.php'),'docente_id'),'El portal docente valida rol y propiedad académica');
  $assert(str_contains((string)file_get_contents($root.'/src/estudiantes/perfil.php'),"exigirRol(['usuario'])")&&str_contains((string)file_get_contents($root.'/includes/portal_estudiante_aula.php'),'usuario_id'),'El aula valida rol y propiedad del estudiante');
  foreach(['024_contacto_chat_moderno.sql','025_autenticacion_sesiones_google.sql','026_perfil_avatar_eliminacion_cuenta.sql','027_copias_seguridad_base_datos.sql','028_personalizacion_visual_portales.sql','029_asignacion_clases_docentes.sql']as$m)$assert(is_file($root.'/src/database/migrations/'.$m),'La migración de entrega existe: '.$m);
  foreach(['MAIL_TEST_MODE','MAIL_TEST_RECIPIENT','MAIL_MAX_PER_MINUTE','MAIL_MAX_PER_USER_PER_HOUR','MAIL_MAX_RETRIES','IMAP_HOST','IMAP_PORT','IMAP_ENCRYPTION','IMAP_USERNAME','IMAP_PASSWORD']as$variable)$assert(preg_match('/^'.preg_quote($variable,'/').'=/m',(string)file_get_contents($root.'/.env.example'))===1,'.env.example documenta '.$variable);
  foreach(['inicio-desktop.png','estudiante-dashboard-desktop.png','dashboard-admin.png','perfil-docente-mobile.png','editor-visual.png','carrito-con-producto.png','contacto-mobile.png','error-404-mobile.png']as$captura)$assert(is_file($root.'/artifacts/etapa12/'.$captura),'Existe evidencia visual: '.$captura);
  echo 'OK '.count($ok)." pruebas\n";foreach($ok as$m)echo '- '.$m."\n";
}finally{
  $pdo->prepare('DELETE FROM correo_envios WHERE idempotency_key LIKE :tag')->execute(['tag'=>$tag.':%']);foreach($original as$clave=>$valor){if($valor===false)putenv($clave);else putenv($clave.'='.$valor);}
}
