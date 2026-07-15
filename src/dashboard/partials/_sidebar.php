<?php
require_once __DIR__ . '/../_auth_guard.php';

$scriptPath = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$dashboardMarker = '/src/dashboard/';
$markerPosition = strpos($scriptPath, $dashboardMarker);
$detectedRoute = $markerPosition === false ? 'panel' : substr($scriptPath, $markerPosition + strlen($dashboardMarker));
$dashboardActive = trim((string) ($dashboardActive ?? $detectedRoute), '/');
if ($dashboardActive === '' || $dashboardActive === 'index.php') $dashboardActive = 'panel';

$inicioAbierto = in_array($dashboardActive, ['secciones/index.php', 'secciones/editar.php', 'elementos/index.php', 'elementos/editar.php', 'hero'], true);
$configAbierta = in_array($dashboardActive, ['configuracion/index.php', 'navbar/index.php', 'navbar/editar.php'], true);
$portalAbierto = str_starts_with($dashboardActive, 'portal-estudiante');
$comercioAbierto = str_starts_with($dashboardActive, 'productos/') || str_starts_with($dashboardActive, 'categorias/') || str_starts_with($dashboardActive, 'pedidos/') || str_starts_with($dashboardActive, 'facturas/');
$operacionesAbiertas = str_starts_with($dashboardActive,'notificaciones/') || str_starts_with($dashboardActive,'comunicaciones/') || str_starts_with($dashboardActive,'errores/');
$usuariosAbierto = str_starts_with($dashboardActive, 'usuarios/');
$bitacoraActiva = str_starts_with($dashboardActive, 'bitacora/');
$rolUsuarios = in_array((string) ($_GET['rol'] ?? ''), ['usuario', 'docente', 'admin'], true) ? (string) $_GET['rol'] : '';
?>
<nav class="sidebar sidebar-offcanvas" id="sidebar" data-active-managed="server" aria-label="Navegación administrativa">
  <ul class="nav">
    <li class="nav-item <?= $dashboardActive === 'panel' ? 'active' : '' ?>">
      <a class="nav-link" href="<?= atenea_url('src/dashboard/index.php') ?>"><i class="mdi mdi-grid-large menu-icon"></i><span class="menu-title">Panel principal</span></a>
    </li>
    <li class="nav-item nav-category">Gestión del sitio web</li>
    <li class="nav-item <?= $inicioAbierto ? 'active' : '' ?>">
      <a class="nav-link <?= $inicioAbierto ? '' : 'collapsed' ?>" data-bs-toggle="collapse" href="#menu-inicio" aria-expanded="<?= $inicioAbierto ? 'true' : 'false' ?>" aria-controls="menu-inicio"><i class="menu-icon mdi mdi-floor-plan"></i><span class="menu-title">Página de inicio</span><i class="menu-arrow"></i></a>
      <div class="collapse <?= $inicioAbierto ? 'show' : '' ?>" id="menu-inicio" data-bs-parent="#sidebar"><ul class="nav flex-column sub-menu">
        <li class="nav-item"><a class="nav-link <?= str_starts_with($dashboardActive, 'secciones/') ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/secciones/index.php') ?>">Secciones</a></li>
        <li class="nav-item"><a class="nav-link <?= str_starts_with($dashboardActive, 'elementos/') ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/elementos/index.php') ?>">Elementos</a></li>
        <li class="nav-item"><a class="nav-link <?= $dashboardActive === 'hero' ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/secciones/index.php') ?>">Hero principal</a></li>
      </ul></div>
    </li>
    <li class="nav-item <?= $configAbierta ? 'active' : '' ?>">
      <a class="nav-link <?= $configAbierta ? '' : 'collapsed' ?>" data-bs-toggle="collapse" href="#menu-configuracion" aria-expanded="<?= $configAbierta ? 'true' : 'false' ?>" aria-controls="menu-configuracion"><i class="menu-icon mdi mdi-card-text-outline"></i><span class="menu-title">Configuración</span><i class="menu-arrow"></i></a>
      <div class="collapse <?= $configAbierta ? 'show' : '' ?>" id="menu-configuracion" data-bs-parent="#sidebar"><ul class="nav flex-column sub-menu">
        <li class="nav-item"><a class="nav-link <?= $dashboardActive === 'configuracion/index.php' ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/configuracion/index.php') ?>">Configuración general</a></li>
        <li class="nav-item"><a class="nav-link <?= $dashboardActive === 'configuracion/dte.php' ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/configuracion/dte.php') ?>">Configuración DTE</a></li>
        <li class="nav-item"><a class="nav-link <?= str_starts_with($dashboardActive, 'navbar/') ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/navbar/index.php') ?>">Barra y menú</a></li>
      </ul></div>
    </li>
    <li class="nav-item <?= $portalAbierto ? 'active' : '' ?>">
      <a class="nav-link <?= $portalAbierto ? '' : 'collapsed' ?>" data-bs-toggle="collapse" href="#menu-portal" aria-expanded="<?= $portalAbierto ? 'true' : 'false' ?>" aria-controls="menu-portal"><i class="menu-icon mdi mdi-school-outline"></i><span class="menu-title">Portal estudiante</span><i class="menu-arrow"></i></a>
      <div class="collapse <?= $portalAbierto ? 'show' : '' ?>" id="menu-portal" data-bs-parent="#sidebar"><ul class="nav flex-column sub-menu"><li class="nav-item"><a class="nav-link <?= $portalAbierto ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/portal-estudiante/index.php') ?>">Apariencia y textos</a></li></ul></div>
    </li>
    <li class="nav-item nav-category">Productos y pedidos</li>
    <li class="nav-item <?= $comercioAbierto ? 'active' : '' ?>">
      <a class="nav-link <?= $comercioAbierto ? '' : 'collapsed' ?>" data-bs-toggle="collapse" href="#menu-comercio" aria-expanded="<?= $comercioAbierto ? 'true' : 'false' ?>" aria-controls="menu-comercio"><i class="menu-icon mdi mdi-cart-outline"></i><span class="menu-title">Productos y pedidos</span><i class="menu-arrow"></i></a>
      <div class="collapse <?= $comercioAbierto ? 'show' : '' ?>" id="menu-comercio" data-bs-parent="#sidebar"><ul class="nav flex-column sub-menu">
        <li class="nav-item"><a class="nav-link <?= str_starts_with($dashboardActive, 'productos/') ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/productos/index.php') ?>">Productos</a></li>
        <li class="nav-item"><a class="nav-link <?= str_starts_with($dashboardActive, 'categorias/') ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/categorias/index.php') ?>">Categorías de productos</a></li>
        <li class="nav-item"><a class="nav-link <?= str_starts_with($dashboardActive, 'pedidos/') ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/pedidos/index.php') ?>">Pedidos</a></li>
        <li class="nav-item"><a class="nav-link <?= str_starts_with($dashboardActive, 'facturas/') ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/facturas/index.php') ?>">Facturas / DTE</a></li>
      </ul></div>
    </li>
    <li class="nav-item nav-category">Operaciones</li>
    <li class="nav-item <?= $operacionesAbiertas ? 'active' : '' ?>">
      <a class="nav-link <?= $operacionesAbiertas?'':'collapsed' ?>" data-bs-toggle="collapse" href="#menu-operaciones" aria-expanded="<?= $operacionesAbiertas?'true':'false' ?>"><i class="menu-icon mdi mdi-message-alert-outline"></i><span class="menu-title">Comunicaciones</span><i class="menu-arrow"></i></a>
      <div class="collapse <?= $operacionesAbiertas?'show':'' ?>" id="menu-operaciones" data-bs-parent="#sidebar"><ul class="nav flex-column sub-menu"><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/dashboard/notificaciones/index.php')?>">Notificaciones</a></li><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/dashboard/comunicaciones/index.php')?>">Correos y mensajes</a></li><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/dashboard/errores/index.php')?>">Errores operativos</a></li></ul></div>
    </li>
    <li class="nav-item nav-category">Gestión de usuarios</li>
    <li class="nav-item <?= $usuariosAbierto ? 'active' : '' ?>">
      <a class="nav-link <?= $usuariosAbierto ? '' : 'collapsed' ?>" data-bs-toggle="collapse" href="#menu-usuarios" aria-expanded="<?= $usuariosAbierto ? 'true' : 'false' ?>" aria-controls="menu-usuarios"><i class="menu-icon mdi mdi-account-multiple-outline"></i><span class="menu-title">Usuarios</span><i class="menu-arrow"></i></a>
      <div class="collapse <?= $usuariosAbierto ? 'show' : '' ?>" id="menu-usuarios" data-bs-parent="#sidebar"><ul class="nav flex-column sub-menu">
        <li class="nav-item"><a class="nav-link <?= $usuariosAbierto && $rolUsuarios === 'usuario' ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/usuarios/index.php?rol=usuario') ?>">Estudiantes</a></li>
        <li class="nav-item"><a class="nav-link <?= $usuariosAbierto && $rolUsuarios === 'docente' ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/usuarios/index.php?rol=docente') ?>">Docentes</a></li>
        <li class="nav-item"><a class="nav-link <?= $usuariosAbierto && $rolUsuarios === 'admin' ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/usuarios/index.php?rol=admin') ?>">Administradores</a></li>
      </ul></div>
    </li>
    <li class="nav-item <?= $bitacoraActiva ? 'active' : '' ?>"><a class="nav-link" href="<?= atenea_url('src/dashboard/bitacora/index.php') ?>"><i class="menu-icon mdi mdi-clipboard-text-clock-outline"></i><span class="menu-title">Bitacora</span></a></li>
    <li class="nav-item nav-category">Resumen y cuenta</li>
    <li class="nav-item"><a class="nav-link" href="<?= atenea_url('src/dashboard/index.php#resumen-sitio') ?>"><i class="menu-icon mdi mdi-chart-box-outline"></i><span class="menu-title">Resumen del sitio</span></a></li>
    <li class="nav-item"><button class="nav-link border-0 bg-transparent w-100" type="button" data-bs-toggle="modal" data-bs-target="#adminProfileModal"><i class="menu-icon mdi mdi-account-circle-outline"></i><span class="menu-title">Mi perfil</span></button></li>
    <li class="nav-item"><a class="nav-link" data-atenea-confirm="logout" href="<?= atenea_url('src/login/logout.php') ?>"><i class="menu-icon mdi mdi-logout"></i><span class="menu-title">Cerrar sesión</span></a></li>
    <li class="nav-item"><a class="nav-link" href="<?= atenea_url('index.php') ?>"><i class="menu-icon mdi mdi-web"></i><span class="menu-title">Ver sitio</span></a></li>
  </ul>
</nav>
