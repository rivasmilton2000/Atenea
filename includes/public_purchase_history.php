<?php

require_once __DIR__ . '/dte/bootstrap.php';

if (!function_exists('atenea_purchase_status_meta')) {
    function atenea_purchase_status_meta(string $status): array
    {
        $normalized = strtolower(trim($status));

        $map = [
            'paid' => ['label' => 'Pagado', 'class' => 'is-paid'],
            'pending_payment' => ['label' => 'Pendiente', 'class' => 'is-pending'],
            'pending' => ['label' => 'Pendiente', 'class' => 'is-pending'],
            'failed' => ['label' => 'Fallido', 'class' => 'is-failed'],
            'cancelled' => ['label' => 'Cancelado', 'class' => 'is-failed'],
            'reembolsado' => ['label' => 'Reembolsado', 'class' => 'is-refunded'],
            'refunded' => ['label' => 'Reembolsado', 'class' => 'is-refunded'],
        ];

        return $map[$normalized] ?? ['label' => ucfirst($normalized !== '' ? $normalized : 'Desconocido'), 'class' => 'is-neutral'];
    }
}

if (!function_exists('atenea_purchase_method_label')) {
    function atenea_purchase_method_label(array $purchase): string
    {
        $stripeSessionId = trim((string) ($purchase['stripe_session_id'] ?? ''));
        $paymentIntent = trim((string) ($purchase['stripe_payment_intent'] ?? ''));

        if ($stripeSessionId !== '' || $paymentIntent !== '') {
            return 'Tarjeta (Stripe)';
        }

        return 'No disponible';
    }
}

if (!function_exists('atenea_purchase_format_amount')) {
    function atenea_purchase_format_amount(float $amount, string $currency = 'USD'): string
    {
        $currency = strtoupper(trim($currency));
        if ($currency === '') {
            $currency = 'USD';
        }

        return '$' . number_format($amount, 2) . ' ' . $currency;
    }
}

if (!function_exists('atenea_purchase_invoice_url')) {
    function atenea_purchase_invoice_url(int $orderId, string $mode = 'view'): string
    {
        $mode = $mode === 'download' ? 'download' : 'view';

        return 'usuario_compra_factura.php?orden=' . $orderId . '&mode=' . $mode;
    }
}

if (!function_exists('atenea_purchase_dte_url')) {
    function atenea_purchase_dte_url(int $orderId, string $mode = 'status'): string
    {
        $allowedModes = ['status', 'json', 'response', 'sello'];
        if (!in_array($mode, $allowedModes, true)) {
            $mode = 'status';
        }

        return 'usuario_compra_dte.php?orden=' . $orderId . '&mode=' . $mode;
    }
}

if (!function_exists('atenea_purchase_resolve_invoice_file')) {
    function atenea_purchase_resolve_invoice_file(string $relativePath): array
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

if (!function_exists('atenea_purchase_concept_summary')) {
    function atenea_purchase_concept_summary(array $itemNames): string
    {
        $cleanNames = [];

        foreach ($itemNames as $itemName) {
            $itemName = trim((string) $itemName);
            if ($itemName === '' || in_array($itemName, $cleanNames, true)) {
                continue;
            }

            $cleanNames[] = $itemName;
        }

        if ($cleanNames === []) {
            return 'Compra registrada';
        }

        return implode(', ', $cleanNames);
    }
}

if (!function_exists('obtenerHistorialComprasUsuario')) {
    function obtenerHistorialComprasUsuario(mysqli $db, array $usuarioActual, int $page = 1, int $perPage = 10): array
    {
        $userEmail = trim((string) ($usuarioActual['email'] ?? ''));
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        if ($userEmail === '' || !atenea_db_has_table($db, 'ordenes')) {
            return [];
        }

        $hasOrderDetails = atenea_db_has_table($db, 'orden_detalles');
        $hasInvoices = atenea_db_has_table($db, 'orden_facturas');
        $hasDteDocuments = atenea_db_has_table($db, 'dte_documents');
        $detailsJoin = $hasOrderDetails
            ? "LEFT JOIN (
                    SELECT
                        od.orden_id,
                        GROUP_CONCAT(od.producto_nombre ORDER BY od.id SEPARATOR '||') AS item_names,
                        COALESCE(SUM(od.cantidad), 0) AS total_quantity,
                        COUNT(*) AS line_count,
                        MAX(CASE WHEN od.producto_id IS NOT NULL AND od.producto_id > 0 THEN 1 ELSE 0 END) AS has_product
                    FROM orden_detalles od
                    GROUP BY od.orden_id
                ) od_agg ON od_agg.orden_id = o.id"
            : '';
        $detailsSelect = $hasOrderDetails
            ? 'COALESCE(od_agg.item_names, \'\') AS item_names,
               COALESCE(od_agg.total_quantity, 0) AS total_quantity,
               COALESCE(od_agg.line_count, 0) AS line_count,
               COALESCE(od_agg.has_product, 0) AS has_product'
            : "'' AS item_names, 0 AS total_quantity, 0 AS line_count, 0 AS has_product";
        $invoiceJoin = $hasInvoices ? 'LEFT JOIN orden_facturas ofa ON ofa.orden_id = o.id' : '';
        $invoiceSelect = $hasInvoices ? 'ofa.pdf_path, ofa.email_status' : 'NULL AS pdf_path, NULL AS email_status';
        $dteJoin = $hasDteDocuments ? 'LEFT JOIN dte_documents dd ON dd.order_id = o.id' : '';
        $dteSelect = $hasDteDocuments
            ? 'dd.estado AS dte_estado, dd.sello_recibido, dd.json_path, dd.response_path, dd.pdf_path AS dte_pdf_path, dd.modo AS dte_modo'
            : "NULL AS dte_estado, NULL AS sello_recibido, NULL AS json_path, NULL AS response_path, NULL AS dte_pdf_path, NULL AS dte_modo";

        $sql = "
            SELECT
                o.id,
                o.billing_name,
                o.billing_email,
                o.total_amount,
                o.estado,
                o.paid_at,
                o.created_at,
                o.stripe_session_id,
                o.stripe_payment_intent,
                {$detailsSelect},
                {$invoiceSelect},
                {$dteSelect}
            FROM (
                SELECT
                    id,
                    billing_name,
                    billing_email,
                    total_amount,
                    estado,
                    paid_at,
                    created_at,
                    stripe_session_id,
                    stripe_payment_intent
                FROM ordenes
                WHERE billing_email = ?
                ORDER BY COALESCE(paid_at, created_at) DESC, id DESC
                LIMIT ? OFFSET ?
            ) o
            {$detailsJoin}
            {$invoiceJoin}
            {$dteJoin}
            ORDER BY COALESCE(o.paid_at, o.created_at) DESC, o.id DESC
        ";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('sii', $userEmail, $perPage, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];

        while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
            $itemNames = explode('||', (string) ($row['item_names'] ?? ''));
            $concept = atenea_purchase_concept_summary($itemNames);
            $dtePdfFile = DteStorage::resolveStoredFile((string) ($row['dte_pdf_path'] ?? ''));
            $invoiceFile = !empty($dtePdfFile['exists'])
                ? $dtePdfFile
                : atenea_purchase_resolve_invoice_file((string) ($row['pdf_path'] ?? ''));
            $jsonFile = DteStorage::resolveStoredFile((string) ($row['json_path'] ?? ''));
            $responseFile = DteStorage::resolveStoredFile((string) ($row['response_path'] ?? ''));

            $history[] = [
                'order_id' => (int) ($row['id'] ?? 0),
                'date' => (string) ($row['paid_at'] ?: $row['created_at']),
                'concept' => $concept,
                'type' => ((int) ($row['has_product'] ?? 0) > 0 || (int) ($row['line_count'] ?? 0) > 0) ? 'Producto' : 'Otro',
                'amount' => (float) ($row['total_amount'] ?? 0),
                'currency' => 'USD',
                'status' => (string) ($row['estado'] ?? ''),
                'status_meta' => atenea_purchase_status_meta((string) ($row['estado'] ?? '')),
                'payment_method' => atenea_purchase_method_label($row),
                'billing_email' => (string) ($row['billing_email'] ?? ''),
                'line_count' => (int) ($row['line_count'] ?? 0),
                'total_quantity' => (int) ($row['total_quantity'] ?? 0),
                'stripe_session_id' => trim((string) ($row['stripe_session_id'] ?? '')),
                'stripe_payment_intent' => trim((string) ($row['stripe_payment_intent'] ?? '')),
                'invoice_available' => $invoiceFile['exists'],
                'invoice_email_status' => (string) ($row['email_status'] ?? ''),
                'dte_status' => (string) ($row['dte_estado'] ?? ''),
                'dte_sello' => (string) ($row['sello_recibido'] ?? ''),
                'dte_mode' => (string) ($row['dte_modo'] ?? ''),
                'dte_json_available' => !empty($jsonFile['exists']),
                'dte_response_available' => !empty($responseFile['exists']),
            ];
        }

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        $stmt->close();

        return $history;
    }
}

if (!function_exists('atenea_contar_historial_compras_usuario')) {
    function atenea_contar_historial_compras_usuario(mysqli $db, array $usuarioActual): int
    {
        $userEmail = trim((string) ($usuarioActual['email'] ?? ''));

        if ($userEmail === '' || !atenea_db_has_table($db, 'ordenes')) {
            return 0;
        }

        $stmt = $db->prepare('SELECT COUNT(*) FROM ordenes WHERE billing_email = ?');
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param('s', $userEmail);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();

        return (int) $total;
    }
}

if (!function_exists('atenea_obtener_factura_compra_usuario')) {
    function atenea_obtener_factura_compra_usuario(mysqli $db, array $usuarioActual, int $orderId): ?array
    {
        $userEmail = trim((string) ($usuarioActual['email'] ?? ''));

        if ($orderId <= 0 || $userEmail === '' || !atenea_db_has_table($db, 'ordenes')) {
            return null;
        }

        $hasInvoices = atenea_db_has_table($db, 'orden_facturas');
        $hasDteDocuments = atenea_db_has_table($db, 'dte_documents');

        $invoiceJoin = $hasInvoices ? 'LEFT JOIN orden_facturas ofa ON ofa.orden_id = o.id' : '';
        $invoiceSelect = $hasInvoices ? 'ofa.pdf_path, ofa.email_status' : 'NULL AS pdf_path, NULL AS email_status';
        $dteJoin = $hasDteDocuments ? 'LEFT JOIN dte_documents dd ON dd.order_id = o.id' : '';
        $dteSelect = $hasDteDocuments
            ? 'dd.pdf_path AS dte_pdf_path, dd.estado AS dte_estado, dd.sello_recibido, dd.json_path, dd.response_path, dd.modo AS dte_modo'
            : 'NULL AS dte_pdf_path, NULL AS dte_estado, NULL AS sello_recibido, NULL AS json_path, NULL AS response_path, NULL AS dte_modo';

        $sql = "
            SELECT
                o.id,
                o.billing_email,
                o.estado,
                o.paid_at,
                o.created_at,
                {$invoiceSelect},
                {$dteSelect}
            FROM ordenes o
            {$invoiceJoin}
            {$dteJoin}
            WHERE o.id = ? AND o.billing_email = ?
            LIMIT 1
        ";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('is', $orderId, $userEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $purchase = $result instanceof mysqli_result ? $result->fetch_assoc() : null;

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        $stmt->close();

        if (!$purchase) {
            return null;
        }

        $dtePdfFile = DteStorage::resolveStoredFile((string) ($purchase['dte_pdf_path'] ?? ''));
        $invoiceFile = !empty($dtePdfFile['exists'])
            ? $dtePdfFile
            : atenea_purchase_resolve_invoice_file((string) ($purchase['pdf_path'] ?? ''));

        return [
            'order_id' => (int) ($purchase['id'] ?? 0),
            'status' => (string) ($purchase['estado'] ?? ''),
            'date' => (string) (($purchase['paid_at'] ?? '') !== '' ? $purchase['paid_at'] : ($purchase['created_at'] ?? '')),
            'billing_email' => (string) ($purchase['billing_email'] ?? ''),
            'invoice_email_status' => (string) ($purchase['email_status'] ?? ''),
            'invoice_relative_path' => $invoiceFile['relative_path'],
            'invoice_absolute_path' => $invoiceFile['absolute_path'],
            'invoice_available' => $invoiceFile['exists'],
            'dte_status' => (string) ($purchase['dte_estado'] ?? ''),
            'dte_sello' => (string) ($purchase['sello_recibido'] ?? ''),
            'dte_mode' => (string) ($purchase['dte_modo'] ?? ''),
        ];
    }
}

if (!function_exists('atenea_obtener_documento_dte_compra_usuario')) {
    function atenea_obtener_documento_dte_compra_usuario(mysqli $db, array $usuarioActual, int $orderId): ?array
    {
        $userEmail = trim((string) ($usuarioActual['email'] ?? ''));

        if ($orderId <= 0 || $userEmail === '' || !atenea_db_has_table($db, 'ordenes') || !atenea_db_has_table($db, 'dte_documents')) {
            return null;
        }

        $sql = "
            SELECT
                o.id,
                o.billing_email,
                o.total_amount,
                o.paid_at,
                o.created_at,
                dd.estado,
                dd.sello_recibido,
                dd.numero_control,
                dd.codigo_generacion,
                dd.modo,
                dd.descripcion_msg,
                dd.pdf_path,
                dd.json_path,
                dd.response_path
            FROM ordenes o
            JOIN dte_documents dd ON dd.order_id = o.id
            WHERE o.id = ? AND o.billing_email = ?
            LIMIT 1
        ";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('is', $orderId, $userEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $document = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        $stmt->close();

        if (!$document) {
            return null;
        }

        $pdfFile = DteStorage::resolveStoredFile((string) ($document['pdf_path'] ?? ''));
        $jsonFile = DteStorage::resolveStoredFile((string) ($document['json_path'] ?? ''));
        $responseFile = DteStorage::resolveStoredFile((string) ($document['response_path'] ?? ''));

        return [
            'order_id' => (int) ($document['id'] ?? 0),
            'billing_email' => (string) ($document['billing_email'] ?? ''),
            'amount' => (float) ($document['total_amount'] ?? 0),
            'date' => (string) (($document['paid_at'] ?? '') !== '' ? $document['paid_at'] : ($document['created_at'] ?? '')),
            'status' => (string) ($document['estado'] ?? ''),
            'sello_recibido' => (string) ($document['sello_recibido'] ?? ''),
            'numero_control' => (string) ($document['numero_control'] ?? ''),
            'codigo_generacion' => (string) ($document['codigo_generacion'] ?? ''),
            'modo' => (string) ($document['modo'] ?? ''),
            'descripcion_msg' => (string) ($document['descripcion_msg'] ?? ''),
            'pdf_relative_path' => (string) ($pdfFile['relative_path'] ?? ''),
            'pdf_absolute_path' => (string) ($pdfFile['absolute_path'] ?? ''),
            'pdf_available' => !empty($pdfFile['exists']),
            'json_relative_path' => (string) ($jsonFile['relative_path'] ?? ''),
            'json_absolute_path' => (string) ($jsonFile['absolute_path'] ?? ''),
            'json_available' => !empty($jsonFile['exists']),
            'response_relative_path' => (string) ($responseFile['relative_path'] ?? ''),
            'response_absolute_path' => (string) ($responseFile['absolute_path'] ?? ''),
            'response_available' => !empty($responseFile['exists']),
        ];
    }
}
