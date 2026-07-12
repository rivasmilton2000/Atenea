<?php
require_once __DIR__ . '/session.php';
if (!logged_in()) {
    header('Location: ' . atenea_build_login_url('curso_videos.php', 'login_required'));
    exit;
}

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';
require_once __DIR__ . '/../includes/atenea_capacitacion.php';

if (!atenea_session_is_public_user()) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}

if (!function_exists('curso_videos_format_date')) {
    function curso_videos_format_date(string $value, string $fallback = 'No disponible'): string
    {
        $timestamp = strtotime(trim($value));

        return $timestamp === false ? $fallback : date('d/m/Y h:i A', $timestamp);
    }
}

if (!function_exists('curso_videos_page_url')) {
    function curso_videos_page_url(int $programId = 0, string $status = ''): string
    {
        $params = [];
        if ($programId > 0) {
            $params['programa'] = $programId;
        }

        if ($status !== '') {
            $params['status'] = $status;
        }

        return 'curso_videos.php' . ($params === [] ? '' : '?' . http_build_query($params));
    }
}

if (!atenea_capacitacion_phase_two_ready($db)) {
    atenea_render_auth_alert(
        'warning',
        'Migracion pendiente',
        'El contenido del curso no esta disponible temporalmente.',
        'usuario_vista.php'
    );
}

$phaseThreeReady = atenea_capacitacion_phase_three_ready($db);
$publicUserId = (int) ($_SESSION['PUBLIC_USER_ID'] ?? 0);
$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);
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

if ($enrollment && $phaseThreeReady && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $videoId = max(0, (int) ($_POST['video_id'] ?? 0));
    $videoAction = trim((string) ($_POST['video_action'] ?? ''));
    $accessibleVideoMap = [];

    foreach (atenea_capacitacion_fetch_accessible_videos_for_enrollment($db, (int) $enrollment['id']) as $videoRow) {
        $accessibleVideoMap[(int) ($videoRow['id'] ?? 0)] = $videoRow;
    }

    if ($videoId > 0 && isset($accessibleVideoMap[$videoId]) && in_array($videoAction, ['mark_complete', 'mark_pending'], true)) {
        atenea_capacitacion_set_video_completion(
            $db,
            (int) $enrollment['id'],
            $videoId,
            $videoAction === 'mark_complete',
            $memberId
        );
        header('Location: ' . curso_videos_page_url((int) ($enrollment['programa_id'] ?? 0), 'progress_updated'));
        exit;
    }

    header('Location: ' . curso_videos_page_url((int) ($enrollment['programa_id'] ?? 0), 'invalid_video'));
    exit;
}

$accessibleVideos = [];
if ($enrollment) {
    if ($phaseThreeReady) {
        $accessibleVideos = atenea_capacitacion_fetch_accessible_videos_for_enrollment($db, (int) $enrollment['id']);
    } else {
        $accessibleVideos = atenea_capacitacion_fetch_accessible_videos_for_public_user($db, $publicUserId, (int) ($enrollment['programa_id'] ?? 0));
    }
}

$allProgramVideos = $enrollment
    ? atenea_capacitacion_fetch_course_videos($db, (int) $enrollment['programa_id'], true)
    : [];
$courseStatusMeta = $enrollment ? atenea_capacitacion_course_status_meta((string) $enrollment['estado_curso']) : ['label' => 'Sin curso activo', 'class' => 'secondary'];
$approvalStatusMeta = $enrollment ? atenea_capacitacion_approval_status_meta((string) $enrollment['estado_aprobacion']) : ['label' => 'Pendiente', 'class' => 'secondary'];
$certificateAvailable = $enrollment ? atenea_capacitacion_certificate_eligible($enrollment) : false;
$progress = $enrollment ? atenea_capacitacion_progress_percentage($enrollment['progreso'] ?? 0) : 0;
$completedVideos = 0;
$totalVideos = count($accessibleVideos);

foreach ($accessibleVideos as $video) {
    if (!empty($video['completed'])) {
        $completedVideos++;
    }
}

$statusFlag = trim((string) ($_GET['status'] ?? ''));
$statusMessage = '';
$statusClass = 'success';

if ($statusFlag === 'progress_updated') {
    $statusMessage = 'Tu avance en el curso fue actualizado correctamente.';
} elseif ($statusFlag === 'invalid_video') {
    $statusMessage = 'No fue posible actualizar ese video porque ya no esta disponible para tu inscripcion.';
    $statusClass = 'warning';
}

ob_start();
?>
<style>
  .atenea-course-videos-page .atenea-video-card {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 1.25rem;
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
  }

  .atenea-course-videos-page .atenea-video-frame {
    position: relative;
    width: 100%;
    border-radius: 1rem;
    overflow: hidden;
    background: #0f172a;
  }

  .atenea-course-videos-page .atenea-video-frame::before {
    content: "";
    display: block;
    padding-top: 56.25%;
  }

  .atenea-course-videos-page .atenea-video-frame iframe,
  .atenea-course-videos-page .atenea-video-frame video {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: 0;
  }

  .atenea-course-videos-page .atenea-video-empty {
    border: 1px dashed rgba(15, 23, 42, 0.16);
    border-radius: 1.25rem;
    padding: 2rem;
    background: #fff;
    text-align: center;
  }

  .atenea-course-videos-page .atenea-video-meta {
    border-top: 1px solid rgba(15, 23, 42, 0.08);
    margin-top: 1rem;
    padding-top: 1rem;
  }
</style>

<?php if ($statusMessage !== '') : ?>
  <div class="alert alert-<?php echo dashboard_h($statusClass); ?> mb-4">
    <?php echo dashboard_h($statusMessage); ?>
  </div>
<?php endif; ?>

<?php if ($enrollment && !$phaseThreeReady) : ?>
  <div class="alert alert-warning mb-4">
    El seguimiento de avance no esta disponible temporalmente. Intenta nuevamente mas tarde.
  </div>
<?php endif; ?>

<?php if ($enrollment && $phaseThreeReady) : ?>
  <div class="alert alert-info mb-4">
    Al completar todos los videos habilitados, el curso pasa a <strong>Finalizado</strong>. Despues, administracion puede marcarlo como
    <strong>Aprobado</strong> para liberar el certificado.
  </div>
<?php endif; ?>

<?php if (!$enrollment) : ?>
  <div class="row">
    <div class="col-12">
      <div class="atenea-video-empty">
        <h4 class="mb-3">No tienes acceso a videos todavia</h4>
        <p class="text-muted mb-4">
          Necesitas una inscripcion activa, finalizada o aprobada para entrar al material audiovisual del curso.
        </p>
        <a href="educacion.php" class="btn btn-primary">Ver capacitacion</a>
      </div>
    </div>
  </div>
<?php elseif ($accessibleVideos === []) : ?>
  <div class="row">
    <div class="col-12">
      <div class="atenea-video-empty">
        <h4 class="mb-3">Tus videos aun no han sido habilitados</h4>
        <p class="text-muted mb-3">
          <?php if ($allProgramVideos === []) : ?>
            Este curso todavia no tiene videos activos cargados por administracion.
          <?php else : ?>
            Ya existen videos asociados al curso, pero el acceso aun no fue activado para tu cuenta.
          <?php endif; ?>
        </p>
        <div class="d-flex justify-content-center flex-wrap" style="gap: 0.75rem;">
          <a href="mi_curso_activo.php?programa=<?php echo (int) $enrollment['programa_id']; ?>" class="btn btn-primary">Volver a mi curso</a>
          <a href="record_escolar.php" class="btn btn-outline-dark">Ver record escolar</a>
        </div>
      </div>
    </div>
  </div>
<?php else : ?>
  <div class="row">
    <?php foreach ($accessibleVideos as $video) : ?>
      <?php
      $sourceMeta = atenea_capacitacion_video_source_meta($video);
      $isCompleted = !empty($video['completed']);
      $completedAt = trim((string) ($video['completed_at'] ?? ''));
      ?>
      <div class="col-12 col-xl-6 mb-4">
        <div class="card atenea-video-card border-0 h-100">
          <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center mb-3" style="gap: 0.5rem;">
              <span class="badge badge-success"><?php echo dashboard_h((string) $sourceMeta['label']); ?></span>
              <?php if (!empty($video['mass_enabled'])) : ?>
                <span class="badge badge-info">Acceso masivo</span>
              <?php elseif (!empty($video['individual_enabled'])) : ?>
                <span class="badge badge-primary">Acceso individual</span>
              <?php endif; ?>
              <?php if ($phaseThreeReady) : ?>
                <span class="badge badge-<?php echo $isCompleted ? 'success' : 'secondary'; ?>">
                  <?php echo $isCompleted ? 'Completado' : 'Pendiente'; ?>
                </span>
              <?php endif; ?>
            </div>

            <h4 class="mb-2"><?php echo dashboard_h((string) ($video['titulo'] ?? 'Video del curso')); ?></h4>
            <p class="text-muted mb-3">
              <?php echo nl2br(dashboard_h((string) ($video['descripcion'] ?? ''))); ?>
            </p>

            <?php if (in_array((string) $sourceMeta['type'], ['youtube', 'upload', 'direct'], true)) : ?>
              <div class="atenea-video-frame mb-3">
                <?php if ((string) $sourceMeta['type'] === 'youtube') : ?>
                  <iframe
                    src="<?php echo dashboard_h((string) $sourceMeta['embed_url']); ?>"
                    title="<?php echo dashboard_h((string) ($video['titulo'] ?? 'Video')); ?>"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                  ></iframe>
                <?php else : ?>
                  <video controls preload="metadata">
                    <source src="<?php echo dashboard_h((string) $sourceMeta['embed_url']); ?>">
                    Tu navegador no puede reproducir este video.
                  </video>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 0.75rem;">
              <small class="text-muted">Curso: <?php echo dashboard_h((string) ($video['programa_titulo'] ?? 'Capacitacion')); ?></small>
              <?php if ((string) $sourceMeta['link_url'] !== '') : ?>
                <a href="<?php echo dashboard_h((string) $sourceMeta['link_url']); ?>" target="_blank" rel="noopener" class="btn btn-outline-success btn-sm">
                  Abrir video
                </a>
              <?php endif; ?>
            </div>

            <?php if ($phaseThreeReady) : ?>
              <div class="atenea-video-meta">
                <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 0.75rem;">
                  <div>
                    <strong>Seguimiento:</strong>
                    <?php echo $isCompleted ? 'Video completado' : 'Pendiente de completar'; ?>
                    <?php if ($completedAt !== '' && $isCompleted) : ?>
                      <br><small class="text-muted">Ultima marca: <?php echo dashboard_h(curso_videos_format_date($completedAt)); ?></small>
                    <?php endif; ?>
                  </div>

                  <form method="post" class="mb-0">
                    <input type="hidden" name="video_id" value="<?php echo (int) ($video['id'] ?? 0); ?>">
                    <input type="hidden" name="video_action" value="<?php echo $isCompleted ? 'mark_pending' : 'mark_complete'; ?>">
                    <button type="submit" class="btn btn-sm <?php echo $isCompleted ? 'btn-outline-dark' : 'btn-primary'; ?>">
                      <?php echo $isCompleted ? 'Marcar pendiente' : 'Marcar completado'; ?>
                    </button>
                  </form>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
$bodySectionsHtml = ob_get_clean();

$cards = [
    [
        'title' => 'Curso actual',
        'value' => $enrollment ? (string) $enrollment['programa_titulo'] : 'Sin acceso',
        'icon' => 'workspace_premium',
        'accent' => 'success',
        'href' => $enrollment ? 'mi_curso_activo.php?programa=' . (int) $enrollment['programa_id'] : 'mi_curso_activo.php',
        'metricLabel' => 'Programa vinculado',
        'footerLabel' => 'Ver curso',
    ],
    [
        'title' => 'Videos completados',
        'value' => $enrollment ? ($completedVideos . '/' . $totalVideos) : '0/0',
        'icon' => 'task_alt',
        'accent' => 'info',
        'href' => $enrollment ? 'curso_videos.php?programa=' . (int) $enrollment['programa_id'] : 'curso_videos.php',
        'metricLabel' => 'Seguimiento del plan',
        'footerLabel' => 'Actualizar vista',
    ],
    [
        'title' => 'Aprobacion',
        'value' => (string) $approvalStatusMeta['label'],
        'icon' => 'school',
        'accent' => 'warning',
        'href' => 'record_escolar.php',
        'metricLabel' => 'Estado academico',
        'footerLabel' => 'Ver record',
    ],
];

$quickLinks = [
    ['label' => 'Mi curso activo', 'href' => $enrollment ? 'mi_curso_activo.php?programa=' . (int) $enrollment['programa_id'] : 'mi_curso_activo.php', 'icon' => 'workspace_premium'],
    ['label' => 'Record escolar', 'href' => 'record_escolar.php', 'icon' => 'school'],
    ['label' => 'Capacitacion', 'href' => 'educacion.php', 'icon' => 'public'],
];

if ($certificateAvailable && $enrollment) {
    $quickLinks[] = ['label' => 'Mi certificado', 'href' => 'certificado_curso.php?enrollment_id=' . (int) $enrollment['id'], 'icon' => 'workspace_premium'];
}

$heroActions = [
    ['label' => 'Mi curso activo', 'href' => $enrollment ? 'mi_curso_activo.php?programa=' . (int) $enrollment['programa_id'] : 'mi_curso_activo.php', 'icon' => 'workspace_premium'],
    ['label' => 'Record escolar', 'href' => 'record_escolar.php', 'icon' => 'school', 'variant' => 'outline'],
];

if ($certificateAvailable && $enrollment) {
    $heroActions[] = ['label' => 'Ver certificado', 'href' => 'certificado_curso.php?enrollment_id=' . (int) $enrollment['id'], 'icon' => 'workspace_premium'];
}

dashboard_render_material_page([
    'bodyClass' => 'atenea-course-videos-page',
    'pageTitle' => 'Videos del curso',
    'roleLabel' => 'Usuario registrado',
    'welcomeTitle' => $enrollment ? 'Material audiovisual de tu curso' : 'Videos del curso',
    'welcomeText' => $enrollment
        ? 'Aqui aparecen los videos habilitados para tu inscripcion y, en esta fase, tambien puedes registrar tu avance para cerrar el curso.'
        : 'Accede con una inscripcion activa para visualizar el contenido audiovisual asignado a tu curso.',
    'profileUrl' => 'usuario_vista.php',
    'logoutUrl' => 'logout.php?redirect=homepage.php',
    'navSections' => atenea_capacitacion_user_nav_sections('curso_videos.php'),
    'cardsColumnClass' => 'col-12 col-md-6 col-xl-4 mb-4',
    'cards' => $cards,
    'quickLinks' => $quickLinks,
    'summaryItems' => [
        ['label' => 'Estado del curso', 'value' => (string) $courseStatusMeta['label']],
        ['label' => 'Aprobacion', 'value' => (string) $approvalStatusMeta['label']],
        ['label' => 'Videos completados', 'value' => $completedVideos . ' de ' . $totalVideos],
        ['label' => 'Progreso general', 'value' => $progress . '%'],
        ['label' => 'Inscripcion', 'value' => $enrollment ? curso_videos_format_date((string) ($enrollment['fecha_inscripcion'] ?? '')) : 'No disponible'],
        ['label' => 'Certificado', 'value' => $certificateAvailable ? 'Disponible' : 'Aun no habilitado'],
    ],
    'heroBadges' => [
        (string) $courseStatusMeta['label'],
        (string) $approvalStatusMeta['label'],
        $completedVideos . '/' . $totalVideos . ' videos completados',
    ],
    'heroActions' => $heroActions,
    'bodySectionsHtml' => $bodySectionsHtml,
]);
