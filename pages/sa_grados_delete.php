<?php
include('../includes/connection.php');

$response = ['success' => false, 'message' => ''];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $gradoId = mysqli_real_escape_string($db, $_GET['id']);

    // Verificar si el grado existe
    $checkQuery = "SELECT * FROM grados WHERE G_ID = '$gradoId'";
    $result = mysqli_query($db, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        // Ejecutar la consulta de eliminación
        $deleteQuery = "DELETE FROM grados WHERE G_ID = '$gradoId'";
        if (mysqli_query($db, $deleteQuery)) {
            $response['success'] = true;
            $response['message'] = 'Grado eliminado exitosamente.';
        } else {
            $response['message'] = 'Error al eliminar el grado: ' . mysqli_error($db);
        }
    } else {
        $response['message'] = 'El grado no existe.';
    }
} else {
    $response['message'] = 'ID de grado no proporcionado.';
}

// Cerrar la conexión a la base de datos
mysqli_close($db);

// Enviar la respuesta como JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
