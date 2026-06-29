<?php
require '../includes/connection.php';
require 'session.php';
require_once '../includes/atenea_auth.php';

header('Content-Type: application/json; charset=UTF-8');

function atenea_json_response(string $status, string $message, array $extra = [], int $code = 200): void
{
    http_response_code($code);
    echo json_encode(
        array_merge(
            [
                'status' => $status,
                'message' => $message,
            ],
            $extra
        ),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    atenea_json_response('error', 'Método no permitido.', [], 405);
}

if (!atenea_google_is_enabled()) {
    atenea_json_response(
        'error',
        'Google Sign-In aún no está configurado para Atenea.',
        [],
        503
    );
}

$rawBody = file_get_contents('php://input');
$payload = json_decode((string) $rawBody, true);

if (!is_array($payload)) {
    $payload = $_POST;
}

$credential = trim((string) ($payload['credential'] ?? ''));
$nonce = trim((string) ($payload['nonce'] ?? ''));
$storedNonce = (string) ($_SESSION['GOOGLE_LOGIN_NONCE'] ?? '');

if ($storedNonce === '' || $nonce === '' || !hash_equals($storedNonce, $nonce)) {
    atenea_json_response(
        'error',
        'La solicitud de Google expiró. Recarga la página de inicio de sesión e intenta otra vez.',
        [],
        419
    );
}

$verification = atenea_verify_google_credential($credential, atenea_google_client_ids());
if (empty($verification['ok'])) {
    atenea_json_response(
        'error',
        (string) ($verification['message'] ?? 'No pudimos validar la cuenta de Google.'),
        [],
        401
    );
}

$googlePayload = (array) ($verification['payload'] ?? []);
$googleEmail = trim((string) ($googlePayload['email'] ?? ''));
$googleSub = trim((string) ($googlePayload['sub'] ?? ''));

$user = atenea_fetch_user_by_email($db, $googleEmail);
if (!$user) {
    atenea_json_response(
        'error',
        'El correo de Google no está vinculado a ninguna cuenta activa dentro de Atenea.',
        [],
        404
    );
}

atenea_sync_public_google_identity($db, (int) ($user['ID'] ?? 0), $googleEmail, $googleSub);
$freshUser = atenea_fetch_user_by_id($db, (int) ($user['ID'] ?? 0));
if ($freshUser) {
    $user = $freshUser;
}

session_regenerate_id(true);
atenea_apply_session_data(
    $user,
    'google',
    [
        'email' => $googleEmail,
        'sub' => $googleSub,
    ]
);

unset($_SESSION['GOOGLE_LOGIN_NONCE']);

atenea_json_response(
    'success',
    'Hola ' . atenea_user_display_name($user, true) . ', tu acceso con Google fue validado correctamente.',
    [
        'redirect' => atenea_dashboard_route_for_user($user),
        'provider' => 'google',
    ]
);
