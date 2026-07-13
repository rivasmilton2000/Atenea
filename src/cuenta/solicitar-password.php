<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/cuenta.php';exigirAutenticacion();
$retorno=cuentaRetornoSeguro($_POST['retorno']??null);
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf(isset($_POST['csrf_token'])?(string)$_POST['csrf_token']:null)){cuentaFlash(['password'=>'La solicitud expiró.']);header('Location: '.$retorno);exit;}
$actual=(string)($_POST['password_actual']??'');$nueva=(string)($_POST['password_nueva']??'');$confirmar=(string)($_POST['password_confirmar']??'');$errores=[];
$perfil=obtenerPerfilUsuario((int)$_SESSION['usuario_id']);if(!$perfil){header('Location: '.atenea_url('src/login/logout.php'));exit;}
if(!empty($perfil['password'])&&!password_verify($actual,(string)$perfil['password']))$errores['password_actual']='La contraseña actual no es correcta.';
if(strlen($nueva)<8||strlen($nueva)>255||!preg_match('/[A-Za-z]/',$nueva)||!preg_match('/\d/',$nueva))$errores['password_nueva']='Usa al menos 8 caracteres, una letra y un número.';
if($nueva!==$confirmar)$errores['password_confirmar']='Las contraseñas no coinciden.';
if($errores){cuentaFlash($errores);header('Location: '.$retorno);exit;}
try{$pdo=obtenerConexion();$verificacion=crearVerificacionCuenta($pdo,(int)$perfil['id'],'cambio_password',['password_hash'=>password_hash($nueva,PASSWORD_DEFAULT)],(string)$perfil['correo']);cuentaFlash([],'Enviamos un código a tu correo.',$verificacion);}catch(Throwable$e){error_log('Solicitar cambio password: '.$e->getMessage());cuentaFlash(['password'=>'No fue posible enviar el código. Verifica la configuración de correo o intenta más tarde.']);}
header('Location: '.$retorno);exit;
