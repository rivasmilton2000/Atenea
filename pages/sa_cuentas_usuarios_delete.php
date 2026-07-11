<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../includes/connection.php';

header('Content-Type: application/json; charset=UTF-8');

if (!function_exists('sa_users_delete_response')) {
    function sa_users_delete_response(bool $success, string $message, int $httpStatus = 200): void
    {
        http_response_code($httpStatus);
        echo json_encode(
            ['success' => $success, 'message' => $message],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }
}

confirm_logged_in();

if ((string) ($_SESSION['TYPE'] ?? '') !== 'SuperAdmin') {
    sa_users_delete_response(false, 'Solo SuperAdmin puede eliminar cuentas.', 403);
}

$userId = (int) filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if ($userId <= 0) {
    sa_users_delete_response(false, 'El identificador de usuario no es valido.');
}

if ($userId === (int) ($_SESSION['MEMBER_ID'] ?? 0)) {
    sa_users_delete_response(false, 'No puedes eliminar tu propia cuenta activa.');
}

$stmtUser = $db->prepare(
    'SELECT u.ID, t.TYPE
     FROM users u
     LEFT JOIN type t ON t.TYPE_ID = u.TYPE_ID
     WHERE u.ID = ?
     LIMIT 1'
);

if (!$stmtUser) {
    sa_users_delete_response(false, 'No fue posible validar la cuenta a eliminar.');
}

$stmtUser->bind_param('i', $userId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser instanceof mysqli_result ? $resultUser->fetch_assoc() : null;
if ($resultUser instanceof mysqli_result) {
    mysqli_free_result($resultUser);
}
$stmtUser->close();

if (!$user) {
    sa_users_delete_response(false, 'La cuenta solicitada no existe.');
}

$role = (string) ($user['TYPE'] ?? '');
if (!in_array($role, ['Admin', 'Personal', 'Estudiante', 'Docente'], true)) {
    sa_users_delete_response(false, 'Solo se pueden eliminar cuentas operativas de Atenea desde este modulo.');
}

$stmtDelete = $db->prepare('DELETE FROM users WHERE ID = ? LIMIT 1');
if (!$stmtDelete) {
    sa_users_delete_response(false, 'No fue posible preparar la eliminacion de la cuenta.');
}

$stmtDelete->bind_param('i', $userId);
$success = $stmtDelete->execute();
$stmtDelete->close();

if (!$success) {
    sa_users_delete_response(false, 'Error al eliminar la cuenta: ' . mysqli_error($db));
}

sa_users_delete_response(true, 'La cuenta fue eliminada correctamente.');
