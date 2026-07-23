<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/includes/cms.php';
require_once dirname(__DIR__,3).'/includes/pedidos_pago.php';
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf((string)($_POST['csrf_token']??''))){http_response_code(400);exit;}
$id=cmsId($_POST['id']??0);if(!$id){cmsFlash('error','Pedido inválido.');header('Location:index.php');exit;}
try{$ok=reenviarCorreoCompraConfirmadaAtenea($id,(int)$_SESSION['usuario_id']);cmsFlash($ok?'success':'error',$ok?'El reenvío quedó pendiente en la cola segura.':'No fue posible preparar el reenvío.');}
catch(Throwable $e){error_log('Reenvío comprobante pedido '.$id.': '.$e->getMessage());cmsFlash('error','No fue posible preparar el reenvío. Revisa el registro de correo.');}
header('Location:detalle.php?id='.$id);
