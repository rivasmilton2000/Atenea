<?php
require_once __DIR__ . '/auth.php';
$usuarioNavbar = obtenerUsuarioActual();
$navItems = [
    'inicio' => ['Inicio', 'index.php'],
    'nosotros' => ['Nosotros', 'src/website/about.php'],
    'capacitaciones' => ['Capacitaciones', 'src/website/courses.php'],
    'docentes' => ['Docentes', 'src/website/trainers.php'],
    'eventos' => ['Eventos', 'src/website/events.php'],
    'productos' => ['Productos', 'src/website/pricing.php'],
    'noticias' => ['Noticias', 'index.php#noticias'],
    'contacto' => ['Contacto', 'src/website/contact.php'],
];
?>
<header id="header" class="header d-flex align-items-center sticky-top">
  <div class="container-fluid container-xl position-relative d-flex align-items-center">
    <a href="<?= atenea_url('index.php') ?>" class="logo atenea-brand d-flex align-items-center me-auto" aria-label="Ir al inicio de Atenea">
      <img src="<?= atenea_url('img/atenea-logo.png') ?>" alt="Atenea Escuela de Naturopatía Holística">
    </a>

    <nav id="navmenu" class="navmenu" aria-label="Navegación principal">
      <ul>
        <?php foreach ($navItems as $key => [$label, $path]): ?>
          <li><a href="<?= atenea_url($path) ?>"<?= $activePage === $key ? ' class="active" aria-current="page"' : '' ?>><?= atenea_e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <i class="mobile-nav-toggle d-xl-none bi bi-list" role="button" tabindex="0" aria-label="Abrir menú" aria-expanded="false"></i>
    </nav>

    <?php if ($usuarioNavbar === null): ?>
      <a class="btn-getstarted" href="<?= atenea_url('login.php') ?>">Iniciar sesión</a>
    <?php else: ?>
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
          <li><a class="dropdown-item" href="<?= rutaPanelPorRol((string) $usuarioNavbar['rol']) ?>"><i class="bi bi-grid me-2"></i>Mi panel</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="<?= atenea_url('logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a></li>
        </ul>
      </div>
    <?php endif; ?>
  </div>
</header>
