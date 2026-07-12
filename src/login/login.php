<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';

if (usuarioAutenticado()) {
    redirigirPorRol();
}

$mensaje = isset($_SESSION['mensaje_auth']) ? (string) $_SESSION['mensaje_auth'] : '';
unset($_SESSION['mensaje_auth']);
$correoAnterior = isset($_SESSION['login_correo']) ? (string) $_SESSION['login_correo'] : '';
unset($_SESSION['login_correo']);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar sesión | Atenea</title>
  <meta name="description" content="Acceso seguro a los paneles de Atenea.">
  <link rel="icon" type="image/png" href="<?= atenea_url('img/atenea-logo.png') ?>">
  <link rel="apple-touch-icon" href="<?= atenea_url('img/atenea-logo.png') ?>">
  <link href="<?= atenea_url('src/website/assets/vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?= atenea_url('src/website/assets/vendor/bootstrap-icons/bootstrap-icons.css') ?>" rel="stylesheet">
  <style>
    :root{--atenea-dorado:#c49a3a;--atenea-dorado-oscuro:#8f6b20;--atenea-negro:#171717;--atenea-gris:#5f6368}
    body{min-height:100vh;background:linear-gradient(135deg,#f8f5ed,#fff);color:var(--atenea-gris)}
    .login-shell{min-height:100vh}.login-card{max-width:470px;border:0;border-radius:20px;box-shadow:0 20px 60px rgba(23,23,23,.12)}
    .login-logo{width:190px;height:110px;object-fit:contain}.form-control:focus{border-color:var(--atenea-dorado);box-shadow:0 0 0 .25rem rgba(196,154,58,.18)}
    .btn-atenea{background:var(--atenea-dorado);border-color:var(--atenea-dorado);color:#fff}.btn-atenea:hover{background:var(--atenea-dorado-oscuro);border-color:var(--atenea-dorado-oscuro);color:#fff}
    a{color:var(--atenea-dorado-oscuro)}@media(max-width:575px){.login-card{border-radius:14px}.login-logo{width:155px;height:90px}}
  </style>
</head>
<body>
  <main class="container login-shell d-flex align-items-center justify-content-center py-4">
    <section class="card login-card w-100" aria-labelledby="login-title">
      <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4"><a href="<?= atenea_url('index.php') ?>"><img class="login-logo" src="<?= atenea_url('img/atenea-logo.png') ?>" alt="Atenea Escuela de Naturopatía Holística"></a><h1 id="login-title" class="h3 text-dark">Iniciar sesión</h1><p>Ingresa a tu espacio de Atenea.</p></div>
        <?php if ($mensaje !== ''): ?><div class="alert alert-warning" role="alert"><?= atenea_e($mensaje) ?></div><?php endif; ?>
        <form method="post" action="<?= atenea_url('src/auth/procesar_login.php') ?>" novalidate>
          <input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>">
          <div class="mb-3"><label class="form-label" for="correo">Correo electrónico</label><input class="form-control form-control-lg" type="email" id="correo" name="correo" value="<?= atenea_e($correoAnterior) ?>" maxlength="190" autocomplete="username" required></div>
          <div class="mb-4"><label class="form-label" for="password">Contraseña</label><input class="form-control form-control-lg" type="password" id="password" name="password" maxlength="255" autocomplete="current-password" required></div>
          <button class="btn btn-atenea btn-lg w-100" type="submit">Ingresar</button>
        </form>
        <p class="text-center mt-4 mb-0"><a href="<?= atenea_url('index.php') ?>"><i class="bi bi-arrow-left"></i> Volver al inicio</a></p>
      </div>
    </section>
  </main>
</body>
</html>
