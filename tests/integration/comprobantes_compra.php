<?php
declare(strict_types=1);

require_once dirname(__DIR__,2).'/includes/comprobantes.php';

$pdo=obtenerConexion();
$pedidoId=(int)($argv[1]??0);
if($pedidoId<1){$pedidoId=(int)$pdo->query("SELECT id FROM pedidos WHERE payment_status='paid' AND paid_at IS NOT NULL ORDER BY id DESC LIMIT 1")->fetchColumn();}
if($pedidoId<1)throw new RuntimeException('No existe un pedido pagado apto para la prueba.');

$a=generarComprobantesCompraAtenea($pedidoId);$b=generarComprobantesCompraAtenea($pedidoId);
$pdf=rutaDocumentoComprobanteAtenea($a,'pdf');$json=rutaDocumentoComprobanteAtenea($a,'json');
$data=$json?json_decode((string)file_get_contents($json),true,512,JSON_THROW_ON_ERROR):null;
$pedido=pedidoCompletoComprobanteAtenea($pdo,$pedidoId);
$q=$pdo->prepare('SELECT COUNT(*) FROM comprobante_documentos WHERE pedido_id=:id');$q->execute(['id'=>$pedidoId]);
$documentRoot=trim((string)($_SERVER['DOCUMENT_ROOT']??''))?:ATENEA_ROOT;
$checks=[
 'pedido_pagado'=>($pedido['payment_status']??'')==='paid',
 'pdf_existe'=>$pdf!==null&&filesize($pdf)>0,
 'pdf_binario'=>$pdf!==null&&file_get_contents($pdf,false,null,0,5)==='%PDF-',
 'json_existe'=>$json!==null&&filesize($json)>0,
 'json_valido'=>is_array($data),
 'json_no_fiscal'=>($data['documento']['valido_fiscalmente']??null)===false,
 'total_coincide'=>number_format((float)($data['totales']['total']??-1),2,'.','')===number_format((float)$pedido['total'],2,'.',''),
 'productos_coinciden'=>count($data['pedido']['productos']??[])===count($pedido['detalles']),
 'idempotencia_documento'=>$a['id']===$b['id']&&$a['pdf_sha256']===$b['pdf_sha256']&&$a['json_sha256']===$b['json_sha256']&&(int)$q->fetchColumn()===1,
 'stripe_oculto'=>!str_contains((string)($data['pago']['payment_intent_id']??''),(string)$pedido['stripe_payment_intent_id']),
 'ruta_privada'=>!str_starts_with(strtolower((string)$pdf),strtolower($documentRoot.DIRECTORY_SEPARATOR)),
];
$fallos=array_keys(array_filter($checks,static fn(bool $ok):bool=>!$ok));
echo json_encode(['pedido_id'=>$pedidoId,'checks'=>$checks,'resultado'=>$fallos?'FALLO':'OK','fallos'=>$fallos],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),PHP_EOL;
exit($fallos?1:0);
