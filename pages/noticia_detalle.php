<!DOCTYPE html>
<html lang="en">
  <?php include '../includes/connection.php'; ?>
  
 <?php 
    // Obtener ID de la noticia
    $noticia_id = isset($_GET['id']) ? mysqli_real_escape_string($db, $_GET['id']) : 0;

    //Consulta de la noticia
    $sql_noticia = "SELECT * FROM noticias WHERE id = '$noticia_id' AND estado = 1";
    $resultado_noticia = mysqli_query($db, $sql_noticia);

    if(!$resultado_noticia || mysqli_num_rows($resultado_noticia) == 0)
        {
            header('Location: noticias.php');
            exit();
        }
    
    $noticia = mysqli_fetch_assoc($resultado_noticia);
 ?>
  
  <!-- Head start -->
  <?php include '../includes/head_home.php'; ?>
  <!-- Head end -->

  <body>
    <!-- Navbar Start -->
    <?php include '../includes/navbar_home.php' ?>
    <!-- Navbar End -->
    <!-- Header Start -->
    <section class="container-fluid atenea-noticia-detalle-hero">
      <div class="atenea-noticia-detalle-hero-inner">
        <p class="atenea-noticia-detalle-kicker">Atenea Escuela de Naturopatía Holística</p>
        <h1 class="atenea-noticia-detalle-title"><?php echo $noticia['titulo']; ?></h1>
        <p class="atenea-noticia-detalle-summary">
          Información y actualidad de ATENEA Escuela para mantenerte al día con nuestras actividades y novedades.
        </p>
      </div>
    </section>
    <!-- Header End -->


    <!-- Blog Detail Start -->
    <div class="container py-5">
      <div class="row">
        <div class="col-lg-8">
          <div class="mb-5">
            <img class="img-fluid w-100 mb-4" src="../img/<?php echo $noticia['imagen']; ?>" alt="<?php echo $noticia['titulo']; ?>">
            
            <div class="d-flex mb-3">
              <small class="mr-3">
                <i class="fa fa-calendar-alt text-primary mr-2"></i>
                <?php echo date('d/m/Y', strtotime($noticia['fecha_publicacion'])); ?>
              </small>
            </div>
            
            <h2 class="mb-4"><?php echo $noticia['titulo']; ?></h2>
            
            <div class="bg-light p-4 mb-4">
              <p class="text-muted mb-0">
                <i class="fa fa-quote-left text-primary mr-2"></i>
                <?php echo $noticia['descripcion_corta']; ?>
              </p>
            </div>
            
            <div class="text-justify" style="line-height: 1.8;">
              <?php echo nl2br($noticia['descripcion_completa']); ?>
            </div>
            
            <div class="mt-5">
              <a href="noticias.php" class="btn btn-primary1 px-4">
                <i class="fa fa-arrow-left mr-2"></i>Volver a noticias
              </a>
            </div>
          </div>
        </div>

        <!-- Sidebar Start -->
        <div class="col-lg-4 mt-5 mt-lg-0">
          <!-- Recent Post -->
          <div class="mb-5">
            <h3 class="mb-4">Noticias Recientes</h3>
            <?php
            // Obtener otras noticias recientes (excepto la actual)
            $sql_recientes = "SELECT id, titulo, imagen, fecha_publicacion FROM noticias WHERE estado = 1 AND id != '$noticia_id' ORDER BY fecha_publicacion DESC LIMIT 3";
            $resultado_recientes = mysqli_query($db, $sql_recientes);
            
            while ($reciente = mysqli_fetch_assoc($resultado_recientes)) :
            ?>
              <div class="d-flex align-items-center border-bottom mb-3 pb-3">
                <img class="img-fluid rounded" src="../img/<?php echo $reciente['imagen']; ?>" style="width: 80px; height: 80px; object-fit: cover;" alt="<?php echo $reciente['titulo']; ?>">
                <div class="pl-3">
                  <a class="text-dark mb-2" href="noticia_detalle.php?id=<?php echo $reciente['id']; ?>">
                    <h6><?php echo substr($reciente['titulo'], 0, 50) . (strlen($reciente['titulo']) > 50 ? '...' : ''); ?></h6>
                  </a>
                  <small class="text-muted">
                    <i class="fa fa-calendar-alt text-primary mr-1"></i>
                    <?php echo date('d/m/Y', strtotime($reciente['fecha_publicacion'])); ?>
                  </small>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        </div>
        <!-- Sidebar End -->
      </div>
    </div>
    <!-- Blog Detail End -->

    <!-- Footer Start -->
    <?php include '../includes/footer_home.php'; ?>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary p-3 back-to-top">
      <i class="fa fa-angle-double-up"></i>
    </a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="../libs/easing/easing.min.js"></script>
    <script src="../libs/owlcarousel/owl.carousel.min.js"></script>
    <script src="../libs/isotope/isotope.pkgd.min.js"></script>
    <script src="../libs/lightbox/js/lightbox.min.js"></script>

    <!-- Contact Javascript File -->
    <script src="../mail/jqBootstrapValidation.min.js"></script>
    <script src="../mail/contact.js"></script>

    <!-- Template Javascript -->
    <script src="../js/main.js"></script>
  </body>
</html>
