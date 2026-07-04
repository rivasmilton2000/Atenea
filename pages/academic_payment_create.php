<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/stripe_config.php';
require_once __DIR__ . '/../includes/academic_payments.php';

atenea_academic_assert_tables($db);
atenea_academic_require_role(['Estudiante']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: estudiante_pagos.php');
    exit;
}

try {
    $transactionStarted = false;
    atenea_require_csrf_token('academic_payment_checkout', (string) ($_POST['csrf_token'] ?? ''));

    $studentId = (int) ($_SESSION['ESTUDIANTE_ID'] ?? 0);
    $userId = (int) ($_SESSION['MEMBER_ID'] ?? 0);
    $chargeIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['charge_ids'] ?? [])))));
    if ($studentId <= 0 || $userId <= 0 || $chargeIds === []) {
        throw new RuntimeException('Selecciona al menos un cargo pendiente.');
    }

    if (strpos(STRIPE_SECRET_KEY, 'sk_test_REEMPLAZA_AQUI') === 0 || strpos(STRIPE_SECRET_KEY, 'sk_live_REEMPLAZA_AQUI') === 0) {
        throw new RuntimeException('Stripe no esta configurado para recibir pagos.');
    }

    mysqli_begin_transaction($db);
    $transactionStarted = true;

    $charges = [];
    $total = 0.0;
    $cycleId = 0;
    foreach ($chargeIds as $chargeId) {
        $stmt = $db->prepare(
            "SELECT ac.*, e.nombres_estudiante, e.apellidos_estudiante, e.correo_estudiante
             FROM academic_charges ac
             JOIN estudiantes e ON e.ESTUDIANTE_ID = ac.student_id
             WHERE ac.id = ? AND ac.student_id = ? AND ac.status IN ('pending','partial','overdue')
             LIMIT 1 FOR UPDATE"
        );
        if (!$stmt) {
            throw new RuntimeException('No se pudo consultar un cargo seleccionado.');
        }
        $stmt->bind_param('ii', $chargeId, $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $charge = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        $stmt->close();
        if (!$charge) {
            throw new RuntimeException('Uno de los cargos ya no esta disponible para pago.');
        }
        $balance = atenea_academic_charge_balance($charge);
        if ($balance <= 0.0) {
            throw new RuntimeException('Uno de los cargos seleccionados ya no tiene saldo.');
        }
        if ($cycleId > 0 && $cycleId !== (int) $charge['cycle_id']) {
            throw new RuntimeException('No puedes mezclar cargos de ciclos academicos diferentes en un mismo pago.');
        }
        $charge['balance'] = $balance;
        $charges[] = $charge;
        $total += $balance;
        $cycleId = (int) $charge['cycle_id'];
    }

    $payerName = trim((string) ($_SESSION['nombres_estudiante'] ?? '') . ' ' . (string) ($_SESSION['apellidos_estudiante'] ?? ''));
    $payerEmail = trim((string) ($_SESSION['correo_estudiante'] ?? ($_SESSION['EMAIL'] ?? '')));
    if ($payerName === '') {
        $payerName = 'Estudiante Atenea';
    }
    if (!filter_var($payerEmail, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Tu cuenta no tiene un correo valido para crear el pago.');
    }

    $method = 'stripe';
    $status = 'pending_payment';
    $stmtPayment = $db->prepare(
        'INSERT INTO academic_payments (student_id, cycle_id, user_id, payment_method, amount, status, payer_name, payer_email)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    if (!$stmtPayment) {
        throw new RuntimeException('No se pudo crear el pago academico.');
    }
    $stmtPayment->bind_param('iiisdsss', $studentId, $cycleId, $userId, $method, $total, $status, $payerName, $payerEmail);
    $stmtPayment->execute();
    $paymentId = (int) $db->insert_id;
    $stmtPayment->close();

    $stmtDetail = $db->prepare('INSERT INTO academic_payment_details (payment_id, charge_id, amount) VALUES (?, ?, ?)');
    if (!$stmtDetail) {
        throw new RuntimeException('No se pudo crear el detalle del pago.');
    }
    foreach ($charges as $charge) {
        $chargeId = (int) $charge['id'];
        $amount = (float) $charge['balance'];
        $stmtDetail->bind_param('iid', $paymentId, $chargeId, $amount);
        $stmtDetail->execute();
    }
    $stmtDetail->close();

    $payload = [
        'mode' => 'payment',
        'success_url' => APP_BASE_URL . '/pages/academic_payment_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => APP_BASE_URL . '/pages/estudiante_pagos.php',
        'customer_email' => $payerEmail,
        'metadata[payment_id]' => (string) $paymentId,
        'metadata[student_id]' => (string) $studentId,
        'payment_method_types[0]' => 'card',
    ];

    foreach ($charges as $idx => $charge) {
        $payload['line_items[' . $idx . '][price_data][currency]'] = 'usd';
        $payload['line_items[' . $idx . '][price_data][product_data][name]'] = 'Pago academico - ' . $charge['description'] . ' ' . $charge['period_label'];
        $payload['line_items[' . $idx . '][price_data][unit_amount]'] = (int) round(((float) $charge['balance']) * 100);
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
        throw new RuntimeException('Stripe no pudo iniciar el pago academico.');
    }

    $stripeSessionId = (string) $sessionData['id'];
    $stmtUpdate = $db->prepare('UPDATE academic_payments SET stripe_session_id = ? WHERE id = ? LIMIT 1');
    if (!$stmtUpdate) {
        throw new RuntimeException('No se pudo guardar la sesion de Stripe.');
    }
    $stmtUpdate->bind_param('si', $stripeSessionId, $paymentId);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    mysqli_commit($db);
    $transactionStarted = false;
    header('Location: ' . $sessionData['url']);
    exit;
} catch (Throwable $exception) {
    if (!empty($transactionStarted)) {
        mysqli_rollback($db);
    }
    atenea_academic_flash_set('danger', $exception->getMessage());
    header('Location: estudiante_pagos.php');
    exit;
}
