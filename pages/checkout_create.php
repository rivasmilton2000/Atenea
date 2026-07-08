<?php
require 'session.php';

include '../includes/connection.php';
include '../includes/stripe_config.php';
require_once '../includes/atenea_auth.php';
require_once '../includes/atenea_catalog.php';
require_once '../includes/dte/DteSchema.php';

if (!function_exists('atenea_checkout_redirect_error')) {
    function atenea_checkout_redirect_error(string $message, array $formData = []): void
    {
        $_SESSION['checkout_form'] = $formData;
        header('Location: carrito.php?checkout_error=' . urlencode($message));
        exit();
    }
}

if (!function_exists('atenea_checkout_clean_text')) {
    function atenea_checkout_clean_text(string $value, int $maxLength): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');

        return function_exists('mb_substr')
            ? mb_substr($value, 0, $maxLength, 'UTF-8')
            : substr($value, 0, $maxLength);
    }
}

if (!function_exists('atenea_checkout_clean_phone')) {
    function atenea_checkout_clean_phone(string $value): string
    {
        $value = preg_replace('/[^0-9+\-\s()]/', '', trim($value)) ?? '';
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return atenea_checkout_clean_text($value, 20);
    }
}

if (!function_exists('atenea_checkout_clean_nrc')) {
    function atenea_checkout_clean_nrc(string $value): string
    {
        $value = strtoupper(trim($value));
        $value = preg_replace('/[^A-Z0-9-]/', '', $value) ?? '';

        return atenea_checkout_clean_text($value, 20);
    }
}

if (!function_exists('atenea_checkout_normalize_document')) {
    function atenea_checkout_normalize_document(string $documentType, string $documentNumber): ?string
    {
        $documentType = strtoupper(trim($documentType));
        $digits = preg_replace('/\D+/', '', $documentNumber) ?? '';

        if ($documentType === 'DUI') {
            if (strlen($digits) !== 9) {
                return null;
            }

            return substr($digits, 0, 8) . '-' . substr($digits, 8, 1);
        }

        if ($documentType === 'NIT') {
            if (strlen($digits) !== 14) {
                return null;
            }

            return substr($digits, 0, 4)
                . '-' . substr($digits, 4, 6)
                . '-' . substr($digits, 10, 3)
                . '-' . substr($digits, 13, 1);
        }

        return null;
    }
}

if (!logged_in()) {
    atenea_login_required_response('carrito.php', 'checkout_required');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: carrito.php');
    exit();
}

if (!isset($_SESSION['cart_session'])) {
    header('Location: carrito.php?checkout_error=' . urlencode('Sesion de carrito invalida.'));
    exit();
}

$formData = [
    'billing_name' => trim((string) ($_POST['billing_name'] ?? '')),
    'billing_email' => trim((string) ($_POST['billing_email'] ?? '')),
    'billing_phone' => trim((string) ($_POST['billing_phone'] ?? '')),
    'billing_tipo_documento' => strtoupper(trim((string) ($_POST['billing_tipo_documento'] ?? ''))),
    'billing_numero_documento' => trim((string) ($_POST['billing_numero_documento'] ?? '')),
    'billing_departamento' => trim((string) ($_POST['billing_departamento'] ?? '')),
    'billing_municipio' => trim((string) ($_POST['billing_municipio'] ?? '')),
    'billing_distrito' => trim((string) ($_POST['billing_distrito'] ?? '')),
    'billing_address' => trim((string) ($_POST['billing_address'] ?? '')),
    'billing_has_nrc' => !empty($_POST['billing_has_nrc']) ? '1' : '',
    'billing_nrc' => trim((string) ($_POST['billing_nrc'] ?? '')),
];

try {
    DteSchema::ensureOrderBillingColumns($db);
} catch (Throwable $exception) {
    atenea_checkout_redirect_error('No se pudo preparar la facturacion DTE para esta compra.', $formData);
}

$sessionId = (string) $_SESSION['cart_session'];
$billingValidation = atenea_validate_billing_profile_input($formData, [
    'require_name' => true,
    'require_email' => true,
]);

if ($billingValidation['errors'] !== []) {
    atenea_checkout_redirect_error((string) $billingValidation['errors'][0], $formData);
}

$billingData = (array) ($billingValidation['data'] ?? []);
$billingName = atenea_billing_clean_text($formData['billing_name'], 120);
$billingEmail = strtolower(trim((string) ($billingData['billing_email'] ?? '')));
$billingPhone = (string) ($billingData['phone_number'] ?? '');
$billingDocumentType = (string) ($billingData['tipo_documento'] ?? '');
$billingDocumentNumber = (string) ($billingData['numero_documento'] ?? '');
$billingDepartment = (string) ($billingData['billing_departamento'] ?? '');
$billingMunicipality = (string) ($billingData['billing_municipio'] ?? '');
$billingDistrict = (string) ($billingData['billing_distrito'] ?? '');
$billingAddress = (string) ($billingData['billing_direccion'] ?? '');
$billingNrc = (string) ($billingData['billing_nrc'] ?? '');

if (strpos(STRIPE_SECRET_KEY, 'sk_test_REEMPLAZA_AQUI') === 0) {
    atenea_checkout_redirect_error('Debes configurar STRIPE_SECRET_KEY en includes/stripe_config.php.', $formData);
}

$stmt = $db->prepare("
    SELECT c.producto_id, c.cantidad, p.nombre, p.precio, p.precio_descuento, p.stock,
           " . atenea_catalog_product_select_sql($db, 'p') . "
    FROM carrito c
    JOIN productos p ON c.producto_id = p.id
    WHERE c.session_id = ?
");

if (!$stmt) {
    atenea_checkout_redirect_error('No se pudo leer el carrito actual.', $formData);
}

$stmt->bind_param('s', $sessionId);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
$subtotal = 0.0;

while ($row = $result->fetch_assoc()) {
    $price = !empty($row['precio_descuento']) ? (float) $row['precio_descuento'] : (float) $row['precio'];
    $quantity = (int) $row['cantidad'];
    $stock = (int) $row['stock'];

    if ($quantity < 1 || $quantity > $stock) {
        $stmt->close();
        atenea_checkout_redirect_error('Hay productos sin stock suficiente. Ajusta tu carrito.', $formData);
    }

    $lineSubtotal = $price * $quantity;
    $subtotal += $lineSubtotal;

    $cartItems[] = [
        'producto_id' => (int) $row['producto_id'],
        'nombre' => (string) $row['nombre'],
        'tipo_oferta' => (string) ($row['tipo_oferta'] ?? 'producto'),
        'precio' => $price,
        'cantidad' => $quantity,
        'subtotal' => $lineSubtotal,
    ];
}

$stmt->close();

if ($cartItems === []) {
    atenea_checkout_redirect_error('Tu carrito esta vacio.', $formData);
}

$shippingAmount = atenea_catalog_cart_requires_shipping($cartItems) ? 5.00 : 0.00;
$total = $subtotal + $shippingAmount;

mysqli_begin_transaction($db);

try {
    $stmtOrder = $db->prepare("
        INSERT INTO ordenes (
            session_id,
            billing_name,
            billing_email,
            billing_address,
            billing_tipo_documento,
            billing_numero_documento,
            billing_telefono,
            billing_departamento,
            billing_municipio,
            billing_distrito,
            billing_nrc,
            subtotal,
            shipping_amount,
            total_amount,
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending_payment')
    ");

    if (!$stmtOrder) {
        throw new Exception('No se pudo crear la orden.');
    }

    $billingNrcForDb = $billingNrc !== '' ? $billingNrc : null;
    $stmtOrder->bind_param(
        'sssssssssssddd',
        $sessionId,
        $billingName,
        $billingEmail,
        $billingAddress,
        $billingDocumentType,
        $billingDocumentNumber,
        $billingPhone,
        $billingDepartment,
        $billingMunicipality,
        $billingDistrict,
        $billingNrcForDb,
        $subtotal,
        $shippingAmount,
        $total
    );

    if (!$stmtOrder->execute()) {
        throw new Exception('No se pudo crear la orden.');
    }

    $orderId = (int) $db->insert_id;
    $stmtOrder->close();

    $stmtDetail = $db->prepare("
        INSERT INTO orden_detalles (orden_id, producto_id, producto_nombre, precio_unitario, cantidad, subtotal)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmtDetail) {
        throw new Exception('No se pudo preparar el detalle de la orden.');
    }

    foreach ($cartItems as $item) {
        $stmtDetail->bind_param(
            'iisdid',
            $orderId,
            $item['producto_id'],
            $item['nombre'],
            $item['precio'],
            $item['cantidad'],
            $item['subtotal']
        );

        if (!$stmtDetail->execute()) {
            throw new Exception('No se pudo guardar el detalle de la orden.');
        }
    }

    $stmtDetail->close();

    $payload = [
        'mode' => 'payment',
        'success_url' => APP_BASE_URL . '/pages/checkout_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => APP_BASE_URL . '/pages/carrito.php?checkout_cancelled=1',
        'billing_address_collection' => 'required',
        'customer_email' => $billingEmail,
        'metadata[order_id]' => (string) $orderId,
        'metadata[cart_session]' => $sessionId,
        'payment_method_types[0]' => 'card',
    ];

    $idx = 0;
    foreach ($cartItems as $item) {
        $payload['line_items[' . $idx . '][price_data][currency]'] = 'usd';
        $payload['line_items[' . $idx . '][price_data][product_data][name]'] = $item['nombre'];
        $payload['line_items[' . $idx . '][price_data][unit_amount]'] = (int) round($item['precio'] * 100);
        $payload['line_items[' . $idx . '][quantity]'] = $item['cantidad'];
        $idx++;
    }

    if ($shippingAmount > 0) {
        $payload['line_items[' . $idx . '][price_data][currency]'] = 'usd';
        $payload['line_items[' . $idx . '][price_data][product_data][name]'] = 'Envio';
        $payload['line_items[' . $idx . '][price_data][unit_amount]'] = (int) round($shippingAmount * 100);
        $payload['line_items[' . $idx . '][quantity]'] = 1;
    }

    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . STRIPE_SECRET_KEY,
        'Content-Type: application/x-www-form-urlencoded',
    ]);

    $stripeResponse = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $sessionData = json_decode((string) $stripeResponse, true);

    if ($httpStatus < 200 || $httpStatus >= 300 || empty($sessionData['id']) || empty($sessionData['url'])) {
        throw new Exception('Stripe no pudo iniciar el pago.');
    }

    $stripeSessionId = (string) $sessionData['id'];

    $stmtUpdate = $db->prepare('UPDATE ordenes SET stripe_session_id = ? WHERE id = ?');
    if (!$stmtUpdate) {
        throw new Exception('No se pudo guardar la sesion de Stripe.');
    }

    $stmtUpdate->bind_param('si', $stripeSessionId, $orderId);
    if (!$stmtUpdate->execute()) {
        throw new Exception('No se pudo guardar la sesion de Stripe.');
    }
    $stmtUpdate->close();

    mysqli_commit($db);
    unset($_SESSION['checkout_form']);

    header('Location: ' . $sessionData['url']);
    exit();
} catch (Throwable $exception) {
    mysqli_rollback($db);
    atenea_checkout_redirect_error($exception->getMessage(), $formData);
}
