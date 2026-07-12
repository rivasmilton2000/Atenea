<?php
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

    <?php // Punto preparado para sustituir el botón por el nombre/menú de una sesión activa. ?>
    <a class="btn-getstarted" href="<?= atenea_url('src/login/login.php') ?>">Iniciar sesión</a>
  </div>
</header>
