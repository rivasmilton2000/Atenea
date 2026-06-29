<!DOCTYPE html>
<html lang="es">
<?php include '../includes/connection.php'; ?>
<?php include '../includes/head_home.php'; ?>

<body>
  <?php include '../includes/navbar_home.php'; ?>

  <section class="container-fluid atenea-contacto-hero">
    <div class="atenea-contacto-hero-inner">
      <p class="atenea-contacto-kicker">Atenea Escuela de Naturopatía Holística</p>
      <h1 class="atenea-contacto-title">Contáctanos</h1>
      <p class="atenea-contacto-summary">
        Estamos para ayudarte. Escríbenos para resolver dudas sobre programas, procesos de inscripción
        y servicios de Atenea Escuela.
      </p>
    </div>
  </section>

  <div class="container-fluid pt-5">
    <div class="container">
      <div class="text-center pb-2">
        <p class="section-title px-5">
          <span class="px-2">Contáctanos</span>
        </p>
        <h1 class="mb-4">¡Contáctanos!</h1>
      </div>
      <div class="row">
        <div class="col-lg-7 mb-5">
          <div class="contact-form">
            <div id="success"></div>
            <form name="sentMessage" id="contactForm" action="contacto_home.php" method="post" novalidate="novalidate">
              <div class="control-group">
                <input
                  type="text"
                  class="form-control"
                  id="name"
                  name="name"
                  placeholder="Nombre..."
                  required="required"
                  data-validation-required-message="Por favor escriba su nombre."
                >
                <p class="help-block text-danger"></p>
              </div>
              <div class="control-group">
                <input
                  minlength="5"
                  maxlength="55"
                  type="email"
                  class="form-control"
                  name="email"
                  id="email"
                  placeholder="Correo electrónico..."
                  required="required"
                  data-validation-required-message="Por favor escriba su correo electrónico."
                >
                <p class="help-block text-danger"></p>
              </div>
              <div class="control-group">
                <input
                  type="text"
                  class="form-control"
                  name="subject"
                  id="subject"
                  placeholder="Asunto..."
                  required="required"
                  data-validation-required-message="Por favor escriba el asunto."
                >
                <p class="help-block text-danger"></p>
              </div>
              <div class="control-group">
                <textarea
                  class="form-control"
                  rows="6"
                  name="message"
                  id="message"
                  placeholder="Mensaje..."
                  required="required"
                  data-validation-required-message="Por favor escriba su mensaje."
                ></textarea>
                <p class="help-block text-danger"></p>
              </div>
              <div>
                <button class="btn btn-primary py-2 px-4" type="submit" name="submit" id="submit">
                  Enviar mensaje
                </button>
              </div>
            </form>
          </div>
        </div>
        <div class="col-lg-5 mb-5 text-justify">
          <p>
            Puedes contactar a Atenea Escuela de Naturopatía Holística por correo electrónico para obtener más
            información sobre nuestros programas, procesos de inscripción y servicios. Estamos disponibles para
            responder a cualquier consulta y ofrecerte la orientación que necesites.
          </p>
          <div class="d-flex">
            <i
              class="fa fa-map-marker-alt d-inline-flex align-items-center justify-content-center bg-primary text-secondary rounded-circle"
              style="width: 45px; height: 45px"
            ></i>
            <div class="pl-3">
              <h5>Dirección</h5>
              <p>Av. El Níspero Final, Huizúcar</p>
            </div>
          </div>
          <div class="d-flex">
            <i
              class="fa fa-envelope d-inline-flex align-items-center justify-content-center bg-primary text-secondary rounded-circle"
              style="width: 45px; height: 45px"
            ></i>
            <div class="pl-3">
              <h5>Correo</h5>
              <p>ateneanaturopatia@gmail.com</p>
            </div>
          </div>
          <div class="d-flex">
            <i
              class="fa fa-phone-alt d-inline-flex align-items-center justify-content-center bg-primary text-secondary rounded-circle"
              style="width: 45px; height: 45px"
            ></i>
            <div class="pl-3">
              <h5>Número Telefónico</h5>
              <p>(+503) 2291-2313</p>
            </div>
          </div>
          <div class="d-flex">
            <i
              class="far fa-clock d-inline-flex align-items-center justify-content-center bg-primary text-secondary rounded-circle"
              style="width: 45px; height: 45px"
            ></i>
            <div class="pl-3">
              <h5>Horario</h5>
              <strong>Lunes - Viernes:</strong>
              <p class="m-0">07:00 AM - 12:00 PM</p>
            </div>
          </div>
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
  <script src="../js/main.js"></script>
</body>
</html>
