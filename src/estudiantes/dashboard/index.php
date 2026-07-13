<?php
declare(strict_types=1);

require_once __DIR__ . '/_auth_guard.php';
require_once dirname(__DIR__, 3) . '/includes/portal_estudiante.php';
require_once dirname(__DIR__, 3) . '/includes/contenido.php';

$usuario = obtenerUsuarioActual() ?? [];
$nombreEstudiante = trim((string) (($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')));
$avatarConfigurado = obtenerConfiguracionPortalEstudiante('avatar_predeterminado');
$avatar = !empty($usuario['foto'])
    ? rutaImagenContenido((string) $usuario['foto'], $avatarConfigurado)
    : rutaImagenContenido($avatarConfigurado, 'src/estudiantes/assets/images/avatars/01.png');
$logoPortal = rutaImagenContenido(obtenerConfiguracionPortalEstudiante('portal_logo'), 'img/atenea-logo.png');
$horaLocal = (int) date('G');
$saludo = $horaLocal < 12 ? 'Buenos días' : ($horaLocal < 18 ? 'Buenas tardes' : 'Buenas noches');
?>
<!doctype html>
<html lang="es" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Portal del estudiante | Atenea</title>
  <link rel="shortcut icon" href="<?= $logoPortal ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/core/libs.min.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/vendor/aos/dist/aos.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/hope-ui.min.css?v=4.0.0') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/custom.min.css?v=4.0.0') ?>">
</head>
<body>
  <div id="loading"><div class="loader simple-loader"><div class="loader-body"></div></div></div>

  <aside class="sidebar sidebar-default sidebar-white sidebar-base navs-rounded-all">
    <div class="sidebar-header d-flex align-items-center justify-content-start">
      <a href="<?= atenea_url('src/estudiantes/index.php') ?>" class="navbar-brand">
        <img src="<?= $logoPortal ?>" class="img-fluid" style="width:auto;height:52px;object-fit:contain" alt="Atenea">
      </a>
      <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
        <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path d="M4.25 12.27h15M10.3 18.3l-6.05-6.03 6.05-6.02" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></i>
      </div>
    </div>
    <div class="sidebar-body pt-0 data-scrollbar">
      <div class="sidebar-list">
        <ul class="navbar-nav iq-main-menu" id="sidebar-menu">
          <li class="nav-item static-item"><span class="nav-link static-item disabled"><span class="default-icon">Portal del estudiante</span><span class="mini-icon">-</span></span></li>
          <li class="nav-item"><a class="nav-link active" aria-current="page" href="<?= atenea_url('src/estudiantes/index.php') ?>"><i class="icon"><i class="bi bi-grid"></i></i><span class="item-name">Inicio</span></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= atenea_url('src/estudiantes/perfil.php') ?>"><i class="icon"><i class="bi bi-person"></i></i><span class="item-name">Mi perfil</span></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= atenea_url('src/estudiantes/pedidos.php') ?>"><i class="icon"><i class="bi bi-bag"></i></i><span class="item-name">Mis pedidos</span></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= atenea_url('index.php') ?>"><i class="icon"><i class="bi bi-globe"></i></i><span class="item-name">Volver al sitio</span></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= atenea_url('src/login/logout.php') ?>"><i class="icon"><i class="bi bi-box-arrow-right"></i></i><span class="item-name">Cerrar sesión</span></a></li>
        </ul>
      </div>
    </div>
    <div class="sidebar-footer"></div>
  </aside>

  <main class="main-content">
    <nav class="nav navbar navbar-expand-lg navbar-light iq-navbar">
      <div class="container-fluid navbar-inner">
        <a href="<?= atenea_url('src/estudiantes/index.php') ?>" class="navbar-brand d-lg-none"><img src="<?= $logoPortal ?>" style="width:auto;height:42px;object-fit:contain" alt="Atenea"></a>
        <div class="sidebar-toggle" data-toggle="sidebar" data-active="true"><i class="icon"><i class="bi bi-list"></i></i></div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Abrir navegación"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="mb-2 navbar-nav ms-auto align-items-center navbar-list mb-lg-0">
            <li class="nav-item dropdown">
              <a class="py-0 nav-link d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?= $avatar ?>" class="theme-color-default-img img-fluid avatar avatar-50 avatar-rounded" alt="Foto de <?= atenea_e($nombreEstudiante) ?>">
                <div class="caption ms-3 d-none d-md-block"><h6 class="mb-0 caption-title"><?= atenea_e($nombreEstudiante) ?></h6><p class="mb-0 caption-sub-title">Estudiante</p></div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="<?= atenea_url('src/estudiantes/perfil.php') ?>">Mi perfil</a></li>
                <li><a class="dropdown-item" href="<?= atenea_url('index.php') ?>">Ver sitio</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= atenea_url('src/login/logout.php') ?>">Cerrar sesión</a></li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="iq-navbar-header" style="height:215px">
      <div class="container-fluid iq-container">
        <div class="row"><div class="col-md-12"><div class="flex-wrap d-flex justify-content-between align-items-center"><div><h1><?= $saludo ?>, <?= atenea_e((string) ($usuario['nombre'] ?? '')) ?></h1><p><?= atenea_e(obtenerConfiguracionPortalEstudiante('panel_texto_bienvenida')) ?></p></div></div></div></div>
      </div>
      <div class="iq-header-img"><img src="<?= atenea_url('src/estudiantes/assets/images/dashboard/top-header.png') ?>" alt="" class="theme-color-default-img img-fluid w-100 h-100 animated-scaleX"></div>
    </div>

    <div class="container-fluid content-inner mt-n5 py-0">
      <div class="row"><div class="col-12"><div class="card"><div class="card-body">
        <h2 class="h4"><?= atenea_e(obtenerConfiguracionPortalEstudiante('panel_titulo')) ?></h2>
        <p><?= atenea_e(obtenerConfiguracionPortalEstudiante('panel_subtitulo')) ?></p>
        <p class="mb-0 text-muted"><?= atenea_e((string) ($usuario['correo'] ?? '')) ?> &middot; Estudiante &middot; <?= date('d/m/Y') ?></p>
      </div></div></div></div>
    </div>

    <footer class="footer"><div class="footer-body text-center"><?= atenea_e(obtenerConfiguracionPortalEstudiante('texto_pie_pagina')) ?> &copy; <?= date('Y') ?></div></footer>
  </main>

  <script src="<?= atenea_url('src/estudiantes/assets/js/core/libs.min.js') ?>"></script>
  <script src="<?= atenea_url('src/estudiantes/assets/js/core/external.min.js') ?>"></script>
  <script src="<?= atenea_url('src/estudiantes/assets/vendor/aos/dist/aos.js') ?>"></script>
  <script src="<?= atenea_url('src/estudiantes/assets/js/hope-ui.js') ?>" defer></script>
</body>
</html>
