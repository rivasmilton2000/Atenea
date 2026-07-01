<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';
require_once __DIR__ . '/../includes/public_purchase_history.php';

if (!atenea_session_is_public_user()) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}

if (!function_exists('usuario_dte_format_date')) {
    function usuario_dte_format_date(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return 'No disponible';
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? 'No disponible' : date('d/m/Y h:i A', $timestamp);
    }
}

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);
$profile = atenea_fetch_public_profile_by_user_id($db, $memberId);

if (!$profile) {
    atenea_render_auth_alert(
        'warning',
        'Perfil no disponible',
        'No pudimos validar el perfil asociado a esta sesion.',
        'logout.php?redirect=homepage.php'
    );
}

$email = trim((string) ($profile['EMAIL'] ?? ($_SESSION['EMAIL'] ?? '')));
$orderId = (int) ($_GET['orden'] ?? 0);
$mode = trim((string) ($_GET['mode'] ?? 'status'));

$document = atenea_obtener_documento_dte_compra_usuario($db, [
    'user_id' => $memberId,
    'email' => $email,
], $orderId);

if (!$document) {
    atenea_render_auth_alert(
        'warning',
        'DTE no disponible',
        'No encontramos un documento DTE asociado a esa compra o no pertenece a tu cuenta.',
        'historial_compras.php'
    );
}

if ($mode === 'json') {
    if (empty($document['json_available']) || empty($document['json_absolute_path'])) {
        atenea_render_auth_alert(
            'warning',
            'JSON no disponible',
            'El JSON DTE todavia no esta disponible para esta compra.',
            'historial_compras.php'
        );
    }

    header('Content-Type: application/json');
    header('Content-Length: ' . (string) filesize((string) $document['json_absolute_path']));
    header('Content-Disposition: attachment; filename="' . basename((string) $document['json_absolute_path']) . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    header('X-Content-Type-Options: nosniff');
    readfile((string) $document['json_absolute_path']);
    exit;
}

if ($mode === 'response') {
    if (empty($document['response_available']) || empty($document['response_absolute_path'])) {
        atenea_render_auth_alert(
            'warning',
            'Respuesta no disponible',
            'La respuesta de Hacienda aun no esta disponible para esta compra.',
            'historial_compras.php'
        );
    }

    header('Content-Type: application/json');
    header('Content-Length: ' . (string) filesize((string) $document['response_absolute_path']));
    header('Content-Disposition: attachment; filename="' . basename((string) $document['response_absolute_path']) . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    header('X-Content-Type-Options: nosniff');
    readfile((string) $document['response_absolute_path']);
    exit;
}

$navSections = [
    [
        'title' => 'Panel',
        'items' => [
            ['label' => 'Inicio', 'href' => 'usuario_vista.php', 'icon' => 'dashboard'],
        ],
    ],
    [
        'title' => 'Compras',
        'items' => [
            ['label' => 'Historial de compras', 'href' => 'historial_compras.php', 'icon' => 'receipt_long'],
            ['label' => 'Estado DTE', 'href' => 'usuario_compra_dte.php?orden=' . $orderId, 'icon' => 'description', 'active' => true],
        ],
    ],
];

ob_start();
?>
<div class="row mt-2">
  <div class="col-12">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white">
        <h5 class="mb-1">Documento DTE de la compra #<?php echo (int) $orderId; ?></h5>
        <p class="mb-0 text-muted">Consulta el estado interno, sello de recepcion y archivos asociados al documento tributario electronico.</p>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-lg-6 mb-3">
            <div class="border rounded p-3 h-100">
              <h6 class="text-uppercase text-muted mb-3">Resumen</h6>
              <p class="mb-2"><strong>Fecha:</strong> <?php echo dashboard_h(usuario_dte_format_date((string) $document['date'])); ?></p>
              <p class="mb-2"><strong>Total:</strong> $<?php echo number_format((float) $document['amount'], 2); ?></p>
              <p class="mb-2"><strong>Estado interno:</strong> <?php echo dashboard_h((string) $document['status']); ?></p>
              <p class="mb-2"><strong>Modo:</strong> <?php echo dashboard_h((string) $document['modo']); ?></p>
              <p class="mb-0"><strong>Mensaje:</strong> <?php echo dashboard_h((string) $document['descripcion_msg']); ?></p>
            </div>
          </div>
          <div class="col-lg-6 mb-3">
            <div class="border rounded p-3 h-100">
              <h6 class="text-uppercase text-muted mb-3">Identificadores</h6>
              <p class="mb-2"><strong>Numero de control:</strong><br><?php echo dashboard_h((string) $document['numero_control']); ?></p>
              <p class="mb-2"><strong>Codigo de generacion:</strong><br><?php echo dashboard_h((string) $document['codigo_generacion']); ?></p>
              <p class="mb-0"><strong>Sello de recepcion:</strong><br><?php echo dashboard_h((string) $document['sello_recibido']); ?></p>
            </div>
          </div>
        </div>

        <div class="alert alert-warning">
          <?php if (strtoupper(trim((string) $document['status'])) === 'PROCESADO SIMULADO'): ?>
            Estado interno: PROCESADO SIMULADO. Validez fiscal: NO VALIDO FISCALMENTE.
          <?php else: ?>
            Este documento solo sera fiscalmente valido cuando exista configuracion real y Hacienda devuelva sello autentico.
          <?php endif; ?>
        </div>

        <div class="d-flex flex-wrap" style="gap: 0.75rem;">
          <?php if (!empty($document['pdf_available'])): ?>
            <a class="btn btn-outline-dark" href="<?php echo dashboard_h(atenea_purchase_invoice_url((int) $document['order_id'], 'view')); ?>" target="_blank" rel="noopener">Ver factura PDF</a>
          <?php endif; ?>
          <?php if (!empty($document['json_available'])): ?>
            <a class="btn btn-outline-primary" href="<?php echo dashboard_h(atenea_purchase_dte_url((int) $document['order_id'], 'json')); ?>">Descargar JSON DTE</a>
          <?php endif; ?>
          <?php if (!empty($document['response_available'])): ?>
            <a class="btn btn-outline-info" href="<?php echo dashboard_h(atenea_purchase_dte_url((int) $document['order_id'], 'response')); ?>">Respuesta Hacienda</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$bodySectionsHtml = ob_get_clean();

dashboard_render_material_page([
    'pageTitle' => 'Estado DTE',
    'roleLabel' => 'Usuario registrado',
    'welcomeTitle' => $mode === 'sello' ? 'Sello de recepcion DTE' : 'Estado del documento DTE',
    'welcomeText' => $mode === 'sello'
        ? 'Revisa el sello de recepcion vinculado a tu compra.'
        : 'Consulta la trazabilidad del documento tributario electronico generado para tu compra.',
    'profileUrl' => 'usuario_vista.php',
    'logoutUrl' => 'logout.php?redirect=homepage.php',
    'navSections' => $navSections,
    'cards' => [],
    'quickLinks' => [],
    'summaryItems' => [],
    'bodySectionsHtml' => $bodySectionsHtml,
    'heroBadges' => [
        'Compra #' . $orderId,
        (string) $document['status'],
        (string) $document['modo'],
    ],
    'heroActions' => [
        ['label' => 'Volver al historial', 'href' => 'historial_compras.php', 'icon' => 'receipt_long'],
        ['label' => 'Ver factura', 'href' => atenea_purchase_invoice_url((int) $document['order_id'], 'view'), 'icon' => 'picture_as_pdf', 'variant' => 'outline'],
    ],
]);
