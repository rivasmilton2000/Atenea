<?php
require 'session.php';
require_once '../includes/atenea_auth.php';
include '../includes/connection.php';

header('Content-Type: application/json');

if (!logged_in()) {
    atenea_login_required_response('productos.php', 'login_required', true);
}

// Crear session del carrito si no existe
if (!isset($_SESSION['cart_session'])) {
    $_SESSION['cart_session'] = uniqid('cart_', true);
}

$session_id  = $_SESSION['cart_session'];
$producto_id = isset($_POST['producto_id']) ? (int) $_POST['producto_id'] : 0;

if ($producto_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de producto inválido'
    ]);
    exit();
}

/* =========================
   VERIFICAR PRODUCTO
========================= */
$stmt_producto = $db->prepare("
    SELECT id, stock 
    FROM productos 
    WHERE id = ? AND estado = 1
");
$stmt_producto->bind_param("i", $producto_id);
$stmt_producto->execute();
$result_producto = $stmt_producto->get_result();

if ($result_producto->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Producto no encontrado'
    ]);
    exit();
}

$producto = $result_producto->fetch_assoc();

if ($producto['stock'] <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Producto sin stock'
    ]);
    exit();
}

/* =========================
   VERIFICAR SI YA ESTÁ EN CARRITO
========================= */
$stmt_check = $db->prepare("
    SELECT id, cantidad 
    FROM carrito 
    WHERE session_id = ? AND producto_id = ?
");
$stmt_check->bind_param("si", $session_id, $producto_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {

    // Ya existe → aumentar cantidad

    $row = $result_check->fetch_assoc();
    $cantidad_actual = (int) $row['cantidad'];

    if ($cantidad_actual + 1 > (int) $producto['stock']) {
        echo json_encode([
            'success' => false,
            'message' => 'No hay suficiente stock disponible'
        ]);
        exit();
    }
        
    $stmt_update = $db->prepare("
        UPDATE carrito 
        SET cantidad = cantidad + 1 
        WHERE session_id = ? AND producto_id = ?
    ");
    $stmt_update->bind_param("si", $session_id, $producto_id);

    if ($stmt_update->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Cantidad actualizada en el carrito'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar cantidad'
        ]);
    }

} else {

    // No existe → insertar nuevo
    $stmt_insert = $db->prepare("
        INSERT INTO carrito (session_id, producto_id, cantidad)
        VALUES (?, ?, 1)
    ");
    $stmt_insert->bind_param("si", $session_id, $producto_id);

    if ($stmt_insert->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Producto agregado al carrito'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al agregar producto'
        ]);
    }
}

$db->close();
?>
