<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/contenido.php';

$pageTitle = $pageTitle ?? 'Atenea Escuela de Naturopatía Holística';
$pageDescription = $pageDescription ?? 'Capacitaciones y certificaciones en naturopatía y bienestar holístico.';
$pageClass = $pageClass ?? 'index-page';
$activePage = $activePage ?? 'inicio';
$configuracionSitio = obtenerConfiguracionSitio();
$faviconSitio = $configuracionSitio['favicon'] ?? 'img/atenea-logo.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= atenea_e($pageTitle) ?></title>
  <meta name="description" content="<?= atenea_e($pageDescription) ?>">
  <meta name="keywords" content="naturopatía, bienestar holístico, capacitaciones, certificaciones, Atenea">

  <link rel="icon" type="image/png" href="<?= rutaImagenContenido($faviconSitio, 'img/atenea-logo.png') ?>">
  <link rel="apple-touch-icon" href="<?= rutaImagenContenido($faviconSitio, 'img/atenea-logo.png') ?>">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&family=Raleway:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= atenea_url('src/website/assets/vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?= atenea_url('src/website/assets/vendor/bootstrap-icons/bootstrap-icons.css') ?>" rel="stylesheet">
  <link href="<?= atenea_url('src/website/assets/vendor/aos/aos.css') ?>" rel="stylesheet">
  <link href="<?= atenea_url('src/website/assets/vendor/glightbox/css/glightbox.min.css') ?>" rel="stylesheet">
  <link href="<?= atenea_url('src/website/assets/vendor/swiper/swiper-bundle.min.css') ?>" rel="stylesheet">
  <link href="<?= atenea_url('src/website/assets/css/main.css') ?>" rel="stylesheet">
</head>
<body class="<?= atenea_e($pageClass) ?>">
<?php require __DIR__ . '/navbar.php'; ?>
