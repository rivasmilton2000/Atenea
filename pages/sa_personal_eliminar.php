<?php
include '../includes/connection.php';

header('Content-Type: application/json');

if (isset($_POST['id'])) {
    $employeeId = $_POST['id'];

    // Realizar la consulta de eliminación
    $query = "DELETE FROM employee WHERE EMPLOYEE_ID = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $employeeId);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($db)]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($db);
} else {
    echo json_encode(['success' => false, 'error' => 'No se proporcionó un ID válido']);
}
?>