<?php
include '../includes/connection.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($db, $_GET['id']);
    
    // Opción 1: Eliminación lógica (cambiar estado a 0)
    $sql = "UPDATE facilities SET estado = 0 WHERE id = '$id'";
    
    // Opción 2: Eliminación física (descomentar si prefieres borrar completamente)
    // $sql = "DELETE FROM facilities WHERE id = '$id'";
    
    if (mysqli_query($db, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($db)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
}

mysqli_close($db);
?>