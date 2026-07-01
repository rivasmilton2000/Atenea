<?php
require_once __DIR__ . '/../pages/session.php';
require_once __DIR__ . '/atenea_auth.php';

$currentPage = basename((string) ($_SERVER['PHP_SELF'] ?? ''));
$ateneaNavMap = [
    'homepage.php' => ['homepage.php'],
    'about.php' => ['about.php'],
    'educacion.php' => ['educacion.php'],
    'galeria.php' => ['galeria.php'],
    'noticias.php' => ['noticias.php', 'noticia_detalle.php'],
    'productos.php' => ['productos.php', 'producto_detalle.php', 'carrito.php'],
    'contacto.php' => ['contacto.php'],
];

$ateneaIsActive = static function (string $href) use ($currentPage, $ateneaNavMap): string {
    $pages = $ateneaNavMap[$href] ?? [$href];

    return in_array($currentPage, $pages, true) ? ' active' : '';
};

$ateneaLoggedIn = logged_in();
$dashboardUrl = $ateneaLoggedIn ? atenea_dashboard_route_for_session() : 'login.php';
$dashboardLabel = $ateneaLoggedIn ? atenea_dashboard_label_for_session() : 'Aula Virtual';
$logoutUrl = $ateneaLoggedIn ? 'logout.php?next=' . rawurlencode($currentPage) : '';
$dashboardUrlEscaped = htmlspecialchars($dashboardUrl, ENT_QUOTES, 'UTF-8');
$dashboardLabelEscaped = htmlspecialchars($dashboardLabel, ENT_QUOTES, 'UTF-8');
$logoutUrlEscaped = htmlspecialchars($logoutUrl, ENT_QUOTES, 'UTF-8');
?>
<!-- Navbar Start -->
<div class="container-fluid atenea-navbar-wrap bg-light position-relative">
  <nav class="navbar navbar-expand-lg bg-light navbar-light py-3 py-lg-0 px-0 px-lg-5 atenea-navbar">
    <a href="homepage.php" class="navbar-brand font-weight-bold text-secondary" data-loader-text="Cargando inicio...">
      <img src="../img/Atenea Logo.png" alt="ATENEA">
      <span class="text-primary1">ATENEA</span>
    </a>
    <button
      type="button"
      class="navbar-toggler"
      data-toggle="collapse"
      data-target="#navbarCollapse"
      aria-label="Abrir navegación"
    >
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
      <div class="navbar-nav font-weight-bold mx-auto py-0 atenea-main-menu">
        <a href="homepage.php" class="nav-item nav-link<?php echo $ateneaIsActive('homepage.php'); ?>" data-loader-text="Cargando inicio...">Inicio</a>
        <a href="about.php" class="nav-item nav-link<?php echo $ateneaIsActive('about.php'); ?>" data-loader-text="Cargando información...">¿Quiénes somos?</a>
        <a href="educacion.php" class="nav-item nav-link<?php echo $ateneaIsActive('educacion.php'); ?>" data-loader-text="Cargando programas...">Educación</a>
        <a href="galeria.php" class="nav-item nav-link<?php echo $ateneaIsActive('galeria.php'); ?>" data-loader-text="Cargando galería...">Galería</a>
        <a href="noticias.php" class="nav-item nav-link<?php echo $ateneaIsActive('noticias.php'); ?>" data-loader-text="Cargando noticias...">Noticias</a>
        <a href="productos.php" class="nav-item nav-link<?php echo $ateneaIsActive('productos.php'); ?>" data-loader-text="Cargando productos...">Productos</a>
        <a href="contacto.php" class="nav-item nav-link<?php echo $ateneaIsActive('contacto.php'); ?>" data-loader-text="Cargando contacto...">Contáctanos</a>
      </div>
      <div class="atenea-nav-actions">
        <a href="productos.php" class="btn atenea-cart-btn" data-loader-text="Cargando tienda...">Tienda</a>
        <?php if (!$ateneaLoggedIn): ?>
          <a href="registro.php" class="btn atenea-cart-btn" data-loader-text="Abriendo registro...">Registrarme</a>
        <?php endif; ?>
        <a href="<?php echo $dashboardUrlEscaped; ?>" class="btn atenea-virtual-btn" data-loader-text="Abriendo tu panel..."><?php echo $dashboardLabelEscaped; ?></a>
        <?php if ($ateneaLoggedIn): ?>
          <a href="<?php echo $logoutUrlEscaped; ?>" class="btn atenea-cart-btn" data-loader-text="Cerrando sesión...">Salir</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>
</div>
<!-- Navbar End -->
