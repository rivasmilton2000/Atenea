<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/includes/cms.php';
$rol = in_array($_GET['rol'] ?? '', ['usuario','docente','admin'], true) ? (string)$_GET['rol'] : '';
$pdo = obtenerConexion();
$sql = 'SELECT id,nombre,apellido,correo,rol,estado,proveedor,google_id,created_at,foto FROM usuarios';
$parametros=[];
if($rol!==''){$sql.=' WHERE rol=:rol';$parametros['rol']=$rol;}
$sql.=' ORDER BY created_at DESC,id DESC';
$consulta=$pdo->prepare($sql);$consulta->execute($parametros);$usuarios=$consulta->fetchAll();
$titulo=$rol===''?'Usuarios':match($rol){'usuario'=>'Estudiantes','docente'=>'Docentes','admin'=>'Administradores'};
cmsCabecera($titulo,'usuarios/index.php','Consulta las cuentas y sus permisos registrados en Atenea.');
?>
<div class="row"><div class="col-12 grid-margin stretch-card"><div class="card card-rounded"><div class="card-body">
<div class="d-flex flex-wrap gap-2 mb-4"><a class="btn btn-sm <?=$rol===''?'btn-primary':'btn-outline-primary'?>" href="index.php">Todos</a><a class="btn btn-sm <?=$rol==='usuario'?'btn-primary':'btn-outline-primary'?>" href="?rol=usuario">Estudiantes</a><a class="btn btn-sm <?=$rol==='docente'?'btn-primary':'btn-outline-primary'?>" href="?rol=docente">Docentes</a><a class="btn btn-sm <?=$rol==='admin'?'btn-primary':'btn-outline-primary'?>" href="?rol=admin">Administradores</a></div>
<div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Usuario</th><th>Correo</th><th>Rol</th><th>Acceso</th><th>Estado</th><th>Registro</th></tr></thead><tbody>
<?php foreach($usuarios as $usuario):$foto=rutaFotoPerfil($usuario);?><tr><td><div class="d-flex align-items-center"><img class="img-sm rounded-circle me-3" src="<?=atenea_e($foto)?>" alt="Foto de <?=atenea_e((string)$usuario['nombre'])?>"><span class="fw-semibold"><?=atenea_e(trim((string)$usuario['nombre'].' '.(string)$usuario['apellido']))?></span></div></td><td><?=atenea_e((string)$usuario['correo'])?></td><td><?=atenea_e(etiquetaRol((string)$usuario['rol']))?></td><td><?=!empty($usuario['google_id'])?'Google y cuenta local':atenea_e(ucfirst((string)$usuario['proveedor']))?></td><td><span class="badge badge-opacity-<?=$usuario['estado']==='activo'?'success':'secondary'?>"><?=atenea_e(ucfirst((string)$usuario['estado']))?></span></td><td><?=date('d/m/Y',strtotime((string)$usuario['created_at']))?></td></tr><?php endforeach;?>
<?php if(!$usuarios):?><tr><td colspan="6" class="text-center text-muted py-5">No hay cuentas para este filtro.</td></tr><?php endif;?></tbody></table></div>
</div></div></div></div>
<?php cmsPie(); ?>
