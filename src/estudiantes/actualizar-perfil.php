<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
exigirRol(['usuario']);

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . atenea_url('src/estudiantes/perfil.php'));
    exit;
}

$fecha = trim((string) ($_POST['fecha_nacimiento'] ?? ''));
$dui = normalizarDui(isset($_POST['dui']) ? (string) $_POST['dui'] : null);
$codigo = normalizarCodigoTelefono(isset($_POST['codigo_telefono']) ? (string) $_POST['codigo_telefono'] : null);
$telefono = normalizarTelefono(isset($_POST['telefono']) ? (string) $_POST['telefono'] : null);
$departamento = filter_var($_POST['departamento_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$municipio = filter_var($_POST['municipio_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$distrito = filter_var($_POST['distrito_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$direccion = trim(strip_tags((string) ($_POST['direccion'] ?? '')));
$errores = [];

if (!validarTokenCsrf(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) $errores[] = 'La solicitud expiró. Intenta nuevamente.';
if (!fechaNacimientoValida($fecha)) $errores[] = 'La fecha de nacimiento no es válida ni puede estar en el futuro.';
if ($dui === null || $dui === '') $errores[] = 'El DUI debe usar el formato 00000000-0.';
if (!telefonoValido($codigo, $telefono)) $errores[] = $codigo === '+503' ? 'El teléfono salvadoreño debe contener 8 dígitos y comenzar con 2, 6 o 7.' : 'Ingresa un teléfono válido.';
if (mb_strlen($direccion) > 500) $errores[] = 'La dirección no puede superar 500 caracteres.';

try {
    $pdo = obtenerConexion();
    if (!$errores && !ubicacionValida($pdo, $departamento, $municipio, $distrito)) $errores[] = 'Selecciona una ubicación válida.';
    if (!$errores) {
        $consulta = $pdo->prepare('SELECT id FROM usuarios WHERE dui=:dui AND id<>:id LIMIT 1');
        $consulta->execute(['dui' => $dui, 'id' => (int) $_SESSION['usuario_id']]);
        if ($consulta->fetch()) $errores[] = 'El DUI ya está registrado.';
    }
    if (!$errores) {
        $consulta = $pdo->prepare('UPDATE usuarios SET fecha_nacimiento=:fecha,dui=:dui,codigo_telefono=:codigo,telefono=:telefono,departamento_id=:departamento,municipio_id=:municipio,distrito_id=:distrito,direccion=:direccion WHERE id=:id AND rol=\'usuario\'');
        $consulta->execute(['fecha'=>$fecha,'dui'=>$dui,'codigo'=>$codigo,'telefono'=>$telefono,'departamento'=>$departamento,'municipio'=>$municipio,'distrito'=>$distrito,'direccion'=>$direccion===''?null:$direccion,'id'=>(int)$_SESSION['usuario_id']]);
        $perfil = obtenerPerfilUsuario((int) $_SESSION['usuario_id']);
        if (!$perfil) throw new RuntimeException('Perfil no disponible.');
        iniciarSesionUsuario($perfil);
        $_SESSION['perfil_mensaje'] = 'Datos personales actualizados correctamente.';
        header('Location: ' . atenea_url('src/estudiantes/perfil.php'));
        exit;
    }
} catch (PDOException $e) {
    error_log('Actualizar perfil Atenea: ' . $e->getMessage());
    $errores[] = (string) $e->getCode() === '23000' ? 'El DUI ya está registrado.' : 'No fue posible actualizar el perfil.';
} catch (Throwable $e) {
    error_log('Actualizar perfil Atenea: ' . $e->getMessage());
    $errores[] = 'No fue posible actualizar el perfil.';
}

$_SESSION['perfil_errores'] = array_values(array_unique($errores));
$_SESSION['perfil_datos'] = $_POST;
header('Location: ' . atenea_url('src/estudiantes/perfil.php?completar=1'));
exit;
