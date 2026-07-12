<?php
require 'session.php';
require_once '../includes/connection.php';

header('Content-Type: application/json');

if (!logged_in() || atenea_session_is_public_user()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado', 'redirect' => 'login.php']);
    exit;
}

if (!in_array((string) ($_SESSION['TYPE'] ?? ''), ['Admin', 'SuperAdmin'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso restringido', 'redirect' => atenea_dashboard_route_for_session()]);
    exit;
}

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
?>SESSION['TYPE'] ?? ''), ['Admin', 'SuperAdmin'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
    exit;
}
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
