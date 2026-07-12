<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/conexion.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . atenea_url('src/login/login.php'));
    exit;
}

$ahora = time();
$intentos = $_SESSION['login_intentos'] ?? [];
$intentos = is_array($intentos) ? array_values(array_filter($intentos, static fn ($t): bool => is_int($t) && $t > $ahora - 300)) : [];

if (count($intentos) >= 5) {
    $_SESSION['mensaje_auth'] = 'Demasiados intentos. Espera unos minutos antes de volver a intentarlo.';
    header('Location: ' . atenea_url('src/login/login.php'));
    exit;
}

$correo = strtolower(trim((string) ($_POST['correo'] ?? '')));
$password = (string) ($_POST['password'] ?? '');
$token = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null;
$_SESSION['login_correo'] = substr($correo, 0, 190);

if (!validarTokenCsrf($token)) {
    $_SESSION['mensaje_auth'] = 'La solicitud expiró. Intenta iniciar sesión nuevamente.';
    header('Location: ' . atenea_url('src/login/login.php'));
    exit;
}

if (strlen($correo) > 190 || strlen($password) > 255 || !filter_var($correo, FILTER_VALIDATE_EMAIL) || $password === '') {
    $_SESSION['mensaje_auth'] = 'Ingresa un correo electrónico y una contraseña válidos.';
    header('Location: ' . atenea_url('src/login/login.php'));
    exit;
}

try {
    $pdo = obtenerConexion();
    $consulta = $pdo->prepare('SELECT id, nombre, apellido, correo, password, rol, foto, estado FROM usuarios WHERE correo = :correo LIMIT 1');
    $consulta->execute(['correo' => $correo]);
    $usuario = $consulta->fetch();

    $credencialesValidas = is_array($usuario)
        && ($usuario['estado'] ?? '') === 'activo'
        && password_verify($password, (string) ($usuario['password'] ?? ''));

    if (!$credencialesValidas) {
        $intentos[] = $ahora;
        $_SESSION['login_intentos'] = $intentos;
        $_SESSION['mensaje_auth'] = 'El correo o la contraseña no son válidos, o la cuenta no está disponible.';
        header('Location: ' . atenea_url('src/login/login.php'));
        exit;
    }

    session_regenerate_id(true);
    unset($_SESSION['login_intentos'], $_SESSION['login_correo'], $_SESSION['csrf_token'], $_SESSION['url_retorno']);
    $_SESSION['usuario_id'] = (int) $usuario['id'];
    $_SESSION['usuario_nombre'] = trim((string) $usuario['nombre'] . ' ' . (string) $usuario['apellido']);
    $_SESSION['usuario_correo'] = (string) $usuario['correo'];
    $_SESSION['usuario_rol'] = (string) $usuario['rol'];
    $_SESSION['usuario_foto'] = $usuario['foto'] !== null ? (string) $usuario['foto'] : null;

    $actualizar = $pdo->prepare('UPDATE usuarios SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = :id');
    $actualizar->execute(['id' => (int) $usuario['id']]);
    redirigirPorRol((string) $usuario['rol']);
} catch (Throwable $error) {
    error_log('Error de autenticación en Atenea: ' . $error->getMessage());
    $_SESSION['mensaje_auth'] = 'No fue posible iniciar sesión en este momento. Intenta nuevamente más tarde.';
    header('Location: ' . atenea_url('src/login/login.php'));
    exit;
}
