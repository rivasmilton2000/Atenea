<!DOCTYPE html>
<html lang="es">
<?php include '../includes/connection.php'; ?>
<?php
// Consulta para facilities (servicios)
$sql_facilities = "SELECT titulo, descripcion FROM facilities WHERE estado = 1 ORDER BY orden";
$resultado_facilities = mysqli_query($db, $sql_facilities);

if (!$resultado_facilities) {
    die("Error en la consulta facilities: " . mysqli_error($db));
}

// Consulta para galería
$sql_galeria = "SELECT titulo, imagen FROM galeria WHERE estado = 1 ORDER BY orden LIMIT 3";
$resultado_galeria = mysqli_query($db, $sql_galeria);

if (!$resultado_galeria) {
    die("Error en la consulta galería: " . mysqli_error($db));
}

// Consulta para noticias (últimas 3)
$sql_noticias = "SELECT id, titulo, descripcion_corta, imagen, fecha_publicacion FROM noticias WHERE estado = 1 ORDER BY fecha_publicacion DESC LIMIT 3";
$resultado_noticias = mysqli_query($db, $sql_noticias);

if (!$resultado_noticias) {
    die("Error en la consulta noticias: " . mysqli_error($db));
}

// Consulta para about
$sql_about = "SELECT * FROM about WHERE estado = 1 LIMIT 1";
$resultado_about = mysqli_query($db, $sql_about);

if (!$resultado_about) {
    die("Error en la consulta about: " . mysqli_error($db));
}

$about = mysqli_fetch_assoc($resultado_about);

// Consulta para programas educativos (primeros 3)
$sql_programas = "SELECT * FROM programas_educativos WHERE estado = 1 ORDER BY orden LIMIT 3";
$resultado_programas = mysqli_query($db, $sql_programas);

if (!$resultado_programas) {
    die("Error en la consulta programas: " . mysqli_error($db));
}
?>

<?php include '../includes/head_home.php'; ?>

<body>
  <?php include '../includes/navbar_home.php'; ?>

  <div class="container-fluid atenea-hero px-0 mb-5">
    <div class="container-fluid atenea-hero-inner">
      <div class="row align-items-center no-gutters">
        <div class="col-lg-6 atenea-hero-copy">
          <h4 class="atenea-kicker">Atenea Escuela de Naturopatía Holística</h4>
          <h1 class="atenea-hero-title">"La salud se aprende, el cuerpo sana"</h1>
          <p class="atenea-hero-text text-justify">
            Atenea Escuela de Naturopatía Holística es una institución enfocada en la educación,
            la divulgación del conocimiento en salud natural y la comercialización de productos alineados con un estilo de vida saludable.
            Su propuesta combina una escuela online de naturopatía, la venta de herramientas pedagógicas educativas especializadas
            y la comercialización de productos naturopáticos, creando un entorno armónico entre salud, enseñanza y bienestar.
          </p>
        </div>
        <div class="col-lg-6 atenea-hero-media">
          <img class="img-fluid atenea-hero-image" src="../img/Cajuela.jpeg" alt="Atenea Escuela" />
        </div>
      </div>
    </div>
  </div>

  <div class="container-fluid pt-5">
    <div class="container pb-3">
      <div class="text-center pb-2">
        <p class="section-title px-5">
          <span class="px-2">Nuestros servicios</span>
        </p>
        <h1 class="mb-4">Lo que ofrecemos en Atenea Escuela</h1>
      </div>

      <div class="row">
        <?php while ($fila = mysqli_fetch_assoc($resultado_facilities)) : ?>
          <div class="col-lg-4 col-md-6 pb-4">
            <div class="d-flex flex-column h-100 bg-light shadow-sm border-top border-primary rounded mb-4 p-4">
              <div class="text-center flex-grow-1">
                <i class="flaticon-baby-boy fa-3x text-primary mb-3"></i>
                <h4 class="mb-3"><?php echo $fila['titulo']; ?></h4>
                <p class="m-0"><?php echo $fila['descripcion']; ?></p>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>

  <div class="container-fluid py-5">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-5">
          <img class="img-fluid rounded mb-5 mb-lg-0" src="../img/<?php echo $about['imagen']; ?>" alt="">
        </div>
        <div class="col-lg-7">
          <p class="section-title pr-5">
            <span class="pr-2">Descubre más sobre nosotros</span>
          </p>
          <h1 class="mb-4">¡La mejor opción educativa!</h1>
          <p class="text-justify">
            <?php echo $about['descripcion_corta']; ?>
          </p>
          <div class="row pt-2 pb-4">
            <div class="col-6 col-md-4">
              <img class="img-fluid rounded" src="../img/<?php echo $about['imagen3']; ?>" alt="">
            </div>
            <div class="col-6 col-md-8">
              <ul class="list-inline m-0">
                <li class="py-2 border-top border-bottom">
                  <i class="fa fa-check text-primary mr-3"></i><?php echo $about['caracteristica1']; ?>
                </li>
                <li class="py-2 border-bottom">
                  <i class="fa fa-check text-primary mr-3"></i><?php echo $about['caracteristica2']; ?>
                </li>
                <li class="py-2 border-bottom">
                  <i class="fa fa-check text-primary mr-3"></i><?php echo $about['caracteristica3']; ?>
                </li>
              </ul>
            </div>
          </div>
          <a href="about.php" class="btn btn-primary1 mt-2 py-2 px-4">Más información</a>
        </div>
      </div>
    </div>
  </div>

  <div class="container-fluid pt-5">
    <div class="container">
      <div class="text-center pb-2">
        <p class="section-title px-5">
          <span class="px-2">Nuestros programas</span>
        </p>
        <h1 class="mb-4">Formación integral en Naturopatía</h1>
      </div>
      <div class="row">
        <?php while ($programa = mysqli_fetch_assoc($resultado_programas)) : ?>
          <div class="col-lg-4 mb-5">
            <div class="card border-0 bg-light shadow-sm pb-2 h-100">
              <img class="card-img-top mb-2" src="../img/<?php echo $programa['imagen']; ?>" alt="<?php echo $programa['titulo']; ?>" style="height: 200px; object-fit: cover;">
              <div class="card-body text-center d-flex flex-column">
                <h4 class="card-title"><?php echo $programa['titulo']; ?></h4>
                <p class="card-text text-justify flex-grow-1">
                  <?php echo $programa['descripcion_corta']; ?>
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
      </div>
      <div class="text-center mt-4">
        <a href="educacion.php" class="btn btn-primary2 py-2 px-4">Ver todos los programas</a>
      </div>
    </div>
  </div>

  <div class="container-fluid py-5">
    <div class="container p-0">
      <div class="text-center pb-2">
        <p class="section-title px-5">
          <span class="px-2">Galería</span>
        </p>
        <h1 class="mb-4">Conoce nuestras actividades</h1>
      </div>
      <div class="owl-carousel school-photos-carousel">
        <?php while ($foto = mysqli_fetch_assoc($resultado_galeria)) : ?>
          <div class="school-photo-item px-3">
            <div class="bg-light shadow-sm rounded mb-4 p-4">
              <img src="../img/<?php echo $foto['imagen']; ?>" alt="<?php echo $foto['titulo']; ?>" class="img-fluid rounded">
            </div>
            <h5 class="text-center"><?php echo $foto['titulo']; ?></h5>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
    <div class="text-center mt-4">
      <a href="galeria.php" class="btn btn-primary2 py-2 px-4">Ver toda la galería</a>
    </div>
  </div>

  <div class="container-fluid pt-5">
    <div class="container">
      <div class="text-center pb-2">
        <p class="section-title px-5">
          <span class="px-2">Últimas noticias</span>
        </p>
        <h1 class="mb-4">Sección de noticias</h1>
      </div>
      <div class="owl-carousel news-carousel">
        <?php while ($noticia = mysqli_fetch_assoc($resultado_noticias)) : ?>
          <div class="news-item px-3">
            <div class="card border-0 shadow-sm mb-2">
              <img class="card-img-top mb-2" src="../img/<?php echo $noticia['imagen']; ?>" alt="<?php echo $noticia['titulo']; ?>">
              <div class="card-body bg-light text-center p-4">
                <h4><?php echo $noticia['titulo']; ?></h4>
                <p><?php echo substr($noticia['descripcion_corta'], 0, 100) . '...'; ?></p>
                <a href="noticia_detalle.php?id=<?php echo $noticia['id']; ?>" class="btn btn-primary1 px-4 mx-auto my-2">Ver más</a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
      <div class="text-center mt-4">
        <a href="noticias.php" class="btn btn-primary2 py-2 px-4">Ver todas las noticias</a>
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
  <script src="../libs/isotope/isotope.pkgd.min.js"></script>
  <script src="../libs/lightbox/js/lightbox.min.js"></script>

  <script>
    $(".school-photos-carousel").owlCarousel({
      autoplay: true,
      smartSpeed: 1500,
      dots: true,
      loop: true,
      center: true,
      responsive: {
        0: {
          items: 1
        },
        576: {
          items: 1
        },
        768: {
          items: 2
        },
        992: {
          items: 3
        }
      }
    });

    $(".news-carousel").owlCarousel({
      autoplay: true,
      smartSpeed: 1500,
      dots: true,
      loop: true,
      nav: true,
      navText: [
        '<i class="fa fa-angle-left"></i>',
        '<i class="fa fa-angle-right"></i>'
      ],
      responsive: {
        0: {
          items: 1
        },
        576: {
          items: 1
        },
        768: {
          items: 2
        },
        992: {
          items: 3
        }
      }
    });
  </script>

  <script src="../mail/jqBootstrapValidation.min.js"></script>
  <script src="../mail/contact.js"></script>
  <script src="../js/main.js"></script>
</body>
</html>
