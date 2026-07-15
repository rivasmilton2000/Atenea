<?php
declare(strict_types=1);

require_once __DIR__ . '/comercio.php';
require_once __DIR__ . '/mailer.php';

function obtenerPedidoParaComprobante(int $pedidoId, ?int $usuarioId = null): ?array
{
    $pdo = obtenerConexion();
    $sql = 'SELECT p.*,u.nombre,u.apellido,u.correo,d.codigo_generacion,d.numero_control,d.estado estado_dte FROM pedidos p INNER JOIN usuarios u ON u.id=p.usuario_id LEFT JOIN dte_documentos d ON d.pedido_id=p.id WHERE p.id=:pedido';
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

function enviarConfirmacionCompraAtenea(int $pedidoId): bool
{
    $pedido = obtenerPedidoParaComprobante($pedidoId);
    if (!$pedido || $pedido['estado'] !== 'pagado' || ($pedido['payment_status'] ?? '') !== 'paid') return false;
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
            'direccion' => $pedido['direccion'] ? (($pedido['direccion']['direccion_detallada']??'').', '.($pedido['direccion']['municipio']??'').', '.($pedido['direccion']['departamento']??'')) : 'No disponible en pedidos anteriores',
            'codigo_generacion' => $pedido['codigo_generacion'] ?: 'Pendiente de emisión',
            'comprobante_url' => atenea_url_absoluta('src/estudiantes/comprobante.php?pedido=' . $pedidoId),
            'pdf_url' => $pedido['codigo_generacion'] ? atenea_url_absoluta('src/dte/documento.php?pedido='.$pedidoId.'&descargar=1') : '',
            'json_url' => $pedido['codigo_generacion'] ? atenea_url_absoluta('src/dte/documento.php?pedido='.$pedidoId.'&tipo=json&descargar=1') : '',
        ], ['usuario_id' => (int) $pedido['usuario_id'], 'pedido_id' => $pedidoId, 'idempotency_key' => $clave]);
        $pdo = obtenerConexion();
        if (tablaCorreoDisponible($pdo)) {
            $consulta = $pdo->prepare("SELECT estado FROM correo_envios WHERE idempotency_key=:clave LIMIT 1");
            $consulta->execute(['clave' => $clave]);
            if ($consulta->fetchColumn() === 'enviado') {
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
