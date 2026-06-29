<?php
include '../includes/connection.php';
header('Content-Type: application/json');

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp = trim($_POST['empid'] ?? '');
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';
    $type = trim($_POST['type'] ?? '');
    $estado = trim($_POST['estado'] ?? '');

    // Validar campos vacíos
    if (empty($emp) || empty($user) || empty($pass) || empty($confirm_pass) || empty($type) || empty($estado)) {
        $response['message'] = 'Todos los campos son obligatorios.';
        echo json_encode($response);
        exit;
    }

    // Validar que las contraseñas coincidan (doble verificación)
    if ($pass !== $confirm_pass) {
        $response['message'] = 'Las contraseñas no coinciden, intentalo de nuevo.';
        echo json_encode($response);
        exit;
    }

    // Validar longitud mínima de la contraseña
    if (strlen($pass) < 8) {
        $response['message'] = 'La contraseña debe tener al menos 8 caracteres.';
        echo json_encode($response);
        exit;
    }

    // Verificar si el usuario ya existe
    $checkUser = mysqli_query($db, "SELECT * FROM users WHERE USERNAME = '$user'");
    if (mysqli_num_rows($checkUser) > 0) {
        $response['message'] = 'El nombre de usuario ya existe.';
        echo json_encode($response);
        exit;
    }

    // Verificar si el empleado ya tiene una cuenta del mismo tipo
    $checkEmployeeType = mysqli_query($db, "SELECT * FROM users WHERE EMPLOYEE_ID = '$emp' AND TYPE_ID = '$type'");
    if (mysqli_num_rows($checkEmployeeType) > 0) {
        $response['message'] = 'Este empleado ya tiene una cuenta del mismo tipo.';
        echo json_encode($response);
        exit;
    }

    // Insertar nuevo usuario
    $query = "INSERT INTO users (EMPLOYEE_ID, USERNAME, PASSWORD, TYPE_ID, U_ESTADO) VALUES (?, ?, SHA1(?), ?, ?)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'sssss', $emp, $user, $pass, $type, $estado);
    
    if (mysqli_stmt_execute($stmt)) {
        $response['success'] = true;
        $response['message'] = 'Usuario creado exitosamente.';
    } else {
        $response['message'] = 'Error al crear el usuario: ' . mysqli_error($db);
    }

    mysqli_stmt_close($stmt);
}

echo json_encode($response);
?>