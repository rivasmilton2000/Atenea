<!DOCTYPE html>
<html lang="es">
  <?php
  require 'session.php';
  require_once '../includes/atenea_auth.php';
  require_once '../includes/atenea_catalog.php';
  include '../includes/connection.php';

  $ateneaCanPurchase = logged_in();
  $loginToBuyUrl = atenea_build_login_url('productos.php', 'login_required');
  $loginToCartUrl = atenea_build_login_url('carrito.php', 'cart_required');
  $catalogSchema = atenea_catalog_product_schema_flags($db);

  if ($ateneaCanPurchase && !isset($_SESSION['cart_session'])) {
      $_SESSION['cart_session'] = uniqid('cart_', true);
  }

  $categoria_filtro = isset($_GET['categoria']) ? (int) $_GET['categoria'] : 0;
  $tipo_filtro = trim((string) ($_GET['tipo'] ?? ''));

  if ($tipo_filtro !== '' && $catalogSchema['tipo_oferta']) {
      $tipo_filtro = atenea_catalog_normalize_type($tipo_filtro);
  } else {
      $tipo_filtro = '';
  }

  if (!function_exists('productos_catalogo_url')) {
      function productos_catalogo_url(array $changes = []): string
      {
          $params = $_GET;

          foreach ($changes as $key => $value) {
              if ($value === null || $value === '') {
                  unset($params[$key]);
              } else {
                  $params[$key] = $value;
              }
          }

          $query = http_build_query($params);

          return 'productos.php' . ($query !== '' ? '?' . $query : '');
      }
  }

  $catalogSelect = atenea_catalog_product_select_sql($db, 'p');
  $typeFilterSql = $tipo_filtro !== '' ? atenea_catalog_type_filter_sql($db, $tipo_filtro, 'p') : '';

  if ($categoria_filtro > 0) {
      $stmt = $db->prepare("
          SELECT p.*, c.nombre AS categoria_nombre,
                 {$catalogSelect}
          FROM productos p
          JOIN categorias_productos c ON p.categoria_id = c.id
          WHERE p.estado = 1 AND p.categoria_id = ?{$typeFilterSql}
          ORDER BY p.destacado DESC, p.nombre ASC
      ");

      $stmt->bind_param('i', $categoria_filtro);
      $stmt->execute();
      $resultado_productos = $stmt->get_result();
  } else {
      $stmt = $db->prepare("
          SELECT p.*, c.nombre AS categoria_nombre,
                 {$catalogSelect}
          FROM productos p
          JOIN categorias_productos c ON p.categoria_id = c.id
          WHERE p.estado = 1{$typeFilterSql}
          ORDER BY p.destacado DESC, p.nombre ASC
      ");

      $stmt->execute();
      $resultado_productos = $stmt->get_result();
  }

  $sql_categorias = "SELECT * FROM categorias_productos WHERE estado = 1 ORDER BY nombre";
  $resultado_categorias = mysqli_query($db, $sql_categorias);

  $cart_count = 0;
  if ($ateneaCanPurchase && !empty($_SESSION['cart_session'])) {
      $session_id = (string) $_SESSION['cart_session'];
      $stmt_cart = $db->prepare("
          SELECT COALESCE(SUM(cantidad), 0) AS total
          FROM carrito
          WHERE session_id = ?
      ");

      if ($stmt_cart) {
          $stmt_cart->bind_param('s', $session_id);
          $stmt_cart->execute();
          $result_cart_count = $stmt_cart->get_result();
          $cart_count = (int) (($result_cart_count->fetch_assoc()['total'] ?? 0));
          $stmt_cart->close();
      }
  }
  ?>

  <?php include '../includes/head_home.php'; ?>

  <body>
    <?php include '../includes/navbar_home.php'; ?>

    <section class="container-fluid atenea-productos-hero">
      <div class="atenea-productos-hero-inner">
        <p class="atenea-productos-kicker">Atenea Escuela de Naturopatía Holística</p>
        <h1 class="atenea-productos-title">Catálogo Atenea</h1>
        <p class="atenea-productos-summary">
          Explora productos, cursos y certificaciones pensados para complementar tu bienestar integral y tu capacitación en naturopatía.
        </p>
      </div>
    </section>

    <div class="container-fluid pt-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-3 col-md-4">
            <div class="mb-5">
              <h4 class="mb-3">Tipo de oferta</h4>
              <div class="atenea-offer-filter-nav mb-4">
                <a href="<?php echo htmlspecialchars(productos_catalogo_url(['tipo' => null]), ENT_QUOTES, 'UTF-8'); ?>" class="atenea-offer-filter-link<?php echo $tipo_filtro === '' ? ' is-active' : ''; ?>">
                  Todo el catálogo
                </a>
                <?php if ($catalogSchema['tipo_oferta']) : ?>
                  <?php foreach (atenea_catalog_type_options() as $typeValue => $typeLabel) : ?>
                    <a href="<?php echo htmlspecialchars(productos_catalogo_url(['tipo' => $typeValue]), ENT_QUOTES, 'UTF-8'); ?>" class="atenea-offer-filter-link<?php echo $tipo_filtro === $typeValue ? ' is-active' : ''; ?>">
                      <?php echo htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>

              <h4 class="mb-3">Categorías</h4>
              <ul class="list-group">
                <li class="list-group-item">
                  <a href="<?php echo htmlspecialchars(productos_catalogo_url(['categoria' => null]), ENT_QUOTES, 'UTF-8'); ?>" class="text-decoration-none <?php echo !$categoria_filtro ? 'text-primary font-weight-bold' : 'text-dark'; ?>">
                    Todas las categorías
                  </a>
                </li>
                <?php while ($cat = mysqli_fetch_assoc($resultado_categorias)) : ?>
                  <li class="list-group-item">
                    <a href="<?php echo htmlspecialchars(productos_catalogo_url(['categoria' => (int) $cat['id']]), ENT_QUOTES, 'UTF-8'); ?>" class="text-decoration-none <?php echo $categoria_filtro == $cat['id'] ? 'text-primary font-weight-bold' : 'text-dark'; ?>">
                      <?php echo htmlspecialchars((string) $cat['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                  </li>
                <?php endwhile; ?>
              </ul>
            </div>

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
                <a href="<?php echo htmlspecialchars($loginToCartUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary btn-block">Iniciar sesión</a>
              <?php endif; ?>
            </div>
          </div>

          <div class="col-lg-9 col-md-8">
            <div class="row pb-3">
              <?php
              if (mysqli_num_rows($resultado_productos) > 0) {
                  while ($producto = mysqli_fetch_assoc($resultado_productos)) :
                      $offerType = atenea_catalog_normalize_type($producto['tipo_oferta'] ?? 'producto');
                      $offerLabel = atenea_catalog_type_label($offerType);
                      $duration = trim((string) ($producto['duracion'] ?? ''));
                      $stockLabel = atenea_catalog_stock_label($offerType);
                      $hasVideo = atenea_catalog_has_active_video($producto);
                      $precio_mostrar = $producto['precio_descuento'] ? $producto['precio_descuento'] : $producto['precio'];
                      $tiene_descuento = $producto['precio_descuento'] ? true : false;
              ?>
                <div class="col-lg-4 col-md-6 pb-4">
                  <div class="card border-0 shadow-sm h-100">
                    <div class="position-absolute" style="top: 10px; right: 10px; z-index: 3;">
                      <span class="atenea-offer-badge atenea-offer-badge--<?php echo htmlspecialchars($offerType, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($offerLabel, ENT_QUOTES, 'UTF-8'); ?>
                      </span>
                    </div>

                    <?php if (!empty($producto['destacado'])) : ?>
                      <div class="badge badge-warning position-absolute" style="top: 10px; left: 10px;">Destacado</div>
                    <?php endif; ?>
                    <?php if ($tiene_descuento) : ?>
                      <div class="badge badge-danger position-absolute" style="top: 46px; right: 10px;">Oferta</div>
                    <?php endif; ?>

                    <img class="card-img-top" src="../img/<?php echo htmlspecialchars((string) $producto['imagen'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) $producto['nombre'], ENT_QUOTES, 'UTF-8'); ?>" style="height: 200px; object-fit: cover;">

                    <div class="card-body d-flex flex-column">
                      <span class="text-muted small"><?php echo htmlspecialchars((string) $producto['categoria_nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
                      <h5 class="card-title"><?php echo htmlspecialchars((string) $producto['nombre'], ENT_QUOTES, 'UTF-8'); ?></h5>
                      <p class="card-text text-muted small flex-grow-1"><?php echo htmlspecialchars((string) $producto['descripcion_corta'], ENT_QUOTES, 'UTF-8'); ?></p>
                      <div class="atenea-offer-meta mb-3">
                        <span><?php echo htmlspecialchars($stockLabel, ENT_QUOTES, 'UTF-8'); ?>: <?php echo (int) $producto['stock']; ?></span>
                        <?php if ($duration !== '') : ?>
                          <span>Duración: <?php echo htmlspecialchars($duration, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                        <?php if ($hasVideo) : ?>
                          <span>Video disponible</span>
                        <?php endif; ?>
                      </div>
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <?php if ($tiene_descuento) : ?>
                            <span class="h5 text-primary mb-0">$<?php echo number_format((float) $precio_mostrar, 2); ?></span>
                            <span class="text-muted small"><del>$<?php echo number_format((float) $producto['precio'], 2); ?></del></span>
                          <?php else : ?>
                            <span class="h5 text-primary mb-0"><?php echo (float) $precio_mostrar > 0 ? '$' . number_format((float) $precio_mostrar, 2) : 'Consultar'; ?></span>
                          <?php endif; ?>
                        </div>
                        <?php if ((int) $producto['stock'] > 0) : ?>
                          <?php if ($ateneaCanPurchase): ?>
                            <button class="btn btn-primary btn-sm" onclick="agregarAlCarrito(<?php echo (int) $producto['id']; ?>)">
                              <i class="fa fa-shopping-cart"></i> <?php echo $offerType === 'producto' ? 'Agregar' : 'Inscribirme'; ?>
                            </button>
                          <?php else: ?>
                            <a href="<?php echo htmlspecialchars($loginToBuyUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary btn-sm">
                              <i class="fa fa-user"></i> Iniciar sesión para comprar
                            </a>
                          <?php endif; ?>
                        <?php else : ?>
                          <button class="btn btn-secondary btn-sm" disabled><?php echo htmlspecialchars(atenea_catalog_out_of_stock_label($offerType), ENT_QUOTES, 'UTF-8'); ?></button>
                        <?php endif; ?>
                      </div>

                      <a href="producto_detalle.php?id=<?php echo (int) $producto['id']; ?>" class="btn btn-outline-primary btn-sm mt-2">Ver detalles</a>
                    </div>
                  </div>
                </div>
              <?php
                  endwhile;
              } else {
                  echo '<div class="col-12"><p class="text-center">No hay elementos disponibles con los filtros seleccionados.</p></div>';
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include '../includes/footer_home.php'; ?>

    <a href="#" class="btn btn-primary p-3 back-to-top">
      <i class="fa fa-angle-double-up"></i>
    </a>

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
