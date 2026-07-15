<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/includes/cms.php';
exigirPermiso('audit.view');

$usuarioId=cmsId($_GET['usuario_id']??0);
if(!$usuarioId){http_response_code(400);echo '<div class="alert alert-danger">El usuario indicado no es valido.</div>';exit;}
$pdo=obtenerConexion();
$q=$pdo->prepare('SELECT id,nombre,apellido,nombre_usuario,correo,rol,estado,deleted_at FROM usuarios WHERE id=:id LIMIT 1');$q->execute(['id'=>$usuarioId]);$usuario=$q->fetch();
if(!$usuario){http_response_code(404);echo '<div class="alert alert-warning">El usuario ya no existe.</div>';exit;}

$pagina=max(1,(int)($_GET['activity_page']??1));$limite=20;
$tipo=mb_substr(trim((string)($_GET['tipo']??'')),0,100);$modulo=mb_substr(trim((string)($_GET['modulo']??'')),0,80);
$desde=(string)($_GET['desde']??'');$hasta=(string)($_GET['hasta']??'');
$scope=' INNER JOIN (SELECT id FROM audit_logs WHERE target_user_id=:scope_target UNION SELECT id FROM audit_logs WHERE actor_user_id=:scope_actor) scoped ON scoped.id=a.id';
$where=[];$params=['scope_target'=>$usuarioId,'scope_actor'=>$usuarioId];
if($tipo!==''){$where[]='a.event_type=:tipo';$params['tipo']=$tipo;}
if($modulo!==''){$where[]='a.module=:modulo';$params['modulo']=$modulo;}
if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$desde)){$where[]='a.created_at>=:desde';$params['desde']=$desde.' 00:00:00';}
if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$hasta)){$where[]='a.created_at<=:hasta';$params['hasta']=$hasta.' 23:59:59';}
$filtro=$where?' WHERE '.implode(' AND ',$where):'';
$q=$pdo->prepare('SELECT COUNT(*) FROM audit_logs a'.$scope.$filtro);$q->execute($params);$total=(int)$q->fetchColumn();
$q=$pdo->prepare('SELECT a.id,a.created_at,a.event_type,a.action,a.module,a.description,a.ip_address,a.result,a.actor_user_id,CONCAT(actor.nombre," ",actor.apellido) actor_nombre,actor.rol actor_rol FROM audit_logs a'.$scope.' LEFT JOIN usuarios actor ON actor.id=a.actor_user_id'.$filtro.' ORDER BY a.created_at DESC,a.id DESC LIMIT '.$limite.' OFFSET '.(($pagina-1)*$limite));$q->execute($params);$eventos=$q->fetchAll();
$scopeOpciones=' INNER JOIN (SELECT id FROM audit_logs WHERE target_user_id=:option_target UNION SELECT id FROM audit_logs WHERE actor_user_id=:option_actor) scoped ON scoped.id=a.id';
$opciones=['option_target'=>$usuarioId,'option_actor'=>$usuarioId];
$q=$pdo->prepare('SELECT DISTINCT a.event_type FROM audit_logs a'.$scopeOpciones.' ORDER BY a.event_type LIMIT 200');$q->execute($opciones);$tipos=$q->fetchAll(PDO::FETCH_COLUMN);
$q=$pdo->prepare('SELECT DISTINCT a.module FROM audit_logs a'.$scopeOpciones.' ORDER BY a.module LIMIT 100');$q->execute($opciones);$modulos=$q->fetchAll(PDO::FETCH_COLUMN);
$paginas=max(1,(int)ceil($total/$limite));
?>
<div class="card card-rounded"><div class="card-body">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><div><h2 class="h5 mb-1">Actividad de <?= atenea_e(trim((string)$usuario['nombre'].' '.(string)$usuario['apellido'])) ?></h2><span class="text-muted">@<?= atenea_e((string)$usuario['nombre_usuario']) ?> · <?= atenea_e((string)$usuario['correo']) ?> · <?= atenea_e(etiquetaRol((string)$usuario['rol'])) ?></span></div><span class="badge badge-opacity-<?= $usuario['estado']==='activo'&&!$usuario['deleted_at']?'success':'secondary' ?>"><?= $usuario['deleted_at']?'Eliminado/inactivo':atenea_e(ucfirst((string)$usuario['estado'])) ?></span></div>
  <form id="filtros-actividad" class="row g-2 mb-4"><input type="hidden" name="usuario_id" value="<?= $usuarioId ?>"><div class="col-lg-3"><label class="form-label">Desde</label><input class="form-control" type="date" name="desde" value="<?= atenea_e($desde) ?>"></div><div class="col-lg-3"><label class="form-label">Hasta</label><input class="form-control" type="date" name="hasta" value="<?= atenea_e($hasta) ?>"></div><div class="col-lg-2"><label class="form-label">Modulo</label><select class="form-select" name="modulo"><option value="">Todos</option><?php foreach($modulos as $op):?><option value="<?= atenea_e((string)$op) ?>" <?= $modulo===$op?'selected':'' ?>><?= atenea_e((string)$op) ?></option><?php endforeach;?></select></div><div class="col-lg-3"><label class="form-label">Accion/evento</label><select class="form-select" name="tipo"><option value="">Todos</option><?php foreach($tipos as $op):?><option value="<?= atenea_e((string)$op) ?>" <?= $tipo===$op?'selected':'' ?>><?= atenea_e((string)$op) ?></option><?php endforeach;?></select></div><div class="col-lg-1 d-flex align-items-end"><button class="btn btn-primary w-100" aria-label="Aplicar filtros">Filtrar</button></div></form>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Fecha y hora</th><th>Accion</th><th>Modulo</th><th>Descripcion</th><th>IP</th><th>Resultado</th><th>Responsable</th></tr></thead><tbody><?php foreach($eventos as $evento):?><tr><td><?= date('d/m/Y H:i:s',strtotime((string)$evento['created_at'])) ?></td><td><?= atenea_e((string)$evento['event_type']) ?><br><small class="text-muted"><?= atenea_e((string)$evento['action']) ?></small></td><td><?= atenea_e((string)$evento['module']) ?></td><td><?= atenea_e((string)$evento['description']) ?></td><td><?= atenea_e((string)($evento['ip_address']?:'No registrada')) ?></td><td><span class="badge badge-opacity-<?= $evento['result']==='success'?'success':'danger' ?>"><?= atenea_e((string)$evento['result']) ?></span></td><td><?= atenea_e((string)($evento['actor_nombre']?:'Sistema')) ?><?= $evento['actor_rol']==='admin'?'<br><small class="text-muted">Administrador</small>':'' ?></td></tr><?php endforeach;?><?php if(!$eventos):?><tr><td colspan="7" class="text-center text-muted py-5">No hay actividad para los filtros seleccionados.</td></tr><?php endif;?></tbody></table></div>
  <?php if($paginas>1):?><nav aria-label="Paginacion de actividad"><ul class="pagination justify-content-end mb-0"><?php for($i=1;$i<=$paginas;$i++):?><li class="page-item <?= $i===$pagina?'active':'' ?>"><a class="page-link" href="actividad.php?<?= atenea_e(http_build_query(['usuario_id'=>$usuarioId,'desde'=>$desde,'hasta'=>$hasta,'modulo'=>$modulo,'tipo'=>$tipo,'activity_page'=>$i])) ?>"><?= $i ?></a></li><?php endfor;?></ul></nav><?php endif;?>
</div></div>
