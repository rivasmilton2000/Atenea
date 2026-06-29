<?php
session_start();
include '../includes/connection.php';

header('Content-Type: application/json');

$item_id = isset($_GET['id']) ? mysqli_real_escape_string($db, $_GET['id']) : 0;

if (!$item_id) {
    echo json_encode(['success' => false, 'message' => 'ID invalido']);
    exit();
}

$sql = "DELETE FROM carrito WHERE id = '$item_id'";

if (mysqli_query($db, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
}

mysqli_close($db);
?>