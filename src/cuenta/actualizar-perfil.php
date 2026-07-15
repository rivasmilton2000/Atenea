<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/cuenta.php';
require_once dirname(__DIR__, 2) . '/includes/audit.php';
exigirAutenticacion();
$retorno = cuentaRetornoSeguro($_POST['retorno'] ?? null);
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !validarTokenCsrf(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
    cuentaFlash(['general' => 'La solicitud expiró. Intenta nuevamente.']); header('Location: ' . $retorno); exit;
}
$id = (int) $_SESSION['usuario_id'];
$perfil = obtenerPerfilUsuario($id);
if (!$perfil) { header('Location: ' . atenea_url('src/login/logout.php')); exit; }
$nombre = trim(strip_tags((string) ($_POST['nombre'] ?? $perfil['nombre'])));
$apellido = trim(strip_tags((string) ($_POST['apellido'] ?? $perfil['apellido'])));
$fecha = trim((string) ($_POST['fecha_nacimiento'] ?? $perfil['fecha_nacimiento'] ?? ''));
$duiEntrada = trim((string) ($_POST['dui'] ?? $perfil['dui'] ?? ''));
$dui = normalizarDui($duiEntrada);
$codigo = normalizarCodigoTelefono((string) ($_POST['codigo_telefono'] ?? $perfil['codigo_telefono'] ?? ''));
$telefono = normalizarTelefono((string) ($_POST['telefono'] ?? $perfil['telefono'] ?? ''));
$departamento = filter_var($_POST['departamento_id'] ?? $perfil['departamento_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$municipio = filter_var($_POST['municipio_id'] ?? $perfil['municipio_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$distrito = filter_var($_POST['distrito_id'] ?? $perfil['distrito_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$direccion = trim(strip_tags((string) ($_POST['direccion'] ?? $perfil['direccion'] ?? '')));
$errores = [];
if ($nombre === '' || mb_strlen($nombre) > 100) $errores['nombre'] = 'Ingresa un nombre válido.';
if (mb_strlen($apellido) > 100) $errores['apellido'] = 'El apellido no puede superar 100 caracteres.';
$requiereDatos = $perfil['rol'] === 'usuario';
if (($fecha !== '' && !fechaNacimientoValida($fecha)) || ($requiereDatos && $fecha === '')) $errores['fecha_nacimiento'] = 'Ingresa una fecha válida.';
if (($duiEntrada !== '' && $dui === '') || ($requiereDatos && !$dui)) $errores['dui'] = 'Usa el formato 00000000-0.';
if ((($telefono !== '' || $codigo !== '') && !telefonoValido($codigo, $telefono)) || ($requiereDatos && $telefono === '')) $errores['telefono'] = 'Ingresa un teléfono válido.';
if (mb_strlen($direccion) > 500) $errores['direccion'] = 'La dirección no puede superar 500 caracteres.';
try {
    $pdo = obtenerConexion();
    if (($departamento || $municipio || $distrito) && !ubicacionValida($pdo, $departamento, $municipio, $distrito)) $errores['ubicacion'] = 'Selecciona una ubicación válida.';
    if ($requiereDatos && (!$departamento || !$municipio || !$distrito)) $errores['ubicacion'] = 'La ubicación es obligatoria para estudiantes.';
    if ($dui) { $q=$pdo->prepare('SELECT id FROM usuarios WHERE dui=:dui AND id<>:id LIMIT 1');$q->execute(['dui'=>$dui,'id'=>$id]);if($q->fetch())$errores['dui']='Ese DUI ya está registrado.'; }
    if ($errores) { cuentaFlash($errores); header('Location: ' . $retorno); exit; }
    try {
        $foto = guardarFotoPerfil($_FILES['foto'] ?? [], $perfil['foto'] ?? null);
    } catch (RuntimeException $e) {
        cuentaFlash(['foto' => $e->getMessage()]);
        header('Location: ' . $retorno);
        exit;
    }
    $nuevos = ['nombre'=>$nombre,'apellido'=>$apellido,'fecha_nacimiento'=>$fecha?:null,'dui'=>$dui?:null,'codigo_telefono'=>$codigo?:null,'telefono'=>$telefono?:null,'departamento_id'=>$departamento?:null,'municipio_id'=>$municipio?:null,'distrito_id'=>$distrito?:null,'direccion'=>$direccion?:null,'foto'=>$foto?:null];
    $campos=[];foreach($nuevos as $campo=>$valor){if((string)($perfil[$campo]??'')!==(string)($valor??''))$campos[]=$campo;}
    if (!$campos) { cuentaFlash([], 'No había cambios por guardar.'); header('Location: ' . $retorno); exit; }
    $pdo->beginTransaction();
    $q=$pdo->prepare('UPDATE usuarios SET nombre=:nombre,apellido=:apellido,fecha_nacimiento=:fecha_nacimiento,dui=:dui,codigo_telefono=:codigo_telefono,telefono=:telefono,departamento_id=:departamento_id,municipio_id=:municipio_id,distrito_id=:distrito_id,direccion=:direccion,foto=:foto,last_activity_at=NOW() WHERE id=:id');
    $q->execute($nuevos+['id'=>$id]);
    registrarCambioCuenta($pdo,$id,'actualizacion_perfil',$campos);
    registrarAuditoria(['actor_user_id'=>$id,'target_user_id'=>$id,'event_type'=>'user.profile_updated','module'=>'account','entity_type'=>'user','entity_id'=>$id,'action'=>'update','result'=>'success','description'=>'El usuario actualizo su perfil.','metadata'=>['changed_fields'=>$campos]],$pdo);
    $pdo->commit();
    if ($foto !== (string)($perfil['foto'] ?? '')) eliminarFotoPerfilLocal($perfil['foto'] ?? null);
    $actualizado=obtenerPerfilUsuario($id);if(!$actualizado)throw new RuntimeException('Perfil no disponible.');
    iniciarSesionUsuario($actualizado);
    notificarCambioCuenta($actualizado,$campos);
    cuentaFlash([], 'Tu perfil se actualizó correctamente.');
} catch(Throwable $e) {
    if(isset($pdo)&&$pdo instanceof PDO&&$pdo->inTransaction())$pdo->rollBack();
    if(isset($foto) && $foto !== (string)($perfil['foto'] ?? '')) eliminarFotoPerfilLocal($foto);
    error_log('Actualizar cuenta Atenea: '.$e->getMessage());cuentaFlash(['general'=>'No fue posible actualizar el perfil.']);
}
header('Location: ' . $retorno);exit;
