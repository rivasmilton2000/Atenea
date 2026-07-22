<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/pedidos_pago.php';
require_once dirname(__DIR__, 2) . '/includes/dte.php';
exigirRol(['usuario']);
$sesion = substr((string) ($_GET['session_id'] ?? ''), 0, 255);
$sincronizado = sincronizarRetornoPedidoStripe($sesion, (int) $_SESSION['usuario_id']);
if ($sincronizado) {
    try { generarDtePedidoSeguro((int)$sincronizado['pedido_id']); enviarConfirmacionCompraAtenea((int)$sincronizado['pedido_id']); }
    catch(Throwable $error) { error_log('Documentos después del retorno Stripe: '.preg_replace('/[\r\n\t]+/',' ',$error->getMessage())); }
}
$consulta = obtenerConexion()->prepare('SELECT id,numero,total,moneda,estado,payment_status FROM pedidos WHERE stripe_checkout_session_id=:sesion AND usuario_id=:usuario LIMIT 1');
$consulta->execute(['sesion' => $sesion, 'usuario' => $_SESSION['usuario_id']]);
$pedido = $consulta->fetch();
$pageTitle = 'Estado del pedido | Atenea';
$activePage = 'productos';
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<main class="main"><section class="section"><div class="container"><div class="payment-result-card mx-auto text-center">
<?php if ($pedido): ?>
  <?php if ($pedido['payment_status'] === 'paid'): ?>
    <span class="payment-result-icon is-success"><i class="bi bi-check-lg"></i></span><h1>Pago completado</h1><p>Tu pedido <strong><?= atenea_e((string) $pedido['numero']) ?></strong> fue confirmado por Stripe.</p><p class="fs-5">$<?= number_format((float) $pedido['total'], 2) ?> <?= atenea_e(strtoupper((string) $pedido['moneda'])) ?></p><a class="btn-atenea" href="<?= atenea_url('src/estudiantes/comprobante.php?pedido=' . (int) $pedido['id']) ?>">Ver comprobante</a>
  <?php else: ?>
    <span class="payment-result-icon is-pending"><i class="bi bi-hourglass-split"></i></span><h1>Pago en verificación</h1><p>El pago sigue pendiente. Puedes volver a consultar su estado sin realizar otro pago.</p><p>Pedido <?= atenea_e((string) $pedido['numero']) ?> · $<?= number_format((float) $pedido['total'], 2) ?> <?= atenea_e(strtoupper((string) $pedido['moneda'])) ?></p><a class="btn-atenea" href="?session_id=<?=rawurlencode($sesion)?>">Volver a consultar</a>
  <?php endif; ?>
<?php else: ?>
  <span class="payment-result-icon is-error"><i class="bi bi-exclamation-lg"></i></span><h1>Pedido no encontrado</h1><p>No pudimos relacionar esta sesión con un pedido de tu cuenta.</p><a class="btn-atenea" href="<?= atenea_url('src/estudiantes/pedidos.php') ?>">Ver mis pedidos</a>
<?php endif; ?>
</div></div></section></main>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
