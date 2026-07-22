<?php
require_once __DIR__.'/../_auth_guard.php';
$activo=(string)($GLOBALS['atenea_dashboard_active']??'panel');
$modo=modoHibridoActualAtenea();
$puede=static fn(string $p):bool=>usuarioTienePermiso($p);
?>
<nav class="sidebar sidebar-offcanvas" id="sidebar" data-active-managed="server" aria-label="Navegación de administrador docente">
  <ul class="nav">
    <li class="nav-item <?=$activo==='panel'?'active':''?>"><a class="nav-link" href="<?=atenea_url('src/administador_docente/dashboard/index.php')?>"><i class="mdi mdi-grid-large menu-icon"></i><span class="menu-title">Inicio híbrido</span></a></li>
    <?php if($modo==='admin'):?>
      <li class="nav-item nav-category">Modo Administración</li>
      <?php if($puede('dashboard.view')):?><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/dashboard/index.php')?>"><i class="mdi mdi-view-dashboard-outline menu-icon"></i><span class="menu-title">Dashboard</span></a></li><?php endif;?>
      <?php if($puede('users.view')):?><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/dashboard/usuarios/index.php')?>"><i class="mdi mdi-account-multiple-outline menu-icon"></i><span class="menu-title">Usuarios</span></a></li><?php endif;?>
      <?php if($puede('website.view')):?><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/dashboard/website/editor.php')?>"><i class="mdi mdi-monitor-edit menu-icon"></i><span class="menu-title">Website</span></a></li><?php endif;?>
      <?php if($puede('products.view')):?><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/dashboard/productos/index.php')?>"><i class="mdi mdi-package-variant-closed menu-icon"></i><span class="menu-title">Productos</span></a></li><?php endif;?>
      <?php if($puede('training.view')):?><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/dashboard/capacitaciones/index.php')?>"><i class="mdi mdi-school-outline menu-icon"></i><span class="menu-title">Capacitaciones</span></a></li><?php endif;?>
      <?php if($puede('orders.view')):?><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/dashboard/pedidos/index.php')?>"><i class="mdi mdi-cart-outline menu-icon"></i><span class="menu-title">Compras</span></a></li><?php endif;?>
      <?php if($puede('communications.view')):?><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/comunicaciones/correo.php?vista=entrada')?>"><i class="mdi mdi-email-outline menu-icon"></i><span class="menu-title">Correos</span></a></li><?php endif;?>
      <?php if($puede('newsletter.view')):?><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/dashboard/newsletter/index.php')?>"><i class="mdi mdi-email-newsletter menu-icon"></i><span class="menu-title">Boletín</span></a></li><?php endif;?>
      <?php if($puede('notifications.view')):?><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/dashboard/notificaciones/index.php')?>"><i class="mdi mdi-bell-outline menu-icon"></i><span class="menu-title">Notificaciones</span></a></li><?php endif;?>
      <?php if($puede('audit.view')):?><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/dashboard/bitacora/index.php')?>"><i class="mdi mdi-clipboard-text-clock-outline menu-icon"></i><span class="menu-title">Bitácora limitada</span></a></li><?php endif;?>
    <?php elseif($modo==='docente'):?>
      <li class="nav-item nav-category">Modo Docente</li>
      <?php foreach([
        ['academic.courses.view','cursos.php','Clases asignadas','mdi-school-outline'],
        ['academic.students.view','estudiantes.php','Estudiantes','mdi-account-group-outline'],
        ['academic.content.manage','contenidos.php','Contenidos','mdi-book-open-page-variant-outline'],
        ['academic.tasks.manage','tareas.php','Tareas','mdi-clipboard-text-outline'],
        ['academic.evaluations.manage','evaluaciones.php','Evaluaciones','mdi-file-document-edit-outline'],
        ['academic.grades.manage','calificaciones.php','Calificaciones','mdi-chart-box-outline'],
        ['academic.calendar.view','calendario.php','Calendario','mdi-calendar-month-outline'],
        ['academic.tracking.view','progreso.php','Seguimiento','mdi-chart-timeline-variant'],
      ] as $item):if(!$puede($item[0]))continue;?><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/docente/'.$item[1])?>"><i class="mdi <?=$item[3]?> menu-icon"></i><span class="menu-title"><?=atenea_e($item[2])?></span></a></li><?php endforeach;?>
      <?php if($puede('academic.communications.send')):?><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/comunicaciones/chat.php')?>"><i class="mdi mdi-message-text-outline menu-icon"></i><span class="menu-title">Mensajes</span></a></li><?php endif;?>
      <?php if($puede('academic.notifications.view')):?><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/notificaciones/index.php')?>"><i class="mdi mdi-bell-outline menu-icon"></i><span class="menu-title">Notificaciones</span></a></li><?php endif;?>
    <?php else:?><li class="nav-item"><span class="nav-link text-muted"><i class="mdi mdi-lock-outline menu-icon"></i><span class="menu-title">Sin modo habilitado</span></span></li><?php endif;?>
    <li class="nav-item nav-category">Cuenta</li>
    <li class="nav-item"><a class="nav-link" href="<?=atenea_url('index.php')?>"><i class="mdi mdi-web menu-icon"></i><span class="menu-title">Volver al sitio</span></a></li>
    <li class="nav-item"><a class="nav-link" data-atenea-confirm="logout" href="<?=atenea_url('src/login/logout.php')?>"><i class="mdi mdi-logout menu-icon"></i><span class="menu-title">Cerrar sesión</span></a></li>
  </ul>
</nav>
