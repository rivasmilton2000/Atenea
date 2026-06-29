<?php
session_start();
include '../includes/connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['cart_session'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión inválida']);
    exit();
}

$session_id = $_SESSION['cart_session'];
$item_id = isset($_POST['item_id']) ? (int) $_POST['item_id'] : 0;
$cambio = isset($_POST['cambio']) ? (int) $_POST['cambio'] : 0;

if (!$item_id || $cambio === 0) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}

/* ============================
   OBTENER CANTIDAD + STOCK
============================ */
$sql = "
    SELECT c.cantidad, p.stock
    FROM carrito c
    JOIN productos p ON c.producto_id = p.id
    WHERE c.id = '$item_id' AND c.session_id = '$session_id'
";

$result = mysqli_query($db, $sql);

if (mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Item no encontrado']);
    exit();
}

$data = mysqli_fetch_assoc($result);

$cantidad_actual = (int) $data['cantidad'];
$stock = (int) $data['stock'];

$nueva_cantidad = $cantidad_actual + $cambio;

/* ============================
   VALIDACIONES
============================ */

//  No permitir menos de 1
if ($nueva_cantidad < 1) {
    echo json_encode([
        'success' => false,
        'message' => 'La cantidad mínima es 1'
    ]);
    exit();
}

//  No permitir pasar el stock
if ($nueva_cantidad > $stock) {
    echo json_encode([
        'success' => false,
        'message' => 'No hay suficiente stock disponible'
    ]);
    exit();
}

/* ============================
   ACTUALIZAR CANTIDAD
============================ */
$sql_update = "
    UPDATE carrito 
    SET cantidad = '$nueva_cantidad'
    WHERE id = '$item_id' AND session_id = '$session_id'
";

if (mysqli_query($db, $sql_update)) {
    echo json_encode(['success' => true, 'message' => 'Cantidad actualizada']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
}

mysqli_close($db);
