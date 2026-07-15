<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/audit.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . atenea_url('src/login/sign-up.php'));
    exit;
}

$nombre = trim((string) ($_POST['nombre'] ?? ''));
$apellido = trim((string) ($_POST['apellido'] ?? ''));
$correo = strtolower(trim((string) ($_POST['correo'] ?? '')));
$fechaNacimiento = trim((string) ($_POST['fecha_nacimiento'] ?? ''));
$dui = normalizarDui(isset($_POST['dui']) ? (string) $_POST['dui'] : null);
$codigoTelefono = normalizarCodigoTelefono(isset($_POST['codigo_telefono']) ? (string) $_POST['codigo_telefono'] : null);
$telefono = normalizarTelefono(isset($_POST['telefono']) ? (string) $_POST['telefono'] : null);
$departamentoId = filter_var($_POST['departamento_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$municipioId = filter_var($_POST['municipio_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$distritoId = filter_var($_POST['distrito_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$direccion = trim(strip_tags((string) ($_POST['direccion'] ?? '')));
$password = (string) ($_POST['password'] ?? '');
$confirmarPassword = (string) ($_POST['confirmar_password'] ?? '');
$errores = [];

if (!validarTokenCsrf(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) $errores[] = 'La solicitud expiró. Intenta nuevamente.';
if ($nombre === '' || mb_strlen($nombre) > 100) $errores[] = 'Ingresa un nombre válido.';
if ($apellido === '' || mb_strlen($apellido) > 100) $errores[] = 'Ingresa un apellido válido.';
if (strlen($correo) > 190 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores[] = 'Ingresa un correo electrónico válido.';
if (!fechaNacimientoValida($fechaNacimiento)) $errores[] = 'La fecha de nacimiento no es válida ni puede estar en el futuro.';
if ($dui === null || $dui === '') $errores[] = 'El DUI debe contener 9 dígitos y usar el formato 00000000-0.';
if (!telefonoValido($codigoTelefono, $telefono)) $errores[] = $codigoTelefono === '+503' ? 'El teléfono salvadoreño debe contener 8 dígitos y comenzar con 2, 6 o 7.' : 'Ingresa un teléfono internacional válido.';
if (mb_strlen($direccion) > 500) $errores[] = 'La dirección no puede superar 500 caracteres.';
if (strlen($password) < 8 || strlen($password) > 255 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) $errores[] = 'La contraseña debe tener al menos 8 caracteres, una letra y un número.';
if ($password !== $confirmarPassword) $errores[] = 'Las contraseñas no coinciden.';
if (($_POST['terminos'] ?? '') !== '1') $errores[] = 'Debes aceptar los términos y condiciones.';

$_SESSION['registro_datos'] = [
    'nombre' => $nombre, 'apellido' => $apellido, 'correo' => $correo,
    'fecha_nacimiento' => $fechaNacimiento, 'dui' => $dui ?? '',
    'codigo_telefono' => $codigoTelefono ?: '+503', 'telefono' => $telefono,
    'departamento_id' => $departamentoId, 'municipio_id' => $municipioId,
    'distrito_id' => $distritoId, 'direccion' => $direccion,
];

try {
    $pdo = obtenerConexion();
    if (!$errores && !ubicacionValida($pdo, $departamentoId, $municipioId, $distritoId)) {
        $errores[] = 'Selecciona un departamento, municipio y distrito válidos.';
    }

    if (!$errores) {
        $consulta = $pdo->prepare('SELECT correo,dui FROM usuarios WHERE correo=:correo OR dui=:dui LIMIT 1');
        $consulta->execute(['correo' => $correo, 'dui' => $dui]);
        $existente = $consulta->fetch();
        if ($existente) {
            $errores[] = hash_equals((string) ($existente['dui'] ?? ''), (string) $dui)
                ? 'El DUI ya está registrado.'
                : 'No fue posible crear la cuenta con los datos proporcionados.';
        }
    }

    if (!$errores) {
        $nombreUsuario = generarNombreUsuarioDisponible($pdo, $correo, $nombre . '.' . $apellido);
        $consulta = $pdo->prepare(
            "INSERT INTO usuarios
             (nombre,apellido,nombre_usuario,correo,password,rol,estado,proveedor,email_verificado,fecha_nacimiento,dui,codigo_telefono,telefono,departamento_id,municipio_id,distrito_id,direccion,created_at,updated_at)
             VALUES(:nombre,:apellido,:nombre_usuario,:correo,:password,'usuario','activo','local',0,:fecha,:dui,:codigo,:telefono,:departamento,:municipio,:distrito,:direccion,NOW(),NOW())"
        );
        $consulta->execute([
            'nombre' => $nombre, 'apellido' => $apellido, 'nombre_usuario' => $nombreUsuario, 'correo' => $correo,
            'password' => password_hash($password, PASSWORD_DEFAULT), 'fecha' => $fechaNacimiento,
            'dui' => $dui, 'codigo' => $codigoTelefono, 'telefono' => $telefono,
            'departamento' => $departamentoId, 'municipio' => $municipioId, 'distrito' => $distritoId,
            'direccion' => $direccion === '' ? null : $direccion,
        ]);
        $usuarioId = (int) $pdo->lastInsertId();
        registrarAuditoria(['actor_user_id'=>$usuarioId,'target_user_id'=>$usuarioId,'event_type'=>'user.created','module'=>'users','entity_type'=>'user','entity_id'=>$usuarioId,'action'=>'create','result'=>'success','description'=>'Se creo una cuenta mediante registro tradicional.','metadata'=>['provider'=>'local','role'=>'usuario']],$pdo);
        iniciarSesionUsuario([
            'id' => $usuarioId, 'nombre' => $nombre, 'apellido' => $apellido,
            'correo' => $correo, 'rol' => 'usuario', 'foto' => null,
            'session_version' => 1,
            'fecha_nacimiento' => $fechaNacimiento, 'dui' => $dui,
            'codigo_telefono' => $codigoTelefono, 'telefono' => $telefono,
            'departamento_id' => $departamentoId, 'municipio_id' => $municipioId, 'distrito_id' => $distritoId,
        ]);
        unset($_SESSION['registro_datos']);
        redirigirPorRol('usuario');
    }
} catch (PDOException $e) {
    error_log('Registro Atenea: ' . $e->getMessage());
    $errores[] = (string) $e->getCode() === '23000' && str_contains($e->getMessage(), 'uq_usuarios_dui')
        ? 'El DUI ya está registrado.'
        : 'No fue posible crear la cuenta en este momento.';
} catch (Throwable $e) {
    error_log('Registro Atenea: ' . $e->getMessage());
    $errores[] = 'No fue posible crear la cuenta en este momento.';
}

$_SESSION['registro_errores'] = array_values(array_unique($errores));
header('Location: ' . atenea_url('src/login/sign-up.php'));
exit;
