<?php
require_once __DIR__ . '/session.php';
if (!logged_in()) {
    header('Location: ' . atenea_build_login_url('historial_compras.php', 'login_required'));
    exit;
}

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';
require_once __DIR__ . '/../includes/public_purchase_history.php';

if (!atenea_session_is_public_user()) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);
$profile = atenea_fetch_public_profile_by_user_id($db, $memberId);

if (!$profile) {
    atenea_render_auth_alert(
        'warning',
        'Perfil no disponible',
        'No pudimos validar el perfil asociado a esta sesión.',
        'logout.php?redirect=homepage.php'
    );
}

$email = trim((string) ($profile['EMAIL'] ?? ($_SESSION['EMAIL'] ?? '')));
$orderId = (int) ($_GET['orden'] ?? 0);
$mode = (string) ($_GET['mode'] ?? 'view');
$mode = $mode === 'download' ? 'download' : 'view';

$invoice = atenea_obtener_factura_compra_usuario($db, [
    'user_id' => $memberId,
    'email' => $email,
], $orderId);

if (!$invoice || empty($invoice['invoice_available']) || empty($invoice['invoice_absolute_path'])) {
    atenea_render_auth_alert(
        'warning',
        'Factura no disponible',
        'No encontramos una factura disponible para esa compra o no pertenece a tu cuenta.',
        'usuario_vista.php'
    );
}

$absolutePath = (string) $invoice['invoice_absolute_path'];
if (!is_file($absolutePath) || !is_readable($absolutePath)) {
    atenea_render_auth_alert(
        'error',
        'Archivo no disponible',
        'La factura existe en el historial, pero no se pudo abrir el archivo en este momento.',
        'usuario_vista.php'
    );
}

$fileName = basename($absolutePath);
$fileSize = filesize($absolutePath);

header('Content-Type: application/pdf');
header('Content-Length: ' . ($fileSize !== false ? (string) $fileSize : '0'));
header('Content-Disposition: ' . ($mode === 'download' ? 'attachment' : 'inline') . '; filename="' . $fileName . '"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
header('X-Content-Type-Options: nosniff');

readfile($absolutePath);
exit;
