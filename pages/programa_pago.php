<?php
require 'session.php';
require_once '../includes/connection.php';
require_once '../includes/atenea_auth.php';
require_once '../includes/atenea_capacitacion.php';
require_once '../includes/stripe_config.php';

$programId = max(0, (int) ($_POST['programa_id'] ?? $_GET['id'] ?? 0));
$returnUrl = 'programa_cotizar.php?id=' . $programId;

if (!logged_in()) {
    header('Location: ' . atenea_build_login_url($returnUrl, 'checkout_required'));
    exit;
}
if (!atenea_session_is_public_user() || $programId <= 0) {
    header('Location: educacion.php');
    exit;
}

$programa = atenea_capacitacion_fetch_program_by_id($db, $programId, true);
$publicUserId = (int) ($_SESSION['PUBLIC_USER_ID'] ?? 0);
$userId = (int) ($_SESSION['MEMBER_ID'] ?? 0);
$profile = atenea_fetch_public_profile_by_user_id($db, $userId);
$existingEnrollment = atenea_capacitacion_fetch_active_enrollment_for_public_user($db, $publicUserId, $programId);
if ($existingEnrollment) {
    header('Location: mi_curso_activo.php?programa=' . $programId);
    exit;
}
if (!$programa || !$profile || !atenea_capacitacion_payment_ready($db)) {
    header('Location: ' . $returnUrl . '&payment=unavailable');
    exit;
}
if ((int) ($profile['BILLING_PROFILE_COMPLETED'] ?? 0) !== 1) {
    header('Location: billing_profile.php?prompt=1&return=' . rawurlencode($returnUrl));
    exit;
}
if (strpos(STRIPE_SECRET_KEY, 'REEMPLAZA_AQUI') !== false) {
    header('Location: ' . $returnUrl . '&payment=unavailable');
    exit;
}

$amount = atenea_capacitacion_price($programa);
$sessionId = 'course_' . bin2hex(random_bytes(16));
$billingName = trim((string) ($profile['BILLING_NAME'] ?? '')) ?: trim((string) ($profile['FIRST_NAME'] ?? '') . ' ' . (string) ($profile['LAST_NAME'] ?? ''));
$billingEmail = trim((string) ($profile['BILLING_EMAIL'] ?? '')) ?: trim((string) ($profile['EMAIL'] ?? ''));
$billingAddress = trim((string) ($profile['BILLING_DIRECCION'] ?? ''));
$documentType = trim((string) ($profile['TIPO_DOCUMENTO'] ?? ''));
$documentNumber = trim((string) ($profile['NUMERO_DOCUMENTO'] ?? ''));
$phone = trim((string) ($profile['PHONE_NUMBER'] ?? ''));
$department = trim((string) ($profile['BILLING_DEPARTAMENTO'] ?? ''));
$municipality = trim((string) ($profile['BILLING_MUNICIPIO'] ?? ''));
$district = trim((string) ($profile['BILLING_DISTRITO'] ?? ''));
$nrc = trim((string) ($profile['BILLING_NRC'] ?? ''));

$db->begin_transaction();
try {
    $stmt = $db->prepare("INSERT INTO ordenes (session_id,billing_name,billing_email,billing_address,billing_tipo_documento,billing_numero_documento,billing_telefono,billing_departamento,billing_municipio,billing_distrito,billing_nrc,subtotal,shipping_amount,total_amount,estado) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,0,?,'pending_payment')");
    if (!$stmt) throw new RuntimeException('order');
    $stmt->bind_param('sssssssssssdd', $sessionId,$billingName,$billingEmail,$billingAddress,$documentType,$documentNumber,$phone,$department,$municipality,$district,$nrc,$amount,$amount);
    if (!$stmt->execute()) throw new RuntimeException('order');
    $orderId = (int) $stmt->insert_id; $stmt->close();

    $stmt = $db->prepare('INSERT INTO orden_detalles (orden_id,producto_id,programa_id,producto_nombre,precio_unitario,cantidad,subtotal) VALUES (?,NULL,?,?,?,1,?)');
    if (!$stmt) throw new RuntimeException('detail');
    $title = (string) $programa['titulo'];
    $stmt->bind_param('iisdd', $orderId,$programId,$title,$amount,$amount);
    if (!$stmt->execute()) throw new RuntimeException('detail');
    $stmt->close();

    $stmt = $db->prepare("INSERT INTO course_payment_requests (public_user_id,user_id,programa_id,order_id,amount,status,payment_method) VALUES (?,?,?,?,?,'pendiente','stripe') ON DUPLICATE KEY UPDATE user_id=VALUES(user_id),order_id=VALUES(order_id),amount=VALUES(amount),status=IF(status='pagado',status,'pendiente'),payment_method='stripe'");
    if (!$stmt) throw new RuntimeException('payment');
    $stmt->bind_param('iiiid', $publicUserId,$userId,$programId,$orderId,$amount);
    if (!$stmt->execute()) throw new RuntimeException('payment');
    $stmt->close();

    $payload = [
        'mode' => 'payment',
        'success_url' => APP_BASE_URL . '/pages/checkout_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => APP_BASE_URL . '/pages/programa_pago_cancelado.php?order_id=' . $orderId,
        'billing_address_collection' => 'required',
        'customer_email' => $billingEmail,
        'metadata[order_id]' => (string) $orderId,
        'metadata[course_program_id]' => (string) $programId,
        'line_items[0][price_data][currency]' => 'usd',
        'line_items[0][price_data][product_data][name]' => $title,
        'line_items[0][price_data][unit_amount]' => (int) round($amount * 100),
        'line_items[0][quantity]' => 1,
        'payment_method_types[0]' => 'card',
    ];
    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($payload),CURLOPT_HTTPHEADER=>['Authorization: Bearer '.STRIPE_SECRET_KEY,'Content-Type: application/x-www-form-urlencoded']]);
    $response = curl_exec($ch); $http = curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
    $stripe = json_decode((string)$response,true);
    if ($http < 200 || $http >= 300 || empty($stripe['id']) || empty($stripe['url'])) throw new RuntimeException('stripe');
    $stmt=$db->prepare('UPDATE ordenes SET stripe_session_id=? WHERE id=?');
    $stmt->bind_param('si',$stripe['id'],$orderId); $stmt->execute(); $stmt->close();
    $db->commit();
    header('Location: '.$stripe['url']); exit;
} catch (Throwable $e) {
    $db->rollback();
    error_log('Atenea direct course checkout: '.$e->getMessage());
    $stmt=$db->prepare("INSERT INTO course_payment_requests (public_user_id,user_id,programa_id,amount,status,payment_method) VALUES (?,?,?,?,'fallido','stripe') ON DUPLICATE KEY UPDATE status=IF(status='pagado',status,'fallido'),updated_at=CURRENT_TIMESTAMP");
    if($stmt){$stmt->bind_param('iiid',$publicUserId,$userId,$programId,$amount);$stmt->execute();$stmt->close();}
    header('Location: '.$returnUrl.'&payment=failed'); exit;
}
