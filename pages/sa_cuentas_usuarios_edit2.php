<?php
include('../includes/connection.php');

// Validar y sanitizar las entradas
$zz = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
$empid = filter_input(INPUT_POST, 'empid', FILTER_SANITIZE_STRING);
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
$confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_UNSAFE_RAW);
$type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_NUMBER_INT);
$estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_NUMBER_INT);

// Verificar si las contraseñas coinciden
if (!empty($password) && $password !== $confirm_password) {
    echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden.']);
    exit();
}
if (!empty($password)) {
    $query = "SELECT * FROM users WHERE PASSWORD = SHA1(?) AND ID != ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "si", $password, $zz);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'La contraseña ya existe en el sistema. Por favor, elija una contraseña diferente.']);
        exit();
    }
}
// Verificar si el nombre de usuario ya existe
$query = "SELECT * FROM users WHERE USERNAME = ? AND ID != ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "si", $username, $zz);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'El nombre de usuario ya existe.']);
    exit();
}

// Verificar si ya existe una cuenta con el mismo tipo para el mismo empleado
$query = "SELECT * FROM users WHERE EMPLOYEE_ID = ? AND TYPE_ID = ? AND ID != ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "sii", $empid, $type, $zz);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Ya existe una cuenta con este tipo para el mismo empleado.']);
    exit();
}

// Construir la consulta de actualización
$query = 'UPDATE users SET USERNAME = ?, TYPE_ID = ?, U_ESTADO = ?';
$params = [$username, $type, $estado];
$types = "sii";

if (!empty($password)) {
    $query .= ', PASSWORD = SHA1(?)';
    $params[] = $password;
    $types .= "s";
}

$query .= ' WHERE ID = ?';
$params[] = $zz;
$types .= "i";

// Preparar y ejecutar la consulta
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
$result = mysqli_stmt_execute($stmt);

if ($result) {
    // Verificar si se han realizado cambios
    $current_data_query = "SELECT USERNAME, TYPE_ID, U_ESTADO FROM users WHERE ID = ?";
    $stmt = mysqli_prepare($db, $current_data_query);
    mysqli_stmt_bind_param($stmt, "i", $zz);
    mysqli_stmt_execute($stmt);
    $current_data_result = mysqli_stmt_get_result($stmt);
    $current_data = mysqli_fetch_assoc($current_data_result);

    if (
        $current_data['USERNAME'] == $username &&
        $current_data['TYPE_ID'] == $type &&
        $current_data['U_ESTADO'] == $estado &&
        empty($password)
    ) {
        echo json_encode(['status' => 'warning', 'message' => 'No se han realizado cambios.']);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Has actualizado la cuenta de usuario correctamente.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la cuenta de usuario: ' . mysqli_error($db)]);
}

exit();
?>