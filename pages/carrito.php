<!DOCTYPE html>
<html lang="es">
<?php
session_start();
include '../includes/connection.php';
require_once '../includes/atenea_auth.php';

atenea_handle_session_timeout();

if (!isset($_SESSION['cart_session'])) {
    $_SESSION['cart_session'] = uniqid('cart_', true);
}

$session_id = $_SESSION['cart_session'];

$sql_carrito = "SELECT c.*, p.nombre, p.descripcion_corta, p.precio, p.precio_descuento, p.imagen, p.stock
                FROM carrito c
                JOIN productos p ON c.producto_id = p.id
                WHERE c.session_id = '$session_id'";
$resultado_carrito = mysqli_query($db, $sql_carrito);

$total = 0;
$envio = 5.00;
$checkout_error = isset($_GET['checkout_error']) ? trim((string) $_GET['checkout_error']) : '';
$checkout_ok = isset($_GET['checkout_ok']) ? trim((string) $_GET['checkout_ok']) : '';
$checkout_cancelled = isset($_GET['checkout_cancelled']) ? trim((string) $_GET['checkout_cancelled']) : '';
$billing_name_prefill = trim((string) ($_SESSION['nombres_estudiante'] ?? '') . ' ' . (string) ($_SESSION['apellidos_estudiante'] ?? ''));

if ($billing_name_prefill === '') {
    $billing_name_prefill = trim((string) ($_SESSION['FIRST_NAME'] ?? '') . ' ' . (string) ($_SESSION['LAST_NAME'] ?? ''));
}

$billing_email_prefill = trim((string) ($_SESSION['correo_estudiante'] ?? ($_SESSION['EMAIL'] ?? '')));
$billing_address_prefill = trim((string) ($_SESSION['direccion_estudiante'] ?? ''));
?>

<?php include '../includes/head_home.php'; ?>

<body>
  <?php include '../includes/navbar_home.php'; ?>

  <section class="container-fluid atenea-carrito-hero">
    <div class="atenea-carrito-hero-inner">
      <p class="atenea-carrito-kicker">Atenea Escuela de Naturopatía Holística</p>
      <h1 class="atenea-carrito-title">Mi carrito</h1>
      <p class="atenea-carrito-summary">
        Revisa tus productos, ajusta cantidades y completa tu compra de forma rápida y segura.
      </p>
    </div>
  </section>

  <div class="container-fluid pt-5">
    <div class="container">
      <?php if ($checkout_error !== '') : ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($checkout_error); ?></div>
      <?php endif; ?>
      <?php if ($checkout_ok !== '') : ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($checkout_ok); ?></div>
      <?php endif; ?>
      <?php if ($checkout_cancelled !== '') : ?>
        <div class="alert alert-warning">El pago fue cancelado. Puedes intentarlo nuevamente.</div>
      <?php endif; ?>

      <?php if (mysqli_num_rows($resultado_carrito) > 0) : ?>
        <div class="row">
          <div class="col-lg-8">
            <div class="table-responsive mb-5">
              <table class="table table-bordered text-center mb-0">
                <thead class="bg-light">
                  <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Eliminar</th>
                  </tr>
                </thead>
                <tbody class="align-middle">
                  <?php while ($item = mysqli_fetch_assoc($resultado_carrito)) : ?>
                    <?php
                    $precio = $item['precio_descuento'] ? $item['precio_descuento'] : $item['precio'];
                    $subtotal = $precio * $item['cantidad'];
                    $total += $subtotal;
                    ?>
                    <tr>
                      <td class="align-middle">
                        <img src="../img/<?php echo $item['imagen']; ?>" alt="" style="width: 50px;" class="mr-2">
                        <?php echo $item['nombre']; ?>
                      </td>
                      <td class="align-middle">$<?php echo number_format($precio, 2); ?></td>
                      <td class="align-middle">
                        <div class="input-group quantity mx-auto" style="width: 100px;">
                          <div class="input-group-btn">
                            <button class="btn btn-sm btn-primary btn-minus" onclick="actualizarCantidad(<?php echo $item['id']; ?>, -1)">
                              <i class="fa fa-minus"></i>
                            </button>
                          </div>
                          <input type="text" class="form-control form-control-sm bg-white border-0 text-center" value="<?php echo $item['cantidad']; ?>" readonly>
                          <div class="input-group-btn">
                            <button class="btn btn-sm btn-primary btn-plus" <?php if ($item['cantidad'] >= $item['stock']) echo 'disabled'; ?> onclick="actualizarCantidad(<?php echo $item['id']; ?>, 1)">
                              <i class="fa fa-plus"></i>
                            </button>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle">$<?php echo number_format($subtotal, 2); ?></td>
                      <td class="align-middle">
                        <button class="btn btn-sm btn-danger" onclick="eliminarDelCarrito(<?php echo $item['id']; ?>)">
                          <i class="fa fa-times"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <h5 class="mb-3">Resumen del pedido</h5>
                <div class="d-flex justify-content-between mb-3">
                  <h6 class="font-weight-medium">Subtotal</h6>
                  <h6 class="font-weight-medium">$<?php echo number_format($total, 2); ?></h6>
                </div>
                <div class="d-flex justify-content-between mb-3">
                  <h6 class="font-weight-medium">Envío</h6>
                  <h6 class="font-weight-medium">$<?php echo number_format($envio, 2); ?></h6>
                </div>
                <hr class="mt-0">
                <div class="d-flex justify-content-between mb-3">
                  <h5 class="font-weight-bold">Total</h5>
                  <h5 class="font-weight-bold">$<?php echo number_format($total + $envio, 2); ?></h5>
                </div>
                <form method="POST" action="checkout_create.php" class="mb-2" data-atenea-loading-form data-loader-text="Preparando pago seguro...">
                  <div class="form-group">
                    <label for="billing_name">Nombre completo</label>
                    <input id="billing_name" name="billing_name" type="text" class="form-control" required maxlength="120" value="<?php echo htmlspecialchars($billing_name_prefill); ?>">
                  </div>
                  <div class="form-group">
                    <label for="billing_email">Correo</label>
                    <input id="billing_email" name="billing_email" type="email" class="form-control" required maxlength="150" value="<?php echo htmlspecialchars($billing_email_prefill); ?>">
                  </div>
                  <div class="form-group">
                    <label for="billing_address">Dirección de facturación</label>
                    <textarea id="billing_address" name="billing_address" class="form-control" rows="2" required maxlength="255"><?php echo htmlspecialchars($billing_address_prefill); ?></textarea>
                  </div>
                  <button type="submit" class="btn btn-primary btn-block">Proceder al pago</button>
                </form>
                <a href="productos.php" class="btn btn-outline-primary btn-block">Seguir comprando</a>
              </div>
            </div>
          </div>
        </div>
      <?php else : ?>
        <div class="text-center py-5">
          <i class="fa fa-shopping-cart" style="font-size: 100px; color: #ccc;"></i>
          <h3 class="mt-4">Tu carrito está vacío</h3>
          <p class="text-muted">Agrega productos para comenzar tu compra.</p>
          <a href="productos.php" class="btn btn-primary mt-3">Ver productos</a>
        </div>
      <?php endif; ?>
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
    function actualizarCantidad(itemId, cambio) {
      fetch('carrito_update.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'item_id=' + itemId + '&cambio=' + cambio
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message
            });
          }
        });
    }

    function eliminarDelCarrito(itemId) {
      Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Deseas eliminar este producto del carrito?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch('carrito_delete.php?id=' + itemId)
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                location.reload();
              } else {
                Swal.fire('Error', data.message, 'error');
              }
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
