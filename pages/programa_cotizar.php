<!DOCTYPE html>
<html lang="es">
<?php
require 'session.php';
require_once '../includes/atenea_auth.php';
require_once '../includes/atenea_capacitacion.php';
include '../includes/connection.php';

$programaId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($programaId <= 0) {
    header('Location: educacion.php');
    exit;
}

$stmtPrograma = $db->prepare(
    "SELECT pe.*,
            " . atenea_capacitacion_select_sql($db, 'pe') . "
     FROM programas_educativos pe
     WHERE pe.id = ? AND pe.estado = 1
     LIMIT 1"
);

if (!$stmtPrograma) {
    header('Location: educacion.php');
    exit;
}

$stmtPrograma->bind_param('i', $programaId);
$stmtPrograma->execute();
$resultadoPrograma = $stmtPrograma->get_result();
$programa = $resultadoPrograma instanceof mysqli_result ? $resultadoPrograma->fetch_assoc() : null;
$stmtPrograma->close();

if (!$programa) {
    header('Location: educacion.php');
    exit;
}

if (!logged_in()) {
    header('Location: ' . atenea_capacitacion_login_quote_url((int) $programa['id']));
    exit;
}

$programPrice = atenea_capacitacion_price($programa);
$programTypeLabel = atenea_capacitacion_type_label((string) ($programa['tipo_programa'] ?? 'curso'));
$programDuration = atenea_capacitacion_text_value($programa['duracion'] ?? '');
$programMode = atenea_capacitacion_text_value($programa['modalidad'] ?? '');

$_SESSION['ATENEA_PENDING_PROGRAM_QUOTE'] = [
    'program_id' => (int) $programa['id'],
    'titulo' => (string) $programa['titulo'],
    'tipo_programa' => (string) ($programa['tipo_programa'] ?? 'curso'),
    'precio' => $programPrice,
    'requested_at' => date('c'),
];
?>

<?php include '../includes/head_home.php'; ?>

<body>
  <?php include '../includes/navbar_home.php'; ?>

  <section class="container-fluid atenea-producto-detalle-hero">
    <div class="atenea-producto-detalle-hero-inner">
      <p class="atenea-producto-detalle-kicker">Atenea Escuela de Naturopat&iacute;a Hol&iacute;stica</p>
      <h1 class="atenea-producto-detalle-title">Cotizaci&oacute;n preparada</h1>
      <p class="atenea-producto-detalle-summary">
        Dejamos lista la validaci&oacute;n base de esta capacitaci&oacute;n para conectarla con la inscripci&oacute;n final en la siguiente fase.
      </p>
    </div>
  </section>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4 p-lg-5">
            <h2 class="mb-4"><?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <div class="row">
              <div class="col-md-6 mb-3">
                <p class="mb-2"><strong>Tipo:</strong> <?php echo htmlspecialchars($programTypeLabel, ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mb-2"><strong>Precio inicial:</strong> $<?php echo number_format($programPrice, 2); ?></p>
                <p class="mb-2"><strong>Nivel:</strong> <?php echo htmlspecialchars((string) $programa['nivel'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mb-0"><strong>Instructor:</strong> <?php echo htmlspecialchars((string) $programa['instructor'], ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
              <div class="col-md-6 mb-3">
                <p class="mb-2"><strong>Duraci&oacute;n:</strong> <?php echo htmlspecialchars($programDuration !== '' ? $programDuration : 'Por definir', ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mb-2"><strong>Modalidad:</strong> <?php echo htmlspecialchars($programMode !== '' ? $programMode : 'Por definir', ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mb-0"><strong>Estado del flujo:</strong> Base de cotizaci&oacute;n lista para la siguiente fase.</p>
              </div>
            </div>

            <div class="alert alert-success mt-4 mb-4" role="alert">
              Tu sesi&oacute;n est&aacute; activa y el programa fue guardado como referencia de cotizaci&oacute;n en la sesi&oacute;n actual.
            </div>

            <p class="text-muted mb-4">
              En esta fase no se completa todav&iacute;a la inscripci&oacute;n final ni la aprobaci&oacute;n del curso. El objetivo era dejar lista la ruta p&uacute;blica, el detalle din&aacute;mico y la validaci&oacute;n base para continuar despu&eacute;s.
            </p>

            <div class="d-flex flex-wrap gap-2">
              <a href="<?php echo htmlspecialchars(atenea_capacitacion_detail_url((int) $programa['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary2 mr-2 mb-2">
                Volver al detalle
              </a>
              <a href="educacion.php" class="btn btn-outline-dark mr-2 mb-2">
                Seguir explorando capacitacion
              </a>
              <a href="<?php echo htmlspecialchars(atenea_dashboard_route_for_session(), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary mb-2">
                Ir a mi panel
              </a>
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
