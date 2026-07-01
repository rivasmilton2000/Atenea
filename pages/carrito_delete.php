<?php
require 'session.php';
require_once '../includes/atenea_auth.php';
include '../includes/connection.php';

header('Content-Type: application/json');

if (!logged_in()) {
    atenea_login_required_response('carrito.php', 'cart_required', true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo invalido']);
    exit();
}

if (!isset($_SESSION['cart_session'])) {
    echo json_encode(['success' => false, 'message' => 'Sesion invalida']);
    exit();
}

$session_id = (string) $_SESSION['cart_session'];
$item_id = isset($_POST['item_id']) ? (int) $_POST['item_id'] : 0;

if ($item_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID invalido']);
    exit();
}

$stmt = $db->prepare('DELETE FROM carrito WHERE id = ? AND session_id = ?');

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el carrito']);
    exit();
}

$stmt->bind_param('is', $item_id, $session_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
}

$stmt->close();
mysqli_close($db);
