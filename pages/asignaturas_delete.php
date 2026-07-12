<?php
include '../includes/connection.php';

$response = ['success' => false];

if (isset($_GET['id'])) {
    $asignaturaId = $_GET['id'];
    
    // Realizar la consulta de actualización
    $query = "UPDATE asignaturas SET A_ESTADO = 0 WHERE ASIGNATURA_ID = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $asignaturaId);
    
    if (mysqli_stmt_execute($stmt)) {
        $response['success'] = true;
    }
    
    mysqli_stmt_close($stmt);
}

mysqli_close($db);

// Enviar la respuesta como JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>