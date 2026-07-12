<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/stripe_config.php';
require_once __DIR__ . '/../includes/academic_payments.php';
require_once __DIR__ . '/../includes/invoice_mailer.php';
require_once __DIR__ . '/../includes/dte/bootstrap.php';

atenea_academic_assert_tables($db);
atenea_academic_require_role(['Estudiante']);

$checkoutSessionId = trim((string) ($_GET['session_id'] ?? ''));
if ($checkoutSessionId === '') {
    atenea_academic_flash_set('danger', 'Sesion de pago no valida.');
    header('Location: estudiante_pagos.php');
    exit;
}

$ch = curl_init('https://api.stripe.com/v1/checkout/sessions/' . rawurlencode($checkoutSessionId));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . STRIPE_SECRET_KEY]);
$stripeResponse = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$sessionData = json_decode((string) $stripeResponse, true);

if ($httpStatus < 200 || $httpStatus >= 300 || empty($sessionData['id'])) {
    atenea_academic_flash_set('danger', 'No se pudo verificar el pago con Stripe.');
    header('Location: estudiante_pagos.php');
    exit;
}

if (($sessionData['payment_status'] ?? '') !== 'paid') {
    atenea_academic_flash_set('danger', 'El pago aun no esta confirmado.');
    header('Location: estudiante_pagos.php');
    exit;
}

$paymentId = (int) ($sessionData['metadata']['payment_id'] ?? 0);
$studentId = (int) ($_SESSION['ESTUDIANTE_ID'] ?? 0);
$paymentIntent = trim((string) ($sessionData['payment_intent'] ?? ''));

$stmt = $db->prepare('SELECT id, student_id FROM academic_payments WHERE id = ? AND stripe_session_id = ? LIMIT 1');
if (!$stmt) {
    atenea_academic_flash_set('danger', 'No se pudo consultar el pago academico.');
    header('Location: estudiante_pagos.php');
    exit;
}
$stmt->bind_param('is', $paymentId, $checkoutSessionId);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
if ($result instanceof mysqli_result) {
    mysqli_free_result($result);
}
$stmt->close();

if (!$payment || (int) $payment['student_id'] !== $studentId) {
    atenea_academic_flash_set('danger', 'El pago no pertenece a tu cuenta.');
    header('Location: estudiante_pagos.php');
    exit;
}

try {
    atenea_academic_apply_paid_payment($db, $paymentId, $paymentIntent);
    $dteNotice = '';

    try {
        $dteDocument = DteAcademicService::generateForPayment($db, $paymentId, [
            'user_id' => (int) ($_SESSION['MEMBER_ID'] ?? 0),
        ]);

        if (!empty($dteDocument['pdf_available'])) {
            $paymentStmt = $db->prepare('SELECT id, payer_name, payer_email, amount FROM academic_payments WHERE id = ? LIMIT 1');
            if ($paymentStmt) {
                $paymentStmt->bind_param('i', $paymentId);
                $paymentStmt->execute();
                $paymentResult = $paymentStmt->get_result();
                $paymentRow = $paymentResult instanceof mysqli_result ? $paymentResult->fetch_assoc() : null;
                if ($paymentResult instanceof mysqli_result) {
                    mysqli_free_result($paymentResult);
                }
                $paymentStmt->close();

                if ($paymentRow) {
                    $extraAttachments = [];
                    if (!empty($dteDocument['json_available'])) {
                        $extraAttachments[] = [
                            'path' => (string) $dteDocument['json_absolute_path'],
                            'name' => basename((string) $dteDocument['json_absolute_path']),
                        ];
                    }

                    try {
                        atenea_send_invoice_email(
                            [
                                'id' => (int) $paymentRow['id'],
                                'billing_name' => (string) $paymentRow['payer_name'],
                                'billing_email' => (string) $paymentRow['payer_email'],
                                'total_amount' => (float) $paymentRow['amount'],
                            ],
                            (string) $dteDocument['pdf_absolute_path'],
                            $extraAttachments,
                            [
                                'is_simulation' => strtolower(trim((string) ($dteDocument['modo'] ?? 'simulation'))) === 'simulation',
                            ]
                        );
                        DteAcademicService::markEmail($db, $paymentId, 'sent', null);
                    } catch (Throwable $mailException) {
                        DteAcademicService::markEmail($db, $paymentId, 'failed', $mailException->getMessage());
                        $dteNotice = ' El DTE se genero, pero no se pudo enviar por correo.';
                    }
                }
            }
        }
    } catch (Throwable $dteException) {
        error_log('[DTE Academico] Fallo para pago #' . $paymentId . ': ' . $dteException->getMessage());
        $dteNotice = ' El DTE quedo pendiente/error para revision administrativa.';
    }

    atenea_academic_flash_set('success', 'Pago academico confirmado correctamente.' . $dteNotice);
} catch (Throwable $exception) {
    atenea_academic_flash_set('danger', $exception->getMessage());
}

header('Location: estudiante_pagos.php');
exit;
