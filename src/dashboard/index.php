<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/cms.php';

function contarPanel(PDO $pdo, string $sql, array $parametros = []): int
{
    try { $consulta=$pdo->prepare($sql); $consulta->execute($parametros); return (int)$consulta->fetchColumn(); }
    catch(Throwable $e) { error_log('Panel Atenea: '.$e->getMessage()); return 0; }
}
$pdo=obtenerConexion();
$metricas=[
 ['Total de usuarios',contarPanel($pdo,'SELECT COUNT(*) FROM usuarios'),'mdi-account-group','primary'],
 ['Estudiantes',contarPanel($pdo,"SELECT COUNT(*) FROM usuarios WHERE rol='usuario'"),'mdi-school','success'],
 ['Docentes',contarPanel($pdo,"SELECT COUNT(*) FROM usuarios WHERE rol='docente'"),'mdi-teach','info'],
 ['Administradores',contarPanel($pdo,"SELECT COUNT(*) FROM usuarios WHERE rol='admin'"),'mdi-shield-account','dark'],
 ['Secciones activas',contarPanel($pdo,'SELECT COUNT(*) FROM secciones WHERE activo=1'),'mdi-view-dashboard','warning'],
 ['Elementos publicados',contarPanel($pdo,'SELECT COUNT(*) FROM elementos_seccion WHERE activo=1'),'mdi-card-text','danger'],
 ['Capacitaciones publicadas',contarPanel($pdo,"SELECT COUNT(*) FROM elementos_seccion e JOIN secciones s ON s.id=e.seccion_id WHERE s.clave='capacitaciones' AND s.activo=1 AND e.activo=1"),'mdi-book-open-page-variant','primary'],
 ['Noticias publicadas',contarPanel($pdo,"SELECT COUNT(*) FROM elementos_seccion e JOIN secciones s ON s.id=e.seccion_id WHERE s.clave='noticias' AND s.activo=1 AND e.activo=1"),'mdi-newspaper','success'],
];
try{$recientes=$pdo->query('SELECT id,nombre,titulo,activo,updated_at FROM secciones ORDER BY updated_at DESC LIMIT 5')->fetchAll();$capacitacionesId=(int)$pdo->query("SELECT id FROM secciones WHERE clave='capacitaciones' LIMIT 1")->fetchColumn();$noticiasId=(int)$pdo->query("SELECT id FROM secciones WHERE clave='noticias' LIMIT 1")->fetchColumn();}
catch(Throwable $e){error_log($e->getMessage());$recientes=[];$capacitacionesId=$noticiasId=0;}
cmsCabecera('Panel principal','panel','Resumen real del contenido y los usuarios de Atenea.');
?>
<div class="row">
<?php foreach($metricas as [$titulo,$valor,$icono,$color]):?><div class="col-sm-6 col-lg-3 grid-margin stretch-card"><div class="card card-rounded"><div class="card-body"><div class="d-flex align-items-center justify-content-between"><div><p class="statistics-title mb-2"><?=atenea_e($titulo)?></p><h3 class="rate-percentage mb-0"><?=$valor?></h3></div><div class="card-stat-icon bg-<?=$color?>"><i class="mdi <?=atenea_e($icono)?>"></i></div></div></div></div></div><?php endforeach;?>
</div>
<div class="row">
<div class="col-lg-4 grid-margin stretch-card"><div class="card card-rounded"><div class="card-body"><h2 class="card-title card-title-dash">Accesos rápidos</h2><p class="text-muted">Acciones disponibles en los módulos implementados.</p><div class="d-grid gap-2">
<a class="btn btn-primary text-white" href="<?=atenea_url('src/dashboard/secciones/editar.php')?>"><i class="mdi mdi-plus me-2"></i>Nueva sección</a>
<?php if($capacitacionesId):?><a class="btn btn-outline-primary" href="<?=atenea_url('src/dashboard/elementos/editar.php?seccion_id='.$capacitacionesId)?>"><i class="mdi mdi-book-plus me-2"></i>Nueva capacitación</a><?php endif;?>
<?php if($noticiasId):?><a class="btn btn-outline-primary" href="<?=atenea_url('src/dashboard/elementos/editar.php?seccion_id='.$noticiasId)?>"><i class="mdi mdi-newspaper-plus me-2"></i>Nueva noticia</a><?php endif;?>
<a class="btn btn-outline-dark" href="<?=atenea_url('index.php')?>" target="_blank" rel="noopener"><i class="mdi mdi-web me-2"></i>Ver sitio web</a>
</div></div></div></div>
<div class="col-lg-8 grid-margin stretch-card"><div class="card card-rounded"><div class="card-body"><div class="d-flex justify-content-between align-items-center mb-3"><h2 class="card-title card-title-dash mb-0">Secciones actualizadas recientemente</h2><a href="<?=atenea_url('src/dashboard/secciones/index.php')?>">Ver todas</a></div><div class="table-responsive"><table class="table"><thead><tr><th>Nombre</th><th>Título</th><th>Estado</th><th>Actualización</th><th></th></tr></thead><tbody>
<?php if(!$recientes):?><tr><td colspan="5" class="text-center text-muted py-4">No hay datos disponibles.</td></tr><?php endif;?>
<?php foreach($recientes as $fila):?><tr><td><?=atenea_e($fila['nombre'])?></td><td><?=atenea_e((string)$fila['titulo'])?></td><td><span class="badge badge-opacity-<?=$fila['activo']?'success':'secondary'?>"><?=$fila['activo']?'Activa':'Inactiva'?></span></td><td><?=atenea_e($fila['updated_at'])?></td><td><a class="btn btn-sm btn-outline-primary" href="<?=atenea_url('src/dashboard/secciones/editar.php?id='.$fila['id'])?>"><i class="mdi mdi-pencil"></i> Editar</a></td></tr><?php endforeach;?>
</tbody></table></div></div></div></div>
</div>
<?php cmsPie(); ?>

