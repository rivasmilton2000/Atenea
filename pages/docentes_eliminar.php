<?php
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$password = "";
$database = "u445672402_escuela";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "Conexión fallida: " . $conn->connect_error]));
}

$conn->set_charset('utf8mb4');

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $employeeIdToUpdate = $conn->real_escape_string($_POST['id']);

    $updateQuery = "UPDATE employee SET E_ESTADO = 0 WHERE EMPLOYEE_ID = $employeeIdToUpdate";

    if ($conn->query($updateQuery) === TRUE) {
        $response['success'] = true;
        $response['message'] = 'Registro actualizado exitosamente';
    } else {
        $response['message'] = 'Error al actualizar el registro: ' . $conn->error;
    }
} else {
    $response['message'] = 'Solicitud inválida';
}

$conn->close();

echo json_encode($response);
?>  
