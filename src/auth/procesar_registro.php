<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/auth.php'; require_once dirname(__DIR__,2).'/includes/conexion.php';
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){header('Location: '.atenea_url('src/login/sign-up.php'));exit;}
$n=trim((string)($_POST['nombre']??''));$a=trim((string)($_POST['apellido']??''));$c=strtolower(trim((string)($_POST['correo']??'')));$p=(string)($_POST['password']??'');$pc=(string)($_POST['confirmar_password']??'');$e=[];
if(!validarTokenCsrf(isset($_POST['csrf_token'])?(string)$_POST['csrf_token']:null))$e[]='La solicitud expiró. Intenta nuevamente.';
if($n===''||mb_strlen($n)>100)$e[]='Ingresa un nombre válido.';if($a===''||mb_strlen($a)>100)$e[]='Ingresa un apellido válido.';if(strlen($c)>190||!filter_var($c,FILTER_VALIDATE_EMAIL))$e[]='Ingresa un correo electrónico válido.';
if(strlen($p)<8||strlen($p)>255||!preg_match('/[A-Za-z]/',$p)||!preg_match('/\d/',$p))$e[]='La contraseña debe tener al menos 8 caracteres, una letra y un número.';if($p!==$pc)$e[]='Las contraseñas no coinciden.';if(($_POST['terminos']??'')!=='1')$e[]='Debes aceptar los términos y condiciones.';
$_SESSION['registro_datos']=['nombre'=>$n,'apellido'=>$a,'correo'=>$c];
try{if(!$e){$pdo=obtenerConexion();$q=$pdo->prepare('SELECT id FROM usuarios WHERE correo=:correo');$q->execute(['correo'=>$c]);if($q->fetch())$e[]='Ya existe una cuenta con este correo.';else{$q=$pdo->prepare("INSERT INTO usuarios(nombre,apellido,correo,password,rol,estado,proveedor,email_verificado,created_at,updated_at) VALUES(:n,:a,:c,:p,'usuario','activo','local',0,NOW(),NOW())");$q->execute(['n'=>$n,'a'=>$a,'c'=>$c,'p'=>password_hash($p,PASSWORD_DEFAULT)]);iniciarSesionUsuario(['id'=>(int)$pdo->lastInsertId(),'nombre'=>$n,'apellido'=>$a,'correo'=>$c,'rol'=>'usuario','foto'=>null]);redirigirPorRol('usuario');}}}catch(Throwable $x){error_log('Registro Atenea: '.$x->getMessage());$e=['No fue posible crear la cuenta en este momento.'];}
$_SESSION['registro_errores']=$e;header('Location: '.atenea_url('src/login/sign-up.php'));exit;
