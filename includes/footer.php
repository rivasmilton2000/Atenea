<footer id="footer" class="footer position-relative light-background">
  <div class="container footer-top">
    <div class="row gy-4">
      <div class="col-lg-4 col-md-6 footer-about">
        <a href="<?= atenea_url('index.php') ?>" class="logo footer-logo d-flex align-items-center">
          <img src="<?= atenea_url('img/atenea-logo.png') ?>" alt="Atenea Escuela de Naturopatía Holística">
        </a>
        <div class="footer-contact pt-3">
          <p>El Salvador</p>
          <p class="mt-3"><strong>Atención:</strong> <span>Información sobre capacitaciones y certificaciones</span></p>
          <p><strong>Correo:</strong> <span>info@atenea.edu.sv</span></p>
        </div>
        <div class="social-links d-flex mt-4" aria-label="Redes sociales">
          <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
        </div>
      </div>

      <div class="col-lg-2 col-md-3 footer-links">
        <h4>Enlaces útiles</h4>
        <ul>
          <li><a href="<?= atenea_url('index.php') ?>">Inicio</a></li>
          <li><a href="<?= atenea_url('src/website/about.php') ?>">Nosotros</a></li>
          <li><a href="<?= atenea_url('src/website/courses.php') ?>">Capacitaciones</a></li>
          <li><a href="<?= atenea_url('src/website/contact.php') ?>">Contacto</a></li>
        </ul>
      </div>

      <div class="col-lg-2 col-md-3 footer-links">
        <h4>Formación</h4>
        <ul>
          <li><a href="<?= atenea_url('src/website/courses.php') ?>">Naturopatía</a></li>
          <li><a href="<?= atenea_url('src/website/courses.php') ?>">Terapias holísticas</a></li>
          <li><a href="<?= atenea_url('src/website/trainers.php') ?>">Equipo docente</a></li>
          <li><a href="<?= atenea_url('src/website/events.php') ?>">Próximos eventos</a></li>
        </ul>
      </div>

      <div class="col-lg-4 col-md-12 footer-newsletter">
        <h4>Boletín de Atenea</h4>
        <p>Recibe novedades sobre programas, eventos y bienestar holístico.</p>
        <form action="<?= atenea_url('src/website/forms/newsletter.php') ?>" method="post" class="php-email-form">
          <div class="newsletter-form"><input type="email" name="email" aria-label="Correo electrónico" required><input type="submit" value="Suscribirme"></div>
          <div class="loading">Enviando</div>
          <div class="error-message"></div>
          <div class="sent-message">Tu solicitud fue enviada. ¡Gracias!</div>
        </form>
      </div>
    </div>
  </div>

  <div class="container copyright text-center mt-4">
    <p>© <span><?= date('Y') ?></span> <strong class="px-1 sitename">Atenea</strong> <span>Todos los derechos reservados</span></p>
    <div class="credits">Plantilla base por <a href="https://bootstrapmade.com/">BootstrapMade</a>, distribuida por <a href="https://themewagon.com/">ThemeWagon</a> y adaptada para Atenea.</div>
  </div>
</footer>

<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center" aria-label="Volver arriba"><i class="bi bi-arrow-up-short"></i></a>
<div id="preloader"></div>

<script src="<?= atenea_url('src/website/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= atenea_url('src/website/assets/vendor/php-email-form/validate.js') ?>"></script>
<script src="<?= atenea_url('src/website/assets/vendor/aos/aos.js') ?>"></script>
<script src="<?= atenea_url('src/website/assets/vendor/glightbox/js/glightbox.min.js') ?>"></script>
<script src="<?= atenea_url('src/website/assets/vendor/purecounter/purecounter_vanilla.js') ?>"></script>
<script src="<?= atenea_url('src/website/assets/vendor/swiper/swiper-bundle.min.js') ?>"></script>
<script src="<?= atenea_url('src/website/assets/js/main.js') ?>"></script>
</body>
</html>
