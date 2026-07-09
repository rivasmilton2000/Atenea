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

if (!atenea_capacitacion_phase_two_ready($db)) {
    atenea_render_auth_alert(
        'warning',
        'Migracion pendiente',
        'Debes aplicar Database/migrations/2026_07_09_capacitacion_acceso_videos.sql para habilitar los videos del curso.',
        'usuario_vista.php'
    );
}

$publicUserId = (int) ($_SESSION['PUBLIC_USER_ID'] ?? 0);
$programId = max(0, (int) ($_GET['programa'] ?? ($_SESSION['ATENEA_ACTIVE_PROGRAM_ID'] ?? 0)));
$enrollment = atenea_capacitacion_fetch_active_enrollment_for_public_user($db, $publicUserId, $programId);
$accessibleVideos = $enrollment
    ? atenea_capacitacion_fetch_accessible_videos_for_public_user($db, $publicUserId, (int) ($enrollment['programa_id'] ?? 0))
    : [];
$allProgramVideos = $enrollment
    ? atenea_capacitacion_fetch_course_videos($db, (int) $enrollment['programa_id'], true)
    : [];
$courseStatusMeta = $enrollment ? atenea_capacitacion_course_status_meta((string) $enrollment['estado_curso']) : ['label' => 'Sin curso activo', 'class' => 'secondary'];
$approvalStatusMeta = $enrollment ? atenea_capacitacion_approval_status_meta((string) $enrollment['estado_aprobacion']) : ['label' => 'Pendiente', 'class' => 'secondary'];

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
</style>

<?php if (!$enrollment) : ?>
  <div class="row">
    <div class="col-12">
      <div class="atenea-video-empty">
        <h4 class="mb-3">No tienes acceso a videos todavia</h4>
        <p class="text-muted mb-4">
          Necesitas una inscripcion activa o aprobada para entrar al material audiovisual del curso.
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
      <?php $sourceMeta = atenea_capacitacion_video_source_meta($video); ?>
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
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
$bodySectionsHtml = ob_get_clean();

dashboard_render_material_page([
    'bodyClass' => 'atenea-course-videos-page',
    'pageTitle' => 'Videos del curso',
    'roleLabel' => 'Usuario registrado',
    'welcomeTitle' => $enrollment ? 'Material audiovisual de tu curso' : 'Videos del curso',
    'welcomeText' => $enrollment
        ? 'Aqui solo aparecen los videos habilitados para tu inscripcion actual, ya sea por activacion individual o masiva.'
        : 'Accede con una inscripcion activa para visualizar el contenido audiovisual asignado a tu curso.',
    'profileUrl' => 'usuario_vista.php',
    'logoutUrl' => 'logout.php?redirect=homepage.php',
    'navSections' => atenea_capacitacion_user_nav_sections('curso_videos.php'),
    'cardsColumnClass' => 'col-12 col-md-6 col-xl-4 mb-4',
    'cards' => [
        ['title' => 'Curso actual', 'value' => $enrollment ? (string) $enrollment['programa_titulo'] : 'Sin acceso', 'icon' => 'workspace_premium', 'accent' => 'success', 'href' => $enrollment ? 'mi_curso_activo.php?programa=' . (int) $enrollment['programa_id'] : 'mi_curso_activo.php', 'metricLabel' => 'Programa vinculado', 'footerLabel' => 'Ver curso'],
        ['title' => 'Videos visibles', 'value' => count($accessibleVideos), 'icon' => 'play_circle', 'accent' => 'info', 'href' => $enrollment ? 'curso_videos.php?programa=' . (int) $enrollment['programa_id'] : 'curso_videos.php', 'metricLabel' => 'Accesos habilitados', 'footerLabel' => 'Actualizar vista'],
        ['title' => 'Aprobacion', 'value' => (string) $approvalStatusMeta['label'], 'icon' => 'school', 'accent' => 'warning', 'href' => 'record_escolar.php', 'metricLabel' => 'Estado academico', 'footerLabel' => 'Ver record'],
    ],
    'quickLinks' => [
        ['label' => 'Mi curso activo', 'href' => $enrollment ? 'mi_curso_activo.php?programa=' . (int) $enrollment['programa_id'] : 'mi_curso_activo.php', 'icon' => 'workspace_premium'],
        ['label' => 'Record escolar', 'href' => 'record_escolar.php', 'icon' => 'school'],
        ['label' => 'Capacitacion', 'href' => 'educacion.php', 'icon' => 'public'],
    ],
    'summaryItems' => [
        ['label' => 'Estado del curso', 'value' => (string) $courseStatusMeta['label']],
        ['label' => 'Aprobacion', 'value' => (string) $approvalStatusMeta['label']],
        ['label' => 'Videos habilitados', 'value' => (string) count($accessibleVideos)],
        ['label' => 'Inscripcion', 'value' => $enrollment ? curso_videos_format_date((string) ($enrollment['fecha_inscripcion'] ?? '')) : 'No disponible'],
    ],
    'heroBadges' => [
        (string) $courseStatusMeta['label'],
        (string) $approvalStatusMeta['label'],
        count($accessibleVideos) . ' videos habilitados',
    ],
    'heroActions' => [
        ['label' => 'Mi curso activo', 'href' => $enrollment ? 'mi_curso_activo.php?programa=' . (int) $enrollment['programa_id'] : 'mi_curso_activo.php', 'icon' => 'workspace_premium'],
        ['label' => 'Record escolar', 'href' => 'record_escolar.php', 'icon' => 'school', 'variant' => 'outline'],
    ],
    'bodySectionsHtml' => $bodySectionsHtml,
]);
