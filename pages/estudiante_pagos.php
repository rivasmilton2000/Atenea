<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/academic_payments.php';
require_once __DIR__ . '/../includes/dte/bootstrap.php';

atenea_academic_assert_tables($db);
DteAcademicService::ensureSchema($db);
atenea_academic_require_role(['Estudiante']);

$studentId = (int) ($_SESSION['ESTUDIANTE_ID'] ?? 0);
if ($studentId <= 0) {
    atenea_render_auth_alert('warning', 'Cuenta sin estudiante', 'No encontramos un estudiante asociado a tu usuario.', atenea_dashboard_route_for_session());
}

$charges = atenea_academic_fetch_charges($db, $studentId, false);
$payableTotal = 0.0;
foreach ($charges as $charge) {
    if (in_array((string) $charge['status'], ['pending', 'partial', 'overdue'], true)) {
        $payableTotal += (float) $charge['balance'];
    }
}

$paymentsStmt = $db->prepare(
    "SELECT ap.id, ap.amount, ap.payment_method, ap.status, ap.paid_at, ap.created_at,
            ad.estado AS dte_estado,
            ad.pdf_path AS dte_pdf_path,
            ad.email_status AS dte_email_status
     FROM academic_payments ap
     LEFT JOIN academic_dte_documents ad ON ad.payment_id = ap.id
     WHERE ap.student_id = ?
     ORDER BY ap.created_at DESC
     LIMIT 100"
);
if (!$paymentsStmt) {
    throw new RuntimeException('No se pudo cargar el historial de pagos.');
}
$paymentsStmt->bind_param('i', $studentId);
$paymentsStmt->execute();
$paymentsResult = $paymentsStmt->get_result();
$payments = [];
while ($paymentsResult instanceof mysqli_result && ($row = $paymentsResult->fetch_assoc())) {
    $payments[] = $row;
}
if ($paymentsResult instanceof mysqli_result) {
    mysqli_free_result($paymentsResult);
}
$paymentsStmt->close();

$csrf = atenea_csrf_token('academic_payment_checkout');
$flash = atenea_academic_flash_pull();

include '../includes/sidebar_estudiante.php';
?>

<?php if ($flash): ?>
  <div class="alert alert-<?php echo atenea_academic_h($flash['type'] ?? 'info'); ?>"><?php echo atenea_academic_h($flash['message'] ?? ''); ?></div>
<?php endif; ?>

<div class="row">
  <div class="col-lg-4 mb-4">
    <div class="card shadow border-0 h-100">
      <div class="card-body">
        <p class="text-uppercase text-muted font-weight-bold mb-2">Estado de cuenta</p>
        <h3 class="mb-1">$<?php echo number_format($payableTotal, 2); ?></h3>
        <p class="mb-0 text-muted">Saldo pendiente de matricula, mensualidades u otros cargos academicos.</p>
      </div>
    </div>
  </div>
  <div class="col-lg-8 mb-4">
    <div class="card shadow border-0 h-100">
      <div class="card-body">
        <h5 class="mb-2">Pagos academicos</h5>
        <p class="mb-0 text-muted">Selecciona los cargos pendientes y paga en linea de forma segura. Los pagos confirmados actualizan tu estado de cuenta automaticamente.</p>
      </div>
    </div>
  </div>
</div>

<form method="post" action="academic_payment_create.php" data-atenea-loading-form data-loader-text="Preparando pago academico...">
  <input type="hidden" name="csrf_token" value="<?php echo atenea_academic_h($csrf); ?>">
  <div class="card shadow border-0 mb-4">
    <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">
      <h5 class="mb-0">Cargos academicos</h5>
      <button class="btn btn-primary mb-0" type="submit">Pagar seleccionados</button>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-bordered" width="100%">
        <thead>
          <tr>
            <th></th>
            <th>Ciclo</th>
            <th>Concepto</th>
            <th>Vence</th>
            <th>Total</th>
            <th>Pagado</th>
            <th>Saldo</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($charges as $charge): ?>
            <?php $canPay = (float) $charge['balance'] > 0 && in_array((string) $charge['status'], ['pending', 'partial', 'overdue'], true); ?>
            <tr>
              <td class="text-center">
                <?php if ($canPay): ?>
                  <input type="checkbox" name="charge_ids[]" value="<?php echo (int) $charge['id']; ?>">
                <?php endif; ?>
              </td>
              <td><?php echo atenea_academic_h($charge['cycle_name']); ?></td>
              <td><?php echo atenea_academic_h($charge['description'] . ' - ' . $charge['period_label']); ?></td>
              <td><?php echo atenea_academic_h($charge['due_date'] ?: 'Sin fecha'); ?></td>
              <td>$<?php echo number_format((float) $charge['total_amount'], 2); ?></td>
              <td>$<?php echo number_format((float) $charge['paid_amount'], 2); ?></td>
              <td>$<?php echo number_format((float) $charge['balance'], 2); ?></td>
              <td><span class="badge <?php echo atenea_academic_h(atenea_academic_badge_class((string) $charge['status'])); ?>"><?php echo atenea_academic_h(atenea_academic_status_label((string) $charge['status'])); ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if ($charges === []): ?>
            <tr><td colspan="8" class="text-center text-muted">No tienes cargos academicos registrados.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</form>

<div class="card shadow border-0 mb-4">
  <div class="card-header py-3"><h5 class="mb-0">Historial de pagos</h5></div>
  <div class="card-body table-responsive">
    <table class="table table-bordered js-datatable" width="100%">
      <thead><tr><th>#</th><th>Monto</th><th>Metodo</th><th>Estado</th><th>DTE</th><th>Fecha</th></tr></thead>
      <tbody>
        <?php foreach ($payments as $payment): ?>
          <tr>
            <td><?php echo (int) $payment['id']; ?></td>
            <td>$<?php echo number_format((float) $payment['amount'], 2); ?></td>
            <td><?php echo atenea_academic_h($payment['payment_method']); ?></td>
            <td><span class="badge <?php echo atenea_academic_h(atenea_academic_badge_class((string) $payment['status'])); ?>"><?php echo atenea_academic_h(atenea_academic_status_label((string) $payment['status'])); ?></span></td>
            <td>
              <?php if (!empty($payment['dte_pdf_path'])): ?>
                <a class="btn btn-sm btn-outline-primary" href="academic_payment_dte_download.php?payment_id=<?php echo (int) $payment['id']; ?>">Descargar PDF</a>
              <?php elseif ((string) $payment['status'] === 'paid'): ?>
                <span class="text-muted">Pendiente</span>
              <?php else: ?>
                <span class="text-muted">No disponible</span>
              <?php endif; ?>
            </td>
            <td><?php echo atenea_academic_h($payment['paid_at'] ?: $payment['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
