<?php
require_once dirname(__DIR__) . '/includes/cms.php';
$pdo=obtenerConexion();$pagina=max(1,cmsId($_GET['pagina']??1));$porPagina=10;$total=(int)$pdo->query('SELECT COUNT(*) FROM secciones')->fetchColumn();$offset=($pagina-1)*$porPagina;
$q=$pdo->prepare('SELECT * FROM secciones ORDER BY orden,id LIMIT :limite OFFSET :offset');$q->bindValue('limite',$porPagina,PDO::PARAM_INT);$q->bindValue('offset',$offset,PDO::PARAM_INT);$q->execute();$filas=$q->fetchAll();
cmsCabecera('Secciones de la página de inicio','secciones/index.php','Crea, ordena y publica las secciones visibles del index principal.');
?>
<div class="row"><div class="col-12 grid-margin stretch-card"><div class="card card-rounded"><div class="card-body">
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4"><div><h2 class="card-title card-title-dash mb-1">Contenido de la portada</h2><p class="text-muted mb-0">Las secciones inactivas no se muestran en el sitio público.</p></div><a class="btn btn-primary text-white mt-3 mt-sm-0" href="editar.php"><i class="mdi mdi-plus me-1"></i>Agregar sección</a></div>
<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Nombre</th><th>Clave</th><th>Título</th><th>Estado</th><th>Orden</th><th>Última actualización</th><th>Acciones</th></tr></thead><tbody>
<?php if(!$filas):?><tr><td colspan="7" class="text-center text-muted py-5"><i class="mdi mdi-view-dashboard-outline d-block fs-2"></i>No hay secciones registradas.</td></tr><?php endif;?>
<?php foreach($filas as $f):?><tr><td class="fw-semibold"><?=atenea_e($f['nombre'])?></td><td><code><?=atenea_e($f['clave'])?></code></td><td><?=atenea_e((string)$f['titulo'])?></td><td><span class="badge badge-opacity-<?=$f['activo']?'success':'secondary'?>"><?=$f['activo']?'Activa':'Inactiva'?></span></td><td><?=$f['orden']?></td><td><?=atenea_e($f['updated_at'])?></td><td class="text-nowrap">
<a class="btn btn-sm btn-outline-primary" href="editar.php?id=<?=$f['id']?>" title="Editar"><i class="mdi mdi-pencil"></i> Editar</a>
<a class="btn btn-sm btn-outline-info" href="../elementos/index.php?seccion_id=<?=$f['id']?>" title="Elementos"><i class="mdi mdi-view-grid-plus"></i> Elementos</a>
<form class="d-inline" method="post" action="accion.php"><input type="hidden" name="csrf_token" value="<?=atenea_e(obtenerTokenCsrf())?>"><input type="hidden" name="id" value="<?=$f['id']?>"><input type="hidden" name="accion" value="toggle"><button class="btn btn-sm <?=$f['activo']?'btn-outline-warning':'btn-outline-success'?>"><i class="mdi <?=$f['activo']?'mdi-eye-off':'mdi-eye'?>"></i> <?=$f['activo']?'Desactivar':'Activar'?></button></form>
<button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#eliminar<?=$f['id']?>"><i class="mdi mdi-delete"></i> Eliminar</button>
</td></tr>
<div class="modal fade" id="eliminar<?=$f['id']?>" tabindex="-1" aria-labelledby="tituloEliminar<?=$f['id']?>" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="h5 modal-title" id="tituloEliminar<?=$f['id']?>">Confirmar eliminación</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><div class="modal-body"><p>Esta acción no se puede deshacer. ¿Deseas eliminar este contenido?</p><p class="text-muted mb-0">También se eliminarán los elementos relacionados con <strong><?=atenea_e($f['nombre'])?></strong>.</p></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><form method="post" action="accion.php"><input type="hidden" name="csrf_token" value="<?=atenea_e(obtenerTokenCsrf())?>"><input type="hidden" name="id" value="<?=$f['id']?>"><input type="hidden" name="accion" value="eliminar"><button class="btn btn-danger"><i class="mdi mdi-delete me-1"></i>Sí, eliminar</button></form></div></div></div></div>
<?php endforeach;?></tbody></table></div>
<?php $paginas=max(1,(int)ceil($total/$porPagina));if($paginas>1):?><nav class="mt-4" aria-label="Paginación"><ul class="pagination mb-0"><?php for($i=1;$i<=$paginas;$i++):?><li class="page-item <?=$i===$pagina?'active':''?>"><a class="page-link" href="?pagina=<?=$i?>"><?=$i?></a></li><?php endfor;?></ul></nav><?php endif;?>
</div></div></div></div>
<?php cmsPie(); ?>

