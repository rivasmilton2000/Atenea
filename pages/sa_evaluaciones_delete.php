<?php
include '../includes/connection.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => ''];

if (isset($_GET['id'])) {
    $evaluacionId = intval($_GET['id']);

    // Preparar la consulta de eliminación
    $query = "DELETE FROM evaluaciones WHERE evaluacion_id = ?";
    
    // Preparar la declaración
    $stmt = mysqli_prepare($db, $query);

    if ($stmt) {
        // Vincular el parámetro
        mysqli_stmt_bind_param($stmt, "i", $evaluacionId);

        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            // Éxito en la eliminación
            $response['status'] = 'success';
            $response['message'] = 'La evaluación ha sido eliminada correctamente.';
        } else {
            // Error en la eliminación
            $response['message'] = "Error al eliminar la evaluación: " . mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
    } else {
        $response['message'] = "Error al preparar la consulta: " . mysqli_error($db);
    }

    mysqli_close($db);
} else {
    $response['message'] = "No se proporcionó un ID válido.";
}

echo json_encode($response);