<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/cms.php';
require_once dirname(__DIR__, 3) . '/includes/mailer.php';
require_once dirname(__DIR__, 3) . '/includes/audit.php';
exigirPermiso('mail.manage');
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf((string)($_POST['csrf_token']??''))){http_response_code(400);exit;}
try{
 if(($_POST['accion']??'')!=='probar'||($_POST['aceptar']??'')!=='1'||!hash_equals('ENVIAR PRUEBA',trim((string)($_POST['confirmacion']??''))))throw new DomainException('La confirmación de la prueba no es válida.');
 $correo=strtolower(trim((string)($_POST['correo']??'')));$autorizado=correoDestinatarioPruebaAtenea();
 if($autorizado===''||!filter_var($correo,FILTER_VALIDATE_EMAIL)||!hash_equals($autorizado,$correo))throw new DomainException('La dirección no está autorizada para pruebas.');
 $evento='prueba-admin:'.(int)$_SESSION['usuario_id'].':'.bin2hex(random_bytes(12));$html='<div style="font-family:Arial,sans-serif"><h2>Prueba de correo de Atenea</h2><p>La cola, la autorización y la entrega SMTP se verifican con un solo destinatario autorizado.</p></div>';
 $id=encolarCorreoAtenea($correo,'Dirección de pruebas','Prueba autorizada de correo Atenea',$html,'Prueba autorizada de correo Atenea.',['tipo'=>'prueba_administrativa','categoria'=>'transaccional','evento_id'=>$evento,'idempotency_key'=>$evento,'permitir_envio_prueba'=>true]);
 $resultado=$id?procesarCorreoEnColaAtenea($id):'omitido';
 registrarAuditoria(['actor_user_id'=>(int)$_SESSION['usuario_id'],'event_type'=>'mail.authorized_test','module'=>'mail','entity_type'=>'correo_envio','entity_id'=>$id,'action'=>'send_test','result'=>$resultado==='enviado'?'success':'failure','description'=>'Se ejecutó una prueba para la dirección de correo autorizada.','metadata'=>['resultado'=>$resultado]]);
 cmsFlash($resultado==='enviado'?'exito':'error',$resultado==='enviado'?'El correo de prueba autorizado fue enviado.':'La prueba quedó registrada con estado: '.$resultado.'.');
}catch(Throwable $e){cmsFlash('error',$e instanceof DomainException?$e->getMessage():'No fue posible ejecutar la prueba autorizada.');}
header('Location:'.atenea_url('src/dashboard/comunicaciones/correos.php'));exit;
