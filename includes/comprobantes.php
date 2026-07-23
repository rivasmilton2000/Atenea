<?php
declare(strict_types=1);

require_once __DIR__ . '/pedidos_pago.php';

function uuidComprobanteAtenea(): string
{
    $b=random_bytes(16);$b[6]=chr((ord($b[6])&15)|64);$b[8]=chr((ord($b[8])&63)|128);
    return strtoupper(vsprintf('%s%s-%s-%s-%s-%s%s%s',[...str_split(bin2hex($b),4)]));
}

function totalEnLetrasComprobanteAtenea(int $centavos): string
{
    $entero = intdiv($centavos,100); $c = $centavos%100;
    return number_format($entero,0,'.',',').' DÓLARES CON '.str_pad((string)$c,2,'0',STR_PAD_LEFT).' CENTAVOS';
}

function rutaBaseComprobantesAtenea(): string
{
    $configurada = entornoAtenea('COMPROBANTES_STORAGE_PATH');
    return rtrim($configurada !== '' ? $configurada : dirname(ATENEA_ROOT,2) . '/atenea-private/comprobantes', '/\\');
}

function numeroComprobanteAtenea(array $pedido): string
{
    $numero = strtoupper((string) ($pedido['numero'] ?? ''));
    if (preg_match('/^AT-[A-Z0-9-]{4,29}$/', $numero)) return $numero;
    return 'AT-' . date('Ymd', strtotime((string) ($pedido['paid_at'] ?? 'now'))) . '-' . str_pad((string) (int) $pedido['id'], 6, '0', STR_PAD_LEFT);
}

function pagoStripeOcultoAtenea(?string $id): string
{
    $id = trim((string) $id);
    if ($id === '') return 'No disponible';
    $prefijo = str_starts_with($id, 'pi_') ? 'pi_' : '';
    return $prefijo . str_repeat('*', 15) . substr($id, -4);
}

function emisorComprobanteAtenea(PDO $pdo): array
{
    $fila = null;
    try { $fila = $pdo->query('SELECT * FROM dte_configuracion WHERE activo=1 ORDER BY id DESC LIMIT 1')->fetch(); }
    catch (Throwable) { $fila = null; }
    $valor = static function(string $env, string $campo, string $defecto = '') use ($fila): string {
        $texto=trim(entornoAtenea($env,(string)($fila[$campo]??$defecto)));
        if(str_contains($texto,'Ã')||str_contains($texto,'Â')){$reparado=mb_convert_encoding($texto,'UTF-8','Windows-1252');if(mb_check_encoding($reparado,'UTF-8'))$texto=$reparado;}
        return $texto;
    };
    $nit=$valor('INSTITUTION_NIT','nit');$nrc=$valor('INSTITUTION_NRC','nrc');$correo=$valor('INSTITUTION_EMAIL','correo',entornoAtenea('SMTP_FROM_EMAIL'));
    if($nit!==''&&preg_match('/^0+$/',$nit))$nit='';if($nrc!==''&&preg_match('/^0+$/',$nrc))$nrc='';if(str_ends_with(strtolower($correo),'@atenea.local'))$correo='';
    return [
        'nombre' => $valor('INSTITUTION_NAME', 'nombre_comercial', 'Atenea Escuela de Naturopatía Holística'),
        'razon_social' => $valor('INSTITUTION_LEGAL_NAME', 'razon_social'),
        'nit' => $nit ?: null,
        'nrc' => $nrc ?: null,
        'actividad' => $valor('INSTITUTION_ACTIVITY', 'actividad_descripcion'),
        'direccion' => $valor('INSTITUTION_ADDRESS', 'direccion'),
        'correo' => $correo ?: null,
        'telefono' => $valor('INSTITUTION_PHONE', 'telefono'),
    ];
}

function pedidoCompletoComprobanteAtenea(PDO $pdo, int $pedidoId): ?array
{
    $q = $pdo->prepare('SELECT p.*,u.nombre,u.apellido,u.correo,u.telefono telefono_usuario,u.codigo_telefono,u.dui,u.direccion direccion_usuario,dep.nombre departamento_usuario,mun.nombre municipio_usuario,dis.nombre distrito_usuario FROM pedidos p JOIN usuarios u ON u.id=p.usuario_id LEFT JOIN departamentos dep ON dep.id=u.departamento_id LEFT JOIN municipios mun ON mun.id=u.municipio_id LEFT JOIN distritos dis ON dis.id=u.distrito_id WHERE p.id=:id LIMIT 1');
    $q->execute(['id' => $pedidoId]);
    $pedido = $q->fetch();
    if (!$pedido) return null;
    $q = $pdo->prepare('SELECT pd.producto_id,pd.nombre_producto,pd.sku,pd.cantidad,pd.precio_normal,pd.precio_unitario,pd.descuento_unitario,pd.subtotal,COALESCE(pr.tipo_producto,\'producto\') tipo_producto FROM pedido_detalles pd LEFT JOIN productos pr ON pr.id=pd.producto_id WHERE pd.pedido_id=:id ORDER BY pd.id');
    $q->execute(['id' => $pedidoId]);
    $pedido['detalles'] = $q->fetchAll();
    $snap = json_decode((string) ($pedido['direccion_snapshot'] ?? ''), true);
    $pedido['direccion'] = is_array($snap) ? $snap : [];
    return $pedido;
}

function construirJsonComprobanteAtenea(array $p, array $emisor, string $numero, string $codigo): array
{
    $fecha = new DateTimeImmutable((string) ($p['paid_at'] ?: $p['updated_at']));
    $d = $p['direccion'];
    $productos = [];
    foreach ($p['detalles'] as $item) {
        $cantidad = (int) $item['cantidad'];
        $descuento = round((float) $item['descuento_unitario'] * $cantidad, 2);
        $productos[] = [
            'id' => (int) $item['producto_id'], 'tipo' => (string) $item['tipo_producto'],
            'codigo' => $item['sku'] ?: null, 'nombre' => (string) $item['nombre_producto'],
            'cantidad' => $cantidad, 'precio_unitario' => round((float) $item['precio_unitario'], 2),
            'descuento' => $descuento, 'subtotal' => round((float) $item['subtotal'], 2),
        ];
    }
    return [
        'documento' => ['tipo'=>'comprobante_interno','version'=>1,'numero'=>$numero,'codigo_generacion'=>$codigo,'fecha_emision'=>$fecha->format('Y-m-d'),'hora_emision'=>$fecha->format('H:i:s'),'moneda'=>strtoupper((string)$p['moneda']),'valido_fiscalmente'=>false],
        'emisor' => $emisor,
        'receptor' => [
            'id_usuario'=>(int)$p['usuario_id'], 'nombre'=>trim($p['nombre'].' '.$p['apellido']), 'correo'=>$p['correo'],
            'telefono'=>trim((string)($d['telefono'] ?? ($p['codigo_telefono'].' '.$p['telefono_usuario']))) ?: null,
            'dui'=>$p['dui'] ?: null, 'departamento'=>$d['departamento'] ?? $p['departamento_usuario'] ?: null,
            'municipio'=>$d['municipio'] ?? $p['municipio_usuario'] ?: null, 'distrito'=>$d['distrito'] ?? $p['distrito_usuario'] ?: null,
            'direccion'=>$d['direccion_detallada'] ?? $p['direccion_usuario'] ?: null,
        ],
        'pago' => ['estado'=>'pagado','metodo'=>'stripe','fecha_confirmacion'=>$p['paid_at'],'checkout_session_id'=>$p['stripe_checkout_session_id'],'payment_intent_id'=>pagoStripeOcultoAtenea($p['stripe_payment_intent_id'])],
        'pedido' => ['id'=>(int)$p['id'],'numero'=>$p['numero'],'estado_envio'=>$p['estado_pedido'] ?: 'pagado','productos'=>$productos],
        'totales' => ['subtotal'=>round((float)$p['subtotal'],2),'descuento'=>round((float)$p['descuento'],2),'envio'=>round((float)$p['envio'],2),'impuestos'=>round((float)$p['impuestos'],2),'total'=>round((float)$p['total'],2)],
    ];
}

function renderizarPdfComprobanteAtenea(array $j, array $p): string
{
    require_once ATENEA_ROOT . '/vendor/autoload.php';
    $e = static fn(mixed $v): string => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    $logo = ATENEA_ROOT . '/img/atenea-logo-pdf.jpg';
    $logoUri = is_file($logo) ? 'data:image/jpeg;base64,' . base64_encode((string) file_get_contents($logo)) : '';
    $em = $j['emisor']; $re = $j['receptor']; $doc = $j['documento']; $tot = $j['totales'];
    $estadoActual = (string) $j['pedido']['estado_envio'];
    $estados = ['pagado'=>'Pago completado','en_proceso_envio'=>'En proceso de envío','saliendo_almacen'=>'Saliendo de almacén','entregado'=>'Entregado'];
    ob_start(); ?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><style>
@page{margin:24px 30px 45px}body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#242722}.header{width:100%;border-bottom:3px solid #b8943e;padding-bottom:10px}.logo{width:112px}.title{font-size:19px;color:#173f35;margin:0}.warning{margin:10px 0;padding:7px;background:#fff8df;border:1px solid #b8943e;color:#6c5317;text-align:center;font-weight:bold}.box{border:1px solid #d9d5c9;border-radius:5px;padding:9px}.section{color:#173f35;font-size:11px;border-bottom:1px solid #b8943e;padding-bottom:3px;margin:12px 0 6px}.meta,.buyer,.items,.totals,.tracking{width:100%;border-collapse:collapse}.meta td,.buyer td{vertical-align:top;padding:3px 7px}.label{color:#62665e;font-size:8px}.items th{background:#173f35;color:white;padding:6px 4px}.items td{padding:6px 4px;border-bottom:1px solid #ddd9ce;vertical-align:top}.num{text-align:right;white-space:nowrap}.totals{width:47%;margin-left:auto;margin-top:8px}.totals td{padding:4px 6px;border-bottom:1px solid #e4e0d6}.grand td{background:#f2e6bd;font-size:11px;font-weight:bold}.tracking td{padding:7px 3px;text-align:center;border:1px solid #d9d5c9;color:#777}.tracking .active{background:#173f35;color:#fff;font-weight:bold}.footer{position:fixed;bottom:-28px;left:0;right:0;text-align:center;color:#666;font-size:8px}.page:after{content:counter(page)}
</style></head><body>
<table class="header"><tr><td width="55%"><?php if($logoUri):?><img class="logo" src="<?=$logoUri?>"><br><?php endif;?><strong><?=$e($em['nombre'])?></strong><?php if($em['razon_social']):?><br><?=$e($em['razon_social'])?><?php endif;?><?php if($em['nit']):?><br>NIT: <?=$e($em['nit'])?><?php endif;?><?php if($em['nrc']):?> · NRC: <?=$e($em['nrc'])?><?php endif;?><?php if($em['actividad']):?><br><?=$e($em['actividad'])?><?php endif;?><?php if($em['direccion']):?><br><?=$e($em['direccion'])?><?php endif;?><?php if($em['correo']):?><br><?=$e($em['correo'])?><?php endif;?><?php if($em['telefono']):?> · <?=$e($em['telefono'])?><?php endif;?></td><td width="45%" align="right"><h1 class="title">COMPROBANTE DE COMPRA</h1><strong><?=$e($doc['numero'])?></strong><br><span class="label">Código de generación</span><br><?=$e($doc['codigo_generacion'])?><br><span class="label">Número de control interno</span><br><?=$e($doc['numero'])?></td></tr></table>
<div class="warning">DOCUMENTO INTERNO NO FISCAL · NO VÁLIDO FISCALMENTE</div>
<table class="meta box"><tr><td><span class="label">Fecha de emisión</span><br><?=$e($doc['fecha_emision'])?></td><td><span class="label">Hora</span><br><?=$e($doc['hora_emision'])?></td><td><span class="label">Estado del pago</span><br><strong>Pagado</strong></td><td><span class="label">Forma de pago</span><br><?=$e(metodoPagoPedido($p))?></td></tr><tr><td colspan="4"><span class="label">Payment Intent</span><br><?=$e($j['pago']['payment_intent_id'])?></td></tr></table>
<h2 class="section">Datos del comprador</h2><table class="buyer box"><tr><td width="50%"><span class="label">Nombre completo</span><br><?=$e($re['nombre'])?></td><td><span class="label">Correo</span><br><?=$e($re['correo'])?></td></tr><tr><td><span class="label">Teléfono</span><br><?=$e($re['telefono'] ?: 'No registrado')?></td><td><span class="label">DUI</span><br><?=$e($re['dui'] ?: 'No registrado')?></td></tr><tr><td><span class="label">Departamento / Municipio</span><br><?=$e(trim(($re['departamento']??'').' / '.($re['municipio']??''),' /'))?></td><td><span class="label">Distrito o ciudad</span><br><?=$e($re['distrito'] ?: 'No registrado')?></td></tr><tr><td colspan="2"><span class="label">Dirección completa</span><br><?=$e($re['direccion'] ?: 'No registrada')?></td></tr></table>
<h2 class="section">Detalle de la compra</h2><table class="items"><thead><tr><th width="5%">N.º</th><th width="11%">Tipo</th><th width="8%">Cant.</th><th width="14%">Código</th><th width="30%">Descripción</th><th width="11%">P. unit.</th><th width="10%">Desc.</th><th width="11%">Subtotal</th></tr></thead><tbody><?php foreach($j['pedido']['productos'] as $n=>$i):?><tr><td><?=($n+1)?></td><td><?=$e($i['tipo'])?></td><td class="num"><?=$i['cantidad']?></td><td><?=$e($i['codigo'] ?: '—')?></td><td><?=$e($i['nombre'])?></td><td class="num">$<?=number_format($i['precio_unitario'],2)?></td><td class="num">$<?=number_format($i['descuento'],2)?></td><td class="num">$<?=number_format($i['subtotal'],2)?></td></tr><?php endforeach;?><?php if($tot['envio']>0):?><tr><td></td><td>Servicio</td><td class="num">1</td><td>ENVÍO</td><td>Costo de envío</td><td class="num">$<?=number_format($tot['envio'],2)?></td><td class="num">$0.00</td><td class="num">$<?=number_format($tot['envio'],2)?></td></tr><?php endif;?></tbody></table>
<table class="totals"><tr><td>Suma de productos</td><td class="num">$<?=number_format($tot['subtotal'],2)?></td></tr><tr><td>Descuentos</td><td class="num">−$<?=number_format($tot['descuento'],2)?></td></tr><tr><td>Costo de envío</td><td class="num">$<?=number_format($tot['envio'],2)?></td></tr><?php if($tot['impuestos']>0):?><tr><td>Impuestos</td><td class="num">$<?=number_format($tot['impuestos'],2)?></td></tr><?php endif;?><tr class="grand"><td>Total pagado</td><td class="num">$<?=number_format($tot['total'],2)?> USD</td></tr></table><p><strong>Valor total en letras:</strong> <?=$e(totalEnLetrasComprobanteAtenea((int)round($tot['total']*100)))?></p>
<h2 class="section">Seguimiento del pedido</h2><table class="tracking"><tr><?php foreach($estados as $k=>$v):?><td class="<?=$k===$estadoActual?'active':''?>"><?=$e($v)?></td><?php endforeach;?></tr></table>
<p><strong>Observaciones:</strong> Comprobante generado al confirmarse el pago del pedido <?=$e($j['pedido']['numero'])?>.</p>
<div class="footer">Documento interno generado por Atenea. No constituye un DTE ni una factura fiscal certificada. · Pedido <?=$e($j['pedido']['numero'])?> · Generado <?=$e($doc['fecha_emision'].' '.$doc['hora_emision'])?> · Página <span class="page"></span></div>
</body></html><?php
    $html = (string) ob_get_clean();
    $dompdf = new Dompdf\Dompdf(['isRemoteEnabled'=>false,'isHtml5ParserEnabled'=>true]);
    $dompdf->loadHtml($html, 'UTF-8'); $dompdf->setPaper('letter', 'portrait'); $dompdf->render();
    $pdf = $dompdf->output();
    if (!str_starts_with($pdf, '%PDF-')) throw new RuntimeException('Dompdf no produjo un PDF binario válido.');
    return $pdf;
}

function generarComprobantesCompraAtenea(int $pedidoId): array
{
    $pdo = obtenerConexion();
    $lock = 'atenea:comprobante:pedido:' . $pedidoId;
    $q = $pdo->prepare('SELECT GET_LOCK(:lock,10)'); $q->execute(['lock'=>$lock]);
    if ((int)$q->fetchColumn() !== 1) throw new RuntimeException('No fue posible bloquear la generación del comprobante.');
    try {
        $q = $pdo->prepare('SELECT * FROM comprobante_documentos WHERE pedido_id=:id LIMIT 1'); $q->execute(['id'=>$pedidoId]);
        if ($existente = $q->fetch()) return $existente;
        $p = pedidoCompletoComprobanteAtenea($pdo, $pedidoId);
        if (!$p || ($p['payment_status']??'') !== 'paid' || !$p['paid_at']) throw new RuntimeException('El comprobante solo se genera después de confirmar el pago.');
        $numero = numeroComprobanteAtenea($p); $codigo = uuidComprobanteAtenea(); $emisor = emisorComprobanteAtenea($pdo);
        $jsonData = construirJsonComprobanteAtenea($p, $emisor, $numero, $codigo);
        $json = json_encode($jsonData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRESERVE_ZERO_FRACTION|JSON_THROW_ON_ERROR);
        $pdf = renderizarPdfComprobanteAtenea($jsonData, $p);
        json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $base = rutaBaseComprobantesAtenea(); $relDir = (int)$p['usuario_id'].'/'.$pedidoId; $dir = $base.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$relDir);
        if (!is_dir($dir) && !mkdir($dir,0700,true) && !is_dir($dir)) throw new RuntimeException('No se creó el almacenamiento privado de comprobantes.');
        $archivo = preg_replace('/[^A-Z0-9-]/i','-',$numero); $pdfRel=$relDir.'/factura_'.$archivo.'.pdf'; $jsonRel=$relDir.'/factura_'.$archivo.'.json';
        $pdfTmp=$dir.DIRECTORY_SEPARATOR.'.'.$archivo.'.pdf.tmp'; $jsonTmp=$dir.DIRECTORY_SEPARATOR.'.'.$archivo.'.json.tmp';
        if (file_put_contents($pdfTmp,$pdf,LOCK_EX)!==strlen($pdf) || file_put_contents($jsonTmp,$json,LOCK_EX)!==strlen($json)) throw new RuntimeException('No se pudieron guardar documentos completos.');
        $pdfPath=$base.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$pdfRel); $jsonPath=$base.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$jsonRel);
        if (!rename($pdfTmp,$pdfPath) || !rename($jsonTmp,$jsonPath)) throw new RuntimeException('No se pudieron publicar los documentos generados.');
        $q=$pdo->prepare('INSERT INTO comprobante_documentos(pedido_id,usuario_id,numero,codigo_generacion,pdf_relpath,json_relpath,pdf_sha256,json_sha256,generado_at) VALUES(:p,:u,:n,:c,:pdf,:json,:ph,:jh,NOW())');
        $q->execute(['p'=>$pedidoId,'u'=>$p['usuario_id'],'n'=>$numero,'c'=>$codigo,'pdf'=>$pdfRel,'json'=>$jsonRel,'ph'=>hash('sha256',$pdf),'jh'=>hash('sha256',$json)]);
        $pdo->prepare('UPDATE pedidos SET receipt_generated_at=COALESCE(receipt_generated_at,NOW()) WHERE id=:id')->execute(['id'=>$pedidoId]);
        $q=$pdo->prepare('SELECT * FROM comprobante_documentos WHERE pedido_id=:id');$q->execute(['id'=>$pedidoId]);return $q->fetch();
    } finally { try{$q=$pdo->prepare('SELECT RELEASE_LOCK(:lock)');$q->execute(['lock'=>$lock]);}catch(Throwable){} }
}

function rutaDocumentoComprobanteAtenea(array $documento, string $tipo): ?string
{
    $campo = $tipo === 'json' ? 'json_relpath' : 'pdf_relpath';
    $base = rutaBaseComprobantesAtenea(); $root = realpath($base); $ruta = realpath($base.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,(string)$documento[$campo]));
    if (!$root || !$ruta || !str_starts_with(strtolower($ruta),strtolower($root.DIRECTORY_SEPARATOR)) || !is_file($ruta) || filesize($ruta)<1) return null;
    return $ruta;
}
