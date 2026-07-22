<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/portal_estudiante.php';
require_once dirname(__DIR__, 2) . '/includes/alerts.php';

$mensaje = (string) ($_SESSION['recuperacion_mensaje'] ?? '');
$tipo = ($_SESSION['recuperacion_tipo'] ?? 'success') === 'danger' ? 'danger' : 'success';
unset($_SESSION['recuperacion_mensaje'], $_SESSION['recuperacion_tipo']);
$logo = atenea_url(obtenerConfiguracionPortalEstudiante('portal_logo'));
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Recuperar contraseña | Atenea</title><link rel="icon" type="image/png" href="<?= $logo ?>"><link rel="stylesheet" href="<?= atenea_url('src/estudiantes/dashboard_estudiantes/dashboard/assets/vendors/css/vendor.bundle.base.css') ?>"><link rel="stylesheet" href="<?= atenea_url('src/estudiantes/dashboard_estudiantes/dashboard/assets/css/style.css') ?>"><link rel="stylesheet" href="<?= atenea_url('src/login/auth.css') ?>"><?php ateneaAlertasHead(); ?></head>
<body><div class="wrapper"><section class="login-content"><div class="row m-0 align-items-center bg-white min-vh-100"><div class="col-md-6 py-4"><div class="row justify-content-center"><div class="col-md-10 col-xl-8"><div class="card card-transparent shadow-none auth-card"><div class="card-body">
<a href="<?= atenea_url('index.php') ?>" class="navbar-brand atenea-auth-logo d-flex justify-content-center mb-3"><img src="<?= $logo ?>" alt="Atenea Escuela de Naturopatía Holística"></a>
<h1 class="h2 text-center">Recuperar contraseña</h1><p class="text-center">Ingresa tu correo y, si existe una cuenta, enviaremos un enlace válido durante 30 minutos.</p>
<?php if ($mensaje !== ''): ?><noscript><div class="alert alert-<?= $tipo ?>" role="alert"><?= atenea_e($mensaje) ?></div></noscript><?php endif; ?>
<form method="post" action="<?= atenea_url('src/auth/solicitar-restablecimiento.php') ?>"><input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>"><div class="form-group"><label class="form-label" for="correo">Correo electrónico</label><input class="form-control" type="email" id="correo" name="correo" maxlength="190" autocomplete="email" required></div><div class="d-grid"><button class="btn btn-primary" type="submit">Enviar enlace de recuperación</button></div></form>
<p class="mt-4 mb-2 text-center"><a href="<?= atenea_url('src/login/sign-in.php') ?>">← Volver al inicio de sesión</a></p><p class="mb-0 text-center"><a class="back-to-site" href="<?= atenea_url('index.php') ?>">← Volver al sitio principal</a></p>
</div></div></div></div></div><div class="col-md-6 d-md-block d-none bg-primary p-0 vh-100 overflow-hidden"><img src="<?= recursoPortalEstudiante('login_imagen_lateral','src/estudiantes/dashboard_estudiantes/dashboard/assets/images/dashboard/darkBG.png') ?>" class="img-fluid gradient-main animated-scaleX" alt="Recuperación de cuenta Atenea"></div></div></section></div><script src="<?= atenea_url('src/estudiantes/dashboard_estudiantes/dashboard/assets/vendors/js/vendor.bundle.base.js') ?>"></script><?php ateneaAlertasScripts($mensaje !== '' ? ['type' => $tipo, 'title' => $tipo === 'success' ? 'Solicitud enviada' : 'No fue posible continuar', 'message' => $mensaje] : null); ?></body></html>
