<?php
include '../includes/connection.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($db, $_GET['id']);
    
    // Obtener nombre de la imagen antes de eliminar
    $query = "SELECT imagen FROM noticias WHERE id = '$id'";
    $result = mysqli_query($db, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $imagen = $row['imagen'];
        
        // Opción 1: Eliminación lógica (cambiar estado a 0)
        // $sql = "UPDATE noticias SET estado = 0 WHERE id = '$id'";
        
        // Opción 2: Eliminación física
        $sql = "DELETE FROM noticias WHERE id = '$id'";
        
        if (mysqli_query($db, $sql)) {
            // Eliminar archivo de imagen del servidor
            $file_path = "../img/" . $imagen;
            if(file_exists($file_path)) {
                unlink($file_path);
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($db)]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Noticia no encontrada']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
}

mysqli_close($db);
?>