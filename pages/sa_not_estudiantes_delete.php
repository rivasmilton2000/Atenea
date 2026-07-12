<?php
include('../includes/connection.php');

// Establecer el tipo de contenido para JSON
header('Content-Type: application/json');

// Verificar si se ha proporcionado un ID
if (isset($_GET['id'])) {
    $nota_id = intval($_GET['id']);

    // Preparar la consulta SQL para eliminar la nota
    $query = "DELETE FROM notas WHERE nota_id = ?";

    // Preparar la declaración
    $stmt = $db->prepare($query);

    // Vincular el parámetro
    $stmt->bind_param("i", $nota_id);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        // Si la eliminación fue exitosa
        echo json_encode(['status' => 'success', 'message' => 'Nota eliminada con éxito.']);
    } else {
        // Si hubo un error en la eliminación
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar la nota: ' . $stmt->error]);
    }

    // Cerrar la declaración
    $stmt->close();
} else {
    // Si no se proporcionó un ID
    echo json_encode(['status' => 'error', 'message' => 'ID de nota no especificado.']);
}

// Cerrar la conexión
$db->close();
?>
