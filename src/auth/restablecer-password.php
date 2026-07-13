<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/mailer.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . atenea_url('src/login/forgot-password.php'));
    exit;
}

$token = (string) ($_POST['token'] ?? '');
$password = (string) ($_POST['password'] ?? '');
$confirmacion = (string) ($_POST['confirmar_password'] ?? '');
$errores = [];
if (!validarTokenCsrf(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) $errores[] = 'La solicitud expiró. Intenta nuevamente.';
if (!preg_match('/^[a-f0-9]{64}$/', $token)) $errores[] = 'El enlace de recuperación no es válido.';
if (strlen($password) < 8 || strlen($password) > 255 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) $errores[] = 'La contraseña debe tener al menos 8 caracteres, una letra y un número.';
if ($password !== $confirmacion) $errores[] = 'Las contraseñas no coinciden.';

if ($errores) {
    $_SESSION['reset_errores'] = $errores;
    $_SESSION['reset_token_retorno'] = $token;
    header('Location: ' . atenea_url('src/login/reset-password.php'));
    exit;
}

$usuario = null;
try {
    $pdo = obtenerConexion();
    $pdo->beginTransaction();
    $consulta = $pdo->prepare('SELECT t.id token_id,t.user_id,u.nombre,u.apellido,u.correo FROM password_reset_tokens t INNER JOIN usuarios u ON u.id=t.user_id WHERE t.token_hash=:hash AND t.used_at IS NULL AND t.expires_at>NOW() AND u.estado=\'activo\' LIMIT 1 FOR UPDATE');
    $consulta->execute(['hash' => hash('sha256', $token)]);
    $usuario = $consulta->fetch();
    if (!is_array($usuario)) throw new RuntimeException('Token vencido, modificado o utilizado.');

    $actualizar = $pdo->prepare("UPDATE usuarios SET password=:password,proveedor=IF(google_id IS NULL,'local','mixto'),session_version=session_version+1 WHERE id=:id");
    $actualizar->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => (int) $usuario['user_id']]);
    $pdo->prepare('UPDATE password_reset_tokens SET used_at=NOW() WHERE user_id=:usuario AND used_at IS NULL')->execute(['usuario' => (int) $usuario['user_id']]);
    $pdo->commit();
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Restablecer contraseña Atenea: ' . $e->getMessage());
    $_SESSION['reset_errores'] = ['Este enlace no es válido, ya fue utilizado o expiró. Solicita uno nuevo.'];
    $_SESSION['reset_token_retorno'] = $token;
    header('Location: ' . atenea_url('src/login/reset-password.php'));
    exit;
}

try {
    $nombre = trim((string) ($usuario['nombre'] . ' ' . $usuario['apellido']));
    enviarCorreoAtenea(
        (string) $usuario['correo'],
        $nombre,
        'Tu contraseña de Atenea fue actualizada',
        '<h2>Atenea</h2><p>Tu contraseña se actualizó correctamente.</p><p>Si no realizaste este cambio, contacta al equipo de Atenea de inmediato.</p>',
        "Atenea\n\nTu contraseña se actualizó correctamente. Si no realizaste este cambio, contacta al equipo de Atenea de inmediato."
    );
} catch (Throwable $e) {
    error_log('Confirmación de contraseña Atenea: ' . $e->getMessage());
}

$_SESSION['mensaje_auth'] = 'Tu contraseña se actualizó correctamente. Ya puedes iniciar sesión.';
$_SESSION['mensaje_auth_tipo'] = 'success';
header('Location: ' . atenea_url('src/login/sign-in.php'));
exit;
