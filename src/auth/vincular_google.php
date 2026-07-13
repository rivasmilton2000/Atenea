<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
exigirRol(['usuario']);

function volverPerfilGoogle(string $mensaje): never
{
    $_SESSION['perfil_mensaje'] = $mensaje;
    header('Location: ' . atenea_url('src/estudiantes/perfil.php'));
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !validarTokenCsrf(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
    volverPerfilGoogle('La solicitud de Google expiró.');
}

$configuracion = require dirname(__DIR__, 2) . '/config/google.php';
$autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
if ($configuracion['client_id'] === '' || !is_file($autoload)) {
    volverPerfilGoogle('Google aún no está configurado.');
}
require $autoload;

try {
    $payload = (new Google_Client(['client_id' => $configuracion['client_id']]))->verifyIdToken((string) ($_POST['credential'] ?? ''));
    if (!$payload || ($payload['aud'] ?? '') !== $configuracion['client_id'] || empty($payload['email_verified']) || empty($payload['sub']) || strtolower((string) $payload['email']) !== strtolower((string) $_SESSION['usuario_correo'])) {
        throw new RuntimeException('Token, audiencia o correo inválido.');
    }

    $pdo = obtenerConexion();
    $consulta = $pdo->prepare('SELECT id FROM usuarios WHERE google_id=:google_id AND id<>:id LIMIT 1');
    $consulta->execute(['google_id' => (string) $payload['sub'], 'id' => (int) $_SESSION['usuario_id']]);
    if ($consulta->fetch()) throw new RuntimeException('Identidad de Google ya vinculada.');

    $consulta = $pdo->prepare("UPDATE usuarios SET google_id=:google_id,proveedor=IF(proveedor='local','mixto','google'),email_verificado=1 WHERE id=:id AND estado='activo'");
    $consulta->execute(['google_id' => (string) $payload['sub'], 'id' => (int) $_SESSION['usuario_id']]);
    volverPerfilGoogle('Cuenta de Google vinculada correctamente.');
} catch (Throwable $e) {
    error_log('Vincular Google Atenea: ' . $e->getMessage());
    volverPerfilGoogle('No fue posible vincular esa cuenta de Google.');
}
