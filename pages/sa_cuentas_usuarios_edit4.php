<?php
include('../includes/connection.php');

// Validar y sanitizar las entradas
$zz = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
$estid = filter_input(INPUT_POST, 'estid', FILTER_SANITIZE_STRING);
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

// Validar si el nombre de usuario ya existe
$query = "SELECT ID FROM users WHERE USERNAME = ? AND ID != ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "si", $username, $zz);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'El nombre de usuario ya existe.']);
    exit();
}

// Verificar si no se han realizado cambios
$query = "SELECT * FROM users WHERE ID = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $zz);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$current_data = mysqli_fetch_assoc($result);

$no_changes = true;
if ($current_data['ESTUDIANTE_ID'] != $estid || $current_data['USERNAME'] != $username || 
    (!empty($password) && sha1($password) != $current_data['PASSWORD']) ||
    $current_data['TYPE_ID'] != $type || $current_data['U_ESTADO'] != $estado) {
    $no_changes = false;
}

if ($no_changes) {
    echo json_encode(['status' => 'warning', 'message' => 'No se han realizado cambios.']);
    exit();
}

// Verificar si la nueva contraseña ya está en uso por otra cuenta
if (!empty($password)) {
    $password_hash = sha1($password);
    $query = "SELECT ID FROM users WHERE PASSWORD = ? AND ID != ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "si", $password_hash, $zz);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'La nueva contraseña ya está en uso por otra cuenta.']);
        exit();
    }
}

// Actualizar el usuario
$query = "UPDATE users SET ESTUDIANTE_ID = ?, USERNAME = ?, TYPE_ID = ?, U_ESTADO = ?";
$params = [$estid, $username, $type, $estado];
$types = "ssii";

if (!empty($password)) {
    $query .= ", PASSWORD = SHA1(?)";
    $params[] = $password;
    $types .= "s";
}

$query .= " WHERE ID = ?";
$params[] = $zz;
$types .= "i";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
$result = mysqli_stmt_execute($stmt);

if ($result) {
    echo json_encode(['status' => 'success', 'message' => 'Has actualizado la cuenta de estudiante correctamente.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la cuenta de estudiante: ' . mysqli_error($db)]);
}
?>