<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';

function falloGoogle(string $mensaje): never
{
    $_SESSION['mensaje_auth'] = $mensaje;
    header('Location: ' . atenea_url('src/login/sign-in.php'));
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !validarTokenCsrf(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
    falloGoogle('La solicitud de Google expiró.');
}

$configuracion = require dirname(__DIR__, 2) . '/config/google.php';
$autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
if ($configuracion['client_id'] === '' || !is_file($autoload)) {
    falloGoogle('El acceso con Google aún no está configurado.');
}
require $autoload;

try {
    $cliente = new Google_Client(['client_id' => $configuracion['client_id']]);
    $payload = $cliente->verifyIdToken((string) ($_POST['credential'] ?? ''));
    if (!$payload || ($payload['aud'] ?? '') !== $configuracion['client_id'] || empty($payload['email_verified']) || empty($payload['sub']) || empty($payload['email'])) {
        falloGoogle('No fue posible validar la cuenta de Google.');
    }

    $correo = strtolower((string) $payload['email']);
    $googleId = (string) $payload['sub'];
    $pdo = obtenerConexion();
    $pdo->beginTransaction();

    $consulta = $pdo->prepare('SELECT * FROM usuarios WHERE google_id=:google_id LIMIT 1 FOR UPDATE');
    $consulta->execute(['google_id' => $googleId]);
    $usuario = $consulta->fetch();

    if (!$usuario) {
        $consulta = $pdo->prepare('SELECT * FROM usuarios WHERE correo=:correo LIMIT 1 FOR UPDATE');
        $consulta->execute(['correo' => $correo]);
        $usuario = $consulta->fetch();

        if ($usuario) {
            if (!empty($usuario['google_id']) && !hash_equals((string) $usuario['google_id'], $googleId)) {
                throw new RuntimeException('El correo ya está vinculado con otra identidad de Google.');
            }
            $consulta = $pdo->prepare("UPDATE usuarios SET google_id=:google_id,proveedor=IF(proveedor='local','mixto','google'),email_verificado=1 WHERE id=:id");
            $consulta->execute(['google_id' => $googleId, 'id' => (int) $usuario['id']]);
            $usuario['google_id'] = $googleId;
            $usuario['email_verificado'] = 1;
        } else {
            $consulta = $pdo->prepare(
                "INSERT INTO usuarios(nombre,apellido,correo,password,google_id,proveedor,email_verificado,foto,rol,estado)
                 VALUES(:nombre,:apellido,:correo,NULL,:google_id,'google',1,:foto,'usuario','activo')"
            );
            $consulta->execute([
                'nombre' => mb_substr((string) ($payload['given_name'] ?? $payload['name'] ?? 'Estudiante'), 0, 100),
                'apellido' => mb_substr((string) ($payload['family_name'] ?? ''), 0, 100),
                'correo' => $correo,
                'google_id' => $googleId,
                'foto' => mb_substr((string) ($payload['picture'] ?? ''), 0, 500),
            ]);
            $consulta = $pdo->prepare('SELECT * FROM usuarios WHERE id=:id');
            $consulta->execute(['id' => (int) $pdo->lastInsertId()]);
            $usuario = $consulta->fetch();
        }
    }

    if (!is_array($usuario) || $usuario['estado'] !== 'activo') {
        throw new RuntimeException('La cuenta no está activa.');
    }

    $pdo->prepare('UPDATE usuarios SET ultimo_acceso=NOW() WHERE id=:id')->execute(['id' => (int) $usuario['id']]);
    $pdo->commit();
    iniciarSesionUsuario($usuario);
    redirigirPorRol((string) $usuario['rol']);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Google Atenea: ' . $e->getMessage());
    falloGoogle('No fue posible iniciar sesión con Google.');
}
