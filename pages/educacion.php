<!DOCTYPE html>
<html lang="es">
<?php include '../includes/connection.php'; ?>
<?php
$sql_programas = "SELECT * FROM programas_educativos WHERE estado = 1 ORDER BY orden";
$resultado_programas = mysqli_query($db, $sql_programas);

if (!$resultado_programas) {
    die("Error en la consulta programas: " . mysqli_error($db));
}
?>
<?php include '../includes/head_home.php'; ?>

<body>
  <?php include '../includes/navbar_home.php'; ?>

  <section class="container-fluid atenea-edu-hero">
    <div class="atenea-edu-hero-inner">
      <p class="atenea-edu-kicker">Atenea Escuela de Naturopatía Holística</p>
      <h1 class="atenea-edu-title">Educación</h1>
      <p class="atenea-edu-summary">
        Nuestros programas están diseñados para brindar una formación integral en terapias naturales,
        combinando conocimiento académico, práctica guiada y acompañamiento humano.
      </p>
    </div>
  </section>

  <div class="container-fluid pt-5">
    <div class="container">
      <div class="text-center pb-2">
        <p class="section-title px-5">
          <span class="px-2">Nuestros programas</span>
        </p>
        <h1 class="mb-4">Formación integral en Naturopatía</h1>
      </div>
      <div class="row">
        <?php if (mysqli_num_rows($resultado_programas) > 0) : ?>
          <?php while ($programa = mysqli_fetch_assoc($resultado_programas)) : ?>
            <div class="col-lg-4 mb-5">
              <div class="card border-0 bg-light shadow-sm pb-2 h-100">
                <img class="card-img-top mb-2" src="../img/<?php echo $programa['imagen']; ?>" alt="<?php echo $programa['titulo']; ?>" style="height: 200px; object-fit: cover;">
                <div class="card-body text-center d-flex flex-column">
                  <h4 class="card-title"><?php echo $programa['titulo']; ?></h4>
                  <p class="card-text text-justify flex-grow-1">
                    <?php echo $programa['descripcion_completa']; ?>
                  </p>
                </div>
                <div class="card-footer bg-transparent py-4 px-5">
                  <div class="row border-bottom">
                    <div class="col-6 py-1 text-right border-right">
                      <strong>Nivel</strong>
                    </div>
                    <div class="col-6 py-1"><?php echo $programa['nivel']; ?></div>
                  </div>
                  <div class="row border-bottom">
                    <div class="col-6 py-1 text-right border-right">
                      <strong>Instructor</strong>
                    </div>
                    <div class="col-6 py-1"><?php echo $programa['instructor']; ?></div>
                  </div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else : ?>
          <div class="col-12"><p class="text-center">No hay programas educativos disponibles en este momento.</p></div>
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
