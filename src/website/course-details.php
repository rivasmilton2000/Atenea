<?php
$pageTitle = 'Detalle de capacitación | Atenea';
$pageDescription = 'Información de las capacitaciones de Atenea.';
$pageClass = 'course-details-page';
$activePage = 'capacitaciones';
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<main class="main">
  <div class="page-title" data-aos="fade"><div class="heading"><div class="container"><div class="row justify-content-center text-center"><div class="col-lg-8"><h1>Fundamentos de Naturopatía</h1><p class="mb-0">Una introducción estructurada al bienestar natural y sus principios esenciales.</p></div></div></div></div><nav class="breadcrumbs"><div class="container"><ol><li><a href="<?= atenea_url('index.php') ?>">Inicio</a></li><li><a href="<?= atenea_url('src/website/courses.php') ?>">Capacitaciones</a></li><li class="current">Detalle</li></ol></div></nav></div>
  <section class="course-details section"><div class="container"><div class="row gy-4"><div class="col-lg-8" data-aos="fade-up"><img src="<?= atenea_url('src/website/assets/img/course-details.jpg') ?>" class="img-fluid" alt="Capacitación en fundamentos de naturopatía"><h2>Sobre esta capacitación</h2><p>Explora conceptos fundamentales para comprender la naturopatía desde una visión integral, responsable y orientada al bienestar.</p><h3>¿Qué aprenderás?</h3><ul><li>Principios generales del bienestar holístico.</li><li>Hábitos y recursos para el autocuidado consciente.</li><li>Criterios éticos para aplicar los conocimientos adquiridos.</li></ul></div><aside class="col-lg-4" data-aos="fade-up" data-aos-delay="100"><div class="course-info d-flex justify-content-between align-items-center"><h3>Modalidad</h3><p>Consultar disponibilidad</p></div><div class="course-info d-flex justify-content-between align-items-center"><h3>Nivel</h3><p>Introductorio</p></div><div class="mt-4"><a class="btn-atenea" href="<?= atenea_url('src/website/contact.php') ?>">Solicitar información</a></div></aside></div></div></section>
</main>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>

