<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/google_oauth.php';
require_once dirname(__DIR__, 2) . '/includes/portal_estudiante.php';

if (usuarioAutenticado()) {
    redirigirPorRol();
}

$errores = is_array($_SESSION['registro_errores'] ?? null) ? $_SESSION['registro_errores'] : [];
$datos = is_array($_SESSION['registro_datos'] ?? null) ? $_SESSION['registro_datos'] : [];
unset($_SESSION['registro_errores'], $_SESSION['registro_datos']);
$departamentos = obtenerDepartamentos();
$googleDisponible = googleDisponible();
$fechaMaxima = date('Y-m-d');
$codigoTelefono = (string) ($datos['codigo_telefono'] ?? '+503');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= atenea_e(obtenerConfiguracionPortalEstudiante('registro_titulo')) ?> | Atenea</title>
  <link rel="icon" href="<?= atenea_url('img/atenea-logo.png') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/core/libs.min.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/hope-ui.min.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/login/auth.css') ?>">
</head>
<body>
<div class="wrapper">
  <section class="login-content">
    <div class="row m-0 align-items-stretch bg-white min-vh-100">
      <div class="col-lg-7 py-4">
        <div class="row justify-content-center"><div class="col-xl-10">
          <div class="card card-transparent shadow-none auth-card"><div class="card-body">
            <a href="<?= atenea_url('index.php') ?>" class="navbar-brand atenea-auth-logo d-flex justify-content-center mb-3"><img src="<?= atenea_url(obtenerConfiguracionPortalEstudiante('portal_logo')) ?>" alt="Atenea Escuela de Naturopatía Holística"></a>
            <h2 class="text-center"><?= atenea_e(obtenerConfiguracionPortalEstudiante('registro_titulo')) ?></h2>
            <p class="text-center"><?= atenea_e(obtenerConfiguracionPortalEstudiante('registro_subtitulo')) ?></p>
            <?php if ($errores): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $error): ?><li><?= atenea_e((string) $error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

            <form method="post" action="<?= atenea_url('src/auth/procesar_registro.php') ?>">
              <input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>">
              <div class="row">
                <div class="col-md-6 form-group"><label class="form-label" for="nombre">Nombre</label><input class="form-control" id="nombre" name="nombre" maxlength="100" value="<?= atenea_e((string) ($datos['nombre'] ?? '')) ?>" required></div>
                <div class="col-md-6 form-group"><label class="form-label" for="apellido">Apellido</label><input class="form-control" id="apellido" name="apellido" maxlength="100" value="<?= atenea_e((string) ($datos['apellido'] ?? '')) ?>" required></div>
                <div class="col-md-7 form-group"><label class="form-label" for="correo">Correo electrónico</label><input class="form-control" type="email" id="correo" name="correo" maxlength="190" value="<?= atenea_e((string) ($datos['correo'] ?? '')) ?>" autocomplete="email" required></div>
                <div class="col-md-5 form-group"><label class="form-label" for="fecha_nacimiento">Fecha de nacimiento</label><input class="form-control" type="date" id="fecha_nacimiento" name="fecha_nacimiento" max="<?= $fechaMaxima ?>" value="<?= atenea_e((string) ($datos['fecha_nacimiento'] ?? '')) ?>" required></div>
                <div class="col-md-5 form-group"><label class="form-label" for="dui">DUI</label><input class="form-control" id="dui" name="dui" inputmode="numeric" maxlength="10" pattern="\d{8}-\d" placeholder="00000000-0" value="<?= atenea_e((string) ($datos['dui'] ?? '')) ?>" required></div>
                <div class="col-md-3 form-group"><label class="form-label" for="codigo_telefono">Código internacional</label><select class="form-select" id="codigo_telefono" name="codigo_telefono" required><?php foreach (['+503'=>'🇸🇻 El Salvador +503','+502'=>'🇬🇹 Guatemala +502','+504'=>'🇭🇳 Honduras +504','+505'=>'🇳🇮 Nicaragua +505','+506'=>'🇨🇷 Costa Rica +506','+507'=>'🇵🇦 Panamá +507','+52'=>'🇲🇽 México +52','+1'=>'🇺🇸/🇨🇦 +1'] as $codigo=>$etiqueta): ?><option value="<?= $codigo ?>" <?= $codigoTelefono === $codigo ? 'selected' : '' ?>><?= $etiqueta ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4 form-group"><label class="form-label" for="telefono">Número de teléfono</label><input class="form-control" id="telefono" name="telefono" inputmode="numeric" maxlength="15" pattern="\d{7,15}" value="<?= atenea_e((string) ($datos['telefono'] ?? '')) ?>" required></div>
                <div class="col-md-4 form-group"><label class="form-label" for="departamento_id">Departamento</label><select class="form-select" id="departamento_id" name="departamento_id" data-selected="<?= (int) ($datos['departamento_id'] ?? 0) ?>" required><option value="">Seleccione…</option><?php foreach ($departamentos as $departamento): ?><option value="<?= (int) $departamento['id'] ?>" <?= (int) ($datos['departamento_id'] ?? 0) === (int) $departamento['id'] ? 'selected' : '' ?>><?= atenea_e((string) $departamento['nombre']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4 form-group"><label class="form-label" for="municipio_id">Municipio</label><select class="form-select" id="municipio_id" name="municipio_id" data-selected="<?= (int) ($datos['municipio_id'] ?? 0) ?>" required disabled><option value="">Seleccione…</option></select></div>
                <div class="col-md-4 form-group"><label class="form-label" for="distrito_id">Distrito</label><select class="form-select" id="distrito_id" name="distrito_id" data-selected="<?= (int) ($datos['distrito_id'] ?? 0) ?>" required disabled><option value="">Seleccione…</option></select></div>
                <div class="col-12 form-group"><label class="form-label" for="direccion">Dirección completa <span class="text-muted">(opcional)</span></label><textarea class="form-control" id="direccion" name="direccion" rows="3" maxlength="500"><?= atenea_e((string) ($datos['direccion'] ?? '')) ?></textarea></div>
                <div class="col-md-6 form-group"><label class="form-label" for="password">Contraseña</label><input class="form-control" type="password" id="password" name="password" minlength="8" maxlength="255" autocomplete="new-password" required></div>
                <div class="col-md-6 form-group"><label class="form-label" for="confirmar_password">Confirmar contraseña</label><input class="form-control" type="password" id="confirmar_password" name="confirmar_password" minlength="8" maxlength="255" autocomplete="new-password" required></div>
              </div>
              <label><input type="checkbox" name="terminos" value="1" required> Acepto los términos y condiciones</label>
              <div class="text-center mt-3"><button class="btn btn-primary" type="submit"><?= atenea_e(obtenerConfiguracionPortalEstudiante('registro_texto_boton')) ?></button></div>
              <p class="text-center my-3">O continúa con</p>
              <div class="text-center"><?php if ($googleDisponible): ?><a class="btn btn-google w-100" href="<?= atenea_url('src/auth/google.php') ?>">Continuar con Google</a><?php else: ?><div class="alert alert-warning mb-0">El acceso con Google no está disponible porque falta completar su configuración.</div><?php endif; ?></div>
              <p class="text-center mt-3">¿Ya tienes una cuenta? <a href="<?= atenea_url('src/login/sign-in.php') ?>">Inicia sesión</a></p>
            </form>
          </div></div>
        </div></div>
      </div>
      <div class="col-lg-5 d-lg-block d-none bg-primary p-0 position-fixed end-0 top-0 vh-100 overflow-hidden"><img src="<?= atenea_url(obtenerConfiguracionPortalEstudiante('registro_imagen_lateral')) ?>" class="img-fluid h-100 w-100 gradient-main animated-scaleX" style="object-fit:cover" alt="Registro Atenea"></div>
    </div>
  </section>
</div>
<script src="<?= atenea_url('src/estudiantes/assets/js/core/libs.min.js') ?>"></script>
<script>
(() => {
  const endpoint = <?= json_encode(atenea_url('src/api/ubicaciones.php'), JSON_UNESCAPED_SLASHES) ?>;
  const departamento = document.getElementById('departamento_id');
  const municipio = document.getElementById('municipio_id');
  const distrito = document.getElementById('distrito_id');
  const llenar = async (select, tipo, padre, seleccionado = 0) => {
    select.innerHTML = '<option value="">Seleccione…</option>';
    select.disabled = !padre;
    if (!padre) return;
    const respuesta = await fetch(`${endpoint}?tipo=${tipo}&padre=${encodeURIComponent(padre)}`, {headers:{Accept:'application/json'}});
    if (!respuesta.ok) throw new Error('No se pudo cargar la ubicación');
    for (const item of await respuesta.json()) select.add(new Option(item.nombre, item.id, false, Number(item.id) === Number(seleccionado)));
  };
  departamento.addEventListener('change', async () => { await llenar(municipio, 'municipios', departamento.value); await llenar(distrito, 'distritos', ''); });
  municipio.addEventListener('change', () => llenar(distrito, 'distritos', municipio.value));
  if (departamento.value) llenar(municipio, 'municipios', departamento.value, municipio.dataset.selected).then(() => municipio.value && llenar(distrito, 'distritos', municipio.value, distrito.dataset.selected));
  document.getElementById('dui').addEventListener('input', event => { const d=event.target.value.replace(/\D/g,'').slice(0,9); event.target.value=d.length>8?`${d.slice(0,8)}-${d.slice(8)}`:d; });
  document.getElementById('telefono').addEventListener('input', event => { event.target.value=event.target.value.replace(/\D/g,'').slice(0,15); });
})();
</script>
</body></html>
