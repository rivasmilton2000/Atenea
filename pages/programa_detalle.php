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

$programType = atenea_capacitacion_normalize_type((string) ($programa['tipo_programa'] ?? 'curso'));
$programTypeLabel = atenea_capacitacion_type_label($programType);
$programPrice = atenea_capacitacion_price($programa);
$programDuration = atenea_capacitacion_text_value($programa['duracion'] ?? '');
$programMode = atenea_capacitacion_text_value($programa['modalidad'] ?? '');
$programDescription = trim((string) ($programa['descripcion_completa'] ?: $programa['descripcion_corta']));
$programDetails = atenea_capacitacion_text_items($programa['detalles_programa'] ?? '');
$programBenefits = atenea_capacitacion_text_items($programa['beneficios'] ?? '');
$programRequirements = atenea_capacitacion_text_items($programa['requisitos'] ?? '');
$quoteUrl = logged_in()
    ? atenea_capacitacion_quote_url((int) $programa['id'])
    : atenea_capacitacion_login_quote_url((int) $programa['id']);
$quoteLabel = logged_in() ? 'Continuar con la cotizacion' : 'Iniciar sesion para cotizar';
?>

<?php include '../includes/head_home.php'; ?>

<body>
  <?php include '../includes/navbar_home.php'; ?>

  <section class="container-fluid atenea-producto-detalle-hero">
    <div class="atenea-producto-detalle-hero-inner">
      <p class="atenea-producto-detalle-kicker">Atenea Escuela de Naturopat&iacute;a Hol&iacute;stica</p>
      <h1 class="atenea-producto-detalle-title"><?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?></h1>
      <p class="atenea-producto-detalle-summary">
        Revisa la informaci&oacute;n completa de esta capacitaci&oacute;n antes de continuar con tu cotizaci&oacute;n.
      </p>
    </div>
  </section>

  <div class="container py-5">
    <div class="row align-items-start">
      <div class="col-lg-5 mb-4">
        <div class="card border-0 shadow-sm">
          <img
            src="../img/<?php echo htmlspecialchars((string) $programa['imagen'], ENT_QUOTES, 'UTF-8'); ?>"
            class="img-fluid rounded"
            alt="<?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
          >
        </div>
      </div>

      <div class="col-lg-7">
        <div class="atenea-offer-meta mb-3">
          <span class="atenea-detail-pill atenea-offer-badge atenea-offer-badge--<?php echo htmlspecialchars($programType, ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars($programTypeLabel, ENT_QUOTES, 'UTF-8'); ?>
          </span>
          <span class="atenea-detail-pill">Nivel: <?php echo htmlspecialchars((string) $programa['nivel'], ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="atenea-detail-pill">Instructor: <?php echo htmlspecialchars((string) $programa['instructor'], ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="atenea-detail-pill">Duraci&oacute;n: <?php echo htmlspecialchars($programDuration !== '' ? $programDuration : 'Por definir', ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="atenea-detail-pill">Modalidad: <?php echo htmlspecialchars($programMode !== '' ? $programMode : 'Por definir', ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <h2 class="font-weight-bold"><?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <h3 class="text-primary mb-4">$<?php echo number_format($programPrice, 2); ?></h3>

        <div class="mb-4">
          <h4>Descripci&oacute;n</h4>
          <p class="text-justify mb-0">
            <?php echo nl2br(htmlspecialchars($programDescription, ENT_QUOTES, 'UTF-8')); ?>
          </p>
        </div>

        <div class="row mb-4">
          <div class="col-md-6 mb-3">
            <div class="card border-0 bg-light h-100 shadow-sm">
              <div class="card-body">
                <h5 class="mb-3">Resumen</h5>
                <p class="mb-2"><strong>Precio:</strong> $<?php echo number_format($programPrice, 2); ?></p>
                <p class="mb-2"><strong>Tipo:</strong> <?php echo htmlspecialchars($programTypeLabel, ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mb-2"><strong>Duraci&oacute;n:</strong> <?php echo htmlspecialchars($programDuration !== '' ? $programDuration : 'Por definir', ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mb-2"><strong>Modalidad:</strong> <?php echo htmlspecialchars($programMode !== '' ? $programMode : 'Por definir', ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mb-0"><strong>Instructor:</strong> <?php echo htmlspecialchars((string) $programa['instructor'], ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="card border-0 bg-light h-100 shadow-sm">
              <div class="card-body">
                <h5 class="mb-3">Acceso</h5>
                <p class="mb-3">
                  <?php if (logged_in()) : ?>
                    Tu sesi&oacute;n est&aacute; activa y ya puedes continuar con la cotizaci&oacute;n base de este programa.
                  <?php else : ?>
                    Si deseas continuar con la cotizaci&oacute;n, inicia sesi&oacute;n y retomaremos este curso desde aqu&iacute;.
                  <?php endif; ?>
                </p>
                <a href="<?php echo htmlspecialchars($quoteUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary2 btn-block mb-2">
                  <?php echo htmlspecialchars($quoteLabel, ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <a href="educacion.php" class="btn btn-outline-dark btn-block">Volver a capacitacion</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-2">
      <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h4 class="mb-3">Detalles del programa</h4>
            <?php if ($programDetails !== []) : ?>
              <ul class="mb-0 pl-3">
                <?php foreach ($programDetails as $detailItem) : ?>
                  <li class="mb-2"><?php echo htmlspecialchars($detailItem, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
              </ul>
            <?php else : ?>
              <p class="mb-0">El detalle completo del programa se puede completar desde el panel administrativo.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h4 class="mb-3">Beneficios</h4>
            <?php if ($programBenefits !== []) : ?>
              <ul class="mb-0 pl-3">
                <?php foreach ($programBenefits as $benefitItem) : ?>
                  <li class="mb-2"><?php echo htmlspecialchars($benefitItem, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
              </ul>
            <?php else : ?>
              <p class="mb-0">Los beneficios de esta capacitaci&oacute;n pueden editarse desde el panel cuando se definan.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h4 class="mb-3">Requisitos</h4>
            <?php if ($programRequirements !== []) : ?>
              <ul class="mb-0 pl-3">
                <?php foreach ($programRequirements as $requirementItem) : ?>
                  <li class="mb-2"><?php echo htmlspecialchars($requirementItem, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
              </ul>
            <?php else : ?>
              <p class="mb-0">Por ahora no se han definido requisitos obligatorios para esta capacitaci&oacute;n.</p>
            <?php endif; ?>
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
