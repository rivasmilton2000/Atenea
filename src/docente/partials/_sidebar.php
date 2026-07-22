<?php
if(esRolAdministradorDocenteAtenea($_SESSION['usuario_rol']??null)){
  require ATENEA_ROOT.'/src/administador_docente/dashboard/partials/_sidebar.php';
  return;
}
if(!function_exists('docenteUrl'))require_once dirname(__DIR__).'/_layout.php';
require_once dirname(__DIR__,3).'/includes/notificaciones.php';
$activo=$GLOBALS['docenteActivo']??'inicio';
$resumenDocente=notificacionesUsuarioResumen((int)($_SESSION['usuario_id']??0),1);
$grupos=[
  'Aula virtual'=>[
    ['inicio','index.php','mdi-grid-large','Inicio'],
    ['cursos','cursos.php','mdi-book-open-page-variant','Clases asignadas'],
    ['estudiantes','estudiantes.php','mdi-account-multiple-outline','Estudiantes'],
    ['contenidos','contenidos.php','mdi-file-document-edit-outline','Contenidos'],
    ['tareas','tareas.php','mdi-clipboard-check-outline','Tareas'],
    ['evaluaciones','evaluaciones.php','mdi-clipboard-text-outline','Evaluaciones'],
    ['calificaciones','calificaciones.php','mdi-star-circle-outline','Calificaciones'],
  ],
  'Comunicación'=>[
    ['mensajes','src/comunicaciones/chat.php','mdi-message-text-outline','Mensajes'],
    ['calendario','calendario.php','mdi-calendar-month-outline','Calendario'],
    ['notificaciones','src/notificaciones/index.php','mdi-bell-outline','Notificaciones'],
  ],
  'Seguimiento'=>[
    ['entregas','entregas.php','mdi-inbox-arrow-down-outline','Entregas y revisión'],
    ['progreso','progreso.php','mdi-chart-line','Progreso'],
    ['comunicaciones','comunicaciones.php','mdi-email-outline','Comunicaciones académicas'],
  ],
];
?>
<nav class="sidebar sidebar-offcanvas" id="sidebar" aria-label="Navegación docente"><ul class="nav">
<?php foreach($grupos as$grupo=>$items):?><li class="nav-item nav-category"><?=atenea_e($grupo)?></li><?php foreach($items as$i):?><li class="nav-item <?=$activo===$i[0]?'active':''?>"><a class="nav-link" href="<?=str_starts_with($i[1],'src/')?atenea_url($i[1]):docenteUrl($i[1])?>" <?=$activo===$i[0]?'aria-current="page"':''?>><i class="menu-icon mdi <?=$i[2]?>"></i><span class="menu-title"><?=atenea_e($i[3])?></span><?php if($i[0]==='notificaciones'):?><span class="badge bg-danger ms-auto" data-atenea-notification-count <?=$resumenDocente['no_leidas']?'':'hidden'?>><?=(int)$resumenDocente['no_leidas']?></span><?php endif;?></a></li><?php endforeach;?><?php endforeach;?>
<li class="nav-item nav-category">Cuenta</li><li class="nav-item <?=$activo==='perfil'?'active':''?>"><a class="nav-link" href="<?=docenteUrl('perfil.php')?>" <?=$activo==='perfil'?'aria-current="page"':''?>><i class="menu-icon mdi mdi-account-circle-outline"></i><span class="menu-title">Perfil</span></a></li><li class="nav-item"><a class="nav-link" data-atenea-confirm="logout" href="<?=atenea_url('src/login/logout.php')?>"><i class="menu-icon mdi mdi-logout"></i><span class="menu-title">Cerrar sesión</span></a></li></ul></nav>
