<?php
require_once __DIR__ . '/../_auth_guard.php';
$usuarioAdmin ??= obtenerUsuarioActual();
$configuracionAdmin ??= obtenerConfiguracionSitio();
$logoAdmin = rutaImagenContenido($configuracionAdmin['logo'] ?? 'img/atenea-logo.png', 'img/atenea-logo.png');
$nombreAdmin = trim((string) (($usuarioAdmin['nombre'] ?? 'Administrador') . ' ' . ($usuarioAdmin['apellido'] ?? '')));
$correoAdmin = (string) ($usuarioAdmin['correo'] ?? '');
$fotoAdminRuta = trim((string) ($usuarioAdmin['foto'] ?? ''));
$fotoAdmin = $fotoAdminRuta !== '' ? rutaImagenContenido($fotoAdminRuta, 'src/dashboard/assets/images/faces/face8.jpg') : atenea_url('src/dashboard/assets/images/faces/face8.jpg');
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
        <h1 class="welcome-text">Buenos dias, <span class="text-black fw-bold"><?= atenea_e($nombreAdmin ?: 'Administrador Atenea') ?></span></h1>
        <h3 class="welcome-sub-text">Resumen de la actividad de Atenea esta semana </h3>
      </li>
    </ul>
    <ul class="navbar-nav ms-auto">
      <li class="nav-item dropdown d-none d-lg-block">
        <a class="nav-link dropdown-bordered dropdown-toggle dropdown-toggle-split" id="messageDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false"> Seleccionar categoria </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="messageDropdown">
          <a class="dropdown-item py-3">
            <p class="mb-0 fw-medium float-start">Seleccionar categoria</p>
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item preview-item" href="<?= atenea_url('src/dashboard/secciones/index.php') ?>">
            <div class="preview-item-content flex-grow py-2">
              <p class="preview-subject ellipsis fw-medium text-dark">Contenido del sitio </p>
              <p class="fw-light small-text mb-0">Secciones, elementos y pagina publica</p>
            </div>
          </a>
          <a class="dropdown-item preview-item" href="<?= atenea_url('src/dashboard/configuracion/index.php') ?>">
            <div class="preview-item-content flex-grow py-2">
              <p class="preview-subject ellipsis fw-medium text-dark">Configuracion general</p>
              <p class="fw-light small-text mb-0">Identidad y datos visibles</p>
            </div>
          </a>
        </div>
      </li>
      <li class="nav-item d-none d-lg-block">
        <div id="datepicker-popup" class="input-group date datepicker navbar-date-picker">
          <span class="input-group-addon input-group-prepend border-right">
            <span class="icon-calendar input-group-text calendar-icon"></span>
          </span>
          <input type="text" class="form-control" value="<?= date('m/d/Y') ?>">
        </div>
      </li>
      <li class="nav-item">
        <form class="search-form" action="#">
          <i class="icon-search"></i>
          <input type="search" class="form-control" placeholder="Buscar aqui" title="Buscar aqui">
        </form>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link count-indicator" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
          <i class="icon-bell"></i>
          <span class="count"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="notificationDropdown">
          <a class="dropdown-item py-3 border-bottom">
            <p class="mb-0 fw-medium float-start">Actividad reciente del sitio </p>
            <span class="badge badge-pill badge-primary float-end">Ver todo</span>
          </a>
          <a class="dropdown-item preview-item py-3">
            <div class="preview-thumbnail">
              <i class="mdi mdi-alert m-auto text-primary"></i>
            </div>
            <div class="preview-item-content">
              <h6 class="preview-subject fw-normal text-dark mb-1">Panel Atenea</h6>
              <p class="fw-light small-text mb-0"> Modulos CMS activos </p>
            </div>
          </a>
        </div>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link count-indicator" id="countDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="icon-mail icon-lg"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="countDropdown">
          <a class="dropdown-item py-3">
            <p class="mb-0 fw-medium float-start">Accesos administrativos </p>
            <span class="badge badge-pill badge-primary float-end">CMS</span>
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item preview-item" href="<?= atenea_url('src/dashboard/secciones/index.php') ?>">
            <div class="preview-thumbnail">
              <img src="<?= atenea_url('src/dashboard/assets/images/faces/face10.jpg') ?>" alt="image" class="img-sm profile-pic">
            </div>
            <div class="preview-item-content flex-grow py-2">
              <p class="preview-subject ellipsis fw-medium text-dark">Secciones </p>
              <p class="fw-light small-text mb-0"> Administrar pagina de inicio </p>
            </div>
          </a>
        </div>
      </li>
      <li class="nav-item dropdown d-none d-lg-block user-dropdown">
        <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
          <img class="img-xs rounded-circle" src="<?= $fotoAdmin ?>" alt="Profile image"> </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
          <div class="dropdown-header text-center">
            <img class="img-md rounded-circle" src="<?= $fotoAdmin ?>" alt="Profile image">
            <p class="mb-1 mt-3 fw-semibold"><?= atenea_e($nombreAdmin ?: 'Administrador Atenea') ?></p>
            <p class="fw-light text-muted mb-0"><?= atenea_e($correoAdmin) ?></p>
          </div>
          <a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> Mi perfil <span class="badge badge-pill badge-danger">1</span></a>
          <a class="dropdown-item" href="<?= atenea_url('index.php') ?>" target="_blank"><i class="dropdown-item-icon mdi mdi-web text-primary me-2"></i> Ver sitio</a>
          <a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-calendar-check-outline text-primary me-2"></i> Actividad</a>
          <a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-help-circle-outline text-primary me-2"></i> Soporte</a>
          <a class="dropdown-item" href="<?= atenea_url('src/login/logout.php') ?>"><i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Cerrar sesion</a>
        </div>
      </li>
    </ul>
    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas">
      <span class="mdi mdi-menu"></span>
    </button>
  </div>
</nav>
