<?php
session_start();
include '../includes/connection.php';

$response = array('status' => 'error', 'message' => '');

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $contenidoId = mysqli_real_escape_string($db, $_GET['id']);

    // Verificar si el registro de contenido existe y obtener el nombre del archivo
    $checkQuery = "SELECT material FROM contenidos WHERE contenido_id = '$contenidoId'";
    $result = mysqli_query($db, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $archivo = $row['material'];
        $filePath = 'archivos_contenidos/' . $archivo;

        // Eliminar el archivo del sistema de archivos
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                // Eliminar el registro de la base de datos
                $deleteQuery = "DELETE FROM contenidos WHERE contenido_id = '$contenidoId'";
                if (mysqli_query($db, $deleteQuery)) {
                    $response['status'] = 'success';
                    $response['message'] = 'Registro de contenido y archivo eliminados exitosamente.';
                } else {
                    $response['message'] = 'Error al eliminar el registro de contenido: ' . mysqli_error($db);
                }
            } else {
                $response['message'] = 'Error al eliminar el archivo.';
            }
        } else {
            $response['message'] = 'El archivo no existe.';
        }
    } else {
        $response['message'] = 'El registro de contenido no existe.';
    }

    mysqli_close($db);
} else {
    $response['message'] = 'ID de contenido no proporcionado.';
}

echo json_encode($response);
?>
