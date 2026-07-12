<?php
$pageTitle = 'Nosotros | Atenea';
$pageDescription = 'Conoce la misión y el enfoque de Atenea Escuela de Naturopatía Holística.';
$pageClass = 'about-page';
$activePage = 'nosotros';
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<main class="main">
  <div class="page-title" data-aos="fade"><div class="heading"><div class="container"><div class="row justify-content-center text-center"><div class="col-lg-8"><h1>Nosotros</h1><p class="mb-0">Formamos personas comprometidas con el bienestar integral y el uso responsable de conocimientos naturales.</p></div></div></div></div>
    <nav class="breadcrumbs"><div class="container"><ol><li><a href="<?= atenea_url('index.php') ?>">Inicio</a></li><li class="current">Nosotros</li></ol></div></nav>
  </div>
  <section class="about-us section"><div class="container"><div class="row gy-4 align-items-center">
    <div class="col-lg-6 order-2 order-lg-1 content" data-aos="fade-up"><h2>Atenea Escuela de Naturopatía Holística</h2><p>Somos una comunidad educativa dedicada a compartir conocimientos que promueven hábitos conscientes y una comprensión integral del bienestar.</p><ul><li><i class="bi bi-check-circle"></i> <span>Formación con sentido humano, ético y práctico.</span></li><li><i class="bi bi-check-circle"></i> <span>Programas actualizados y acompañamiento cercano.</span></li><li><i class="bi bi-check-circle"></i> <span>Respeto por la persona, la naturaleza y la comunidad.</span></li></ul></div>
    <div class="col-lg-6 order-1 order-lg-2" data-aos="fade-up" data-aos-delay="100"><img src="<?= atenea_url('src/website/assets/img/about-2.jpg') ?>" class="img-fluid rounded" alt="Experiencia educativa en Atenea"></div>
  </div></div></section>
  <section class="section light-background"><div class="container"><div class="row gy-4">
    <div class="col-md-4" data-aos="fade-up"><div class="icon-box text-center p-4"><i class="bi bi-bullseye fs-1"></i><h3>Misión</h3><p>Facilitar una formación integral y accesible en naturopatía y bienestar holístico.</p></div></div>
    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100"><div class="icon-box text-center p-4"><i class="bi bi-eye fs-1"></i><h3>Visión</h3><p>Ser una comunidad referente en educación natural, consciente y responsable.</p></div></div>
    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200"><div class="icon-box text-center p-4"><i class="bi bi-heart fs-1"></i><h3>Valores</h3><p>Respeto, integridad, aprendizaje continuo y compromiso con el bienestar.</p></div></div>
  </div></div></section>
</main>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>

