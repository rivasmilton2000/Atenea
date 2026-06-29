<?php
include '../includes/connection.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $employeeId = $_GET['id'];

    // Realizar la consulta para actualizar el campo E_ESTADO
    $query = "UPDATE employee SET E_ESTADO = 0 WHERE EMPLOYEE_ID = '$employeeId'";
    $result = mysqli_query($db, $query);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($db)]);
    }

    mysqli_close($db);
} else {
    echo json_encode(['success' => false, 'error' => 'No se proporcionó un ID válido']);
}
?>