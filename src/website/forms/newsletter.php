<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/includes/config.php';
require_once dirname(__DIR__, 3) . '/includes/session.php';
require_once dirname(__DIR__, 3) . '/includes/mailer.php';

const BOLETIN_MAX_INTENTOS = 5;
const BOLETIN_VENTANA_SEGUNDOS = 600;

function responderBoletin(string $mensaje, int $estado = 200): never
{
    http_response_code($estado);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $mensaje;
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    responderBoletin('Método no permitido.', 405);
}

if (!validarTokenCsrf(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
    responderBoletin('La solicitud expiró. Recarga la página e inténtalo nuevamente.');
}

$ahora = time();
$intentos = array_values(array_filter(
    is_array($_SESSION['boletin_intentos'] ?? null) ? $_SESSION['boletin_intentos'] : [],
    static fn($momento): bool => is_int($momento) && $momento >= $ahora - BOLETIN_VENTANA_SEGUNDOS
));
if (count($intentos) >= BOLETIN_MAX_INTENTOS) {
    responderBoletin('Has realizado demasiados intentos. Espera unos minutos.');
}
$intentos[] = $ahora;
$_SESSION['boletin_intentos'] = $intentos;

$correo = strtolower(trim((string) ($_POST['email'] ?? '')));
if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 190) {
    responderBoletin('Ingresa un correo electrónico válido.');
}

$configuracion = configuracionCorreoAtenea();
$destinatario = (string) ($configuracion['recipient'] ?? '');
if (!configuracionSmtpCompleta($configuracion) || !filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
    responderBoletin('El boletín no está disponible temporalmente. Inténtalo más tarde.');
}

try {
    enviarPlantillaCorreoAtenea(
        'aviso_administrativo',
        $destinatario,
        (string) ($configuracion['from_name'] ?? 'Atenea'),
        ['asunto' => 'Nueva suscripción al boletín de Atenea', 'resumen' => 'Se recibió una nueva solicitud de suscripción.', 'mensaje' => "Correo: {$correo}\nFecha: " . date('d/m/Y H:i')],
        ['idempotency_key' => 'boletin:' . hash('sha256', $correo . ':' . date('Y-m-d'))]
    );
} catch (Throwable $e) {
    error_log('Boletín Atenea: ' . $e->getMessage());
    responderBoletin('No fue posible procesar la suscripción. Inténtalo nuevamente.');
}

responderBoletin('OK');
