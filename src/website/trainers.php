<?php
$pageTitle = 'Docentes | Atenea';
$pageDescription = 'Conoce al equipo docente de Atenea.';
$pageClass = 'trainers-page';
$activePage = 'docentes';
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<main class="main">
  <div class="page-title" data-aos="fade"><div class="heading"><div class="container"><div class="row justify-content-center text-center"><div class="col-lg-8"><h1>Docentes</h1><p class="mb-0">Profesionales que acompañan cada proceso de aprendizaje con experiencia, cercanía y compromiso.</p></div></div></div></div><nav class="breadcrumbs"><div class="container"><ol><li><a href="<?= atenea_url('index.php') ?>">Inicio</a></li><li class="current">Docentes</li></ol></div></nav></div>
  <section class="trainers section"><div class="container"><div class="row gy-5">
  <?php $team = [['team-1.jpg','Docente de Naturopatía','Naturopatía integral'],['team-2.jpg','Docente de Bienestar','Hábitos saludables'],['team-3.jpg','Docente de Terapias','Terapias holísticas'],['team-4.jpg','Facilitadora Académica','Acompañamiento educativo'],['team-5.jpg','Docente Especialista','Recursos naturales'],['team-6.jpg','Facilitador de Talleres','Práctica y comunidad']]; foreach ($team as $i => [$img,$name,$role]): ?>
    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i % 3 + 1) * 100 ?>"><div class="member"><div class="pic"><img src="<?= atenea_url('src/website/assets/img/team/' . $img) ?>" class="img-fluid" alt="<?= atenea_e($name) ?>"></div><div class="member-info"><h2><?= atenea_e($name) ?></h2><span><?= atenea_e($role) ?></span><p>Comparte conocimientos desde una perspectiva práctica, ética y centrada en el estudiante.</p></div></div></div>
  <?php endforeach; ?>
  </div></div></section>
</main>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>

