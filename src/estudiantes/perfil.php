<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
exigirRol(['usuario']);
$completar = isset($_GET['completar']) && $_GET['completar'] === '1';
require_once dirname(__DIR__, 2) . '/includes/portal_estudiante_layout.php';
$portal = portalEstudianteCabecera('Mi perfil', 'perfil', 'Administra tus datos personales, dirección y seguridad.', true);
$perfil = $portal['perfil'];

if ($completar && !datosPerfilCompletos($perfil)):
    $errores = is_array($_SESSION['google_perfil_errores'] ?? null) ? $_SESSION['google_perfil_errores'] : [];
    $datos = is_array($_SESSION['google_perfil_datos'] ?? null) ? $_SESSION['google_perfil_datos'] : [];
    unset($_SESSION['google_perfil_errores'], $_SESSION['google_perfil_datos']);
    $departamentos = obtenerDepartamentos();
    $valor = static fn(string $clave, string $predeterminado = ''): string => (string) ($datos[$clave] ?? $perfil[$clave] ?? $predeterminado);
    $zona = new DateTimeZone('America/El_Salvador');
    $hoy = new DateTimeImmutable('today', $zona);
    $fechaMaxima = $hoy->modify('-18 years')->format('Y-m-d');
    $fechaMinima = $hoy->modify('-120 years')->format('Y-m-d');
    $clase = static fn(string $campo): string => isset($errores[$campo]) ? ' is-invalid' : '';
    $mensaje = static function (string $campo) use ($errores): void {
        if (isset($errores[$campo])) {
            echo '<div class="invalid-feedback d-block" data-google-error="' . atenea_e($campo) . '">' . atenea_e((string) $errores[$campo]) . '</div>';
        } else {
            echo '<div class="invalid-feedback" data-google-error="' . atenea_e($campo) . '"></div>';
        }
    };
?>
<div class="row">
  <div class="col-xl-9 mx-auto">
    <div class="card">
      <div class="card-body p-4 p-lg-5">
        <div class="mb-4">
          <span class="badge bg-warning text-dark mb-2">Perfil pendiente</span>
          <h1 class="h3">Completa tu registro con Google</h1>
          <p class="text-muted mb-0">Google proporcionó tu identidad básica. Confirma los datos restantes para habilitar el portal académico.</p>
        </div>
        <?php if ($errores): ?>
          <div class="alert alert-danger" role="alert" data-google-error-summary>
            <strong>Revisa los campos señalados.</strong>
            <?php if (isset($errores['general'])): ?><div class="mt-1"><?=atenea_e((string) $errores['general'])?></div><?php endif; ?>
          </div>
        <?php endif; ?>

        <form method="post" action="<?=atenea_url('src/auth/completar-perfil-google.php')?>" class="row g-3" data-google-profile-form novalidate>
          <input type="hidden" name="csrf_token" value="<?=atenea_e(obtenerTokenCsrf())?>">
          <div class="col-md-6">
            <label class="form-label" for="gp_nombre">Nombres</label>
            <input class="form-control<?=$clase('nombre')?>" id="gp_nombre" name="nombre" minlength="2" maxlength="60" autocomplete="given-name" value="<?=atenea_e($valor('nombre'))?>" required>
            <?php $mensaje('nombre'); ?>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="gp_apellido">Apellidos</label>
            <input class="form-control<?=$clase('apellido')?>" id="gp_apellido" name="apellido" minlength="2" maxlength="60" autocomplete="family-name" value="<?=atenea_e($valor('apellido'))?>" required>
            <?php $mensaje('apellido'); ?>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="gp_fecha">Fecha de nacimiento</label>
            <input class="form-control<?=$clase('fecha_nacimiento')?>" type="date" id="gp_fecha" name="fecha_nacimiento" min="<?=$fechaMinima?>" max="<?=$fechaMaxima?>" data-profile-birthdate value="<?=atenea_e($valor('fecha_nacimiento'))?>" required>
            <div class="form-text">Formato visual: dd/mm/aaaa. Debes tener al menos 18 años.</div>
            <?php $mensaje('fecha_nacimiento'); ?>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="gp_dui">DUI</label>
            <input class="form-control<?=$clase('dui')?>" id="gp_dui" name="dui" inputmode="numeric" maxlength="10" pattern="\d{8}-\d" placeholder="00000000-0" value="<?=atenea_e($valor('dui'))?>" required>
            <div class="form-text">Formato 00000000-0.</div>
            <?php $mensaje('dui'); ?>
          </div>
          <div class="col-md-4">
            <label class="form-label" for="gp_codigo">Prefijo telefónico</label>
            <select class="form-select" id="gp_codigo" name="codigo_telefono" required>
              <?php $codigo = $valor('codigo_telefono', '+503'); foreach (['+503'=>'El Salvador +503','+502'=>'Guatemala +502','+504'=>'Honduras +504','+505'=>'Nicaragua +505','+506'=>'Costa Rica +506','+507'=>'Panamá +507','+52'=>'México +52','+1'=>'EE. UU./Canadá +1'] as $clave => $etiqueta): ?>
                <option value="<?=$clave?>" <?=$codigo === $clave ? 'selected' : ''?>><?=atenea_e($etiqueta)?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-8">
            <label class="form-label" for="gp_telefono">Teléfono</label>
            <input class="form-control<?=$clase('telefono')?>" id="gp_telefono" name="telefono" inputmode="numeric" maxlength="15" autocomplete="tel-national" value="<?=atenea_e($valor('telefono'))?>" required>
            <?php $mensaje('telefono'); ?>
          </div>
          <div class="col-md-4">
            <label class="form-label" for="gp_departamento">Departamento</label>
            <select class="form-select<?=$clase('ubicacion')?>" id="gp_departamento" name="departamento_id" required>
              <option value="">Seleccione…</option>
              <?php foreach ($departamentos as $departamento): ?>
                <option value="<?=$departamento['id']?>" <?=(int) $valor('departamento_id') === (int) $departamento['id'] ? 'selected' : ''?>><?=atenea_e((string) $departamento['nombre'])?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label" for="gp_municipio">Municipio</label>
            <select class="form-select<?=$clase('ubicacion')?>" id="gp_municipio" name="municipio_id" data-selected="<?=(int) $valor('municipio_id')?>" required disabled><option value="">Seleccione…</option></select>
          </div>
          <div class="col-md-4">
            <label class="form-label" for="gp_distrito">Distrito</label>
            <select class="form-select<?=$clase('ubicacion')?>" id="gp_distrito" name="distrito_id" data-selected="<?=(int) $valor('distrito_id')?>" required disabled><option value="">Seleccione…</option></select>
            <?php $mensaje('ubicacion'); ?>
          </div>
          <div class="col-12">
            <label class="form-label" for="gp_direccion">Dirección completa</label>
            <textarea class="form-control<?=$clase('direccion')?>" id="gp_direccion" name="direccion" rows="3" minlength="8" maxlength="250" autocomplete="street-address" required><?=atenea_e($valor('direccion'))?></textarea>
            <?php $mensaje('direccion'); ?>
          </div>
          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input<?=$clase('terminos')?>" type="checkbox" id="gp_terminos" name="terminos" value="1" required>
              <label class="form-check-label" for="gp_terminos">Acepto los términos y condiciones y confirmo que los datos son correctos.</label>
              <?php $mensaje('terminos'); ?>
            </div>
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button class="btn btn-primary" type="submit" data-google-submit>
              <span data-google-submit-label>Guardar y habilitar mi cuenta</span>
              <span class="d-none" data-google-submit-loading><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Guardando...</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>window.ATENEA_GOOGLE_PROFILE={endpoint:<?=json_encode(atenea_url('src/api/ubicaciones.php'), JSON_UNESCAPED_SLASHES)?>,minDate:<?=json_encode($fechaMinima)?>,maxDate:<?=json_encode($fechaMaxima)?>};</script>
<script src="<?=atenea_url('src/website/assets/js/google-profile.js')?>" defer></script>
<?php else: ?>
<div class="row"><div class="col-lg-8 mx-auto"><div class="card"><div class="card-body p-4"><div class="d-flex flex-column flex-md-row align-items-center gap-4"><img src="<?=atenea_e(rutaFotoPerfil($perfil))?>" class="avatar avatar-100 avatar-rounded" data-atenea-current-avatar alt="Foto de perfil"><div class="flex-grow-1 text-center text-md-start"><h1 class="h3 mb-1" data-atenea-current-name><?=atenea_e(trim((string)$perfil['nombre'].' '.(string)$perfil['apellido']))?></h1><p class="text-muted mb-2"><?=atenea_e((string)$perfil['correo'])?> · <?=atenea_e(etiquetaRol((string)$perfil['rol']))?></p><p>Actualiza tus datos, fotografía, dirección, correo y contraseña desde un único perfil seguro.</p><button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalPerfil">Abrir mi perfil</button></div></div></div></div></div></div>
<?php endif; ?>
<?php portalEstudiantePie(); ?>
