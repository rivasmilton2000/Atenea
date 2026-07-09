<!DOCTYPE html>
<html lang="es">
<?php include '../includes/connection.php'; ?>
<?php
require_once '../includes/atenea_capacitacion.php';

$sqlProgramas = "
    SELECT pe.*,
           " . atenea_capacitacion_select_sql($db, 'pe') . "
    FROM programas_educativos pe
    WHERE pe.estado = 1
    ORDER BY pe.orden
";
$resultadoProgramas = mysqli_query($db, $sqlProgramas);

if (!$resultadoProgramas) {
    die('Error en la consulta programas: ' . mysqli_error($db));
}
?>
<?php include '../includes/head_home.php'; ?>

<body>
  <?php include '../includes/navbar_home.php'; ?>

  <section class="container-fluid atenea-edu-hero">
    <div class="atenea-edu-hero-inner">
      <p class="atenea-edu-kicker">Atenea Escuela de Naturopat&iacute;a Hol&iacute;stica</p>
      <h1 class="atenea-edu-title">Capacitaci&oacute;n</h1>
      <p class="atenea-edu-summary">
        Nuestra oferta integra programas formativos, cursos y certificaciones en terapias naturales,
        combinando conocimiento acad&eacute;mico, pr&aacute;ctica guiada y acompa&ntilde;amiento humano.
      </p>
    </div>
  </section>

  <div class="container-fluid pt-5" id="capacitacion-catalogo">
    <div class="container">
      <div class="text-center pb-2">
        <p class="section-title px-5">
          <span class="px-2">Programas de capacitaci&oacute;n</span>
        </p>
        <h1 class="mb-4">Formaci&oacute;n integral en Naturopat&iacute;a</h1>
      </div>
      <div class="row">
        <?php if (mysqli_num_rows($resultadoProgramas) > 0) : ?>
          <?php while ($programa = mysqli_fetch_assoc($resultadoProgramas)) : ?>
            <?php
            $descripcionPrograma = trim((string) ($programa['descripcion_corta'] ?: $programa['descripcion_completa']));
            $programType = atenea_capacitacion_normalize_type((string) ($programa['tipo_programa'] ?? 'curso'));
            $programDuration = atenea_capacitacion_text_value($programa['duracion'] ?? '');
            $programMode = atenea_capacitacion_text_value($programa['modalidad'] ?? '');
            $programPrice = atenea_capacitacion_price($programa);
            ?>
            <div class="col-lg-4 mb-5">
              <div class="card border-0 bg-light shadow-sm pb-2 h-100">
                <img
                  class="card-img-top mb-2"
                  src="../img/<?php echo htmlspecialchars((string) $programa['imagen'], ENT_QUOTES, 'UTF-8'); ?>"
                  alt="<?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                  style="height: 200px; object-fit: cover;"
                >
                <div class="card-body text-center d-flex flex-column">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="atenea-offer-badge atenea-offer-badge--<?php echo htmlspecialchars($programType, ENT_QUOTES, 'UTF-8'); ?>">
                      <?php echo htmlspecialchars(atenea_capacitacion_type_label($programType), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="font-weight-bold text-primary">$<?php echo number_format($programPrice, 2); ?></span>
                  </div>
                  <h4 class="card-title"><?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?></h4>
                  <p class="card-text text-justify flex-grow-1">
                    <?php echo htmlspecialchars($descripcionPrograma, ENT_QUOTES, 'UTF-8'); ?>
                  </p>
                </div>
                <div class="card-footer bg-transparent py-4 px-5">
                  <div class="row border-bottom">
                    <div class="col-6 py-1 text-right border-right">
                      <strong>Nivel</strong>
                    </div>
                    <div class="col-6 py-1"><?php echo htmlspecialchars((string) $programa['nivel'], ENT_QUOTES, 'UTF-8'); ?></div>
                  </div>
                  <div class="row border-bottom">
                    <div class="col-6 py-1 text-right border-right">
                      <strong>Instructor</strong>
                    </div>
                    <div class="col-6 py-1"><?php echo htmlspecialchars((string) $programa['instructor'], ENT_QUOTES, 'UTF-8'); ?></div>
                  </div>
                  <div class="row border-bottom">
                    <div class="col-6 py-1 text-right border-right">
                      <strong>Duraci&oacute;n</strong>
                    </div>
                    <div class="col-6 py-1"><?php echo htmlspecialchars($programDuration !== '' ? $programDuration : 'Por definir', ENT_QUOTES, 'UTF-8'); ?></div>
                  </div>
                  <div class="row border-bottom">
                    <div class="col-6 py-1 text-right border-right">
                      <strong>Modalidad</strong>
                    </div>
                    <div class="col-6 py-1"><?php echo htmlspecialchars($programMode !== '' ? $programMode : 'Por definir', ENT_QUOTES, 'UTF-8'); ?></div>
                  </div>
                  <a href="<?php echo htmlspecialchars(atenea_capacitacion_detail_url((int) $programa['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary2 btn-block mt-4">
                    Cotizar
                  </a>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else : ?>
          <div class="col-12">
            <p class="text-center">No hay programas de capacitaci&oacute;n disponibles en este momento.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php include '../includes/footer_home.php'; ?>

  <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa fa-angle-double-up"></i></a>

  <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
  <script src="../libs/easing/easing.min.js"></script>
  <script src="../libs/owlcarousel/owl.carousel.min.js"></script>
  <script src="../libs/isotope/isotope.pkgd.min.js"></script>
  <script src="../libs/lightbox/js/lightbox.min.js"></script>
  <script src="../mail/jqBootstrapValidation.min.js"></script>
  <script src="../mail/contact.js"></script>
  <script src="../js/main.js"></script>
</body>
</html>
