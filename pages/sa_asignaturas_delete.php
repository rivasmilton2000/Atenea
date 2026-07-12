<?php
include '../includes/connection.php';

// Verificar si se ha proporcionado un ID
if (isset($_GET['id'])) {
    $asignaturaId = mysqli_real_escape_string($db, $_GET['id']);

    // Primero, verificamos si la asignatura existe
    $checkQuery = "SELECT COUNT(*) as count FROM asignaturas WHERE ASIGNATURA_ID = '$asignaturaId'";
    $checkResult = mysqli_query($db, $checkQuery);
    $row = mysqli_fetch_assoc($checkResult);

    if ($row['count'] > 0) {
        // La asignatura existe, procedemos con la eliminación
        $deleteQuery = "DELETE FROM asignaturas WHERE ASIGNATURA_ID = '$asignaturaId'";
        $deleteResult = mysqli_query($db, $deleteQuery);

        if ($deleteResult) {
            // Eliminación exitosa
            $response = ['success' => true, 'message' => 'Asignatura eliminada correctamente.'];
        } else {
            // Error en la eliminación
            $response = ['success' => false, 'message' => 'Error al eliminar la asignatura: ' . mysqli_error($db)];
        }
    } else {
        // La asignatura no existe
        $response = ['success' => false, 'message' => 'La asignatura no existe.'];
    }
} else {
    // No se proporcionó un ID
    $response = ['success' => false, 'message' => 'ID de asignatura no proporcionado.'];
}

// Cerrar la conexión a la base de datos
mysqli_close($db);

// Enviar la respuesta como JSON
header('Content-Type: application/json');
echo json_encode($response);
?>