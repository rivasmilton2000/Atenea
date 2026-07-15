<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/pedidos_pago.php';
require_once dirname(__DIR__, 2) . '/includes/audit.php';

exigirRol(['usuario']);
$pedidoId = filter_input(INPUT_GET, 'pedido', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$pedido = $pedidoId ? obtenerPedidoParaComprobante($pedidoId, (int) $_SESSION['usuario_id']) : null;
if (!$pedido || $pedido['estado'] !== 'pagado' || ($pedido['payment_status'] ?? '') !== 'paid') {
    http_response_code(404);
    $pedido = null;
}

function contenidoComprobanteAtenea(array $pedido): string
{
    ob_start();
    ?>
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
      <div><p class="text-uppercase text-muted small mb-1">Comprobante de compra</p><h2 class="h4 mb-1"><?= atenea_e((string) $pedido['numero']) ?></h2><span class="badge bg-success">Pago confirmado</span></div>
      <div class="text-md-end"><strong>Atenea Escuela de Naturopatía Holística</strong><br><span class="text-muted">Documento interno no fiscal</span></div>
    </div>
    <div class="row g-3 mb-4">
      <div class="col-sm-6"><div class="border rounded p-3 h-100"><strong>Comprador</strong><br><?= atenea_e(trim((string) $pedido['nombre'] . ' ' . (string) $pedido['apellido'])) ?><br><?= atenea_e((string) $pedido['correo']) ?></div></div>
      <div class="col-sm-6"><div class="border rounded p-3 h-100"><strong>Pago</strong><br><?= atenea_e(date('d/m/Y H:i', strtotime((string) ($pedido['paid_at'] ?: $pedido['updated_at'])))) ?> (El Salvador)<br><?= atenea_e(metodoPagoPedido($pedido)) ?></div></div>
    </div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Producto</th><th class="text-center">Cantidad</th><th class="text-end">Precio</th><th class="text-end">Subtotal</th></tr></thead><tbody>
    <?php foreach ($pedido['detalles'] as $detalle): ?>
      <tr><td><strong><?= atenea_e((string) $detalle['nombre_producto']) ?></strong><?php if ($detalle['sku']): ?><br><small class="text-muted">SKU <?= atenea_e((string) $detalle['sku']) ?></small><?php endif; ?></td><td class="text-center"><?= (int) $detalle['cantidad'] ?></td><td class="text-end">$<?= number_format((float) $detalle['precio_unitario'], 2) ?></td><td class="text-end">$<?= number_format((float) $detalle['subtotal'], 2) ?></td></tr>
    <?php endforeach; ?>
    </tbody><tfoot><tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end">$<?= number_format((float) $pedido['subtotal'], 2) ?></td></tr><?php if ((float) $pedido['descuento'] > 0): ?><tr><td colspan="3" class="text-end">Descuento</td><td class="text-end">−$<?= number_format((float) $pedido['descuento'], 2) ?></td></tr><?php endif; ?><tr><td colspan="3" class="text-end"><strong>Total</strong></td><td class="text-end"><strong>$<?= number_format((float) $pedido['total'], 2) ?> <?= atenea_e(strtoupper((string) $pedido['moneda'])) ?></strong></td></tr></tfoot></table></div>
    <p class="small text-muted mb-0">Este comprobante confirma una compra registrada en Atenea. No constituye un DTE ni una factura fiscal certificada.</p>
    <?php
    return (string) ob_get_clean();
}

if ($pedido && isset($_GET['descargar'])) {
    actualizarActividadUsuario((int)$_SESSION['usuario_id']);
    registrarAuditoria(['actor_user_id'=>(int)$_SESSION['usuario_id'],'target_user_id'=>(int)$_SESSION['usuario_id'],'event_type'=>'receipt.downloaded','module'=>'payments','entity_type'=>'order','entity_id'=>$pedidoId,'action'=>'download','result'=>'success','description'=>'El propietario descargo su comprobante interno de compra.']);
    $contenido = contenidoComprobanteAtenea($pedido);
    $nombreArchivo = preg_replace('/[^A-Za-z0-9_-]/', '-', (string) $pedido['numero']) ?: 'atenea';
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="comprobante-' . $nombreArchivo . '.html"');
    header('X-Content-Type-Options: nosniff');
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Comprobante ' . atenea_e((string) $pedido['numero']) . '</title><style>body{font-family:Arial,sans-serif;color:#20251f;padding:32px}.receipt{max-width:850px;margin:auto}.d-flex{display:flex}.justify-content-between{justify-content:space-between}.text-end{text-align:right}.text-center{text-align:center}.text-muted{color:#626a63}.border{border:1px solid #ddd}.rounded{border-radius:8px}.p-3{padding:1rem}.mb-4{margin-bottom:1.5rem}.table{width:100%;border-collapse:collapse}.table th,.table td{padding:10px;border-bottom:1px solid #ddd}</style></head><body><main class="receipt">' . $contenido . '</main></body></html>';
    exit;
}

require_once dirname(__DIR__, 2) . '/includes/portal_estudiante_layout.php';
$portal = portalEstudianteCabecera('Comprobante de compra', 'pedidos', 'Consulta y descarga el comprobante interno de una compra confirmada.');
?>
<div class="row justify-content-center"><div class="col-xl-9">
<?php if (!$pedido): ?>
  <div class="card"><div class="card-body text-center py-5"><i class="bi bi-file-earmark-x display-4 text-danger"></i><h1 class="h4 mt-3">Comprobante no disponible</h1><p class="text-muted">El pedido no existe, no pertenece a tu cuenta o todavía no tiene un pago confirmado.</p><a class="btn btn-primary" href="<?= atenea_url('src/estudiantes/pedidos.php') ?>">Volver a mis pedidos</a></div></div>
<?php else: ?>
  <div class="d-flex flex-wrap justify-content-between gap-2 mb-3"><a class="btn btn-light" href="<?= atenea_url('src/estudiantes/pedidos.php') ?>"><i class="bi bi-arrow-left"></i> Mis pedidos</a><div class="d-flex gap-2"><button class="btn btn-outline-primary" type="button" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button><a class="btn btn-primary" href="<?= atenea_url('src/estudiantes/comprobante.php?pedido=' . $pedidoId . '&descargar=1') ?>"><i class="bi bi-download"></i> Descargar</a></div></div>
  <article class="card atenea-receipt"><div class="card-body p-4 p-lg-5"><?= contenidoComprobanteAtenea($pedido) ?></div></article>
<?php endif; ?>
</div></div>
<?php portalEstudiantePie(); ?>
