<?php
require_once __DIR__ . '/session.php';
if (!logged_in()) {
    header('Location: ' . atenea_build_login_url('certificado_curso.php', 'login_required'));
    exit;
}

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';
require_once __DIR__ . '/../includes/atenea_capacitacion.php';
require_once __DIR__ . '/../includes/certificate_renderer.php';

if (!atenea_session_is_public_user()) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}

if (!function_exists('certificado_curso_format_date')) {
    function certificado_curso_format_date(string $value, string $fallback = 'No disponible'): string
    {
        $timestamp = strtotime(trim($value));

        return $timestamp === false ? $fallback : date('d/m/Y h:i A', $timestamp);
    }
}

if (!atenea_capacitacion_phase_three_ready($db)) {
    atenea_render_auth_alert(
        'warning',
        'Migracion pendiente',
        'El servicio de certificados no esta disponible temporalmente.',
        'record_escolar.php'
    );
}

$publicUserId = (int) ($_SESSION['PUBLIC_USER_ID'] ?? 0);
$requestedEnrollmentId = max(0, (int) ($_GET['enrollment_id'] ?? 0));
$enrollments = atenea_capacitacion_fetch_enrollments_for_public_user($db, $publicUserId);
$selectedEnrollment = null;
$requestedEnrollment = null;

foreach ($enrollments as $index => $enrollment) {
    $updatedEnrollment = atenea_capacitacion_recalculate_enrollment_progress($db, (int) ($enrollment['id'] ?? 0));
    if ($updatedEnrollment) {
        $enrollments[$index] = $updatedEnrollment;
        $enrollment = $updatedEnrollment;
    }

    if ((int) ($enrollment['id'] ?? 0) === $requestedEnrollmentId) {
        $requestedEnrollment = $enrollment;
    }

    if (!atenea_capacitacion_certificate_eligible($enrollment)) {
        continue;
    }

    if ($requestedEnrollmentId > 0) {
        if ((int) ($enrollment['id'] ?? 0) === $requestedEnrollmentId) {
            $selectedEnrollment = $enrollment;
            break;
        }
    } elseif ($selectedEnrollment === null) {
        $selectedEnrollment = $enrollment;
    }
}

if ($requestedEnrollmentId > 0 && $requestedEnrollment === null) {
    atenea_render_auth_alert(
        'warning',
        'Certificado no disponible',
        'No encontramos la inscripcion solicitada dentro de tu cuenta.',
        'record_escolar.php'
    );
}

$certificateData = [];
$certificateHtml = '';
$pdfDownloadUrl = 'certificado_curso_pdf.php';
$pdfInlineUrl = 'certificado_curso_pdf.php';
$certificateTemplateVersion = '0';
$certificatePreviewToken = time();
$studentFullName = trim((string) ($_SESSION['FIRST_NAME'] ?? '') . ' ' . (string) ($_SESSION['LAST_NAME'] ?? ''));
$courseStatusMeta = $selectedEnrollment ? atenea_capacitacion_course_status_meta((string) ($selectedEnrollment['estado_curso'] ?? '')) : ['label' => 'Pendiente', 'class' => 'secondary'];
$approvalStatusMeta = $selectedEnrollment ? atenea_capacitacion_approval_status_meta((string) ($selectedEnrollment['estado_aprobacion'] ?? '')) : ['label' => 'Pendiente', 'class' => 'secondary'];

if ($selectedEnrollment) {
    atenea_capacitacion_mark_certificate_generated($db, (int) $selectedEnrollment['id'], false);
    $certificateData = atenea_certificate_build_data(atenea_capacitacion_certificate_payload($selectedEnrollment));
    $certificateTemplateVersion = atenea_certificate_template_version($certificateData);
    $certificateHtml = atenea_certificate_html($certificateData);
    $studentFullName = atenea_capacitacion_enrollment_full_name($selectedEnrollment);
    $pdfDownloadUrl = 'certificado_curso_pdf.php?' . http_build_query([
        'enrollment_id' => (int) $selectedEnrollment['id'],
        'download' => 1,
        'template_v' => $certificateTemplateVersion,
        'preview_token' => $certificatePreviewToken,
    ]);
    $pdfInlineUrl = 'certificado_curso_pdf.php?' . http_build_query([
        'enrollment_id' => (int) $selectedEnrollment['id'],
        'template_v' => $certificateTemplateVersion,
        'preview_token' => $certificatePreviewToken,
    ]);
}

ob_start();
?>
<style>
  <?php echo atenea_certificate_preview_css(); ?>

  .atenea-certificate-user-page .atenea-certificate-shell {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 1.5rem;
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    background: #fff;
    overflow: hidden;
  }

  .atenea-certificate-user-page .atenea-certificate-empty {
    border: 1px dashed rgba(15, 23, 42, 0.16);
    border-radius: 1.25rem;
    background: #fff;
    padding: 2rem;
    text-align: center;
  }
</style>

<?php if ($selectedEnrollment) : ?>
  <div class="alert alert-success mb-4">
    Tu curso ya fue aprobado y el certificado esta disponible para visualizarse e imprimirse con tu nombre real.
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <p class="mb-2"><strong>Estudiante:</strong> <?php echo dashboard_h($studentFullName !== '' ? $studentFullName : atenea_capacitacion_enrollment_full_name($selectedEnrollment)); ?></p>
          <p class="mb-2"><strong>Curso:</strong> <?php echo dashboard_h((string) ($selectedEnrollment['programa_titulo'] ?? 'Curso aprobado')); ?></p>
          <p class="mb-2"><strong>Estado del curso:</strong> <?php echo dashboard_h((string) $courseStatusMeta['label']); ?></p>
        </div>
        <div class="col-md-6">
          <p class="mb-2"><strong>Aprobacion:</strong> <?php echo dashboard_h((string) $approvalStatusMeta['label']); ?></p>
          <p class="mb-2"><strong>Fecha de finalizacion:</strong> <?php echo dashboard_h(certificado_curso_format_date((string) ($selectedEnrollment['fecha_finalizacion'] ?? ''), 'Pendiente')); ?></p>
          <p class="mb-0"><strong>Fecha de aprobacion:</strong> <?php echo dashboard_h(certificado_curso_format_date((string) ($selectedEnrollment['fecha_aprobacion'] ?? ''), 'Pendiente')); ?></p>
        </div>
      </div>

      <div class="d-flex flex-wrap mt-4" style="gap: 0.75rem;">
        <a href="<?php echo dashboard_h($pdfDownloadUrl); ?>" class="btn btn-primary">Descargar PDF</a>
        <a href="<?php echo dashboard_h($pdfInlineUrl); ?>" target="_blank" rel="noopener" class="btn btn-outline-success">Abrir PDF</a>
        <button type="button" class="btn btn-outline-dark" onclick="window.print();">Imprimir</button>
        <a href="record_escolar.php" class="btn btn-outline-primary">Volver al record</a>
      </div>
    </div>
  </div>

  <div class="atenea-certificate-shell">
    <iframe class="atenea-certificate-pdf-frame" src="<?php echo dashboard_h($pdfInlineUrl); ?>" title="Vista previa del certificado"></iframe>
  </div>
<?php else : ?>
  <div class="atenea-certificate-empty">
    <h4 class="mb-3">Tu certificado aun no esta disponible</h4>
    <p class="text-muted mb-4">
      <?php if ($requestedEnrollment !== null) : ?>
        La inscripcion seleccionada todavia no tiene aprobacion final. Cuando administracion marque el curso como aprobado, el certificado aparecera aqui automaticamente.
      <?php else : ?>
        Cuando completes y aprueben tu curso, aqui podras ver y descargar tu certificado.
      <?php endif; ?>
    </p>
    <div class="d-flex flex-wrap justify-content-center" style="gap: 0.75rem;">
      <a href="record_escolar.php" class="btn btn-primary">Ver record escolar</a>
      <a href="mi_curso_activo.php" class="btn btn-outline-success">Mi curso activo</a>
    </div>
  </div>
<?php endif; ?>
<?php
$bodySectionsHtml = ob_get_clean();

$quickLinks = [
    ['label' => 'Mi curso activo', 'href' => 'mi_curso_activo.php', 'icon' => 'workspace_premium'],
    ['label' => 'Record escolar', 'href' => 'record_escolar.php', 'icon' => 'school'],
    ['label' => 'Videos del curso', 'href' => 'curso_videos.php', 'icon' => 'play_circle'],
];

if ($selectedEnrollment) {
    $quickLinks[] = ['label' => 'Descargar PDF', 'href' => $pdfDownloadUrl, 'icon' => 'picture_as_pdf'];
}

$heroActions = [
    ['label' => 'Record escolar', 'href' => 'record_escolar.php', 'icon' => 'school'],
    ['label' => 'Mi curso activo', 'href' => 'mi_curso_activo.php', 'icon' => 'workspace_premium', 'variant' => 'outline'],
];

if ($selectedEnrollment) {
    $heroActions[] = ['label' => 'Descargar PDF', 'href' => $pdfDownloadUrl, 'icon' => 'picture_as_pdf'];
}

dashboard_render_material_page([
    'bodyClass' => 'atenea-certificate-user-page',
    'pageTitle' => 'Mi certificado',
    'roleLabel' => 'Usuario registrado',
    'welcomeTitle' => $selectedEnrollment ? 'Certificado disponible' : 'Certificado en espera',
    'welcomeText' => $selectedEnrollment
        ? 'Esta vista muestra la plantilla final del certificado del curso aprobado y permite descargar el PDF con tus datos reales.'
        : 'Tu certificado aparecera automaticamente cuando el curso quede finalizado y aprobado.',
    'profileUrl' => 'usuario_vista.php',
    'logoutUrl' => 'logout.php?redirect=homepage.php',
    'navSections' => atenea_capacitacion_user_nav_sections('certificado_curso.php'),
    'cardsColumnClass' => 'col-12 col-md-6 col-xl-4 mb-4',
    'cards' => [
        ['title' => 'Certificados listos', 'value' => $selectedEnrollment ? '1' : '0', 'icon' => 'workspace_premium', 'accent' => 'success', 'href' => 'certificado_curso.php', 'metricLabel' => 'Disponibilidad actual', 'footerLabel' => 'Abrir vista'],
        ['title' => 'Aprobacion', 'value' => (string) $approvalStatusMeta['label'], 'icon' => 'verified', 'accent' => 'info', 'href' => 'record_escolar.php', 'metricLabel' => 'Estado final', 'footerLabel' => 'Ver record'],
        ['title' => 'PDF', 'value' => $selectedEnrollment ? 'Listo' : 'Pendiente', 'icon' => 'picture_as_pdf', 'accent' => 'warning', 'href' => $selectedEnrollment ? $pdfDownloadUrl : 'record_escolar.php', 'metricLabel' => 'Descarga protegida', 'footerLabel' => $selectedEnrollment ? 'Descargar' : 'Esperar aprobacion'],
    ],
    'quickLinks' => $quickLinks,
    'summaryItems' => [
        ['label' => 'Estudiante', 'value' => $studentFullName !== '' ? $studentFullName : 'Pendiente de perfil'],
        ['label' => 'Curso', 'value' => $selectedEnrollment ? (string) ($selectedEnrollment['programa_titulo'] ?? 'Curso aprobado') : 'Pendiente'],
        ['label' => 'Estado del curso', 'value' => (string) $courseStatusMeta['label']],
        ['label' => 'Aprobacion', 'value' => (string) $approvalStatusMeta['label']],
        ['label' => 'Fecha de aprobacion', 'value' => $selectedEnrollment ? certificado_curso_format_date((string) ($selectedEnrollment['fecha_aprobacion'] ?? ''), 'Pendiente') : 'Pendiente'],
        ['label' => 'PDF', 'value' => $selectedEnrollment ? 'Disponible' : 'No disponible'],
    ],
    'heroBadges' => [
        (string) $courseStatusMeta['label'],
        (string) $approvalStatusMeta['label'],
        $selectedEnrollment ? 'Certificado listo' : 'Esperando aprobacion',
    ],
    'heroActions' => $heroActions,
    'bodySectionsHtml' => $bodySectionsHtml,
]);
