<?php
require_once __DIR__ . '/../_auth_guard.php';
$dashboardActive ??= 'panel';
$inicioAbierto = in_array($dashboardActive, ['secciones/index.php', 'elementos/index.php', 'hero'], true);
$configAbierta = in_array($dashboardActive, ['configuracion/index.php', 'navbar/index.php'], true);
?>
<nav class="sidebar sidebar-offcanvas" id="sidebar" data-active-managed="server">
  <ul class="nav">
    <li class="nav-item <?= $dashboardActive === 'panel' ? 'active' : '' ?>">
      <a class="nav-link" href="<?= atenea_url('src/dashboard/index.php') ?>">
        <i class="mdi mdi-grid-large menu-icon"></i>
        <span class="menu-title">Panel principal</span>
      </a>
    </li>
    <li class="nav-item nav-category">Gestion del sitio web</li>
    <li class="nav-item <?= $inicioAbierto ? 'active' : '' ?>">
      <a class="nav-link <?= $inicioAbierto ? '' : 'collapsed' ?>" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="<?= $inicioAbierto ? 'true' : 'false' ?>" aria-controls="ui-basic">
        <i class="menu-icon mdi mdi-floor-plan"></i>
        <span class="menu-title">Pagina de inicio</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse <?= $inicioAbierto ? 'show' : '' ?>" id="ui-basic" data-bs-parent="#sidebar">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link <?= $dashboardActive === 'secciones/index.php' ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/secciones/index.php') ?>">Secciones</a></li>
          <li class="nav-item"> <a class="nav-link <?= $dashboardActive === 'elementos/index.php' ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/elementos/index.php') ?>">Elementos</a></li>
          <li class="nav-item"> <a class="nav-link <?= $dashboardActive === 'hero' ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/secciones/index.php') ?>">Hero principal</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item <?= $configAbierta ? 'active' : '' ?>">
      <a class="nav-link <?= $configAbierta ? '' : 'collapsed' ?>" data-bs-toggle="collapse" href="#form-elements" aria-expanded="<?= $configAbierta ? 'true' : 'false' ?>" aria-controls="form-elements">
        <i class="menu-icon mdi mdi-card-text-outline"></i>
        <span class="menu-title">Configuracion</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse <?= $configAbierta ? 'show' : '' ?>" id="form-elements" data-bs-parent="#sidebar">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"><a class="nav-link <?= $dashboardActive === 'configuracion/index.php' ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/configuracion/index.php') ?>">Configuracion general</a></li>
          <li class="nav-item"><a class="nav-link <?= $dashboardActive === 'navbar/index.php' ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/navbar/index.php') ?>">Navbar y menu</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item <?= $dashboardActive === 'portal-estudiante' ? 'active' : '' ?>">
      <a class="nav-link <?= $dashboardActive === 'portal-estudiante' ? '' : 'collapsed' ?>" data-bs-toggle="collapse" href="#charts" aria-expanded="<?= $dashboardActive === 'portal-estudiante' ? 'true' : 'false' ?>" aria-controls="charts">
        <i class="menu-icon mdi mdi-chart-line"></i>
        <span class="menu-title">Portal estudiante</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse <?= $dashboardActive === 'portal-estudiante' ? 'show' : '' ?>" id="charts" data-bs-parent="#sidebar">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link <?= $dashboardActive === 'portal-estudiante' ? 'active' : '' ?>" href="<?= atenea_url('src/dashboard/portal-estudiante/index.php') ?>">Apariencia y textos</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item nav-category">Gestion de usuarios</li>
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-toggle="collapse" href="#tables" aria-expanded="false" aria-controls="tables">
        <i class="menu-icon mdi mdi-table"></i>
        <span class="menu-title">Usuarios</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="tables" data-bs-parent="#sidebar">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="#">Estudiantes</a></li>
          <li class="nav-item"> <a class="nav-link" href="#">Docentes</a></li>
          <li class="nav-item"> <a class="nav-link" href="#">Administradores</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item nav-category">Cuenta</li>
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
        <i class="menu-icon mdi mdi-account-circle-outline"></i>
        <span class="menu-title">Cuenta</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="auth" data-bs-parent="#sidebar">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="<?= atenea_url('index.php') ?>" target="_blank"> Ver sitio </a></li>
          <li class="nav-item"> <a class="nav-link" href="#"> Mi perfil </a></li>
          <li class="nav-item"> <a class="nav-link" href="<?= atenea_url('src/login/logout.php') ?>"> Cerrar sesion </a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="<?= atenea_url('index.php') ?>" target="_blank">
        <i class="menu-icon mdi mdi-file-document"></i>
        <span class="menu-title">Ver sitio</span>
      </a>
    </li>
  </ul>
</nav>
