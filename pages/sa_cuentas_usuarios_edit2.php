<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../includes/connection.php';

header('Content-Type: application/json; charset=UTF-8');

if (!function_exists('sa_users_json_response')) {
    function sa_users_json_response(string $status, string $message, int $httpStatus = 200): void
    {
        http_response_code($httpStatus);
        echo json_encode(
            ['status' => $status, 'message' => $message],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }
}

confirm_logged_in();

if ((string) ($_SESSION['TYPE'] ?? '') !== 'SuperAdmin') {
    sa_users_json_response('error', 'Solo SuperAdmin puede actualizar cuentas internas.', 403);
}

$userId = (int) filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
$employeeId = (int) filter_input(INPUT_POST, 'empid', FILTER_SANITIZE_NUMBER_INT);
$username = trim((string) filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW));
$password = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');
$roleId = (int) filter_input(INPUT_POST, 'type', FILTER_SANITIZE_NUMBER_INT);
$status = (int) filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_NUMBER_INT);

if ($userId <= 0 || $employeeId <= 0) {
    sa_users_json_response('error', 'La cuenta interna indicada no es valida.');
}

if ($username === '' || strlen($username) < 5 || strlen($username) > 70) {
    sa_users_json_response('error', 'El usuario debe tener entre 5 y 70 caracteres.');
}

if (!atenea_is_valid_employee_role_id($roleId)) {
    sa_users_json_response('error', 'El rol seleccionado no es valido para una cuenta interna.');
}

if (!in_array($status, [0, 1], true)) {
    sa_users_json_response('error', 'El estado seleccionado no es valido.');
}

if ($password !== '' || $confirmPassword !== '') {
    if ($password !== $confirmPassword) {
        sa_users_json_response('error', 'Las contrasenas no coinciden.');
    }

    if (strlen($password) < 8 || strlen($password) > 80) {
        sa_users_json_response('error', 'La contrasena debe tener entre 8 y 80 caracteres.');
    }
}

$stmtCurrent = $db->prepare(
    'SELECT ID, EMPLOYEE_ID, USERNAME, TYPE_ID, U_ESTADO
     FROM users
     WHERE ID = ? AND EMPLOYEE_ID = ? AND TYPE_ID IN (1, 2, 4)
     LIMIT 1'
);

if (!$stmtCurrent) {
    sa_users_json_response('error', 'No fue posible cargar la cuenta interna.');
}

$stmtCurrent->bind_param('ii', $userId, $employeeId);
$stmtCurrent->execute();
$resultCurrent = $stmtCurrent->get_result();
$currentUser = $resultCurrent instanceof mysqli_result ? $resultCurrent->fetch_assoc() : null;
if ($resultCurrent instanceof mysqli_result) {
    mysqli_free_result($resultCurrent);
}
$stmtCurrent->close();

if (!$currentUser) {
    sa_users_json_response('error', 'La cuenta interna ya no existe o no puede editarse desde este formulario.');
}

if (atenea_username_exists($db, $username, $userId)) {
    sa_users_json_response('error', 'El nombre de usuario ya existe.');
}

$stmtRoleDuplicate = $db->prepare(
    'SELECT ID
     FROM users
     WHERE EMPLOYEE_ID = ? AND TYPE_ID = ? AND ID <> ?
     LIMIT 1'
);

if (!$stmtRoleDuplicate) {
    sa_users_json_response('error', 'No fue posible validar el rol asignado.');
}

$stmtRoleDuplicate->bind_param('iii', $employeeId, $roleId, $userId);
$stmtRoleDuplicate->execute();
$resultRoleDuplicate = $stmtRoleDuplicate->get_result();
$roleDuplicate = $resultRoleDuplicate instanceof mysqli_result && $resultRoleDuplicate->num_rows > 0;
if ($resultRoleDuplicate instanceof mysqli_result) {
    mysqli_free_result($resultRoleDuplicate);
}
$stmtRoleDuplicate->close();

if ($roleDuplicate) {
    sa_users_json_response('error', 'Este empleado ya tiene una cuenta con el rol seleccionado.');
}

$passwordChanged = $password !== '';
$noChanges = (string) ($currentUser['USERNAME'] ?? '') === $username
    && (int) ($currentUser['TYPE_ID'] ?? 0) === $roleId
    && (int) ($currentUser['U_ESTADO'] ?? 0) === $status
    && !$passwordChanged;

if ($noChanges) {
    sa_users_json_response('warning', 'No se detectaron cambios para guardar.');
}

$sql = 'UPDATE users SET USERNAME = ?, TYPE_ID = ?, U_ESTADO = ?';
$types = 'sii';
$params = [$username, $roleId, $status];

if ($passwordChanged) {
    $sql .= ', PASSWORD = SHA1(?)';
    $types .= 's';
    $params[] = $password;
}

$sql .= ' WHERE ID = ? LIMIT 1';
$types .= 'i';
$params[] = $userId;

$stmtUpdate = $db->prepare($sql);
if (!$stmtUpdate) {
    sa_users_json_response('error', 'No fue posible preparar la actualizacion de la cuenta.');
}

$stmtUpdate->bind_param($types, ...$params);
$success = $stmtUpdate->execute();
$stmtUpdate->close();

if (!$success) {
    sa_users_json_response('error', 'Error al actualizar la cuenta interna: ' . mysqli_error($db));
}

sa_users_json_response('success', 'La cuenta interna fue actualizada correctamente.');
