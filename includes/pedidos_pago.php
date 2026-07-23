<?php
declare(strict_types=1);

require_once __DIR__ . '/comercio.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/audit.php';

function obtenerPedidoParaComprobante(int $pedidoId, ?int $usuarioId = null): ?array
{
    $pdo = obtenerConexion();
    $sql = 'SELECT p.*,u.nombre,u.apellido,u.correo,c.codigo_generacion,c.numero comprobante_numero,c.pdf_relpath,c.json_relpath FROM pedidos p INNER JOIN usuarios u ON u.id=p.usuario_id LEFT JOIN comprobante_documentos c ON c.pedido_id=p.id WHERE p.id=:pedido';
    $parametros = ['pedido' => $pedidoId];
    if ($usuarioId !== null) {
        $sql .= ' AND p.usuario_id=:usuario';
        $parametros['usuario'] = $usuarioId;
    }
    $consulta = $pdo->prepare($sql . ' LIMIT 1');
    $consulta->execute($parametros);
    $pedido = $consulta->fetch();
    if (!$pedido) return null;
    $consulta = $pdo->prepare('SELECT nombre_producto,sku,cantidad,precio_normal,precio_unitario,descuento_unitario,subtotal FROM pedido_detalles WHERE pedido_id=:pedido ORDER BY id');
    $consulta->execute(['pedido' => $pedidoId]);
    $pedido['detalles'] = $consulta->fetchAll();
    $pedido['direccion'] = $pedido['direccion_snapshot'] ? json_decode((string)$pedido['direccion_snapshot'],true) : null;
    return $pedido;
}

function metodoPagoPedido(array $pedido): string
{
    $marca = trim((string) ($pedido['payment_brand'] ?? ''));
    $ultimos = trim((string) ($pedido['payment_last4'] ?? ''));
    if ($marca !== '' && preg_match('/^\d{4}$/', $ultimos)) return ucfirst($marca) . ' terminada en ' . $ultimos;
    return 'Procesado de forma segura por Stripe';
}

/** Confirma un pedido usando exclusivamente una Checkout Session obtenida/verificada en el servidor. */
function confirmarPedidoDesdeCheckoutStripe(PDO $pdo, object $sesion, string $referenciaEvento, array $metodoPago = []): array
{
    $pedidoId = (int) ($sesion->metadata->pedido_id ?? $sesion->client_reference_id ?? 0);
    if ($pedidoId < 1) throw new RuntimeException('La sesión no contiene una referencia de pedido válida.');
    $q = $pdo->prepare('SELECT * FROM pedidos WHERE id=:id FOR UPDATE');
    $q->execute(['id' => $pedidoId]);
    $pedido = $q->fetch();
    if (!$pedido) throw new RuntimeException('Pedido de Stripe no localizado.');

    $importeEsperado = (int) round((float) $pedido['total'] * 100);
    $valido = hash_equals((string) ($pedido['stripe_checkout_session_id'] ?? ''), (string) ($sesion->id ?? ''))
        && hash_equals((string) $pedidoId, (string) ($sesion->client_reference_id ?? ''))
        && hash_equals((string) $pedido['numero'], (string) ($sesion->metadata->numero ?? ''))
        && (int) ($sesion->amount_total ?? -1) === $importeEsperado
        && strtolower((string) ($sesion->currency ?? '')) === strtolower((string) $pedido['moneda'])
        && (string) ($sesion->payment_status ?? '') === 'paid';
    if (!$valido) throw new RuntimeException('La sesión, referencia, importe, moneda o estado no coincide con el pedido.');

    $cambio = ($pedido['payment_status'] ?? '') !== 'paid' || (int) $pedido['stock_procesado'] !== 1 || empty($pedido['estado_pedido']);
    if (!$cambio) return ['pedido_id'=>$pedidoId,'cambio'=>false,'pedido'=>$pedido];
    if ((int) $pedido['stock_procesado'] !== 1) {
        $detalles = $pdo->prepare('SELECT * FROM pedido_detalles WHERE pedido_id=:pedido');
        $detalles->execute(['pedido' => $pedidoId]);
        foreach ($detalles->fetchAll() as $detalle) {
            $producto = $pdo->prepare('SELECT stock,stock_reservado FROM productos WHERE id=:id FOR UPDATE');
            $producto->execute(['id' => $detalle['producto_id']]);
            $stock = $producto->fetch();
            if (!$stock || (int) $stock['stock'] < (int) $detalle['cantidad']) throw new RuntimeException('Stock insuficiente al confirmar el pedido.');
            $nuevoStock = (int) $stock['stock'] - (int) $detalle['cantidad'];
            $pdo->prepare('UPDATE productos SET stock=:stock,stock_reservado=GREATEST(stock_reservado-:cantidad,0) WHERE id=:id')->execute(['stock'=>$nuevoStock,'cantidad'=>$detalle['cantidad'],'id'=>$detalle['producto_id']]);
            $pdo->prepare("INSERT INTO inventario_movimientos(producto_id,pedido_id,tipo,cantidad,stock_anterior,stock_nuevo,nota) VALUES(:producto,:pedido,'venta',:cantidad,:anterior,:nuevo,'Venta confirmada por Stripe')")->execute(['producto'=>$detalle['producto_id'],'pedido'=>$pedidoId,'cantidad'=>-(int)$detalle['cantidad'],'anterior'=>$stock['stock'],'nuevo'=>$nuevoStock]);
        }
    }
    $intent = substr((string) ($sesion->payment_intent ?? ''), 0, 255) ?: null;
    $pdo->prepare("UPDATE pedidos SET estado=IF(estado IN('pendiente_pago','pago_fallido','carrito'),'pagado',estado),payment_status='paid',estado_pedido=COALESCE(estado_pedido,'pagado'),paid_at=COALESCE(paid_at,NOW()),stripe_payment_intent_id=COALESCE(stripe_payment_intent_id,:intent),payment_brand=COALESCE(:marca,payment_brand),payment_last4=COALESCE(:ultimos,payment_last4),stripe_payment_method_id=COALESCE(:metodo,stripe_payment_method_id),last_stripe_event_id=:evento,receipt_generated_at=COALESCE(receipt_generated_at,NOW()),stock_procesado=1 WHERE id=:id")
        ->execute(['intent'=>$intent,'marca'=>$metodoPago['brand']??null,'ultimos'=>$metodoPago['last4']??null,'metodo'=>$metodoPago['payment_method_id']??null,'evento'=>substr($referenciaEvento,0,255),'id'=>$pedidoId]);
    $referencia = json_encode(['checkout_session'=>(string)$sesion->id,'stripe_event'=>$referenciaEvento], JSON_THROW_ON_ERROR);
    $pdo->prepare("INSERT INTO pagos(pedido_id,stripe_payment_intent_id,importe,moneda,estado,datos_referencia) VALUES(:pedido,:intent,:importe,:moneda,'pagado',:referencia) ON DUPLICATE KEY UPDATE estado='pagado',stripe_payment_intent_id=COALESCE(stripe_payment_intent_id,VALUES(stripe_payment_intent_id)),importe=VALUES(importe),moneda=VALUES(moneda),datos_referencia=VALUES(datos_referencia)")
        ->execute(['pedido'=>$pedidoId,'intent'=>$intent,'importe'=>$pedido['total'],'moneda'=>$pedido['moneda'],'referencia'=>$referencia]);
    registrarHistorialPedido($pdo,$pedidoId,(string)$pedido['estado'],'pagado','stripe',null,'Pago, referencia, importe y moneda confirmados por Stripe.');
    registrarAuditoria(['target_user_id'=>(int)$pedido['usuario_id'],'event_type'=>'payment.approved','module'=>'payments','entity_type'=>'order','entity_id'=>$pedidoId,'action'=>'confirm','result'=>'success','description'=>'Stripe confirmó el pago; referencia, importe y moneda coincidieron.'],$pdo);
    return ['pedido_id'=>$pedidoId,'cambio'=>$cambio,'pedido'=>$pedido];
}

function sincronizarRetornoPedidoStripe(string $sessionId, int $usuarioId): ?array
{
    if (!preg_match('/^cs_[A-Za-z0-9_]+$/', $sessionId)) return null;
    $pdo=obtenerConexion();$q=$pdo->prepare('SELECT id FROM pedidos WHERE stripe_checkout_session_id=:s AND usuario_id=:u LIMIT 1');$q->execute(['s'=>$sessionId,'u'=>$usuarioId]);
    if (!$q->fetchColumn()) return null;
    require_once __DIR__.'/stripe_config.php';$config=configuracionStripe();$autoload=__DIR__.'/stripe/vendor/autoload.php';
    if (!stripeConfigurado($config)||!is_file($autoload)) return null;require_once $autoload;
    try {
        $sesion=(new Stripe\StripeClient($config['secret_key']))->checkout->sessions->retrieve($sessionId,[]);
        if (($sesion->payment_status??'')==='paid') {$pdo->beginTransaction();$r=confirmarPedidoDesdeCheckoutStripe($pdo,$sesion,'server_sync_'.hash('sha256',$sessionId));$pdo->commit();return$r;}
    } catch(Throwable $e) {if($pdo->inTransaction())$pdo->rollBack();error_log('Sincronización retorno Stripe pedido: '.preg_replace('/[\r\n\t]+/',' ',$e->getMessage()));}
    return null;
}

function enviarConfirmacionCompraAtenea(int $pedidoId): bool
{
    require_once __DIR__ . '/comprobantes.php';
    $pedido = obtenerPedidoParaComprobante($pedidoId);
    if (!$pedido || $pedido['estado'] !== 'pagado' || ($pedido['payment_status'] ?? '') !== 'paid') return false;
    if (!filter_var($pedido['correo'], FILTER_VALIDATE_EMAIL) || str_ends_with(strtolower((string)$pedido['correo']), '@atenea.local')) {
        error_log('Confirmación de compra omitida: el pedido '.$pedidoId.' no tiene un destinatario real válido.');
        return false;
    }
    try { $documento = generarComprobantesCompraAtenea($pedidoId); }
    catch (Throwable $error) { error_log('Documentos de compra pedido '.$pedidoId.': '.$error->getMessage()); return false; }
    $pdfPath = rutaDocumentoComprobanteAtenea($documento, 'pdf');
    $jsonPath = rutaDocumentoComprobanteAtenea($documento, 'json');
    if (!$pdfPath || !$jsonPath) return false;
    $nombre = trim((string) ($pedido['nombre'] . ' ' . $pedido['apellido']));
    $clave = 'compra-confirmada:pedido:' . $pedidoId;
    try {
        enviarPlantillaCorreoAtenea('compra_confirmada', (string) $pedido['correo'], $nombre, [
            'numero' => $pedido['numero'],
            'fecha' => date('d/m/Y H:i', strtotime((string) ($pedido['paid_at'] ?: $pedido['updated_at']))) . ' (El Salvador)',
            'productos' => array_map(static fn(array $detalle): array => [
                'nombre' => (string) $detalle['nombre_producto'],
                'cantidad' => (int) $detalle['cantidad'],
                'subtotal' => '$' . number_format((float) $detalle['subtotal'], 2) . ' ' . strtoupper((string) $pedido['moneda']),
            ], $pedido['detalles']),
            'subtotal_formateado' => '$' . number_format((float) $pedido['subtotal'], 2) . ' ' . strtoupper((string) $pedido['moneda']),
            'descuento_formateado' => '-$' . number_format((float) $pedido['descuento'], 2) . ' ' . strtoupper((string) $pedido['moneda']),
            'total_formateado' => '$' . number_format((float) $pedido['total'], 2) . ' ' . strtoupper((string) $pedido['moneda']),
            'metodo' => metodoPagoPedido($pedido),
            'codigo_generacion' => $documento['codigo_generacion'],
            'direccion' => $pedido['direccion'] ? (($pedido['direccion']['direccion_detallada']??'').', '.($pedido['direccion']['municipio']??'').', '.($pedido['direccion']['departamento']??'')) : 'No disponible en pedidos anteriores',
            'codigo_generacion' => $pedido['codigo_generacion'] ?: 'Pendiente de emisión',
            'pedido_id' => $pedidoId,
            'codigo_generacion' => $documento['codigo_generacion'],
            'comprobante_url' => atenea_url_absoluta('src/estudiantes/comprobante.php?pedido=' . $pedidoId),
            'pdf_url' => atenea_url_absoluta('src/comprobantes/descargar.php?pedido='.$pedidoId.'&tipo=pdf'),
            'json_url' => atenea_url_absoluta('src/comprobantes/descargar.php?pedido='.$pedidoId.'&tipo=json'),
        ], ['usuario_id' => (int) $pedido['usuario_id'], 'pedido_id' => $pedidoId, 'idempotency_key' => $clave,
            'attachments'=>[
                ['path'=>$pdfPath,'name'=>'Comprobante_'.$documento['numero'].'.pdf','type'=>'application/pdf'],
                ['path'=>$jsonPath,'name'=>'Compra_'.$documento['numero'].'.json','type'=>'application/json'],
            ]]);
        $pdo = obtenerConexion();
        if (tablaCorreoDisponible($pdo)) {
            $consulta = $pdo->prepare("SELECT id,estado FROM correo_envios WHERE idempotency_key=:clave LIMIT 1");
            $consulta->execute(['clave' => $clave]);
            $envio=$consulta->fetch();
            if ($envio && in_array($envio['estado'],['pendiente','fallido'],true)) {
                procesarCorreoEnColaAtenea((int)$envio['id']);
                $consulta->execute(['clave'=>$clave]);$envio=$consulta->fetch();
            }
            if (($envio['estado']??'') === 'enviado') {
                $pdo->prepare('UPDATE pedidos SET email_sent_at=COALESCE(email_sent_at,NOW()) WHERE id=:id')->execute(['id' => $pedidoId]);
                return true;
            }
            return false;
        }
        return true;
    } catch (Throwable $error) {
        error_log('Confirmación de compra Atenea pedido ' . $pedidoId . ': ' . sanitizarErrorCorreoAtenea($error));
        return false;
    }
}

function reenviarCorreoCompraConfirmadaAtenea(int $pedidoId, int $actorId): bool
{
    require_once __DIR__.'/comprobantes.php';
    $pedido=obtenerPedidoParaComprobante($pedidoId);
    if(!$pedido||($pedido['payment_status']??'')!=='paid')return false;
    if(!filter_var($pedido['correo'],FILTER_VALIDATE_EMAIL)||str_ends_with(strtolower((string)$pedido['correo']),'@atenea.local'))return false;
    $doc=generarComprobantesCompraAtenea($pedidoId);
    $pdf=rutaDocumentoComprobanteAtenea($doc,'pdf');$json=rutaDocumentoComprobanteAtenea($doc,'json');
    if(!$pdf||!$json)return false;
    $datos=['nombre'=>trim($pedido['nombre'].' '.$pedido['apellido']),'numero'=>$pedido['numero'],'pedido_id'=>$pedidoId,
        'fecha'=>date('d/m/Y H:i',strtotime((string)$pedido['paid_at'])).' (El Salvador)',
        'productos'=>array_map(static fn(array $d):array=>['nombre'=>$d['nombre_producto'],'cantidad'=>(int)$d['cantidad'],'subtotal'=>'$'.number_format((float)$d['subtotal'],2).' USD'],$pedido['detalles']),
        'subtotal_formateado'=>'$'.number_format((float)$pedido['subtotal'],2).' USD','descuento_formateado'=>'-$'.number_format((float)$pedido['descuento'],2).' USD','total_formateado'=>'$'.number_format((float)$pedido['total'],2).' USD',
        'metodo'=>metodoPagoPedido($pedido),'direccion'=>implode(', ',array_filter([$pedido['direccion']['direccion_detallada']??null,$pedido['direccion']['municipio']??null,$pedido['direccion']['departamento']??null])),
        'codigo_generacion'=>$doc['codigo_generacion'],'comprobante_url'=>atenea_url_absoluta('src/estudiantes/comprobante.php?pedido='.$pedidoId),
        'pdf_url'=>atenea_url_absoluta('src/comprobantes/descargar.php?pedido='.$pedidoId.'&tipo=pdf'),'json_url'=>atenea_url_absoluta('src/comprobantes/descargar.php?pedido='.$pedidoId.'&tipo=json')];
    $plantilla=plantillaCorreoAtenea('compra_confirmada',$datos);
    $clave='compra-confirmada:pedido:'.$pedidoId.':reenvio:'.bin2hex(random_bytes(8));
    $id=encolarCorreoAtenea((string)$pedido['correo'],trim($pedido['nombre'].' '.$pedido['apellido']),$plantilla['subject'],$plantilla['html'],$plantilla['text'],[
        'tipo'=>'compra_confirmada','usuario_id'=>(int)$pedido['usuario_id'],'pedido_id'=>$pedidoId,'idempotency_key'=>$clave,
        'attachments'=>[['path'=>$pdf,'name'=>'Comprobante_'.$doc['numero'].'.pdf','type'=>'application/pdf'],['path'=>$json,'name'=>'Compra_'.$doc['numero'].'.json','type'=>'application/json']]]);
    if($id){$pdo=obtenerConexion();$pdo->prepare('UPDATE correo_envios SET reenvio_manual=1,reenviado_por=:actor WHERE id=:id')->execute(['actor'=>$actorId,'id'=>$id]);}
    return $id!==null;
}

function enviarAvisoDteDisponibleAtenea(int $pedidoId): bool
{
    $pedido=obtenerPedidoParaComprobante($pedidoId);
    if(!$pedido||!$pedido['codigo_generacion'])return false;
    try{
        enviarPlantillaCorreoAtenea('comprobante_disponible',(string)$pedido['correo'],trim($pedido['nombre'].' '.$pedido['apellido']),[
            'numero'=>$pedido['numero'],
            'comprobante_url'=>atenea_url_absoluta('src/dte/documento.php?pedido='.$pedidoId),
        ],['usuario_id'=>(int)$pedido['usuario_id'],'pedido_id'=>$pedidoId,'idempotency_key'=>'dte-disponible:pedido:'.$pedidoId]);
        return true;
    }catch(Throwable $e){error_log('Aviso DTE pedido '.$pedidoId.': '.sanitizarErrorCorreoAtenea($e));return false;}
}
