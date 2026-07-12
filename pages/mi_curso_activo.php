<?php
require_once __DIR__ . '/session.php';
if (!logged_in()) {
    header('Location: ' . atenea_build_login_url('mi_curso_activo.php', 'login_required'));
    exit;
}

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';
require_once __DIR__ . '/../includes/atenea_capacitacion.php';

if (!atenea_session_is_public_user()) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}

if (!function_exists('mi_curso_activo_format_date')) {
    function mi_curso_activo_format_date(string $value, string $fallback = 'No disponible'): string
    {
        $timestamp = strtotime(trim($value));

        return $timestamp === false ? $fallback : date('d/m/Y h:i A', $timestamp);
    }
}

if (!atenea_capacitacion_phase_two_ready($db)) {
    atenea_render_auth_alert(
        'warning',
        'Migracion pendiente',
        'El acceso al curso no esta disponible temporalmente.',
        'usuario_vista.php'
    );
}

$phaseThreeReady = atenea_capacitacion_phase_three_ready($db);
$publicUserId = (int) ($_SESSION['PUBLIC_USER_ID'] ?? 0);
$programId = max(0, (int) ($_GET['programa'] ?? ($_SESSION['ATENEA_ACTIVE_PROGRAM_ID'] ?? 0)));
$enrollment = atenea_capacitacion_fetch_active_enrollment_for_public_user($db, $publicUserId, $programId);

if (!$enrollment && $programId > 0) {
    $fallbackEnrollments = atenea_capacitacion_fetch_enrollments_for_public_user($db, $publicUserId, $programId);
    $enrollment = $fallbackEnrollments[0] ?? null;
}

if ($enrollment && $phaseThreeReady) {
    $updatedEnrollment = atenea_capacitacion_recalculate_enrollment_progress($db, (int) $enrollment['id']);
    if ($updatedEnrollment) {
        $enrollment = $updatedEnrollment;
    }
}

$accessibleVideos = [];
if ($enrollment) {
    if ($phaseThreeReady) {
        $accessibleVideos = atenea_capacitacion_fetch_accessible_videos_for_enrollment($db, (int) $enrollment['id']);
    } else {
        $accessibleVideos = atenea_capacitacion_fetch_accessible_videos_for_public_user($db, $publicUserId, (int) ($enrollment['programa_id'] ?? 0));
    }
}

$completedVideos = 0;
foreach ($accessibleVideos as $video) {
    if (!empty($video['completed'])) {
        $completedVideos++;
    }
}

$totalVideos = count($accessibleVideos);
$courseStatusMeta = $enrollment ? atenea_capacitacion_course_status_meta((string) $enrollment['estado_curso']) : ['label' => 'Sin curso activo', 'class' => 'secondary'];
$approvalStatusMeta = $enrollment ? atenea_capacitacion_approval_status_meta((string) $enrollment['estado_aprobacion']) : ['label' => 'Pendiente', 'class' => 'secondary'];
$progress = $enrollment ? atenea_capacitacion_progress_percentage($enrollment['progreso'] ?? 0) : 0;
$certificateAvailable = $enrollment ? atenea_capacitacion_certificate_eligible($enrollment) : false;
$isFinalized = $enrollment
    ? atenea_capacitacion_normalize_course_status((string) ($enrollment['estado_curso'] ?? '')) === 'finalizado'
    : false;
$isApproved = $enrollment
    ? atenea_capacitacion_normalize_approval_status((string) ($enrollment['estado_aprobacion'] ?? '')) === 'aprobado'
    : false;

ob_start();
?>
<style>
  .atenea-course-active-page .atenea-course-card,
  .atenea-course-active-page .atenea-course-sidecard {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 1.25rem;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
  }

  .atenea-course-active-page .atenea-course-cover {
    width: 100%;
    height: 100%;
    min-height: 320px;
    object-fit: cover;
  }

  .atenea-course-active-page .atenea-course-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem;
    margin-bottom: 1.25rem;
  }

  .atenea-course-active-page .atenea-course-pill {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    background: rgba(15, 118, 110, 0.1);
    color: #0f766e;
    font-size: 0.84rem;
    font-weight: 700;
    padding: 0.45rem 0.9rem;
  }

  .atenea-course-active-page .atenea-progress-shell {
    border-radius: 999px;
    background: #e2e8f0;
    height: 12px;
    overflow: hidden;
  }

  .atenea-course-active-page .atenea-progress-value {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #0f766e 0%, #16a34a 100%);
  }

  .atenea-course-active-page .atenea-metric-list p {
    margin-bottom: 0.75rem;
  }

  .atenea-course-active-page .atenea-empty-course {
    border: 1px dashed rgba(15, 23, 42, 0.16);
    border-radius: 1.25rem;
    background: #fff;
    padding: 2rem;
    text-align: center;
  }
</style>

<?php if ($enrollment) : ?>
  <?php if (!$phaseThreeReady) : ?>
    <div class="alert alert-warning mb-4">
      El seguimiento de finalizacion y aprobacion no esta disponible temporalmente.
    </div>
  <?php endif; ?>

  <?php if ($phaseThreeReady && $isFinalized && !$isApproved) : ?>
    <div class="alert alert-info mb-4">
      Ya completaste el material habilitado. Tu curso aparece como <strong>Finalizado</strong> y ahora queda pendiente la aprobacion final
      de administracion para liberar el certificado.
    </div>
  <?php endif; ?>

  <?php if ($certificateAvailable) : ?>
    <div class="alert alert-success mb-4">
      Tu certificado ya esta disponible. Puedes visualizarlo en pantalla o descargarlo en PDF desde tu panel.
    </div>
  <?php endif; ?>

  <div class="row">
    <div class="col-12">
      <div class="card atenea-course-card border-0 overflow-hidden">
        <div class="row no-gutters">
          <div class="col-lg-4">
            <img
              src="../img/<?php echo dashboard_h((string) ($enrollment['programa_imagen'] ?? '')); ?>"
              alt="<?php echo dashboard_h((string) ($enrollment['programa_titulo'] ?? 'Curso activo')); ?>"
              class="atenea-course-cover"
            >
          </div>
          <div class="col-lg-8">
            <div class="card-body p-4 p-lg-5">
              <div class="atenea-course-pills">
                <span class="badge badge-<?php echo dashboard_h((string) $courseStatusMeta['class']); ?>"><?php echo dashboard_h((string) $courseStatusMeta['label']); ?></span>
                <span class="badge badge-<?php echo dashboard_h((string) $approvalStatusMeta['class']); ?>"><?php echo dashboard_h((string) $approvalStatusMeta['label']); ?></span>
                <span class="atenea-course-pill"><?php echo dashboard_h(atenea_capacitacion_type_label((string) ($enrollment['tipo_programa'] ?? 'curso'))); ?></span>
                <span class="atenea-course-pill">Precio: $<?php echo number_format(atenea_capacitacion_price($enrollment), 2); ?></span>
              </div>

              <h2 class="mb-3"><?php echo dashboard_h((string) ($enrollment['programa_titulo'] ?? 'Curso activo')); ?></h2>
              <p class="text-muted mb-4">
                <?php echo nl2br(dashboard_h((string) ($enrollment['programa_descripcion_completa'] ?: $enrollment['programa_descripcion_corta']))); ?>
              </p>

              <div class="row">
                <div class="col-md-6 atenea-metric-list">
                  <p><strong>Instructor:</strong> <?php echo dashboard_h((string) ($enrollment['programa_instructor'] ?? 'Por definir')); ?></p>
                  <p><strong>Nivel:</strong> <?php echo dashboard_h((string) ($enrollment['programa_nivel'] ?? 'Por definir')); ?></p>
                  <p><strong>Duracion:</strong> <?php echo dashboard_h(atenea_capacitacion_text_value($enrollment['duracion'] ?? '') !== '' ? (string) $enrollment['duracion'] : 'Por definir'); ?></p>
                  <p class="mb-0"><strong>Modalidad:</strong> <?php echo dashboard_h(atenea_capacitacion_text_value($enrollment['modalidad'] ?? '') !== '' ? (string) $enrollment['modalidad'] : 'Por definir'); ?></p>
                </div>
                <div class="col-md-6 atenea-metric-list">
                  <p><strong>Fecha de inscripcion:</strong> <?php echo dashboard_h(mi_curso_activo_format_date((string) ($enrollment['fecha_inscripcion'] ?? ''))); ?></p>
                  <p><strong>Videos completados:</strong> <?php echo $completedVideos . '/' . $totalVideos; ?></p>
                  <p><strong>Progreso general:</strong> <?php echo $progress; ?>%</p>
                  <p><strong>Fecha de finalizacion:</strong> <?php echo dashboard_h(mi_curso_activo_format_date((string) ($enrollment['fecha_finalizacion'] ?? ''), 'Pendiente')); ?></p>
                  <p><strong>Fecha de aprobacion:</strong> <?php echo dashboard_h(mi_curso_activo_format_date((string) ($enrollment['fecha_aprobacion'] ?? ''), 'Pendiente')); ?></p>
                  <p class="mb-2"><strong>Certificado:</strong> <?php echo $certificateAvailable ? 'Disponible' : 'Aun no habilitado'; ?></p>
                  <div class="atenea-progress-shell">
                    <div class="atenea-progress-value" style="width: <?php echo $progress; ?>%;"></div>
                  </div>
                </div>
              </div>

              <div class="d-flex flex-wrap mt-4" style="gap: 0.75rem;">
                <a href="curso_videos.php?programa=<?php echo (int) $enrollment['programa_id']; ?>" class="btn btn-primary">Ver videos del curso</a>
                <a href="record_escolar.php" class="btn btn-outline-success">Abrir record escolar</a>
                <?php if ($certificateAvailable) : ?>
                  <a href="certificado_curso.php?enrollment_id=<?php echo (int) $enrollment['id']; ?>" class="btn btn-outline-primary">Ver certificado</a>
                <?php endif; ?>
                <a href="educacion.php" class="btn btn-outline-dark">Explorar mas capacitacion</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php else : ?>
  <div class="row">
    <div class="col-12">
      <div class="atenea-empty-course">
        <h4 class="mb-3">Aun no tienes un curso activo</h4>
        <p class="text-muted mb-4">
          Cuando completes el pago, tu inscripcion se activara automaticamente y aqui veras el curso, su avance y el acceso al certificado cuando corresponda.
        </p>
        <a href="educacion.php" class="btn btn-primary">Ver capacitacion disponible</a>
      </div>
    </div>
  </div>
<?php endif; ?>
<?php
$bodySectionsHtml = ob_get_clean();

$cards = [
    ['title' => 'Estado del curso', 'value' => $courseStatusMeta['label'], 'icon' => 'workspace_premium', 'accent' => 'success', 'href' => 'mi_curso_activo.php', 'metricLabel' => 'Situacion actual', 'footerLabel' => 'Ver detalle'],
    ['title' => 'Progreso', 'value' => $progress . '%', 'icon' => 'trending_up', 'accent' => 'info', 'href' => $enrollment ? 'curso_videos.php?programa=' . (int) $enrollment['programa_id'] : 'curso_videos.php', 'metricLabel' => 'Avance acumulado', 'footerLabel' => 'Seguir curso'],
    ['title' => 'Certificado', 'value' => $certificateAvailable ? 'Disponible' : 'Pendiente', 'icon' => 'workspace_premium', 'accent' => 'warning', 'href' => $certificateAvailable && $enrollment ? 'certificado_curso.php?enrollment_id=' . (int) $enrollment['id'] : 'record_escolar.php', 'metricLabel' => 'Resultado final', 'footerLabel' => $certificateAvailable ? 'Abrir certificado' : 'Ver record'],
];

$summaryItems = [
    ['label' => 'Curso actual', 'value' => $enrollment ? (string) $enrollment['programa_titulo'] : 'Sin curso activo'],
    ['label' => 'Estado', 'value' => (string) $courseStatusMeta['label']],
    ['label' => 'Aprobacion', 'value' => (string) $approvalStatusMeta['label']],
    ['label' => 'Progreso', 'value' => $progress . '%'],
    ['label' => 'Videos completados', 'value' => $completedVideos . ' de ' . $totalVideos],
    ['label' => 'Inscripcion', 'value' => $enrollment ? mi_curso_activo_format_date((string) ($enrollment['fecha_inscripcion'] ?? '')) : 'No disponible'],
    ['label' => 'Finalizacion', 'value' => $enrollment ? mi_curso_activo_format_date((string) ($enrollment['fecha_finalizacion'] ?? ''), 'Pendiente') : 'Pendiente'],
    ['label' => 'Certificado', 'value' => $certificateAvailable ? 'Disponible' : 'Aun no habilitado'],
];

$quickLinks = [
    ['label' => 'Ir al panel', 'href' => 'usuario_vista.php', 'icon' => 'dashboard'],
    ['label' => 'Videos del curso', 'href' => $enrollment ? 'curso_videos.php?programa=' . (int) $enrollment['programa_id'] : 'curso_videos.php', 'icon' => 'play_circle'],
    ['label' => 'Record escolar', 'href' => 'record_escolar.php', 'icon' => 'school'],
];

if ($certificateAvailable && $enrollment) {
    $quickLinks[] = ['label' => 'Mi certificado', 'href' => 'certificado_curso.php?enrollment_id=' . (int) $enrollment['id'], 'icon' => 'workspace_premium'];
}

$heroActions = [
    ['label' => 'Abrir videos', 'href' => $enrollment ? 'curso_videos.php?programa=' . (int) $enrollment['programa_id'] : 'curso_videos.php', 'icon' => 'play_circle'],
    ['label' => 'Ver record', 'href' => 'record_escolar.php', 'icon' => 'school', 'variant' => 'outline'],
];

if ($certificateAvailable && $enrollment) {
    $heroActions[] = ['label' => 'Ver certificado', 'href' => 'certificado_curso.php?enrollment_id=' . (int) $enrollment['id'], 'icon' => 'workspace_premium'];
}

dashboard_render_material_page([
    'bodyClass' => 'atenea-course-active-page',
    'pageTitle' => 'Mi curso activo',
    'roleLabel' => 'Usuario registrado',
    'welcomeTitle' => $enrollment ? 'Tu curso activo ya esta listo' : 'Tu curso activo aparecera aqui',
    'welcomeText' => $enrollment
        ? 'Desde esta vista puedes confirmar el estado actual del programa, seguir el avance, revisar la aprobacion y abrir el certificado cuando quede disponible.'
        : 'Cuando completes una inscripcion, este espacio mostrara el curso asociado a tu perfil.',
    'profileUrl' => 'usuario_vista.php',
    'logoutUrl' => 'logout.php?redirect=homepage.php',
    'navSections' => atenea_capacitacion_user_nav_sections('mi_curso_activo.php'),
    'cardsColumnClass' => 'col-12 col-md-6 col-xl-4 mb-4',
    'cards' => $cards,
    'quickLinks' => $quickLinks,
    'summaryItems' => $summaryItems,
    'heroBadges' => [
        (string) $courseStatusMeta['label'],
        (string) $approvalStatusMeta['label'],
        $completedVideos . '/' . $totalVideos . ' videos completados',
    ],
    'heroActions' => $heroActions,
    'bodySectionsHtml' => $bodySectionsHtml,
]);
