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
        'Debes aplicar Database/migrations/2026_07_09_capacitacion_acceso_videos.sql para habilitar el curso activo.',
        'usuario_vista.php'
    );
}

$publicUserId = (int) ($_SESSION['PUBLIC_USER_ID'] ?? 0);
$programId = max(0, (int) ($_GET['programa'] ?? ($_SESSION['ATENEA_ACTIVE_PROGRAM_ID'] ?? 0)));
$enrollment = atenea_capacitacion_fetch_active_enrollment_for_public_user($db, $publicUserId, $programId);

if (!$enrollment && $programId > 0) {
    $fallbackEnrollments = atenea_capacitacion_fetch_enrollments_for_public_user($db, $publicUserId, $programId);
    $enrollment = $fallbackEnrollments[0] ?? null;
}

$accessibleVideos = $enrollment
    ? atenea_capacitacion_fetch_accessible_videos_for_public_user($db, $publicUserId, (int) ($enrollment['programa_id'] ?? 0))
    : [];
$courseStatusMeta = $enrollment ? atenea_capacitacion_course_status_meta((string) $enrollment['estado_curso']) : ['label' => 'Sin curso activo', 'class' => 'secondary'];
$approvalStatusMeta = $enrollment ? atenea_capacitacion_approval_status_meta((string) $enrollment['estado_aprobacion']) : ['label' => 'Pendiente', 'class' => 'secondary'];
$progress = $enrollment ? atenea_capacitacion_progress_percentage($enrollment['progreso'] ?? 0) : 0;

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
                  <p><strong>Videos habilitados:</strong> <?php echo count($accessibleVideos); ?></p>
                  <p class="mb-2"><strong>Progreso general:</strong> <?php echo $progress; ?>%</p>
                  <div class="atenea-progress-shell">
                    <div class="atenea-progress-value" style="width: <?php echo $progress; ?>%;"></div>
                  </div>
                </div>
              </div>

              <div class="d-flex flex-wrap mt-4" style="gap: 0.75rem;">
                <a href="curso_videos.php?programa=<?php echo (int) $enrollment['programa_id']; ?>" class="btn btn-primary">Ver videos del curso</a>
                <a href="record_escolar.php" class="btn btn-outline-success">Abrir record escolar</a>
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
          Cuando confirmes la inscripcion de una capacitacion, aqui veras el curso vinculado a tu perfil y sus accesos principales.
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
    ['title' => 'Videos habilitados', 'value' => count($accessibleVideos), 'icon' => 'play_circle', 'accent' => 'info', 'href' => $enrollment ? 'curso_videos.php?programa=' . (int) $enrollment['programa_id'] : 'curso_videos.php', 'metricLabel' => 'Material disponible', 'footerLabel' => 'Abrir videos'],
    ['title' => 'Progreso', 'value' => $progress . '%', 'icon' => 'trending_up', 'accent' => 'warning', 'href' => 'record_escolar.php', 'metricLabel' => 'Avance acumulado', 'footerLabel' => 'Ver record'],
];

$summaryItems = [
    ['label' => 'Curso actual', 'value' => $enrollment ? (string) $enrollment['programa_titulo'] : 'Sin curso activo'],
    ['label' => 'Estado', 'value' => (string) $courseStatusMeta['label']],
    ['label' => 'Aprobacion', 'value' => (string) $approvalStatusMeta['label']],
    ['label' => 'Progreso', 'value' => $progress . '%'],
    ['label' => 'Inscripcion', 'value' => $enrollment ? mi_curso_activo_format_date((string) ($enrollment['fecha_inscripcion'] ?? '')) : 'No disponible'],
    ['label' => 'Instructor', 'value' => $enrollment ? (string) ($enrollment['programa_instructor'] ?? 'Por definir') : 'Por definir'],
];

dashboard_render_material_page([
    'bodyClass' => 'atenea-course-active-page',
    'pageTitle' => 'Mi curso activo',
    'roleLabel' => 'Usuario registrado',
    'welcomeTitle' => $enrollment ? 'Tu curso activo ya esta listo' : 'Tu curso activo aparecera aqui',
    'welcomeText' => $enrollment
        ? 'Desde esta vista puedes confirmar el estado actual del programa, entrar a sus videos y seguir el avance academico.'
        : 'Cuando completes una inscripcion, este espacio mostrara el curso asociado a tu perfil.',
    'profileUrl' => 'usuario_vista.php',
    'logoutUrl' => 'logout.php?redirect=homepage.php',
    'navSections' => atenea_capacitacion_user_nav_sections('mi_curso_activo.php'),
    'cardsColumnClass' => 'col-12 col-md-6 col-xl-4 mb-4',
    'cards' => $cards,
    'quickLinks' => [
        ['label' => 'Ir al panel', 'href' => 'usuario_vista.php', 'icon' => 'dashboard'],
        ['label' => 'Videos del curso', 'href' => $enrollment ? 'curso_videos.php?programa=' . (int) $enrollment['programa_id'] : 'curso_videos.php', 'icon' => 'play_circle'],
        ['label' => 'Record escolar', 'href' => 'record_escolar.php', 'icon' => 'school'],
    ],
    'summaryItems' => $summaryItems,
    'heroBadges' => [
        (string) $courseStatusMeta['label'],
        (string) $approvalStatusMeta['label'],
        count($accessibleVideos) . ' videos habilitados',
    ],
    'heroActions' => [
        ['label' => 'Abrir videos', 'href' => $enrollment ? 'curso_videos.php?programa=' . (int) $enrollment['programa_id'] : 'curso_videos.php', 'icon' => 'play_circle'],
        ['label' => 'Ver record', 'href' => 'record_escolar.php', 'icon' => 'school', 'variant' => 'outline'],
    ],
    'bodySectionsHtml' => $bodySectionsHtml,
]);
