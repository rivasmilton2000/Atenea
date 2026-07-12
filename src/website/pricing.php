<?php
$pageTitle = 'Productos | Atenea';
$pageDescription = 'Productos y recursos de bienestar disponibles en Atenea.';
$pageClass = 'pricing-page';
$activePage = 'productos';
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<main class="main">
  <div class="page-title" data-aos="fade"><div class="heading"><div class="container"><div class="row justify-content-center text-center"><div class="col-lg-8"><h1>Productos</h1><p class="mb-0">Recursos seleccionados para acompañar hábitos conscientes y procesos de bienestar.</p></div></div></div></div><nav class="breadcrumbs"><div class="container"><ol><li><a href="<?= atenea_url('index.php') ?>">Inicio</a></li><li class="current">Productos</li></ol></div></nav></div>
  <section class="pricing section"><div class="container"><div class="row gy-4">
    <?php $products = [['bi-book','Material educativo','Guías y recursos de apoyo para continuar aprendiendo.'],['bi-flower1','Bienestar natural','Productos seleccionados con criterios de calidad y uso responsable.'],['bi-gift','Colecciones especiales','Opciones pensadas para el autocuidado o para compartir bienestar.']]; foreach ($products as $i => [$icon,$title,$text]): ?>
    <div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="<?= ($i + 1) * 100 ?>"><div class="pricing-item text-center h-100"><i class="bi <?= $icon ?> fs-1"></i><h2><?= atenea_e($title) ?></h2><p><?= atenea_e($text) ?></p><a href="<?= atenea_url('src/website/contact.php') ?>" class="buy-btn">Solicitar información</a></div></div>
    <?php endforeach; ?>
  </div></div></section>
</main>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>

