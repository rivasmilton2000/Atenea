<?php
require 'session.php';
require_once '../includes/connection.php';
require_once '../includes/atenea_auth.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode((string) file_get_contents('php://input'), true);

$credential = trim((string) ($input['credential'] ?? ''));
$nonce = trim((string) ($input['nonce'] ?? ''));

if (empty($_SESSION['GOOGLE_REGISTER_NONCE']) || !hash_equals((string) $_SESSION['GOOGLE_REGISTER_NONCE'], $nonce)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'La validación de seguridad expiró. Recarga la página e intenta nuevamente.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$verify = atenea_verify_google_credential($credential, atenea_google_client_ids());

if (empty($verify['ok'])) {
    echo json_encode([
        'status' => 'error',
        'message' => (string) ($verify['message'] ?? 'No pudimos validar la cuenta de Google.'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$payload = (array) ($verify['payload'] ?? []);

$email = trim((string) ($payload['email'] ?? ''));
$firstName = trim((string) ($payload['given_name'] ?? ''));
$lastName = trim((string) ($payload['family_name'] ?? ''));
$googleSub = trim((string) ($payload['sub'] ?? ''));

if ($email === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Google no devolvió un correo válido.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

atenea_ensure_public_user_schema($db);

$existingUser = atenea_fetch_user_by_email($db, $email);

if ($existingUser) {
    atenea_sync_public_google_identity($db, (int) ($existingUser['ID'] ?? 0), $email, $googleSub);
    $freshUser = atenea_fetch_user_by_id($db, (int) ($existingUser['ID'] ?? 0));
    if ($freshUser) {
        $existingUser = $freshUser;
    }

    session_regenerate_id(true);
    atenea_apply_session_data($existingUser, 'google', [
        'email' => $email,
        'sub' => $googleSub,
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Este correo ya estaba registrado. Iniciamos sesión con Google.',
        'redirect' => atenea_dashboard_route_for_user($existingUser),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($firstName === '') {
    $firstName = explode('@', $email)[0];
}

$usernameBase = strtolower((string) preg_replace('/[^a-zA-Z0-9._-]/', '', explode('@', $email)[0]));

if ($usernameBase === '') {
    $usernameBase = 'usuario';
}

$username = $usernameBase;
$counter = 1;

while (atenea_username_exists($db, $username)) {
    $username = $usernameBase . $counter;
    $counter++;
}

$plainRandomPassword = bin2hex(random_bytes(16));
// TODO: Migrar este almacenamiento heredado de SHA1 a password_hash/password_verify.
$passwordHash = sha1($plainRandomPassword);

mysqli_begin_transaction($db);

try {
    if (atenea_db_has_column($db, 'users', 'U_ESTADO')) {
        $stmtUser = $db->prepare(
            'INSERT INTO users (EMPLOYEE_ID, USERNAME, PASSWORD, TYPE_ID, ESTUDIANTE_ID, U_ESTADO)
             VALUES (NULL, ?, ?, NULL, NULL, 1)'
        );
    } else {
        $stmtUser = $db->prepare(
            'INSERT INTO users (EMPLOYEE_ID, USERNAME, PASSWORD, TYPE_ID, ESTUDIANTE_ID)
             VALUES (NULL, ?, ?, NULL, NULL)'
        );
    }

    if (!$stmtUser) {
        throw new Exception('No se pudo preparar el registro del usuario.');
    }

    $stmtUser->bind_param('ss', $username, $passwordHash);

    if (!$stmtUser->execute()) {
        throw new Exception('No se pudo crear la cuenta principal.');
    }

    $userId = (int) $stmtUser->insert_id;
    $stmtUser->close();

    $phoneNumber = '';
    $planStatus = 'pending';

    $stmtPublic = $db->prepare(
        'INSERT INTO public_users
         (USER_ID, FIRST_NAME, LAST_NAME, EMAIL, PHONE_NUMBER, PLAN_STATUS, ACCOUNT_STATUS)
         VALUES (?, ?, ?, ?, ?, ?, 1)'
    );

    if (!$stmtPublic) {
        throw new Exception('No se pudo preparar el perfil público.');
    }

    $stmtPublic->bind_param(
        'isssss',
        $userId,
        $firstName,
        $lastName,
        $email,
        $phoneNumber,
        $planStatus
    );

    if (!$stmtPublic->execute()) {
        throw new Exception('No se pudo guardar la información del usuario.');
    }

    $stmtPublic->close();
    atenea_sync_public_google_identity($db, $userId, $email, $googleSub);

    mysqli_commit($db);

    $user = atenea_fetch_user_by_id($db, $userId);
    if (!$user) {
        throw new Exception('La cuenta fue creada, pero no se pudo iniciar sesión automáticamente.');
    }

    session_regenerate_id(true);
    atenea_apply_session_data($user, 'google', [
        'email' => $email,
        'sub' => $googleSub,
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Tu cuenta fue creada correctamente con Google.',
        'redirect' => atenea_dashboard_route_for_user($user),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
} catch (Throwable $exception) {
    mysqli_rollback($db);

    echo json_encode([
        'status' => 'error',
        'message' => $exception->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
