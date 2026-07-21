<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/mailer.php';
require_once dirname(__DIR__, 2) . '/includes/audit.php';

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
    $consulta = $pdo->prepare('SELECT t.id token_id,t.user_id,u.nombre,u.apellido,u.correo FROM password_reset_tokens t INNER JOIN usuarios u ON u.id=t.user_id WHERE t.token_hash=:hash AND t.used_at IS NULL AND t.expires_at>NOW() AND u.estado=\'activo\' AND u.deleted_at IS NULL LIMIT 1 FOR UPDATE');
    $consulta->execute(['hash' => hash('sha256', $token)]);
    $usuario = $consulta->fetch();
    if (!is_array($usuario)) throw new RuntimeException('Token vencido, modificado o utilizado.');

    $actualizar = $pdo->prepare("UPDATE usuarios SET password=:password,proveedor=IF(google_id IS NULL,'local','mixto'),session_version=session_version+1,last_activity_at=NOW() WHERE id=:id");
    $actualizar->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => (int) $usuario['user_id']]);
    revocarTokensRecuerdoUsuarioAtenea($pdo,(int)$usuario['user_id']);
    $pdo->prepare('UPDATE password_reset_tokens SET used_at=NOW() WHERE user_id=:usuario AND used_at IS NULL')->execute(['usuario' => (int) $usuario['user_id']]);
    if(!registrarAuditoria(['target_user_id'=>(int)$usuario['user_id'],'event_type'=>'password.reset_completed','module'=>'security','entity_type'=>'user','entity_id'=>$usuario['user_id'],'action'=>'reset_password','result'=>'success','description'=>'El usuario restablecio su contrasena y se invalidaron las sesiones anteriores.'],$pdo))throw new RuntimeException('No fue posible registrar el cambio.');
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
    enviarPlantillaCorreoAtenea(
        'cambio_password',
        (string) $usuario['correo'],
        $nombre,
        [],
        ['usuario_id' => (int) $usuario['user_id'], 'idempotency_key' => 'cambio-password:token:' . (int) $usuario['token_id']]
    );
} catch (Throwable $e) {
    error_log('Confirmación de contraseña Atenea: ' . $e->getMessage());
}

$_SESSION['mensaje_auth'] = 'Tu contraseña se actualizó correctamente. Ya puedes iniciar sesión.';
$_SESSION['mensaje_auth_tipo'] = 'success';
header('Location: ' . atenea_url('src/login/sign-in.php'));
exit;
