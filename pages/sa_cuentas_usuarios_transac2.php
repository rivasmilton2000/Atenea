<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../includes/connection.php';

header('Content-Type: application/json; charset=UTF-8');

if (!function_exists('sa_users_json_response')) {
    function sa_users_json_response(string $status, string $message, int $httpStatus = 200): void
    {
        http_response_code($httpStatus);
        echo json_encode(
            ['success' => $status === 'success', 'message' => $message],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }
}

confirm_logged_in();

if ((string) ($_SESSION['TYPE'] ?? '') !== 'SuperAdmin') {
    sa_users_json_response('error', 'Solo SuperAdmin puede crear cuentas de estudiantes.', 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sa_users_json_response('error', 'Metodo no permitido.', 405);
}

$studentId = (int) filter_input(INPUT_POST, 'estid', FILTER_SANITIZE_NUMBER_INT);
$username = trim((string) filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW));
$password = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');
$roleId = (int) filter_input(INPUT_POST, 'type', FILTER_SANITIZE_NUMBER_INT);
$status = (int) filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_NUMBER_INT);

if ($studentId <= 0 || $username === '' || $password === '' || $confirmPassword === '') {
    sa_users_json_response('error', 'Todos los campos son obligatorios.');
}

if (!atenea_is_valid_student_role_id($roleId)) {
    sa_users_json_response('error', 'El rol seleccionado no es valido para una cuenta de estudiante.');
}

if (!in_array($status, [0, 1], true)) {
    sa_users_json_response('error', 'El estado seleccionado no es valido.');
}

if ($password !== $confirmPassword) {
    sa_users_json_response('error', 'Las contrasenas no coinciden.');
}

if (strlen($username) < 5 || strlen($username) > 70) {
    sa_users_json_response('error', 'El usuario debe tener entre 5 y 70 caracteres.');
}

if (strlen($password) < 8 || strlen($password) > 80) {
    sa_users_json_response('error', 'La contrasena debe tener entre 8 y 80 caracteres.');
}

if (atenea_username_exists($db, $username)) {
    sa_users_json_response('error', 'El nombre de usuario ya existe.');
}

$stmtStudent = $db->prepare(
    'SELECT ESTUDIANTE_ID
     FROM estudiantes
     WHERE ESTUDIANTE_ID = ? AND estado_estudiante = 1
     LIMIT 1'
);

if (!$stmtStudent) {
    sa_users_json_response('error', 'No fue posible validar el estudiante seleccionado.');
}

$stmtStudent->bind_param('i', $studentId);
$stmtStudent->execute();
$resultStudent = $stmtStudent->get_result();
$studentExists = $resultStudent instanceof mysqli_result && $resultStudent->num_rows > 0;
if ($resultStudent instanceof mysqli_result) {
    mysqli_free_result($resultStudent);
}
$stmtStudent->close();

if (!$studentExists) {
    sa_users_json_response('error', 'El estudiante seleccionado no existe o esta inactivo.');
}

$stmtDuplicateStudent = $db->prepare(
    'SELECT ID
     FROM users
     WHERE ESTUDIANTE_ID = ?
     LIMIT 1'
);

if (!$stmtDuplicateStudent) {
    sa_users_json_response('error', 'No fue posible validar si el estudiante ya tiene cuenta.');
}

$stmtDuplicateStudent->bind_param('i', $studentId);
$stmtDuplicateStudent->execute();
$resultDuplicateStudent = $stmtDuplicateStudent->get_result();
$duplicateStudent = $resultDuplicateStudent instanceof mysqli_result && $resultDuplicateStudent->num_rows > 0;
if ($resultDuplicateStudent instanceof mysqli_result) {
    mysqli_free_result($resultDuplicateStudent);
}
$stmtDuplicateStudent->close();

if ($duplicateStudent) {
    sa_users_json_response('error', 'Este estudiante ya tiene una cuenta asociada.');
}

$stmtInsert = $db->prepare(
    'INSERT INTO users (ESTUDIANTE_ID, USERNAME, PASSWORD, TYPE_ID, U_ESTADO)
     VALUES (?, ?, SHA1(?), ?, ?)'
);

if (!$stmtInsert) {
    sa_users_json_response('error', 'No fue posible preparar la creacion de la cuenta.');
}

$stmtInsert->bind_param('issii', $studentId, $username, $password, $roleId, $status);
$success = $stmtInsert->execute();
$stmtInsert->close();

if (!$success) {
    sa_users_json_response('error', 'Error al crear la cuenta del estudiante: ' . mysqli_error($db));
}

sa_users_json_response('success', 'La cuenta del estudiante fue creada correctamente.');
