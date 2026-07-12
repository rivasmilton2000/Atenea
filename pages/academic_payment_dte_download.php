<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/academic_payments.php';
require_once __DIR__ . '/../includes/dte/bootstrap.php';

atenea_academic_assert_tables($db);
atenea_academic_require_role(['Estudiante']);

$paymentId = filter_input(INPUT_GET, 'payment_id', FILTER_VALIDATE_INT);
$studentId = (int) ($_SESSION['ESTUDIANTE_ID'] ?? 0);

if (!$paymentId || $studentId <= 0) {
    atenea_academic_flash_set('danger', 'Documento DTE no valido.');
    header('Location: estudiante_pagos.php');
    exit;
}

$stmt = $db->prepare('SELECT id FROM academic_payments WHERE id = ? AND student_id = ? AND status = \'paid\' LIMIT 1');
if (!$stmt) {
    atenea_academic_flash_set('danger', 'No se pudo validar el pago.');
    header('Location: estudiante_pagos.php');
    exit;
}
$stmt->bind_param('ii', $paymentId, $studentId);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
if ($result instanceof mysqli_result) {
    mysqli_free_result($result);
}
$stmt->close();

if (!$payment) {
    atenea_academic_flash_set('danger', 'No puedes descargar un DTE que no pertenece a tu cuenta.');
    header('Location: estudiante_pagos.php');
    exit;
}

$document = DteAcademicService::getDocumentByPaymentId($db, $paymentId);
if (!$document || empty($document['pdf_available'])) {
    atenea_academic_flash_set('danger', 'El PDF DTE aun no esta disponible.');
    header('Location: estudiante_pagos.php');
    exit;
}

$absolutePath = (string) $document['pdf_absolute_path'];
if (!is_file($absolutePath) || !is_readable($absolutePath)) {
    atenea_academic_flash_set('danger', 'El archivo DTE no esta disponible.');
    header('Location: estudiante_pagos.php');
    exit;
}

$fileSize = filesize($absolutePath);
header('Content-Type: application/pdf');
header('Content-Length: ' . ($fileSize !== false ? (string) $fileSize : '0'));
header('Content-Disposition: attachment; filename="' . basename($absolutePath) . '"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
header('X-Content-Type-Options: nosniff');
readfile($absolutePath);
exit;
