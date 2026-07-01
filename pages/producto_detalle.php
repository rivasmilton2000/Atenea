<!DOCTYPE html>
<html lang="en">
<?php
require 'session.php';
require_once '../includes/atenea_auth.php';
include '../includes/connection.php';

// Validar ID del producto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: productos.php');
    exit;
}

$id_producto = intval($_GET['id']);
$ateneaCanPurchase = logged_in();
$loginToBuyUrl = atenea_build_login_url('productos.php', 'login_required');

// Generar session_id del carrito si no existe
if ($ateneaCanPurchase && !isset($_SESSION['cart_session'])) {
    $_SESSION['cart_session'] = uniqid('cart_', true);
}

// Consulta del producto
$stmtProducto = $db->prepare("
    SELECT p.*, c.nombre AS categoria_nombre 
    FROM productos p
    JOIN categorias_productos c ON p.categoria_id = c.id
    WHERE p.id = ? AND p.estado = 1
    LIMIT 1
");

if (!$stmtProducto) {
    header('Location: productos.php');
    exit;
}

$stmtProducto->bind_param('i', $id_producto);
$stmtProducto->execute();
$resultadoProducto = $stmtProducto->get_result();

if (!$resultadoProducto || $resultadoProducto->num_rows === 0) {
    $stmtProducto->close();
    header('Location: productos.php');
    exit;
}

$producto = $resultadoProducto->fetch_assoc();
$stmtProducto->close();

// Precio
$precio_final = $producto['precio_descuento'] ?: $producto['precio'];
$tiene_descuento = !empty($producto['precio_descuento']);

?>

<?php include '../includes/head_home.php'; ?>

<body>

<!-- Navbar -->
<?php include '../includes/navbar_home.php'; ?>

<!-- Header -->
<section class="container-fluid atenea-producto-detalle-hero">
  <div class="atenea-producto-detalle-hero-inner">
    <p class="atenea-producto-detalle-kicker">Atenea Escuela de Naturopatía Holística</p>
    <h1 class="atenea-producto-detalle-title"><?php echo $producto['nombre']; ?></h1>
    <p class="atenea-producto-detalle-summary">
      Conoce los detalles de este producto y elige la opción ideal para complementar tu bienestar.
    </p>
  </div>
</section>

<!-- Producto Detalle -->
<div class="container py-5">
  <div class="row">

    <!-- Imagen -->
    <div class="col-md-6 mb-4">
      <div class="card border-0 shadow-sm">
        <img src="../img/<?php echo $producto['imagen']; ?>" 
             class="img-fluid rounded"
             alt="<?php echo $producto['nombre']; ?>">
      </div>
    </div>

    <!-- Info -->
    <div class="col-md-6">
      <span class="text-muted"><?php echo $producto['categoria_nombre']; ?></span>
      <h2 class="font-weight-bold"><?php echo $producto['nombre']; ?></h2>

      <?php if ($tiene_descuento): ?>
        <h3 class="text-primary">
          $<?php echo number_format($precio_final, 2); ?>
          <small class="text-muted">
            <del>$<?php echo number_format($producto['precio'], 2); ?></del>
          </small>
        </h3>
      <?php else: ?>
        <h3 class="text-primary">$<?php echo number_format($precio_final, 2); ?></h3>
      <?php endif; ?>

      <p class="mt-3 text-justify">
        <?php echo nl2br($producto['descripcion']); ?>
      </p>

      <p>
        <strong>Stock:</strong>
        <?php echo $producto['stock'] > 0 ? 'Disponible' : 'Agotado'; ?>
      </p>

      <?php if ($producto['stock'] > 0): ?>
        <?php if ($ateneaCanPurchase): ?>
          <button class="btn btn-primary btn-lg mt-3"
                  onclick="agregarAlCarrito(<?php echo $producto['id']; ?>)">
            <i class="fa fa-shopping-cart"></i> Agregar al carrito
          </button>
        <?php else: ?>
          <a href="<?php echo htmlspecialchars($loginToBuyUrl); ?>" class="btn btn-outline-primary btn-lg mt-3">
            <i class="fa fa-user"></i> Iniciar sesión para comprar
          </a>
        <?php endif; ?>
      <?php else: ?>
        <button class="btn btn-secondary btn-lg mt-3" disabled>
          Sin stock
        </button>
      <?php endif; ?>

      <a href="productos.php" class="btn btn-outline-dark btn-lg mt-3 ml-2">
        Volver a la tienda
      </a>
    </div>

  </div>
</div>

<!-- Footer -->
<?php include '../includes/footer_home.php'; ?>

<!-- Back to Top -->
<a href="#" class="btn btn-primary p-3 back-to-top">
  <i class="fa fa-angle-double-up"></i>
</a>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function agregarAlCarrito(productoId) {
  fetch('carrito_add.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'producto_id=' + productoId
  })
  .then(res => res.json())
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

