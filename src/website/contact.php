<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/session.php';
require_once dirname(__DIR__, 2) . '/includes/mail_config.php';

$configuracionCorreo = configuracionCorreoAtenea();
$contactoDisponible = configuracionContactoCompleta($configuracionCorreo)
    && is_file(dirname(__DIR__, 2) . '/includes/mail/vendor/autoload.php');
$contactoFlash = is_array($_SESSION['contacto_flash'] ?? null) ? $_SESSION['contacto_flash'] : [];
$contactoDatos = is_array($_SESSION['contacto_datos'] ?? null) ? $_SESSION['contacto_datos'] : [];
unset($_SESSION['contacto_flash'], $_SESSION['contacto_datos']);

$formularios = is_array($_SESSION['contacto_formularios'] ?? null) ? $_SESSION['contacto_formularios'] : [];
$formularios = array_filter($formularios, static fn($inicio): bool => is_int($inicio) && $inicio >= time() - 1200);
$formularioId = bin2hex(random_bytes(16));
$formularios[$formularioId] = time();
$_SESSION['contacto_formularios'] = array_slice($formularios, -10, null, true);

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
    <div class="col-lg-8">
      <form action="<?= atenea_url('src/website/forms/contact.php') ?>" method="post" class="php-email-form" data-contact-prg="true" data-aos="fade-up" data-aos-delay="100">
        <input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>">
        <input type="hidden" name="formulario_id" value="<?= $formularioId ?>">
        <div class="position-absolute" style="left:-10000px;top:auto;width:1px;height:1px;overflow:hidden" aria-hidden="true"><label for="website">No completar este campo</label><input type="text" id="website" name="website" tabindex="-1" autocomplete="off"></div>
        <div class="row gy-4">
          <div class="col-md-6"><input type="text" name="name" class="form-control" placeholder="Nombre" maxlength="100" value="<?= atenea_e((string) ($contactoDatos['name'] ?? '')) ?>" required></div>
          <div class="col-md-6"><input type="email" name="email" class="form-control" placeholder="Correo electrónico" maxlength="190" value="<?= atenea_e((string) ($contactoDatos['email'] ?? '')) ?>" required></div>
          <div class="col-md-12"><input type="text" name="subject" class="form-control" placeholder="Asunto" maxlength="150" value="<?= atenea_e((string) ($contactoDatos['subject'] ?? '')) ?>" required></div>
          <div class="col-md-12"><textarea name="message" rows="6" class="form-control" placeholder="Mensaje" maxlength="5000" required><?= atenea_e((string) ($contactoDatos['message'] ?? '')) ?></textarea></div>
          <?php if (($configuracionCorreo['recaptcha_site_key'] ?? '') !== ''): ?><div class="col-md-12"><div class="g-recaptcha" data-sitekey="<?= atenea_e((string) $configuracionCorreo['recaptcha_site_key']) ?>"></div></div><?php endif; ?>
          <div class="col-md-12 text-center">
            <div class="loading">Enviando</div>
            <div class="error-message <?= ($contactoFlash['tipo'] ?? '') === 'error' ? 'd-block' : '' ?>"><?= atenea_e((string) (($contactoFlash['tipo'] ?? '') === 'error' ? $contactoFlash['mensaje'] : '')) ?></div>
            <div class="sent-message <?= ($contactoFlash['tipo'] ?? '') === 'exito' ? 'd-block' : '' ?>"><?= atenea_e((string) (($contactoFlash['tipo'] ?? '') === 'exito' ? $contactoFlash['mensaje'] : '')) ?></div>
            <?php if (!$contactoDisponible): ?><div class="alert alert-warning mt-3">El formulario de contacto está temporalmente fuera de servicio.</div><?php endif; ?>
            <button type="submit" <?= !$contactoDisponible ? 'disabled' : '' ?>>Enviar mensaje</button>
          </div>
        </div>
      </form>
    </div>
  </div></div></section>
</main>
<?php if (($configuracionCorreo['recaptcha_site_key'] ?? '') !== ''): ?><script src="https://www.google.com/recaptcha/api.js" async defer></script><?php endif; ?>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
