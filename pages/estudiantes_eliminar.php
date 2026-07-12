<?php
include '../includes/connection.php';

$response = array('success' => false);

if (isset($_GET['id'])) {
    $estudianteId = $_GET['id'];

    // Actualizar el campo estado_estudiante a 0
    $query = "UPDATE estudiantes SET estado_estudiante = 0 WHERE ESTUDIANTE_ID = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $estudianteId);

    if (mysqli_stmt_execute($stmt)) {
        $response['success'] = true;
    }

    mysqli_stmt_close($stmt);
    mysqli_close($db);
}

header('Content-Type: application/json');
echo json_encode($response);
?>
