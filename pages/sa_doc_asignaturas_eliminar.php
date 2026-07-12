<?php
include '../includes/connection.php';

$response = ['success' => false, 'message' => ''];

if (isset($_GET['id'])) {
    $asignaturaId = $_GET['id'];

    // Preparar la consulta para eliminar el registro
    $query = "DELETE FROM docentes_asignaturas WHERE da_id = ?";
    
    // Preparar la declaración
    $stmt = mysqli_prepare($db, $query);

    if ($stmt) {
        // Vincular el parámetro
        mysqli_stmt_bind_param($stmt, "i", $asignaturaId);

        // Ejecutar la declaración
        if (mysqli_stmt_execute($stmt)) {
            // Éxito
            $response['success'] = true;
            $response['message'] = 'Asignación eliminada exitosamente.';
        } else {
            // Error en la ejecución
            $response['message'] = 'Error al eliminar el registro: ' . mysqli_error($db);
        }

        mysqli_stmt_close($stmt);
    } else {
        // Error en la preparación de la declaración
        $response['message'] = 'Error en la preparación de la consulta: ' . mysqli_error($db);
    }
} else {
    // Si no se proporciona un ID válido
    $response['message'] = 'ID de asignatura no proporcionado.';
}

mysqli_close($db);

// Enviar la respuesta como JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
