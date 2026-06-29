<?php
include '../includes/connection.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($db, $_GET['id']);
    
    // Obtener imagen del producto
    $query = "SELECT imagen FROM productos WHERE id = '$id'";
    $result = mysqli_query($db, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $imagen = $row['imagen'];
        
        // Eliminación física
        $sql = "DELETE FROM productos WHERE id = '$id'";
        
        if (mysqli_query($db, $sql)) {
            // Eliminar imagen del servidor
            $file_path = "../img/" . $imagen;
            if(file_exists($file_path)) {
                unlink($file_path);
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($db)]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
}

mysqli_close($db);
?>