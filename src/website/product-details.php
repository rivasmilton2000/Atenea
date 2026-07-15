<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/comercio.php';
require_once dirname(__DIR__, 2) . '/includes/alerts.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$producto = productoPublico($id);
$pageTitle = $producto ? $producto['nombre'] . ' | Atenea' : 'Producto no encontrado | Atenea';
$pageDescription = $producto['descripcion_corta'] ?? 'Producto Atenea';
$pageClass = 'product-details-page';
$activePage = 'productos';
if (!$producto) http_response_code(404);
require dirname(__DIR__, 2) . '/includes/header.php';

if (!$producto):
?>
<main class="main"><section class="section"><div class="container"><div class="product-empty-state text-center mx-auto"><i class="bi bi-bag-x" aria-hidden="true"></i><h1>Producto no encontrado</h1><p>El producto no existe o ya no está disponible en el catálogo.</p><a class="btn-atenea" href="<?= atenea_url('src/website/pricing.php') ?>">Volver a productos</a></div></div></section></main>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; exit; endif;

$pdo = obtenerConexion();
$consulta = $pdo->prepare('SELECT ruta FROM producto_imagenes WHERE producto_id=:id ORDER BY orden,id');
$consulta->execute(['id' => $id]);
$galeria = array_values(array_unique(array_filter(array_merge([(string) ($producto['imagen_principal'] ?? '')], array_column($consulta->fetchAll(), 'ruta')))));
if (!$galeria) $galeria = [''];
$precio = $producto['precio_calculado'];
$disponibles = max(0, (int) $producto['stock'] - (int) $producto['stock_reservado']);
$agotado = !$producto['disponible'] || $disponibles < 1;
$maximoCompra = min(99, $disponibles);
$caracteristicas = array_values(array_filter(array_map('trim', preg_split('/\R+/', (string) ($producto['caracteristicas'] ?? '')) ?: [])));
$porcentaje = $precio['promocion_valida'] ? (float) ($precio['promocion']['porcentaje_descuento'] ?? 0) : 0;
if ($precio['promocion_valida'] && $porcentaje <= 0 && $precio['normal'] > 0) $porcentaje = round(($precio['descuento'] / $precio['normal']) * 100);

$relacionados = [];
if (!empty($producto['categoria_id'])) {
    $consulta = $pdo->prepare('SELECT p.*,c.nombre categoria FROM productos p LEFT JOIN categorias_producto c ON c.id=p.categoria_id WHERE p.categoria_id=:categoria AND p.id<>:id AND p.activo=1 AND p.disponible=1 AND p.eliminado_at IS NULL ORDER BY p.created_at DESC LIMIT 3');
    $consulta->execute(['categoria' => $producto['categoria_id'], 'id' => $id]);
    $relacionados = $consulta->fetchAll();
    foreach ($relacionados as &$relacionado) $relacionado['precio_calculado'] = precioProducto($relacionado, promocionVigente($pdo, (int) $relacionado['id']));
    unset($relacionado);
}
$esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
?>
<main class="main">
  <div class="page-title product-page-title"><nav class="breadcrumbs"><div class="container"><ol><li><a href="<?= atenea_url('index.php') ?>">Inicio</a></li><li><a href="<?= atenea_url('src/website/pricing.php') ?>">Productos</a></li><li class="current" aria-current="page"><?= atenea_e((string) $producto['nombre']) ?></li></ol></div></nav></div>
  <section class="product-detail section"><div class="container"><div class="row gy-5 gx-lg-5 align-items-start">
    <div class="col-lg-7">
      <div class="product-gallery" data-product-gallery>
        <div class="product-gallery-main"><img id="product-main-image" src="<?= imagenProducto($galeria[0]) ?>" alt="<?= atenea_e((string) $producto['nombre']) ?>" loading="eager"></div>
        <?php if (count($galeria) > 1): ?><div class="product-thumbnails" role="list" aria-label="Galería de producto"><?php foreach ($galeria as $indice => $ruta): ?><button class="product-thumbnail<?= $indice === 0 ? ' is-active' : '' ?>" type="button" data-product-image="<?= imagenProducto($ruta) ?>" data-product-alt="<?= atenea_e((string) $producto['nombre'] . ' · imagen ' . ($indice + 1)) ?>" aria-label="Mostrar imagen <?= $indice + 1 ?>" aria-pressed="<?= $indice === 0 ? 'true' : 'false' ?>"><img src="<?= imagenProducto($ruta) ?>" alt="" loading="lazy"></button><?php endforeach; ?></div><?php endif; ?>
      </div>
    </div>
    <div class="col-lg-5"><article class="product-purchase-card">
      <?php if ($producto['categoria']): ?><p class="product-category"><?= atenea_e((string) $producto['categoria']) ?></p><?php endif; ?>
      <h1><?= atenea_e((string) $producto['nombre']) ?></h1>
      <p class="product-summary"><?= atenea_e((string) $producto['descripcion_corta']) ?></p>
      <div class="product-price-row"><?php if ($precio['promocion_valida']): ?><del aria-label="Precio anterior">$<?= number_format($precio['normal'], 2) ?></del><?php endif; ?><strong>$<?= number_format($precio['final'], 2) ?> <small>USD</small></strong><?php if ($precio['promocion_valida']): ?><span class="product-offer-badge"><?= atenea_e((string) ($precio['promocion']['etiqueta'] ?: ('-' . number_format($porcentaje, 0) . '%'))) ?></span><?php endif; ?></div>
      <p class="product-stock <?= $agotado ? 'is-empty' : 'is-available' ?>"><i class="bi <?= $agotado ? 'bi-x-circle' : 'bi-check-circle' ?>" aria-hidden="true"></i> <?= $agotado ? 'Agotado' : ($disponibles <= 10 ? 'Últimas ' . $disponibles . ' unidades disponibles' : 'En existencias') ?></p>
      <?php if (!$agotado): ?><form class="product-checkout-form" method="post" action="<?= atenea_url('src/pagos/crear-checkout.php') ?>" data-checkout-form>
        <input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>"><input type="hidden" name="producto_id" value="<?= $id ?>">
        <div class="product-quantity"><label for="cantidadProducto">Cantidad</label><div class="quantity-control"><button type="button" data-quantity="down" aria-label="Reducir cantidad">−</button><input id="cantidadProducto" type="number" name="cantidad" min="1" max="<?= $maximoCompra ?>" value="1" inputmode="numeric" required><button type="button" data-quantity="up" aria-label="Aumentar cantidad">+</button></div></div>
        <button class="product-buy-button" type="submit"><span class="product-buy-label"><i class="bi bi-lock" aria-hidden="true"></i> Continuar al pago</span><span class="product-buy-loading" aria-hidden="true"><span class="spinner-border spinner-border-sm"></span> Preparando pago…</span></button>
      </form><?php else: ?><button class="product-buy-button" type="button" disabled><i class="bi bi-bag-x" aria-hidden="true"></i> Producto agotado</button><?php endif; ?>
      <div class="payment-trust" aria-label="Información sobre el pago"><div class="payment-trust-heading"><i class="bi bi-shield-lock" aria-hidden="true"></i><div><strong>Pago procesado de forma segura por Stripe</strong><span>Los datos completos de la tarjeta no se almacenan en Atenea.</span></div></div><div class="payment-networks" aria-label="Redes de tarjeta admitidas"><span>Visa</span><span>Mastercard</span><span>Stripe</span></div><?php if ($esHttps): ?><p><i class="bi bi-lock-fill" aria-hidden="true"></i> Conexión HTTPS activa</p><?php endif; ?></div>
      <a class="product-back-link" href="<?= atenea_url('src/website/pricing.php') ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> Volver a productos</a>
    </article></div>
  </div></div></section>

  <section class="product-information section light-background"><div class="container"><div class="row gy-4">
    <div class="col-lg-8"><article class="product-content-card"><h2>Descripción</h2><div class="product-description"><?= nl2br(atenea_e((string) $producto['descripcion'])) ?></div></article></div>
    <div class="col-lg-4"><aside class="product-content-card h-100"><h2>Información del producto</h2><?php if ($caracteristicas): ?><ul class="product-feature-list"><?php foreach ($caracteristicas as $caracteristica): ?><li><i class="bi bi-check2" aria-hidden="true"></i><span><?= atenea_e($caracteristica) ?></span></li><?php endforeach; ?></ul><?php else: ?><p class="text-muted">Consulta la descripción para conocer las características disponibles.</p><?php endif; ?><hr><h3 class="h6">Entrega, acceso o modalidad</h3><p class="mb-0"><?= atenea_e(trim((string) ($producto['informacion_entrega'] ?? '')) ?: 'La modalidad y los detalles de entrega o acceso se confirmarán con tu pedido.') ?></p></aside></div>
  </div></div></section>

  <?php if ($relacionados): ?><section class="related-products section"><div class="container"><div class="section-title"><h2>Productos relacionados</h2><p>También puede interesarte</p></div><div class="row gy-4"><?php foreach ($relacionados as $relacionado): $precioRelacionado = $relacionado['precio_calculado']; ?><div class="col-md-6 col-lg-4"><article class="related-product-card h-100"><img src="<?= imagenProducto($relacionado['imagen_principal']) ?>" alt="<?= atenea_e((string) $relacionado['nombre']) ?>" loading="lazy"><div><p><?= atenea_e((string) $relacionado['categoria']) ?></p><h3><?= atenea_e((string) $relacionado['nombre']) ?></h3><strong>$<?= number_format($precioRelacionado['final'], 2) ?> USD</strong><a href="<?= atenea_url('src/website/product-details.php?id=' . (int) $relacionado['id']) ?>">Ver detalle <i class="bi bi-arrow-right"></i></a></div></article></div><?php endforeach; ?></div></div></section><?php endif; ?>
</main>
<script src="<?= atenea_url('src/website/assets/js/product-details.js') ?>"></script>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
