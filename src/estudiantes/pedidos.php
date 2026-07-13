<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/portal_estudiante_layout.php';
$portal=portalEstudianteCabecera('Mis pedidos y pagos','pedidos','Consulta el estado y el importe de tus operaciones en Atenea.');
$pedidos=$portal['datos']['pedidos'];
?>
<div class="row"><div class="col-12"><div class="card"><div class="card-header"><h1 class="card-title mb-1">Mis pedidos y pagos</h1><p class="text-muted mb-0">Información obtenida directamente de tus pedidos registrados.</p></div><div class="card-body"><div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Pedido</th><th>Fecha</th><th>Total</th><th>Moneda</th><th>Estado</th></tr></thead><tbody><?php foreach($pedidos as $pedido): ?><tr><td><strong><?= atenea_e((string)$pedido['numero']) ?></strong></td><td><?= date('d/m/Y H:i',strtotime((string)$pedido['created_at'])) ?></td><td>$<?= number_format((float)$pedido['total'],2) ?></td><td><?= atenea_e(strtoupper((string)$pedido['moneda'])) ?></td><td><span class="badge <?= claseEstadoPedido((string)$pedido['estado']) ?>"><?= atenea_e(estadoPedidoEstudiante((string)$pedido['estado'])) ?></span></td></tr><?php endforeach; ?><?php if(!$pedidos): ?><tr><td colspan="5" class="text-center py-5"><i class="bi bi-receipt display-5 text-primary"></i><p class="mt-3 mb-0 text-muted">Aún no tienes pedidos registrados.</p></td></tr><?php endif; ?></tbody></table></div></div></div></div></div>
<?php portalEstudiantePie(); ?>
