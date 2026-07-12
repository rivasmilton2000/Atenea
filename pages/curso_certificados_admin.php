<?php
require 'session.php';
require_once '../includes/connection.php';
require_once '../includes/material_dashboard.php';
require_once '../includes/atenea_capacitacion.php';
require_once '../includes/certificate_renderer.php';

dashboard_require_role(
    $db,
    ['Admin', 'SuperAdmin'],
    [
        'Personal' => 'empleados_vista.php',
        'Estudiante' => 'estudiante_vista.php',
        'Docente' => 'docentes_vista.php',
    ]
);

$currentRole = (string) ($_SESSION['TYPE'] ?? '');
include $currentRole === 'SuperAdmin'
    ? '../includes/sidebar_superadmin.php'
    : '../includes/sidebar_admin.php';

if (!atenea_capacitacion_phase_three_ready($db)) {
    atenea_render_auth_alert(
        'warning',
        'Migracion pendiente',
        'El modulo de certificados no esta disponible. Revisa la configuracion del entorno.',
        'dashboard_admin.php'
    );
}

$selectedProgramId = max(0, (int) ($_GET['programa_id'] ?? 0));
$certificatePreviewToken = time();
$programas = [];
$enrollments = atenea_capacitacion_fetch_admin_enrollments($db, $selectedProgramId);
$pendingApprovalCount = 0;
$approvedCount = 0;
$certificateCount = 0;
$finalizedCount = 0;

if (atenea_db_has_table($db, 'programas_educativos')) {
    $programResult = mysqli_query($db, "SELECT id, titulo, estado FROM programas_educativos ORDER BY orden ASC, id ASC") or die(mysqli_error($db));
    while ($programRow = mysqli_fetch_assoc($programResult)) {
        $programas[] = $programRow;
    }
}

foreach ($enrollments as $index => $enrollment) {
    $updatedEnrollment = atenea_capacitacion_recalculate_enrollment_progress($db, (int) ($enrollment['id'] ?? 0));
    if ($updatedEnrollment) {
        $enrollments[$index] = $updatedEnrollment;
        $enrollment = $updatedEnrollment;
    }

    $courseStatus = atenea_capacitacion_normalize_course_status((string) ($enrollment['estado_curso'] ?? ''));
    $approvalStatus = atenea_capacitacion_normalize_approval_status((string) ($enrollment['estado_aprobacion'] ?? ''));

    if ($courseStatus === 'finalizado') {
        $finalizedCount++;
    }

    if ($courseStatus === 'finalizado' && $approvalStatus !== 'aprobado') {
        $pendingApprovalCount++;
    }

    if ($approvalStatus === 'aprobado') {
        $approvedCount++;
    }

    if (atenea_capacitacion_certificate_eligible($enrollment)) {
        $certificateCount++;
    }
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 1rem;">
        <h4 class="m-0 font-weight-bold text-primary">Finalizacion y certificados de capacitacion</h4>
        <form method="get" class="form-inline">
            <label class="sr-only" for="programaFiltroCertificados">Curso</label>
            <select class="form-control mr-2" name="programa_id" id="programaFiltroCertificados">
                <option value="0">Todos los cursos</option>
                <?php foreach ($programas as $programa) : ?>
                    <option value="<?php echo (int) $programa['id']; ?>" <?php echo $selectedProgramId === (int) $programa['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-outline-primary">Filtrar</button>
        </form>
    </div>

    <div class="card-body">
        <div class="alert alert-info">
            Regla aplicada en esta fase: cuando un estudiante completa el 100% de los videos habilitados, el curso queda
            <strong>Finalizado</strong>. Luego, <strong>Admin o SuperAdmin</strong> puede marcarlo como <strong>Aprobado</strong> para habilitar
            el certificado y el PDF seguro.
        </div>

        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-left-primary shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Inscripciones</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($enrollments); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-left-info shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Finalizados</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $finalizedCount; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-left-warning shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pendientes de aprobacion</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pendingApprovalCount; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-left-success shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Certificados disponibles</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $certificateCount; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ESTUDIANTE</th>
                        <th>CURSO</th>
                        <th>PROGRESO</th>
                        <th>ESTADO CURSO</th>
                        <th>APROBACION</th>
                        <th>FINALIZACION</th>
                        <th>CERTIFICADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $enrollment) : ?>
                        <?php
                        $courseStatusMeta = atenea_capacitacion_course_status_meta((string) ($enrollment['estado_curso'] ?? ''));
                        $approvalStatusMeta = atenea_capacitacion_approval_status_meta((string) ($enrollment['estado_aprobacion'] ?? ''));
                        $progress = atenea_capacitacion_progress_percentage($enrollment['progreso'] ?? 0);
                        $certificateAvailable = atenea_capacitacion_certificate_eligible($enrollment);
                        $studentName = atenea_capacitacion_enrollment_full_name($enrollment);
                        $templateVersion = atenea_certificate_template_version(atenea_capacitacion_certificate_payload($enrollment));
                        $pdfInlineUrl = 'certificado_curso_pdf.php?' . http_build_query([
                            'enrollment_id' => (int) $enrollment['id'],
                            'template_v' => $templateVersion,
                            'preview_token' => $certificatePreviewToken,
                        ]);
                        $pdfRegenerateUrl = 'certificado_curso_pdf.php?' . http_build_query([
                            'enrollment_id' => (int) $enrollment['id'],
                            'download' => 1,
                            'regenerate' => 1,
                            'template_v' => $templateVersion,
                            'preview_token' => $certificatePreviewToken,
                        ]);
                        ?>
                        <tr>
                            <td><?php echo (int) $enrollment['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                <small><?php echo htmlspecialchars((string) ($enrollment['EMAIL'] ?? 'Sin correo'), ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars((string) ($enrollment['programa_titulo'] ?? 'Curso'), ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                <small><?php echo htmlspecialchars(atenea_capacitacion_type_label((string) ($enrollment['tipo_programa'] ?? 'curso')), ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td>
                                <?php echo $progress; ?>%<br>
                                <small>Generado: <?php echo (int) ($enrollment['certificate_regenerated_count'] ?? 0); ?> vez/veces</small>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo htmlspecialchars((string) $courseStatusMeta['class'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars((string) $courseStatusMeta['label'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo htmlspecialchars((string) $approvalStatusMeta['class'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars((string) $approvalStatusMeta['label'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars(trim((string) ($enrollment['fecha_finalizacion'] ?? '')) !== '' ? date('d/m/Y h:i A', strtotime((string) $enrollment['fecha_finalizacion'])) : 'Pendiente', ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                            <td>
                                <?php if ($certificateAvailable) : ?>
                                    <span class="badge badge-success">Disponible</span><br>
                                    <small><?php echo htmlspecialchars(trim((string) ($enrollment['certificado_generado_at'] ?? '')) !== '' ? date('d/m/Y h:i A', strtotime((string) $enrollment['certificado_generado_at'])) : 'Sin generar', ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php else : ?>
                                    <span class="badge badge-secondary">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td style="min-width: 250px;">
                                <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                                    <?php if (atenea_capacitacion_normalize_course_status((string) ($enrollment['estado_curso'] ?? '')) !== 'finalizado') : ?>
                                        <form method="post" action="curso_certificados_transac.php?action=mark_finalized" class="mb-0">
                                            <input type="hidden" name="enrollment_id" value="<?php echo (int) $enrollment['id']; ?>">
                                            <button type="submit" class="btn btn-outline-info btn-sm">Marcar finalizado</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (atenea_capacitacion_normalize_approval_status((string) ($enrollment['estado_aprobacion'] ?? '')) !== 'aprobado') : ?>
                                        <form method="post" action="curso_certificados_transac.php?action=approve" class="mb-0">
                                            <input type="hidden" name="enrollment_id" value="<?php echo (int) $enrollment['id']; ?>">
                                            <button type="submit" class="btn btn-success btn-sm">Aprobar</button>
                                        </form>
                                    <?php else : ?>
                                        <form method="post" action="curso_certificados_transac.php?action=reset_approval" class="mb-0">
                                            <input type="hidden" name="enrollment_id" value="<?php echo (int) $enrollment['id']; ?>">
                                            <button type="submit" class="btn btn-outline-warning btn-sm">Volver a proceso</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($certificateAvailable) : ?>
                                        <a href="<?php echo htmlspecialchars($pdfInlineUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm">Ver PDF</a>
                                        <a href="<?php echo htmlspecialchars($pdfRegenerateUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">Regenerar PDF</a>
                                    <?php endif; ?>
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
