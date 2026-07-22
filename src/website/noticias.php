<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/contenido.php';

$seccionNoticias = obtenerSeccionPublica('noticias') ?? [];
$noticias = obtenerNoticiasPublicadas(100);
$pageTitle = ((string) ($seccionNoticias['titulo'] ?? 'Noticias')) . ' | Atenea';
$pageDescription = (string) ($seccionNoticias['subtitulo'] ?? 'Noticias de Atenea');
$pageClass = 'news-page';
$activePage = 'noticias';
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<main class="main">
  <div class="page-title" data-aos="fade"><div class="heading"><div class="container"><div class="row justify-content-center text-center"><div class="col-lg-8"><h1><?=atenea_e((string)($seccionNoticias['titulo']??'Noticias'))?></h1><?php if(!empty($seccionNoticias['subtitulo'])):?><p class="mb-0"><?=atenea_e((string)$seccionNoticias['subtitulo'])?></p><?php endif;?></div></div></div></div><nav class="breadcrumbs" aria-label="Ruta de navegación"><div class="container"><ol><li><a href="<?=atenea_url('index.php')?>">Inicio</a></li><li class="current" aria-current="page">Noticias</li></ol></div></nav></div>
  <section class="section news-list"><div class="container"><div class="row gy-4">
    <?php foreach($noticias as $noticia):?><div class="col-lg-4 col-md-6 d-flex"><article class="news-card w-100"><a class="news-cover" href="<?=atenea_e(urlNoticia($noticia))?>"><img src="<?=rutaImagenContenido($noticia['imagen_portada'],'src/website/assets/img/events-item-1.jpg')?>" alt="<?=atenea_e($noticia['titulo'])?>"></a><div class="news-content"><p class="news-date"><i class="bi bi-calendar3"></i> <?=atenea_e(fechaNoticia($noticia['fecha_publicacion']))?></p><h2 class="h4"><a href="<?=atenea_e(urlNoticia($noticia))?>"><?=atenea_e($noticia['titulo'])?></a></h2><p><?=atenea_e($noticia['resumen'])?></p><a class="news-link" href="<?=atenea_e(urlNoticia($noticia))?>">Leer noticia <i class="bi bi-arrow-right"></i></a></div></article></div><?php endforeach;?>
    <?php if(!$noticias):?><div class="col-12"><div class="news-empty"><i class="bi bi-newspaper fs-1"></i><h2 class="h4 mt-3">No hay noticias publicadas</h2><p class="mb-0">Vuelve pronto para consultar novedades de Atenea.</p></div></div><?php endif;?>
  </div></div></section>
</main>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
