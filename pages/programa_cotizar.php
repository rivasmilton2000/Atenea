<!DOCTYPE html>
<html lang="es">
<?php
require 'session.php';
require_once '../includes/atenea_auth.php';
require_once '../includes/atenea_capacitacion.php';
include '../includes/connection.php';

$programaId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$programa = atenea_capacitacion_fetch_program_by_id($db, $programaId, true);

if (!$programa) {
    header('Location: educacion.php');
    exit;
}

if (!logged_in()) {
    header('Location: ' . atenea_capacitacion_login_quote_url((int) $programa['id']));
    exit;
}

if (!atenea_session_is_public_user()) {
    atenea_render_auth_alert(
        'info',
        'Flujo disponible para usuarios registrados',
        'La inscripcion publica a capacitacion solo esta disponible para cuentas de usuario del sitio.',
        atenea_dashboard_route_for_session()
    );
}

$phaseTwoReady = atenea_capacitacion_phase_two_ready($db);
$publicUserId = (int) ($_SESSION['PUBLIC_USER_ID'] ?? 0);
$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);
$currentEnrollment = $phaseTwoReady
    ? atenea_capacitacion_fetch_active_enrollment_for_public_user($db, $publicUserId, (int) $programa['id'])
    : null;

$_SESSION['ATENEA_PENDING_PROGRAM_QUOTE'] = [
    'program_id' => (int) $programa['id'],
    'titulo' => (string) $programa['titulo'],
    'tipo_programa' => (string) ($programa['tipo_programa'] ?? 'curso'),
    'precio' => atenea_capacitacion_price($programa),
    'requested_at' => date('c'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['course_action'] ?? '') === 'confirm_enrollment') {
    if (!$phaseTwoReady) {
        atenea_render_auth_alert(
            'warning',
            'Migracion pendiente',
            'Antes de continuar debes aplicar la migracion Database/migrations/2026_07_09_capacitacion_acceso_videos.sql.',
            atenea_capacitacion_detail_url((int) $programa['id'])
        );
    }

    $enrollment = atenea_capacitacion_activate_enrollment($db, $publicUserId, $memberId, (int) $programa['id']);
    if (!$enrollment) {
        atenea_render_auth_alert(
            'error',
            'No pudimos activar tu curso',
            'Ocurrio un problema al registrar la inscripcion. Intenta nuevamente en unos minutos.',
            atenea_capacitacion_detail_url((int) $programa['id'])
        );
    }

    $_SESSION['ATENEA_ACTIVE_PROGRAM_ID'] = (int) $programa['id'];
    $_SESSION['ATENEA_ACTIVE_COURSE_STATUS'] = 'curso_activo';

    atenea_render_auth_alert(
        'success',
        'Inscripcion confirmada',
        'Tu perfil ya refleja un curso activo y el acceso base al curso quedo preparado.',
        'mi_curso_activo.php?programa=' . (int) $programa['id']
    );
}

$programPrice = atenea_capacitacion_price($programa);
$programTypeLabel = atenea_capacitacion_type_label((string) ($programa['tipo_programa'] ?? 'curso'));
$programDuration = atenea_capacitacion_text_value($programa['duracion'] ?? '');
$programMode = atenea_capacitacion_text_value($programa['modalidad'] ?? '');
$courseStatusMeta = $currentEnrollment ? atenea_capacitacion_course_status_meta((string) $currentEnrollment['estado_curso']) : null;
$approvalStatusMeta = $currentEnrollment ? atenea_capacitacion_approval_status_meta((string) $currentEnrollment['estado_aprobacion']) : null;
?>

<?php include '../includes/head_home.php'; ?>

<body>
  <?php include '../includes/navbar_home.php'; ?>

  <section class="container-fluid atenea-producto-detalle-hero">
    <div class="atenea-producto-detalle-hero-inner">
      <p class="atenea-producto-detalle-kicker">Atenea Escuela de Naturopat&iacute;a Hol&iacute;stica</p>
      <h1 class="atenea-producto-detalle-title">Confirmar inscripci&oacute;n</h1>
      <p class="atenea-producto-detalle-summary">
        Revisa los datos del programa y confirma tu acceso para dejar activo el curso dentro de tu cuenta.
      </p>
    </div>
  </section>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <?php if (!$phaseTwoReady) : ?>
          <div class="alert alert-warning mb-4">
            Para completar la inscripci&oacute;n debes aplicar primero la migraci&oacute;n
            <code>Database/migrations/2026_07_09_capacitacion_acceso_videos.sql</code>.
          </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm overflow-hidden">
          <div class="row no-gutters">
            <div class="col-lg-5">
              <img
                src="../img/<?php echo htmlspecialchars((string) $programa['imagen'], ENT_QUOTES, 'UTF-8'); ?>"
                class="img-fluid h-100 w-100"
                style="object-fit: cover; min-height: 320px;"
                alt="<?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
              >
            </div>
            <div class="col-lg-7">
              <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-wrap align-items-center mb-3" style="gap: 0.5rem;">
                  <span class="badge badge-success px-3 py-2"><?php echo htmlspecialchars($programTypeLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                  <span class="badge badge-light border px-3 py-2">Precio inicial: $<?php echo number_format($programPrice, 2); ?></span>
                  <?php if ($courseStatusMeta !== null) : ?>
                    <span class="badge badge-<?php echo htmlspecialchars((string) $courseStatusMeta['class'], ENT_QUOTES, 'UTF-8'); ?> px-3 py-2">
                      <?php echo htmlspecialchars((string) $courseStatusMeta['label'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                  <?php endif; ?>
                </div>

                <h2 class="mb-3"><?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="text-muted mb-4">
                  <?php echo nl2br(htmlspecialchars((string) ($programa['descripcion_completa'] ?: $programa['descripcion_corta']), ENT_QUOTES, 'UTF-8')); ?>
                </p>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <p class="mb-2"><strong>Nivel:</strong> <?php echo htmlspecialchars((string) $programa['nivel'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="mb-2"><strong>Instructor:</strong> <?php echo htmlspecialchars((string) $programa['instructor'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="mb-0"><strong>Duraci&oacute;n:</strong> <?php echo htmlspecialchars($programDuration !== '' ? $programDuration : 'Por definir', ENT_QUOTES, 'UTF-8'); ?></p>
                  </div>
                  <div class="col-md-6 mb-3">
                    <p class="mb-2"><strong>Modalidad:</strong> <?php echo htmlspecialchars($programMode !== '' ? $programMode : 'Por definir', ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="mb-2"><strong>Estado del flujo:</strong> Preparado para inscripci&oacute;n base</p>
                    <?php if ($approvalStatusMeta !== null) : ?>
                      <p class="mb-0">
                        <strong>Aprobaci&oacute;n actual:</strong>
                        <span class="badge badge-<?php echo htmlspecialchars((string) $approvalStatusMeta['class'], ENT_QUOTES, 'UTF-8'); ?>">
                          <?php echo htmlspecialchars((string) $approvalStatusMeta['label'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                      </p>
                    <?php endif; ?>
                  </div>
                </div>

                <?php if ($currentEnrollment) : ?>
                  <div class="alert alert-success mt-4 mb-4">
                    Ya tienes una inscripci&oacute;n vinculada a este programa. Puedes entrar directamente a tu curso, revisar videos o consultar tu r&eacute;cord escolar.
                  </div>

                  <div class="d-flex flex-wrap" style="gap: 0.75rem;">
                    <a href="mi_curso_activo.php?programa=<?php echo (int) $programa['id']; ?>" class="btn btn-primary2">Ir a mi curso activo</a>
                    <a href="curso_videos.php?programa=<?php echo (int) $programa['id']; ?>" class="btn btn-outline-success">Ver videos</a>
                    <a href="record_escolar.php" class="btn btn-outline-dark">Ver r&eacute;cord escolar</a>
                  </div>
                <?php else : ?>
                  <div class="alert alert-info mt-4 mb-4">
                    Al confirmar esta inscripci&oacute;n, tu cuenta quedar&aacute; vinculada al programa con estado <strong>Curso activo</strong> y se habilitar&aacute; la base para videos y r&eacute;cord escolar.
                  </div>

                  <form method="post">
                    <input type="hidden" name="course_action" value="confirm_enrollment">
                    <div class="d-flex flex-wrap" style="gap: 0.75rem;">
                      <button type="submit" class="btn btn-primary2" <?php echo !$phaseTwoReady ? 'disabled' : ''; ?>>
                        Confirmar inscripci&oacute;n y continuar
                      </button>
                      <a href="<?php echo htmlspecialchars(atenea_capacitacion_detail_url((int) $programa['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-dark">
                        Volver al detalle
                      </a>
                      <a href="educacion.php" class="btn btn-outline-secondary">
                        Seguir explorando capacitaci&oacute;n
                      </a>
                    </div>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include '../includes/footer_home.php'; ?>

  <a href="#" class="btn btn-primary p-3 back-to-top">
    <i class="fa fa-angle-double-up"></i>
  </a>

  <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
  <script src="../libs/easing/easing.min.js"></script>
  <script src="../libs/owlcarousel/owl.carousel.min.js"></script>
  <script src="../js/main.js"></script>
</body>
</html>
