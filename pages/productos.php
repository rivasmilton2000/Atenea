<!DOCTYPE html>
<html lang="en">
  <?php 
  require 'session.php';
  require_once '../includes/atenea_auth.php';
  include '../includes/connection.php'; 
  $ateneaCanPurchase = logged_in();
  $loginToBuyUrl = atenea_build_login_url('productos.php', 'login_required');
  $loginToCartUrl = atenea_build_login_url('carrito.php', 'cart_required');
  
  // Generar session_id si no existe
  if ($ateneaCanPurchase && !isset($_SESSION['cart_session'])) {
      $_SESSION['cart_session'] = uniqid('cart_', true);
  }
  
  // Filtro por categoría (solo validamos que sea número)
  $categoria_filtro = isset($_GET['categoria']) ? (int) $_GET['categoria'] : 0;
  
  // Consulta de productos
  if ($categoria_filtro > 0) {

    $stmt = $db->prepare("
        SELECT p.*, c.nombre AS categoria_nombre
        FROM productos p
        JOIN categorias_productos c ON p.categoria_id = c.id
        WHERE p.estado = 1 AND p.categoria_id = ?
        ORDER BY p.destacado DESC, p.nombre ASC
    ");

    $stmt->bind_param("i", $categoria_filtro);
    $stmt->execute();
    $resultado_productos = $stmt->get_result();

} else {

    $stmt = $db->prepare("
        SELECT p.*, c.nombre AS categoria_nombre
        FROM productos p
        JOIN categorias_productos c ON p.categoria_id = c.id
        WHERE p.estado = 1
        ORDER BY p.destacado DESC, p.nombre ASC
    ");

    $stmt->execute();
    $resultado_productos = $stmt->get_result();
}
  
  // Consulta de categorías
  $sql_categorias = "SELECT * FROM categorias_productos WHERE estado = 1 ORDER BY nombre";
  $resultado_categorias = mysqli_query($db, $sql_categorias);
  
  // Obtener cantidad de items en el carrito
  $cart_count = 0;
  if ($ateneaCanPurchase && !empty($_SESSION['cart_session'])) {
    $session_id = (string) $_SESSION['cart_session'];

    $stmt_cart = $db->prepare("
        SELECT COALESCE(SUM(cantidad), 0) AS total
        FROM carrito
        WHERE session_id = ?
    ");

    if ($stmt_cart) {
      $stmt_cart->bind_param("s", $session_id);
      $stmt_cart->execute();
      $result_cart_count = $stmt_cart->get_result();
      $cart_count = (int) (($result_cart_count->fetch_assoc()['total'] ?? 0));
      $stmt_cart->close();
    }
  }
  ?>
  
  <!-- Head start -->
  <?php include '../includes/head_home.php'; ?>
  <!-- Head end -->

  <body>
    <!-- Navbar Start -->
    <?php include '../includes/navbar_home.php' ?>
    <!-- Navbar End -->
    <!-- Header Start -->
    <section class="container-fluid atenea-productos-hero">
      <div class="atenea-productos-hero-inner">
        <p class="atenea-productos-kicker">Atenea Escuela de Naturopatía Holística</p>
        <h1 class="atenea-productos-title">Tienda</h1>
        <p class="atenea-productos-summary">
          Descubre nuestros productos naturales y herramientas especializadas para complementar tu bienestar integral.
        </p>
      </div>
    </section>
    <!-- Header End -->


    <!-- Shop Start -->
    <div class="container-fluid pt-5">
      <div class="container">
        <div class="row">
          <!-- Sidebar -->
          <div class="col-lg-3 col-md-4">
            <div class="mb-5">
              <h4 class="mb-3">Categorías</h4>
              <ul class="list-group">
                <li class="list-group-item">
                  <a href="productos.php" class="text-decoration-none <?php echo !$categoria_filtro ? 'text-primary font-weight-bold' : 'text-dark'; ?>">
                    Todos los productos
                  </a>
                </li>
                <?php while ($cat = mysqli_fetch_assoc($resultado_categorias)) : ?>
                  <li class="list-group-item">
                    <a href="productos.php?categoria=<?php echo $cat['id']; ?>" 
                       class="text-decoration-none <?php echo $categoria_filtro == $cat['id'] ? 'text-primary font-weight-bold' : 'text-dark'; ?>">
                      <?php echo $cat['nombre']; ?>
                    </a>
                  </li>
                <?php endwhile; ?>
              </ul>
            </div>
            
            <!-- Carrito Widget -->
            <div class="bg-light p-4 mb-5">
              <h4 class="mb-3">Mi Carrito</h4>
              <?php if ($ateneaCanPurchase): ?>
                <div class="d-flex justify-content-between mb-3">
                  <span>Items:</span>
                  <span class="badge badge-primary badge-pill"><?php echo $cart_count; ?></span>
                </div>
                <a href="carrito.php" class="btn btn-primary btn-block">Ver carrito</a>
              <?php else: ?>
                <p class="text-muted small mb-3">Inicia sesión para guardar productos y continuar con tu compra.</p>
                <a href="<?php echo htmlspecialchars($loginToCartUrl); ?>" class="btn btn-outline-primary btn-block">Iniciar sesión</a>
              <?php endif; ?>
            </div>
          </div>

          <!-- Products -->
          <div class="col-lg-9 col-md-8">
            <div class="row pb-3">
              <?php 
              if (mysqli_num_rows($resultado_productos) > 0) {
                while ($producto = mysqli_fetch_assoc($resultado_productos)) : 
                  $precio_mostrar = $producto['precio_descuento'] ? $producto['precio_descuento'] : $producto['precio'];
                  $tiene_descuento = $producto['precio_descuento'] ? true : false;
              ?>
                <div class="col-lg-4 col-md-6 pb-4">
                  <div class="card border-0 shadow-sm h-100">
                    <?php if ($producto['destacado']) : ?>
                      <div class="badge badge-warning position-absolute" style="top: 10px; left: 10px;">Destacado</div>
                    <?php endif; ?>
                    <?php if ($tiene_descuento) : ?>
                      <div class="badge badge-danger position-absolute" style="top: 10px; right: 10px;">Oferta</div>
                    <?php endif; ?>
                    
                    <img class="card-img-top" src="../img/<?php echo $producto['imagen']; ?>" alt="<?php echo $producto['nombre']; ?>" style="height: 200px; object-fit: cover;">
                    
                    <div class="card-body d-flex flex-column">
                      <span class="text-muted small"><?php echo $producto['categoria_nombre']; ?></span>
                      <h5 class="card-title"><?php echo $producto['nombre']; ?></h5>
                      <p class="card-text text-muted small flex-grow-1"><?php echo $producto['descripcion_corta']; ?></p>
                       <p class="card-text text-muted small flex-grow-1">Cantidad disponible: <?php echo $producto['stock']; ?></p>
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <?php if ($tiene_descuento) : ?>
                            <span class="h5 text-primary mb-0">$<?php echo number_format($precio_mostrar, 2); ?></span>
                            <span class="text-muted small"><del>$<?php echo number_format($producto['precio'], 2); ?></del></span>
                          <?php else : ?>
                            <span class="h5 text-primary mb-0">$<?php echo number_format($precio_mostrar, 2); ?></span>
                          <?php endif; ?>
                        </div>
                        <?php if ($producto['stock'] > 0) : ?>
                          <?php if ($ateneaCanPurchase): ?>
                            <button class="btn btn-primary btn-sm" onclick="agregarAlCarrito(<?php echo $producto['id']; ?>)">
                              <i class="fa fa-shopping-cart"></i> Agregar
                            </button>
                          <?php else: ?>
                            <a href="<?php echo htmlspecialchars($loginToBuyUrl); ?>" class="btn btn-outline-primary btn-sm">
                              <i class="fa fa-user"></i> Iniciar sesión para comprar
                            </a>
                          <?php endif; ?>
                        <?php else : ?>
                          <button class="btn btn-secondary btn-sm" disabled>Sin stock</button>
                        <?php endif; ?>
                      </div>
                      
                      <a href="producto_detalle.php?id=<?php echo $producto['id']; ?>" class="btn btn-outline-primary btn-sm mt-2">Ver detalles</a>
                    </div>
                  </div>
                </div>
              <?php 
                endwhile;
              } else {
                echo '<div class="col-12"><p class="text-center">No hay productos disponibles en esta categoría.</p></div>';
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Shop End -->

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
      function agregarAlCarrito(productoId) {
        fetch('carrito_add.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'producto_id=' + productoId
        })
        .then(response => response.json())
        .then(data => {
          if (data.login_required && data.redirect) {
            window.location.href = data.redirect;
            return;
          }

          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Agregado al carrito',
              text: data.message,
              timer: 1500,
              showConfirmButton: false
            }).then(() => {
              location.reload();
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message
            });
          }
        });
      }
    </script>

    <script src="../libs/easing/easing.min.js"></script>
    <script src="../libs/owlcarousel/owl.carousel.min.js"></script>
    <script src="../js/main.js"></script>
  </body>
</html>
