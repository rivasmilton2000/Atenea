<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/portal_estudiante.php';
require_once dirname(__DIR__, 2) . '/includes/alerts.php';

$token = (string) ($_GET['token'] ?? $_SESSION['reset_token_retorno'] ?? '');
unset($_SESSION['reset_token_retorno']);
$tokenValido = false;
if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    try {
        $consulta = obtenerConexion()->prepare('SELECT id FROM password_reset_tokens WHERE token_hash=:hash AND user_id IS NOT NULL AND used_at IS NULL AND expires_at>NOW() LIMIT 1');
        $consulta->execute(['hash' => hash('sha256', $token)]);
        $tokenValido = (bool) $consulta->fetchColumn();
    } catch (Throwable $e) {
        error_log('Validación de recuperación Atenea: ' . $e->getMessage());
    }
}
$errores = is_array($_SESSION['reset_errores'] ?? null) ? $_SESSION['reset_errores'] : [];
unset($_SESSION['reset_errores']);
$logo = atenea_url(obtenerConfiguracionPortalEstudiante('portal_logo'));
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Nueva contraseña | Atenea</title><link rel="icon" type="image/png" href="<?= $logo ?>"><link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/core/libs.min.css') ?>"><link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/hope-ui.min.css') ?>"><link rel="stylesheet" href="<?= atenea_url('src/login/auth.css') ?>"><?php ateneaAlertasHead(); ?></head>
<body><div class="wrapper"><section class="login-content"><div class="row m-0 align-items-center bg-white min-vh-100"><div class="col-md-6 py-4"><div class="row justify-content-center"><div class="col-md-10 col-xl-8"><div class="card card-transparent shadow-none auth-card"><div class="card-body">
<a href="<?= atenea_url('index.php') ?>" class="navbar-brand atenea-auth-logo d-flex justify-content-center mb-3"><img src="<?= $logo ?>" alt="Atenea Escuela de Naturopatía Holística"></a><h1 class="h2 text-center">Crear nueva contraseña</h1>
<?php if (!$tokenValido): ?><div class="alert alert-warning" role="alert">Este enlace no es válido, ya fue utilizado o expiró.</div><div class="d-grid"><a class="btn btn-primary" href="<?= atenea_url('src/login/forgot-password.php') ?>">Solicitar un enlace nuevo</a></div>
<?php else: ?>
<p class="text-center">Usa al menos 8 caracteres e incluye una letra y un número.</p><?php if ($errores): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $error): ?><li><?= atenea_e((string) $error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" action="<?= atenea_url('src/auth/restablecer-password.php') ?>"><input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>"><input type="hidden" name="token" value="<?= atenea_e($token) ?>"><div class="form-group"><label class="form-label" for="password">Nueva contraseña</label><input class="form-control" type="password" id="password" name="password" minlength="8" maxlength="255" autocomplete="new-password" required></div><div class="form-group"><label class="form-label" for="confirmar_password">Confirmar contraseña</label><input class="form-control" type="password" id="confirmar_password" name="confirmar_password" minlength="8" maxlength="255" autocomplete="new-password" required></div><div class="d-grid"><button class="btn btn-primary" type="submit">Guardar nueva contraseña</button></div></form>
<?php endif; ?><p class="mt-4 mb-0 text-center"><a class="back-to-site" href="<?= atenea_url('index.php') ?>">← Volver al sitio principal</a></p>
</div></div></div></div></div><div class="col-md-6 d-md-block d-none bg-primary p-0 vh-100 overflow-hidden"><img src="<?= atenea_url(obtenerConfiguracionPortalEstudiante('login_imagen_lateral')) ?>" class="img-fluid gradient-main animated-scaleX" alt="Seguridad de la cuenta Atenea"></div></div></section></div><script src="<?= atenea_url('src/estudiantes/assets/js/core/libs.min.js') ?>"></script><?php ateneaAlertasScripts($errores ? ['type' => 'error', 'title' => 'Revisa la nueva contraseña', 'message' => implode("\n", array_map('strval', $errores))] : null); ?></body></html>
