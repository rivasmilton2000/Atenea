<?php
session_start();
include '../includes/connection.php';

header('Content-Type: application/json');

$response = array();

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $gradoId = mysqli_real_escape_string($db, $_GET['id']);

    // Verificar si el grado existe
    $checkQuery = "SELECT * FROM grados WHERE G_ID = '$gradoId'";
    $result = mysqli_query($db, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        // Realizar la actualización del estado
        $updateQuery = "UPDATE grados SET G_ESTADO = 0 WHERE G_ID = '$gradoId'";
        if (mysqli_query($db, $updateQuery)) {
            $response['success'] = true;
            $response['message'] = "Grado desactivado exitosamente.";
        } else {
            $response['success'] = false;
            $response['message'] = "Error al desactivar el grado: " . mysqli_error($db);
        }
    } else {
        $response['success'] = false;
        $response['message'] = "El grado no existe.";
    }

    mysqli_close($db);
} else {
    $response['success'] = false;
    $response['message'] = "ID de grado no proporcionado.";
}

echo json_encode($response);
?>
