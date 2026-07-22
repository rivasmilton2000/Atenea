<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/cms.php';
exigirPermiso('users.view');

$rol = in_array($_GET['rol'] ?? '', rolesAdministrablesAtenea(), true) ? (string) $_GET['rol'] : '';
$estado = in_array($_GET['estado'] ?? '', ['activo','inactivo','eliminado'], true) ? (string) $_GET['estado'] : '';
$busqueda = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
$pagina = max(1, (int) ($_GET['page'] ?? 1));
$porPagina = 20;
$where = [];
$params = [];
if ($rol !== '') { $where[] = 'rol=:rol'; $params['rol'] = $rol; }
if ($estado === 'eliminado') $where[] = 'deleted_at IS NOT NULL';
elseif ($estado !== '') { $where[] = 'estado=:estado AND deleted_at IS NULL'; $params['estado'] = $estado; }
else $where[] = 'deleted_at IS NULL';
if ($busqueda !== '') {
    $where[] = '(nombre LIKE :q_nombre OR apellido LIKE :q_apellido OR nombre_usuario LIKE :q_usuario OR correo LIKE :q_correo' . (ctype_digit($busqueda) ? ' OR id=:id_busqueda' : '') . ')';
    $valorBusqueda = '%' . $busqueda . '%';
    $params['q_nombre'] = $valorBusqueda;$params['q_apellido'] = $valorBusqueda;$params['q_usuario'] = $valorBusqueda;$params['q_correo'] = $valorBusqueda;
    if (ctype_digit($busqueda)) $params['id_busqueda'] = (int)$busqueda;
}
$filtro = ' WHERE ' . implode(' AND ', $where);
$pdo = obtenerConexion();
$q = $pdo->prepare('SELECT COUNT(*) FROM usuarios' . $filtro); $q->execute($params); $total = (int) $q->fetchColumn();
$q = $pdo->prepare('SELECT id,nombre,apellido,nombre_usuario,correo,rol,es_superadmin,estado,proveedor,google_id,created_at,ultimo_acceso,last_activity_at,deleted_at,foto FROM usuarios' . $filtro . ' ORDER BY created_at DESC,id DESC LIMIT ' . $porPagina . ' OFFSET ' . (($pagina - 1) * $porPagina));
$q->execute($params); $usuarios = $q->fetchAll();
$paginas = max(1, (int) ceil($total / $porPagina));
$titulo = $rol === '' ? 'Usuarios' : match ($rol) { 'usuario' => 'Estudiantes', 'docente' => 'Docentes', 'administracion_docente' => 'Administración_Docente', 'admin' => 'Administradores' };
cmsCabecera($titulo, 'usuarios/index.php', 'Consulta cuentas, actividad, permisos y ciclo de vida sin exponer credenciales.');
?>
<div class="card card-rounded"><div class="card-body">
  <form class="row g-2 mb-4" method="get"><div class="col-md-4"><label class="form-label" for="q">Buscar</label><input class="form-control" id="q" name="q" value="<?= atenea_e($busqueda) ?>" placeholder="Nombre, usuario, correo o ID"></div><div class="col-md-3"><label class="form-label" for="rol">Rol</label><select class="form-select" id="rol" name="rol"><option value="">Todos</option><?php foreach(rolesAdministrablesAtenea() as $opcion):?><option value="<?= $opcion ?>" <?= $rol===$opcion?'selected':'' ?>><?= atenea_e(etiquetaRol($opcion)) ?></option><?php endforeach;?></select></div><div class="col-md-3"><label class="form-label" for="estado">Estado</label><select class="form-select" id="estado" name="estado"><option value="">No eliminados</option><option value="activo" <?= $estado==='activo'?'selected':'' ?>>Activo</option><option value="inactivo" <?= $estado==='inactivo'?'selected':'' ?>>Inactivo</option><option value="eliminado" <?= $estado==='eliminado'?'selected':'' ?>>Eliminado/inactivo</option></select></div><div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filtrar</button></div></form>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Nombre completo</th><th>Usuario</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Ultima actividad</th><th></th></tr></thead><tbody>
  <?php foreach($usuarios as $usuario): ?><tr><td><div class="d-flex align-items-center"><img class="img-sm rounded-circle me-3" src="<?= atenea_e(rutaFotoPerfil($usuario)) ?>" alt=""><strong><?= atenea_e(trim((string)$usuario['nombre'].' '.(string)$usuario['apellido'])) ?></strong></div></td><td><?= atenea_e((string)$usuario['nombre_usuario']) ?><br><small class="text-muted">ID <?= (int)$usuario['id'] ?></small></td><td><?= atenea_e((string)$usuario['correo']) ?></td><td><?= atenea_e(etiquetaRol((string)$usuario['rol'])) ?><?= (int)$usuario['es_superadmin']===1?' / SuperAdmin':'' ?></td><td><?php if($usuario['deleted_at']):?><span class="badge badge-opacity-danger">Eliminado/inactivo</span><?php else:?><span class="badge badge-opacity-<?= $usuario['estado']==='activo'?'success':'secondary' ?>"><?= atenea_e(ucfirst((string)$usuario['estado'])) ?></span><?php endif;?></td><td><?= !empty($usuario['last_activity_at'])?date('d/m/Y H:i',strtotime((string)$usuario['last_activity_at'])):'Sin registro' ?></td><td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="detalle.php?id=<?= (int)$usuario['id'] ?>">Ver detalle</a><?php if(!$usuario['deleted_at']&&$usuario['estado']==='activo'&&$usuario['rol']==='usuario'&&(int)$usuario['id']!==(int)($_SESSION['usuario_id']??0)):?><form class="d-inline" method="post" action="accion.php"><input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>"><input type="hidden" name="accion" value="eliminar_logico"><input type="hidden" name="id" value="<?= (int)$usuario['id'] ?>"><input type="hidden" name="motivo" value="Desactivacion rapida desde el listado administrativo."><button class="btn btn-sm btn-outline-danger" data-atenea-confirm="delete" data-atenea-confirm-title="Desactivar usuario" data-atenea-confirm-message="La cuenta quedara inactiva; sus registros historicos se conservaran.">Desactivar</button></form><?php endif;?></td></tr><?php endforeach;?>
  <?php if(!$usuarios):?><tr><td colspan="7" class="text-center text-muted py-5">No hay cuentas para este filtro.</td></tr><?php endif;?></tbody></table></div>
  <?php if($paginas>1):?><nav aria-label="Paginacion"><ul class="pagination justify-content-end mb-0"><?php for($i=1;$i<=$paginas;$i++):?><li class="page-item <?= $i===$pagina?'active':'' ?>"><a class="page-link" href="?<?= http_build_query(['rol'=>$rol,'estado'=>$estado,'q'=>$busqueda,'page'=>$i]) ?>"><?= $i ?></a></li><?php endfor;?></ul></nav><?php endif;?>
</div></div>
<?php cmsPie(); ?>
