<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/google_oauth.php';
require_once dirname(__DIR__, 2) . '/includes/portal_estudiante.php';

if (usuarioAutenticado()) redirigirPorRol();
$mensaje = (string) ($_SESSION['mensaje_auth'] ?? '');
$tipoMensaje = ($_SESSION['mensaje_auth_tipo'] ?? 'danger') === 'success' ? 'success' : 'danger';
$correo = (string) ($_SESSION['login_correo'] ?? '');
unset($_SESSION['mensaje_auth'], $_SESSION['mensaje_auth_tipo'], $_SESSION['login_correo']);
$google = obtenerConfiguracionGoogle();
$googleDisponible = googleDisponible($google);
$diagnosticoGoogle = diagnosticoGoogle($google);
$logo = atenea_url(obtenerConfiguracionPortalEstudiante('portal_logo'));
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= atenea_e(obtenerConfiguracionPortalEstudiante('login_titulo')) ?> | Atenea</title>
  <link rel="icon" type="image/png" href="<?= $logo ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/core/libs.min.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/hope-ui.min.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/login/auth.css') ?>">
</head>
<body>
<div class="wrapper">
  <section class="login-content">
    <div class="row m-0 align-items-center bg-white min-vh-100">
      <div class="col-md-6 py-4">
        <div class="row justify-content-center"><div class="col-md-10 col-xl-8">
          <div class="card card-transparent shadow-none auth-card"><div class="card-body">
            <a href="<?= atenea_url('index.php') ?>" class="navbar-brand atenea-auth-logo d-flex justify-content-center mb-3" aria-label="Ir al sitio principal de Atenea">
              <img src="<?= $logo ?>" alt="Atenea Escuela de Naturopatía Holística">
            </a>
            <h1 class="h2 mb-2 text-center"><?= atenea_e(obtenerConfiguracionPortalEstudiante('login_titulo')) ?></h1>
            <p class="text-center"><?= atenea_e(obtenerConfiguracionPortalEstudiante('login_subtitulo')) ?></p>
            <?php if ($mensaje !== ''): ?><div class="alert alert-<?= $tipoMensaje ?>" role="alert"><?= atenea_e($mensaje) ?></div><?php endif; ?>

            <form method="post" action="<?= atenea_url('src/auth/procesar_login.php') ?>">
              <input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>">
              <div class="form-group"><label class="form-label" for="correo">Correo electrónico</label><input class="form-control" type="email" id="correo" name="correo" value="<?= atenea_e($correo) ?>" maxlength="190" autocomplete="username" required></div>
              <div class="form-group"><label class="form-label" for="password">Contraseña</label><input class="form-control" type="password" id="password" name="password" maxlength="255" autocomplete="current-password" required></div>
              <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <label class="mb-0"><input type="checkbox" name="recordar" value="1"> Recordarme</label>
                <a href="<?= atenea_url('src/login/forgot-password.php') ?>">¿Olvidaste tu contraseña?</a>
              </div>
              <div class="d-grid mt-3"><button class="btn btn-primary" type="submit"><?= atenea_e(obtenerConfiguracionPortalEstudiante('login_texto_boton')) ?></button></div>
            </form>

            <div class="auth-separator" aria-hidden="true"><span>O continúa con</span></div>
            <?php if ($googleDisponible): ?>
              <a class="btn btn-google w-100" id="google-login" href="<?= atenea_url('src/auth/google.php') ?>">
                <svg class="google-icon" viewBox="0 0 18 18" aria-hidden="true"><path fill="#4285F4" d="M17.64 9.205c0-.638-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.797 2.715v2.259h2.909c1.702-1.567 2.684-3.874 2.684-6.614Z"/><path fill="#34A853" d="M9 18c2.43 0 4.468-.806 5.956-2.181l-2.909-2.259c-.806.54-1.835.859-3.047.859-2.344 0-4.328-1.585-5.037-3.714H.956v2.332A9 9 0 0 0 9 18Z"/><path fill="#FBBC05" d="M3.963 10.705A5.41 5.41 0 0 1 3.682 9c0-.592.102-1.167.281-1.705V4.963H.956A9 9 0 0 0 0 9c0 1.452.347 2.827.956 4.037l3.007-2.332Z"/><path fill="#EA4335" d="M9 3.58c1.321 0 2.507.454 3.441 1.346l2.581-2.581C13.464.892 11.426 0 9 0A9 9 0 0 0 .956 4.963l3.007 2.332C4.672 5.166 6.656 3.58 9 3.58Z"/></svg>
                <span class="google-label">Continuar con Google</span>
                <span class="spinner-border spinner-border-sm google-spinner" role="status" aria-label="Redirigiendo a Google"></span>
              </a>
            <?php else: ?>
              <div class="alert alert-warning mb-0" role="status">El acceso con Google no está disponible porque falta completar su configuración.</div>
              <?php if ($diagnosticoGoogle): ?><small class="d-block text-muted mt-2">Diagnóstico local: faltan <?= atenea_e(implode(', ', $diagnosticoGoogle)) ?>. No se muestran credenciales ni tokens.</small><?php endif; ?>
            <?php endif; ?>

            <p class="mt-3 text-center">¿Aún no tienes una cuenta? <a href="<?= atenea_url('src/login/sign-up.php') ?>">Regístrate</a></p>
            <p class="mb-0 text-center"><a class="back-to-site" href="<?= atenea_url('index.php') ?>">← Volver al sitio principal</a></p>
          </div></div>
        </div></div>
      </div>
      <div class="col-md-6 d-md-block d-none bg-primary p-0 vh-100 overflow-hidden"><img src="<?= atenea_url(obtenerConfiguracionPortalEstudiante('login_imagen_lateral')) ?>" class="img-fluid gradient-main animated-scaleX" alt="Acceso Atenea"></div>
    </div>
  </section>
</div>
<script src="<?= atenea_url('src/estudiantes/assets/js/core/libs.min.js') ?>"></script>
<script>
document.getElementById('google-login')?.addEventListener('click', function () {
  this.classList.add('is-loading');
  this.setAttribute('aria-disabled', 'true');
  this.querySelector('.google-label').textContent = 'Redirigiendo a Google…';
});
</script>
<script src="<?= atenea_url('src/estudiantes/assets/js/hope-ui.js') ?>" defer></script>
</body>
</html>
