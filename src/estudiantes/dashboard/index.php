<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/includes/portal_estudiante_layout.php';
$portal = portalEstudianteCabecera('Inicio', 'inicio', 'Consulta tu actividad, aprendizaje y pagos en Atenea.');
$resumen = $portal['datos']['resumen'];
$pedidos = $portal['datos']['pedidos'];
?>
<div class="row">
  <?php foreach ([['bi-journal-bookmark','Capacitaciones activas',count($portal['datos']['capacitaciones']),'primary'],['bi-receipt','Pedidos realizados',(int)$resumen['pedidos'],'info'],['bi-check-circle','Pagos completados',(int)$resumen['pagados'],'success'],['bi-award','Certificados disponibles',count($portal['datos']['certificados']),'warning']] as [$icono,$etiqueta,$valor,$color]): ?>
  <div class="col-md-6 col-lg-3"><div class="card" data-aos="fade-up"><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div class="bg-soft-<?= $color ?> rounded p-3"><i class="bi <?= $icono ?> fs-4 text-<?= $color ?>"></i></div><div class="text-end"><span class="text-muted"><?= atenea_e($etiqueta) ?></span><h2 class="counter mb-0"><?= (int)$valor ?></h2></div></div></div></div></div>
  <?php endforeach; ?>
</div>
<div class="row">
  <div class="col-lg-8"><div class="card" data-aos="fade-up" data-aos-delay="100"><div class="card-header d-flex justify-content-between align-items-center"><div><h2 class="card-title mb-1">Mis capacitaciones</h2><p class="text-muted mb-0">Programas asociados a tu cuenta.</p></div><a class="btn btn-sm btn-primary" href="<?= atenea_url('src/website/courses.php') ?>">Explorar capacitaciones</a></div><div class="card-body">
    <?php if (!$portal['datos']['capacitaciones']): ?><div class="text-center py-5"><i class="bi bi-journal-plus display-4 text-primary"></i><h3 class="h5 mt-3">Aún no tienes capacitaciones asignadas</h3><p class="text-muted mb-0">Cuando Atenea habilite una capacitación para tu cuenta aparecerá aquí.</p></div><?php endif; ?>
  </div></div></div>
  <div class="col-lg-4"><div class="card" data-aos="fade-up" data-aos-delay="200"><div class="card-header"><h2 class="card-title">Resumen de pagos</h2></div><div class="card-body"><div class="d-flex align-items-center justify-content-between mb-4"><span>Total pagado</span><strong class="fs-4 text-success">$<?= number_format((float)$resumen['invertido'],2) ?></strong></div><p class="text-muted">Los pedidos pagados se reflejan automáticamente desde tu cuenta de Atenea.</p><a href="<?= atenea_url('src/estudiantes/pedidos.php') ?>" class="btn btn-outline-primary w-100">Ver pedidos y pagos</a></div></div></div>
</div>
<div class="row"><div class="col-12"><div class="card" data-aos="fade-up" data-aos-delay="300"><div class="card-header d-flex justify-content-between"><h2 class="card-title">Actividad reciente</h2><a href="<?= atenea_url('src/estudiantes/pedidos.php') ?>">Ver todo</a></div><div class="card-body"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Pedido</th><th>Fecha</th><th>Total</th><th>Estado</th></tr></thead><tbody>
<?php foreach(array_slice($pedidos,0,5) as $pedido): ?><tr><td><?= atenea_e((string)$pedido['numero']) ?></td><td><?= date('d/m/Y H:i',strtotime((string)$pedido['created_at'])) ?></td><td>$<?= number_format((float)$pedido['total'],2) ?> <?= atenea_e(strtoupper((string)$pedido['moneda'])) ?></td><td><span class="badge <?= claseEstadoPedido((string)$pedido['estado']) ?>"><?= atenea_e(estadoPedidoEstudiante((string)$pedido['estado'])) ?></span></td></tr><?php endforeach; ?>
<?php if(!$pedidos): ?><tr><td colspan="4" class="text-center py-4 text-muted">Aún no hay actividad registrada.</td></tr><?php endif; ?>
</tbody></table></div></div></div></div></div>
<?php portalEstudiantePie(); ?>
