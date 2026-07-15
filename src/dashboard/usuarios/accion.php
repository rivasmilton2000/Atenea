<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/cms.php';
require_once dirname(__DIR__, 3) . '/includes/admin_users.php';
require_once dirname(__DIR__, 3) . '/includes/mailer.php';

$id = cmsId($_POST['id'] ?? 0);
$accion = (string)($_POST['accion'] ?? '');
$retorno = 'detalle.php?id=' . $id;
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !validarTokenCsrf(isset($_POST['csrf_token'])?(string)$_POST['csrf_token']:null)) {
    registrarAuditoria(['actor_user_id'=>isset($_SESSION['usuario_id'])?(int)$_SESSION['usuario_id']:null,'target_user_id'=>$id?:null,'event_type'=>'user.admin_action_blocked','module'=>'users','entity_type'=>$id?'user':null,'entity_id'=>$id?:null,'action'=>$accion?:'unknown','result'=>'blocked','description'=>'Se bloqueo una accion administrativa por metodo o token CSRF invalido.']);
    cmsFlash('error', 'Solicitud invalida o token CSRF vencido.'); header('Location: index.php'); exit;
}
if (!$id) { cmsFlash('error', 'La cuenta indicada no es valida.'); header('Location: index.php'); exit; }
$pdo = obtenerConexion();
$actorId = (int)($_SESSION['usuario_id'] ?? 0);

try {
    if ($accion === 'revelar_sensible') {
        exigirPermiso('users.view_sensitive');
        $usuario = adminUsuarioPorId($id);
        if (!$usuario) throw new RuntimeException('La cuenta no existe.');
        $_SESSION['sensitive_reveal_user_id'] = $id;
        $_SESSION['sensitive_reveal_until'] = time() + 300;
        registrarAuditoria(['actor_user_id'=>$actorId,'target_user_id'=>$id,'event_type'=>'user.sensitive_data_revealed','module'=>'users','entity_type'=>'user','entity_id'=>$id,'action'=>'reveal','result'=>'success','description'=>'Un administrador revelo temporalmente el DUI en la ficha de cuenta.','metadata'=>['fields'=>['dui'],'duration_seconds'=>300]]);
        cmsFlash('exito', 'El DUI se mostrara durante cinco minutos y el acceso quedo auditado.');
    } elseif ($accion === 'cambiar_rol') {
        exigirPermiso('users.change_role');
        $nuevoRol = (string)($_POST['rol'] ?? '');
        if (!in_array($nuevoRol, rolesAdministrablesAtenea(), true)) throw new RuntimeException('El rol solicitado no esta permitido.');
        if ($id === $actorId) throw new RuntimeException('No puedes cambiar tu propio rol.');
        if (!reautenticacionAdminValida($_POST['admin_password'] ?? null)) throw new RuntimeException('Debes confirmar tu contrasena administrativa.');
        $pdo->beginTransaction();
        $usuario = adminUsuarioPorId($id, true, $pdo);
        if (!$usuario || $usuario['deleted_at']) throw new RuntimeException('La cuenta no esta disponible.');
        $rolAnterior = (string)$usuario['rol'];
        if ($rolAnterior === 'admin' && $nuevoRol !== 'admin' && cantidadAdministradoresActivos($pdo) <= 1) throw new RuntimeException('No se puede degradar al ultimo administrador activo.');
        if ($rolAnterior === $nuevoRol) throw new RuntimeException('La cuenta ya tiene ese rol.');
        $pdo->prepare('UPDATE usuarios SET rol=:rol,session_version=session_version+1,last_activity_at=NOW() WHERE id=:id')->execute(['rol'=>$nuevoRol,'id'=>$id]);
        if (!registrarAuditoria(['actor_user_id'=>$actorId,'target_user_id'=>$id,'event_type'=>'user.role_changed','module'=>'users','entity_type'=>'user','entity_id'=>$id,'action'=>'change_role','result'=>'success','description'=>'Se actualizo el rol de una cuenta.','metadata'=>['previous_role'=>$rolAnterior,'new_role'=>$nuevoRol]], $pdo)) throw new RuntimeException('No fue posible auditar el cambio de rol.');
        $pdo->commit();
        try { enviarPlantillaCorreoAtenea('cambio_rol',(string)$usuario['correo'],trim((string)$usuario['nombre'].' '.(string)$usuario['apellido']),['rol'=>etiquetaRol($nuevoRol)],['usuario_id'=>$id,'idempotency_key'=>'user-role:'.$id.':'.$nuevoRol.':'.date('YmdHi')]); } catch(Throwable $e) { error_log('Correo cambio rol: '.$e->getMessage()); }
        cmsFlash('exito', 'Rol actualizado. Las sesiones anteriores quedaron invalidadas.');
    } elseif ($accion === 'cambiar_estado') {
        exigirPermiso('users.edit');$nuevoEstado=in_array($_POST['estado']??'', ['activo','inactivo'],true)?(string)$_POST['estado']:'';$motivo=mb_substr(trim(strip_tags((string)($_POST['motivo']??''))),0,300);if($nuevoEstado===''||$motivo==='')throw new RuntimeException('Estado y motivo son obligatorios.');if($id===$actorId)throw new RuntimeException('No puedes cambiar el estado de tu propia cuenta.');if(!reautenticacionAdminValida($_POST['admin_password']??null))throw new RuntimeException('Debes confirmar tu contrasena administrativa.');
        $pdo->beginTransaction();$usuario=adminUsuarioPorId($id,true,$pdo);if(!$usuario||$usuario['deleted_at'])throw new RuntimeException('La cuenta no esta disponible.');if($usuario['estado']===$nuevoEstado)throw new RuntimeException('La cuenta ya tiene ese estado.');if($usuario['rol']==='admin'&&$nuevoEstado==='inactivo'&&cantidadAdministradoresActivos($pdo)<=1)throw new RuntimeException('No se puede desactivar al ultimo administrador activo.');$pdo->prepare('UPDATE usuarios SET estado=:estado,session_version=session_version+1 WHERE id=:id')->execute(['estado'=>$nuevoEstado,'id'=>$id]);if(!registrarAuditoria(['actor_user_id'=>$actorId,'target_user_id'=>$id,'event_type'=>$nuevoEstado==='activo'?'user.activated':'user.deactivated','module'=>'users','entity_type'=>'user','entity_id'=>$id,'action'=>'change_status','result'=>'success','description'=>'Un administrador cambio el estado de una cuenta e invalido sus sesiones.','metadata'=>['previous_status'=>$usuario['estado'],'new_status'=>$nuevoEstado,'reason'=>$motivo]],$pdo))throw new RuntimeException('No fue posible auditar el cambio de estado.');$pdo->commit();if($nuevoEstado==='inactivo'){try{enviarPlantillaCorreoAtenea('cuenta_desactivada',(string)$usuario['correo'],trim((string)$usuario['nombre'].' '.(string)$usuario['apellido']),['motivo'=>$motivo],['usuario_id'=>$id,'idempotency_key'=>'account-disabled:'.$id.':'.date('YmdHi')]);}catch(Throwable $e){error_log('Correo desactivacion: '.$e->getMessage());}}cmsFlash('exito','Estado actualizado y sesiones anteriores invalidadas.');
    } elseif ($accion === 'editar') {
        exigirPermiso('users.edit');
        $nombre=mb_substr(trim(strip_tags((string)($_POST['nombre']??''))),0,100);$apellido=mb_substr(trim(strip_tags((string)($_POST['apellido']??''))),0,100);$codigoTelefono=normalizarCodigoTelefono($_POST['codigo_telefono']??null);$telefono=normalizarTelefono($_POST['telefono']??null);$fecha=trim((string)($_POST['fecha_nacimiento']??''));$dui=normalizarDui($_POST['dui']??null);$departamento=cmsId($_POST['departamento_id']??0);$municipio=cmsId($_POST['municipio_id']??0);$distrito=cmsId($_POST['distrito_id']??0);$direccion=mb_substr(trim(strip_tags((string)($_POST['direccion']??''))),0,255);
        if($nombre===''||$apellido==='')throw new RuntimeException('Nombre y apellidos son obligatorios.');if(($codigoTelefono!==''||$telefono!=='')&&!telefonoValido($codigoTelefono,$telefono))throw new RuntimeException('El telefono no es valido.');if($fecha!==''&&!fechaNacimientoValida($fecha))throw new RuntimeException('La fecha de nacimiento no es valida.');if($dui==='')throw new RuntimeException('El DUI no tiene un formato valido.');if(($departamento||$municipio||$distrito)&&!($departamento&&$municipio&&$distrito&&ubicacionValida($pdo,$departamento,$municipio,$distrito)))throw new RuntimeException('La combinacion de departamento, municipio y distrito no es valida.');if($dui){$q=$pdo->prepare('SELECT id FROM usuarios WHERE dui=:dui AND id<>:id LIMIT 1');$q->execute(['dui'=>$dui,'id'=>$id]);if($q->fetch())throw new RuntimeException('Ese DUI ya pertenece a otra cuenta.');}
        $pdo->beginTransaction();$usuario=adminUsuarioPorId($id,true,$pdo);if(!$usuario||$usuario['deleted_at'])throw new RuntimeException('La cuenta no esta disponible.');$duiCambio=(string)($usuario['dui']??'')!==(string)($dui??'');if($duiCambio){$motivo=mb_substr(trim(strip_tags((string)($_POST['motivo_dui']??''))),0,300);if($motivo==='')throw new RuntimeException('El motivo del cambio de DUI es obligatorio.');if(!reautenticacionAdminValida($_POST['admin_password']??null))throw new RuntimeException('Debes confirmar tu contrasena administrativa para cambiar el DUI.');}else $motivo=null;
        $campos=[];foreach(['nombre'=>$nombre,'apellido'=>$apellido,'codigo_telefono'=>$codigoTelefono?:null,'telefono'=>$telefono?:null,'fecha_nacimiento'=>$fecha?:null,'dui'=>$dui,'departamento_id'=>$departamento?:null,'municipio_id'=>$municipio?:null,'distrito_id'=>$distrito?:null,'direccion'=>$direccion?:null] as $campo=>$valor){if((string)($usuario[$campo]??'')!==(string)($valor??''))$campos[]=$campo;}
        $q=$pdo->prepare('UPDATE usuarios SET nombre=:nombre,apellido=:apellido,codigo_telefono=:codigo,telefono=:telefono,fecha_nacimiento=:fecha,dui=:dui,departamento_id=:departamento,municipio_id=:municipio,distrito_id=:distrito,direccion=:direccion,last_activity_at=NOW() WHERE id=:id');$q->execute(['nombre'=>$nombre,'apellido'=>$apellido,'codigo'=>$codigoTelefono?:null,'telefono'=>$telefono?:null,'fecha'=>$fecha?:null,'dui'=>$dui,'departamento'=>$departamento?:null,'municipio'=>$municipio?:null,'distrito'=>$distrito?:null,'direccion'=>$direccion?:null,'id'=>$id]);
        if(!registrarAuditoria(['actor_user_id'=>$actorId,'target_user_id'=>$id,'event_type'=>'user.admin_profile_updated','module'=>'users','entity_type'=>'user','entity_id'=>$id,'action'=>'update','result'=>'success','description'=>'Un administrador actualizo datos permitidos de una cuenta.','metadata'=>['changed_fields'=>$campos,'sensitive_change_reason'=>$duiCambio?$motivo:null]],$pdo))throw new RuntimeException('No fue posible auditar la edicion.');$pdo->commit();cmsFlash('exito','Datos actualizados. El correo principal no fue modificado.');
    } elseif ($accion === 'crear_aviso') {
        exigirPermiso('users.send_notice');
        $tipo = in_array($_POST['tipo'] ?? '', ['correccion','documentacion','seguridad','general'], true)?(string)$_POST['tipo']:'general';
        $prioridad = in_array($_POST['prioridad'] ?? '', ['normal','alta','urgente'], true)?(string)$_POST['prioridad']:'normal';
        $titulo = mb_substr(trim(strip_tags((string)($_POST['titulo']??''))),0,180);
        $mensaje = mb_substr(trim(strip_tags((string)($_POST['mensaje']??''))),0,2000);
        $seccion = mb_substr(trim(strip_tags((string)($_POST['seccion']??''))),0,100) ?: null;
        $fechaLimite = preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)($_POST['fecha_limite']??''))?(string)$_POST['fecha_limite'].' 23:59:59':null;
        if ($titulo==='' || $mensaje==='') throw new RuntimeException('El titulo y el mensaje son obligatorios.');
        $pdo->beginTransaction();
        $usuario=adminUsuarioPorId($id,true,$pdo); if(!$usuario||$usuario['deleted_at']) throw new RuntimeException('La cuenta no esta disponible.');
        $q=$pdo->prepare('INSERT INTO admin_notices(user_id,created_by,type,title,message,target_section,priority,due_at) VALUES(:user,:creator,:type,:title,:message,:section,:priority,:due)');$q->execute(['user'=>$id,'creator'=>$actorId,'type'=>$tipo,'title'=>$titulo,'message'=>$mensaje,'section'=>$seccion,'priority'=>$prioridad,'due'=>$fechaLimite]);$avisoId=(int)$pdo->lastInsertId();
        if(!registrarAuditoria(['actor_user_id'=>$actorId,'target_user_id'=>$id,'event_type'=>'user.admin_notice_created','module'=>'notices','entity_type'=>'admin_notice','entity_id'=>$avisoId,'action'=>'create','result'=>'success','description'=>'Se envio un aviso administrativo a una cuenta.','metadata'=>['type'=>$tipo,'priority'=>$prioridad,'target_section'=>$seccion]],$pdo))throw new RuntimeException('No fue posible auditar el aviso.');
        $pdo->commit();
        try{enviarPlantillaCorreoAtenea('aviso_administrativo',(string)$usuario['correo'],trim((string)$usuario['nombre'].' '.(string)$usuario['apellido']),['asunto'=>$titulo,'resumen'=>'Tienes un nuevo aviso administrativo.','mensaje'=>$mensaje,'enlace'=>atenea_url_absoluta('src/estudiantes/avisos.php'),'texto_boton'=>'Consultar aviso'],['usuario_id'=>$id,'idempotency_key'=>'admin-notice:'.$avisoId]);$pdo->prepare('UPDATE admin_notices SET email_sent_at=NOW() WHERE id=:id')->execute(['id'=>$avisoId]);}catch(Throwable $e){error_log('Correo aviso: '.$e->getMessage());}
        cmsFlash('exito','Aviso creado. El fallo de correo, si ocurre, no duplica el registro.');
    } elseif ($accion === 'iniciar_recuperacion') {
        exigirPermiso('users.start_password_recovery');
        if (!reautenticacionAdminValida($_POST['admin_password'] ?? null)) throw new RuntimeException('Debes confirmar tu contrasena administrativa.');
        $codigo=strtoupper(bin2hex(random_bytes(4)));
        $pdo->beginTransaction();$usuario=adminUsuarioPorId($id,true,$pdo);
        if(!$usuario||$usuario['deleted_at']||$usuario['estado']!=='activo')throw new RuntimeException('Solo puede recuperarse una cuenta activa.');
        $pdo->prepare('UPDATE assisted_password_resets SET cancelled_at=NOW() WHERE user_id=:id AND token_used_at IS NULL AND cancelled_at IS NULL')->execute(['id'=>$id]);
        $q=$pdo->prepare('INSERT INTO assisted_password_resets(user_id,initiated_by,email_hash,code_hash,expires_at) VALUES(:user,:admin,:email,:code,DATE_ADD(NOW(),INTERVAL 10 MINUTE))');$q->execute(['user'=>$id,'admin'=>$actorId,'email'=>hash('sha256',strtolower((string)$usuario['correo'])),'code'=>hash('sha256',$codigo)]);$resetId=(int)$pdo->lastInsertId();
        if(!registrarAuditoria(['actor_user_id'=>$actorId,'target_user_id'=>$id,'event_type'=>'password.assisted_requested','module'=>'security','entity_type'=>'assisted_password_reset','entity_id'=>$resetId,'action'=>'start','result'=>'success','description'=>'Se inicio una recuperacion asistida y se envio el codigo al correo registrado.'],$pdo))throw new RuntimeException('No fue posible auditar la recuperacion.');
        $pdo->commit();
        try{enviarPlantillaCorreoAtenea('recuperacion_asistida_codigo',(string)$usuario['correo'],trim((string)$usuario['nombre'].' '.(string)$usuario['apellido']),['codigo'=>$codigo],['usuario_id'=>$id,'idempotency_key'=>'assisted-code:'.$resetId]);cmsFlash('exito','Codigo enviado al correo registrado. Vence en 10 minutos.');}catch(Throwable $e){error_log('Correo codigo asistido: '.$e->getMessage());cmsFlash('error','La solicitud se creo, pero el correo no pudo enviarse. Inicia una nueva solicitud para reintentar.');}
    } elseif ($accion === 'verificar_recuperacion') {
        exigirPermiso('users.start_password_recovery');
        $codigo=strtoupper(trim((string)($_POST['codigo']??''))); if(!preg_match('/^[A-F0-9]{8}$/',$codigo))throw new RuntimeException('El codigo no tiene el formato esperado.');
        $pdo->beginTransaction();$usuario=adminUsuarioPorId($id,true,$pdo);if(!$usuario)throw new RuntimeException('La cuenta no existe.');
        $q=$pdo->prepare('SELECT * FROM assisted_password_resets WHERE user_id=:id AND cancelled_at IS NULL AND verified_at IS NULL ORDER BY id DESC LIMIT 1 FOR UPDATE');$q->execute(['id'=>$id]);$reset=$q->fetch();
        if(!$reset||strtotime((string)$reset['expires_at'])<time())throw new RuntimeException('El codigo vencio o no existe.');
        if($reset['locked_until']&&strtotime((string)$reset['locked_until'])>time())throw new RuntimeException('La solicitud esta bloqueada temporalmente por exceso de intentos.');
        if(!hash_equals((string)$reset['code_hash'],hash('sha256',$codigo))){$intentos=(int)$reset['attempts']+1;$bloquear=$intentos>=(int)$reset['max_attempts'];$pdo->prepare('UPDATE assisted_password_resets SET attempts=:attempts,locked_until=IF(:lock=1,DATE_ADD(NOW(),INTERVAL 30 MINUTE),locked_until) WHERE id=:id')->execute(['attempts'=>$intentos,'lock'=>$bloquear?1:0,'id'=>$reset['id']]);registrarAuditoria(['actor_user_id'=>$actorId,'target_user_id'=>$id,'event_type'=>'password.assisted_code_failed','module'=>'security','entity_type'=>'assisted_password_reset','entity_id'=>$reset['id'],'action'=>'verify','result'=>'failure','description'=>'Fallo la verificacion de un codigo de recuperacion asistida.','metadata'=>['attempt_number'=>$intentos]],$pdo);$pdo->commit();throw new RuntimeException($bloquear?'Se alcanzo el limite de intentos y la solicitud fue bloqueada.':'Codigo incorrecto.');}
        $token=bin2hex(random_bytes(32));$pdo->prepare('UPDATE assisted_password_resets SET verified_by=:admin,verified_at=NOW(),recovery_token_hash=:token,token_expires_at=DATE_ADD(NOW(),INTERVAL 30 MINUTE) WHERE id=:id')->execute(['admin'=>$actorId,'token'=>hash('sha256',$token),'id'=>$reset['id']]);
        if(!registrarAuditoria(['actor_user_id'=>$actorId,'target_user_id'=>$id,'event_type'=>'password.assisted_verified','module'=>'security','entity_type'=>'assisted_password_reset','entity_id'=>$reset['id'],'action'=>'verify','result'=>'success','description'=>'El codigo fue validado y se emitio un enlace de recuperacion de un solo uso.'],$pdo))throw new RuntimeException('No fue posible auditar la verificacion.');$pdo->commit();
        try{enviarPlantillaCorreoAtenea('recuperacion_asistida_enlace',(string)$usuario['correo'],trim((string)$usuario['nombre'].' '.(string)$usuario['apellido']),['enlace'=>atenea_url_absoluta('src/login/assisted-reset.php?token='.rawurlencode($token))],['usuario_id'=>$id,'idempotency_key'=>'assisted-link:'.$reset['id']]);cmsFlash('exito','Codigo validado. El enlace seguro fue enviado al usuario; el administrador no puede ver la nueva contrasena.');}catch(Throwable $e){error_log('Correo enlace asistido: '.$e->getMessage());cmsFlash('error','El codigo fue validado, pero no pudo enviarse el enlace. Inicia otra solicitud.');}
    } elseif ($accion === 'eliminar_logico') {
        exigirPermiso('users.delete');$motivo=mb_substr(trim(strip_tags((string)($_POST['motivo']??''))),0,500);
        if($motivo==='')throw new RuntimeException('El motivo es obligatorio.');if($id===$actorId)throw new RuntimeException('No puedes eliminar tu propia cuenta.');if(!reautenticacionAdminValida($_POST['admin_password']??null))throw new RuntimeException('Debes confirmar tu contrasena administrativa.');
        $pdo->beginTransaction();$usuario=adminUsuarioPorId($id,true,$pdo);if(!$usuario||$usuario['deleted_at'])throw new RuntimeException('La cuenta no esta disponible.');
        if($usuario['rol']==='admin')throw new RuntimeException('Las cuentas administrativas activas estan protegidas contra eliminacion desde este flujo.');
        if($usuario['rol']==='docente')throw new RuntimeException('No existe aun una fuente confiable de asignaciones docentes; la eliminacion se bloquea de forma conservadora.');
        if((int)$usuario['retention_hold']===1||(int)$usuario['under_investigation']===1)throw new RuntimeException('La cuenta esta marcada para conservacion o bajo investigacion.');
        $q=$pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE usuario_id=:id AND estado IN ('pendiente','esperando_pago','reembolsado')");$q->execute(['id'=>$id]);if((int)$q->fetchColumn()>0)throw new RuntimeException('La cuenta tiene pagos pendientes o un proceso de reembolso que debe conservarse.');
        $q=$pdo->prepare("SELECT COUNT(*) FROM admin_notices WHERE user_id=:id AND status IN ('pendiente','visto')");$q->execute(['id'=>$id]);if((int)$q->fetchColumn()>0)throw new RuntimeException('La cuenta tiene procesos administrativos abiertos.');
        $gracia=30;$fecha=(new DateTimeImmutable('+'.$gracia.' days'))->format('Y-m-d H:i:s');
        $pdo->prepare("UPDATE usuarios SET estado='inactivo',deleted_at=NOW(),deleted_by=:admin,deletion_reason=:reason,deletion_scheduled_at=:date,session_version=session_version+1 WHERE id=:id")->execute(['admin'=>$actorId,'reason'=>$motivo,'date'=>$fecha,'id'=>$id]);
        $q=$pdo->prepare("INSERT INTO user_deletions(user_id,requested_by,reason,effective_at) VALUES(:user,:admin,:reason,:date)");$q->execute(['user'=>$id,'admin'=>$actorId,'reason'=>$motivo,'date'=>$fecha]);$deletionId=(int)$pdo->lastInsertId();
        if(!registrarAuditoria(['actor_user_id'=>$actorId,'target_user_id'=>$id,'event_type'=>'user.deletion_requested','module'=>'users','entity_type'=>'user_deletion','entity_id'=>$deletionId,'action'=>'soft_delete','result'=>'success','description'=>'Se solicito la eliminacion logica de una cuenta y se invalidaron sus sesiones.','metadata'=>['grace_days'=>$gracia]],$pdo))throw new RuntimeException('No fue posible auditar la eliminacion.');$pdo->commit();
        try{enviarPlantillaCorreoAtenea('cuenta_eliminacion_solicitada',(string)$usuario['correo'],trim((string)$usuario['nombre'].' '.(string)$usuario['apellido']),['fecha_limite'=>date('d/m/Y',strtotime($fecha))],['usuario_id'=>$id,'idempotency_key'=>'account-deletion:'.$deletionId]);}catch(Throwable $e){error_log('Correo eliminacion: '.$e->getMessage());}cmsFlash('exito','Cuenta desactivada y en periodo de gracia. No se eliminaron pedidos ni registros historicos.');
    } elseif ($accion === 'restaurar') {
        exigirPermiso('users.delete');if(!reautenticacionAdminValida($_POST['admin_password']??null))throw new RuntimeException('Debes confirmar tu contrasena administrativa.');
        $pdo->beginTransaction();$usuario=adminUsuarioPorId($id,true,$pdo);if(!$usuario||!$usuario['deleted_at'])throw new RuntimeException('La cuenta no tiene una eliminacion pendiente.');if($usuario['anonymized_at'])throw new RuntimeException('Una cuenta anonimizada no puede restaurarse automaticamente.');if(!$usuario['deletion_scheduled_at']||strtotime((string)$usuario['deletion_scheduled_at'])<time())throw new RuntimeException('El periodo de gracia finalizo; la restauracion requiere revision manual.');
        $pdo->prepare("UPDATE usuarios SET estado='activo',deleted_at=NULL,deleted_by=NULL,deletion_reason=NULL,deletion_scheduled_at=NULL,session_version=session_version+1 WHERE id=:id")->execute(['id'=>$id]);
        $q=$pdo->prepare("UPDATE user_deletions SET status='restaurada',restored_by=:admin,restored_at=NOW() WHERE user_id=:id AND status='periodo_gracia'");$q->execute(['admin'=>$actorId,'id'=>$id]);
        if(!registrarAuditoria(['actor_user_id'=>$actorId,'target_user_id'=>$id,'event_type'=>'user.restored','module'=>'users','entity_type'=>'user','entity_id'=>$id,'action'=>'restore','result'=>'success','description'=>'Se restauro una cuenta durante el periodo de gracia.'],$pdo))throw new RuntimeException('No fue posible auditar la restauracion.');$pdo->commit();
        try{enviarPlantillaCorreoAtenea('cuenta_restaurada',(string)$usuario['correo'],trim((string)$usuario['nombre'].' '.(string)$usuario['apellido']),[],['usuario_id'=>$id,'idempotency_key'=>'account-restored:'.$id.':'.date('Ymd')]);}catch(Throwable $e){error_log('Correo restauracion: '.$e->getMessage());}cmsFlash('exito','Cuenta restaurada. Las sesiones anteriores permanecen invalidadas.');
    } else throw new RuntimeException('La accion solicitada no esta permitida.');
} catch(Throwable $error) {
    if($pdo->inTransaction())$pdo->rollBack();
    if(!$error instanceof RuntimeException)error_log('Administracion usuarios: '.$error->getMessage());
    registrarAuditoria(['actor_user_id'=>$actorId,'target_user_id'=>$id,'event_type'=>'user.admin_action_failed','module'=>'users','entity_type'=>'user','entity_id'=>$id,'action'=>$accion?:'unknown','result'=>'failure','description'=>'No fue posible completar una accion administrativa.','metadata'=>['error_class'=>get_class($error)]]);
    cmsFlash('error',$error instanceof RuntimeException?$error->getMessage():'No fue posible completar la accion.');
}
header('Location: '.$retorno);exit;
