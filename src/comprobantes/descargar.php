<?php
declare(strict_types=1);

require_once dirname(__DIR__,2).'/includes/auth.php';
require_once dirname(__DIR__,2).'/includes/comprobantes.php';

exigirRol(['usuario','admin','administracion_docente','administrador_docente']);
$pedidoId=filter_var($_GET['pedido']??0,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:0;
$tipo=($_GET['tipo']??'pdf')==='json'?'json':'pdf';
$inline=$tipo==='pdf'&&($_GET['ver']??'')==='1';
$rol=(string)($_SESSION['usuario_rol']??'');
$admin=in_array($rol,['admin','administracion_docente','administrador_docente'],true);
$pdo=obtenerConexion();
$sql="SELECT c.*,p.usuario_id,p.payment_status FROM comprobante_documentos c JOIN pedidos p ON p.id=c.pedido_id WHERE c.pedido_id=:pedido AND p.payment_status='paid'";
$args=['pedido'=>$pedidoId];
if(!$admin){$sql.=' AND p.usuario_id=:usuario';$args['usuario']=(int)$_SESSION['usuario_id'];}
$q=$pdo->prepare($sql.' LIMIT 1');$q->execute($args);$doc=$q->fetch();
if(!$doc){http_response_code(404);exit;}
$ruta=rutaDocumentoComprobanteAtenea($doc,$tipo);if(!$ruta){http_response_code(404);exit;}
$nombre='factura_'.preg_replace('/[^A-Z0-9-]/i','-',(string)$doc['numero']).'.'.$tipo;
header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-store');
header('Content-Type: '.($tipo==='json'?'application/json; charset=utf-8':'application/pdf'));
header('Content-Length: '.filesize($ruta));header('Content-Disposition: '.($inline?'inline':'attachment').'; filename="'.$nombre.'"');
readfile($ruta);
