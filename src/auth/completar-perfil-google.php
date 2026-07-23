<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/audit.php';

exigirRol(['usuario']);
$retorno = atenea_url('src/estudiantes/perfil.php?completar=1');

function devolverPerfilGoogle(array $errores, array $datos, string $retorno): never
{
    $_SESSION['google_perfil_errores'] = $errores;
    $_SESSION['google_perfil_datos'] = $datos;
    header('Location: ' . $retorno);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . $retorno);
    exit;
}
if (!validarTokenCsrf((string) ($_POST['csrf_token'] ?? ''))) {
    $perfilActual = obtenerPerfilUsuario((int) ($_SESSION['usuario_id'] ?? 0));
    if ($perfilActual && ($perfilActual['perfil_estado'] ?? 'pendiente') === 'completo') {
        header('Location: ' . rutaPanelPorRol('usuario'));
        exit;
    }
    devolverPerfilGoogle(['general' => 'La solicitud expiró. Actualiza la página e intenta nuevamente.'], [], $retorno);
}

$id = (int) $_SESSION['usuario_id'];
$perfil = obtenerPerfilUsuario($id);
if (!$perfil || empty($perfil['google_id']) || ($perfil['perfil_estado'] ?? 'completo') !== 'pendiente') {
    header('Location: ' . rutaPanelPorRol('usuario'));
    exit;
}

$nombreOriginal = (string) ($_POST['nombre'] ?? '');
$apellidoOriginal = (string) ($_POST['apellido'] ?? '');
$direccionOriginal = (string) ($_POST['direccion'] ?? '');
$duiOriginal = trim((string) ($_POST['dui'] ?? ''));
$nombre = normalizarNombrePersona($nombreOriginal);
$apellido = normalizarNombrePersona($apellidoOriginal);
$fechaOriginal = trim((string) ($_POST['fecha_nacimiento'] ?? ''));
$fecha = normalizarFechaNacimiento($fechaOriginal) ?? '';
$dui = duiValidoExacto($duiOriginal) ? $duiOriginal : '';
$codigo = normalizarCodigoTelefono((string) ($_POST['codigo_telefono'] ?? ''));
$telefono = normalizarTelefonoParaCodigo($codigo, (string) ($_POST['telefono'] ?? ''));
$departamento = filter_var($_POST['departamento_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$municipio = filter_var($_POST['municipio_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$distrito = filter_var($_POST['distrito_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$direccion = normalizarDireccionPerfil($direccionOriginal);
$terminos = ($_POST['terminos'] ?? '') === '1';

$datos = [
    'nombre' => $nombre,
    'apellido' => $apellido,
    'fecha_nacimiento' => $fecha,
    'dui' => $duiOriginal,
    'codigo_telefono' => $codigo,
    'telefono' => $telefono,
    'departamento_id' => $departamento,
    'municipio_id' => $municipio,
    'distrito_id' => $distrito,
    'direccion' => $direccion,
];
$errores = [];

if (!nombrePersonaValido($nombreOriginal)) {
    $errores['nombre'] = 'Los nombres deben tener entre 2 y 60 caracteres y contener únicamente letras, espacios, apóstrofes o guiones.';
}
if (!nombrePersonaValido($apellidoOriginal)) {
    $errores['apellido'] = 'Los apellidos deben tener entre 2 y 60 caracteres y contener únicamente letras, espacios, apóstrofes o guiones.';
}
if (!fechaNacimientoValida($fecha)) {
    $errores['fecha_nacimiento'] = 'Debes tener al menos 18 años y proporcionar una fecha válida (máximo 120 años de antigüedad).';
}
if (!$dui) {
    $errores['dui'] = 'El DUI debe usar exactamente el formato 00000000-0.';
}
if (!telefonoValido($codigo, $telefono)) {
    $errores['telefono'] = $codigo === '+503'
        ? 'Para El Salvador ingresa ocho dígitos; el número debe iniciar con 2, 6 o 7.'
        : 'Ingresa la cantidad de dígitos correspondiente al prefijo seleccionado.';
}
if (!direccionPerfilValida($direccionOriginal)) {
    $errores['direccion'] = 'La dirección debe tener entre 8 y 250 caracteres, sin etiquetas HTML ni caracteres de control.';
}
if (!$terminos) {
    $errores['terminos'] = 'Debes aceptar los términos y confirmar que los datos son correctos.';
}

try {
    $pdo = obtenerConexion();
    if (!$departamento || !$municipio || !$distrito || !ubicacionValida($pdo, $departamento, $municipio, $distrito)) {
        $errores['ubicacion'] = 'El municipio y el distrito deben pertenecer al departamento seleccionado.';
    }
    if ($dui) {
        $consultaDui = $pdo->prepare('SELECT id FROM usuarios WHERE dui = :dui AND id <> :id AND deleted_at IS NULL LIMIT 1');
        $consultaDui->execute(['dui' => $dui, 'id' => $id]);
        if ($consultaDui->fetch()) {
            $errores['dui'] = 'Ese DUI ya está registrado por otra cuenta.';
        }
    }
    if ($errores) {
        devolverPerfilGoogle($errores, $datos, $retorno);
    }

    $pdo->beginTransaction();
    $bloqueo = $pdo->prepare("SELECT perfil_estado FROM usuarios WHERE id = :id AND estado = 'activo' AND deleted_at IS NULL FOR UPDATE");
    $bloqueo->execute(['id' => $id]);
    if ($bloqueo->fetchColumn() !== 'pendiente') {
        throw new RuntimeException('El perfil ya fue procesado.');
    }

    $actualizar = $pdo->prepare(
        "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, fecha_nacimiento = :fecha,
         dui = :dui, codigo_telefono = :codigo, telefono = :telefono,
         departamento_id = :departamento, municipio_id = :municipio, distrito_id = :distrito,
         direccion = :direccion, terminos_aceptados_at = NOW(), perfil_estado = 'completo',
         last_activity_at = NOW()
         WHERE id = :id AND perfil_estado = 'pendiente'"
    );
    $actualizar->execute([
        'nombre' => $nombre, 'apellido' => $apellido, 'fecha' => $fecha, 'dui' => $dui,
        'codigo' => $codigo, 'telefono' => $telefono, 'departamento' => $departamento,
        'municipio' => $municipio, 'distrito' => $distrito, 'direccion' => $direccion, 'id' => $id,
    ]);
    if ($actualizar->rowCount() !== 1) {
        throw new RuntimeException('No se actualizó el perfil pendiente.');
    }

    registrarAuditoria([
        'actor_user_id' => $id, 'target_user_id' => $id,
        'event_type' => 'auth.google_profile_completed', 'module' => 'auth',
        'entity_type' => 'user', 'entity_id' => $id, 'action' => 'complete_profile',
        'result' => 'success',
        'description' => 'Se completó el perfil obligatorio de una cuenta registrada con Google.',
    ], $pdo);
    $pdo->commit();

    $actualizado = obtenerPerfilUsuario($id);
    if (!$actualizado || !datosPerfilCompletos($actualizado)) {
        throw new RuntimeException('La verificación final del perfil no fue satisfactoria.');
    }
    iniciarSesionUsuario($actualizado);
    unset($_SESSION['google_perfil_errores'], $_SESSION['google_perfil_datos']);
    $_SESSION['portal_flash'] = [
        'tipo' => 'success', 'titulo' => 'Perfil completado', 'mensaje' => 'Tu cuenta ya está habilitada.',
    ];
    header('Location: ' . atenea_url('src/estudiantes/index.php'));
    exit;
} catch (Throwable $error) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Completar perfil Google Atenea: ' . $error->getMessage());
    devolverPerfilGoogle(['general' => 'No fue posible completar el perfil. Tus datos se conservaron; intenta nuevamente.'], $datos, $retorno);
}
