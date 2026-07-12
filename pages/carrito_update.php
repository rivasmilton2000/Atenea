<?php
require 'session.php';
require_once '../includes/atenea_auth.php';
include '../includes/connection.php';

header('Content-Type: application/json');

if (!logged_in()) {
    atenea_login_required_response('carrito.php', 'cart_required', true);
}

if (!isset($_SESSION['cart_session'])) {
    echo json_encode(['success' => false, 'message' => 'Sesion invalida']);
    exit();
}

$session_id = (string) $_SESSION['cart_session'];
$item_id = isset($_POST['item_id']) ? (int) $_POST['item_id'] : 0;
$cambio = isset($_POST['cambio']) ? (int) $_POST['cambio'] : 0;

if ($item_id <= 0 || $cambio === 0) {
    echo json_encode(['success' => false, 'message' => 'Datos invalidos']);
    exit();
}

$stmt = $db->prepare("
    SELECT c.cantidad, p.stock
    FROM carrito c
    JOIN productos p ON c.producto_id = p.id
    WHERE c.id = ? AND c.session_id = ?
    LIMIT 1
");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'No se pudo consultar el carrito']);
    exit();
}

$stmt->bind_param('is', $item_id, $session_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Item no encontrado']);
    exit();
}

$data = $result->fetch_assoc();
$stmt->close();

$cantidad_actual = (int) ($data['cantidad'] ?? 0);
$stock = (int) ($data['stock'] ?? 0);
$nueva_cantidad = $cantidad_actual + $cambio;

if ($nueva_cantidad < 1) {
    echo json_encode([
        'success' => false,
        'message' => 'La cantidad minima es 1',
    ]);
    exit();
}

if ($nueva_cantidad > $stock) {
    echo json_encode([
        'success' => false,
        'message' => 'No hay suficiente stock disponible',
    ]);
    exit();
}

$stmtUpdate = $db->prepare("
    UPDATE carrito
    SET cantidad = ?
    WHERE id = ? AND session_id = ?
");

if (!$stmtUpdate) {
    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el carrito']);
    exit();
}

$stmtUpdate->bind_param('iis', $nueva_cantidad, $item_id, $session_id);

if ($stmtUpdate->execute()) {
    echo json_encode(['success' => true, 'message' => 'Cantidad actualizada']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
}

$stmtUpdate->close();
mysqli_close($db);
