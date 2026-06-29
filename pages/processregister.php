<?php
require '../includes/connection.php';
require 'session.php';
require_once '../includes/atenea_auth.php';

if (!function_exists('atenea_register_password_errors')) {
    function atenea_register_password_errors(string $password, string $confirmPassword): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'La contraseña debe incluir al menos una letra mayúscula.';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'La contraseña debe incluir al menos una letra minúscula.';
        }

        if (!preg_match('/\d/', $password)) {
            $errors[] = 'La contraseña debe incluir al menos un número.';
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'La contraseña debe incluir al menos un símbolo.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'La confirmación de la contraseña no coincide.';
        }

        return $errors;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['btnregister'])) {
    header('Location: registro.php');
    exit;
}

atenea_ensure_public_user_schema($db);

$firstName = trim((string) ($_POST['first_name'] ?? ''));
$lastName = trim((string) ($_POST['last_name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phoneNumber = trim((string) ($_POST['phone_number'] ?? ''));
$birthdate = trim((string) ($_POST['birthdate'] ?? ''));
$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

if ($firstName === '' || $lastName === '' || $email === '' || $username === '' || $password === '' || $passwordConfirm === '') {
    atenea_render_auth_alert('warning', 'Completa el registro', 'Debes llenar todos los campos obligatorios para crear tu cuenta.', 'registro.php');
}

if (!preg_match("/^[\p{L}\p{M}\s'.-]{2,100}$/u", $firstName) || !preg_match("/^[\p{L}\p{M}\s'.-]{2,100}$/u", $lastName)) {
    atenea_render_auth_alert('warning', 'Nombre no válido', 'Los nombres y apellidos deben contener únicamente letras, espacios y signos básicos.', 'registro.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    atenea_render_auth_alert('warning', 'Correo inválido', 'Escribe un correo electrónico válido para continuar.', 'registro.php');
}

if ($phoneNumber !== '' && !preg_match('/^[0-9+\s().-]{7,25}$/', $phoneNumber)) {
    atenea_render_auth_alert('warning', 'Teléfono no válido', 'El teléfono o WhatsApp debe tener entre 7 y 25 caracteres válidos.', 'registro.php');
}

if (!preg_match('/^[A-Za-z0-9._-]{4,50}$/', $username)) {
    atenea_render_auth_alert('warning', 'Usuario no válido', 'El nombre de usuario debe tener al menos 4 caracteres y solo puede usar letras, números, punto, guion o guion bajo.', 'registro.php');
}

$passwordErrors = atenea_register_password_errors($password, $passwordConfirm);
if ($passwordErrors !== []) {
    atenea_render_auth_alert('warning', 'Contraseña no válida', implode(' ', $passwordErrors), 'registro.php');
}

$birthdateValue = null;
if ($birthdate !== '') {
    $birthdateTimestamp = strtotime($birthdate);
    if ($birthdateTimestamp === false) {
        atenea_render_auth_alert('warning', 'Fecha no válida', 'La fecha de nacimiento no tiene un formato válido.', 'registro.php');
    }

    if ($birthdateTimestamp > time()) {
        atenea_render_auth_alert('warning', 'Fecha no válida', 'La fecha de nacimiento no puede estar en el futuro.', 'registro.php');
    }

    $birthdateValue = date('Y-m-d', $birthdateTimestamp);
}

if (atenea_username_exists($db, $username)) {
    atenea_render_auth_alert('error', 'Usuario ocupado', 'Ese nombre de usuario ya está registrado. Prueba con otro diferente.', 'registro.php');
}

if (atenea_email_exists_for_any_account($db, $email)) {
    atenea_render_auth_alert('error', 'Correo en uso', 'Ese correo ya está vinculado a otra cuenta dentro de Atenea.', 'registro.php');
}

mysqli_begin_transaction($db);

try {
    // TODO: Migrar este almacenamiento heredado de SHA1 a password_hash/password_verify.
    $passwordHash = sha1($password);

    if (atenea_db_has_column($db, 'users', 'U_ESTADO')) {
        $stmtUser = $db->prepare('INSERT INTO users (EMPLOYEE_ID, USERNAME, PASSWORD, TYPE_ID, ESTUDIANTE_ID, U_ESTADO) VALUES (NULL, ?, ?, NULL, NULL, 1)');
    } else {
        $stmtUser = $db->prepare('INSERT INTO users (EMPLOYEE_ID, USERNAME, PASSWORD, TYPE_ID, ESTUDIANTE_ID) VALUES (NULL, ?, ?, NULL, NULL)');
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

    $planStatus = 'pending';
    $hasBirthdateColumn = atenea_db_has_column($db, 'public_users', 'BIRTHDATE');

    if ($hasBirthdateColumn) {
        $stmtPublic = $db->prepare(
            'INSERT INTO public_users (USER_ID, FIRST_NAME, LAST_NAME, EMAIL, PHONE_NUMBER, BIRTHDATE, PLAN_STATUS, ACCOUNT_STATUS)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1)'
        );

        if (!$stmtPublic) {
            throw new Exception('No se pudo preparar el perfil del usuario.');
        }

        $stmtPublic->bind_param('issssss', $userId, $firstName, $lastName, $email, $phoneNumber, $birthdateValue, $planStatus);
    } else {
        $stmtPublic = $db->prepare(
            'INSERT INTO public_users (USER_ID, FIRST_NAME, LAST_NAME, EMAIL, PHONE_NUMBER, PLAN_STATUS, ACCOUNT_STATUS)
             VALUES (?, ?, ?, ?, ?, ?, 1)'
        );

        if (!$stmtPublic) {
            throw new Exception('No se pudo preparar el perfil del usuario.');
        }

        $stmtPublic->bind_param('isssss', $userId, $firstName, $lastName, $email, $phoneNumber, $planStatus);
    }

    if (!$stmtPublic->execute()) {
        throw new Exception('No se pudo guardar la información del usuario.');
    }

    $stmtPublic->close();
    mysqli_commit($db);

    $user = atenea_fetch_user_by_credentials($db, $username, $passwordHash);
    if (!$user) {
        throw new Exception('La cuenta fue creada, pero no se pudo iniciar sesión automáticamente.');
    }

    session_regenerate_id(true);
    atenea_apply_session_data($user, 'password');

    atenea_render_auth_alert(
        'success',
        'Cuenta creada',
        'Bienvenido a Atenea. Ya puedes explorar planes de clase, pagos y productos desde tu panel.',
        atenea_dashboard_route_for_user($user)
    );
} catch (Throwable $exception) {
    mysqli_rollback($db);
    atenea_render_auth_alert('error', 'Registro no completado', $exception->getMessage(), 'registro.php');
}
