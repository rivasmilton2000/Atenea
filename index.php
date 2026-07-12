<?php
$pageTitle = 'Atenea | Escuela de Naturopatía Holística';
$pageDescription = 'Formación integral en naturopatía, terapias naturales y bienestar holístico.';
$pageClass = 'index-page';
$activePage = 'inicio';
require __DIR__ . '/includes/header.php';
?>

<main class="main">
  <section id="hero" class="hero section dark-background">
    <img src="<?= atenea_url('src/website/assets/img/hero-bg.jpg') ?>" alt="Formación en bienestar y naturopatía" data-aos="fade-in">
    <div class="container">
      <h1 data-aos="fade-up" data-aos-delay="100">Formación integral para transformar tu bienestar</h1>
      <p data-aos="fade-up" data-aos-delay="200">Capacitaciones, certificaciones y conocimientos enfocados en naturopatía y bienestar holístico.</p>
      <div class="d-flex mt-4" data-aos="fade-up" data-aos-delay="300">
        <a href="<?= atenea_url('src/website/courses.php') ?>" class="btn-get-started">Ver capacitaciones</a>
      </div>
    </div>
  </section>

  <section id="nosotros" class="about section">
    <div class="container">
      <div class="row gy-4">
        <div class="col-lg-6 order-1 order-lg-2" data-aos="fade-up" data-aos-delay="100">
          <img src="<?= atenea_url('src/website/assets/img/about.jpg') ?>" class="img-fluid" alt="Comunidad educativa de Atenea">
        </div>
        <div class="col-lg-6 order-2 order-lg-1 content" data-aos="fade-up" data-aos-delay="200">
          <h2>Conocimiento natural para una vida en equilibrio</h2>
          <p class="fst-italic">Atenea Escuela de Naturopatía Holística impulsa una formación responsable, práctica y humana.</p>
          <ul>
            <li><i class="bi bi-check-circle"></i> <span>Programas orientados al cuidado integral y preventivo.</span></li>
            <li><i class="bi bi-check-circle"></i> <span>Docentes con experiencia en terapias naturales y bienestar.</span></li>
            <li><i class="bi bi-check-circle"></i> <span>Aprendizaje aplicable a la vida personal y al desarrollo profesional.</span></li>
          </ul>
          <a href="<?= atenea_url('src/website/about.php') ?>" class="read-more"><span>Conocer más</span><i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
    </div>
  </section>

  <section id="cifras" class="section counts light-background">
    <div class="container" data-aos="fade-up" data-aos-delay="100">
      <div class="row gy-4">
        <div class="col-lg-3 col-md-6"><div class="stats-item text-center w-100 h-100"><span data-purecounter-start="0" data-purecounter-end="1200" data-purecounter-duration="1" class="purecounter"></span><p>Estudiantes</p></div></div>
        <div class="col-lg-3 col-md-6"><div class="stats-item text-center w-100 h-100"><span data-purecounter-start="0" data-purecounter-end="64" data-purecounter-duration="1" class="purecounter"></span><p>Capacitaciones</p></div></div>
        <div class="col-lg-3 col-md-6"><div class="stats-item text-center w-100 h-100"><span data-purecounter-start="0" data-purecounter-end="42" data-purecounter-duration="1" class="purecounter"></span><p>Eventos</p></div></div>
        <div class="col-lg-3 col-md-6"><div class="stats-item text-center w-100 h-100"><span data-purecounter-start="0" data-purecounter-end="24" data-purecounter-duration="1" class="purecounter"></span><p>Docentes</p></div></div>
      </div>
    </div>
  </section>

  <section id="propuesta" class="section why-us">
    <div class="container">
      <div class="row gy-4">
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
          <div class="why-box">
            <h2>¿Por qué formarte con Atenea?</h2>
            <p>Integramos fundamentos de naturopatía, acompañamiento docente y experiencias prácticas para ayudarte a comprender el bienestar desde una visión completa.</p>
            <div class="text-center"><a href="<?= atenea_url('src/website/about.php') ?>" class="more-btn"><span>Conocer más</span> <i class="bi bi-chevron-right"></i></a></div>
          </div>
        </div>
        <div class="col-lg-8 d-flex align-items-stretch">
          <div class="row gy-4" data-aos="fade-up" data-aos-delay="200">
            <div class="col-xl-4"><div class="icon-box d-flex flex-column justify-content-center align-items-center"><i class="bi bi-mortarboard"></i><h3>Formación integral</h3><p>Contenidos que relacionan conocimientos tradicionales, hábitos saludables y práctica consciente.</p></div></div>
            <div class="col-xl-4" data-aos="fade-up" data-aos-delay="300"><div class="icon-box d-flex flex-column justify-content-center align-items-center"><i class="bi bi-people"></i><h3>Acompañamiento</h3><p>Docentes comprometidos con un proceso de aprendizaje cercano y orientado a resultados.</p></div></div>
            <div class="col-xl-4" data-aos="fade-up" data-aos-delay="400"><div class="icon-box d-flex flex-column justify-content-center align-items-center"><i class="bi bi-flower1"></i><h3>Visión holística</h3><p>Herramientas para promover equilibrio físico, emocional y ambiental de forma responsable.</p></div></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="areas" class="features section">
    <div class="container">
      <div class="row gy-4">
        <?php
        $areas = [
          ['bi-flower2', 'Fundamentos de naturopatía'], ['bi-heart-pulse', 'Bienestar integral'],
          ['bi-cup-hot', 'Nutrición consciente'], ['bi-tree', 'Plantas y recursos naturales'],
          ['bi-person-arms-up', 'Terapias manuales'], ['bi-wind', 'Equilibrio energético'],
          ['bi-journal-check', 'Certificaciones'], ['bi-people', 'Comunidad de aprendizaje'],
        ];
        foreach ($areas as $index => [$icon, $label]): ?>
          <div class="col-lg-3 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?= ($index + 1) * 100 ?>">
            <div class="features-item"><i class="bi <?= $icon ?>"></i><h3><a href="<?= atenea_url('src/website/courses.php') ?>" class="stretched-link"><?= atenea_e($label) ?></a></h3></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="capacitaciones" class="courses section light-background">
    <div class="container section-title" data-aos="fade-up"><h2>Capacitaciones</h2><p>Programas destacados</p></div>
    <div class="container">
      <div class="row gy-4">
        <?php
        $courses = [
          ['course-1.jpg', 'Naturopatía', 'Fundamentos de Naturopatía', 'Bases para comprender el bienestar y el cuidado natural desde una perspectiva integral.'],
          ['course-2.jpg', 'Terapias holísticas', 'Bienestar y Equilibrio', 'Herramientas prácticas para acompañar procesos de autocuidado y hábitos saludables.'],
          ['course-3.jpg', 'Especialización', 'Recursos Naturales Aplicados', 'Conocimientos para utilizar recursos naturales de manera informada, ética y responsable.'],
        ];
        foreach ($courses as $index => [$image, $category, $title, $description]): ?>
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="zoom-in" data-aos-delay="<?= ($index + 1) * 100 ?>">
            <article class="course-item">
              <img src="<?= atenea_url('src/website/assets/img/' . $image) ?>" class="img-fluid" alt="<?= atenea_e($title) ?>">
              <div class="course-content">
                <p class="category"><?= atenea_e($category) ?></p>
                <h3><a href="<?= atenea_url('src/website/course-details.php') ?>"><?= atenea_e($title) ?></a></h3>
                <p class="description"><?= atenea_e($description) ?></p>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="text-center mt-5"><a class="btn-atenea" href="<?= atenea_url('src/website/courses.php') ?>">Ver todas las capacitaciones</a></div>
    </div>
  </section>

  <section id="noticias" class="section noticias">
    <div class="container section-title" data-aos="fade-up"><h2>Noticias</h2><p>Actualidad de Atenea</p></div>
    <div class="container">
      <div class="row gy-4">
        <div class="col-lg-6" data-aos="fade-up"><div class="news-card"><i class="bi bi-megaphone"></i><div><h3>Nuevas oportunidades de formación</h3><p>Conoce nuestros próximos programas, talleres y actividades para la comunidad.</p><a href="<?= atenea_url('src/website/events.php') ?>">Leer más <i class="bi bi-arrow-right"></i></a></div></div></div>
        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100"><div class="news-card"><i class="bi bi-calendar-event"></i><div><h3>Agenda de eventos holísticos</h3><p>Participa en encuentros diseñados para compartir conocimientos y experiencias de bienestar.</p><a href="<?= atenea_url('src/website/events.php') ?>">Leer más <i class="bi bi-arrow-right"></i></a></div></div></div>
      </div>
    </div>
  </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
