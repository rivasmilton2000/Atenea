<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/auth.php';
require_once dirname(__DIR__,2).'/includes/admin_users.php';
require_once dirname(__DIR__,2).'/includes/mailer.php';

if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){header('Location: '.atenea_url('src/login/forgot-password.php'));exit;}
$token=(string)($_POST['token']??'');$password=(string)($_POST['password']??'');$confirmacion=(string)($_POST['confirmar_password']??'');$errores=[];
if(!validarTokenCsrf(isset($_POST['csrf_token'])?(string)$_POST['csrf_token']:null))$errores[]='La solicitud expiro. Intenta nuevamente.';
if(!preg_match('/^[a-f0-9]{64}$/',$token))$errores[]='El enlace no es valido.';
if(($error=validarPasswordRobustaAtenea($password))!==null)$errores[]=$error;
if($password!==$confirmacion)$errores[]='Las contrasenas no coinciden.';
if($errores){$_SESSION['assisted_reset_errors']=$errores;$_SESSION['assisted_token_return']=$token;header('Location: '.atenea_url('src/login/assisted-reset.php'));exit;}
$usuario=null;$pdo=obtenerConexion();
try{$pdo->beginTransaction();$q=$pdo->prepare("SELECT r.id reset_id,r.user_id,u.nombre,u.apellido,u.correo FROM assisted_password_resets r JOIN usuarios u ON u.id=r.user_id WHERE r.recovery_token_hash=:hash AND r.verified_at IS NOT NULL AND r.token_used_at IS NULL AND r.cancelled_at IS NULL AND r.token_expires_at>NOW() AND u.estado='activo' AND u.deleted_at IS NULL LIMIT 1 FOR UPDATE");$q->execute(['hash'=>hash('sha256',$token)]);$usuario=$q->fetch();if(!$usuario)throw new RuntimeException('Enlace vencido, modificado o utilizado.');
$pdo->prepare("UPDATE usuarios SET password=:password,proveedor=IF(google_id IS NULL,'local','mixto'),session_version=session_version+1,last_activity_at=NOW() WHERE id=:id")->execute(['password'=>password_hash($password,PASSWORD_DEFAULT),'id'=>$usuario['user_id']]);
$pdo->prepare('UPDATE assisted_password_resets SET token_used_at=NOW() WHERE id=:id')->execute(['id'=>$usuario['reset_id']]);$pdo->prepare('UPDATE assisted_password_resets SET cancelled_at=COALESCE(cancelled_at,NOW()) WHERE user_id=:id AND id<>:reset AND token_used_at IS NULL')->execute(['id'=>$usuario['user_id'],'reset'=>$usuario['reset_id']]);$pdo->prepare('UPDATE password_reset_tokens SET used_at=COALESCE(used_at,NOW()) WHERE user_id=:id')->execute(['id'=>$usuario['user_id']]);
if(!registrarAuditoria(['target_user_id'=>(int)$usuario['user_id'],'event_type'=>'password.assisted_completed','module'=>'security','entity_type'=>'assisted_password_reset','entity_id'=>$usuario['reset_id'],'action'=>'reset_password','result'=>'success','description'=>'El usuario establecio una nueva contrasena mediante recuperacion asistida y se invalidaron las sesiones anteriores.'],$pdo))throw new RuntimeException('No fue posible auditar el cambio.');$pdo->commit();
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Restablecer asistido: '.$e->getMessage());$_SESSION['assisted_reset_errors']=['El enlace no es valido, ya fue utilizado o expiro.'];$_SESSION['assisted_token_return']=$token;header('Location: '.atenea_url('src/login/assisted-reset.php'));exit;}
try{enviarPlantillaCorreoAtenea('cambio_password',(string)$usuario['correo'],trim((string)$usuario['nombre'].' '.(string)$usuario['apellido']),[],['usuario_id'=>(int)$usuario['user_id'],'idempotency_key'=>'assisted-password-complete:'.$usuario['reset_id']]);}catch(Throwable $e){error_log('Correo cambio asistido: '.$e->getMessage());}
$_SESSION['mensaje_auth']='Tu contrasena se actualizo correctamente. Todas las sesiones anteriores fueron invalidadas.';$_SESSION['mensaje_auth_tipo']='success';header('Location: '.atenea_url('src/login/sign-in.php'));exit;
