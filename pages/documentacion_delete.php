<?php
session_start();
include '../includes/connection.php';

$response = array();

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $documentoId = mysqli_real_escape_string($db, $_GET['id']);

    // Verificar si el registro de documentación existe
    $checkQuery = "SELECT * FROM archivos WHERE a_id = '$documentoId'";
    $result = mysqli_query($db, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        // Realizar la actualización del estado
        $updateQuery = "UPDATE archivos SET a_estado = 0 WHERE a_id = '$documentoId'";
        if (mysqli_query($db, $updateQuery)) {
            $response['success'] = true;
            $response['message'] = "Registro de documentación desactivado exitosamente.";
        } else {
            $response['success'] = false;
            $response['message'] = "Error al desactivar el registro de documentación: " . mysqli_error($db);
        }
    } else {
        $response['success'] = false;
        $response['message'] = "El registro de documentación no existe.";
    }

    mysqli_close($db);
} else {
    $response['success'] = false;
    $response['message'] = "ID de documentación no proporcionado.";
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>