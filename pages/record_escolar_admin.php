<?php
require 'session.php';
require_once '../includes/connection.php';
require_once '../includes/material_dashboard.php';
require_once '../includes/atenea_admin.php';
require_once '../includes/atenea_capacitacion.php';

atenea_backoffice_require($db);

if (!function_exists('atenea_record_admin_h')) {
    function atenea_record_admin_h($value): string
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
$enrollments = atenea_capacitacion_fetch_admin_enrollments($db, $selectedProgramId);
$rows = [];
$certificateCount = 0;
$finalizedCount = 0;

foreach ($enrollments as $index => $enrollment) {
    $updatedEnrollment = atenea_capacitacion_recalculate_enrollment_progress($db, (int) ($enrollment['id'] ?? 0));
    if ($updatedEnrollment) {
        $enrollment = $updatedEnrollment;
    }

    if ($selectedUserId > 0 && (int) ($enrollment['user_id'] ?? 0) !== $selectedUserId) {
        continue;
    }

    if ((string) ($enrollment['estado_curso'] ?? '') === 'finalizado') {
        $finalizedCount++;
    }

    if (atenea_capacitacion_certificate_eligible($enrollment)) {
        $certificateCount++;
    }

    $rows[] = $enrollment;
}
?>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card border-left-primary shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Registros</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($rows); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-info shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Cursos finalizados</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $finalizedCount; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-success shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Certificados disponibles</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $certificateCount; ?></div>
            </div>
        </div>
    </div>
</div>

<?php if ($rows === []) : ?>
    <div class="alert alert-info">No hay registros escolares para los filtros seleccionados.</div>
<?php endif; ?>

<div class="row">
    <?php foreach ($rows as $enrollment) : ?>
        <?php
        $certificateAvailable = atenea_capacitacion_certificate_eligible($enrollment);
        $templateVersion = atenea_certificate_template_version(atenea_capacitacion_certificate_payload($enrollment));
        $pdfUrl = 'certificado_curso_pdf.php?' . http_build_query([
            'enrollment_id' => (int) ($enrollment['id'] ?? 0),
            'template_v' => $templateVersion,
        ]);
        ?>
        <div class="col-12 col-xl-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center mb-3" style="gap: 0.5rem;">
                        <span class="badge badge-primary"><?php echo atenea_record_admin_h((string) ($enrollment['estado_curso'] ?? 'pendiente')); ?></span>
                        <span class="badge badge-info"><?php echo atenea_record_admin_h((string) ($enrollment['estado_aprobacion'] ?? 'pendiente')); ?></span>
                        <?php if ($certificateAvailable) : ?>
                            <span class="badge badge-success">Certificado habilitado</span>
                        <?php endif; ?>
                    </div>

                    <h5 class="mb-2"><?php echo atenea_record_admin_h((string) ($enrollment['programa_titulo'] ?? 'Curso')); ?></h5>
                    <p class="mb-2"><strong>Estudiante:</strong> <?php echo atenea_record_admin_h(atenea_capacitacion_enrollment_full_name($enrollment)); ?></p>
                    <p class="mb-2"><strong>Correo:</strong> <?php echo atenea_record_admin_h((string) ($enrollment['EMAIL'] ?? 'Sin correo')); ?></p>
                    <p class="mb-2"><strong>Progreso:</strong> <?php echo atenea_record_admin_h((string) ($enrollment['progreso'] ?? '0')); ?>%</p>
                    <p class="mb-2"><strong>Fecha de inscripcion:</strong> <?php echo atenea_record_admin_h(trim((string) ($enrollment['fecha_inscripcion'] ?? '')) !== '' ? date('d/m/Y h:i A', strtotime((string) $enrollment['fecha_inscripcion'])) : 'No disponible'); ?></p>
                    <p class="mb-2"><strong>Fecha de finalizacion:</strong> <?php echo atenea_record_admin_h(trim((string) ($enrollment['fecha_finalizacion'] ?? '')) !== '' ? date('d/m/Y h:i A', strtotime((string) $enrollment['fecha_finalizacion'])) : 'Pendiente'); ?></p>
                    <p class="mb-3"><strong>Fecha de aprobacion:</strong> <?php echo atenea_record_admin_h(trim((string) ($enrollment['fecha_aprobacion'] ?? '')) !== '' ? date('d/m/Y h:i A', strtotime((string) $enrollment['fecha_aprobacion'])) : 'Pendiente'); ?></p>

                    <div class="d-flex flex-wrap" style="gap: 0.4rem;">
                        <a class="btn btn-outline-primary btn-sm" href="estudiante_usuario.php?id=<?php echo (int) ($enrollment['user_id'] ?? 0); ?>">Perfil</a>
                        <a class="btn btn-outline-success btn-sm" href="curso_certificados_admin.php?programa_id=<?php echo (int) ($enrollment['programa_id'] ?? 0); ?>">Certificados</a>
                        <?php if ($certificateAvailable) : ?>
                            <a class="btn btn-primary btn-sm" href="<?php echo atenea_record_admin_h($pdfUrl); ?>" target="_blank" rel="noopener">Ver PDF</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include '../includes/footer.php'; ?>
