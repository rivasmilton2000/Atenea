<?php
include '../../includes/connection.php';

if (isset($_POST['id']) && !empty($_POST['id'])) {
    $evaluacion_id = $_POST['id'];

    // Actualizar el estado de la evaluación en lugar de eliminarla
    $query = "UPDATE evaluaciones SET evaluacion_estado = 0 WHERE evaluacion_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $evaluacion_id);

    if ($stmt->execute()) {
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'error' => 'Error al actualizar el estado de la evaluación'));
    }
    
    $stmt->close();
} else {
    echo json_encode(array('success' => false, 'error' => 'ID de evaluación no proporcionado'));
}

$db->close();
?>