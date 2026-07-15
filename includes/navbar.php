<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/contenido.php';
require_once __DIR__ . '/carrito.php';
$usuarioNavbar = obtenerUsuarioActual();
$cantidadNavbar = $usuarioNavbar && $usuarioNavbar['rol']==='usuario' ? cantidadCarrito(obtenerConexion(),(int)$usuarioNavbar['id']) : 0;
$navItems = obtenerMenuSitio();
$configuracionNavbar = $configuracionSitio ?? obtenerConfiguracionSitio();
$logoNavbar = $configuracionNavbar['logo'] ?? 'img/atenea-logo.png';
?>
<header id="header" class="header d-flex align-items-center sticky-top">
  <div class="container-fluid container-xl position-relative d-flex align-items-center">
    <a href="<?= atenea_url('index.php') ?>" class="logo atenea-brand d-flex align-items-center me-auto" aria-label="Ir al inicio de Atenea">
      <img src="<?= rutaImagenContenido($logoNavbar, 'img/atenea-logo.png') ?>" alt="<?= atenea_e($configuracionNavbar['nombre_sitio'] ?? 'Atenea Escuela de Naturopatía Holística') ?>">
    </a>

    <nav id="navmenu" class="navmenu" aria-label="Navegación principal">
      <ul>
        <?php foreach ($navItems as $item): ?>
          <?php
          $paginaPorUrl = [
              'index.php' => 'inicio',
              'src/website/about.php' => 'nosotros',
              'src/website/courses.php' => 'capacitaciones',
              'src/website/trainers.php' => 'docentes',
              'src/website/events.php' => 'eventos',
              'src/website/pricing.php' => 'productos',
              'index.php#noticias' => 'noticias',
              'src/website/contact.php' => 'contacto',
          ];
          $esActivo = ($paginaPorUrl[$item['url']] ?? '') === $activePage;
          ?>
          <li><a href="<?= atenea_e(urlContenidoSegura($item['url'])) ?>"<?= $esActivo ? ' class="active" aria-current="page"' : '' ?><?= $item['nueva_pestana'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= atenea_e($item['texto']) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <i class="mobile-nav-toggle d-xl-none bi bi-list" role="button" tabindex="0" aria-label="Abrir menú" aria-expanded="false"></i>
    </nav>

    <?php if ($usuarioNavbar === null): ?>
      <a class="btn-getstarted" href="<?= atenea_url('src/login/sign-in.php') ?>">Iniciar sesión</a>
    <?php else: ?>
      <?php if($usuarioNavbar['rol']==='usuario'): ?><a class="btn position-relative me-2" href="<?=atenea_url('src/estudiantes/carrito.php')?>" aria-label="Carrito, <?=$cantidadNavbar?> productos"><i class="bi bi-cart3 fs-5"></i><?php if($cantidadNavbar):?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?=$cantidadNavbar?></span><?php endif;?></a><?php endif;?>
      <div class="dropdown atenea-user-menu">
        <button class="btn atenea-user-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <?php if (!empty($usuarioNavbar['foto'])): ?>
            <img src="<?= atenea_url('uploads/perfiles/' . rawurlencode(basename((string) $usuarioNavbar['foto']))) ?>" alt="Foto de <?= atenea_e((string) $usuarioNavbar['nombre']) ?>">
          <?php else: ?>
            <span class="atenea-user-avatar" aria-hidden="true"><?= atenea_e(mb_strtoupper(mb_substr((string) $usuarioNavbar['nombre'], 0, 1))) ?></span>
          <?php endif; ?>
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
