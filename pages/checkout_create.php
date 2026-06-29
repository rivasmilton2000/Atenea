<?php
session_start();
include '../includes/connection.php';
include '../includes/stripe_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: carrito.php');
    exit();
}

if (!isset($_SESSION['cart_session'])) {
    header('Location: carrito.php?checkout_error=' . urlencode('Sesion de carrito invalida.'));
    exit();
}

$session_id = $_SESSION['cart_session'];
$billing_name = trim($_POST['billing_name'] ?? '');
$billing_email = trim($_POST['billing_email'] ?? '');
$billing_address = trim($_POST['billing_address'] ?? '');

if ($billing_name === '' || $billing_email === '' || $billing_address === '') {
    header('Location: carrito.php?checkout_error=' . urlencode('Completa todos los datos de facturacion.'));
    exit();
}

if (!filter_var($billing_email, FILTER_VALIDATE_EMAIL)) {
    header('Location: carrito.php?checkout_error=' . urlencode('El correo de facturacion no es valido.'));
    exit();
}

if (strpos(STRIPE_SECRET_KEY, 'sk_test_REEMPLAZA_AQUI') === 0) {
    header('Location: carrito.php?checkout_error=' . urlencode('Debes configurar STRIPE_SECRET_KEY en includes/stripe_config.php.'));
    exit();
}

$stmt = $db->prepare("
    SELECT c.producto_id, c.cantidad, p.nombre, p.precio, p.precio_descuento, p.stock
    FROM carrito c
    JOIN productos p ON c.producto_id = p.id
    WHERE c.session_id = ?
");
$stmt->bind_param('s', $session_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$subtotal = 0.0;

while ($row = $result->fetch_assoc()) {
    $precio = !empty($row['precio_descuento']) ? (float)$row['precio_descuento'] : (float)$row['precio'];
    $cantidad = (int)$row['cantidad'];
    $stock = (int)$row['stock'];

    if ($cantidad < 1 || $cantidad > $stock) {
        header('Location: carrito.php?checkout_error=' . urlencode('Hay productos sin stock suficiente. Ajusta tu carrito.'));
        exit();
    }

    $line_subtotal = $precio * $cantidad;
    $subtotal += $line_subtotal;

    $cart_items[] = [
        'producto_id' => (int)$row['producto_id'],
        'nombre' => $row['nombre'],
        'precio' => $precio,
        'cantidad' => $cantidad,
        'subtotal' => $line_subtotal,
    ];
}

if (count($cart_items) === 0) {
    header('Location: carrito.php?checkout_error=' . urlencode('Tu carrito esta vacio.'));
    exit();
}

$envio = 5.00;
$total = $subtotal + $envio;

mysqli_begin_transaction($db);

try {
    $stmt_order = $db->prepare("
        INSERT INTO ordenes (
            session_id,
            billing_name,
            billing_email,
            billing_address,
            subtotal,
            shipping_amount,
            total_amount,
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending_payment')
    ");
    $stmt_order->bind_param(
        'ssssddd',
        $session_id,
        $billing_name,
        $billing_email,
        $billing_address,
        $subtotal,
        $envio,
        $total
    );

    if (!$stmt_order->execute()) {
        throw new Exception('No se pudo crear la orden.');
    }

    $order_id = (int)$db->insert_id;

    $stmt_detail = $db->prepare("
        INSERT INTO orden_detalles (orden_id, producto_id, producto_nombre, precio_unitario, cantidad, subtotal)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($cart_items as $item) {
        $stmt_detail->bind_param(
            'iisdid',
            $order_id,
            $item['producto_id'],
            $item['nombre'],
            $item['precio'],
            $item['cantidad'],
            $item['subtotal']
        );
        if (!$stmt_detail->execute()) {
            throw new Exception('No se pudo guardar el detalle de la orden.');
        }
    }

    $payload = [
        'mode' => 'payment',
        'success_url' => APP_BASE_URL . '/pages/checkout_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => APP_BASE_URL . '/pages/carrito.php?checkout_cancelled=1',
        'billing_address_collection' => 'required',
        'customer_email' => $billing_email,
        'metadata[order_id]' => (string)$order_id,
        'metadata[cart_session]' => $session_id,
        'payment_method_types[0]' => 'card',
    ];

    $idx = 0;
    foreach ($cart_items as $item) {
        $payload['line_items[' . $idx . '][price_data][currency]'] = 'usd';
        $payload['line_items[' . $idx . '][price_data][product_data][name]'] = $item['nombre'];
        $payload['line_items[' . $idx . '][price_data][unit_amount]'] = (int)round($item['precio'] * 100);
        $payload['line_items[' . $idx . '][quantity]'] = $item['cantidad'];
        $idx++;
    }

    $payload['line_items[' . $idx . '][price_data][currency]'] = 'usd';
    $payload['line_items[' . $idx . '][price_data][product_data][name]'] = 'Envio';
    $payload['line_items[' . $idx . '][price_data][unit_amount]'] = (int)round($envio * 100);
    $payload['line_items[' . $idx . '][quantity]'] = 1;

    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . STRIPE_SECRET_KEY,
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    $stripe_response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $session_data = json_decode((string)$stripe_response, true);

    if ($http_status < 200 || $http_status >= 300 || empty($session_data['id']) || empty($session_data['url'])) {
        throw new Exception('Stripe no pudo iniciar el pago.');
    }

    $stripe_session_id = $session_data['id'];

    $stmt_update = $db->prepare("UPDATE ordenes SET stripe_session_id = ? WHERE id = ?");
    $stmt_update->bind_param('si', $stripe_session_id, $order_id);
    if (!$stmt_update->execute()) {
        throw new Exception('No se pudo guardar la sesion de Stripe.');
    }

    mysqli_commit($db);
    header('Location: ' . $session_data['url']);
    exit();
} catch (Throwable $e) {
    mysqli_rollback($db);
    header('Location: carrito.php?checkout_error=' . urlencode($e->getMessage()));
    exit();
}

