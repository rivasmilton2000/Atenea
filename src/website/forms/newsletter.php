<?php
declare(strict_types=1);
require_once dirname(__DIR__,3).'/includes/config.php';
require_once dirname(__DIR__,3).'/includes/session.php';
require_once dirname(__DIR__,3).'/includes/newsletter.php';

const BOLETIN_MAX_INTENTOS=5;
const BOLETIN_VENTANA_SEGUNDOS=600;
function responderBoletin(string $mensaje,int $estado=200):never{http_response_code($estado);header('Content-Type:text/plain; charset=UTF-8');echo$mensaje;exit;}
if(($_SERVER['REQUEST_METHOD']??'')!=='POST')responderBoletin('Método no permitido.',405);
if(!validarTokenCsrf((string)($_POST['csrf_token']??'')))responderBoletin('La solicitud expiró. Recarga la página e inténtalo nuevamente.');
if(trim((string)($_POST['website']??''))!=='')responderBoletin('OK');
$emitido=(int)($_SESSION['boletin_formulario_emitido']??0);if($emitido<1||time()-$emitido<2)responderBoletin('Espera unos segundos antes de enviar el formulario.');
$ahora=time();$intentos=array_values(array_filter(is_array($_SESSION['boletin_intentos']??null)?$_SESSION['boletin_intentos']:[],static fn($t)=>is_int($t)&&$t>=$ahora-BOLETIN_VENTANA_SEGUNDOS));if(count($intentos)>=BOLETIN_MAX_INTENTOS)responderBoletin('Has realizado demasiados intentos. Espera unos minutos.');$intentos[]=$ahora;$_SESSION['boletin_intentos']=$intentos;
try{
 $resultado=suscribirNewsletterAtenea((string)($_POST['email']??''),trim((string)($_POST['nombre']??''))?:null,'website_footer',(string)($_SERVER['REMOTE_ADDR']??''));
 if($resultado['estado']==='existente')responderBoletin('Este correo ya está suscrito al boletín de Atenea.');
 responderBoletin('OK');
}catch(DomainException$e){responderBoletin($e->getMessage());}catch(Throwable$e){error_log('Newsletter suscripción: '.sanitizarErrorCorreoAtenea($e));responderBoletin('No fue posible completar la suscripción. Inténtalo nuevamente.');}
