<?php
declare(strict_types=1);
require_once __DIR__.'/includes/cms.php';
$pdo=obtenerConexion();$usuario=obtenerUsuarioActual()??[];$modo=modoHibridoActualAtenea();$permisos=permisosHibridosUsuarioAtenea((int)$usuario['id'],$pdo);
$totalHabilitados=count(array_filter($permisos));
cmsCabecera('Administración_Docente','panel','Selecciona un contexto de trabajo. Los menús y rutas se limitan a tus permisos individuales.');
?>
<div class="row g-4 mb-4">
  <div class="col-lg-7"><div class="card card-rounded h-100"><div class="card-body"><div class="d-flex align-items-center gap-3"><img class="rounded-circle" width="72" height="72" style="object-fit:cover" src="<?=atenea_e(rutaFotoPerfil($usuario))?>" alt="Fotografía"><div><span class="badge badge-opacity-primary">Administración_Docente</span><h2 class="h4 mt-2 mb-1"><?=atenea_e(trim(($usuario['nombre']??'').' '.($usuario['apellido']??'')))?></h2><p class="text-muted mb-0">Modo actual: <strong><?=atenea_e($modo==='admin'?'Administración':($modo==='docente'?'Docente':'Sin modo seleccionado'))?></strong></p></div></div></div></div></div>
  <div class="col-lg-5"><div class="card card-rounded h-100"><div class="card-body"><h2 class="h5">Permisos vigentes</h2><p class="display-5 mb-1"><?=$totalHabilitados?></p><p class="text-muted mb-0">Los cambios del administrador principal se aplican inmediatamente.</p></div></div></div>
</div>
<div class="row g-4">
 <?php foreach([['admin','Administración','Gestiona únicamente los módulos administrativos habilitados.','hybrid.admin.access','mdi-shield-account-outline'],['docente','Docente','Trabaja con tus clases y estudiantes asignados.','hybrid.docente.access','mdi-school-outline']] as $opcion):$habilitado=$permisos[$opcion[3]]??false;?>
 <div class="col-md-6"><div class="card card-rounded h-100"><div class="card-body"><i class="mdi <?=$opcion[4]?> display-5 text-primary"></i><h2 class="h4 mt-3">Modo <?=$opcion[1]?></h2><p class="text-muted"><?=$opcion[2]?></p><?php if($habilitado):?><form method="post" action="<?=atenea_url('src/administador_docente/cambiar-modo.php')?>"><input type="hidden" name="csrf_token" value="<?=atenea_e(obtenerTokenCsrf())?>"><input type="hidden" name="modo" value="<?=$opcion[0]?>"><button class="btn btn-primary">Trabajar en <?=$opcion[1]?></button></form><?php else:?><span class="badge badge-opacity-secondary">Deshabilitado por el administrador</span><?php endif;?></div></div></div>
 <?php endforeach;?>
</div>
<?php cmsPie();
