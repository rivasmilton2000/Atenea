<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/auth.php'; require_once dirname(__DIR__,2).'/includes/conexion.php';
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){header('Location: '.atenea_url('src/login/sign-in.php'));exit;}
$now=time(); $tries=array_values(array_filter(is_array($_SESSION['login_intentos']??null)?$_SESSION['login_intentos']:[],fn($t)=>is_int($t)&&$t>$now-300));
if(count($tries)>=5){$_SESSION['mensaje_auth']='Demasiados intentos. Espera unos minutos antes de volver a intentarlo.';header('Location: '.atenea_url('src/login/sign-in.php'));exit;}
$correo=strtolower(trim((string)($_POST['correo']??''))); $password=(string)($_POST['password']??''); $_SESSION['login_correo']=substr($correo,0,190);
if(!validarTokenCsrf(isset($_POST['csrf_token'])?(string)$_POST['csrf_token']:null)){$_SESSION['mensaje_auth']='La solicitud expiró. Intenta nuevamente.';header('Location: '.atenea_url('src/login/sign-in.php'));exit;}
try{$q=obtenerConexion()->prepare('SELECT id,nombre,apellido,correo,password,rol,foto,estado FROM usuarios WHERE correo=:correo LIMIT 1');$q->execute(['correo'=>$correo]);$u=$q->fetch();
$ok=is_array($u)&&$u['estado']==='activo'&&!empty($u['password'])&&password_verify($password,(string)$u['password']);
if(!$ok){$tries[]=$now;$_SESSION['login_intentos']=$tries;$_SESSION['mensaje_auth']='El correo o la contraseña no son correctos.';header('Location: '.atenea_url('src/login/sign-in.php'));exit;}
iniciarSesionUsuario($u);$q=obtenerConexion()->prepare('UPDATE usuarios SET ultimo_acceso=NOW() WHERE id=:id');$q->execute(['id'=>(int)$u['id']]);redirigirPorRol($u['rol']);
}catch(Throwable $e){error_log('Login Atenea: '.$e->getMessage());$_SESSION['mensaje_auth']='No fue posible iniciar sesión en este momento.';header('Location: '.atenea_url('src/login/sign-in.php'));exit;}
