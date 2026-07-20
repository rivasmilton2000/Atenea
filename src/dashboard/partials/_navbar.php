<?php
require_once __DIR__ . '/../_auth_guard.php';
require_once dirname(__DIR__, 3) . '/includes/notificaciones.php';
$usuarioAdmin ??= obtenerUsuarioActual();
$configuracionAdmin ??= obtenerConfiguracionSitio();
$logoAdmin = rutaImagenContenido($configuracionAdmin['logo'] ?? 'img/atenea-logo.png', 'img/atenea-logo.png');
$nombreAdmin = trim((string) (($usuarioAdmin['nombre'] ?? 'Administrador') . ' ' . ($usuarioAdmin['apellido'] ?? '')));
$correoAdmin = (string) ($usuarioAdmin['correo'] ?? '');
$perfilAdminNav = obtenerPerfilUsuario((int)($usuarioAdmin['id'] ?? 0)) ?: $usuarioAdmin;
$fotoAdmin = rutaFotoPerfil($perfilAdminNav);
$horaLocal = (int) date('G');
$saludoAdmin = $horaLocal < 12 ? 'Buenos días' : ($horaLocal < 18 ? 'Buenas tardes' : 'Buenas noches');
$fechaAdmin = date('d/m/Y');
$resumenNotificaciones = notificacionesAdminResumen((int)($usuarioAdmin['id'] ?? 0));
?>
<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
  <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
    <div class="me-3">
      <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
        <span class="icon-menu"></span>
      </button>
    </div>
    <div>
      <a class="navbar-brand brand-logo" href="<?= atenea_url('src/dashboard/index.php') ?>">
        <img src="<?= $logoAdmin ?>" alt="Atenea" />
      </a>
      <a class="navbar-brand brand-logo-mini" href="<?= atenea_url('src/dashboard/index.php') ?>">
        <img src="<?= $logoAdmin ?>" alt="Atenea" />
      </a>
    </div>
  </div>
  <div class="navbar-menu-wrapper d-flex align-items-top">
    <ul class="navbar-nav">
      <li class="nav-item fw-semibold d-none d-lg-block ms-0">
        <h1 class="welcome-text"><?= $saludoAdmin ?>, <span class="text-black fw-bold"><?= atenea_e($nombreAdmin ?: 'Administrador Atenea') ?></span></h1>
        <h3 class="welcome-sub-text">Resumen de la actividad de Atenea esta semana </h3>
      </li>
    </ul>
    <ul class="navbar-nav ms-auto">
      <li class="nav-item dropdown d-none d-lg-block">
        <button class="nav-link dropdown-bordered dropdown-toggle dropdown-toggle-split border-0" id="messageDropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false"> Accesos rápidos </button>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="messageDropdown">
          <a class="dropdown-item py-3">
            <p class="mb-0 fw-medium float-start">Administración de Atenea</p>
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item preview-item" href="<?= atenea_url('src/dashboard/secciones/index.php') ?>">
            <div class="preview-item-content flex-grow py-2">
              <p class="preview-subject ellipsis fw-medium text-dark">Contenido del sitio </p>
              <p class="fw-light small-text mb-0">Secciones, elementos y página pública</p>
            </div>
          </a>
          <a class="dropdown-item preview-item" href="<?= atenea_url('src/dashboard/configuracion/index.php') ?>">
            <div class="preview-item-content flex-grow py-2">
              <p class="preview-subject ellipsis fw-medium text-dark">Configuración general</p>
              <p class="fw-light small-text mb-0">Identidad y datos visibles</p>
            </div>
          </a>
        </div>
      </li>
      <li class="nav-item d-none d-lg-block">
        <div class="input-group date navbar-date-picker">
          <span class="input-group-addon input-group-prepend border-right">
            <span class="icon-calendar input-group-text calendar-icon"></span>
          </span>
          <input type="text" class="form-control" value="<?= $fechaAdmin ?>" aria-label="Fecha actual" readonly>
        </div>
      </li>
      <li class="nav-item">
        <form class="search-form" action="<?= atenea_url('src/dashboard/usuarios/index.php') ?>" method="get" role="search">
          <i class="icon-search"></i>
          <label class="visually-hidden" for="adminGlobalSearch">Buscar usuarios</label>
          <input id="adminGlobalSearch" name="q" type="search" class="form-control" placeholder="Buscar usuarios" title="Buscar usuarios">
        </form>
      </li>
      <li class="nav-item dropdown">
        <button class="nav-link count-indicator border-0 bg-transparent" id="notificationDropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Abrir notificaciones">
          <i class="icon-bell"></i>
          <span class="count<?= $resumenNotificaciones['no_leidas'] > 0 ? '' : ' d-none' ?>" id="ateneaNotificationDot"></span>
        </button>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="notificationDropdown">
          <div class="dropdown-item py-3 border-bottom">
            <p class="mb-0 fw-medium float-start">Notificaciones</p>
            <a href="<?= atenea_url('src/dashboard/notificaciones/index.php') ?>" class="badge badge-pill badge-primary float-end">Ver historial</a>
          </div>
          <div id="ateneaNotificationList">
            <?php foreach($resumenNotificaciones['notificaciones'] as $notificacion): ?>
              <a class="dropdown-item preview-item py-3" href="<?= atenea_e($notificacion['action_url'] ?: atenea_url('src/dashboard/notificaciones/index.php')) ?>"><div class="preview-thumbnail"><i class="mdi mdi-bell-outline m-auto text-primary"></i></div><div class="preview-item-content"><h6 class="preview-subject fw-normal text-dark mb-1"><?= atenea_e($notificacion['title']) ?></h6><p class="fw-light small-text mb-0"><?= atenea_e(mb_substr($notificacion['message'],0,90)) ?></p></div></a>
            <?php endforeach; ?>
            <?php if(!$resumenNotificaciones['notificaciones']): ?><div class="dropdown-item py-3 text-muted">No hay notificaciones recientes.</div><?php endif; ?>
          </div>
        </div>
      </li>
      <li class="nav-item dropdown">
        <button class="nav-link count-indicator border-0 bg-transparent" id="countDropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Abrir comunicaciones">
          <i class="icon-mail icon-lg"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="countDropdown">
          <div class="dropdown-item py-3">
            <p class="mb-0 fw-medium float-start">Comunicaciones</p>
            <a href="<?= atenea_url('src/dashboard/comunicaciones/index.php') ?>" class="badge badge-pill badge-primary float-end">Abrir</a>
          </div>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item preview-item" href="<?= atenea_url('src/dashboard/comunicaciones/index.php?estado=recibido') ?>">
            <div class="preview-thumbnail">
              <span class="img-sm profile-pic d-inline-flex align-items-center justify-content-center bg-primary text-white"><i class="mdi mdi-view-dashboard"></i></span>
            </div>
            <div class="preview-item-content flex-grow py-2">
              <p class="preview-subject ellipsis fw-medium text-dark">Secciones </p>
              <p class="fw-light small-text mb-0"> Administrar página de inicio </p>
            </div>
          </a>
        </div>
      </li>
      <li class="nav-item d-none d-lg-block">
        <button type="button" id="adminProfileTrigger" class="admin-profile-trigger" data-bs-toggle="modal" data-bs-target="#adminProfileModal" aria-label="Abrir mi perfil">
          <img class="admin-navbar-avatar" src="<?= atenea_e($fotoAdmin) ?>" alt="Fotografía del administrador">
        </button>
      </li>
      <li class="nav-item dropdown d-none d-lg-block user-dropdown">
        <button type="button" class="nav-link border-0 bg-transparent" id="adminUserMenuToggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Abrir menú de usuario">
          <i class="mdi mdi-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="adminUserMenuToggle">
          <div class="dropdown-header text-center">
            <p class="mb-1 fw-semibold"><?= atenea_e($nombreAdmin ?: 'Administrador Atenea') ?></p>
            <p class="fw-light text-muted mb-0"><?= atenea_e($correoAdmin) ?></p>
          </div>
          <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#adminProfileModal"><i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> Mi perfil</button>
          <a class="dropdown-item" href="<?= atenea_url('index.php') ?>"><i class="dropdown-item-icon mdi mdi-web text-primary me-2"></i> Ver sitio</a>
          <a class="dropdown-item" href="<?= atenea_url('src/dashboard/bitacora/index.php') ?>"><i class="dropdown-item-icon mdi mdi-calendar-check-outline text-primary me-2"></i> Actividad</a>
          <a class="dropdown-item" href="<?= atenea_url('src/dashboard/docs/documentation.php') ?>"><i class="dropdown-item-icon mdi mdi-help-circle-outline text-primary me-2"></i> Ayuda</a>
          <a class="dropdown-item" href="<?= atenea_url('src/login/logout.php') ?>"><i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Cerrar sesión</a>
        </div>
      </li>
    </ul>
    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas">
      <span class="mdi mdi-menu"></span>
    </button>
  </div>
</nav>
