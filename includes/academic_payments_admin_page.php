<?php

require_once __DIR__ . '/academic_payments.php';
require_once __DIR__ . '/invoice_mailer.php';
require_once __DIR__ . '/dte/bootstrap.php';

if (!function_exists('atenea_academic_admin_redirect')) {
    function atenea_academic_admin_redirect(string $target): void
    {
        header('Location: ' . $target);
        exit;
    }
}

if (!function_exists('atenea_academic_admin_fetch_plans')) {
    function atenea_academic_admin_fetch_plans(mysqli $db): array
    {
        $result = mysqli_query(
            $db,
            "SELECT p.*, cy.name AS cycle_name, g.G_NAME
             FROM academic_payment_plans p
             JOIN academic_cycles cy ON cy.id = p.cycle_id
             LEFT JOIN grados g ON g.G_ID = p.grade_id
             ORDER BY p.is_active DESC, cy.id DESC, p.name ASC"
        );
        $rows = [];
        while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
            $rows[] = $row;
        }
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        return $rows;
    }
}

if (!function_exists('atenea_academic_admin_fetch_grades')) {
    function atenea_academic_admin_fetch_grades(mysqli $db): array
    {
        $result = mysqli_query($db, 'SELECT G_ID, G_NAME FROM grados WHERE G_ESTADO = 1 ORDER BY G_NAME ASC');
        $rows = [];
        while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
            $rows[] = $row;
        }
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        return $rows;
    }
}

if (!function_exists('atenea_academic_admin_prepare')) {
    function atenea_academic_admin_prepare(mysqli $db, string $page): array
    {
        atenea_academic_assert_tables($db);
        atenea_academic_require_role(['Admin', 'SuperAdmin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = trim((string) ($_POST['action'] ?? ''));

            try {
                atenea_require_csrf_token('academic_payments_admin', (string) ($_POST['csrf_token'] ?? ''));

                if ($action === 'create_cycle') {
                    $name = trim((string) ($_POST['name'] ?? ''));
                    $startsOn = trim((string) ($_POST['starts_on'] ?? ''));
                    $endsOn = trim((string) ($_POST['ends_on'] ?? ''));
                    if ($name === '' || strlen($name) > 100) {
                        throw new RuntimeException('Ingresa un nombre valido para el ciclo.');
                    }

                    $stmt = $db->prepare('INSERT INTO academic_cycles (name, starts_on, ends_on, is_active) VALUES (?, NULLIF(?, \'\'), NULLIF(?, \'\'), 1)');
                    if (!$stmt) {
                        throw new RuntimeException('No se pudo preparar el ciclo.');
                    }
                    $stmt->bind_param('sss', $name, $startsOn, $endsOn);
                    $stmt->execute();
                    $stmt->close();
                    atenea_academic_flash_set('success', 'Ciclo academico creado.');
                }

                if ($action === 'create_plan') {
                    $cycleId = (int) ($_POST['cycle_id'] ?? 0);
                    $gradeId = (int) ($_POST['grade_id'] ?? 0);
                    $name = trim((string) ($_POST['name'] ?? ''));
                    $enrollment = atenea_academic_money($_POST['enrollment_amount'] ?? 0);
                    $monthly = atenea_academic_money($_POST['monthly_amount'] ?? 0);
                    $dueDay = max(1, min(28, (int) ($_POST['monthly_due_day'] ?? 10)));
                    if ($cycleId <= 0 || $name === '' || strlen($name) > 120) {
                        throw new RuntimeException('Completa el ciclo y nombre del plan.');
                    }

                    $gradeValue = $gradeId > 0 ? $gradeId : null;
                    $stmt = $db->prepare(
                        'INSERT INTO academic_payment_plans (cycle_id, grade_id, name, enrollment_amount, monthly_amount, monthly_due_day, is_active)
                         VALUES (?, ?, ?, ?, ?, ?, 1)'
                    );
                    if (!$stmt) {
                        throw new RuntimeException('No se pudo preparar el plan.');
                    }
                    $stmt->bind_param('iisddi', $cycleId, $gradeValue, $name, $enrollment, $monthly, $dueDay);
                    $stmt->execute();
                    $stmt->close();
                    atenea_academic_flash_set('success', 'Plan de pago creado.');
                }

                if ($action === 'create_charge') {
                    $studentId = (int) ($_POST['student_id'] ?? 0);
                    $cycleId = (int) ($_POST['cycle_id'] ?? 0);
                    $planId = (int) ($_POST['plan_id'] ?? 0);
                    $chargeType = trim((string) ($_POST['charge_type'] ?? 'monthly'));
                    $periodLabel = trim((string) ($_POST['period_label'] ?? ''));
                    $description = trim((string) ($_POST['description'] ?? ''));
                    $amount = atenea_academic_money($_POST['amount'] ?? 0);
                    $discount = atenea_academic_money($_POST['discount_amount'] ?? 0);
                    $penalty = atenea_academic_money($_POST['penalty_amount'] ?? 0);
                    $dueDate = trim((string) ($_POST['due_date'] ?? ''));
                    if ($studentId <= 0 || $cycleId <= 0 || $periodLabel === '' || $description === '' || $amount <= 0) {
                        throw new RuntimeException('Completa estudiante, ciclo, periodo, descripcion y monto.');
                    }
                    if (!in_array($chargeType, ['enrollment', 'monthly', 'other'], true)) {
                        throw new RuntimeException('Tipo de cargo no valido.');
                    }

                    $planValue = $planId > 0 ? $planId : null;
                    $createdBy = (int) ($_SESSION['MEMBER_ID'] ?? 0);
                    $stmt = $db->prepare(
                        "INSERT INTO academic_charges
                         (student_id, cycle_id, plan_id, charge_type, period_label, description, amount, discount_amount, penalty_amount, due_date, status, created_by)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULLIF(?, ''), 'pending', ?)"
                    );
                    if (!$stmt) {
                        throw new RuntimeException('No se pudo preparar el cargo.');
                    }
                    $stmt->bind_param('iiisssdddsi', $studentId, $cycleId, $planValue, $chargeType, $periodLabel, $description, $amount, $discount, $penalty, $dueDate, $createdBy);
                    $stmt->execute();
                    $chargeId = (int) $db->insert_id;
                    $stmt->close();
                    atenea_academic_refresh_charge_status($db, $chargeId);
                    atenea_academic_flash_set('success', 'Cargo academico creado.');
                }

                if ($action === 'manual_payment') {
                    $chargeId = (int) ($_POST['charge_id'] ?? 0);
                    $amount = atenea_academic_money($_POST['amount'] ?? 0);
                    $payerName = trim((string) ($_POST['payer_name'] ?? ''));
                    $payerEmail = trim((string) ($_POST['payer_email'] ?? ''));
                    $notes = trim((string) ($_POST['notes'] ?? ''));
                    if ($chargeId <= 0 || $amount <= 0 || $payerName === '' || !filter_var($payerEmail, FILTER_VALIDATE_EMAIL)) {
                        throw new RuntimeException('Completa los datos del pago manual.');
                    }

                    mysqli_begin_transaction($db);
                    try {
                        $stmtCharge = $db->prepare('SELECT student_id, cycle_id, amount, discount_amount, penalty_amount, paid_amount FROM academic_charges WHERE id = ? AND status IN (\'pending\',\'partial\',\'overdue\') LIMIT 1 FOR UPDATE');
                        if (!$stmtCharge) {
                            throw new RuntimeException('No se pudo consultar el cargo.');
                        }
                        $stmtCharge->bind_param('i', $chargeId);
                        $stmtCharge->execute();
                        $chargeResult = $stmtCharge->get_result();
                        $charge = $chargeResult instanceof mysqli_result ? $chargeResult->fetch_assoc() : null;
                        if ($chargeResult instanceof mysqli_result) {
                            mysqli_free_result($chargeResult);
                        }
                        $stmtCharge->close();
                        if (!$charge) {
                            throw new RuntimeException('El cargo no esta disponible para pago.');
                        }
                        $balance = atenea_academic_charge_balance($charge);
                        if ($amount > $balance) {
                            $amount = $balance;
                        }

                        $method = 'cash';
                        $status = 'pending_payment';
                        $createdBy = (int) ($_SESSION['MEMBER_ID'] ?? 0);
                        $stmtPayment = $db->prepare(
                            'INSERT INTO academic_payments (student_id, cycle_id, user_id, payment_method, amount, status, payer_name, payer_email, notes, created_by)
                             VALUES (?, ?, NULL, ?, ?, ?, ?, ?, NULLIF(?, \'\'), ?)'
                        );
                        if (!$stmtPayment) {
                            throw new RuntimeException('No se pudo registrar el pago.');
                        }
                        $stmtPayment->bind_param('iisdssssi', $charge['student_id'], $charge['cycle_id'], $method, $amount, $status, $payerName, $payerEmail, $notes, $createdBy);
                        $stmtPayment->execute();
                        $paymentId = (int) $db->insert_id;
                        $stmtPayment->close();

                        $stmtDetail = $db->prepare('INSERT INTO academic_payment_details (payment_id, charge_id, amount) VALUES (?, ?, ?)');
                        if (!$stmtDetail) {
                            throw new RuntimeException('No se pudo registrar el detalle.');
                        }
                        $stmtDetail->bind_param('iid', $paymentId, $chargeId, $amount);
                        $stmtDetail->execute();
                        $stmtDetail->close();
                        mysqli_commit($db);
                        atenea_academic_apply_paid_payment($db, $paymentId);

                        try {
                            $dteDocument = DteAcademicService::generateForPayment($db, $paymentId, [
                                'user_id' => (int) ($_SESSION['MEMBER_ID'] ?? 0),
                            ]);

                            if (!empty($dteDocument['pdf_available'])) {
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
                                            'id' => $paymentId,
                                            'billing_name' => $payerName,
                                            'billing_email' => $payerEmail,
                                            'total_amount' => $amount,
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
                                }
                            }
                        } catch (Throwable $dteException) {
                            error_log('[DTE Academico] Fallo para pago manual #' . $paymentId . ': ' . $dteException->getMessage());
                        }
                    } catch (Throwable $exception) {
                        mysqli_rollback($db);
                        throw $exception;
                    }

                    atenea_academic_flash_set('success', 'Pago manual registrado.');
                }
            } catch (Throwable $exception) {
                atenea_academic_flash_set('danger', $exception->getMessage());
            }

            atenea_academic_admin_redirect($page);
        }

        return [
            'csrf' => atenea_csrf_token('academic_payments_admin'),
            'flash' => atenea_academic_flash_pull(),
            'cycles' => atenea_academic_fetch_cycles($db),
            'grades' => atenea_academic_admin_fetch_grades($db),
            'students' => atenea_academic_fetch_students($db),
            'plans' => atenea_academic_admin_fetch_plans($db),
            'charges' => atenea_academic_fetch_charges($db, null, false),
        ];
    }
}

if (!function_exists('atenea_academic_admin_render')) {
    function atenea_academic_admin_render(array $state): void
    {
        $csrf = (string) $state['csrf'];
        ?>
        <?php if ($state['flash']): ?>
          <div class="alert alert-<?php echo atenea_academic_h($state['flash']['type'] ?? 'info'); ?>"><?php echo atenea_academic_h($state['flash']['message'] ?? ''); ?></div>
        <?php endif; ?>

        <div class="row">
          <div class="col-lg-4 mb-4">
            <div class="card shadow border-0 h-100">
              <div class="card-header py-3"><h5 class="mb-0">Nuevo ciclo</h5></div>
              <div class="card-body">
                <form method="post" data-atenea-loading-form>
                  <input type="hidden" name="csrf_token" value="<?php echo atenea_academic_h($csrf); ?>">
                  <input type="hidden" name="action" value="create_cycle">
                  <div class="form-group"><label>Nombre</label><input class="form-control" name="name" maxlength="100" required placeholder="Ciclo 2026"></div>
                  <div class="form-group"><label>Inicio</label><input class="form-control" type="date" name="starts_on"></div>
                  <div class="form-group"><label>Fin</label><input class="form-control" type="date" name="ends_on"></div>
                  <button class="btn btn-primary btn-block">Crear ciclo</button>
                </form>
              </div>
            </div>
          </div>
          <div class="col-lg-8 mb-4">
            <div class="card shadow border-0 h-100">
              <div class="card-header py-3"><h5 class="mb-0">Nuevo plan de pago</h5></div>
              <div class="card-body">
                <form method="post" class="row" data-atenea-loading-form>
                  <input type="hidden" name="csrf_token" value="<?php echo atenea_academic_h($csrf); ?>">
                  <input type="hidden" name="action" value="create_plan">
                  <div class="form-group col-md-4"><label>Ciclo</label><select class="form-control" name="cycle_id" required><?php foreach ($state['cycles'] as $cycle): ?><option value="<?php echo (int) $cycle['id']; ?>"><?php echo atenea_academic_h($cycle['name']); ?></option><?php endforeach; ?></select></div>
                  <div class="form-group col-md-4"><label>Grado</label><select class="form-control" name="grade_id"><option value="">General</option><?php foreach ($state['grades'] as $grade): ?><option value="<?php echo (int) $grade['G_ID']; ?>"><?php echo atenea_academic_h($grade['G_NAME']); ?></option><?php endforeach; ?></select></div>
                  <div class="form-group col-md-4"><label>Nombre</label><input class="form-control" name="name" maxlength="120" required></div>
                  <div class="form-group col-md-4"><label>Matricula</label><input class="form-control" type="number" min="0" step="0.01" name="enrollment_amount" required></div>
                  <div class="form-group col-md-4"><label>Mensualidad</label><input class="form-control" type="number" min="0" step="0.01" name="monthly_amount" required></div>
                  <div class="form-group col-md-4"><label>Dia vencimiento</label><input class="form-control" type="number" min="1" max="28" name="monthly_due_day" value="10" required></div>
                  <div class="col-12"><button class="btn btn-primary">Guardar plan</button></div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <div class="card shadow border-0 mb-4">
          <div class="card-header py-3"><h5 class="mb-0">Crear cargo a estudiante</h5></div>
          <div class="card-body">
            <form method="post" class="row" data-atenea-loading-form>
              <input type="hidden" name="csrf_token" value="<?php echo atenea_academic_h($csrf); ?>">
              <input type="hidden" name="action" value="create_charge">
              <div class="form-group col-md-4"><label>Estudiante</label><select class="form-control" name="student_id" required><?php foreach ($state['students'] as $student): ?><option value="<?php echo (int) $student['ESTUDIANTE_ID']; ?>"><?php echo atenea_academic_h(trim($student['apellidos_estudiante'] . ', ' . $student['nombres_estudiante']) . ' - ' . ($student['G_NAME'] ?: 'Sin grado')); ?></option><?php endforeach; ?></select></div>
              <div class="form-group col-md-3"><label>Ciclo</label><select class="form-control" name="cycle_id" required><?php foreach ($state['cycles'] as $cycle): ?><option value="<?php echo (int) $cycle['id']; ?>"><?php echo atenea_academic_h($cycle['name']); ?></option><?php endforeach; ?></select></div>
              <div class="form-group col-md-3"><label>Plan</label><select class="form-control" name="plan_id"><option value="">Sin plan</option><?php foreach ($state['plans'] as $plan): ?><option value="<?php echo (int) $plan['id']; ?>"><?php echo atenea_academic_h($plan['name']); ?></option><?php endforeach; ?></select></div>
              <div class="form-group col-md-2"><label>Tipo</label><select class="form-control" name="charge_type"><option value="enrollment">Matricula</option><option value="monthly">Mensualidad</option><option value="other">Otro</option></select></div>
              <div class="form-group col-md-3"><label>Periodo</label><input class="form-control" name="period_label" maxlength="80" required placeholder="Enero 2026"></div>
              <div class="form-group col-md-5"><label>Descripcion</label><input class="form-control" name="description" maxlength="180" required placeholder="Mensualidad enero"></div>
              <div class="form-group col-md-2"><label>Monto</label><input class="form-control" type="number" min="0.01" step="0.01" name="amount" required></div>
              <div class="form-group col-md-2"><label>Vence</label><input class="form-control" type="date" name="due_date"></div>
              <div class="form-group col-md-2"><label>Descuento</label><input class="form-control" type="number" min="0" step="0.01" name="discount_amount" value="0"></div>
              <div class="form-group col-md-2"><label>Mora</label><input class="form-control" type="number" min="0" step="0.01" name="penalty_amount" value="0"></div>
              <div class="col-12"><button class="btn btn-success">Crear cargo</button></div>
            </form>
          </div>
        </div>

        <div class="card shadow border-0 mb-4">
          <div class="card-header py-3"><h5 class="mb-0">Estado de cuenta academico</h5></div>
          <div class="card-body table-responsive">
            <table class="table table-bordered js-datatable" width="100%">
              <thead><tr><th>Estudiante</th><th>Ciclo</th><th>Concepto</th><th>Vence</th><th>Total</th><th>Pagado</th><th>Saldo</th><th>Estado</th><th>Pago manual</th></tr></thead>
              <tbody>
              <?php foreach ($state['charges'] as $charge): ?>
                <tr>
                  <td><?php echo atenea_academic_h(trim($charge['nombres_estudiante'] . ' ' . $charge['apellidos_estudiante'])); ?></td>
                  <td><?php echo atenea_academic_h($charge['cycle_name']); ?></td>
                  <td><?php echo atenea_academic_h($charge['description'] . ' - ' . $charge['period_label']); ?></td>
                  <td><?php echo atenea_academic_h($charge['due_date'] ?: 'Sin fecha'); ?></td>
                  <td>$<?php echo number_format((float) $charge['total_amount'], 2); ?></td>
                  <td>$<?php echo number_format((float) $charge['paid_amount'], 2); ?></td>
                  <td>$<?php echo number_format((float) $charge['balance'], 2); ?></td>
                  <td><span class="badge <?php echo atenea_academic_h(atenea_academic_badge_class((string) $charge['status'])); ?>"><?php echo atenea_academic_h(atenea_academic_status_label((string) $charge['status'])); ?></span></td>
                  <td>
                    <?php if ((float) $charge['balance'] > 0 && in_array($charge['status'], ['pending', 'partial', 'overdue'], true)): ?>
                    <form method="post" class="d-flex flex-wrap" style="gap:6px; min-width:360px;" data-atenea-loading-form>
                      <input type="hidden" name="csrf_token" value="<?php echo atenea_academic_h($csrf); ?>">
                      <input type="hidden" name="action" value="manual_payment">
                      <input type="hidden" name="charge_id" value="<?php echo (int) $charge['id']; ?>">
                      <input class="form-control form-control-sm" style="width:95px" type="number" min="0.01" max="<?php echo atenea_academic_h($charge['balance']); ?>" step="0.01" name="amount" value="<?php echo atenea_academic_h($charge['balance']); ?>" required>
                      <input class="form-control form-control-sm" style="width:130px" name="payer_name" maxlength="150" placeholder="Pagador" value="<?php echo atenea_academic_h(trim($charge['nombres_estudiante'] . ' ' . $charge['apellidos_estudiante'])); ?>" required>
                      <input class="form-control form-control-sm" style="width:150px" type="email" name="payer_email" maxlength="150" placeholder="Correo" value="<?php echo atenea_academic_h($charge['correo_estudiante']); ?>" required>
                      <button class="btn btn-sm btn-primary">Registrar</button>
                    </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php
    }
}
