<?php
require 'session.php';
require_once '../includes/connection.php';
require_once '../includes/material_dashboard.php';
require_once '../includes/atenea_admin.php';
require_once '../includes/atenea_capacitacion.php';

atenea_backoffice_require($db);


if (!function_exists('atenea_enrollments_h')) {
    function atenea_enrollments_h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$currentRole = (string) ($_SESSION['TYPE'] ?? '');
include $currentRole === 'SuperAdmin'
    ? '../includes/sidebar_superadmin.php'
    : '../includes/sidebar_admin.php';

$selectedProgramId = max(0, (int) ($_GET['programa_id'] ?? 0));
$selectedUserId = max(0, (int) ($_GET['user_id'] ?? 0));
$search = trim((string) ($_GET['q'] ?? ''));
$programas = [];
$programResult = mysqli_query($db, 'SELECT id, titulo FROM programas_educativos ORDER BY orden ASC, id ASC') or die(mysqli_error($db));
while ($programRow = mysqli_fetch_assoc($programResult)) {
    $programas[] = $programRow;
}

$enrollments = atenea_capacitacion_fetch_admin_enrollments($db, $selectedProgramId);
$paymentRequests = atenea_capacitacion_fetch_admin_payments($db);
$rows = [];
$activeCount = 0;
$approvedCount = 0;

foreach ($enrollments as $enrollment) {
    if ($selectedUserId > 0 && (int) ($enrollment['user_id'] ?? 0) !== $selectedUserId) {
        continue;
    }

    $studentName = atenea_capacitacion_enrollment_full_name($enrollment);
    $haystack = strtolower(
        $studentName . ' ' .
        (string) ($enrollment['EMAIL'] ?? '') . ' ' .
        (string) ($enrollment['programa_titulo'] ?? '')
    );

    if ($search !== '' && strpos($haystack, strtolower($search)) === false) {
        continue;
    }

    if (in_array((string) ($enrollment['estado_curso'] ?? ''), ['curso_activo', 'activo'], true)
        || in_array((string) ($enrollment['estado_aprobacion'] ?? ''), ['en_proceso', 'aprobado'], true)) {
        $activeCount++;
    }

    if ((string) ($enrollment['estado_aprobacion'] ?? '') === 'aprobado') {
        $approvedCount++;
    }

    $rows[] = $enrollment;
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3"><h4 class="m-0 font-weight-bold text-primary">Pagos directos de capacitacion</h4></div>
    <div class="card-body"><div class="table-responsive"><table class="table table-bordered">
        <thead><tr><th>Estudiante</th><th>Curso</th><th>Fecha</th><th>Monto</th><th>Pago</th><th>Inscripcion</th><th>Accion</th></tr></thead>
        <tbody>
        <?php foreach ($paymentRequests as $payment) : $meta = atenea_capacitacion_payment_status_meta((string)$payment['status']); ?>
            <tr>
                <td><strong><?php echo atenea_enrollments_h(trim($payment['FIRST_NAME'].' '.$payment['LAST_NAME'])); ?></strong><br><small><?php echo atenea_enrollments_h($payment['EMAIL']); ?></small></td>
                <td><?php echo atenea_enrollments_h($payment['programa_titulo']); ?></td>
                <td class="text-nowrap"><?php echo atenea_enrollments_h(date('d/m/Y H:i', strtotime((string)$payment['created_at']))); ?></td>
                <td>$<?php echo number_format((float)$payment['amount'], 2); ?></td>
                <td><span class="badge badge-<?php echo atenea_enrollments_h($meta['class']); ?>"><?php echo atenea_enrollments_h($meta['label']); ?></span></td>
                <td><?php echo !empty($payment['enrollment_id']) ? 'Activa' : 'Sin activar'; ?></td>
                <td><?php if(!empty($payment['order_id'])):?><a class="btn btn-outline-primary btn-sm" href="compras_admin.php?q=<?php echo (int)$payment['order_id']; ?>">Ver orden #<?php echo (int)$payment['order_id']; ?></a><?php else: ?>Sin orden<?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$paymentRequests) : ?><tr><td colspan="7" class="text-center text-muted">No hay solicitudes de pago.</td></tr><?php endif; ?>
        </tbody>
    </table></div></div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card border-left-primary shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Inscripciones</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($rows); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-success shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Activas</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $activeCount; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-info shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Aprobadas</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $approvedCount; ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 1rem;">
        <h4 class="m-0 font-weight-bold text-primary">Inscripciones de cursos y capacitaciones</h4>
        <form method="get" class="form-inline d-flex flex-wrap" style="gap: 0.5rem;">
            <select class="form-control" name="programa_id">
                <option value="0">Todos los cursos</option>
                <?php foreach ($programas as $programa) : ?>
                    <option value="<?php echo (int) $programa['id']; ?>" <?php echo $selectedProgramId === (int) $programa['id'] ? 'selected' : ''; ?>>
                        <?php echo atenea_enrollments_h((string) $programa['titulo']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="user_id" value="<?php echo (int) $selectedUserId; ?>">
            <input class="form-control" type="search" name="q" value="<?php echo atenea_enrollments_h($search); ?>" placeholder="Buscar estudiante o curso">
            <button type="submit" class="btn btn-outline-primary">Filtrar</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ESTUDIANTE</th>
                        <th>CURSO</th>
                        <th>PROGRESO</th>
                        <th>ESTADO CURSO</th>
                        <th>APROBACION</th>
                        <th>INSCRIPCION</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $enrollment) : ?>
                        <tr>
                            <td>
                                <strong><?php echo atenea_enrollments_h(atenea_capacitacion_enrollment_full_name($enrollment)); ?></strong><br>
                                <small><?php echo atenea_enrollments_h((string) ($enrollment['EMAIL'] ?? 'Sin correo')); ?></small>
                            </td>
                            <td><?php echo atenea_enrollments_h((string) ($enrollment['programa_titulo'] ?? 'Curso')); ?></td>
                            <td><?php echo atenea_enrollments_h((string) ($enrollment['progreso'] ?? '0')); ?>%</td>
                            <td><?php echo atenea_enrollments_h((string) ($enrollment['estado_curso'] ?? 'pendiente')); ?></td>
                            <td><?php echo atenea_enrollments_h((string) ($enrollment['estado_aprobacion'] ?? 'pendiente')); ?></td>
                            <td><?php echo atenea_enrollments_h(trim((string) ($enrollment['fecha_inscripcion'] ?? '')) !== '' ? date('d/m/Y h:i A', strtotime((string) $enrollment['fecha_inscripcion'])) : 'No disponible'); ?></td>
                            <td style="min-width: 220px;">
                                <div class="d-flex flex-wrap" style="gap: 0.4rem;">
                                    <a class="btn btn-outline-primary btn-sm" href="estudiante_usuario.php?id=<?php echo (int) ($enrollment['user_id'] ?? 0); ?>">Perfil</a>
                                    <a class="btn btn-outline-success btn-sm" href="record_escolar_admin.php?user_id=<?php echo (int) ($enrollment['user_id'] ?? 0); ?>">Record</a>
                                    <a class="btn btn-outline-dark btn-sm" href="curso_certificados_admin.php?programa_id=<?php echo (int) ($enrollment['programa_id'] ?? 0); ?>">Certificados</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
