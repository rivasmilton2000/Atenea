<?php
include '../includes/connection.php';

$response = array('success' => false, 'message' => '');

if (isset($_GET['id'])) {
    $estudianteId = $_GET['id'];

    // Obtener el nombre de la imagen actual del estudiante antes de eliminar
    $query_select_imagen = "SELECT foto_estudiante FROM estudiantes WHERE ESTUDIANTE_ID = ?";
    $stmt_select_imagen = mysqli_prepare($db, $query_select_imagen);
    mysqli_stmt_bind_param($stmt_select_imagen, "i", $estudianteId);
    mysqli_stmt_execute($stmt_select_imagen);
    mysqli_stmt_bind_result($stmt_select_imagen, $foto_estudiante);
    mysqli_stmt_fetch($stmt_select_imagen);
    mysqli_stmt_close($stmt_select_imagen);

    // Realizar la eliminación del registro de estudiante
    $query_delete = "DELETE FROM estudiantes WHERE ESTUDIANTE_ID = ?";
    $stmt_delete = mysqli_prepare($db, $query_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $estudianteId);
    
    if (mysqli_stmt_execute($stmt_delete)) {
        // Borrar la imagen del estudiante si existe
        if (!empty($foto_estudiante) && file_exists("imagenes_estudiantes/" . $foto_estudiante)) {
            unlink("imagenes_estudiantes/" . $foto_estudiante);
        }
        
        mysqli_stmt_close($stmt_delete);
        mysqli_close($db);

        $response['success'] = true;
        $response['message'] = 'Registro eliminado exitosamente.';
    } else {
        $response['message'] = "Error al eliminar el registro de estudiante: " . mysqli_error($db);
    }
} else {
    $response['message'] = "ID de estudiante no proporcionado.";
}

header('Content-Type: application/json');
echo json_encode($response);
?>
