<?php
session_start();
include '../includes/connection.php';

$response = array();

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $inventarioId = mysqli_real_escape_string($db, $_GET['id']);

    // Verificar si el registro de inventario existe
    $checkQuery = "SELECT * FROM inventario WHERE i_id = '$inventarioId'";
    $result = mysqli_query($db, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        // Realizar la actualización del estado
        $updateQuery = "UPDATE inventario SET i_estado = 0 WHERE i_id = '$inventarioId'";
        if (mysqli_query($db, $updateQuery)) {
            $response['success'] = true;
            $response['message'] = "Registro de inventario desactivado exitosamente.";
        } else {
            $response['success'] = false;
            $response['message'] = "Error al desactivar el registro de inventario: " . mysqli_error($db);
        }
    } else {
        $response['success'] = false;
        $response['message'] = "El registro de inventario no existe.";
    }

    mysqli_close($db);
} else {
    $response['success'] = false;
    $response['message'] = "ID de inventario no proporcionado.";
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>