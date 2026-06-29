<?php
session_start();
include '../includes/connection.php';
include '../includes/stripe_config.php';
include '../includes/invoice_mailer.php';

$checkout_session_id = trim($_GET['session_id'] ?? '');
if ($checkout_session_id === '') {
    header('Location: carrito.php?checkout_error=' . urlencode('Sesion de pago no valida.'));
    exit();
}

$ch = curl_init('https://api.stripe.com/v1/checkout/sessions/' . rawurlencode($checkout_session_id));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . STRIPE_SECRET_KEY,
]);

$stripe_response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$session_data = json_decode((string)$stripe_response, true);

if ($http_status < 200 || $http_status >= 300 || empty($session_data['id'])) {
    header('Location: carrito.php?checkout_error=' . urlencode('No se pudo verificar el pago con Stripe.'));
    exit();
}

if (($session_data['payment_status'] ?? '') !== 'paid') {
    header('Location: carrito.php?checkout_error=' . urlencode('El pago aun no esta confirmado.'));
    exit();
}

$order_id = (int)($session_data['metadata']['order_id'] ?? 0);
if ($order_id <= 0) {
    header('Location: carrito.php?checkout_error=' . urlencode('No se encontro la orden asociada al pago.'));
    exit();
}

$payment_intent = trim($session_data['payment_intent'] ?? '');
$invoice_notice = '';
$invoice_download_url = '';

mysqli_begin_transaction($db);

try {
    $stmt_order = $db->prepare("
        SELECT id, session_id, estado, total_amount, billing_name, billing_email
        FROM ordenes
        WHERE id = ? AND stripe_session_id = ?
        LIMIT 1
    ");
    $stmt_order->bind_param('is', $order_id, $checkout_session_id);
    $stmt_order->execute();
    $order_result = $stmt_order->get_result();
    $order = $order_result->fetch_assoc();

    if (!$order) {
        throw new Exception('Orden no encontrada para esta sesion de pago.');
    }

    if ($order['estado'] !== 'paid') {
        $stmt_items = $db->prepare("
            SELECT producto_id, cantidad
            FROM orden_detalles
            WHERE orden_id = ?
        ");
        $stmt_items->bind_param('i', $order_id);
        $stmt_items->execute();
        $items_result = $stmt_items->get_result();

        while ($item = $items_result->fetch_assoc()) {
            $producto_id = (int)$item['producto_id'];
            $cantidad = (int)$item['cantidad'];

            $stmt_stock = $db->prepare("
                UPDATE productos
                SET stock = stock - ?
                WHERE id = ? AND stock >= ?
            ");
            $stmt_stock->bind_param('iii', $cantidad, $producto_id, $cantidad);
            $stmt_stock->execute();

            if ($stmt_stock->affected_rows === 0) {
                throw new Exception('Stock insuficiente al confirmar el pago.');
            }
        }

        $stmt_paid = $db->prepare("
            UPDATE ordenes
            SET estado = 'paid', stripe_payment_intent = ?, paid_at = NOW()
            WHERE id = ?
        ");
        $stmt_paid->bind_param('si', $payment_intent, $order_id);
        $stmt_paid->execute();

        $stmt_clear = $db->prepare("DELETE FROM carrito WHERE session_id = ?");
        $stmt_clear->bind_param('s', $order['session_id']);
        $stmt_clear->execute();
    }

    mysqli_commit($db);
} catch (Throwable $e) {
    mysqli_rollback($db);
    header('Location: carrito.php?checkout_error=' . urlencode($e->getMessage()));
    exit();
}

try {
    $stmt_invoice = $db->prepare("
        SELECT id, email_status, pdf_path
        FROM orden_facturas
        WHERE orden_id = ?
        LIMIT 1
    ");
    $stmt_invoice->bind_param('i', $order_id);
    $stmt_invoice->execute();
    $invoice_result = $stmt_invoice->get_result();
    $invoice_row = $invoice_result->fetch_assoc();

    if (!$invoice_row || $invoice_row['email_status'] !== 'sent') {
        $stmt_order_full = $db->prepare("
            SELECT id, billing_name, billing_email, billing_address, subtotal, shipping_amount, total_amount
            FROM ordenes
            WHERE id = ?
            LIMIT 1
        ");
        $stmt_order_full->bind_param('i', $order_id);
        $stmt_order_full->execute();
        $order_full_result = $stmt_order_full->get_result();
        $order_full = $order_full_result->fetch_assoc();

        if (!$order_full) {
            throw new Exception('No se encontro la orden para generar factura.');
        }

        $stmt_items = $db->prepare("
            SELECT producto_nombre, cantidad, precio_unitario, subtotal
            FROM orden_detalles
            WHERE orden_id = ?
            ORDER BY id ASC
        ");
        $stmt_items->bind_param('i', $order_id);
        $stmt_items->execute();
        $items_result = $stmt_items->get_result();
        $items = [];
        while ($item_row = $items_result->fetch_assoc()) {
            $items[] = $item_row;
        }

        $pdf_data = atenea_build_invoice_pdf($order_full, $items);
        atenea_send_invoice_email($order_full, $pdf_data['absolute_path']);

        $status_sent = 'sent';
        $error_message = null;
        $pdf_relative_path = $pdf_data['relative_path'];
        $stmt_log = $db->prepare("
            INSERT INTO orden_facturas (orden_id, billing_email, pdf_path, email_status, error_message, sent_at)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                billing_email = VALUES(billing_email),
                pdf_path = VALUES(pdf_path),
                email_status = VALUES(email_status),
                error_message = VALUES(error_message),
                sent_at = VALUES(sent_at)
        ");
        $stmt_log->bind_param('issss', $order_id, $order_full['billing_email'], $pdf_relative_path, $status_sent, $error_message);
        $stmt_log->execute();

        $invoice_notice = 'La factura fue enviada a tu correo.';
        $invoice_download_url = '../' . ltrim($pdf_relative_path, '/');
    } else {
        $invoice_notice = 'La factura ya habia sido enviada anteriormente.';
        if (!empty($invoice_row['pdf_path'])) {
            $invoice_download_url = '../' . ltrim($invoice_row['pdf_path'], '/');
        }
    }
} catch (Throwable $e) {
    $status_failed = 'failed';
    $pdf_relative_path = '';
    $error_message = $e->getMessage();
    $billing_email_log = $order['billing_email'] ?? '';

    $stmt_fail = $db->prepare("
        INSERT INTO orden_facturas (orden_id, billing_email, pdf_path, email_status, error_message, sent_at)
        VALUES (?, ?, ?, ?, ?, NULL)
        ON DUPLICATE KEY UPDATE
            billing_email = VALUES(billing_email),
            pdf_path = VALUES(pdf_path),
            email_status = VALUES(email_status),
            error_message = VALUES(error_message),
            sent_at = VALUES(sent_at)
    ");
    $stmt_fail->bind_param('issss', $order_id, $billing_email_log, $pdf_relative_path, $status_failed, $error_message);
    $stmt_fail->execute();

    $invoice_notice = 'El pago fue confirmado, pero no se pudo enviar la factura por correo.';
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../includes/head_home.php'; ?>
<body>
  <?php include '../includes/navbar_home.php'; ?>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <h3 class="text-success mb-3">Pago completado</h3>
            <p class="mb-1"><strong>Orden:</strong> #<?php echo (int)$order_id; ?></p>
            <p class="mb-1"><strong>Cliente:</strong> <?php echo htmlspecialchars($order['billing_name']); ?></p>
            <p class="mb-1"><strong>Correo:</strong> <?php echo htmlspecialchars($order['billing_email']); ?></p>
            <p class="mb-3"><strong>Total:</strong> $<?php echo number_format((float)$order['total_amount'], 2); ?></p>
            <div class="alert alert-info py-2"><?php echo htmlspecialchars($invoice_notice); ?></div>
            <?php if ($invoice_download_url !== '') : ?>
              <a href="<?php echo htmlspecialchars($invoice_download_url); ?>" class="btn btn-success mb-3" target="_blank" rel="noopener">
                Descargar Factura PDF
              </a>
            <?php endif; ?>
            <a href="productos.php" class="btn btn-primary">Seguir comprando</a>
            <a href="carrito.php?checkout_ok=<?php echo urlencode('Tu pago fue confirmado correctamente.'); ?>" class="btn btn-outline-primary ml-2">Ver carrito</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php include '../includes/footer_home.php'; ?>
  <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
  <script src="../libs/easing/easing.min.js"></script>
  <script src="../libs/owlcarousel/owl.carousel.min.js"></script>
  <script src="../js/main.js"></script>
</body>
</html>
