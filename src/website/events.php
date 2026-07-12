<?php
$pageTitle = 'Eventos | Atenea';
$pageDescription = 'Eventos, talleres y encuentros de Atenea.';
$pageClass = 'events-page';
$activePage = 'eventos';
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<main class="main">
  <div class="page-title" data-aos="fade"><div class="heading"><div class="container"><div class="row justify-content-center text-center"><div class="col-lg-8"><h1>Eventos</h1><p class="mb-0">Encuentros para aprender, compartir experiencias y fortalecer nuestra comunidad.</p></div></div></div></div><nav class="breadcrumbs"><div class="container"><ol><li><a href="<?= atenea_url('index.php') ?>">Inicio</a></li><li class="current">Eventos</li></ol></div></nav></div>
  <section class="events section"><div class="container"><div class="row gy-4">
    <div class="col-md-6 d-flex align-items-stretch" data-aos="fade-up"><div class="card"><div class="card-img"><img src="<?= atenea_url('src/website/assets/img/events-item-1.jpg') ?>" alt="Taller práctico de bienestar"></div><div class="card-body"><h2 class="card-title">Taller práctico de bienestar holístico</h2><p class="fst-italic text-center">Próxima fecha por anunciar</p><p class="card-text">Una experiencia guiada para conocer herramientas de autocuidado aplicables a la vida diaria.</p></div></div></div>
    <div class="col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="100"><div class="card"><div class="card-img"><img src="<?= atenea_url('src/website/assets/img/events-item-2.jpg') ?>" alt="Encuentro de la comunidad Atenea"></div><div class="card-body"><h2 class="card-title">Encuentro de la comunidad Atenea</h2><p class="fst-italic text-center">Próxima fecha por anunciar</p><p class="card-text">Un espacio para intercambiar conocimientos, resolver inquietudes y crear vínculos.</p></div></div></div>
  </div></div></section>
</main>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>

