<!DOCTYPE html>
<html lang="es">
<?php include '../includes/connection.php'; ?>
<?php
$sql_galeria_fotos = "SELECT * FROM galeria WHERE estado = 1 ORDER BY orden";
$resultado_galeria_fotos = mysqli_query($db, $sql_galeria_fotos);

if (!$resultado_galeria_fotos) {
    die("Error en la consulta galeria: " . mysqli_error($db));
}

$sql_categorias = "SELECT DISTINCT categoria FROM galeria WHERE estado = 1 ORDER BY categoria";
$resultado_categorias = mysqli_query($db, $sql_categorias);
?>
<?php include '../includes/head_home.php'; ?>

<body>
  <?php include '../includes/navbar_home.php'; ?>

  <section class="container-fluid atenea-galeria-hero">
    <div class="atenea-galeria-hero-inner">
      <p class="atenea-galeria-kicker">Atenea Escuela de Naturopatía Holística</p>
      <h1 class="atenea-galeria-title">Galería</h1>
      <p class="atenea-galeria-summary">
        Explora nuestros espacios, actividades y experiencias formativas.
        Cada imagen refleja el compromiso de Atenea con una capacitación práctica, humana y transformadora.
      </p>
    </div>
  </section>

  <div class="container-fluid pt-5 pb-3">
    <div class="container">
      <div class="text-center pb-2">
        <p class="section-title px-5">
          <span class="px-2">Nuestra galería</span>
        </p>
        <h1 class="mb-4">Conoce nuestras instalaciones</h1>
      </div>
      <div class="row">
        <div class="col-12 text-center mb-2">
          <ul class="list-inline mb-4" id="portfolio-flters">
            <li class="btn btn-outline-primary m-1 active" data-filter="*">Todo</li>
            <?php
            $categorias_nombres = [
                'terapias' => 'Terapias',
                'nutricion' => 'Nutrición',
                'general' => 'General',
                'laboratorio' => 'Laboratorio',
            ];

            mysqli_data_seek($resultado_categorias, 0);
            while ($cat = mysqli_fetch_assoc($resultado_categorias)) :
                $categoria = $cat['categoria'];
                $nombre = $categorias_nombres[$categoria] ?? ucfirst($categoria);
            ?>
              <li class="btn btn-outline-primary m-1" data-filter=".<?php echo $categoria; ?>">
                <?php echo $nombre; ?>
              </li>
            <?php endwhile; ?>
          </ul>
        </div>
      </div>
      <div class="row portfolio-container">
        <?php
        mysqli_data_seek($resultado_galeria_fotos, 0);
        while ($foto = mysqli_fetch_assoc($resultado_galeria_fotos)) :
        ?>
          <div class="col-lg-4 col-md-6 mb-4 portfolio-item <?php echo $foto['categoria']; ?>">
            <div class="position-relative overflow-hidden mb-2" style="height: 250px;">
              <img class="img-fluid w-100 h-100" src="../img/<?php echo $foto['imagen']; ?>" alt="<?php echo $foto['titulo']; ?>" style="object-fit: cover;">
              <div class="portfolio-btn bg-primary position-absolute w-100 h-100 d-flex align-items-center justify-content-center" style="top: 0; left: 0; opacity: 0; transition: all 0.3s;">
                <a href="../img/<?php echo $foto['imagen']; ?>" data-lightbox="portfolio">
                  <i class="fa fa-plus text-white" style="font-size: 60px"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
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
