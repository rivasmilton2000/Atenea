<?php
require '../includes/connection.php';
require 'session.php';
require_once '../includes/atenea_auth.php';

if (!function_exists('atenea_register_password_errors')) {
    function atenea_register_password_errors(string $password, string $confirmPassword): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'La contrasena debe tener al menos 8 caracteres.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'La contrasena debe incluir al menos una letra mayuscula.';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'La contrasena debe incluir al menos una letra minuscula.';
        }

        if (!preg_match('/\d/', $password)) {
            $errors[] = 'La contrasena debe incluir al menos un numero.';
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'La contrasena debe incluir al menos un simbolo.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'La confirmacion de la contrasena no coincide.';
        }

        return $errors;
    }
}

if (!function_exists('atenea_register_fail')) {
    function atenea_register_fail(string $title, string $message, string $icon, array $formData): void
    {
        $_SESSION['register_form'] = $formData;
        atenea_render_auth_alert($icon, $title, $message, 'registro.php');
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
$formData = [
    'first_name' => $firstName,
    'last_name' => $lastName,
    'email' => $email,
    'phone_number' => $phoneNumber,
    'birthdate' => $birthdate,
    'username' => $username,
    'billing_tipo_documento' => strtoupper(trim((string) ($_POST['billing_tipo_documento'] ?? ''))),
    'billing_numero_documento' => trim((string) ($_POST['billing_numero_documento'] ?? '')),
    'billing_departamento' => trim((string) ($_POST['billing_departamento'] ?? '')),
    'billing_municipio' => trim((string) ($_POST['billing_municipio'] ?? '')),
    'billing_distrito' => trim((string) ($_POST['billing_distrito'] ?? '')),
    'billing_address' => trim((string) ($_POST['billing_address'] ?? '')),
];

if ($firstName === '' || $lastName === '' || $email === '' || $username === '' || $password === '' || $passwordConfirm === '') {
    atenea_register_fail('Completa el registro', 'Debes llenar todos los campos obligatorios para crear tu cuenta.', 'warning', $formData);
}

if (!preg_match("/^[\p{L}\p{M}\s'.-]{2,100}$/u", $firstName) || !preg_match("/^[\p{L}\p{M}\s'.-]{2,100}$/u", $lastName)) {
    atenea_register_fail('Nombre no valido', 'Los nombres y apellidos deben contener unicamente letras, espacios y signos basicos.', 'warning', $formData);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    atenea_register_fail('Correo invalido', 'Escribe un correo electronico valido para continuar.', 'warning', $formData);
}

if (!preg_match('/^[A-Za-z0-9._-]{4,50}$/', $username)) {
    atenea_register_fail('Usuario no valido', 'El nombre de usuario debe tener al menos 4 caracteres y solo puede usar letras, numeros, punto, guion o guion bajo.', 'warning', $formData);
}

$billingValidation = atenea_validate_billing_profile_input($formData, [
    'require_name' => false,
    'require_email' => false,
]);
if ($billingValidation['errors'] !== []) {
    atenea_register_fail('Datos de facturacion incompletos', (string) $billingValidation['errors'][0], 'warning', $formData);
}

$passwordErrors = atenea_register_password_errors($password, $passwordConfirm);
if ($passwordErrors !== []) {
    atenea_register_fail('Contrasena no valida', implode(' ', $passwordErrors), 'warning', $formData);
}

$birthdateValue = null;
if ($birthdate !== '') {
    $birthdateTimestamp = strtotime($birthdate);
    if ($birthdateTimestamp === false) {
        atenea_register_fail('Fecha no valida', 'La fecha de nacimiento no tiene un formato valido.', 'warning', $formData);
    }

    if ($birthdateTimestamp > time()) {
        atenea_register_fail('Fecha no valida', 'La fecha de nacimiento no puede estar en el futuro.', 'warning', $formData);
    }

    $birthdateValue = date('Y-m-d', $birthdateTimestamp);
}

if (atenea_username_exists($db, $username)) {
    atenea_register_fail('Usuario ocupado', 'Ese nombre de usuario ya esta registrado. Prueba con otro diferente.', 'error', $formData);
}

if (atenea_email_exists_for_any_account($db, $email)) {
    atenea_register_fail('Correo en uso', 'Ese correo ya esta vinculado a otra cuenta dentro de Atenea.', 'error', $formData);
}

$billingData = (array) ($billingValidation['data'] ?? []);
$billingName = atenea_profile_full_name($firstName, $lastName);
$billingNrc = trim((string) ($billingData['billing_nrc'] ?? '')) !== '' ? (string) $billingData['billing_nrc'] : null;
$billingProfileCompleted = atenea_billing_profile_is_complete($billingData) ? 1 : 0;

mysqli_begin_transaction($db);

try {
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
    $stmtPublic = $db->prepare(
        'INSERT INTO public_users (
            USER_ID,
            FIRST_NAME,
            LAST_NAME,
            EMAIL,
            PHONE_NUMBER,
            BIRTHDATE,
            BILLING_NAME,
            BILLING_EMAIL,
            TIPO_DOCUMENTO,
            NUMERO_DOCUMENTO,
            BILLING_DEPARTAMENTO,
            BILLING_MUNICIPIO,
            BILLING_DISTRITO,
            BILLING_DIRECCION,
            BILLING_NRC,
            BILLING_PROFILE_COMPLETED,
            PLAN_STATUS,
            ACCOUNT_STATUS
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
    );

    if (!$stmtPublic) {
        throw new Exception('No se pudo preparar el perfil del usuario.');
    }

    $stmtPublic->bind_param(
        'issssssssssssssis',
        $userId,
        $firstName,
        $lastName,
        $email,
        $billingData['phone_number'],
        $birthdateValue,
        $billingName,
        $email,
        $billingData['tipo_documento'],
        $billingData['numero_documento'],
        $billingData['billing_departamento'],
        $billingData['billing_municipio'],
        $billingData['billing_distrito'],
        $billingData['billing_direccion'],
        $billingNrc,
        $billingProfileCompleted,
        $planStatus
    );

    if (!$stmtPublic->execute()) {
        throw new Exception('No se pudo guardar la informacion del usuario.');
    }

    $stmtPublic->close();
    mysqli_commit($db);
    unset($_SESSION['register_form']);

    $user = atenea_fetch_user_by_credentials($db, $username, $passwordHash);
    if (!$user) {
        throw new Exception('La cuenta fue creada, pero no se pudo iniciar sesion automaticamente.');
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
    atenea_register_fail('Registro no completado', $exception->getMessage(), 'error', $formData);
}
