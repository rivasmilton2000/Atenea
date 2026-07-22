<?php
declare(strict_types=1);
require_once __DIR__ . '/_categorias.php';

$pdo = obtenerConexion();
$buscar = trim((string)($_GET['q'] ?? ''));
$estado = in_array($_GET['estado'] ?? '', ['activo','inactivo'], true) ? (string)$_GET['estado'] : '';
$orden = in_array($_GET['orden'] ?? '', ['nombre','estado','creacion','actualizacion'], true) ? (string)$_GET['orden'] : 'nombre';
$direccion = strtolower((string)($_GET['direccion'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
$pagina = max(1, cmsId($_GET['pagina'] ?? 1));
$porPagina = 10;
$where = ['c.eliminado_at IS NULL'];
$parametros = [];
if ($buscar !== '') { $where[] = '(c.nombre LIKE :buscar_nombre OR c.slug LIKE :buscar_slug OR c.descripcion LIKE :buscar_descripcion)'; $parametros['buscar_nombre'] = '%' . $buscar . '%'; $parametros['buscar_slug'] = '%' . $buscar . '%'; $parametros['buscar_descripcion'] = '%' . $buscar . '%'; }
if ($estado === 'activo') $where[] = 'c.activo=1';
if ($estado === 'inactivo') $where[] = 'c.activo=0';
$whereSql = implode(' AND ', $where);
$consulta = $pdo->prepare("SELECT COUNT(*) FROM categorias_producto c WHERE {$whereSql}");
$consulta->execute($parametros);
$total = (int)$consulta->fetchColumn();
$paginas = max(1, (int)ceil($total / $porPagina));
$pagina = min($pagina, $paginas);
$offset = ($pagina - 1) * $porPagina;
$ordenSql = match($orden) { 'estado' => 'c.activo', 'creacion' => 'c.created_at', 'actualizacion' => 'c.updated_at', default => 'c.nombre' };
$sql = "SELECT c.*,COUNT(p.id) productos FROM categorias_producto c LEFT JOIN productos p ON p.categoria_id=c.id AND p.eliminado_at IS NULL WHERE {$whereSql} GROUP BY c.id ORDER BY {$ordenSql} {$direccion},c.id ASC LIMIT :limite OFFSET :offset";
$consulta = $pdo->prepare($sql);
foreach ($parametros as $clave => $valor) $consulta->bindValue($clave, $valor);
$consulta->bindValue('limite', $porPagina, PDO::PARAM_INT);
$consulta->bindValue('offset', $offset, PDO::PARAM_INT);
$consulta->execute();
$categorias = $consulta->fetchAll();
$destinos = $pdo->query('SELECT id,nombre FROM categorias_producto WHERE activo=1 AND eliminado_at IS NULL ORDER BY nombre')->fetchAll();
$queryBase = ['q'=>$buscar,'estado'=>$estado,'orden'=>$orden,'direccion'=>strtolower($direccion)];
cmsCabecera('Categorías de productos','categorias/index.php','Organiza el catálogo y controla la relación entre categorías y productos.');
?>
<div class="card card-rounded"><div class="card-body">
  <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
    <form class="row g-2 flex-grow-1" method="get">
      <div class="col-md-4"><label class="visually-hidden" for="buscarCategoria">Buscar</label><input class="form-control" id="buscarCategoria" name="q" value="<?=atenea_e($buscar)?>" placeholder="Buscar nombre, slug o descripción"></div>
      <div class="col-md-2"><label class="visually-hidden" for="estadoCategoria">Estado</label><select class="form-select" id="estadoCategoria" name="estado"><option value="">Todos los estados</option><option value="activo" <?=$estado==='activo'?'selected':''?>>Activas</option><option value="inactivo" <?=$estado==='inactivo'?'selected':''?>>Inactivas</option></select></div>
      <div class="col-md-2"><label class="visually-hidden" for="ordenCategoria">Ordenar</label><select class="form-select" id="ordenCategoria" name="orden"><option value="nombre" <?=$orden==='nombre'?'selected':''?>>Nombre</option><option value="estado" <?=$orden==='estado'?'selected':''?>>Estado</option><option value="creacion" <?=$orden==='creacion'?'selected':''?>>Creación</option><option value="actualizacion" <?=$orden==='actualizacion'?'selected':''?>>Actualización</option></select></div>
      <div class="col-md-2"><label class="visually-hidden" for="direccionCategoria">Dirección</label><select class="form-select" id="direccionCategoria" name="direccion"><option value="asc" <?=$direccion==='ASC'?'selected':''?>>Ascendente</option><option value="desc" <?=$direccion==='DESC'?'selected':''?>>Descendente</option></select></div>
      <div class="col-md-2 d-flex gap-2"><button class="btn btn-outline-primary flex-grow-1" type="submit">Filtrar</button><?php if($buscar!==''||$estado!==''):?><a class="btn btn-light" href="index.php" aria-label="Limpiar filtros"><i class="mdi mdi-close"></i></a><?php endif;?></div>
    </form>
    <a class="btn btn-primary text-white flex-shrink-0" href="editar.php"><i class="mdi mdi-plus me-1"></i>Nueva categoría</a>
  </div>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Categoría</th><th>Slug</th><th>Productos</th><th>Estado</th><th>Creación</th><th>Actualización</th><th>Acciones</th></tr></thead><tbody>
  <?php foreach($categorias as $categoria): ?><tr><td><div class="d-flex align-items-center"><?php if($categoria['imagen']):?><img class="img-sm rounded me-3" src="<?=rutaImagenContenido((string)$categoria['imagen'],'img/atenea-logo.png')?>" alt="Imagen de <?=atenea_e((string)$categoria['nombre'])?>"><?php else:?><span class="img-sm rounded me-3 bg-primary text-white d-inline-flex align-items-center justify-content-center"><i class="mdi mdi-tag"></i></span><?php endif;?><div><strong><?=atenea_e((string)$categoria['nombre'])?></strong><?php if($categoria['descripcion']):?><small class="d-block text-muted text-truncate" style="max-width:260px"><?=atenea_e((string)$categoria['descripcion'])?></small><?php endif;?></div></div></td><td><code><?=atenea_e((string)$categoria['slug'])?></code></td><td><span class="badge badge-opacity-info"><?=(int)$categoria['productos']?></span></td><td><span class="badge badge-opacity-<?=$categoria['activo']?'success':'secondary'?>"><?=$categoria['activo']?'Activa':'Inactiva'?></span></td><td><?=date('d/m/Y',strtotime((string)$categoria['created_at']))?></td><td><?=date('d/m/Y H:i',strtotime((string)$categoria['updated_at']))?></td><td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="editar.php?id=<?=(int)$categoria['id']?>"><i class="mdi mdi-pencil"></i> Editar</a> <form class="d-inline" method="post" action="accion.php"><input type="hidden" name="csrf_token" value="<?=atenea_e(obtenerTokenCsrf())?>"><input type="hidden" name="id" value="<?=(int)$categoria['id']?>"><button class="btn btn-sm <?=$categoria['activo']?'btn-outline-warning':'btn-outline-success'?>" name="accion" value="toggle"><i class="mdi <?=$categoria['activo']?'mdi-eye-off':'mdi-eye'?>"></i> <?=$categoria['activo']?'Desactivar':'Activar'?></button></form> <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#eliminarCategoria<?=(int)$categoria['id']?>"><i class="mdi mdi-delete"></i> Eliminar</button></td></tr>
  <div class="modal fade" id="eliminarCategoria<?=(int)$categoria['id']?>" tabindex="-1" aria-labelledby="tituloEliminar<?=(int)$categoria['id']?>" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title h5" id="tituloEliminar<?=(int)$categoria['id']?>">Eliminar <?=atenea_e((string)$categoria['nombre'])?></h2><button class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><div class="modal-body"><?php if((int)$categoria['productos']===0):?><p>Esta categoría no tiene productos asociados. Se eliminará de los listados conservando su registro de auditoría.</p><?php else:?><div class="alert alert-warning">La categoría tiene <?=(int)$categoria['productos']?> producto(s). No puede eliminarse sin reasignarlos.</div><p>Selecciona otra categoría activa para trasladar los productos o desactívala sin modificar sus relaciones.</p><?php endif;?></div><div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><?php if((int)$categoria['productos']===0):?><form method="post" action="accion.php"><input type="hidden" name="csrf_token" value="<?=atenea_e(obtenerTokenCsrf())?>"><input type="hidden" name="id" value="<?=(int)$categoria['id']?>"><button class="btn btn-danger" name="accion" value="eliminar">Sí, eliminar</button></form><?php else:?><form method="post" action="accion.php" class="w-100"><input type="hidden" name="csrf_token" value="<?=atenea_e(obtenerTokenCsrf())?>"><input type="hidden" name="id" value="<?=(int)$categoria['id']?>"><label class="form-label" for="destino<?=(int)$categoria['id']?>">Categoría de destino</label><select class="form-select mb-3" id="destino<?=(int)$categoria['id']?>" name="destino_id" required><option value="">Selecciona una categoría</option><?php foreach($destinos as $destino):if((int)$destino['id']===(int)$categoria['id'])continue;?><option value="<?=(int)$destino['id']?>"><?=atenea_e((string)$destino['nombre'])?></option><?php endforeach;?></select><div class="d-flex justify-content-end gap-2"><button class="btn btn-outline-warning" name="accion" value="desactivar">Solo desactivar</button><button class="btn btn-danger" name="accion" value="reasignar" <?=(count($destinos)-($categoria['activo']?1:0))<1?'disabled':''?>>Reasignar y eliminar</button></div></form><?php endif;?></div></div></div></div>
  <?php endforeach; ?><?php if(!$categorias):?><tr><td colspan="7" class="text-center text-muted py-5"><i class="mdi mdi-tag-outline d-block fs-2"></i>No hay categorías que coincidan con los filtros.</td></tr><?php endif;?></tbody></table></div>
  <?php if($paginas>1):?><nav class="mt-4" aria-label="Paginación de categorías"><ul class="pagination mb-0"><?php for($i=1;$i<=$paginas;$i++):?><li class="page-item <?=$i===$pagina?'active':''?>"><a class="page-link" href="?<?=http_build_query($queryBase+['pagina'=>$i])?>"><?=$i?></a></li><?php endfor;?></ul></nav><?php endif;?>
</div></div>
<?php cmsPie(); ?>
