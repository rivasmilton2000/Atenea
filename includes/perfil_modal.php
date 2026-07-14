<?php
declare(strict_types=1);
require_once __DIR__ . '/cuenta.php';

function renderizarModalPerfil(string $contexto = 'website'): void
{
    static $renderizado = false;
    if ($renderizado || !usuarioAutenticado()) return;
    $renderizado = true;
    $perfil = obtenerPerfilUsuario((int) $_SESSION['usuario_id']);
    if (!$perfil) return;
    $estado = is_array($_SESSION['cuenta_modal'] ?? null) ? $_SESSION['cuenta_modal'] : [];
    unset($_SESSION['cuenta_modal']);
    $errores = is_array($estado['errores'] ?? null) ? $estado['errores'] : [];
    $verificacion = is_array($estado['verificacion'] ?? null) ? $estado['verificacion'] : null;
    $retorno = (string) ($_SERVER['REQUEST_URI'] ?? rutaPanelPorRol((string) $perfil['rol']));
    if (!str_starts_with($retorno, ATENEA_BASE_URL . '/')) $retorno = rutaPanelPorRol((string) $perfil['rol']);
    $departamentos = obtenerDepartamentos();
    $error = static fn(string $campo): string => isset($errores[$campo]) ? '<div class="invalid-feedback d-block">'.atenea_e((string)$errores[$campo]).'</div>' : '';
    $modalId = $contexto === 'dashboard' ? 'adminProfileModal' : 'modalPerfil';
    $modalTituloId = $modalId . 'Titulo';
    $claseFoto = $contexto === 'dashboard' ? 'cuenta-foto admin-profile-modal-photo' : 'cuenta-foto';
    ?>
    <div class="modal fade cuenta-modal" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalTituloId ?>" aria-hidden="true" data-atenea-profile-modal>
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><div><h2 class="modal-title h4 mb-1" id="<?= $modalTituloId ?>">Mi perfil</h2><p class="mb-0 text-muted small"><?= atenea_e((string)$perfil['correo']) ?> · <?= atenea_e(etiquetaRol((string)$perfil['rol'])) ?></p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <div class="modal-body">
          <?php if(!empty($estado['mensaje'])): ?><div class="alert alert-success"><?= atenea_e((string)$estado['mensaje']) ?></div><?php endif; ?>
          <?php if(isset($errores['general'])): ?><div class="alert alert-danger"><?= atenea_e((string)$errores['general']) ?></div><?php endif; ?>
          <div class="row g-4">
            <div class="col-lg-3"><div class="cuenta-resumen text-center"><img id="vistaPreviaFoto" src="<?= atenea_e(rutaFotoPerfil($perfil)) ?>" class="<?= $claseFoto ?>" data-profile-photo-preview alt="Foto de perfil"><h3 class="h5 mt-3 mb-1"><?= atenea_e(trim((string)$perfil['nombre'].' '.(string)$perfil['apellido'])) ?></h3><span class="badge bg-primary"><?= atenea_e(etiquetaRol((string)$perfil['rol'])) ?></span><dl class="text-start small mt-4 mb-0"><dt>Registro</dt><dd><?= date('d/m/Y',strtotime((string)$perfil['created_at'])) ?></dd><dt>Estado</dt><dd><?= atenea_e(ucfirst((string)$perfil['estado'])) ?></dd><dt>Google</dt><dd><?= !empty($perfil['google_id'])?'Vinculada':'No vinculada' ?></dd></dl></div></div>
            <div class="col-lg-9">
              <ul class="nav nav-pills cuenta-tabs mb-4" role="tablist"><li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#cuenta-personal" type="button">Información personal</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#cuenta-direccion" type="button">Dirección</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#cuenta-seguridad" type="button">Seguridad</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#cuenta-vinculada" type="button">Cuenta vinculada</button></li></ul>
              <div class="tab-content">
                <div class="tab-pane fade show active" id="cuenta-personal"><form method="post" enctype="multipart/form-data" action="<?= atenea_url('src/cuenta/actualizar-perfil.php') ?>" class="row g-3 cuenta-form-perfil"><input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>"><input type="hidden" name="retorno" value="<?= atenea_e($retorno) ?>">
                  <div class="col-md-6"><label class="form-label" for="cuenta_nombre">Nombre</label><input class="form-control" id="cuenta_nombre" name="nombre" maxlength="100" value="<?= atenea_e((string)$perfil['nombre']) ?>" required><?= $error('nombre') ?></div>
                  <div class="col-md-6"><label class="form-label" for="cuenta_apellido">Apellidos</label><input class="form-control" id="cuenta_apellido" name="apellido" maxlength="100" value="<?= atenea_e((string)$perfil['apellido']) ?>"><?= $error('apellido') ?></div>
                  <div class="col-md-6"><label class="form-label" for="cuenta_fecha">Fecha de nacimiento</label><input class="form-control" type="date" id="cuenta_fecha" name="fecha_nacimiento" max="<?= date('Y-m-d') ?>" value="<?= atenea_e((string)$perfil['fecha_nacimiento']) ?>" <?= $perfil['rol']==='usuario'?'required':'' ?>><?= $error('fecha_nacimiento') ?></div>
                  <div class="col-md-6"><label class="form-label" for="cuenta_dui">DUI</label><input class="form-control" id="cuenta_dui" name="dui" maxlength="10" inputmode="numeric" placeholder="00000000-0" value="<?= atenea_e((string)$perfil['dui']) ?>" <?= $perfil['rol']==='usuario'?'required':'' ?>><?= $error('dui') ?></div>
                  <div class="col-md-4"><label class="form-label" for="cuenta_codigo">Código</label><input class="form-control" id="cuenta_codigo" name="codigo_telefono" maxlength="5" value="<?= atenea_e((string)($perfil['codigo_telefono']?:'+503')) ?>"></div>
                  <div class="col-md-8"><label class="form-label" for="cuenta_telefono">Teléfono</label><input class="form-control" id="cuenta_telefono" name="telefono" maxlength="15" inputmode="numeric" value="<?= atenea_e((string)$perfil['telefono']) ?>" <?= $perfil['rol']==='usuario'?'required':'' ?>><?= $error('telefono') ?></div>
                  <div class="col-12"><label class="form-label" for="cuenta_foto">Fotografía</label><input class="form-control" type="file" id="cuenta_foto" name="foto" data-profile-photo-input accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><div class="form-text">JPG, PNG o WEBP; máximo 3 MB.</div><?= $error('foto') ?></div>
                  <div class="col-12 d-flex justify-content-end"><button class="btn btn-primary" type="submit">Guardar perfil</button></div>
                </form></div>
                <div class="tab-pane fade" id="cuenta-direccion"><form method="post" action="<?= atenea_url('src/cuenta/actualizar-perfil.php') ?>" class="row g-3"><input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>"><input type="hidden" name="retorno" value="<?= atenea_e($retorno) ?>">
                  <div class="col-md-4"><label class="form-label" for="cuenta_departamento">Departamento</label><select class="form-select" id="cuenta_departamento" name="departamento_id" <?= $perfil['rol']==='usuario'?'required':'' ?>><option value="">Seleccione…</option><?php foreach($departamentos as $d):?><option value="<?= (int)$d['id'] ?>" <?= (int)$perfil['departamento_id']===(int)$d['id']?'selected':'' ?>><?= atenea_e((string)$d['nombre']) ?></option><?php endforeach;?></select></div>
                  <div class="col-md-4"><label class="form-label" for="cuenta_municipio">Municipio</label><select class="form-select" id="cuenta_municipio" name="municipio_id" data-selected="<?= (int)$perfil['municipio_id'] ?>"><option value="">Seleccione…</option></select></div>
                  <div class="col-md-4"><label class="form-label" for="cuenta_distrito">Distrito</label><select class="form-select" id="cuenta_distrito" name="distrito_id" data-selected="<?= (int)$perfil['distrito_id'] ?>"><option value="">Seleccione…</option></select></div><?= $error('ubicacion') ?>
                  <div class="col-12"><label class="form-label" for="cuenta_direccion_texto">Dirección completa</label><textarea class="form-control" id="cuenta_direccion_texto" name="direccion" rows="3" maxlength="500"><?= atenea_e((string)$perfil['direccion']) ?></textarea><?= $error('direccion') ?></div>
                  <div class="col-12 d-flex justify-content-end"><button class="btn btn-primary" type="submit">Guardar dirección</button></div>
                </form></div>
                <div class="tab-pane fade" id="cuenta-seguridad">
                  <form method="post" action="<?= atenea_url('src/cuenta/solicitar-password.php') ?>" class="row g-3"><input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>"><input type="hidden" name="retorno" value="<?= atenea_e($retorno) ?>">
                    <?php if(!empty($perfil['password'])): ?><div class="col-12"><label class="form-label">Contraseña actual</label><input class="form-control" type="password" name="password_actual" autocomplete="current-password" required><?= $error('password_actual') ?></div><?php else: ?><div class="col-12"><div class="alert alert-info">Tu cuenta usa Google. Puedes crear una contraseña local verificando el código enviado a tu correo.</div></div><?php endif; ?>
                    <div class="col-md-6"><label class="form-label">Nueva contraseña</label><input class="form-control" type="password" name="password_nueva" minlength="8" autocomplete="new-password" required><?= $error('password_nueva') ?></div><div class="col-md-6"><label class="form-label">Confirmar contraseña</label><input class="form-control" type="password" name="password_confirmar" minlength="8" autocomplete="new-password" required><?= $error('password_confirmar') ?></div><div class="col-12"><button class="btn btn-primary">Enviar código de verificación</button></div>
                  </form><hr class="my-4"><form method="post" action="<?= atenea_url('src/cuenta/solicitar-correo.php') ?>" class="row g-3"><input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>"><input type="hidden" name="retorno" value="<?= atenea_e($retorno) ?>"><div class="col-md-8"><label class="form-label">Nuevo correo electrónico</label><input class="form-control" type="email" name="correo_nuevo" maxlength="190" required><?= $error('correo') ?></div><div class="col-md-4 d-flex align-items-end"><button class="btn btn-outline-primary w-100">Verificar correo nuevo</button></div></form>
                </div>
                <div class="tab-pane fade" id="cuenta-vinculada"><h3 class="h5">Cuenta de Google</h3><p><?= !empty($perfil['google_id'])?'Tu cuenta está vinculada con Google.':'Tu cuenta todavía no está vinculada con Google.' ?></p><?php if(empty($perfil['google_id'])): ?><a class="btn btn-outline-primary" href="<?= atenea_url('src/auth/google.php?accion=vincular&amp;retorno='.rawurlencode($retorno)) ?>">Vincular con Google</a><?php elseif(!empty($perfil['password'])): ?><form method="post" action="<?= atenea_url('src/cuenta/solicitar-desvincular-google.php') ?>"><input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>"><input type="hidden" name="retorno" value="<?= atenea_e($retorno) ?>"><button class="btn btn-outline-danger" type="submit">Desvincular Google</button></form><?php else: ?><div class="alert alert-info">Crea una contraseña local antes de desvincular Google.</div><?php endif; ?><hr><p class="mb-1"><strong>Proveedor:</strong> <?= atenea_e(ucfirst((string)$perfil['proveedor'])) ?></p><p><strong>Correo verificado:</strong> <?= $perfil['email_verificado']?'Sí':'No' ?></p></div>
              </div>
              <?php if($verificacion): ?><div class="cuenta-verificacion mt-4"><h3 class="h5">Confirmar cambio sensible</h3><p>Escribe el código enviado por correo. Vence en 15 minutos.</p><form method="post" action="<?= atenea_url('src/cuenta/confirmar-cambio.php') ?>" class="row g-2"><input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>"><input type="hidden" name="retorno" value="<?= atenea_e($retorno) ?>"><input type="hidden" name="verificacion_id" value="<?= (int)$verificacion['id'] ?>"><div class="col-md-8"><input class="form-control text-uppercase" name="codigo" minlength="8" maxlength="8" autocomplete="one-time-code" placeholder="Código de 8 caracteres" required><?= $error('codigo') ?></div><div class="col-md-4"><button class="btn btn-success w-100">Confirmar cambio</button></div></form></div><?php endif; ?>
            </div>
          </div>
        </div>
      </div></div>
    </div>
    <script>window.ATENEA_CUENTA={ubicaciones:<?= json_encode(atenea_url('src/api/ubicaciones.php'),JSON_UNESCAPED_SLASHES) ?>};</script>
    <?php
}
