<?php
require_once __DIR__ . '/session.php';
if (!logged_in()) {
    header('Location: ' . atenea_build_login_url('record_escolar.php', 'login_required'));
    exit;
}

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';
require_once __DIR__ . '/../includes/atenea_capacitacion.php';

if (!atenea_session_is_public_user()) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}

if (!function_exists('record_escolar_format_date')) {
    function record_escolar_format_date(string $value, string $fallback = 'No disponible'): string
    {
        $timestamp = strtotime(trim($value));

        return $timestamp === false ? $fallback : date('d/m/Y h:i A', $timestamp);
    }
}

if (!atenea_capacitacion_phase_two_ready($db)) {
    atenea_render_auth_alert(
        'warning',
        'Migracion pendiente',
        'Debes aplicar Database/migrations/2026_07_09_capacitacion_acceso_videos.sql para habilitar el record escolar.',
        'usuario_vista.php'
    );
}

$publicUserId = (int) ($_SESSION['PUBLIC_USER_ID'] ?? 0);
$enrollments = atenea_capacitacion_fetch_enrollments_for_public_user($db, $publicUserId);
$activeCount = 0;
$approvedCount = 0;

foreach ($enrollments as $row) {
    $courseStatus = atenea_capacitacion_normalize_course_status((string) ($row['estado_curso'] ?? ''));
    $approvalStatus = atenea_capacitacion_normalize_approval_status((string) ($row['estado_aprobacion'] ?? ''));

    if (in_array($courseStatus, ['curso_activo', 'activo'], true)) {
        $activeCount++;
    }

    if ($approvalStatus === 'aprobado') {
        $approvedCount++;
    }
}

ob_start();
?>
<style>
  .atenea-record-page .atenea-record-card {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 1.25rem;
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
  }

  .atenea-record-page .atenea-record-cover {
    width: 100%;
    height: 220px;
    object-fit: cover;
    border-radius: 1rem;
  }

  .atenea-record-page .atenea-record-progress {
    height: 12px;
    border-radius: 999px;
    overflow: hidden;
    background: #e2e8f0;
  }

  .atenea-record-page .atenea-record-progress > span {
    display: block;
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #0f766e 0%, #16a34a 100%);
  }

  .atenea-record-page .atenea-record-empty {
    border: 1px dashed rgba(15, 23, 42, 0.16);
    border-radius: 1.25rem;
    background: #fff;
    padding: 2rem;
    text-align: center;
  }
</style>

<?php if ($enrollments === []) : ?>
  <div class="row">
    <div class="col-12">
      <div class="atenea-record-empty">
        <h4 class="mb-3">Todavia no hay registros academicos</h4>
        <p class="text-muted mb-4">
          Tu record escolar se construira automaticamente cuando confirmes una inscripcion a un curso o certificacion.
        </p>
        <a href="educacion.php" class="btn btn-primary">Explorar capacitacion</a>
      </div>
    </div>
  </div>
<?php else : ?>
  <div class="row">
    <?php foreach ($enrollments as $enrollment) : ?>
      <?php
      $courseStatusMeta = atenea_capacitacion_course_status_meta((string) ($enrollment['estado_curso'] ?? ''));
      $approvalStatusMeta = atenea_capacitacion_approval_status_meta((string) ($enrollment['estado_aprobacion'] ?? ''));
      $progress = atenea_capacitacion_progress_percentage($enrollment['progreso'] ?? 0);
      ?>
      <div class="col-12 col-xl-6 mb-4">
        <div class="card atenea-record-card border-0 h-100">
          <div class="card-body p-4">
            <div class="row">
              <div class="col-md-4 mb-3 mb-md-0">
                <img
                  src="../img/<?php echo dashboard_h((string) ($enrollment['programa_imagen'] ?? '')); ?>"
                  alt="<?php echo dashboard_h((string) ($enrollment['programa_titulo'] ?? 'Curso')); ?>"
                  class="atenea-record-cover"
                >
              </div>
              <div class="col-md-8">
                <div class="d-flex flex-wrap align-items-center mb-3" style="gap: 0.5rem;">
                  <span class="badge badge-<?php echo dashboard_h((string) $courseStatusMeta['class']); ?>"><?php echo dashboard_h((string) $courseStatusMeta['label']); ?></span>
                  <span class="badge badge-<?php echo dashboard_h((string) $approvalStatusMeta['class']); ?>"><?php echo dashboard_h((string) $approvalStatusMeta['label']); ?></span>
                  <span class="badge badge-light border"><?php echo dashboard_h(atenea_capacitacion_type_label((string) ($enrollment['tipo_programa'] ?? 'curso'))); ?></span>
                </div>

                <h4 class="mb-2"><?php echo dashboard_h((string) ($enrollment['programa_titulo'] ?? 'Curso inscrito')); ?></h4>
                <p class="text-muted mb-3">
                  <?php echo nl2br(dashboard_h((string) ($enrollment['programa_descripcion_corta'] ?? ''))); ?>
                </p>

                <div class="row">
                  <div class="col-sm-6">
                    <p class="mb-2"><strong>Instructor:</strong> <?php echo dashboard_h((string) ($enrollment['programa_instructor'] ?? 'Por definir')); ?></p>
                    <p class="mb-2"><strong>Nivel:</strong> <?php echo dashboard_h((string) ($enrollment['programa_nivel'] ?? 'Por definir')); ?></p>
                    <p class="mb-2"><strong>Fecha de inscripcion:</strong> <?php echo dashboard_h(record_escolar_format_date((string) ($enrollment['fecha_inscripcion'] ?? ''))); ?></p>
                  </div>
                  <div class="col-sm-6">
                    <p class="mb-2"><strong>Duracion:</strong> <?php echo dashboard_h(atenea_capacitacion_text_value($enrollment['duracion'] ?? '') !== '' ? (string) $enrollment['duracion'] : 'Por definir'); ?></p>
                    <p class="mb-2"><strong>Modalidad:</strong> <?php echo dashboard_h(atenea_capacitacion_text_value($enrollment['modalidad'] ?? '') !== '' ? (string) $enrollment['modalidad'] : 'Por definir'); ?></p>
                    <p class="mb-2"><strong>Progreso:</strong> <?php echo $progress; ?>%</p>
                  </div>
                </div>

                <div class="atenea-record-progress mt-2 mb-3">
                  <span style="width: <?php echo $progress; ?>%;"></span>
                </div>

                <div class="d-flex flex-wrap" style="gap: 0.75rem;">
                  <a href="mi_curso_activo.php?programa=<?php echo (int) $enrollment['programa_id']; ?>" class="btn btn-outline-success btn-sm">Ver curso</a>
                  <a href="curso_videos.php?programa=<?php echo (int) $enrollment['programa_id']; ?>" class="btn btn-outline-primary btn-sm">Videos</a>
                </div>
              </div>
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
    'bodyClass' => 'atenea-record-page',
    'pageTitle' => 'Record escolar',
    'roleLabel' => 'Usuario registrado',
    'welcomeTitle' => 'Seguimiento academico de tu capacitacion',
    'welcomeText' => 'Aqui se resume el curso inscrito, su estado, el avance acumulado y la aprobacion registrada para tu perfil.',
    'profileUrl' => 'usuario_vista.php',
    'logoutUrl' => 'logout.php?redirect=homepage.php',
    'navSections' => atenea_capacitacion_user_nav_sections('record_escolar.php'),
    'cardsColumnClass' => 'col-12 col-md-6 col-xl-4 mb-4',
    'cards' => [
        ['title' => 'Inscripciones', 'value' => count($enrollments), 'icon' => 'school', 'accent' => 'primary', 'href' => 'record_escolar.php', 'metricLabel' => 'Registros en tu cuenta', 'footerLabel' => 'Ver historial'],
        ['title' => 'Cursos activos', 'value' => $activeCount, 'icon' => 'workspace_premium', 'accent' => 'success', 'href' => 'mi_curso_activo.php', 'metricLabel' => 'Accesos vigentes', 'footerLabel' => 'Ver curso'],
        ['title' => 'Aprobados', 'value' => $approvedCount, 'icon' => 'verified', 'accent' => 'warning', 'href' => 'record_escolar.php', 'metricLabel' => 'Resultados finales', 'footerLabel' => 'Seguir avance'],
    ],
    'quickLinks' => [
        ['label' => 'Mi curso activo', 'href' => 'mi_curso_activo.php', 'icon' => 'workspace_premium'],
        ['label' => 'Videos del curso', 'href' => 'curso_videos.php', 'icon' => 'play_circle'],
        ['label' => 'Capacitacion', 'href' => 'educacion.php', 'icon' => 'public'],
    ],
    'summaryItems' => [
        ['label' => 'Total de registros', 'value' => (string) count($enrollments)],
        ['label' => 'Cursos activos', 'value' => (string) $activeCount],
        ['label' => 'Aprobados', 'value' => (string) $approvedCount],
        ['label' => 'Ultima actividad', 'value' => $enrollments !== [] ? record_escolar_format_date((string) ($enrollments[0]['updated_at'] ?? '')) : 'No disponible'],
    ],
    'heroBadges' => [
        count($enrollments) . ' registros',
        $activeCount . ' activos',
        $approvedCount . ' aprobados',
    ],
    'heroActions' => [
        ['label' => 'Mi curso activo', 'href' => 'mi_curso_activo.php', 'icon' => 'workspace_premium'],
        ['label' => 'Videos del curso', 'href' => 'curso_videos.php', 'icon' => 'play_circle', 'variant' => 'outline'],
    ],
    'bodySectionsHtml' => $bodySectionsHtml,
]);
