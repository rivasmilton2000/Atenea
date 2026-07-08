<!DOCTYPE html>
<html lang="es">
<?php
require 'session.php';
require_once '../includes/atenea_auth.php';
require_once '../includes/atenea_catalog.php';
include '../includes/connection.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: productos.php');
    exit;
}

$id_producto = (int) $_GET['id'];
$ateneaCanPurchase = logged_in();
$loginToBuyUrl = atenea_build_login_url('productos.php', 'login_required');

if ($ateneaCanPurchase && !isset($_SESSION['cart_session'])) {
    $_SESSION['cart_session'] = uniqid('cart_', true);
}

$stmtProducto = $db->prepare("
    SELECT p.*, c.nombre AS categoria_nombre,
           " . atenea_catalog_product_select_sql($db, 'p') . "
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

$offerType = atenea_catalog_normalize_type($producto['tipo_oferta'] ?? 'producto');
$offerLabel = atenea_catalog_type_label($offerType);
$duration = trim((string) ($producto['duracion'] ?? ''));
$stockLabel = atenea_catalog_stock_label($offerType);
$hasVideo = atenea_catalog_has_active_video($producto);
$videoEmbedUrl = atenea_catalog_video_embed_url((string) ($producto['video_url'] ?? ''));
$precio_final = $producto['precio_descuento'] ?: $producto['precio'];
$tiene_descuento = !empty($producto['precio_descuento']);
$heroSummary = $offerType === 'producto'
    ? 'Conoce los detalles de este producto y elige la opción ideal para complementar tu bienestar.'
    : 'Revisa esta oferta de capacitación, su duración y los recursos incluidos antes de inscribirte.';
$ctaLabel = $offerType === 'producto'
    ? 'Agregar al carrito'
    : ($offerType === 'curso' ? 'Inscribirme al curso' : 'Inscribirme a la certificación');
?>

<?php include '../includes/head_home.php'; ?>

<body>
<?php include '../includes/navbar_home.php'; ?>

<section class="container-fluid atenea-producto-detalle-hero">
  <div class="atenea-producto-detalle-hero-inner">
    <p class="atenea-producto-detalle-kicker">Atenea Escuela de Naturopatía Holística</p>
    <h1 class="atenea-producto-detalle-title"><?php echo htmlspecialchars((string) $producto['nombre'], ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="atenea-producto-detalle-summary">
      <?php echo htmlspecialchars($heroSummary, ENT_QUOTES, 'UTF-8'); ?>
    </p>
  </div>
</section>

<div class="container py-5">
  <div class="row">
    <div class="col-md-6 mb-4">
      <div class="card border-0 shadow-sm">
        <img src="../img/<?php echo htmlspecialchars((string) $producto['imagen'], ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars((string) $producto['nombre'], ENT_QUOTES, 'UTF-8'); ?>">
      </div>
    </div>

    <div class="col-md-6">
      <div class="atenea-offer-meta mb-3">
        <span class="atenea-detail-pill atenea-offer-badge atenea-offer-badge--<?php echo htmlspecialchars($offerType, ENT_QUOTES, 'UTF-8'); ?>">
          <?php echo htmlspecialchars($offerLabel, ENT_QUOTES, 'UTF-8'); ?>
        </span>
        <span class="atenea-detail-pill"><?php echo htmlspecialchars((string) $producto['categoria_nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
        <?php if ($duration !== '') : ?>
          <span class="atenea-detail-pill">Duración: <?php echo htmlspecialchars($duration, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>
        <?php if ($hasVideo) : ?>
          <span class="atenea-detail-pill">Video activo</span>
        <?php endif; ?>
      </div>

      <h2 class="font-weight-bold"><?php echo htmlspecialchars((string) $producto['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2>

      <?php if ($tiene_descuento): ?>
        <h3 class="text-primary">
          $<?php echo number_format((float) $precio_final, 2); ?>
          <small class="text-muted">
            <del>$<?php echo number_format((float) $producto['precio'], 2); ?></del>
          </small>
        </h3>
      <?php else: ?>
        <h3 class="text-primary">
          <?php echo (float) $precio_final > 0 ? '$' . number_format((float) $precio_final, 2) : 'Precio a consultar'; ?>
        </h3>
      <?php endif; ?>

      <p class="mt-3 text-justify">
        <?php echo nl2br(htmlspecialchars((string) $producto['descripcion'], ENT_QUOTES, 'UTF-8')); ?>
      </p>

      <p>
        <strong><?php echo htmlspecialchars($stockLabel, ENT_QUOTES, 'UTF-8'); ?>:</strong>
        <?php echo (int) $producto['stock'] > 0 ? (int) $producto['stock'] : htmlspecialchars(atenea_catalog_out_of_stock_label($offerType), ENT_QUOTES, 'UTF-8'); ?>
      </p>

      <?php if ((int) $producto['stock'] > 0): ?>
        <?php if ($ateneaCanPurchase): ?>
          <button class="btn btn-primary btn-lg mt-3" onclick="agregarAlCarrito(<?php echo (int) $producto['id']; ?>)">
            <i class="fa fa-shopping-cart"></i> <?php echo htmlspecialchars($ctaLabel, ENT_QUOTES, 'UTF-8'); ?>
          </button>
        <?php else: ?>
          <a href="<?php echo htmlspecialchars($loginToBuyUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary btn-lg mt-3">
            <i class="fa fa-user"></i> Iniciar sesión para comprar
          </a>
        <?php endif; ?>
      <?php else: ?>
        <button class="btn btn-secondary btn-lg mt-3" disabled>
          <?php echo htmlspecialchars(atenea_catalog_out_of_stock_label($offerType), ENT_QUOTES, 'UTF-8'); ?>
        </button>
      <?php endif; ?>

      <a href="productos.php" class="btn btn-outline-dark btn-lg mt-3 ml-2">
        Volver al catálogo
      </a>
    </div>
  </div>

  <?php if ($hasVideo && $videoEmbedUrl !== '') : ?>
    <div class="row mt-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm atenea-offer-video">
          <div class="card-body p-4">
            <h4 class="mb-3">Video informativo</h4>
            <div class="embed-responsive embed-responsive-16by9 rounded overflow-hidden">
              <iframe
                class="embed-responsive-item"
                src="<?php echo htmlspecialchars($videoEmbedUrl, ENT_QUOTES, 'UTF-8'); ?>"
                title="Video de <?php echo htmlspecialchars((string) $producto['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
              ></iframe>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
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
