<?php
include '../includes/connection.php';

$response = array();

if (isset($_GET['id'])) {
    $disasignaturaId = mysqli_real_escape_string($db, $_GET['id']);

    // Realizar la consulta de actualización en lugar de eliminación
    $query = "UPDATE estudiantes_docentes SET ed_estado = 0 WHERE ed_id = '$disasignaturaId'";
    $result = mysqli_query($db, $query);

    if ($result) {
        // Si la actualización fue exitosa
        $response['success'] = true;
        $response['message'] = "El estudiante ha sido desactivado de la asignatura exitosamente.";
    } else {
        // Si hubo un error en la actualización
        $response['success'] = false;
        $response['message'] = "Error al eliminar el estudiante de la asignatura: " . mysqli_error($db);
    }
} else {
    // Si no se proporciona un ID válido
    $response['success'] = false;
    $response['message'] = "ID de asignatura no proporcionado.";
}

mysqli_close($db);

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>