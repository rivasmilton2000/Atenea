<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/mailer.php';
require_once dirname(__DIR__, 2) . '/includes/audit.php';

const MENSAJE_RECUPERACION = 'Si existe una cuenta asociada a ese correo, recibirás un enlace para restablecer la contraseña.';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . atenea_url('src/login/forgot-password.php'));
    exit;
}
if (!validarTokenCsrf(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
    $_SESSION['recuperacion_mensaje'] = 'La solicitud expiró. Recarga la página e intenta nuevamente.';
    $_SESSION['recuperacion_tipo'] = 'danger';
    header('Location: ' . atenea_url('src/login/forgot-password.php'));
    exit;
}

$correo = strtolower(trim((string) ($_POST['correo'] ?? '')));
$correoValido = strlen($correo) <= 190 && filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
$emailHash = hash('sha256', $correo);
$ipHash = hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'desconocida'));
$usuario = null;
$token = null;

try {
    $pdo = obtenerConexion();
    $limite = $pdo->prepare('SELECT SUM(email_hash=:email_hash) solicitudes_correo,SUM(request_ip_hash=:ip_hash) solicitudes_ip FROM password_reset_tokens WHERE created_at>=DATE_SUB(NOW(),INTERVAL 1 HOUR)');
    $limite->execute(['email_hash' => $emailHash, 'ip_hash' => $ipHash]);
    $conteos = $limite->fetch() ?: [];
    $limitado = (int) ($conteos['solicitudes_correo'] ?? 0) >= 3 || (int) ($conteos['solicitudes_ip'] ?? 0) >= 5;

    if ($correoValido && !$limitado) {
        $consulta = $pdo->prepare("SELECT id,nombre,apellido,correo FROM usuarios WHERE correo=:correo AND estado='activo' LIMIT 1");
        $consulta->execute(['correo' => $correo]);
        $usuario = $consulta->fetch();
        $token = is_array($usuario) ? bin2hex(random_bytes(32)) : null;
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE password_reset_tokens SET used_at=NOW() WHERE email_hash=:email_hash AND used_at IS NULL')->execute(['email_hash' => $emailHash]);
        $registro = $pdo->prepare('INSERT INTO password_reset_tokens(user_id,email_hash,token_hash,request_ip_hash,expires_at) VALUES(:usuario,:email_hash,:token_hash,:ip_hash,DATE_ADD(NOW(),INTERVAL 30 MINUTE))');
        $registro->execute([
            'usuario' => is_array($usuario) ? (int) $usuario['id'] : null,
            'email_hash' => $emailHash,
            'token_hash' => $token !== null ? hash('sha256', $token) : null,
            'ip_hash' => $ipHash,
        ]);
        registrarAuditoria(['target_user_id'=>is_array($usuario)?(int)$usuario['id']:null,'event_type'=>'password.reset_requested','module'=>'security','entity_type'=>is_array($usuario)?'user':null,'entity_id'=>is_array($usuario)?(int)$usuario['id']:null,'action'=>'request','result'=>'success','description'=>'Se proceso una solicitud de recuperacion de contrasena.'], $pdo);
        $pdo->commit();
    }
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Solicitud de recuperación Atenea: ' . $e->getMessage());
    $usuario = null;
    $token = null;
}

if (is_array($usuario) && is_string($token)) {
    $nombre = trim((string) ($usuario['nombre'] . ' ' . $usuario['apellido']));
    $enlace = atenea_url_absoluta('src/login/reset-password.php?token=' . rawurlencode($token));
    try {
        enviarPlantillaCorreoAtenea(
            'recuperacion_password',
            (string) $usuario['correo'],
            $nombre,
            ['enlace' => $enlace],
            ['usuario_id' => (int) $usuario['id'], 'idempotency_key' => 'recuperacion-password:' . hash('sha256', $token)]
        );
    } catch (Throwable $e) {
        error_log('Correo de recuperación Atenea: ' . $e->getMessage());
    }
}

$_SESSION['recuperacion_mensaje'] = MENSAJE_RECUPERACION;
$_SESSION['recuperacion_tipo'] = 'success';
header('Location: ' . atenea_url('src/login/forgot-password.php'));
exit;
