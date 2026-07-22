<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/includes/cms.php';
exigirPermiso('audit.view');

$pdo=obtenerConexion();
$pagina=max(1,(int)($_GET['page']??1));
$limite=20;
$buscar=mb_substr(trim((string)($_GET['q']??'')),0,100);
$rol=in_array($_GET['rol']??'',rolesAdministrablesAtenea(),true)?(string)$_GET['rol']:'';
$estado=in_array($_GET['estado']??'',['activo','inactivo','eliminado'],true)?(string)$_GET['estado']:'';
$where=[];$params=[];
if(($_SESSION['usuario_rol']??'')==='administracion_docente'){$where[]='id=:hybrid_usuario';$params['hybrid_usuario']=(int)$_SESSION['usuario_id'];}
if($buscar!==''){
    $where[]='(nombre LIKE :q_nombre OR apellido LIKE :q_apellido OR nombre_usuario LIKE :q_usuario OR correo LIKE :q_correo OR CAST(rol AS CHAR) LIKE :q_rol'.(ctype_digit($buscar)?' OR id=:id':'').')';
    $valorBusqueda='%'.$buscar.'%';
    $params['q_nombre']=$valorBusqueda;$params['q_apellido']=$valorBusqueda;$params['q_usuario']=$valorBusqueda;$params['q_correo']=$valorBusqueda;$params['q_rol']=$valorBusqueda;
    if(ctype_digit($buscar))$params['id']=(int)$buscar;
}
if($rol!==''){$where[]='rol=:rol';$params['rol']=$rol;}
if($estado==='eliminado')$where[]='deleted_at IS NOT NULL';
elseif($estado!==''){$where[]='estado=:estado AND deleted_at IS NULL';$params['estado']=$estado;}
$filtro=$where?' WHERE '.implode(' AND ',$where):'';
$q=$pdo->prepare('SELECT COUNT(*) FROM usuarios'.$filtro);$q->execute($params);$total=(int)$q->fetchColumn();
$q=$pdo->prepare('SELECT id,nombre,apellido,nombre_usuario,correo,rol,estado,deleted_at FROM usuarios'.$filtro.' ORDER BY nombre,apellido,id LIMIT '.$limite.' OFFSET '.(($pagina-1)*$limite));$q->execute($params);$usuarios=$q->fetchAll();
$paginas=max(1,(int)ceil($total/$limite));
cmsCabecera('Bitacora','bitacora/index.php','Selecciona un usuario para consultar solo su actividad, con filtros y paginacion.');
?>
<div class="card card-rounded mb-4"><div class="card-body">
  <form class="row g-2" method="get">
    <div class="col-lg-5"><label class="form-label" for="q">Buscar usuario</label><input class="form-control" id="q" name="q" value="<?= atenea_e($buscar) ?>" placeholder="Nombre, usuario, correo, rol o ID"></div>
    <div class="col-lg-2"><label class="form-label" for="rol">Rol</label><select class="form-select" id="rol" name="rol"><option value="">Todos</option><?php foreach(rolesAdministrablesAtenea() as $op):?><option value="<?= $op ?>" <?= $rol===$op?'selected':'' ?>><?= atenea_e(etiquetaRol($op)) ?></option><?php endforeach;?></select></div>
    <div class="col-lg-3"><label class="form-label" for="estado">Estado</label><select class="form-select" id="estado" name="estado"><option value="">Todos</option><option value="activo" <?= $estado==='activo'?'selected':'' ?>>Activo</option><option value="inactivo" <?= $estado==='inactivo'?'selected':'' ?>>Inactivo</option><option value="eliminado" <?= $estado==='eliminado'?'selected':'' ?>>Eliminado/inactivo</option></select></div>
    <div class="col-lg-2 d-flex align-items-end"><button class="btn btn-primary w-100">Buscar</button></div>
  </form>
</div></div>
<div class="card card-rounded mb-4"><div class="card-body"><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>ID</th><th>Nombre completo</th><th>Usuario</th><th>Correo</th><th>Rol</th><th>Estado</th><th></th></tr></thead><tbody>
<?php foreach($usuarios as $usuario):?><tr><td><?= (int)$usuario['id'] ?></td><td><?= atenea_e(trim((string)$usuario['nombre'].' '.(string)$usuario['apellido'])) ?></td><td>@<?= atenea_e((string)$usuario['nombre_usuario']) ?></td><td><?= atenea_e((string)$usuario['correo']) ?></td><td><?= atenea_e(etiquetaRol((string)$usuario['rol'])) ?></td><td><span class="badge badge-opacity-<?= $usuario['estado']==='activo'&&!$usuario['deleted_at']?'success':'secondary' ?>"><?= $usuario['deleted_at']?'Eliminado/inactivo':atenea_e(ucfirst((string)$usuario['estado'])) ?></span></td><td><button type="button" class="btn btn-sm btn-outline-primary js-cargar-actividad" data-usuario-id="<?= (int)$usuario['id'] ?>" data-usuario-nombre="<?= atenea_e(trim((string)$usuario['nombre'].' '.(string)$usuario['apellido'])) ?>">Ver actividad</button></td></tr><?php endforeach;?>
<?php if(!$usuarios):?><tr><td colspan="7" class="text-center text-muted py-5">No se encontraron usuarios.</td></tr><?php endif;?></tbody></table></div>
<?php if($paginas>1):?><nav aria-label="Paginacion de usuarios"><ul class="pagination justify-content-end mb-0"><?php for($i=1;$i<=$paginas;$i++):?><li class="page-item <?= $i===$pagina?'active':'' ?>"><a class="page-link" href="?<?= atenea_e(http_build_query(['q'=>$buscar,'rol'=>$rol,'estado'=>$estado,'page'=>$i])) ?>"><?= $i ?></a></li><?php endfor;?></ul></nav><?php endif;?>
</div></div>
<div id="actividad-loader" class="card card-rounded d-none" aria-live="polite"><div class="card-body text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Consultando actividad...</span></div><p class="text-muted mt-3 mb-0">Consultando actividad...</p></div></div>
<section id="actividad-usuario" aria-live="polite"></section>
<script>
document.addEventListener('DOMContentLoaded',function(){
  var destino=document.getElementById('actividad-usuario');var loader=document.getElementById('actividad-loader');
  async function cargar(url){loader.classList.remove('d-none');destino.innerHTML='';try{var respuesta=await fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest'},credentials:'same-origin'});if(!respuesta.ok)throw new Error('HTTP '+respuesta.status);destino.innerHTML=await respuesta.text();destino.scrollIntoView({behavior:'smooth',block:'start'});}catch(e){destino.innerHTML='<div class="alert alert-danger">No fue posible consultar la actividad. Intenta nuevamente.</div>';}finally{loader.classList.add('d-none');}}
  document.addEventListener('click',function(e){var boton=e.target.closest('.js-cargar-actividad');if(!boton)return;cargar('actividad.php?usuario_id='+encodeURIComponent(boton.dataset.usuarioId));});
  document.addEventListener('submit',function(e){var formulario=e.target.closest('#filtros-actividad');if(!formulario)return;e.preventDefault();cargar('actividad.php?'+new URLSearchParams(new FormData(formulario)).toString());});
  document.addEventListener('click',function(e){var enlace=e.target.closest('#actividad-usuario .pagination a');if(!enlace)return;e.preventDefault();cargar(enlace.href);});
});
</script>
<?php cmsPie(); ?>
