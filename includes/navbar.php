<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/contenido.php';
require_once __DIR__ . '/carrito.php';
require_once __DIR__ . '/notificaciones.php';
$usuarioNavbar = obtenerUsuarioActual();
$cantidadNavbar = cantidadCarritoActualAtenea(obtenerConexion());
$notificacionesNavbar = $usuarioNavbar ? notificacionesUsuarioResumen((int)$usuarioNavbar['id'],1) : ['no_leidas'=>0,'notificaciones'=>[]];
$navItems = obtenerMenuSitio();
$configuracionNavbar = $configuracionSitio ?? obtenerConfiguracionSitio();
$logoNavbar = $configuracionNavbar['logo'] ?? 'img/atenea-logo.png';
$paginaPorUrlNavbar = ['index.php'=>'inicio','src/website/about.php'=>'nosotros','src/website/courses.php'=>'capacitaciones','src/website/trainers.php'=>'docentes','src/website/events.php'=>'eventos','src/website/pricing.php'=>'productos','src/website/noticias.php'=>'noticias','src/website/contact.php'=>'contacto'];
$itemActivoNavbar = static function(array $item) use (&$itemActivoNavbar, $paginaPorUrlNavbar, $activePage): bool {
    if (($paginaPorUrlNavbar[$item['url'] ?? ''] ?? '') === $activePage) return true;
    if (str_starts_with((string)$activePage, 'navbar-') && (string)($item['slug'] ?? '') === substr((string)$activePage, 7)) return true;
    foreach ($item['hijos'] ?? [] as $hijo) if ($itemActivoNavbar($hijo)) return true;
    return false;
};
$renderMenuNavbar = static function(array $items) use (&$renderMenuNavbar, $itemActivoNavbar): void {
    foreach ($items as $item) {
        $hijos = $item['hijos'] ?? []; $activo = $itemActivoNavbar($item); $url = urlContenidoSegura((string)($item['url'] ?? '#'));
        echo '<li'.($hijos?' class="dropdown"':'').'><a href="'.atenea_e($url).'"'.($activo?' class="active"':'').(!empty($item['nueva_pestana'])?' target="_blank" rel="noopener noreferrer"':'').($activo&&!$hijos?' aria-current="page"':'').'>';
        if (!empty($item['icono'])) echo '<i class="'.atenea_e((string)$item['icono']).'" aria-hidden="true"></i> ';
        echo $hijos?'<span>'.atenea_e((string)$item['texto']).'</span><i class="bi bi-chevron-down toggle-dropdown" aria-hidden="true"></i>':atenea_e((string)$item['texto']);
        echo '</a>'; if($hijos){echo '<ul>';$renderMenuNavbar($hijos);echo '</ul>';} echo '</li>';
    }
};
?>
<header id="header" class="header d-flex align-items-center sticky-top">
  <div class="container-fluid container-xl position-relative d-flex align-items-center">
    <a href="<?= atenea_url('index.php') ?>" class="logo atenea-brand d-flex align-items-center me-auto" aria-label="Ir al inicio de Atenea">
      <img src="<?= rutaImagenContenido($logoNavbar, 'img/atenea-logo.png') ?>" alt="<?= atenea_e($configuracionNavbar['nombre_sitio'] ?? 'Atenea Escuela de Naturopatía Holística') ?>">
    </a>

    <nav id="navmenu" class="navmenu" aria-label="Navegación principal">
      <ul>
        <?php $renderMenuNavbar($navItems); ?>
      </ul>
      <i class="mobile-nav-toggle d-xl-none bi bi-list" role="button" tabindex="0" aria-label="Abrir menú" aria-expanded="false"></i>
    </nav>

    <?php if ($usuarioNavbar === null || $usuarioNavbar['rol'] === 'usuario'): ?><a class="btn position-relative me-2" href="<?=atenea_url('src/carrito/index.php')?>" aria-label="Carrito, <?=$cantidadNavbar?> productos"><i class="bi bi-cart3 fs-5" aria-hidden="true"></i><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" <?=$cantidadNavbar?'':'hidden'?>><?=$cantidadNavbar?></span></a><?php endif; ?>
    <?php if ($usuarioNavbar === null): ?>
      <a class="btn-getstarted" href="<?= atenea_url('src/login/sign-in.php') ?>">Iniciar sesión</a>
    <?php else: ?>
      <a class="btn position-relative me-2" href="<?=atenea_url('src/notificaciones/index.php')?>" aria-label="Notificaciones"><i class="bi bi-bell fs-5" aria-hidden="true"></i><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" data-atenea-notification-count <?=$notificacionesNavbar['no_leidas']?'':'hidden'?>><?=(int)$notificacionesNavbar['no_leidas']?></span></a>
      <div class="dropdown atenea-user-menu">
        <button class="btn atenea-user-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="<?= atenea_e(urlAvatarAtenea($usuarioNavbar)) ?>" alt="Foto de <?= atenea_e((string) $usuarioNavbar['nombre']) ?>">
          <span class="atenea-user-name"><?= atenea_e((string) $usuarioNavbar['nombre']) ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#modalPerfil"><i class="bi bi-person me-2"></i>Mi perfil</button></li>
          <li><a class="dropdown-item" href="<?= rutaPanelPorRol((string) $usuarioNavbar['rol']) ?>"><i class="bi bi-grid me-2"></i>Mi panel</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="<?= atenea_url('src/login/logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a></li>
        </ul>
      </div>
    <?php endif; ?>
  </div>
</header>
