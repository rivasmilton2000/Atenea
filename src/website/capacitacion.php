<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/auth.php';
require_once dirname(__DIR__,2).'/includes/capacitaciones.php';
$slug=strtolower(trim((string)($_GET['slug']??'')));
$capacitacion=capacitacionPublicaPorSlug($slug);
if(!$capacitacion){http_response_code(404);$pageTitle='Capacitación no encontrada | Atenea';$pageDescription='La capacitación solicitada no está disponible.';}
else{$pageTitle=$capacitacion['nombre'].' | Atenea';$pageDescription=(string)$capacitacion['descripcion_corta'];}
require dirname(__DIR__,2).'/includes/header.php';
?>
<main class="main">
<?php if(!$capacitacion):?>
 <section class="section"><div class="container text-center py-5"><h1>Capacitación no encontrada</h1><p>El contenido solicitado no existe o ya no está publicado.</p><a class="btn-atenea" href="<?=atenea_url('src/website/courses.php')?>">Ver capacitaciones</a></div></section>
<?php else:?>
 <section class="section"><div class="container"><div class="row gy-5">
  <div class="col-lg-6"><img class="img-fluid rounded-4 w-100" style="max-height:520px;object-fit:cover" src="<?=rutaImagenContenido($capacitacion['imagen'],'src/website/assets/img/course-1.jpg')?>" alt="<?=atenea_e($capacitacion['nombre'])?>"></div>
  <div class="col-lg-6"><span class="badge text-bg-success mb-3"><?=atenea_e(ucfirst($capacitacion['tipo']))?></span><h1><?=atenea_e($capacitacion['nombre'])?></h1><p class="lead"><?=atenea_e($capacitacion['descripcion_corta'])?></p>
   <dl class="row"><dt class="col-5">Nivel</dt><dd class="col-7"><?=atenea_e($capacitacion['nivel']?:'No especificado')?></dd><dt class="col-5">Modalidad</dt><dd class="col-7"><?=atenea_e(ucfirst($capacitacion['modalidad']))?></dd><dt class="col-5">Duración</dt><dd class="col-7"><?=atenea_e($capacitacion['duracion']?:'No especificada')?></dd><dt class="col-5">Precio</dt><dd class="col-7"><strong>$<?=number_format((float)$capacitacion['precio'],2)?> USD</strong></dd></dl>
   <?php if(!empty($_SESSION['usuario_id'])&&($_SESSION['usuario_rol']??'')==='usuario'):?><form method="post" action="<?=atenea_url('src/pagos/crear-checkout-capacitacion.php')?>"><input type="hidden" name="csrf_token" value="<?=atenea_e(obtenerTokenCsrf())?>"><input type="hidden" name="capacitacion_id" value="<?=$capacitacion['id']?>"><button class="btn-atenea border-0" type="submit">Pagar con Stripe</button></form><?php else:?><a class="btn-atenea" href="<?=atenea_url('src/login/sign-in.php')?>">Inicia sesión para pagar</a><?php endif;?>
  </div></div><div class="mt-5"><h2>Descripción</h2><p><?=nl2br(atenea_e($capacitacion['descripcion_completa']?:$capacitacion['descripcion']))?></p><?php if($capacitacion['objetivos']):?><h2>Objetivos</h2><p><?=nl2br(atenea_e($capacitacion['objetivos']))?></p><?php endif;?><?php if($capacitacion['requisitos']):?><h2>Requisitos</h2><p><?=nl2br(atenea_e($capacitacion['requisitos']))?></p><?php endif;?></div></div></section>
<?php endif;?>
</main>
<?php require dirname(__DIR__,2).'/includes/footer.php';
