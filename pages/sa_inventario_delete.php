<?php
session_start();
include '../includes/connection.php';

$response = array();

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $inventarioId = mysqli_real_escape_string($db, $_GET['id']);

    // Check if the inventory record exists
    $checkQuery = "SELECT * FROM inventario WHERE i_id = '$inventarioId'";
    $result = mysqli_query($db, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        // Perform the deletion query
        $deleteQuery = "DELETE FROM inventario WHERE i_id = '$inventarioId'";
        if (mysqli_query($db, $deleteQuery)) {
            $response['success'] = true;
            $response['message'] = "Registro de inventario eliminado exitosamente.";
        } else {
            $response['success'] = false;
            $response['message'] = "Error al eliminar el registro de inventario: " . mysqli_error($db);
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

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>
