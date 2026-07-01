<?php
session_start();

include '../includes/connection.php';
include '../includes/stripe_config.php';
include '../includes/invoice_mailer.php';
require_once '../includes/dte/bootstrap.php';

if (!function_exists('atenea_checkout_load_order_basic')) {
    function atenea_checkout_load_order_basic(mysqli $db, int $orderId, string $checkoutSessionId): ?array
    {
        $stmt = $db->prepare("
            SELECT id, session_id, estado, total_amount, billing_name, billing_email, stripe_session_id
            FROM ordenes
            WHERE id = ? AND stripe_session_id = ?
            LIMIT 1
        ");
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('is', $orderId, $checkoutSessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        $stmt->close();

        return $order ?: null;
    }
}

if (!function_exists('atenea_checkout_load_order_full')) {
    function atenea_checkout_load_order_full(mysqli $db, int $orderId): ?array
    {
        $stmt = $db->prepare("
            SELECT id, session_id, stripe_session_id, stripe_payment_intent, billing_name, billing_email, billing_address, subtotal, shipping_amount, total_amount, paid_at, created_at
            FROM ordenes
            WHERE id = ?
            LIMIT 1
        ");
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        $stmt->close();

        return $order ?: null;
    }
}

if (!function_exists('atenea_checkout_load_order_items')) {
    function atenea_checkout_load_order_items(mysqli $db, int $orderId): array
    {
        $stmt = $db->prepare("
            SELECT producto_id, producto_nombre, cantidad, precio_unitario, subtotal
            FROM orden_detalles
            WHERE orden_id = ?
            ORDER BY id ASC
        ");
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
            $items[] = $row;
        }
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        $stmt->close();

        return $items;
    }
}

if (!function_exists('atenea_checkout_load_invoice_row')) {
    function atenea_checkout_load_invoice_row(mysqli $db, int $orderId): ?array
    {
        $stmt = $db->prepare("
            SELECT id, billing_email, pdf_path, email_status, error_message, sent_at
            FROM orden_facturas
            WHERE orden_id = ?
            LIMIT 1
        ");
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $invoice = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        $stmt->close();

        return $invoice ?: null;
    }
}

if (!function_exists('atenea_checkout_resolve_legacy_invoice_file')) {
    function atenea_checkout_resolve_legacy_invoice_file(string $relativePath): array
    {
        $relativePath = ltrim(str_replace('\\', '/', trim($relativePath)), '/');
        if ($relativePath === '') {
            return [
                'relative_path' => '',
                'absolute_path' => '',
                'exists' => false,
            ];
        }

        $invoiceRoot = realpath(__DIR__ . '/../uploads/facturas');
        $absolutePath = realpath(__DIR__ . '/../' . $relativePath);
        $isValid = $invoiceRoot !== false
            && $absolutePath !== false
            && strpos($absolutePath, $invoiceRoot) === 0
            && is_file($absolutePath);

        return [
            'relative_path' => $relativePath,
            'absolute_path' => $isValid ? $absolutePath : '',
            'exists' => $isValid,
        ];
    }
}

if (!function_exists('atenea_checkout_stream_file')) {
    function atenea_checkout_stream_file(string $absolutePath, string $contentType, string $downloadName): void
    {
        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            header('Location: checkout_success.php?checkout_error=' . urlencode('El archivo solicitado no esta disponible.') . '&session_id=' . urlencode((string) ($_GET['session_id'] ?? '')));
            exit();
        }

        $fileSize = filesize($absolutePath);
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . ($fileSize !== false ? (string) $fileSize : '0'));
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        header('X-Content-Type-Options: nosniff');
        readfile($absolutePath);
        exit();
    }
}

$checkoutSessionId = trim((string) ($_GET['session_id'] ?? ''));
if ($checkoutSessionId === '') {
    header('Location: carrito.php?checkout_error=' . urlencode('Sesion de pago no valida.'));
    exit();
}

$downloadRequest = trim((string) ($_GET['download'] ?? ''));
$allowedDownloads = ['pdf', 'json'];
$downloadRequest = in_array($downloadRequest, $allowedDownloads, true) ? $downloadRequest : '';

$ch = curl_init('https://api.stripe.com/v1/checkout/sessions/' . rawurlencode($checkoutSessionId));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . STRIPE_SECRET_KEY,
]);

$stripeResponse = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$sessionData = json_decode((string) $stripeResponse, true);

if ($httpStatus < 200 || $httpStatus >= 300 || empty($sessionData['id'])) {
    header('Location: carrito.php?checkout_error=' . urlencode('No se pudo verificar el pago con Stripe.'));
    exit();
}

if (($sessionData['payment_status'] ?? '') !== 'paid') {
    header('Location: carrito.php?checkout_error=' . urlencode('El pago aun no esta confirmado.'));
    exit();
}

$orderId = (int) ($sessionData['metadata']['order_id'] ?? 0);
if ($orderId <= 0) {
    header('Location: carrito.php?checkout_error=' . urlencode('No se encontro la orden asociada al pago.'));
    exit();
}

$paymentIntent = trim((string) ($sessionData['payment_intent'] ?? ''));
$invoiceNotice = '';
$dteStatusNote = '';
$invoiceDownloadUrl = '';
$jsonDownloadUrl = '';
$order = null;

mysqli_begin_transaction($db);

try {
    $order = atenea_checkout_load_order_basic($db, $orderId, $checkoutSessionId);
    if (!$order) {
        throw new Exception('Orden no encontrada para esta sesion de pago.');
    }

    if (($order['estado'] ?? '') !== 'paid') {
        $stmtItems = $db->prepare("
            SELECT producto_id, cantidad
            FROM orden_detalles
            WHERE orden_id = ?
        ");
        $stmtItems->bind_param('i', $orderId);
        $stmtItems->execute();
        $itemsResult = $stmtItems->get_result();

        while ($item = $itemsResult->fetch_assoc()) {
            $productoId = (int) $item['producto_id'];
            $cantidad = (int) $item['cantidad'];

            $stmtStock = $db->prepare("
                UPDATE productos
                SET stock = stock - ?
                WHERE id = ? AND stock >= ?
            ");
            $stmtStock->bind_param('iii', $cantidad, $productoId, $cantidad);
            $stmtStock->execute();

            if ($stmtStock->affected_rows === 0) {
                throw new Exception('Stock insuficiente al confirmar el pago.');
            }
            $stmtStock->close();
        }

        if ($itemsResult instanceof mysqli_result) {
            mysqli_free_result($itemsResult);
        }
        $stmtItems->close();

        $stmtPaid = $db->prepare("
            UPDATE ordenes
            SET estado = 'paid', stripe_payment_intent = ?, paid_at = NOW()
            WHERE id = ?
        ");
        $stmtPaid->bind_param('si', $paymentIntent, $orderId);
        $stmtPaid->execute();
        $stmtPaid->close();

        $stmtClear = $db->prepare("DELETE FROM carrito WHERE session_id = ?");
        $stmtClear->bind_param('s', $order['session_id']);
        $stmtClear->execute();
        $stmtClear->close();
    }

    mysqli_commit($db);
} catch (Throwable $exception) {
    mysqli_rollback($db);
    header('Location: carrito.php?checkout_error=' . urlencode($exception->getMessage()));
    exit();
}

$orderFull = atenea_checkout_load_order_full($db, $orderId);
if (!$orderFull) {
    header('Location: carrito.php?checkout_error=' . urlencode('No se encontro la orden para completar el post-pago.'));
    exit();
}

$items = atenea_checkout_load_order_items($db, $orderId);
$invoiceRow = atenea_checkout_load_invoice_row($db, $orderId);
$dteDocument = DteService::getDocumentByOrderId($db, $orderId);
$legacyInvoiceFile = $invoiceRow ? atenea_checkout_resolve_legacy_invoice_file((string) ($invoiceRow['pdf_path'] ?? '')) : ['exists' => false];

if ($downloadRequest !== '') {
    if ($downloadRequest === 'pdf') {
        if ($dteDocument && !empty($dteDocument['pdf_available'])) {
            atenea_checkout_stream_file((string) $dteDocument['pdf_absolute_path'], 'application/pdf', basename((string) $dteDocument['pdf_absolute_path']));
        }

        if (!empty($legacyInvoiceFile['exists'])) {
            atenea_checkout_stream_file((string) $legacyInvoiceFile['absolute_path'], 'application/pdf', basename((string) $legacyInvoiceFile['absolute_path']));
        }
    }

    if ($downloadRequest === 'json' && $dteDocument && !empty($dteDocument['json_available'])) {
        atenea_checkout_stream_file((string) $dteDocument['json_absolute_path'], 'application/json', basename((string) $dteDocument['json_absolute_path']));
    }

    header('Location: checkout_success.php?session_id=' . urlencode($checkoutSessionId));
    exit();
}

$shouldGenerateDte = !$dteDocument
    || empty($dteDocument['pdf_available'])
    || empty($dteDocument['json_available'])
    || in_array(strtoupper(trim((string) ($dteDocument['estado'] ?? ''))), ['', 'ERROR', 'PENDIENTE'], true);

$shouldAttemptEmail = !$invoiceRow || (string) ($invoiceRow['email_status'] ?? '') !== 'sent';

if ($shouldGenerateDte) {
    try {
        $dteDocument = DteService::generateForOrder($db, $orderId, [
            'user_id' => (int) ($_SESSION['MEMBER_ID'] ?? 0),
        ]);
    } catch (Throwable $dteException) {
        error_log('[DTE] Fallo al generar el documento para la orden #' . $orderId . ': ' . $dteException->getMessage());
        $dteStatusNote = 'El DTE quedo en estado pendiente/error para reintento desde administracion.';
    }
}

if ($dteDocument && !empty($dteDocument['pdf_available'])) {
    $invoiceDownloadUrl = 'checkout_success.php?session_id=' . urlencode($checkoutSessionId) . '&download=pdf';
}

if ($dteDocument && !empty($dteDocument['json_available'])) {
    $jsonDownloadUrl = 'checkout_success.php?session_id=' . urlencode($checkoutSessionId) . '&download=json';
}

if ($dteDocument && !empty($dteDocument['estado'])) {
    $dteStatusNote = 'Estado interno DTE: ' . (string) $dteDocument['estado'];
    if (strtoupper(trim((string) $dteDocument['estado'])) === 'PROCESADO SIMULADO') {
        $dteStatusNote .= ' | Validez fiscal: NO VALIDO FISCALMENTE';
    }
}

if ($shouldAttemptEmail) {
    if ($dteDocument && !empty($dteDocument['pdf_available'])) {
        try {
            $extraAttachments = [];
            if (!empty($dteDocument['json_available'])) {
                $extraAttachments[] = [
                    'path' => (string) $dteDocument['json_absolute_path'],
                    'name' => basename((string) $dteDocument['json_absolute_path']),
                ];
            }

            atenea_send_invoice_email(
                $orderFull,
                (string) $dteDocument['pdf_absolute_path'],
                $extraAttachments,
                [
                    'is_simulation' => strtolower(trim((string) ($dteDocument['modo'] ?? 'simulation'))) === 'simulation',
                ]
            );

            DteService::syncLegacyInvoiceRecord(
                $db,
                $orderId,
                (string) ($orderFull['billing_email'] ?? ''),
                (string) $dteDocument['pdf_relative_path'],
                'sent',
                null
            );

            $invoiceNotice = strtolower(trim((string) ($dteDocument['modo'] ?? 'simulation'))) === 'simulation'
                ? 'La representacion grafica DTE y el JSON fueron enviados a tu correo en modo simulacion.'
                : 'La factura DTE fue enviada a tu correo.';
        } catch (Throwable $mailException) {
            DteService::syncLegacyInvoiceRecord(
                $db,
                $orderId,
                (string) ($orderFull['billing_email'] ?? ''),
                (string) ($dteDocument['pdf_relative_path'] ?? ''),
                'failed',
                $mailException->getMessage()
            );

            $invoiceNotice = 'El pago fue confirmado y el DTE se genero, pero no se pudo enviar por correo.';
        }
    } else {
        try {
            $legacyPdf = atenea_build_legacy_invoice_pdf($orderFull, $items);
            atenea_send_invoice_email($orderFull, $legacyPdf['absolute_path']);

            DteService::syncLegacyInvoiceRecord(
                $db,
                $orderId,
                (string) ($orderFull['billing_email'] ?? ''),
                (string) $legacyPdf['relative_path'],
                'sent',
                'Respaldo legacy enviado porque el DTE no estuvo disponible.'
            );

            $invoiceDownloadUrl = '../' . ltrim((string) $legacyPdf['relative_path'], '/');
            $invoiceNotice = 'El pago fue confirmado. Se envio el comprobante de respaldo mientras el DTE queda pendiente.';
        } catch (Throwable $legacyException) {
            DteService::syncLegacyInvoiceRecord(
                $db,
                $orderId,
                (string) ($orderFull['billing_email'] ?? ''),
                '',
                'failed',
                $legacyException->getMessage()
            );

            $invoiceNotice = 'El pago fue confirmado, pero no se pudo enviar la documentacion por correo.';
        }
    }
} else {
    $invoiceNotice = !empty($invoiceRow['sent_at'])
        ? 'La documentacion de la compra ya habia sido enviada anteriormente.'
        : 'La documentacion de la compra ya habia sido procesada.';
}

if ($invoiceDownloadUrl === '' && !empty($legacyInvoiceFile['exists'])) {
    $invoiceDownloadUrl = '../' . ltrim((string) $legacyInvoiceFile['relative_path'], '/');
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../includes/head_home.php'; ?>
<body>
  <?php include '../includes/navbar_home.php'; ?>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <h3 class="text-success mb-3">Pago completado</h3>
            <p class="mb-1"><strong>Orden:</strong> #<?php echo (int) $orderId; ?></p>
            <p class="mb-1"><strong>Cliente:</strong> <?php echo htmlspecialchars((string) ($orderFull['billing_name'] ?? '')); ?></p>
            <p class="mb-1"><strong>Correo:</strong> <?php echo htmlspecialchars((string) ($orderFull['billing_email'] ?? '')); ?></p>
            <p class="mb-3"><strong>Total:</strong> $<?php echo number_format((float) ($orderFull['total_amount'] ?? 0), 2); ?></p>

            <?php if ($invoiceNotice !== '') : ?>
              <div class="alert alert-info py-2"><?php echo htmlspecialchars($invoiceNotice); ?></div>
            <?php endif; ?>

            <?php if ($dteStatusNote !== '') : ?>
              <div class="alert alert-warning py-2"><?php echo htmlspecialchars($dteStatusNote); ?></div>
            <?php endif; ?>

            <div class="d-flex flex-wrap" style="gap: 0.75rem;">
              <?php if ($invoiceDownloadUrl !== '') : ?>
                <a href="<?php echo htmlspecialchars($invoiceDownloadUrl); ?>" class="btn btn-success" target="_blank" rel="noopener">
                  Descargar Factura PDF
                </a>
              <?php endif; ?>

              <?php if ($jsonDownloadUrl !== '') : ?>
                <a href="<?php echo htmlspecialchars($jsonDownloadUrl); ?>" class="btn btn-outline-primary">
                  Descargar JSON DTE
                </a>
              <?php endif; ?>
            </div>

            <div class="mt-4">
              <a href="productos.php" class="btn btn-primary">Seguir comprando</a>
              <a href="carrito.php?checkout_ok=<?php echo urlencode('Tu pago fue confirmado correctamente.'); ?>" class="btn btn-outline-primary ml-2">Ver carrito</a>
            </div>
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
