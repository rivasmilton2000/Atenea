<?php

class DteAcademicService
{
    public static function ensureSchema(mysqli $db): void
    {
        DteSchema::ensure($db);

        $sql = "CREATE TABLE IF NOT EXISTS academic_dte_documents (
            id INT NOT NULL AUTO_INCREMENT,
            payment_id INT NOT NULL,
            student_id INT NOT NULL,
            user_id INT DEFAULT NULL,
            tipo_dte VARCHAR(10) NOT NULL DEFAULT '01',
            numero_control VARCHAR(50) NOT NULL,
            codigo_generacion VARCHAR(50) NOT NULL,
            sello_recibido VARCHAR(120) DEFAULT NULL,
            modelo_facturacion VARCHAR(50) NOT NULL DEFAULT 'Previo',
            tipo_transmision VARCHAR(50) NOT NULL DEFAULT 'Normal',
            version_json INT NOT NULL DEFAULT 1,
            ambiente VARCHAR(10) NOT NULL DEFAULT '00',
            estado VARCHAR(50) NOT NULL DEFAULT 'PENDIENTE',
            codigo_msg VARCHAR(20) DEFAULT NULL,
            descripcion_msg VARCHAR(255) DEFAULT NULL,
            fecha_emision DATE NOT NULL,
            hora_emision TIME NOT NULL,
            total_pagar DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            json_path VARCHAR(255) DEFAULT NULL,
            pdf_path VARCHAR(255) DEFAULT NULL,
            response_path VARCHAR(255) DEFAULT NULL,
            modo ENUM('simulation','test','production') NOT NULL DEFAULT 'simulation',
            email_status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
            email_error VARCHAR(255) DEFAULT NULL,
            sent_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_academic_dte_payment (payment_id),
            UNIQUE KEY uq_academic_dte_numero_control (numero_control),
            UNIQUE KEY uq_academic_dte_codigo_generacion (codigo_generacion),
            KEY idx_academic_dte_student (student_id),
            KEY idx_academic_dte_estado (estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if ($db->query($sql) !== true) {
            throw new RuntimeException('No se pudo preparar la tabla DTE academica: ' . $db->error);
        }
    }

    public static function generateForPayment(mysqli $db, int $paymentId, array $options = []): array
    {
        self::ensureSchema($db);
        DteStorage::ensureDirectories();

        $forceRetry = !empty($options['force_retry']);
        $createdFiles = [];
        $identifiers = [
            'tipo_dte' => '01',
            'numero_control' => '',
            'codigo_generacion' => '',
        ];

        try {
            mysqli_begin_transaction($db);

            $existing = self::getDocumentRowForUpdate($db, $paymentId);
            if ($existing && self::isProcessedState((string) ($existing['estado'] ?? '')) && !$forceRetry) {
                mysqli_commit($db);

                return self::hydrateDocumentResult($existing);
            }

            $payment = self::loadPayment($db, $paymentId);
            if ((string) ($payment['status'] ?? '') !== 'paid') {
                throw new RuntimeException('El pago academico aun no esta confirmado.');
            }

            $items = self::loadPaymentItems($db, $paymentId);
            $settings = DteConfig::getActive($db);
            $receiverProfile = self::buildReceiverProfile($payment);
            $order = self::buildOrderLikePayload($payment);
            $identifiers = DteNumbering::reserve($db, $settings, '01');
            $dtePayload = DteJsonBuilder::build($order, $items, $settings, $identifiers, $receiverProfile);
            $dtePayload['apendice'][] = [
                'campo' => 'PAGO_ACADEMICO_ID',
                'etiqueta' => 'Pago academico',
                'valor' => (string) $paymentId,
            ];

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

            $codigoGeneracion = (string) ($identifiers['codigo_generacion'] ?? '');
            $jsonFile = DteStorage::writeJson('json', 'academic_' . $codigoGeneracion . '.json', $dtePayload);
            $createdFiles[] = $jsonFile['absolute_path'];
            $responseFile = DteStorage::writeJson('responses', 'academic_' . $codigoGeneracion . '_response.json', $responsePayload);
            $createdFiles[] = $responseFile['absolute_path'];
            $pdfFile = DteStorage::buildFilePaths('pdf', 'academic_' . $codigoGeneracion . '.pdf');
            DtePdfRenderer::render([
                'order' => $order,
                'items' => $items,
                'settings' => $settings,
                'dte' => $dtePayload,
                'response' => $responsePayload,
                'status_label' => $internalStatus,
                'fiscal_validity' => DteConfig::fiscalValidityLabel($settings, $responsePayload),
            ], $pdfFile['absolute_path']);
            $createdFiles[] = $pdfFile['absolute_path'];

            self::persistDocument($db, $existing, [
                'payment_id' => $paymentId,
                'student_id' => (int) $payment['student_id'],
                'user_id' => isset($payment['user_id']) ? (int) $payment['user_id'] : null,
                'tipo_dte' => '01',
                'numero_control' => (string) ($identifiers['numero_control'] ?? ''),
                'codigo_generacion' => $codigoGeneracion,
                'sello_recibido' => (string) ($responsePayload['selloRecibido'] ?? ''),
                'ambiente' => DteConfig::environmentCode((string) ($settings['mode'] ?? 'simulation')),
                'estado' => $internalStatus,
                'codigo_msg' => (string) ($responsePayload['codigoMsg'] ?? ''),
                'descripcion_msg' => (string) ($responsePayload['descripcionMsg'] ?? ''),
                'fecha_emision' => (string) ($dtePayload['identificacion']['fecEmi'] ?? date('Y-m-d')),
                'hora_emision' => (string) ($dtePayload['identificacion']['horEmi'] ?? date('H:i:s')),
                'total_pagar' => (float) ($dtePayload['resumen']['totalPagar'] ?? 0),
                'json_path' => $jsonFile['relative_path'],
                'pdf_path' => $pdfFile['relative_path'],
                'response_path' => $responseFile['relative_path'],
                'modo' => (string) ($settings['mode'] ?? 'simulation'),
            ]);

            mysqli_commit($db);

            $document = self::getDocumentByPaymentId($db, $paymentId);
            if (!$document) {
                throw new RuntimeException('No se pudo recuperar el DTE academico generado.');
            }

            return $document;
        } catch (Throwable $exception) {
            mysqli_rollback($db);
            foreach ($createdFiles as $filePath) {
                DteStorage::deleteIfExists($filePath);
            }
            self::persistFailure($db, $paymentId, $identifiers, $exception->getMessage());
            throw $exception;
        }
    }

    public static function getDocumentByPaymentId(mysqli $db, int $paymentId): ?array
    {
        self::ensureSchema($db);

        $stmt = $db->prepare('SELECT * FROM academic_dte_documents WHERE payment_id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $paymentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        $stmt->close();

        return $row ? self::hydrateDocumentResult($row) : null;
    }

    public static function markEmail(mysqli $db, int $paymentId, string $status, ?string $errorMessage = null): void
    {
        self::ensureSchema($db);

        $status = $status === 'sent' ? 'sent' : 'failed';
        $sentAt = $status === 'sent' ? date('Y-m-d H:i:s') : null;
        $errorMessage = $errorMessage !== null ? substr(trim($errorMessage), 0, 255) : null;
        $stmt = $db->prepare('UPDATE academic_dte_documents SET email_status = ?, email_error = ?, sent_at = ? WHERE payment_id = ? LIMIT 1');
        if (!$stmt) {
            return;
        }
        $stmt->bind_param('sssi', $status, $errorMessage, $sentAt, $paymentId);
        $stmt->execute();
        $stmt->close();
    }

    private static function loadPayment(mysqli $db, int $paymentId): array
    {
        $stmt = $db->prepare(
            "SELECT ap.*, e.nombres_estudiante, e.apellidos_estudiante, e.correo_estudiante, e.direccion_estudiante,
                    e.numero_cel_encargado, e.numero_tel_encargado, e.nombres_encargado, e.apellidos_encargado,
                    pu.BILLING_NAME, pu.BILLING_EMAIL, pu.PHONE_NUMBER, pu.TIPO_DOCUMENTO, pu.NUMERO_DOCUMENTO,
                    pu.BILLING_DEPARTAMENTO, pu.BILLING_MUNICIPIO, pu.BILLING_DISTRITO, pu.BILLING_DIRECCION, pu.BILLING_NRC
             FROM academic_payments ap
             JOIN estudiantes e ON e.ESTUDIANTE_ID = ap.student_id
             LEFT JOIN public_users pu ON pu.USER_ID = ap.user_id
             WHERE ap.id = ?
             LIMIT 1"
        );
        if (!$stmt) {
            throw new RuntimeException('No se pudo consultar el pago academico.');
        }
        $stmt->bind_param('i', $paymentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $payment = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        $stmt->close();

        if (!$payment) {
            throw new RuntimeException('Pago academico no encontrado.');
        }

        return $payment;
    }

    private static function loadPaymentItems(mysqli $db, int $paymentId): array
    {
        $stmt = $db->prepare(
            "SELECT apd.charge_id AS producto_id,
                    ac.description AS producto_nombre,
                    1 AS cantidad,
                    apd.amount AS precio_unitario,
                    apd.amount AS subtotal
             FROM academic_payment_details apd
             JOIN academic_charges ac ON ac.id = apd.charge_id
             WHERE apd.payment_id = ?
             ORDER BY apd.id ASC"
        );
        if (!$stmt) {
            throw new RuntimeException('No se pudo consultar el detalle academico para DTE.');
        }
        $stmt->bind_param('i', $paymentId);
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
            throw new RuntimeException('El pago academico no tiene conceptos para facturar.');
        }

        return $items;
    }

    private static function buildOrderLikePayload(array $payment): array
    {
        return [
            'id' => (int) $payment['id'],
            'stripe_session_id' => (string) ($payment['stripe_session_id'] ?? ''),
            'stripe_payment_intent' => (string) ($payment['stripe_payment_intent'] ?? ''),
            'payment_reference' => self::paymentReference($payment),
            'payment_code' => self::paymentCode((string) ($payment['payment_method'] ?? 'stripe')),
            'payment_method_label' => self::paymentMethodLabel((string) ($payment['payment_method'] ?? 'stripe')),
            'billing_name' => self::receiverName($payment),
            'billing_email' => self::receiverEmail($payment),
            'billing_address' => trim((string) ($payment['BILLING_DIRECCION'] ?? $payment['direccion_estudiante'] ?? '')),
            'billing_tipo_documento' => trim((string) ($payment['TIPO_DOCUMENTO'] ?? '')),
            'billing_numero_documento' => trim((string) ($payment['NUMERO_DOCUMENTO'] ?? '')),
            'billing_telefono' => trim((string) ($payment['PHONE_NUMBER'] ?? $payment['numero_cel_encargado'] ?? $payment['numero_tel_encargado'] ?? '')),
            'billing_departamento' => trim((string) ($payment['BILLING_DEPARTAMENTO'] ?? '')),
            'billing_municipio' => trim((string) ($payment['BILLING_MUNICIPIO'] ?? '')),
            'billing_distrito' => trim((string) ($payment['BILLING_DISTRITO'] ?? '')),
            'billing_nrc' => trim((string) ($payment['BILLING_NRC'] ?? '')),
            'subtotal' => (float) $payment['amount'],
            'shipping_amount' => 0.0,
            'total_amount' => (float) $payment['amount'],
            'paid_at' => (string) ($payment['paid_at'] ?? ''),
            'created_at' => (string) ($payment['created_at'] ?? ''),
        ];
    }

    private static function buildReceiverProfile(array $payment): array
    {
        return [
            'nombre' => self::receiverName($payment),
            'correo' => self::receiverEmail($payment),
            'telefono' => trim((string) ($payment['PHONE_NUMBER'] ?? $payment['numero_cel_encargado'] ?? $payment['numero_tel_encargado'] ?? '')),
            'direccion' => trim((string) ($payment['BILLING_DIRECCION'] ?? $payment['direccion_estudiante'] ?? '')),
            'municipio' => trim((string) ($payment['BILLING_MUNICIPIO'] ?? '')),
            'departamento' => trim((string) ($payment['BILLING_DEPARTAMENTO'] ?? '')),
            'distrito' => trim((string) ($payment['BILLING_DISTRITO'] ?? '')),
            'tipo_documento' => trim((string) ($payment['TIPO_DOCUMENTO'] ?? '')),
            'numero_documento' => trim((string) ($payment['NUMERO_DOCUMENTO'] ?? '')),
            'nrc' => trim((string) ($payment['BILLING_NRC'] ?? '')),
            'cod_actividad' => '',
            'desc_actividad' => '',
        ];
    }

    private static function receiverName(array $payment): string
    {
        $name = trim((string) ($payment['BILLING_NAME'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $payer = trim((string) ($payment['payer_name'] ?? ''));
        if ($payer !== '') {
            return $payer;
        }

        return trim((string) ($payment['nombres_estudiante'] ?? '') . ' ' . (string) ($payment['apellidos_estudiante'] ?? ''));
    }

    private static function receiverEmail(array $payment): string
    {
        foreach ([$payment['BILLING_EMAIL'] ?? '', $payment['payer_email'] ?? '', $payment['correo_estudiante'] ?? ''] as $email) {
            $email = trim((string) $email);
            if ($email !== '') {
                return $email;
            }
        }

        return '';
    }

    private static function paymentReference(array $payment): string
    {
        foreach ([$payment['stripe_payment_intent'] ?? '', $payment['stripe_session_id'] ?? ''] as $reference) {
            $reference = trim((string) $reference);
            if ($reference !== '') {
                return $reference;
            }
        }

        return 'PAGO-ACADEMICO-' . (int) ($payment['id'] ?? 0);
    }

    private static function paymentCode(string $method): string
    {
        switch ($method) {
            case 'cash':
                return '01';
            case 'bank':
                return '05';
            case 'stripe':
            case 'other':
            default:
                return '03';
        }
    }

    private static function paymentMethodLabel(string $method): string
    {
        switch ($method) {
            case 'cash':
                return 'Efectivo';
            case 'bank':
                return 'Transferencia bancaria';
            case 'stripe':
                return 'Tarjeta (Stripe)';
            default:
                return 'Otro metodo de pago';
        }
    }

    private static function getDocumentRowForUpdate(mysqli $db, int $paymentId): ?array
    {
        $stmt = $db->prepare('SELECT * FROM academic_dte_documents WHERE payment_id = ? LIMIT 1 FOR UPDATE');
        if (!$stmt) {
            throw new RuntimeException('No se pudo bloquear el DTE academico actual.');
        }
        $stmt->bind_param('i', $paymentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        $stmt->close();

        return $row ?: null;
    }

    private static function persistDocument(mysqli $db, ?array $existing, array $payload): void
    {
        if ($existing) {
            $sql = 'UPDATE academic_dte_documents SET
                student_id = ?, user_id = ?, tipo_dte = ?, numero_control = ?, codigo_generacion = ?, sello_recibido = ?,
                ambiente = ?, estado = ?, codigo_msg = ?, descripcion_msg = ?, fecha_emision = ?, hora_emision = ?,
                total_pagar = ?, json_path = ?, pdf_path = ?, response_path = ?, modo = ?
                WHERE payment_id = ?';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new RuntimeException('No se pudo actualizar el DTE academico.');
            }
            $stmt->bind_param(
                'iissssssssssdssssi',
                $payload['student_id'],
                $payload['user_id'],
                $payload['tipo_dte'],
                $payload['numero_control'],
                $payload['codigo_generacion'],
                $payload['sello_recibido'],
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
                $payload['payment_id']
            );
            $stmt->execute();
            $stmt->close();

            return;
        }

        $sql = 'INSERT INTO academic_dte_documents (
            payment_id, student_id, user_id, tipo_dte, numero_control, codigo_generacion, sello_recibido,
            ambiente, estado, codigo_msg, descripcion_msg, fecha_emision, hora_emision, total_pagar,
            json_path, pdf_path, response_path, modo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('No se pudo insertar el DTE academico.');
        }
        $stmt->bind_param(
            'iiissssssssssdssss',
            $payload['payment_id'],
            $payload['student_id'],
            $payload['user_id'],
            $payload['tipo_dte'],
            $payload['numero_control'],
            $payload['codigo_generacion'],
            $payload['sello_recibido'],
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

    private static function persistFailure(mysqli $db, int $paymentId, array $identifiers, string $message): void
    {
        try {
            self::ensureSchema($db);
            $payment = self::loadPayment($db, $paymentId);
            $settings = DteConfig::getActive($db);
            $status = 'ERROR';
            $tipoDte = '01';
            $numeroControl = trim((string) ($identifiers['numero_control'] ?? ''));
            $codigoGeneracion = trim((string) ($identifiers['codigo_generacion'] ?? ''));
            if ($numeroControl === '') {
                $numeroControl = 'ACADEMIC-ERROR-' . $paymentId . '-' . time();
            }
            if ($codigoGeneracion === '') {
                $codigoGeneracion = DteNumbering::uuidV4();
            }
            $today = date('Y-m-d');
            $now = date('H:i:s');
            $total = (float) ($payment['amount'] ?? 0);
            $modo = (string) ($settings['mode'] ?? 'simulation');
            $ambiente = DteConfig::environmentCode($modo);
            $studentId = (int) ($payment['student_id'] ?? 0);
            $userId = isset($payment['user_id']) ? (int) $payment['user_id'] : null;
            $message = substr($message, 0, 255);

            $sql = 'INSERT INTO academic_dte_documents (
                payment_id, student_id, user_id, tipo_dte, numero_control, codigo_generacion,
                ambiente, estado, descripcion_msg, fecha_emision, hora_emision, total_pagar, modo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                estado = VALUES(estado),
                descripcion_msg = VALUES(descripcion_msg),
                modo = VALUES(modo)';
            $stmt = $db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('iiissssssssds', $paymentId, $studentId, $userId, $tipoDte, $numeroControl, $codigoGeneracion, $ambiente, $status, $message, $today, $now, $total, $modo);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Throwable $ignored) {
            error_log('[DTE Academico] No se pudo persistir el error: ' . $ignored->getMessage());
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
        return in_array(strtoupper(trim($status)), ['PROCESADO', 'PROCESADO SIMULADO'], true);
    }
}
