<?php
require_once __DIR__ . '/includes/contenido.php';
$previewToken=(string)($_GET['preview_token']??'');if($previewToken!==''){if(!validarTokenPreviewWebsite(obtenerConexion(),$previewToken)){http_response_code(403);exit('Vista previa no autorizada o expirada.');}$GLOBALS['ATENEA_WEBSITE_PREVIEW']=true;header('Cache-Control: private, no-store');header("Content-Security-Policy: frame-ancestors 'self'");}
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
    <section id="propuesta" class="section atenea-offering">
      <div class="container offering-heading text-center" data-aos="fade-up">
        <?php if($seccion['subtitulo']):?><span class="section-eyebrow"><?=atenea_e((string)$seccion['subtitulo'])?></span><?php endif;?>
        <h2><?=atenea_e((string)$seccion['titulo'])?></h2>
        <?php if($seccion['descripcion']):?><p><?=atenea_e((string)$seccion['descripcion'])?></p><?php endif;?>
      </div>
      <div class="container"><div class="row g-4 offering-grid">
        <?php foreach($elementos as $i=>$e):?><div class="col-lg-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="<?=min(($i+1)*80,400)?>"><article class="offering-card"><div class="offering-icon"><i class="bi <?=atenea_e($e['icono']?:'bi-star')?>"></i></div><h3><?=atenea_e($e['titulo'])?></h3><p><?=atenea_e((string)$e['descripcion'])?></p></article></div><?php endforeach;?>
      </div><?php if($seccion['boton_texto']):?><div class="text-center mt-4"><a href="<?=atenea_e(urlContenidoSegura($seccion['boton_url']))?>" class="btn-atenea"><?=atenea_e($seccion['boton_texto'])?></a></div><?php endif;?></div>
    </section>

  <?php elseif ($clave === 'areas'): ?>
    <section id="areas" class="features section"><div class="container"><div class="row g-3"><?php foreach($elementos as $i=>$e):?><div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-delay="<?=($i+1)*100?>"><div class="features-item h-100"><i class="bi <?=atenea_e($e['icono']?:'bi-star')?>"></i><h3><a href="<?=atenea_e(urlContenidoSegura($e['enlace']))?>" class="stretched-link"><?=atenea_e($e['titulo'])?></a></h3></div></div><?php endforeach;?></div></div></section>

  <?php elseif ($clave === 'capacitaciones'): ?>
    <section id="capacitaciones" class="courses section light-background"><div class="container section-title" data-aos="fade-up"><h2><?=atenea_e((string)$seccion['titulo'])?></h2><p><?=atenea_e((string)$seccion['subtitulo'])?></p></div><div class="container"><div class="row gy-4">
      <?php foreach($elementos as $i=>$e):?><div class="col-lg-4 col-md-6 d-flex" data-aos="zoom-in" data-aos-delay="<?=($i+1)*100?>"><article class="course-item d-flex flex-column w-100"><div class="course-cover"><img src="<?=rutaImagenContenido($e['imagen'],'src/website/assets/img/course-1.jpg')?>" alt="<?=atenea_e($e['titulo'])?>"></div><div class="course-content d-flex flex-column flex-grow-1"><div class="course-badges"><span><?=atenea_e((string)($e['tipo']?:'Capacitación'))?></span><?php if($e['nivel']):?><span><?=atenea_e((string)$e['nivel'])?></span><?php endif;?></div><div class="d-flex justify-content-between gap-3 align-items-start"><h3><a href="<?=atenea_e(urlContenidoSegura($e['enlace']))?>"><?=atenea_e($e['titulo'])?></a></h3><?php if($e['precio']!==null):?><p class="price">$<?=number_format((float)$e['precio'],2)?></p><?php endif;?></div><p class="description"><?=atenea_e((string)$e['descripcion'])?></p><dl class="course-meta"><div><dt><i class="bi bi-clock"></i> Duración</dt><dd><?=atenea_e((string)($e['duracion']?:'Por definir'))?></dd></div><div><dt><i class="bi bi-person"></i> Instructor</dt><dd><?=atenea_e((string)($e['instructor']?:'Por asignar'))?></dd></div></dl><?php if($e['texto_boton']):?><a class="course-action mt-auto" href="<?=atenea_e(urlContenidoSegura($e['enlace']))?>"><?=atenea_e($e['texto_boton'])?> <i class="bi bi-arrow-right"></i></a><?php endif;?></div></article></div><?php endforeach;?>
    </div><?php if($seccion['boton_texto']):?><div class="text-center mt-5"><a class="btn-atenea" href="<?=atenea_e(urlContenidoSegura($seccion['boton_url']))?>"><?=atenea_e($seccion['boton_texto'])?></a></div><?php endif;?></div></section>

  <?php elseif ($clave === 'noticias'): ?>
    <?php $noticiasInicio=$contenidoInicio['noticias']??[]; ?>
    <section id="noticias" class="section noticias"><div class="container section-title" data-aos="fade-up"><h2><?=atenea_e((string)$seccion['titulo'])?></h2><p><?=atenea_e((string)$seccion['subtitulo'])?></p></div><div class="container"><div class="row gy-4"><?php foreach($noticiasInicio as $i=>$noticia):?><div class="col-lg-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="<?=$i*100?>"><article class="news-card w-100"><a class="news-cover" href="<?=atenea_e(urlNoticia($noticia))?>"><img src="<?=rutaImagenContenido($noticia['imagen_portada'],'src/website/assets/img/events-item-1.jpg')?>" alt="<?=atenea_e($noticia['titulo'])?>"></a><div class="news-content"><p class="news-date"><i class="bi bi-calendar3"></i> <?=atenea_e(fechaNoticia($noticia['fecha_publicacion']))?></p><h3><a href="<?=atenea_e(urlNoticia($noticia))?>"><?=atenea_e($noticia['titulo'])?></a></h3><p><?=atenea_e($noticia['resumen'])?></p><a class="news-link" href="<?=atenea_e(urlNoticia($noticia))?>">Leer noticia <i class="bi bi-arrow-right"></i></a></div></article></div><?php endforeach;?></div><?php if($seccion['boton_texto']):?><div class="text-center mt-5"><a class="btn-atenea" href="<?=atenea_e(urlContenidoSegura($seccion['boton_url']))?>"><?=atenea_e($seccion['boton_texto'])?></a></div><?php endif;?></div></section>

  <?php else: ?>
    <section id="<?=atenea_e($clave)?>" class="section"><div class="container" data-aos="fade-up"><div class="section-title"><h2><?=atenea_e((string)$seccion['titulo'])?></h2><p><?=atenea_e((string)$seccion['subtitulo'])?></p></div><p><?=atenea_e((string)$seccion['descripcion'])?></p></div></section>
  <?php endif; ?>
<?php endforeach; ?>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>


