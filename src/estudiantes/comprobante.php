<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/pedidos_pago.php';
require_once dirname(__DIR__, 2) . '/includes/audit.php';

exigirRol(['usuario']);
$pedidoId = filter_input(INPUT_GET, 'pedido', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$pedido = $pedidoId ? obtenerPedidoParaComprobante($pedidoId, (int) $_SESSION['usuario_id']) : null;
if (!$pedido || ($pedido['payment_status'] ?? '') !== 'paid') {
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
      <?php if(!empty($pedido['direccion'])):?><div class="col-12"><div class="border rounded p-3"><strong>Dirección utilizada</strong><br><?=atenea_e((string)($pedido['direccion']['receptor']??''))?> · <?=atenea_e((string)($pedido['direccion']['telefono']??''))?><br><?=atenea_e((string)($pedido['direccion']['direccion_detallada']??''))?>, <?=atenea_e((string)($pedido['direccion']['municipio']??''))?>, <?=atenea_e((string)($pedido['direccion']['departamento']??''))?></div></div><?php endif;?>
    </div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Producto</th><th class="text-center">Cantidad</th><th class="text-end">Precio</th><th class="text-end">Subtotal</th></tr></thead><tbody>
    <?php foreach ($pedido['detalles'] as $detalle): ?>
      <tr><td><strong><?= atenea_e((string) $detalle['nombre_producto']) ?></strong><?php if ($detalle['sku']): ?><br><small class="text-muted">SKU <?= atenea_e((string) $detalle['sku']) ?></small><?php endif; ?></td><td class="text-center"><?= (int) $detalle['cantidad'] ?></td><td class="text-end">$<?= number_format((float) $detalle['precio_unitario'], 2) ?></td><td class="text-end">$<?= number_format((float) $detalle['subtotal'], 2) ?></td></tr>
    <?php endforeach; ?>
    </tbody><tfoot><tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end">$<?= number_format((float) $pedido['subtotal'], 2) ?></td></tr><?php if ((float) $pedido['descuento'] > 0): ?><tr><td colspan="3" class="text-end">Descuento</td><td class="text-end">−$<?= number_format((float) $pedido['descuento'], 2) ?></td></tr><?php endif; ?><tr><td colspan="3" class="text-end"><strong>Total</strong></td><td class="text-end"><strong>$<?= number_format((float) $pedido['total'], 2) ?> <?= atenea_e(strtoupper((string) $pedido['moneda'])) ?></strong></td></tr></tfoot></table></div>
    <div class="mt-4"><strong>Seguimiento del pedido</strong><div class="progress mt-2" style="height:10px"><div class="progress-bar bg-success" style="width:<?=progresoSeguimientoPedido($pedido['estado_pedido']??null)?>%"></div></div><div class="d-flex justify-content-between flex-wrap small mt-2"><?php foreach(estadosSeguimientoPedido() as$clave=>$etiqueta):?><span class="<?=($pedido['estado_pedido']??'')===$clave?'fw-bold text-success':'text-muted'?>"><?=atenea_e($etiqueta)?></span><?php endforeach;?></div></div>
    <p class="small text-muted mt-4 mb-0">Este comprobante confirma una compra registrada en Atenea. No constituye un DTE ni una factura fiscal certificada.</p>
    <?php
    return (string) ob_get_clean();
}

if ($pedido && isset($_GET['descargar'])) {
    header('Location: '.atenea_url('src/comprobantes/descargar.php?pedido='.$pedidoId.'&tipo=pdf'));
    exit;
}

require_once dirname(__DIR__, 2) . '/includes/portal_estudiante_layout.php';
$portal = portalEstudianteCabecera('Comprobante de compra', 'pedidos', 'Consulta y descarga el comprobante interno de una compra confirmada.');
?>
<div class="row justify-content-center"><div class="col-xl-9">
<?php if (!$pedido): ?>
  <div class="card"><div class="card-body text-center py-5"><i class="bi bi-file-earmark-x display-4 text-danger"></i><h1 class="h4 mt-3">Comprobante no disponible</h1><p class="text-muted">El pedido no existe, no pertenece a tu cuenta o todavía no tiene un pago confirmado.</p><a class="btn btn-primary" href="<?= atenea_url('src/estudiantes/pedidos.php') ?>">Volver a mis pedidos</a></div></div>
<?php else: ?>
  <div class="d-flex flex-wrap justify-content-between gap-2 mb-3"><a class="btn btn-light" href="<?= atenea_url('src/estudiantes/pedidos.php') ?>"><i class="bi bi-arrow-left"></i> Volver a Mis pedidos</a><div class="d-flex flex-wrap gap-2"><?php if(!empty($pedido['pdf_relpath'])&&!empty($pedido['json_relpath'])):?><a class="btn btn-outline-secondary" target="_blank" href="<?=atenea_url('src/comprobantes/descargar.php?pedido='.$pedidoId.'&tipo=pdf&ver=1')?>">Ver factura</a><a class="btn btn-primary" href="<?=atenea_url('src/comprobantes/descargar.php?pedido='.$pedidoId.'&tipo=pdf')?>">Descargar PDF</a><a class="btn btn-outline-primary" href="<?=atenea_url('src/comprobantes/descargar.php?pedido='.$pedidoId.'&tipo=json')?>">Descargar JSON</a><a class="btn btn-outline-primary" target="_blank" href="<?=atenea_url('src/comprobantes/descargar.php?pedido='.$pedidoId.'&tipo=pdf&ver=1')?>"><i class="bi bi-printer"></i> Imprimir</a><?php endif;?></div></div>
  <article class="card atenea-receipt"><div class="card-body p-4 p-lg-5"><?= contenidoComprobanteAtenea($pedido) ?></div></article>
<?php endif; ?>
</div></div>
<?php portalEstudiantePie(); ?>
