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
?>SESSION['TYPE'] ?? ''), ['Admin', 'SuperAdmin'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
    exit;
}
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
