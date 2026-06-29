<?php
include '../includes/connection.php';

$response = array();

if (isset($_GET['id'])) {
    $vehicleId = $_GET['id'];

    // Realizar la consulta de eliminación
    $query = "DELETE FROM vehicles WHERE id = '$vehicleId'";
    if (mysqli_query($db, $query)) {
        $response['success'] = true;
        $response['message'] = "El registro se ha eliminado correctamente.";
    } else {
        $response['success'] = false;
        $response['message'] = "Hubo un error al eliminar el registro: " . mysqli_error($db);
    }

    mysqli_close($db);
} else {
    $response['success'] = false;
    $response['message'] = "ID de vehículo no válido.";
}

header('Content-Type: application/json');
echo json_encode($response);
?>