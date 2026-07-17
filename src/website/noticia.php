<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/contenido.php';

$slug = strtolower(trim((string) ($_GET['slug'] ?? '')));
$noticia = obtenerNoticiaPublicada($slug);
$noEncontrada = $noticia === null;
if ($noEncontrada) http_response_code(404);

$pageTitle = $noEncontrada ? 'Noticia no encontrada | Atenea' : (string) $noticia['titulo'] . ' | Atenea';
$pageDescription = $noEncontrada ? 'La noticia solicitada no está disponible.' : (string) $noticia['resumen'];
$pageClass = 'news-detail-page';
$activePage = 'noticias';
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<main class="main">
<?php if($noEncontrada):?>
  <section class="section"><div class="container py-5 text-center"><div class="news-empty"><span class="display-1 fw-bold">404</span><h1 class="h2">Noticia no encontrada</h1><p>La noticia no existe, no está publicada o ya no se encuentra disponible.</p><a class="btn-atenea" href="<?=atenea_url('src/website/noticias.php')?>">Volver a Noticias</a></div></div></section>
<?php else:?>
  <section class="page-title light-background"><div class="container"><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?=atenea_url('index.php')?>">Inicio</a></li><li class="breadcrumb-item"><a href="<?=atenea_url('src/website/noticias.php')?>">Noticias</a></li><li class="breadcrumb-item active" aria-current="page"><?=atenea_e($noticia['titulo'])?></li></ol></nav></div></section>
  <article class="section"><div class="container news-detail"><p class="news-date"><i class="bi bi-calendar3"></i> <?=atenea_e(fechaNoticia($noticia['fecha_publicacion']))?><?php if($noticia['autor']):?> <span class="mx-2">·</span><i class="bi bi-person"></i> <?=atenea_e($noticia['autor'])?><?php endif;?></p><h1><?=atenea_e($noticia['titulo'])?></h1><p class="lead"><?=atenea_e($noticia['resumen'])?></p><?php if($noticia['imagen_portada']):?><img class="news-detail-cover" src="<?=rutaImagenContenido($noticia['imagen_portada'],'src/website/assets/img/events-item-1.jpg')?>" alt="<?=atenea_e($noticia['titulo'])?>"><?php endif;?><div class="news-detail-content"><?=atenea_e($noticia['contenido'])?></div><div class="mt-5"><a class="btn-atenea" href="<?=atenea_url('src/website/noticias.php')?>"><i class="bi bi-arrow-left"></i> Todas las noticias</a></div></div></article>
<?php endif;?>
</main>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
