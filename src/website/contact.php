<?php
$pageTitle = 'Contacto | Atenea';
$pageDescription = 'Comunícate con Atenea Escuela de Naturopatía Holística.';
$pageClass = 'contact-page';
$activePage = 'contacto';
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<main class="main">
  <div class="page-title" data-aos="fade"><div class="heading"><div class="container"><div class="row justify-content-center text-center"><div class="col-lg-8"><h1>Contacto</h1><p class="mb-0">Estamos disponibles para orientarte sobre capacitaciones, eventos y productos.</p></div></div></div></div><nav class="breadcrumbs"><div class="container"><ol><li><a href="<?= atenea_url('index.php') ?>">Inicio</a></li><li class="current">Contacto</li></ol></div></nav></div>
  <section class="contact section"><div class="container" data-aos="fade-up"><div class="row gy-4">
    <div class="col-lg-4"><div class="info-item d-flex"><i class="bi bi-geo-alt flex-shrink-0"></i><div><h2>Ubicación</h2><p>El Salvador</p></div></div><div class="info-item d-flex"><i class="bi bi-envelope flex-shrink-0"></i><div><h2>Correo</h2><p>info@atenea.edu.sv</p></div></div><div class="info-item d-flex"><i class="bi bi-clock flex-shrink-0"></i><div><h2>Atención</h2><p>Lunes a viernes, horario laboral</p></div></div></div>
    <div class="col-lg-8"><form action="<?= atenea_url('src/website/forms/contact.php') ?>" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="100"><div class="row gy-4"><div class="col-md-6"><input type="text" name="name" class="form-control" placeholder="Nombre" required></div><div class="col-md-6"><input type="email" name="email" class="form-control" placeholder="Correo electrónico" required></div><div class="col-md-12"><input type="text" name="subject" class="form-control" placeholder="Asunto" required></div><div class="col-md-12"><textarea name="message" rows="6" class="form-control" placeholder="Mensaje" required></textarea></div><div class="col-md-12 text-center"><div class="loading">Enviando</div><div class="error-message"></div><div class="sent-message">Tu mensaje fue enviado. ¡Gracias!</div><button type="submit">Enviar mensaje</button></div></div></form></div>
  </div></div></section>
</main>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>

