<?php
include '../../includes/connection.php';

if (isset($_POST['id']) && !empty($_POST['id'])) {
    $contenidoId = $_POST['id'];

    // Verificar si el contenido existe
    $query = "SELECT material FROM contenidos WHERE contenido_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $contenidoId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $fileName = $row['material'];

        // Actualizar el campo c_estado a 0 en lugar de eliminar el registro
        $updateQuery = "UPDATE contenidos SET c_estado = 0 WHERE contenido_id = ?";
        $stmt = $db->prepare($updateQuery);
        $stmt->bind_param("i", $contenidoId);
        
        if ($stmt->execute()) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'error' => 'Error al actualizar el estado del contenido'));
        }
    } else {
        echo json_encode(array('success' => false, 'error' => 'Contenido no encontrado'));
    }
} else {
    echo json_encode(array('success' => false, 'error' => 'ID de contenido no proporcionado'));
}

$db->close();
?>