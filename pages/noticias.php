<!DOCTYPE html>
<html lang="es">
<?php include '../includes/connection.php'; ?>
<?php
$sql_noticias = "SELECT id, titulo, descripcion_corta, imagen, fecha_publicacion FROM noticias WHERE estado = 1 ORDER BY fecha_publicacion DESC";
$resultado_noticias = mysqli_query($db, $sql_noticias);

if (!$resultado_noticias) {
    die("Error en la consulta noticias: " . mysqli_error($db));
}
?>
<?php include '../includes/head_home.php'; ?>

<body>
  <?php include '../includes/navbar_home.php'; ?>

  <section class="container-fluid atenea-noticias-hero">
    <div class="atenea-noticias-hero-inner">
      <p class="atenea-noticias-kicker">Atenea Escuela de Naturopatía Holística</p>
      <h1 class="atenea-noticias-title">Noticias</h1>
      <p class="atenea-noticias-summary">
        Mantente al día con nuestras actividades, eventos y novedades académicas.
        Compartimos información relevante para la comunidad Atenea.
      </p>
    </div>
  </section>

  <div class="container-fluid pt-5">
    <div class="container">
      <div class="text-center pb-2">
        <p class="section-title px-5">
          <span class="px-2">Todas las noticias</span>
        </p>
        <h1 class="mb-4">Mantente informado</h1>
      </div>
      <div class="row pb-3">
        <?php if (mysqli_num_rows($resultado_noticias) > 0) : ?>
          <?php while ($noticia = mysqli_fetch_assoc($resultado_noticias)) : ?>
            <div class="col-lg-4 mb-4">
              <div class="card border-0 shadow-sm mb-2 h-100">
                <img class="card-img-top mb-2" src="../img/<?php echo $noticia['imagen']; ?>" alt="<?php echo $noticia['titulo']; ?>" style="height: 250px; object-fit: cover;">
                <div class="card-body bg-light text-center p-4 d-flex flex-column">
                  <h4 class="mb-3"><?php echo $noticia['titulo']; ?></h4>
                  <small class="text-muted mb-3">
                    <i class="fa fa-calendar-alt text-primary mr-2"></i>
                    <?php echo date('d/m/Y', strtotime($noticia['fecha_publicacion'])); ?>
                  </small>
                  <p class="flex-grow-1"><?php echo $noticia['descripcion_corta']; ?></p>
                  <a href="noticia_detalle.php?id=<?php echo $noticia['id']; ?>" class="btn btn-primary1 px-4 mx-auto my-2">Ver más</a>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else : ?>
          <div class="col-12"><p class="text-center">No hay noticias disponibles en este momento.</p></div>
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
