<!DOCTYPE html>
<html lang="es">
<?php include '../includes/connection.php'; ?>
<?php
require_once '../includes/atenea_catalog.php';

$sql_programas = "SELECT * FROM programas_educativos WHERE estado = 1 ORDER BY orden";
$resultado_programas = mysqli_query($db, $sql_programas);

if (!$resultado_programas) {
    die("Error en la consulta programas: " . mysqli_error($db));
}

$catalogSchema = atenea_catalog_product_schema_flags($db);
$resultado_ofertas = null;
$mostrarOfertasCatalogo = false;

if ($catalogSchema['tipo_oferta']) {
    $sql_ofertas = "
        SELECT p.id, p.nombre, p.descripcion_corta, p.precio, p.precio_descuento, p.imagen, p.stock,
               " . atenea_catalog_product_select_sql($db, 'p') . "
        FROM productos p
        WHERE p.estado = 1
          AND p.tipo_oferta IN ('curso', 'certificacion')
        ORDER BY FIELD(p.tipo_oferta, 'curso', 'certificacion'), p.id DESC
    ";

    $resultado_ofertas = mysqli_query($db, $sql_ofertas);
    $mostrarOfertasCatalogo = $resultado_ofertas instanceof mysqli_result;
}
?>
<?php include '../includes/head_home.php'; ?>

<body>
  <?php include '../includes/navbar_home.php'; ?>

  <section class="container-fluid atenea-edu-hero">
    <div class="atenea-edu-hero-inner">
      <p class="atenea-edu-kicker">Atenea Escuela de Naturopatía Holística</p>
      <h1 class="atenea-edu-title">Capacitación</h1>
      <p class="atenea-edu-summary">
        Nuestra oferta integra programas formativos, cursos y certificaciones en terapias naturales,
        combinando conocimiento académico, práctica guiada y acompañamiento humano.
      </p>
    </div>
  </section>

  <div class="container-fluid pt-5">
    <div class="container">
      <div class="text-center pb-2">
        <p class="section-title px-5">
          <span class="px-2">Programas de capacitación</span>
        </p>
        <h1 class="mb-4">Formación integral en Naturopatía</h1>
      </div>
      <div class="row">
        <?php if (mysqli_num_rows($resultado_programas) > 0) : ?>
          <?php while ($programa = mysqli_fetch_assoc($resultado_programas)) : ?>
            <?php $descripcionPrograma = trim((string) ($programa['descripcion_corta'] ?: $programa['descripcion_completa'])); ?>
            <div class="col-lg-4 mb-5">
              <div class="card border-0 bg-light shadow-sm pb-2 h-100">
                <img
                  class="card-img-top mb-2"
                  src="../img/<?php echo htmlspecialchars((string) $programa['imagen'], ENT_QUOTES, 'UTF-8'); ?>"
                  alt="<?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                  style="height: 200px; object-fit: cover;"
                >
                <div class="card-body text-center d-flex flex-column">
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
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else : ?>
          <div class="col-12">
            <p class="text-center">No hay programas de capacitación disponibles en este momento.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ($mostrarOfertasCatalogo) : ?>
    <div class="container-fluid pb-5">
      <div class="container">
        <div class="text-center pb-2">
          <p class="section-title px-5">
            <span class="px-2">Cursos y certificaciones</span>
          </p>
          <h1 class="mb-4">Oferta especializada de Atenea</h1>
          <p class="mb-0">Explora opciones del catálogo con precio, duración y recursos audiovisuales cuando estén disponibles.</p>
        </div>
        <div class="row mt-4">
          <?php if ($resultado_ofertas !== null && mysqli_num_rows($resultado_ofertas) > 0) : ?>
            <?php while ($oferta = mysqli_fetch_assoc($resultado_ofertas)) : ?>
              <?php
              $offerType = atenea_catalog_normalize_type((string) ($oferta['tipo_oferta'] ?? 'producto'));
              $offerLabel = atenea_catalog_type_label($offerType);
              $precioFinal = !empty($oferta['precio_descuento']) ? (float) $oferta['precio_descuento'] : (float) $oferta['precio'];
              $duration = trim((string) ($oferta['duracion'] ?? ''));
              $stockLabel = atenea_catalog_stock_label($offerType);
              $hasVideo = atenea_catalog_has_active_video($oferta);
              ?>
              <div class="col-lg-4 mb-5">
                <div class="card border-0 bg-light shadow-sm pb-2 h-100">
                  <img
                    class="card-img-top mb-2"
                    src="../img/<?php echo htmlspecialchars((string) $oferta['imagen'], ENT_QUOTES, 'UTF-8'); ?>"
                    alt="<?php echo htmlspecialchars((string) $oferta['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                    style="height: 200px; object-fit: cover;"
                  >
                  <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                      <span class="atenea-offer-badge atenea-offer-badge--<?php echo htmlspecialchars($offerType, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($offerLabel, ENT_QUOTES, 'UTF-8'); ?>
                      </span>
                      <span class="font-weight-bold text-primary">$<?php echo number_format($precioFinal, 2); ?></span>
                    </div>
                    <h4 class="card-title"><?php echo htmlspecialchars((string) $oferta['nombre'], ENT_QUOTES, 'UTF-8'); ?></h4>
                    <p class="card-text text-justify flex-grow-1">
                      <?php echo htmlspecialchars((string) $oferta['descripcion_corta'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <div class="atenea-offer-meta">
                      <span><?php echo htmlspecialchars($stockLabel, ENT_QUOTES, 'UTF-8'); ?>: <?php echo (int) $oferta['stock']; ?></span>
                      <?php if ($duration !== '') : ?>
                        <span>Duración: <?php echo htmlspecialchars($duration, ENT_QUOTES, 'UTF-8'); ?></span>
                      <?php endif; ?>
                      <?php if ($hasVideo) : ?>
                        <span>Video disponible</span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="card-footer bg-transparent py-3 px-4">
                    <a href="producto_detalle.php?id=<?php echo (int) $oferta['id']; ?>" class="btn btn-primary btn-block">Ver detalle</a>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else : ?>
            <div class="col-12">
              <p class="text-center">Aún no hay cursos o certificaciones publicados en el catálogo.</p>
            </div>
          <?php endif; ?>
        </div>
        <div class="text-center">
          <a href="productos.php?tipo=curso" class="btn btn-primary2 py-2 px-4 mr-2">Ver cursos</a>
          <a href="productos.php?tipo=certificacion" class="btn btn-outline-primary py-2 px-4">Ver certificaciones</a>
        </div>
      </div>
    </div>
  <?php endif; ?>

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
