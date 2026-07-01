<?php

class DteService
{
    public static function generateForOrder(mysqli $db, int $orderId, array $options = []): array
    {
        DteSchema::ensure($db);
        DteStorage::ensureDirectories();

        $forceRetry = !empty($options['force_retry']);
        $userId = isset($options['user_id']) && (int) $options['user_id'] > 0 ? (int) $options['user_id'] : null;
        $createdFiles = [];
        $identifiers = [
            'tipo_dte' => '01',
            'numero_control' => '',
            'codigo_generacion' => '',
        ];

        try {
            mysqli_begin_transaction($db);

            $existingDocument = self::getDocumentRowForUpdate($db, $orderId);
            if ($existingDocument && self::isProcessedState((string) ($existingDocument['estado'] ?? '')) && !$forceRetry) {
                mysqli_commit($db);

                return self::hydrateDocumentResult($existingDocument);
            }

            if ($existingDocument && !$forceRetry && !self::isRetryableState((string) ($existingDocument['estado'] ?? ''))) {
                mysqli_commit($db);

                return self::hydrateDocumentResult($existingDocument);
            }

            $order = self::loadOrder($db, $orderId);
            $items = self::loadOrderItems($db, $orderId);
            $settings = DteConfig::getActive($db);

            if ($userId === null) {
                $userId = self::resolveUserId($db, $order, $options);
            }

            $receiverProfile = self::resolveReceiverProfile($db, $order, $userId);
            $identifiers = DteNumbering::reserve($db, $settings, '01', $existingDocument);
            $dtePayload = DteJsonBuilder::build($order, $items, $settings, $identifiers, $receiverProfile);

            if (DteConfig::isSimulation($settings)) {
                $responsePayload = DteMockHaciendaClient::submit($dtePayload, $settings);
                $dtePayload['firmaElectronica'] = DteMockHaciendaClient::buildSimulatedSignature($dtePayload);
                $dtePayload['selloRecibido'] = (string) ($responsePayload['selloRecibido'] ?? '');
                $dtePayload['validezFiscal'] = 'NO VALIDO FISCALMENTE';
                $internalStatus = 'PROCESADO SIMULADO';
            } else {
                $responsePayload = DteHaciendaClient::submit($dtePayload, $settings);
                $dtePayload['selloRecibido'] = (string) ($responsePayload['selloRecibido'] ?? '');
                $internalStatus = strtoupper(trim((string) ($responsePayload['estado'] ?? 'PENDIENTE'))) === 'PROCESADO'
                    && trim((string) ($responsePayload['selloRecibido'] ?? '')) !== ''
                    ? 'PROCESADO'
                    : 'PENDIENTE';
            }

            $fiscalValidity = DteConfig::fiscalValidityLabel($settings, $responsePayload);
            $codigoGeneracion = (string) ($identifiers['codigo_generacion'] ?? '');

            $jsonFile = DteStorage::writeJson('json', $codigoGeneracion . '.json', $dtePayload);
            $createdFiles[] = $jsonFile['absolute_path'];
            $responseFile = DteStorage::writeJson('responses', $codigoGeneracion . '_response.json', $responsePayload);
            $createdFiles[] = $responseFile['absolute_path'];

            $pdfFile = DteStorage::buildFilePaths('pdf', $codigoGeneracion . '.pdf');
            DtePdfRenderer::render([
                'order' => $order,
                'items' => $items,
                'settings' => $settings,
                'dte' => $dtePayload,
                'response' => $responsePayload,
                'status_label' => $internalStatus,
                'fiscal_validity' => $fiscalValidity,
            ], $pdfFile['absolute_path']);
            $createdFiles[] = $pdfFile['absolute_path'];

            $issuedDate = (string) ($dtePayload['identificacion']['fecEmi'] ?? date('Y-m-d'));
            $issuedTime = (string) ($dtePayload['identificacion']['horEmi'] ?? date('H:i:s'));
            $totalPagar = (float) ($dtePayload['resumen']['totalPagar'] ?? 0);

            self::persistDocument(
                $db,
                $existingDocument,
                [
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'tipo_dte' => '01',
                    'numero_control' => (string) ($identifiers['numero_control'] ?? ''),
                    'codigo_generacion' => $codigoGeneracion,
                    'sello_recibido' => (string) ($responsePayload['selloRecibido'] ?? ''),
                    'modelo_facturacion' => 'Previo',
                    'tipo_transmision' => 'Normal',
                    'version_json' => 1,
                    'ambiente' => DteConfig::environmentCode((string) ($settings['mode'] ?? 'simulation')),
                    'estado' => $internalStatus,
                    'codigo_msg' => (string) ($responsePayload['codigoMsg'] ?? ''),
                    'descripcion_msg' => (string) ($responsePayload['descripcionMsg'] ?? ''),
                    'fecha_emision' => $issuedDate,
                    'hora_emision' => $issuedTime,
                    'total_pagar' => $totalPagar,
                    'json_path' => $jsonFile['relative_path'],
                    'pdf_path' => $pdfFile['relative_path'],
                    'response_path' => $responseFile['relative_path'],
                    'modo' => (string) ($settings['mode'] ?? 'simulation'),
                ]
            );

            mysqli_commit($db);

            $document = self::getDocumentByOrderId($db, $orderId);
            if (!$document) {
                throw new RuntimeException('No se pudo recuperar el documento DTE generado.');
            }

            return $document;
        } catch (Throwable $exception) {
            mysqli_rollback($db);

            foreach ($createdFiles as $filePath) {
                DteStorage::deleteIfExists($filePath);
            }

            self::persistFailure($db, $orderId, $userId, $identifiers, $exception->getMessage());
            error_log('[DTE] ' . $exception->getMessage());

            throw $exception;
        }
    }

    public static function getDocumentByOrderId(mysqli $db, int $orderId): ?array
    {
        DteSchema::ensure($db);

        $stmt = $db->prepare('SELECT * FROM dte_documents WHERE order_id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        $stmt->close();

        if (!$row) {
            return null;
        }

        return self::hydrateDocumentResult($row);
    }

    public static function syncLegacyInvoiceRecord(mysqli $db, int $orderId, string $billingEmail, string $pdfRelativePath, string $emailStatus, ?string $errorMessage): void
    {
        $emailStatus = $emailStatus === 'sent' ? 'sent' : 'failed';
        $errorMessage = $errorMessage !== null ? trim($errorMessage) : null;
        $sentAt = $emailStatus === 'sent' ? date('Y-m-d H:i:s') : null;

        $sql = 'INSERT INTO orden_facturas (orden_id, billing_email, pdf_path, email_status, error_message, sent_at)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    billing_email = VALUES(billing_email),
                    pdf_path = VALUES(pdf_path),
                    email_status = VALUES(email_status),
                    error_message = VALUES(error_message),
                    sent_at = VALUES(sent_at)';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('No se pudo actualizar el registro legacy de la factura.');
        }

        $stmt->bind_param('isssss', $orderId, $billingEmail, $pdfRelativePath, $emailStatus, $errorMessage, $sentAt);
        $stmt->execute();
        $stmt->close();
    }

    public static function buildReceiverProfileForUser(array $profile): array
    {
        return [
            'nombre' => trim((string) ($profile['BILLING_NAME'] ?? '')) !== ''
                ? trim((string) ($profile['BILLING_NAME'] ?? ''))
                : trim((string) ($profile['FIRST_NAME'] ?? '') . ' ' . (string) ($profile['LAST_NAME'] ?? '')),
            'correo' => trim((string) ($profile['BILLING_EMAIL'] ?? '')) !== ''
                ? trim((string) ($profile['BILLING_EMAIL'] ?? ''))
                : trim((string) ($profile['EMAIL'] ?? '')),
            'telefono' => trim((string) ($profile['PHONE_NUMBER'] ?? '')),
            'direccion' => trim((string) ($profile['BILLING_DIRECCION'] ?? ($profile['direccion_estudiante'] ?? ''))),
            'municipio' => trim((string) ($profile['BILLING_MUNICIPIO'] ?? '')),
            'departamento' => trim((string) ($profile['BILLING_DEPARTAMENTO'] ?? '')),
            'distrito' => trim((string) ($profile['BILLING_DISTRITO'] ?? '')),
            'tipo_documento' => trim((string) ($profile['TIPO_DOCUMENTO'] ?? '')),
            'numero_documento' => trim((string) ($profile['NUMERO_DOCUMENTO'] ?? '')),
            'nrc' => trim((string) ($profile['BILLING_NRC'] ?? '')),
            'cod_actividad' => '',
            'desc_actividad' => '',
        ];
    }

    private static function getDocumentRowForUpdate(mysqli $db, int $orderId): ?array
    {
        $stmt = $db->prepare('SELECT * FROM dte_documents WHERE order_id = ? LIMIT 1 FOR UPDATE');
        if (!$stmt) {
            throw new RuntimeException('No se pudo bloquear el documento DTE actual.');
        }

        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        $stmt->close();

        return $row ?: null;
    }

    private static function loadOrder(mysqli $db, int $orderId): array
    {
        $sql = 'SELECT id, session_id, stripe_session_id, stripe_payment_intent, billing_name, billing_email, billing_address,
                       billing_tipo_documento, billing_numero_documento, billing_telefono, billing_departamento, billing_municipio, billing_distrito, billing_nrc,
                       subtotal, shipping_amount, total_amount, paid_at, created_at
                FROM ordenes
                WHERE id = ?
                LIMIT 1';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('No se pudo consultar la orden para generar el DTE.');
        }

        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        $stmt->close();

        if (!$order) {
            throw new RuntimeException('No se encontro la orden para generar el DTE.');
        }

        return $order;
    }

    private static function loadOrderItems(mysqli $db, int $orderId): array
    {
        $stmt = $db->prepare('SELECT producto_id, producto_nombre, precio_unitario, cantidad, subtotal FROM orden_detalles WHERE orden_id = ? ORDER BY id ASC');
        if (!$stmt) {
            throw new RuntimeException('No se pudo consultar el detalle de la orden para el DTE.');
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

        if ($items === []) {
            throw new RuntimeException('La orden no tiene items para construir el DTE.');
        }

        return $items;
    }

    private static function resolveUserId(mysqli $db, array $order, array $options): ?int
    {
        if (isset($options['user_id']) && (int) $options['user_id'] > 0) {
            return (int) $options['user_id'];
        }

        $email = trim((string) ($order['billing_email'] ?? ''));
        if ($email === '') {
            return null;
        }

        $stmt = $db->prepare('SELECT USER_ID FROM public_users WHERE LOWER(EMAIL) = LOWER(?) OR LOWER(BILLING_EMAIL) = LOWER(?) LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('ss', $email, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
            if ($result instanceof mysqli_result) {
                mysqli_free_result($result);
            }
            $stmt->close();

            if ($row && (int) ($row['USER_ID'] ?? 0) > 0) {
                return (int) $row['USER_ID'];
            }
        }

        return null;
    }

    private static function resolveReceiverProfile(mysqli $db, array $order, ?int $userId): array
    {
        $profile = [
            'nombre' => trim((string) ($order['billing_name'] ?? '')),
            'correo' => trim((string) ($order['billing_email'] ?? '')),
            'telefono' => trim((string) ($order['billing_telefono'] ?? '')),
            'direccion' => trim((string) ($order['billing_address'] ?? '')),
            'municipio' => trim((string) ($order['billing_municipio'] ?? '')),
            'departamento' => trim((string) ($order['billing_departamento'] ?? '')),
            'distrito' => trim((string) ($order['billing_distrito'] ?? '')),
            'tipo_documento' => trim((string) ($order['billing_tipo_documento'] ?? '')),
            'numero_documento' => trim((string) ($order['billing_numero_documento'] ?? '')),
            'nrc' => trim((string) ($order['billing_nrc'] ?? '')),
            'cod_actividad' => '',
            'desc_actividad' => '',
        ];

        if ($userId !== null && $userId > 0 && function_exists('atenea_fetch_user_by_id')) {
            $user = atenea_fetch_user_by_id($db, $userId);
            if (is_array($user)) {
                foreach (self::buildReceiverProfileForUser($user) as $key => $value) {
                    if (trim((string) ($profile[$key] ?? '')) === '' && trim((string) $value) !== '') {
                        $profile[$key] = $value;
                    }
                }
            }
        }

        if ($profile['telefono'] === '' && trim((string) ($order['billing_email'] ?? '')) !== '') {
            $email = trim((string) ($order['billing_email'] ?? ''));

            $stmt = $db->prepare('SELECT PHONE_NUMBER FROM public_users WHERE LOWER(EMAIL) = LOWER(?) LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
                if ($result instanceof mysqli_result) {
                    mysqli_free_result($result);
                }
                $stmt->close();

                if ($row) {
                    $profile['telefono'] = trim((string) ($row['PHONE_NUMBER'] ?? ''));
                }
            }
        }

        return $profile;
    }

    private static function persistDocument(mysqli $db, ?array $existingDocument, array $payload): void
    {
        if ($existingDocument) {
            $sql = 'UPDATE dte_documents SET
                user_id = ?,
                tipo_dte = ?,
                numero_control = ?,
                codigo_generacion = ?,
                sello_recibido = ?,
                modelo_facturacion = ?,
                tipo_transmision = ?,
                version_json = ?,
                ambiente = ?,
                estado = ?,
                codigo_msg = ?,
                descripcion_msg = ?,
                fecha_emision = ?,
                hora_emision = ?,
                total_pagar = ?,
                json_path = ?,
                pdf_path = ?,
                response_path = ?,
                modo = ?
                WHERE order_id = ?';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new RuntimeException('No se pudo actualizar el documento DTE.');
            }

            $userId = $payload['user_id'];
            $stmt->bind_param(
                'issssssisssssdssssi',
                $userId,
                $payload['tipo_dte'],
                $payload['numero_control'],
                $payload['codigo_generacion'],
                $payload['sello_recibido'],
                $payload['modelo_facturacion'],
                $payload['tipo_transmision'],
                $payload['version_json'],
                $payload['ambiente'],
                $payload['estado'],
                $payload['codigo_msg'],
                $payload['descripcion_msg'],
                $payload['fecha_emision'],
                $payload['hora_emision'],
                $payload['total_pagar'],
                $payload['json_path'],
                $payload['pdf_path'],
                $payload['response_path'],
                $payload['modo'],
                $payload['order_id']
            );
            $stmt->execute();
            $stmt->close();

            return;
        }

        $sql = 'INSERT INTO dte_documents (
            order_id, user_id, tipo_dte, numero_control, codigo_generacion, sello_recibido,
            modelo_facturacion, tipo_transmision, version_json, ambiente, estado,
            codigo_msg, descripcion_msg, fecha_emision, hora_emision, total_pagar,
            json_path, pdf_path, response_path, modo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('No se pudo insertar el documento DTE.');
        }

        $userId = $payload['user_id'];
        $stmt->bind_param(
            'iissssssisssssdsssss',
            $payload['order_id'],
            $userId,
            $payload['tipo_dte'],
            $payload['numero_control'],
            $payload['codigo_generacion'],
            $payload['sello_recibido'],
            $payload['modelo_facturacion'],
            $payload['tipo_transmision'],
            $payload['version_json'],
            $payload['ambiente'],
            $payload['estado'],
            $payload['codigo_msg'],
            $payload['descripcion_msg'],
            $payload['fecha_emision'],
            $payload['hora_emision'],
            $payload['total_pagar'],
            $payload['json_path'],
            $payload['pdf_path'],
            $payload['response_path'],
            $payload['modo']
        );
        $stmt->execute();
        $stmt->close();
    }

    private static function persistFailure(mysqli $db, int $orderId, ?int $userId, array $identifiers, string $message): void
    {
        try {
            DteSchema::ensure($db);
            $current = self::getDocumentByOrderId($db, $orderId);
            $numeroControl = trim((string) ($identifiers['numero_control'] ?? ($current['numero_control'] ?? '')));
            $codigoGeneracion = trim((string) ($identifiers['codigo_generacion'] ?? ($current['codigo_generacion'] ?? '')));
            $modo = 'simulation';
            $settings = DteConfig::getActive($db);
            $modo = (string) ($settings['mode'] ?? 'simulation');

            if ($current) {
                $sql = 'UPDATE dte_documents SET
                    user_id = ?,
                    numero_control = ?,
                    codigo_generacion = ?,
                    estado = ?,
                    descripcion_msg = ?,
                    modo = ?
                    WHERE order_id = ?';
                $stmt = $db->prepare($sql);
                if ($stmt) {
                    $status = 'ERROR';
                    $stmt->bind_param('isssssi', $userId, $numeroControl, $codigoGeneracion, $status, $message, $modo, $orderId);
                    $stmt->execute();
                    $stmt->close();
                }

                return;
            }

            $sql = 'INSERT INTO dte_documents (
                order_id, user_id, tipo_dte, numero_control, codigo_generacion, sello_recibido,
                modelo_facturacion, tipo_transmision, version_json, ambiente, estado,
                codigo_msg, descripcion_msg, fecha_emision, hora_emision, total_pagar,
                json_path, pdf_path, response_path, modo
            ) VALUES (?, ?, ?, ?, ?, NULL, ?, ?, 1, ?, ?, NULL, ?, ?, ?, 0.00, NULL, NULL, NULL, ?)
            ON DUPLICATE KEY UPDATE
                user_id = VALUES(user_id),
                numero_control = VALUES(numero_control),
                codigo_generacion = VALUES(codigo_generacion),
                estado = VALUES(estado),
                descripcion_msg = VALUES(descripcion_msg),
                modo = VALUES(modo)';
            $stmt = $db->prepare($sql);
            if ($stmt) {
                $tipoDte = '01';
                $modelo = 'Previo';
                $transmision = 'Normal';
                $ambiente = DteConfig::environmentCode($modo);
                $estado = 'ERROR';
                $today = date('Y-m-d');
                $now = date('H:i:s');
                $stmt->bind_param(
                    'iisssssssssss',
                    $orderId,
                    $userId,
                    $tipoDte,
                    $numeroControl,
                    $codigoGeneracion,
                    $modelo,
                    $transmision,
                    $ambiente,
                    $estado,
                    $message,
                    $today,
                    $now,
                    $modo
                );
                $stmt->execute();
                $stmt->close();
            }
        } catch (Throwable $ignored) {
            error_log('[DTE] No se pudo persistir el estado de error: ' . $ignored->getMessage());
        }
    }

    private static function hydrateDocumentResult(array $row): array
    {
        $pdf = DteStorage::resolveStoredFile((string) ($row['pdf_path'] ?? ''));
        $json = DteStorage::resolveStoredFile((string) ($row['json_path'] ?? ''));
        $response = DteStorage::resolveStoredFile((string) ($row['response_path'] ?? ''));

        $row['pdf_absolute_path'] = (string) ($pdf['absolute_path'] ?? '');
        $row['pdf_relative_path'] = (string) ($pdf['relative_path'] ?? ($row['pdf_path'] ?? ''));
        $row['pdf_available'] = !empty($pdf['exists']);
        $row['json_absolute_path'] = (string) ($json['absolute_path'] ?? '');
        $row['json_relative_path'] = (string) ($json['relative_path'] ?? ($row['json_path'] ?? ''));
        $row['json_available'] = !empty($json['exists']);
        $row['response_absolute_path'] = (string) ($response['absolute_path'] ?? '');
        $row['response_relative_path'] = (string) ($response['relative_path'] ?? ($row['response_path'] ?? ''));
        $row['response_available'] = !empty($response['exists']);

        return $row;
    }

    private static function isProcessedState(string $status): bool
    {
        $status = strtoupper(trim($status));

        return in_array($status, ['PROCESADO', 'PROCESADO SIMULADO'], true);
    }

    private static function isRetryableState(string $status): bool
    {
        $status = strtoupper(trim($status));

        return in_array($status, ['', 'PENDIENTE', 'ERROR'], true);
    }
}
