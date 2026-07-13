<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/cuenta.php';exigirAutenticacion();$retorno=cuentaRetornoSeguro($_POST['retorno']??null);
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf(isset($_POST['csrf_token'])?(string)$_POST['csrf_token']:null)){cuentaFlash(['correo'=>'La solicitud expiró.']);header('Location: '.$retorno);exit;}
$nuevo=strtolower(trim((string)($_POST['correo_nuevo']??'')));$perfil=obtenerPerfilUsuario((int)$_SESSION['usuario_id']);
if(!$perfil||!filter_var($nuevo,FILTER_VALIDATE_EMAIL)||strlen($nuevo)>190||$nuevo===$perfil['correo']){cuentaFlash(['correo'=>'Ingresa un correo nuevo válido.']);header('Location: '.$retorno);exit;}
try{$pdo=obtenerConexion();$q=$pdo->prepare('SELECT id FROM usuarios WHERE correo=:correo AND id<>:id');$q->execute(['correo'=>$nuevo,'id'=>$perfil['id']]);if($q->fetch())throw new RuntimeException('duplicado');$verificacion=crearVerificacionCuenta($pdo,(int)$perfil['id'],'cambio_correo',['correo_nuevo'=>$nuevo,'correo_anterior'=>$perfil['correo']],$nuevo);notificarCambioCuenta($perfil,['solicitud de cambio de correo']);cuentaFlash([],'Enviamos un código al correo nuevo.',$verificacion);}catch(Throwable$e){error_log('Solicitar cambio correo: '.$e->getMessage());cuentaFlash(['correo'=>'No fue posible iniciar el cambio. El correo puede estar registrado o el servicio no está disponible.']);}
header('Location: '.$retorno);exit;
