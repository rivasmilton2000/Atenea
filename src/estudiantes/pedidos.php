<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/portal_estudiante_layout.php';
require_once dirname(__DIR__,2).'/includes/comercio.php';
exigirRol(['usuario']);
$pdo=obtenerConexion();$pagina=max(1,(int)($_GET['pagina']??1));$limite=10;
$q=$pdo->prepare('SELECT COUNT(*) FROM pedidos WHERE usuario_id=:u');$q->execute(['u'=>$_SESSION['usuario_id']]);$total=(int)$q->fetchColumn();
$q=$pdo->prepare('SELECT p.*,d.codigo_generacion,d.numero_control,d.estado estado_dte,d.pdf_relpath FROM pedidos p LEFT JOIN dte_documentos d ON d.pedido_id=p.id WHERE p.usuario_id=:u ORDER BY p.created_at DESC LIMIT :lim OFFSET :off');
$q->bindValue(':u',(int)$_SESSION['usuario_id'],PDO::PARAM_INT);$q->bindValue(':lim',$limite,PDO::PARAM_INT);$q->bindValue(':off',($pagina-1)*$limite,PDO::PARAM_INT);$q->execute();$pedidos=$q->fetchAll();
$portal=portalEstudianteCabecera('Mis pedidos y pagos','pedidos','Consulta el pago, seguimiento y documentos de tus compras.');
?>
<div class="card"><div class="card-header"><h1 class="card-title">Mis pedidos</h1></div><div class="card-body"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Pedido / fecha</th><th>Total</th><th>Pago</th><th style="min-width:260px">Seguimiento</th><th>Documentos</th></tr></thead><tbody>
<?php foreach($pedidos as$p):$pagado=($p['payment_status']??'')==='paid';?>
<tr><td><strong><?=atenea_e($p['numero'])?></strong><small class="d-block"><?=date('d/m/Y H:i',strtotime($p['created_at']))?></small></td><td>$<?=atenea_e($p['total'])?> <?=atenea_e(strtoupper($p['moneda']))?></td><td><span class="badge <?=$pagado?'bg-success':'bg-warning text-dark'?>"><?=$pagado?'Pago completado':'Pago pendiente'?></span></td><td><?php if($pagado):?><strong><?=atenea_e(etiquetaSeguimientoPedido($p['estado_pedido']))?></strong><div class="progress mt-2" style="height:8px"><div class="progress-bar bg-success" style="width:<?=progresoSeguimientoPedido($p['estado_pedido'])?>%"></div></div><div class="d-flex justify-content-between small text-muted mt-1"><span>Pago</span><span>Envío</span><span>Almacén</span><span>Entregado</span></div><?php else:?><small class="text-muted">Disponible después del pago.</small><?php endif;?></td><td><a class="btn btn-sm btn-outline-secondary mb-1" href="<?=atenea_url('src/estudiantes/comprobante.php?pedido='.$p['id'])?>">Detalle</a><?php if($pagado&&!empty($p['codigo_generacion'])&&!empty($p['pdf_relpath'])):?> <a class="btn btn-sm btn-outline-primary mb-1" href="<?=atenea_url('src/dte/documento.php?pedido='.$p['id'].'&descargar=1')?>">Descargar PDF</a> <a class="btn btn-sm btn-outline-primary mb-1" href="<?=atenea_url('src/dte/documento.php?pedido='.$p['id'].'&tipo=json&descargar=1')?>">Descargar JSON</a><?php endif;?></td></tr>
<?php endforeach;?><?php if(!$pedidos):?><tr><td colspan="5" class="text-center py-5 text-muted">Aún no tienes pedidos.</td></tr><?php endif;?></tbody></table></div>
<?php $paginas=max(1,(int)ceil($total/$limite));if($paginas>1):?><nav><ul class="pagination justify-content-end"><?php for($i=1;$i<=$paginas;$i++):?><li class="page-item <?=$i===$pagina?'active':''?>"><a class="page-link" href="?pagina=<?=$i?>"><?=$i?></a></li><?php endfor;?></ul></nav><?php endif;?></div></div>
<?php portalEstudiantePie();
