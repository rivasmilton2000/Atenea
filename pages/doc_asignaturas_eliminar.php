<?php
include '../includes/connection.php';

header('Content-Type: application/json');

$response = array();

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $asignaturaId = mysqli_real_escape_string($db, $_GET['id']);

    // Verificar si la asignatura existe
    $checkQuery = "SELECT * FROM docentes_asignaturas WHERE da_id = '$asignaturaId'";
    $result = mysqli_query($db, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        // Realizar la actualización del estado
        $updateQuery = "UPDATE docentes_asignaturas SET da_estado = 0 WHERE da_id = '$asignaturaId'";
        if (mysqli_query($db, $updateQuery)) {
            $response['success'] = true;
            $response['message'] = "Asignatura desactivada exitosamente.";
        } else {
            $response['success'] = false;
            $response['message'] = "Error al desactivar la asignatura: " . mysqli_error($db);
        }
    } else {
        $response['success'] = false;
        $response['message'] = "La asignatura no existe.";
    }

    mysqli_close($db);
} else {
    $response['success'] = false;
    $response['message'] = "ID de asignatura no proporcionado.";
}

echo json_encode($response);
?>
