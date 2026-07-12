<?php
include('../includes/connection.php');

$response = ['success' => false, 'message' => ''];

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($db, $_GET['id']);

    // Obtener la información de la imagen antes de eliminar el registro
    $query = "SELECT vehicle_image FROM vehicles WHERE id = '$id'";
    $result = mysqli_query($db, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $image_path = $row['vehicle_image'];

        // Eliminar el registro de la base de datos
        $delete_query = "DELETE FROM vehicles WHERE id = '$id'";
        $delete_result = mysqli_query($db, $delete_query);

        if ($delete_result) {
            // Si el registro se eliminó correctamente, eliminar la imagen si existe
            if (!empty($image_path) && file_exists($image_path)) {
                unlink($image_path);
            }

            $response['success'] = true;
            $response['message'] = 'Vehículo eliminado correctamente.';
        } else {
            $response['message'] = 'Error al eliminar el vehículo: ' . mysqli_error($db);
        }
    } else {
        $response['message'] = 'No se encontró el vehículo especificado.';
    }
} else {
    $response['message'] = 'No se proporcionó un ID de vehículo válido.';
}

// Cerrar la conexión a la base de datos
mysqli_close($db);

// Enviar la respuesta como JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
