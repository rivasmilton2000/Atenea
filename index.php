<?php
require_once __DIR__ . '/includes/contenido.php';
$pageTitle = 'Atenea | Escuela de Naturopatía Holística';
$pageDescription = 'Formación integral en naturopatía, terapias naturales y bienestar holístico.';
$pageClass = 'index-page';
$activePage = 'inicio';
$errorContenido = null;

try {
    $contenidoInicio = cargarContenidoInicio();
    $configSitio = $contenidoInicio['configuracion'];
    $configuracionSitio = $configSitio;
    $pageTitle = ($configSitio['nombre_sitio'] ?? 'Atenea') . ' | Escuela de Naturopatía Holística';
} catch (Throwable $e) {
    error_log('Contenido del inicio: ' . $e->getMessage());
    $contenidoInicio = ['configuracion' => [], 'secciones' => []];
    $errorContenido = 'En este momento no podemos cargar el contenido del sitio. Intenta nuevamente más tarde.';
}
require __DIR__ . '/includes/header.php';
?>
<main class="main">
<?php if ($errorContenido): ?>
  <section class="section"><div class="container py-5"><div class="alert alert-warning text-center"><?= atenea_e($errorContenido) ?></div></div></section>
<?php endif; ?>

<?php foreach ($contenidoInicio['secciones'] as $seccion): ?>
  <?php $elementos = $seccion['elementos']; $clave = $seccion['clave']; ?>
  <?php if ($clave === 'hero'): ?>
    <section id="hero" class="hero section dark-background">
      <img src="<?= rutaImagenContenido($seccion['imagen'], 'src/website/assets/img/hero-bg.jpg') ?>" alt="<?= atenea_e($seccion['titulo'] ?: 'Atenea') ?>" data-aos="fade-in">
      <div class="container"><h1 data-aos="fade-up" data-aos-delay="100"><?= atenea_e((string)$seccion['titulo']) ?></h1><p data-aos="fade-up" data-aos-delay="200"><?= atenea_e((string)$seccion['subtitulo']) ?></p>
      <?php if ($seccion['boton_texto']): ?><div class="d-flex mt-4" data-aos="fade-up" data-aos-delay="300"><a href="<?= atenea_e(urlContenidoSegura($seccion['boton_url'])) ?>" class="btn-get-started"><?= atenea_e($seccion['boton_texto']) ?></a></div><?php endif; ?></div>
    </section>

  <?php elseif ($clave === 'nosotros'): ?>
    <section id="nosotros" class="about section"><div class="container"><div class="row gy-4">
      <div class="col-lg-6 order-1 order-lg-2" data-aos="fade-up" data-aos-delay="100"><img src="<?= rutaImagenContenido($seccion['imagen']) ?>" class="img-fluid" alt="<?= atenea_e((string)$seccion['titulo']) ?>"></div>
      <div class="col-lg-6 order-2 order-lg-1 content" data-aos="fade-up" data-aos-delay="200"><h2><?= atenea_e((string)$seccion['titulo']) ?></h2><p class="fst-italic"><?= atenea_e((string)$seccion['subtitulo']) ?></p>
      <?php if ($elementos): ?><ul><?php foreach($elementos as $e): ?><li><i class="bi <?= atenea_e($e['icono'] ?: 'bi-check-circle') ?>"></i> <span><?= atenea_e($e['titulo']) ?></span></li><?php endforeach; ?></ul><?php endif; ?>
      <?php if ($seccion['boton_texto']): ?><a href="<?= atenea_e(urlContenidoSegura($seccion['boton_url'])) ?>" class="read-more"><span><?= atenea_e($seccion['boton_texto']) ?></span><i class="bi bi-arrow-right"></i></a><?php endif; ?></div>
    </div></div></section>

  <?php elseif ($clave === 'cifras'): ?>
    <section id="cifras" class="section counts light-background"><div class="container" data-aos="fade-up" data-aos-delay="100"><div class="row gy-4">
      <?php foreach($elementos as $e): $numero=preg_replace('/[^0-9]/','',(string)$e['subtitulo']); ?><div class="col-lg-3 col-md-6"><div class="stats-item text-center w-100 h-100"><span data-purecounter-start="0" data-purecounter-end="<?= atenea_e($numero ?: '0') ?>" data-purecounter-duration="1" class="purecounter"></span><p><?= atenea_e($e['titulo']) ?></p></div></div><?php endforeach; ?>
    </div></div></section>

  <?php elseif ($clave === 'propuesta'): ?>
    <section id="propuesta" class="section why-us"><div class="container"><div class="row gy-4">
      <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100"><div class="why-box"><h2><?= atenea_e((string)$seccion['titulo']) ?></h2><p><?= atenea_e((string)$seccion['descripcion']) ?></p><?php if($seccion['boton_texto']):?><div class="text-center"><a href="<?=atenea_e(urlContenidoSegura($seccion['boton_url']))?>" class="more-btn"><span><?=atenea_e($seccion['boton_texto'])?></span> <i class="bi bi-chevron-right"></i></a></div><?php endif;?></div></div>
      <div class="col-lg-8 d-flex align-items-stretch"><div class="row gy-4" data-aos="fade-up" data-aos-delay="200"><?php foreach($elementos as $i=>$e):?><div class="col-xl-4" data-aos="fade-up" data-aos-delay="<?=($i+2)*100?>"><div class="icon-box d-flex flex-column justify-content-center align-items-center"><i class="bi <?=atenea_e($e['icono']?:'bi-star')?>"></i><h3><?=atenea_e($e['titulo'])?></h3><p><?=atenea_e((string)$e['descripcion'])?></p></div></div><?php endforeach;?></div></div>
    </div></div></section>

  <?php elseif ($clave === 'areas'): ?>
    <section id="areas" class="features section"><div class="container"><div class="row gy-4"><?php foreach($elementos as $i=>$e):?><div class="col-lg-3 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?=($i+1)*100?>"><div class="features-item"><i class="bi <?=atenea_e($e['icono']?:'bi-star')?>"></i><h3><a href="<?=atenea_e(urlContenidoSegura($e['enlace']))?>" class="stretched-link"><?=atenea_e($e['titulo'])?></a></h3></div></div><?php endforeach;?></div></div></section>

  <?php elseif ($clave === 'capacitaciones'): ?>
    <section id="capacitaciones" class="courses section light-background"><div class="container section-title" data-aos="fade-up"><h2><?=atenea_e((string)$seccion['titulo'])?></h2><p><?=atenea_e((string)$seccion['subtitulo'])?></p></div><div class="container"><div class="row gy-4">
      <?php foreach($elementos as $i=>$e):?><div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="zoom-in" data-aos-delay="<?=($i+1)*100?>"><article class="course-item"><img src="<?=rutaImagenContenido($e['imagen'],'src/website/assets/img/course-1.jpg')?>" class="img-fluid" alt="<?=atenea_e($e['titulo'])?>"><div class="course-content"><p class="category"><?=atenea_e((string)$e['subtitulo'])?></p><h3><a href="<?=atenea_e(urlContenidoSegura($e['enlace']))?>"><?=atenea_e($e['titulo'])?></a></h3><p class="description"><?=atenea_e((string)$e['descripcion'])?></p><?php if($e['texto_boton']):?><a href="<?=atenea_e(urlContenidoSegura($e['enlace']))?>"><?=atenea_e($e['texto_boton'])?> <i class="bi bi-arrow-right"></i></a><?php endif;?></div></article></div><?php endforeach;?>
    </div><?php if($seccion['boton_texto']):?><div class="text-center mt-5"><a class="btn-atenea" href="<?=atenea_e(urlContenidoSegura($seccion['boton_url']))?>"><?=atenea_e($seccion['boton_texto'])?></a></div><?php endif;?></div></section>

  <?php elseif ($clave === 'noticias'): ?>
    <section id="noticias" class="section noticias"><div class="container section-title" data-aos="fade-up"><h2><?=atenea_e((string)$seccion['titulo'])?></h2><p><?=atenea_e((string)$seccion['subtitulo'])?></p></div><div class="container"><div class="row gy-4"><?php foreach($elementos as $i=>$e):?><div class="col-lg-6" data-aos="fade-up" data-aos-delay="<?=$i*100?>"><div class="news-card"><i class="bi <?=atenea_e($e['icono']?:'bi-newspaper')?>"></i><div><h3><?=atenea_e($e['titulo'])?></h3><p><?=atenea_e((string)$e['descripcion'])?></p><?php if($e['texto_boton']):?><a href="<?=atenea_e(urlContenidoSegura($e['enlace']))?>"><?=atenea_e($e['texto_boton'])?> <i class="bi bi-arrow-right"></i></a><?php endif;?></div></div></div><?php endforeach;?></div></div></section>

  <?php else: ?>
    <section id="<?=atenea_e($clave)?>" class="section"><div class="container" data-aos="fade-up"><div class="section-title"><h2><?=atenea_e((string)$seccion['titulo'])?></h2><p><?=atenea_e((string)$seccion['subtitulo'])?></p></div><p><?=atenea_e((string)$seccion['descripcion'])?></p></div></section>
  <?php endif; ?>
<?php endforeach; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>


