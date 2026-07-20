<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/carrito.php';
require_once dirname(__DIR__, 2) . '/includes/alerts.php';
$pdo = obtenerConexion();
$usuarioActual = obtenerUsuarioActual();
$resumen = resumenCarritoActualAtenea($pdo);
$esCliente = $usuarioActual !== null && $usuarioActual['rol'] === 'usuario';
$pageTitle = 'Carrito de compras | Atenea';
$pageDescription = 'Revisa los productos, ofertas, existencias y total de tu carrito Atenea.';
$pageClass = 'cart-page'; $activePage = 'productos';
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<main class="main cart-main">
  <div class="page-title"><nav class="breadcrumbs"><div class="container"><ol><li><a href="<?= atenea_url('index.php') ?>">Inicio</a></li><li><a href="<?= atenea_url('src/website/pricing.php') ?>">Productos</a></li><li class="current" aria-current="page">Carrito</li></ol></div></nav></div>
  <section class="section cart-section"><div class="container">
    <div class="cart-heading"><div><span class="cart-kicker">Tu selección</span><h1>Carrito de compras</h1><p>Los precios y la disponibilidad se comprobarán nuevamente antes de crear el pago.</p></div><span class="cart-heading-count"><i class="bi bi-cart3" aria-hidden="true"></i> <?= (int)$resumen['cantidad'] ?> producto<?= (int)$resumen['cantidad'] === 1 ? '' : 's' ?></span></div>
    <?php if (!$resumen['items']): ?>
      <div class="cart-empty" role="status"><span class="cart-empty-icon"><i class="bi bi-cart-x" aria-hidden="true"></i></span><h2>Tu carrito está vacío</h2><p>Explora la tienda y añade los productos que quieras llevar contigo.</p><a class="cart-primary-button" href="<?= atenea_url('src/website/pricing.php') ?>"><i class="bi bi-bag" aria-hidden="true"></i> Ver productos</a></div>
    <?php else: ?>
      <div class="row g-4 align-items-start"><div class="col-lg-8"><div class="cart-items" aria-label="Productos del carrito">
        <?php foreach ($resumen['items'] as $item): $agotado=(int)$item['disponible_real']<1||empty($item['disponible']); $excede=(int)$item['cantidad']>(int)$item['disponible_real']; ?>
          <article class="cart-item<?= ($agotado || $excede) ? ' has-stock-warning' : '' ?>">
            <a class="cart-item-image" href="<?= atenea_url('src/website/product-details.php?id='.(int)$item['id']) ?>"><img src="<?= atenea_e(imagenProducto($item['imagen_principal'] ?? null)) ?>" alt="<?= atenea_e((string)$item['nombre']) ?>" loading="lazy"></a>
            <div class="cart-item-info"><div class="cart-item-title-row"><div><h2><a href="<?= atenea_url('src/website/product-details.php?id='.(int)$item['id']) ?>"><?= atenea_e((string)$item['nombre']) ?></a></h2><?php if (!empty($item['promo_id'])): ?><span class="cart-offer"><i class="bi bi-tag" aria-hidden="true"></i> <?= atenea_e((string)($item['promo_etiqueta'] ?: 'Oferta vigente')) ?></span><?php endif; ?></div><form method="post" action="<?= atenea_url('src/carrito/accion.php') ?>" data-atenea-confirm="eliminar-carrito" data-atenea-danger="true" data-atenea-confirm-title="¿Eliminar este producto?" data-atenea-confirm-message="El producto se quitará de tu carrito."><input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="producto_id" value="<?= (int)$item['id'] ?>"><button class="cart-remove" type="submit" aria-label="Eliminar <?= atenea_e((string)$item['nombre']) ?>"><i class="bi bi-trash3" aria-hidden="true"></i><span>Eliminar</span></button></form></div>
              <div class="cart-price"><?php if ((int)$item['descuento_centavos']>0): ?><del>$<?= centavosDinero((int)$item['precio_normal_centavos']) ?></del><?php endif; ?><strong>$<?= centavosDinero((int)$item['precio_centavos']) ?> USD</strong><span>por unidad</span></div>
              <p class="cart-stock <?= ($agotado || $excede) ? 'is-warning' : '' ?>"><i class="bi <?= ($agotado || $excede) ? 'bi-exclamation-circle' : 'bi-check-circle' ?>" aria-hidden="true"></i> <?= $agotado ? 'Producto agotado' : ($excede ? 'Reduce la cantidad: quedan '.(int)$item['disponible_real'].' unidades' : (int)$item['disponible_real'].' unidades disponibles') ?></p>
              <div class="cart-item-actions"><div class="cart-quantity">
                <form method="post" action="<?= atenea_url('src/carrito/accion.php') ?>"><input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>"><input type="hidden" name="accion" value="disminuir"><input type="hidden" name="producto_id" value="<?= (int)$item['id'] ?>"><button type="submit" aria-label="Disminuir cantidad"><i class="bi bi-dash" aria-hidden="true"></i></button></form>
                <form class="cart-quantity-value" method="post" action="<?= atenea_url('src/carrito/accion.php') ?>"><input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>"><input type="hidden" name="accion" value="actualizar"><input type="hidden" name="producto_id" value="<?= (int)$item['id'] ?>"><label class="visually-hidden" for="cantidad-<?= (int)$item['id'] ?>">Cantidad</label><input id="cantidad-<?= (int)$item['id'] ?>" type="number" name="cantidad" min="1" max="<?= max(1,min(99,(int)$item['disponible_real'])) ?>" value="<?= (int)$item['cantidad'] ?>" inputmode="numeric" required><button type="submit" title="Guardar cantidad" aria-label="Guardar cantidad"><i class="bi bi-check2" aria-hidden="true"></i></button></form>
                <form method="post" action="<?= atenea_url('src/carrito/accion.php') ?>"><input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>"><input type="hidden" name="accion" value="incrementar"><input type="hidden" name="producto_id" value="<?= (int)$item['id'] ?>"><button type="submit" aria-label="Aumentar cantidad" <?= (int)$item['cantidad']>=min(99,(int)$item['disponible_real'])?'disabled':'' ?>><i class="bi bi-plus" aria-hidden="true"></i></button></form>
              </div><div class="cart-line-total"><span>Subtotal</span><strong>$<?= centavosDinero((int)$item['linea_centavos']) ?></strong></div></div>
            </div>
          </article>
        <?php endforeach; ?>
      </div><div class="cart-list-actions"><a href="<?= atenea_url('src/website/pricing.php') ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> Continuar comprando</a><form method="post" action="<?= atenea_url('src/carrito/accion.php') ?>" data-atenea-confirm="vaciar-carrito" data-atenea-danger="true" data-atenea-confirm-title="¿Vaciar todo el carrito?" data-atenea-confirm-message="Se eliminarán todos los productos de tu selección."><input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>"><input type="hidden" name="accion" value="vaciar"><button type="submit"><i class="bi bi-trash3" aria-hidden="true"></i> Vaciar carrito</button></form></div></div>
      <div class="col-lg-4"><aside class="cart-summary"><h2>Resumen de compra</h2><div class="cart-summary-row"><span>Subtotal</span><strong>$<?= centavosDinero((int)$resumen['subtotal']) ?></strong></div><?php if ((int)$resumen['descuento']>0): ?><div class="cart-summary-row is-saving"><span>Descuentos</span><strong>−$<?= centavosDinero((int)$resumen['descuento']) ?></strong></div><?php endif; ?><div class="cart-summary-row"><span>Envío</span><strong><?= (int)$resumen['envio']?'$'.centavosDinero((int)$resumen['envio']):'Sin costo' ?></strong></div><div class="cart-summary-row"><span>Impuestos</span><strong><?= (int)$resumen['impuesto']?'$'.centavosDinero((int)$resumen['impuesto']):'Incluidos' ?></strong></div><div class="cart-summary-total"><span>Total</span><strong>$<?= centavosDinero((int)$resumen['total']) ?> <small>USD</small></strong></div>
        <?php $bloqueado=array_filter($resumen['items'],static fn($i)=>(int)$i['cantidad']>(int)$i['disponible_real']||empty($i['disponible'])); if ($bloqueado): ?><div class="cart-summary-alert" role="alert"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><span>Ajusta los productos sin existencias antes de continuar.</span></div><?php endif; ?>
        <?php if (!$esCliente): ?><p class="cart-login-note"><i class="bi bi-person-lock" aria-hidden="true"></i> Inicia sesión para guardar el carrito en tu cuenta y completar el pago.</p><?php endif; ?>
        <a class="cart-primary-button<?= $bloqueado?' is-disabled':'' ?>" href="<?= $bloqueado?'#':atenea_url('src/estudiantes/checkout.php') ?>" <?= $bloqueado?'aria-disabled="true" tabindex="-1"':'' ?>><i class="bi bi-lock" aria-hidden="true"></i> <?= $esCliente?'Proceder al pago':'Iniciar sesión y pagar' ?></a><p class="cart-security"><i class="bi bi-shield-check" aria-hidden="true"></i> Pago seguro. El precio y el stock se validan en el servidor.</p>
      </aside></div></div>
    <?php endif; ?>
  </div></section>
</main>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
