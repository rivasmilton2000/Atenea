<?php
require_once __DIR__ . '/../_auth_guard.php';
$usuarioAdmin ??= obtenerUsuarioActual();
$configuracionAdmin ??= obtenerConfiguracionSitio();
$logoAdmin = $configuracionAdmin['logo'] ?? 'img/atenea-logo.png';
$nombreAdmin = (string)($usuarioAdmin['nombre'] ?? 'Administrador');
$correoAdmin = (string)($usuarioAdmin['correo'] ?? '');
$fotoAdmin = $usuarioAdmin['foto'] ?? null;
?>
<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
  <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
    <div class="me-3"><button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize" aria-label="Contraer menú"><span class="icon-menu"></span></button></div>
    <div>
      <a class="navbar-brand brand-logo" href="<?= atenea_url('src/dashboard/index.php') ?>"><img src="<?= rutaImagenContenido($logoAdmin,'img/atenea-logo.png') ?>" alt="Atenea Escuela de Naturopatía Holística"></a>
      <a class="navbar-brand brand-logo-mini" href="<?= atenea_url('src/dashboard/index.php') ?>"><img src="<?= rutaImagenContenido($logoAdmin,'img/atenea-logo.png') ?>" alt="Atenea"></a>
    </div>
  </div>
  <div class="navbar-menu-wrapper d-flex align-items-top">
    <ul class="navbar-nav"><li class="nav-item fw-semibold d-none d-lg-block ms-0"><h1 class="welcome-text">Buenos días, <span class="text-black fw-bold"><?= atenea_e($nombreAdmin) ?></span></h1><h3 class="welcome-sub-text"><?= atenea_e(fechaAdminActual()) ?></h3></li></ul>
    <ul class="navbar-nav ms-auto">
      <li class="nav-item dropdown d-none d-lg-block user-dropdown">
        <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
          <?php if($fotoAdmin):?><img class="img-xs rounded-circle" src="<?=atenea_url('uploads/perfiles/'.rawurlencode(basename((string)$fotoAdmin)))?>" alt="Foto de <?=atenea_e($nombreAdmin)?>"><?php else:?><span class="atenea-avatar"><?=atenea_e(mb_strtoupper(mb_substr($nombreAdmin,0,1)))?></span><?php endif;?>
        </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
          <div class="dropdown-header text-center"><?php if($fotoAdmin):?><img class="img-md rounded-circle" src="<?=atenea_url('uploads/perfiles/'.rawurlencode(basename((string)$fotoAdmin)))?>" alt="Foto de perfil"><?php else:?><span class="atenea-avatar mx-auto"><?=atenea_e(mb_strtoupper(mb_substr($nombreAdmin,0,1)))?></span><?php endif;?><p class="mb-1 mt-3 fw-semibold"><?=atenea_e($nombreAdmin)?></p><p class="fw-light text-muted mb-0"><?=atenea_e($correoAdmin)?></p></div>
          <span class="dropdown-item text-muted"><i class="dropdown-item-icon mdi mdi-account-outline me-2"></i>Mi perfil <small>(próximamente)</small></span>
          <a class="dropdown-item" href="<?=atenea_url('index.php')?>" target="_blank" rel="noopener"><i class="dropdown-item-icon mdi mdi-web text-primary me-2"></i>Ver sitio</a>
          <a class="dropdown-item" href="<?=atenea_url('src/login/logout.php')?>"><i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Cerrar sesión</a>
        </div>
      </li>
    </ul>
    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas" aria-label="Abrir menú"><span class="mdi mdi-menu"></span></button>
  </div>
</nav>

