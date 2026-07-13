<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/cuenta.php';
exigirAutenticacion();
$retorno=cuentaRetornoSeguro($_POST['retorno']??null);
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf(isset($_POST['csrf_token'])?(string)$_POST['csrf_token']:null)){cuentaFlash(['general'=>'La solicitud expiró.']);header('Location: '.$retorno);exit;}
$perfil=obtenerPerfilUsuario((int)$_SESSION['usuario_id']);
if(!$perfil||empty($perfil['google_id'])){cuentaFlash(['general'=>'La cuenta no está vinculada con Google.']);header('Location: '.$retorno);exit;}
if(empty($perfil['password'])){cuentaFlash(['general'=>'Crea primero una contraseña local para no perder el acceso a tu cuenta.']);header('Location: '.$retorno);exit;}
try{$verificacion=crearVerificacionCuenta(obtenerConexion(),(int)$perfil['id'],'desvincular_google',[],(string)$perfil['correo']);cuentaFlash([],'Enviamos un código para confirmar la desvinculación.',$verificacion);}catch(Throwable$e){error_log('Desvincular Google: '.$e->getMessage());cuentaFlash(['general'=>'No fue posible enviar el código de verificación.']);}
header('Location: '.$retorno);exit;
