<?php
declare(strict_types=1);
$activo=(string)($GLOBALS['portal_estudiante_activo']??'inicio');
$item=static fn(string $clave):string=>$activo===$clave?' active':'';
?>
<nav class="sidebar sidebar-offcanvas" id="sidebar" data-active-managed="server" aria-label="Navegación del estudiante">
  <ul class="nav">
    <li class="nav-item nav-category">Aula virtual</li>
    <li class="nav-item<?=$item('inicio')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/index.php')?>"><i class="mdi mdi-home-outline menu-icon"></i><span class="menu-title">Inicio</span></a></li>
    <li class="nav-item<?=$item('clase')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/clase.php')?>"><i class="mdi mdi-account-group-outline menu-icon"></i><span class="menu-title">Mi clase</span></a></li>
    <li class="nav-item<?=$item('cursos')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/cursos.php')?>"><i class="mdi mdi-school-outline menu-icon"></i><span class="menu-title">Capacitaciones</span></a></li>
    <li class="nav-item<?=$item('contenidos')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/contenidos.php')?>"><i class="mdi mdi-book-open-page-variant-outline menu-icon"></i><span class="menu-title">Contenidos</span></a></li>
    <li class="nav-item<?=$item('videos')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/videos.php')?>"><i class="mdi mdi-video-outline menu-icon"></i><span class="menu-title">Videos</span></a></li>
    <li class="nav-item nav-category">Actividades</li>
    <li class="nav-item<?=$item('tareas')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/tareas.php')?>"><i class="mdi mdi-clipboard-text-outline menu-icon"></i><span class="menu-title">Tareas</span></a></li>
    <li class="nav-item<?=$item('evaluaciones')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/evaluaciones.php')?>"><i class="mdi mdi-file-document-edit-outline menu-icon"></i><span class="menu-title">Evaluaciones</span></a></li>
    <li class="nav-item<?=$item('calificaciones')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/calificaciones.php')?>"><i class="mdi mdi-chart-box-outline menu-icon"></i><span class="menu-title">Calificaciones</span></a></li>
    <li class="nav-item<?=$item('record')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/record-academico.php')?>"><i class="mdi mdi-chart-timeline-variant menu-icon"></i><span class="menu-title">Récord académico</span></a></li>
    <li class="nav-item nav-category">Comunicación</li>
    <li class="nav-item<?=$item('comunicaciones')?>"><a class="nav-link" href="<?=atenea_url('src/comunicaciones/chat.php')?>"><i class="mdi mdi-message-text-outline menu-icon"></i><span class="menu-title">Mensajes</span><?php if($mensajesNoLeidos):?><span class="badge badge-opacity-warning ms-auto"><?=$mensajesNoLeidos?></span><?php endif;?></a></li>
    <li class="nav-item<?=$item('calendario')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/calendario.php')?>"><i class="mdi mdi-calendar-month-outline menu-icon"></i><span class="menu-title">Calendario</span></a></li>
    <li class="nav-item<?=$item('notificaciones')?>"><a class="nav-link" href="<?=atenea_url('src/notificaciones/index.php')?>"><i class="mdi mdi-bell-outline menu-icon"></i><span class="menu-title">Notificaciones</span><?php if($notificacionesPortal['no_leidas']??0):?><span class="badge badge-opacity-danger ms-auto" data-atenea-notification-count><?=(int)$notificacionesPortal['no_leidas']?></span><?php endif;?></a></li>
    <li class="nav-item<?=$item('avisos')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/avisos.php')?>"><i class="mdi mdi-bullhorn-outline menu-icon"></i><span class="menu-title">Avisos</span><?php if($datos['avisos_pendientes']??0):?><span class="badge badge-opacity-warning ms-auto"><?=(int)$datos['avisos_pendientes']?></span><?php endif;?></a></li>
    <li class="nav-item<?=$item('soporte')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/soporte.php')?>"><i class="mdi mdi-lifebuoy menu-icon"></i><span class="menu-title">Soporte</span></a></li>
    <li class="nav-item nav-category">Mi cuenta</li>
    <li class="nav-item<?=$item('pedidos')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/pedidos.php')?>"><i class="mdi mdi-cart-outline menu-icon"></i><span class="menu-title">Compras</span></a></li>
    <li class="nav-item<?=$item('facturas')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/facturas.php')?>"><i class="mdi mdi-receipt menu-icon"></i><span class="menu-title">Facturas</span></a></li>
    <li class="nav-item<?=$item('certificados')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/certificados.php')?>"><i class="mdi mdi-certificate-outline menu-icon"></i><span class="menu-title">Certificaciones</span></a></li>
    <li class="nav-item<?=$item('carrito')?>"><a class="nav-link" href="<?=atenea_url('src/carrito/index.php')?>"><i class="mdi mdi-cart-plus menu-icon"></i><span class="menu-title">Carrito</span><?php if($cantidadCarrito):?><span class="badge badge-opacity-primary ms-auto"><?=$cantidadCarrito?></span><?php endif;?></a></li>
    <li class="nav-item<?=$item('direcciones')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/direcciones.php')?>"><i class="mdi mdi-map-marker-outline menu-icon"></i><span class="menu-title">Direcciones</span></a></li>
    <li class="nav-item<?=$item('perfil')?>"><a class="nav-link" href="<?=atenea_url('src/estudiantes/perfil.php')?>"><i class="mdi mdi-account-outline menu-icon"></i><span class="menu-title">Mi perfil</span></a></li>
    <li class="nav-item"><a class="nav-link" href="<?=atenea_url('index.php')?>"><i class="mdi mdi-web menu-icon"></i><span class="menu-title">Volver al sitio</span></a></li>
    <li class="nav-item"><a class="nav-link" data-atenea-confirm="logout" href="<?=atenea_url('src/login/logout.php')?>"><i class="mdi mdi-logout menu-icon"></i><span class="menu-title">Cerrar sesión</span></a></li>
  </ul>
</nav>
