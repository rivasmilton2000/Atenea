<?php
$pageTitle = 'Capacitaciones | Atenea';
$pageDescription = 'Explora las capacitaciones de Atenea en naturopatía y bienestar holístico.';
$pageClass = 'courses-page';
$activePage = 'capacitaciones';
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<main class="main">
  <div class="page-title" data-aos="fade"><div class="heading"><div class="container"><div class="row justify-content-center text-center"><div class="col-lg-8"><h1>Capacitaciones</h1><p class="mb-0">Programas creados para fortalecer tus conocimientos y llevar el bienestar holístico a la práctica.</p></div></div></div></div><nav class="breadcrumbs"><div class="container"><ol><li><a href="<?= atenea_url('index.php') ?>">Inicio</a></li><li class="current">Capacitaciones</li></ol></div></nav></div>
  <section class="courses section"><div class="container"><div class="row gy-4">
  <?php $items = [
    ['course-1.jpg','Naturopatía','Fundamentos de Naturopatía','Comprende los principios del cuidado natural y su aplicación responsable.'],
    ['course-2.jpg','Bienestar','Hábitos para el Equilibrio Integral','Desarrolla rutinas conscientes que apoyen el bienestar cotidiano.'],
    ['course-3.jpg','Especialización','Recursos Naturales Aplicados','Profundiza en el conocimiento y uso informado de recursos naturales.']
  ]; foreach ($items as $i => [$img,$category,$title,$text]): ?>
    <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="zoom-in" data-aos-delay="<?= ($i + 1) * 100 ?>"><article class="course-item"><img src="<?= atenea_url('src/website/assets/img/' . $img) ?>" class="img-fluid" alt="<?= atenea_e($title) ?>"><div class="course-content"><p class="category"><?= atenea_e($category) ?></p><h2><a href="<?= atenea_url('src/website/course-details.php') ?>"><?= atenea_e($title) ?></a></h2><p class="description"><?= atenea_e($text) ?></p><a href="<?= atenea_url('src/website/course-details.php') ?>">Ver detalles <i class="bi bi-arrow-right"></i></a></div></article></div>
  <?php endforeach; ?>
  </div></div></section>
</main>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>

