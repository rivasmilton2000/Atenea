<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/google_oauth.php';
exigirRol(['usuario']);
$perfil = obtenerPerfilUsuario((int) $_SESSION['usuario_id']);
if (!$perfil) { header('Location: ' . atenea_url('src/login/logout.php')); exit; }
$datosSesion = is_array($_SESSION['perfil_datos'] ?? null) ? $_SESSION['perfil_datos'] : [];
$datos = array_merge($perfil, $datosSesion);
$errores = is_array($_SESSION['perfil_errores'] ?? null) ? $_SESSION['perfil_errores'] : [];
$mensaje = (string) ($_SESSION['perfil_mensaje'] ?? '');
$tipoMensaje = ($_SESSION['perfil_mensaje_tipo'] ?? 'success') === 'danger' ? 'danger' : 'success';
unset($_SESSION['perfil_datos'], $_SESSION['perfil_errores'], $_SESSION['perfil_mensaje'], $_SESSION['perfil_mensaje_tipo']);
$departamentos = obtenerDepartamentos();
$googleDisponible = googleDisponible();
$incompleto = !datosPerfilCompletos($perfil);
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mi perfil | Atenea</title><link rel="icon" href="<?= atenea_url('img/atenea-logo.png') ?>"><link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/core/libs.min.css') ?>"><link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/hope-ui.min.css') ?>"></head><body>
<main class="main-content ms-0"><div class="container-fluid content-inner py-5"><div class="row justify-content-center"><div class="col-xl-9">
<div class="d-flex justify-content-between align-items-center mb-3"><a class="btn btn-outline-primary" href="<?= atenea_url('src/estudiantes/index.php') ?>">← Volver al portal</a><a href="<?= atenea_url('src/login/logout.php') ?>">Cerrar sesión</a></div>
<div class="card"><div class="card-header"><h1 class="h3 mb-1">Mi perfil</h1><p class="mb-0 text-muted"><?= atenea_e(trim($perfil['nombre'].' '.$perfil['apellido'])) ?> · <?= atenea_e((string)$perfil['correo']) ?></p></div><div class="card-body">
<?php if ($incompleto): ?><div class="alert alert-warning">Completa los datos obligatorios para acceder al portal estudiantil.</div><?php endif; ?>
<?php if ($mensaje !== ''): ?><div class="alert alert-<?= $tipoMensaje ?>"><?= atenea_e($mensaje) ?></div><?php endif; ?>
<?php if ($errores): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errores as $error): ?><li><?= atenea_e((string)$error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" action="<?= atenea_url('src/estudiantes/actualizar-perfil.php') ?>"><input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>"><div class="row">
<div class="col-md-6 form-group"><label class="form-label">Fecha de nacimiento</label><input class="form-control" type="date" name="fecha_nacimiento" max="<?= date('Y-m-d') ?>" value="<?= atenea_e((string)($datos['fecha_nacimiento']??'')) ?>" required></div>
<div class="col-md-6 form-group"><label class="form-label">DUI</label><input class="form-control" id="dui" name="dui" inputmode="numeric" maxlength="10" pattern="\d{8}-\d" placeholder="00000000-0" value="<?= atenea_e((string)($datos['dui']??'')) ?>" required></div>
<div class="col-md-4 form-group"><label class="form-label">Código internacional</label><select class="form-select" name="codigo_telefono" id="codigo_telefono" required><?php foreach(['+503'=>'🇸🇻 El Salvador +503','+502'=>'🇬🇹 Guatemala +502','+504'=>'🇭🇳 Honduras +504','+505'=>'🇳🇮 Nicaragua +505','+506'=>'🇨🇷 Costa Rica +506','+507'=>'🇵🇦 Panamá +507','+52'=>'🇲🇽 México +52','+1'=>'🇺🇸/🇨🇦 +1'] as $codigo=>$etiqueta): ?><option value="<?= $codigo ?>" <?= ($datos['codigo_telefono']??'+503')===$codigo?'selected':'' ?>><?= $etiqueta ?></option><?php endforeach; ?></select></div>
<div class="col-md-8 form-group"><label class="form-label">Número de teléfono</label><input class="form-control" id="telefono" name="telefono" inputmode="numeric" maxlength="15" pattern="\d{7,15}" value="<?= atenea_e((string)($datos['telefono']??'')) ?>" required></div>
<div class="col-md-4 form-group"><label class="form-label">Departamento</label><select class="form-select" id="departamento_id" name="departamento_id" required><option value="">Seleccione…</option><?php foreach($departamentos as $departamento): ?><option value="<?= (int)$departamento['id'] ?>" <?= (int)($datos['departamento_id']??0)===(int)$departamento['id']?'selected':'' ?>><?= atenea_e((string)$departamento['nombre']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-4 form-group"><label class="form-label">Municipio</label><select class="form-select" id="municipio_id" name="municipio_id" data-selected="<?= (int)($datos['municipio_id']??0) ?>" required disabled><option value="">Seleccione…</option></select></div>
<div class="col-md-4 form-group"><label class="form-label">Distrito</label><select class="form-select" id="distrito_id" name="distrito_id" data-selected="<?= (int)($datos['distrito_id']??0) ?>" required disabled><option value="">Seleccione…</option></select></div>
<div class="col-12 form-group"><label class="form-label">Dirección completa <span class="text-muted">(opcional)</span></label><textarea class="form-control" name="direccion" rows="3" maxlength="500"><?= atenea_e((string)($datos['direccion']??'')) ?></textarea></div>
</div><button class="btn btn-primary" type="submit">Guardar datos personales</button></form>
<hr><h2 class="h5">Cuenta de Google</h2><p>Proveedor actual: <?= atenea_e((string)$perfil['proveedor']) ?>.</p><?php if(!$googleDisponible): ?><div class="alert alert-warning">Google no está disponible porque falta completar su configuración.</div><?php else: ?><a class="btn btn-outline-primary" href="<?= atenea_url('src/auth/google.php?accion=vincular') ?>">Vincular cuenta de Google</a><?php endif; ?>
</div></div></div></div></div></main>
<script src="<?= atenea_url('src/estudiantes/assets/js/core/libs.min.js') ?>"></script><script>(()=>{const endpoint=<?= json_encode(atenea_url('src/api/ubicaciones.php'),JSON_UNESCAPED_SLASHES) ?>,dep=document.getElementById('departamento_id'),mun=document.getElementById('municipio_id'),dis=document.getElementById('distrito_id');const fill=async(s,t,p,v=0)=>{s.innerHTML='<option value="">Seleccione…</option>';s.disabled=!p;if(!p)return;const r=await fetch(`${endpoint}?tipo=${t}&padre=${encodeURIComponent(p)}`);if(!r.ok)return;for(const x of await r.json())s.add(new Option(x.nombre,x.id,false,+x.id===+v))};dep.onchange=async()=>{await fill(mun,'municipios',dep.value);await fill(dis,'distritos','')};mun.onchange=()=>fill(dis,'distritos',mun.value);if(dep.value)fill(mun,'municipios',dep.value,mun.dataset.selected).then(()=>mun.value&&fill(dis,'distritos',mun.value,dis.dataset.selected));document.getElementById('dui').oninput=e=>{const d=e.target.value.replace(/\D/g,'').slice(0,9);e.target.value=d.length>8?`${d.slice(0,8)}-${d[8]}`:d};document.getElementById('telefono').oninput=e=>e.target.value=e.target.value.replace(/\D/g,'').slice(0,15)})();</script>
<script src="<?= atenea_url('src/estudiantes/assets/js/hope-ui.js') ?>" defer></script></body></html>
