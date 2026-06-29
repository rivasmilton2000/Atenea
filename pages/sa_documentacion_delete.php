<?php
header('Content-Type: application/json');
session_start();
include '../includes/connection.php';

$response = array('status' => 'error', 'message' => '');

// Verificar si se proporciona un ID válido
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $documentoId = mysqli_real_escape_string($db, $_GET['id']);

    // Verificar si el registro de documentación existe y obtener el nombre del archivo
    $checkQuery = "SELECT archivo FROM archivos WHERE a_id = '$documentoId'";
    $result = mysqli_query($db, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $archivo = $row['archivo'];
        $filePath = 'archivos_documentacion/' . $archivo;

        // Eliminar el archivo del sistema de archivos
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                // Eliminar el registro de la base de datos
                $deleteQuery = "DELETE FROM archivos WHERE a_id = '$documentoId'";
                if (mysqli_query($db, $deleteQuery)) {
                    $response['status'] = 'success';
                    $response['message'] = 'Registro de documentación y archivo eliminados exitosamente.';
                } else {
                    $response['message'] = 'Error al eliminar el registro de documentación: ' . mysqli_error($db);
                }
            } else {
                $response['message'] = 'Error al eliminar el archivo.';
            }
        } else {
            $response['message'] = 'El archivo no existe.';
        }
    } else {
        $response['message'] = 'El registro de documentación no existe.';
    }

    mysqli_close($db);
} else {
    $response['message'] = 'ID de documentación no proporcionado.';
}

// Enviar respuesta JSON
echo json_encode($response);
exit();
?>
