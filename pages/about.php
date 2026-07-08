<!DOCTYPE html>
<html lang="es">
<?php include '../includes/connection.php'; ?>
<?php
$sql_about = 'SELECT * FROM about WHERE estado = 1 LIMIT 1';
$resultado_about = mysqli_query($db, $sql_about);

if (!$resultado_about) {
    die("Error en la consulta about: " . mysqli_error($db));
}

$about = mysqli_fetch_assoc($resultado_about);
?>
<?php include '../includes/head_home.php'; ?>

<body>
  <?php include '../includes/navbar_home.php'; ?>

  <section class="container-fluid atenea-about-hero">
    <div class="atenea-about-hero-inner">
      <p class="atenea-about-kicker">Atenea Escuela de Naturopatía Holística</p>
      <h1 class="atenea-about-title">¿Quiénes somos?</h1>
      <p class="atenea-about-summary">
        Atenea Escuela es una institución dedicada a la formación integral en terapias naturales y salud holística.
        Nos enfocamos en brindar capacitación académica y práctica con un enfoque humano y ético.
      </p>
    </div>
  </section>

  <div class="container-fluid py-5">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-5">
          <img
            class="img-fluid rounded mb-5 mb-lg-0"
            src="../img/<?php echo $about['imagen2']; ?>"
            alt="<?php echo $about['titulo']; ?>"
          >
        </div>
        <div class="col-lg-7">
          <p class="section-title pr-5">
            <span class="pr-2">Descubre más sobre Atenea Escuela de Naturopatía Holística</span>
          </p>
          <h1 class="mb-4"><?php echo $about['titulo']; ?></h1>
          <p class="text-justify">
            <?php echo nl2br($about['descripcion']); ?>
          </p>
        </div>
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
