<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/comercio.php';
require_once dirname(__DIR__, 2) . '/includes/stripe_config.php';
require_once dirname(__DIR__, 2) . '/includes/alerts.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . atenea_url('src/website/pricing.php'));
    exit;
}

$productoId = filter_var($_POST['producto_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$retornoProducto = atenea_url('src/website/product-details.php?id=' . $productoId);

if (!usuarioAutenticado()) {
    if ($productoId > 0) $_SESSION['url_retorno'] = $retornoProducto;
    $_SESSION['mensaje_auth'] = 'Inicia sesión para continuar de forma segura con tu compra.';
    $_SESSION['mensaje_auth_tipo'] = 'info';
    header('Location: ' . atenea_url('src/login/sign-in.php'));
    exit;
}
if (($_SESSION['usuario_rol'] ?? '') !== 'usuario') redirigirPorRol();
if (!validarTokenCsrf(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
    ateneaFlash('error', 'Solicitud expirada', 'Recarga la página e intenta nuevamente.');
    header('Location: ' . $retornoProducto);
    exit;
}

$cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]) ?: 0;
if ($productoId < 1 || $cantidad < 1) {
    ateneaFlash('warning', 'Cantidad no válida', 'Selecciona una cantidad válida para continuar.');
    header('Location: ' . ($productoId > 0 ? $retornoProducto : atenea_url('src/website/pricing.php')));
    exit;
}

$configuracion = configuracionStripe();
$autoload = dirname(__DIR__, 2) . '/includes/stripe/vendor/autoload.php';
if (!stripeConfigurado($configuracion) || !is_file($autoload)) {
    ateneaFlash('error', 'Pago no disponible', 'La pasarela de pago no está disponible en este momento.');
    header('Location: ' . $retornoProducto);
    exit;
}

$pdo = obtenerConexion();
$pedidoId = null;
$sesionStripe = null;
try {
    $pdo->beginTransaction();
    $consulta = $pdo->prepare('SELECT * FROM productos WHERE id=:id AND activo=1 AND disponible=1 AND eliminado_at IS NULL FOR UPDATE');
    $consulta->execute(['id' => $productoId]);
    $producto = $consulta->fetch();
    if (!$producto) throw new DomainException('El producto ya no está disponible.');
    $disponible = (int) $producto['stock'] - (int) $producto['stock_reservado'];
    if ($cantidad > $disponible) throw new DomainException('La cantidad solicitada ya no está disponible.');

    $precio = precioProducto($producto, promocionVigente($pdo, $productoId));
    $subtotalNormal = round($precio['normal'] * $cantidad, 2);
    $descuento = round($precio['descuento'] * $cantidad, 2);
    $total = round($precio['final'] * $cantidad, 2);
    $numero = numeroPedido();

    $consulta = $pdo->prepare("INSERT INTO pedidos(numero,usuario_id,subtotal,descuento,total,moneda,estado,payment_status) VALUES(:numero,:usuario,:subtotal,:descuento,:total,:moneda,'pendiente','pending')");
    $consulta->execute(['numero' => $numero, 'usuario' => $_SESSION['usuario_id'], 'subtotal' => $subtotalNormal, 'descuento' => $descuento, 'total' => $total, 'moneda' => $configuracion['currency']]);
    $pedidoId = (int) $pdo->lastInsertId();

    $consulta = $pdo->prepare('INSERT INTO pedido_detalles(pedido_id,producto_id,nombre_producto,sku,cantidad,precio_normal,precio_unitario,descuento_unitario,subtotal,promocion_id) VALUES(:pedido,:producto,:nombre,:sku,:cantidad,:normal,:unitario,:descuento,:subtotal,:promocion)');
    $consulta->execute(['pedido' => $pedidoId, 'producto' => $productoId, 'nombre' => $producto['nombre'], 'sku' => $producto['sku'], 'cantidad' => $cantidad, 'normal' => $precio['normal'], 'unitario' => $precio['final'], 'descuento' => $precio['descuento'], 'subtotal' => $total, 'promocion' => $precio['promocion']['id'] ?? null]);
    $pdo->prepare('UPDATE productos SET stock_reservado=stock_reservado+:cantidad WHERE id=:id')->execute(['cantidad' => $cantidad, 'id' => $productoId]);
    registrarHistorialPedido($pdo, $pedidoId, null, 'pendiente', 'sistema', (int) $_SESSION['usuario_id'], 'Pedido creado con precio recalculado en el servidor; stock reservado.');
    $pdo->commit();

    require_once $autoload;
    $stripe = new Stripe\StripeClient($configuracion['secret_key']);
    $sesionStripe = $stripe->checkout->sessions->create([
        'mode' => 'payment',
        'customer_email' => (string) $_SESSION['usuario_correo'],
        'client_reference_id' => (string) $pedidoId,
        'line_items' => [[
            'quantity' => $cantidad,
            'price_data' => [
                'currency' => $configuracion['currency'],
                'unit_amount' => (int) round($precio['final'] * 100),
                'product_data' => ['name' => (string) $producto['nombre'], 'description' => (string) $producto['descripcion_corta']],
            ],
        ]],
        'metadata' => ['pedido_id' => (string) $pedidoId, 'numero' => $numero],
        'payment_intent_data' => ['metadata' => ['pedido_id' => (string) $pedidoId, 'numero' => $numero]],
        'success_url' => atenea_url_absoluta('src/pagos/success.php?session_id={CHECKOUT_SESSION_ID}'),
        'cancel_url' => atenea_url_absoluta('src/pagos/cancel.php?pedido=' . $pedidoId),
    ], ['idempotency_key' => 'checkout-pedido-' . $pedidoId]);

    $pdo->beginTransaction();
    $consulta = $pdo->prepare("UPDATE pedidos SET estado='esperando_pago',payment_status='pending',stripe_checkout_session_id=:sesion WHERE id=:id AND estado='pendiente'");
    $consulta->execute(['sesion' => $sesionStripe->id, 'id' => $pedidoId]);
    if ($consulta->rowCount() !== 1) throw new RuntimeException('No fue posible vincular la sesión de pago.');
    registrarHistorialPedido($pdo, $pedidoId, 'pendiente', 'esperando_pago', 'stripe', null, 'Checkout de Stripe creado.');
    $pdo->commit();

    header('Location: ' . $sesionStripe->url, true, 303);
    exit;
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if ($sesionStripe instanceof Stripe\Checkout\Session) {
        try { $stripe->checkout->sessions->expire($sesionStripe->id); } catch (Throwable) {}
    }
    if ($pedidoId) {
        try {
            $pdo->beginTransaction();
            $consulta = $pdo->prepare("SELECT estado FROM pedidos WHERE id=:id FOR UPDATE");
            $consulta->execute(['id' => $pedidoId]);
            $estado = $consulta->fetchColumn();
            if (in_array($estado, ['pendiente', 'esperando_pago'], true)) {
                $pdo->prepare("UPDATE pedidos SET estado='fallido',payment_status='failed' WHERE id=:id")->execute(['id' => $pedidoId]);
                $pdo->prepare('UPDATE productos SET stock_reservado=GREATEST(stock_reservado-:cantidad,0) WHERE id=:id')->execute(['cantidad' => $cantidad, 'id' => $productoId]);
                registrarHistorialPedido($pdo, $pedidoId, (string) $estado, 'fallido', 'sistema', null, 'No fue posible completar la creación del Checkout; reserva liberada.');
            }
            $pdo->commit();
        } catch (Throwable $compensacion) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Compensación checkout Atenea: ' . $compensacion->getMessage());
        }
    }
    error_log('Checkout Atenea: ' . $error->getMessage());
    ateneaFlash('error', 'No fue posible iniciar el pago', $error instanceof DomainException ? $error->getMessage() : 'Intenta nuevamente dentro de unos momentos.');
    header('Location: ' . $retornoProducto);
    exit;
}
