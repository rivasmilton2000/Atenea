<?php
declare(strict_types=1);
require_once __DIR__.'/includes/cms.php';
require_once dirname(__DIR__,2).'/includes/admin_metricas.php';
$pdo=obtenerConexion();$periodo=periodoMetricasAtenea($_GET['desde']??null,$_GET['hasta']??null);$m=metricasAdministrativasAtenea($pdo,$periodo);$k=$m['kpi'];$prev=(float)$k['ingresos_anterior'];$comparacion=$prev>0?(((float)$k['ingresos']-$prev)/$prev*100):null;
$tarjetas=[['Pedidos',$k['pedidos']],['Pagados',$k['pagados']],['Pendientes',$k['pendientes']],['Entregados',$k['entregados']],['Ingresos','$'.number_format((float)$k['ingresos'],2)],['Promedio','$'.number_format((float)$k['promedio'],2)]];
cmsCabecera('Panel principal','panel','Métricas reales y actividad administrativa de Atenea.');
?>
<div id="resumen-sitio">
 <form class="row g-2 mb-4"><div class="col-md-3"><label class="form-label">Desde</label><input class="form-control" type="date" name="desde" value="<?=atenea_e($periodo['desde'])?>"></div><div class="col-md-3"><label class="form-label">Hasta</label><input class="form-control" type="date" name="hasta" value="<?=atenea_e($periodo['hasta'])?>"></div><div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Aplicar</button></div><div class="col-md-4 d-flex align-items-end"><small class="text-muted">Ingresos: pagos confirmados, excluyendo cancelados y reembolsados.</small></div></form>
 <div class="home-tab"><ul class="nav nav-tabs" role="tablist"><?php foreach([['resumen','Resumen'],['usuarios','Usuarios'],['contenido','Contenido'],['mas','Más']] as$i=>$tab):?><li class="nav-item"><button class="nav-link <?=$i===0?'active ps-0':''?>" data-bs-toggle="tab" data-bs-target="#<?=$tab[0]?>" type="button"><?=$tab[1]?></button></li><?php endforeach;?></ul>
 <div class="tab-content tab-content-basic">
  <div class="tab-pane fade show active" id="resumen">
   <div class="row g-3 mb-4"><?php foreach($tarjetas as$x):?><div class="col-6 col-lg-2"><div class="card card-rounded h-100"><div class="card-body py-3"><p class="statistics-title mb-1"><?=atenea_e($x[0])?></p><h3 class="rate-percentage mb-0"><?=atenea_e((string)$x[1])?></h3></div></div></div><?php endforeach;?></div>
   <p class="text-muted">Comparación con el periodo anterior: <?=$comparacion===null?'sin base comparable':number_format($comparacion,1).'%'?>.</p>
   <div class="row"><?php foreach([['chartVentas','Ventas e ingresos confirmados','col-lg-8'],['chartEstados','Pedidos por estado','col-lg-4'],['chartProductos','Productos más vendidos','col-lg-6'],['chartUsuarios','Nuevos usuarios','col-lg-6']] as$c):?><div class="<?=$c[2]?> grid-margin stretch-card"><div class="card card-rounded"><div class="card-body"><h2 class="h5"><?=$c[1]?></h2><div class="atenea-chart-empty text-muted d-none">No hay datos en el periodo.</div><canvas id="<?=$c[0]?>" height="110"></canvas></div></div></div><?php endforeach;?></div>
   <div class="alert alert-warning">Pedidos pendientes: <?=$m['alertas']['pedidos_pendientes']?> · Errores nuevos: <?=$m['alertas']['errores_nuevos']?> · Correos fallidos: <?=$m['alertas']['correos_fallidos']?></div>
  </div>
  <div class="tab-pane fade" id="usuarios">
   <div class="row g-3 mb-4"><?php foreach([['Usuarios',$m['usuarios']['total']],['Activos',$m['usuarios']['activos']],['Inactivos',$m['usuarios']['inactivos']]] as$x):?><div class="col-md-4"><div class="card card-rounded"><div class="card-body"><h3 class="h6"><?=$x[0]?></h3><p class="display-6"><?=$x[1]?></p></div></div></div><?php endforeach;?></div>
   <div class="row"><div class="col-lg-6"><div class="card card-rounded"><div class="card-body"><h3 class="h5">Distribución por rol</h3><canvas id="chartRoles"></canvas></div></div></div><div class="col-lg-6"><div class="card card-rounded"><div class="card-body"><h3 class="h5">Accesos recientes</h3><?php foreach($m['usuarios']['accesos'] as$u):?><p><?=atenea_e(trim($u['nombre'].' '.$u['apellido']))?><small class="float-end"><?=date('d/m/Y H:i',strtotime($u['ultimo_acceso']))?></small></p><?php endforeach;?><a href="usuarios/index.php" class="btn btn-outline-primary">Módulo de usuarios</a></div></div></div></div>
  </div>
  <div class="tab-pane fade" id="contenido">
   <div class="row g-3 mb-4"><?php foreach([['Capacitaciones publicadas',$m['contenido']['capacitaciones']],['Productos activos',$m['contenido']['productos']],['Contenidos pendientes',$m['contenido']['pendientes']]] as$x):?><div class="col-md-4"><div class="card card-rounded"><div class="card-body"><h3 class="h6"><?=$x[0]?></h3><p class="display-6"><?=$x[1]?></p></div></div></div><?php endforeach;?></div>
   <div class="card card-rounded"><div class="card-body"><h3 class="h5">Stock bajo</h3><?php foreach($m['contenido']['stock_bajo'] as$p):?><p><?=atenea_e($p['nombre'])?><span class="float-end"><?=max(0,(int)$p['stock']-(int)$p['stock_reservado'])?> disponibles</span></p><?php endforeach;?><?php if(!$m['contenido']['stock_bajo']):?><p class="text-muted">No hay alertas de stock bajo.</p><?php endif;?><a href="productos/index.php" class="btn btn-outline-primary">Administrar productos</a> <a href="elementos/index.php" class="btn btn-outline-primary">Administrar contenido</a></div></div>
  </div>
  <div class="tab-pane fade" id="mas"><div class="row g-3"><?php foreach([['Pedidos','pedidos/index.php'],['Facturas / DTE','facturas/index.php'],['Comunicaciones','comunicaciones/index.php'],['Bitácora','bitacora/index.php'],['Errores','errores/index.php'],['Configuración','configuracion/index.php']] as$x):?><div class="col-md-4"><a class="card card-rounded text-decoration-none h-100" href="<?=atenea_e($x[1])?>"><div class="card-body"><h3 class="h5 mb-0"><?=atenea_e($x[0])?></h3></div></a></div><?php endforeach;?></div></div>
 </div></div>
</div>
<script src="<?=atenea_url('src/dashboard/assets/vendors/chart.js/chart.umd.js')?>"></script>
<script>window.ateneaMetrics=<?=json_encode(['ventas'=>$m['ventas'],'estados'=>$m['pedidos_estado'],'productos'=>$m['productos'],'usuarios'=>$m['usuarios_nuevos'],'roles'=>$m['roles']],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)?>;</script>
<script src="<?=atenea_url('src/dashboard/assets/js/metricas.js')?>"></script>
<?php cmsPie();
