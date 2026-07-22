<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/auth.php';
require_once dirname(__DIR__,2).'/includes/permissions.php';

$pdo=obtenerConexion();$ok=[];$id=0;$assert=static function(bool $condicion,string $mensaje)use(&$ok):void{if(!$condicion)throw new RuntimeException('FALLO: '.$mensaje);$ok[]=$mensaje;};
try{
 $tipo=(string)$pdo->query("SHOW COLUMNS FROM usuarios LIKE 'rol'")->fetch()['Type'];$assert(str_contains($tipo,"'administracion_docente'"),'El enum conserva roles existentes e incorpora administracion_docente');
 foreach(['usuario_permisos','usuario_permisos_historial']as$tabla)$assert((bool)$pdo->query("SHOW TABLES LIKE '$tabla'")->fetchColumn(),"Existe la tabla $tabla");
 $actor=(int)$pdo->query("SELECT id FROM usuarios WHERE rol='admin' AND es_superadmin=1 AND estado='activo' AND deleted_at IS NULL ORDER BY id LIMIT 1")->fetchColumn();$assert($actor>0,'Existe un SuperAdmin para autorizar permisos');
 $tag='hibrido_'.bin2hex(random_bytes(5));$q=$pdo->prepare("INSERT INTO usuarios(nombre,apellido,nombre_usuario,correo,password,rol,estado,email_verificado,perfil_estado,terminos_aceptados_at) VALUES('Cuenta','Híbrida',:usuario,:correo,:password,'administracion_docente','activo',1,'completo',NOW())");$q->execute(['usuario'=>$tag,'correo'=>$tag.'@example.invalid','password'=>password_hash('Temporal!2026',PASSWORD_DEFAULT)]);$id=(int)$pdo->lastInsertId();
 $pdo->beginTransaction();guardarPermisosHibridosAtenea($id,['hybrid.admin.access','hybrid.docente.access','users.view','academic.courses.view'],$actor,$pdo);$pdo->commit();
 $assert(permisoHibridoUsuarioAtenea($id,'users.view'),'El permiso individual habilitado se concede');$assert(!permisoHibridoUsuarioAtenea($id,'users.change_role'),'Los permisos críticos no se conceden implícitamente');$assert(!permisoHibridoUsuarioAtenea($id,'backups.restore'),'El rol híbrido no puede restaurar copias');
 $q=$pdo->prepare('SELECT COUNT(*) FROM usuario_permisos_historial WHERE usuario_id=:id AND cambiado_por=:actor');$q->execute(['id'=>$id,'actor'=>$actor]);$historialCambios=(int)$q->fetchColumn();$q=$pdo->prepare('SELECT COUNT(*) FROM usuario_permisos WHERE usuario_id=:id');$q->execute(['id'=>$id]);$assert($historialCambios===(int)$q->fetchColumn()&&$historialCambios>=4,'Cada cambio registra usuario, actor y fecha en historial');
 $_SESSION['usuario_id']=$id;$_SESSION['usuario_rol']='administracion_docente';$_SESSION['hybrid_mode']='admin';$assert(modoHibridoActualAtenea()==='admin','El contexto administrativo se conserva sin cambiar identidad');$assert(str_contains(cambiarModoHibridoAtenea('docente'),'src/docente/index.php'),'El selector cambia al dashboard docente sin cerrar sesión');
 $assert(permisoRutaAdministrativaHibridaAtenea('/Atenea/src/dashboard/usuarios/index.php','GET')==='users.view','La ruta de usuarios exige permiso de lectura');$assert(permisoRutaAdministrativaHibridaAtenea('/Atenea/src/dashboard/usuarios/accion.php','POST')==='users.edit','La acción de usuarios exige gestión');$assert(permisoRutaAdministrativaHibridaAtenea('/Atenea/src/dashboard/configuracion/index.php','GET')===null,'Las configuraciones críticas se deniegan');$assert(permisoRutaDocenteHibridaAtenea('/Atenea/src/docente/calificar.php')==='academic.grades.manage','Calificar exige permiso docente específico');
 $pdo->beginTransaction();guardarPermisosHibridosAtenea($id,['hybrid.admin.access','hybrid.docente.access','academic.courses.view'],$actor,$pdo);$pdo->commit();$assert(!permisoHibridoUsuarioAtenea($id,'users.view'),'La revocación se aplica inmediatamente');
 $bloqueado=false;try{$pdo->beginTransaction();guardarPermisosHibridosAtenea($id,[],$id,$pdo);}catch(DomainException){$bloqueado=true;if($pdo->inTransaction())$pdo->rollBack();}$assert($bloqueado,'La cuenta no puede modificar sus propios permisos');
 $assert(etiquetaRol('administracion_docente')==='Administrador docente','El alias legado conserva el nombre visual canónico');
 echo 'OK '.count($ok)." pruebas\n";foreach($ok as$m)echo '- '.$m."\n";
}finally{
 unset($_SESSION['usuario_id'],$_SESSION['usuario_rol'],$_SESSION['hybrid_mode']);
 if($id){$pdo->prepare('DELETE FROM audit_logs WHERE target_user_id=:id OR actor_user_id=:id2')->execute(['id'=>$id,'id2'=>$id]);$pdo->prepare('DELETE FROM usuarios WHERE id=:id')->execute(['id'=>$id]);}
}
