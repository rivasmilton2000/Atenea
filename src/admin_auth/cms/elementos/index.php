<?php
require_once dirname(__DIR__) . '/_bootstrap.php';
$pdo = obtenerConexion();
$seccionId = cmsId($_GET['seccion_id'] ?? 0);
$pagina = max(1, cmsId($_GET['pagina'] ?? 1));
$porPagina = 20;
$offset = ($pagina - 1) * $porPagina;
$secciones = $pdo->query('SELECT id,nombre FROM secciones ORDER BY orden,id')->fetchAll();
$where = $seccionId ? 'WHERE e.seccion_id=:sid' : '';
$conteo = $pdo->prepare("SELECT COUNT(*) FROM elementos_seccion e $where");
$conteo->execute($seccionId ? ['sid' => $seccionId] : []);
$total = (int) $conteo->fetchColumn();
$q = $pdo->prepare("SELECT e.*,s.nombre seccion_nombre FROM elementos_seccion e JOIN secciones s ON s.id=e.seccion_id $where ORDER BY s.orden,e.orden,e.id LIMIT :limite OFFSET :offset");
if ($seccionId) $q->bindValue('sid', $seccionId, PDO::PARAM_INT);
$q->bindValue('limite', $porPagina, PDO::PARAM_INT);
$q->bindValue('offset', $offset, PDO::PARAM_INT);
$q->execute();
$filas = $q->fetchAll();
cmsCabecera('Elementos de las secciones', 'elementos/index.php');
?>
<div class="row g-2 mb-3">
  <div class="col-md-6"><form><select class="form-select" name="seccion_id" onchange="this.form.submit()"><option value="">Todas las secciones</option><?php foreach($secciones as $s):?><option value="<?=$s['id']?>" <?=$seccionId===$s['id']?'selected':''?>><?=atenea_e($s['nombre'])?></option><?php endforeach;?></select></form></div>
  <div class="col-md-6 text-md-end"><a class="btn btn-atenea" href="editar.php<?=$seccionId?'?seccion_id='.$seccionId:''?>">Agregar elemento</a></div>
</div>
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table"><thead><tr><th>Sección</th><th>Título</th><th>Estado</th><th>Orden</th><th>Imagen</th><th>Acciones</th></tr></thead><tbody>
<?php if(!$filas):?><tr><td colspan="6" class="text-center py-5">No hay elementos.</td></tr><?php endif;?>
<?php foreach($filas as $f):?><tr><td><?=atenea_e($f['seccion_nombre'])?></td><td><?=atenea_e($f['titulo'])?><div class="small text-muted"><?=atenea_e((string)$f['subtitulo'])?></div></td><td><span class="badge bg-<?=$f['activo']?'success':'secondary'?>"><?=$f['activo']?'Activo':'Inactivo'?></span></td><td><?=$f['orden']?></td><td><?php if($f['imagen']):?><img src="<?=rutaImagenContenido($f['imagen'])?>" alt="" style="width:60px;height:45px;object-fit:cover"><?php else:?>—<?php endif;?></td><td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="editar.php?id=<?=$f['id']?>">Editar</a> <form class="d-inline" method="post" action="accion.php"><input type="hidden" name="csrf_token" value="<?=atenea_e(obtenerTokenCsrf())?>"><input type="hidden" name="id" value="<?=$f['id']?>"><input type="hidden" name="accion" value="toggle"><button class="btn btn-sm btn-outline-warning"><?=$f['activo']?'Desactivar':'Activar'?></button></form> <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#del<?=$f['id']?>">Eliminar</button></td></tr>
<div class="modal fade" id="del<?=$f['id']?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h2 class="h5">Eliminar elemento</h2><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">Se eliminará “<?=atenea_e($f['titulo'])?>”. Esta acción no se puede deshacer.</div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><form method="post" action="accion.php"><input type="hidden" name="csrf_token" value="<?=atenea_e(obtenerTokenCsrf())?>"><input type="hidden" name="id" value="<?=$f['id']?>"><input type="hidden" name="accion" value="eliminar"><button class="btn btn-danger">Eliminar</button></form></div></div></div></div>
<?php endforeach;?></tbody></table></div>
<?php $paginas=max(1,(int)ceil($total/$porPagina));if($paginas>1):?><nav class="mt-3"><ul class="pagination"><?php for($i=1;$i<=$paginas;$i++):?><li class="page-item <?=$i===$pagina?'active':''?>"><a class="page-link" href="?pagina=<?=$i?><?=$seccionId?'&seccion_id='.$seccionId:''?>"><?=$i?></a></li><?php endfor;?></ul></nav><?php endif;?>
</div></div>
<?php cmsPie(); ?>

