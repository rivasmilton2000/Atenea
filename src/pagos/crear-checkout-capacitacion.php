<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/stripe_config.php';
require_once dirname(__DIR__, 2) . '/includes/capacitaciones.php';
require_once dirname(__DIR__, 2) . '/includes/alerts.php';

exigirRol(['usuario']);
$retorno = atenea_url('src/website/courses.php');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !validarTokenCsrf((string) ($_POST['csrf_token'] ?? ''))) {
    ateneaFlash('error', 'Solicitud expirada', 'Recarga la capacitación e inténtalo nuevamente.');
    header('Location:' . $retorno);
    exit;
}
$asignaturaId = filter_var($_POST['capacitacion_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
if ($asignaturaId < 1) { http_response_code(400); exit; }
$config = configuracionStripe();
$autoload = dirname(__DIR__, 2) . '/includes/stripe/vendor/autoload.php';
if (!stripeConfigurado($config) || !is_file($autoload)) {
    ateneaFlash('error', 'Pago no disponible', 'Stripe no está configurado correctamente.');
    header('Location:' . $retorno);
    exit;
}

$pdo = obtenerConexion();
$pagoId = 0;
try {
    $pdo->beginTransaction();
    $q = $pdo->prepare("SELECT * FROM asignaturas WHERE id=:id AND estado_capacitacion='publicada' AND estado='activo' AND activo=1 AND deleted_at IS NULL FOR UPDATE");
    $q->execute(['id' => $asignaturaId]);
    $capacitacion = $q->fetch();
    if (!$capacitacion) throw new DomainException('La capacitación no está disponible.');
    if ((float) $capacitacion['precio'] <= 0) throw new DomainException('Esta capacitación no tiene un precio válido para Checkout.');
    $q = $pdo->prepare("SELECT cp.estado FROM capacitacion_pagos cp WHERE cp.usuario_id=:u AND cp.asignatura_id=:a AND cp.estado IN('pendiente','pagado') ORDER BY cp.id DESC LIMIT 1 FOR UPDATE");
    $q->execute(['u' => $_SESSION['usuario_id'], 'a' => $asignaturaId]);
    if ($previo = $q->fetchColumn()) throw new DomainException($previo === 'pagado' ? 'Ya tienes una inscripción pagada para esta capacitación.' : 'Ya existe un pago pendiente. No inicies otro cobro.');
    $clave = bin2hex(random_bytes(32));
    $q = $pdo->prepare("INSERT INTO capacitacion_pagos(usuario_id,asignatura_id,checkout_key,importe,moneda,estado) VALUES(:u,:a,:clave,:importe,:moneda,'pendiente')");
    $q->execute(['u' => $_SESSION['usuario_id'], 'a' => $asignaturaId, 'clave' => $clave, 'importe' => $capacitacion['precio'], 'moneda' => strtolower((string) $config['currency'])]);
    $pagoId = (int) $pdo->lastInsertId();
    $pdo->commit();

    require_once $autoload;
    $stripe = new Stripe\StripeClient($config['secret_key']);
    $session = $stripe->checkout->sessions->create([
        'mode' => 'payment',
        'customer_email' => (string) $_SESSION['usuario_correo'],
        'client_reference_id' => (string) $pagoId,
        'line_items' => [[
            'quantity' => 1,
            'price_data' => [
                'currency' => strtolower((string) $config['currency']),
                'unit_amount' => (int) round((float) $capacitacion['precio'] * 100),
                'product_data' => ['name' => (string) $capacitacion['nombre'], 'description' => mb_substr((string) $capacitacion['descripcion_corta'], 0, 500)],
            ],
        ]],
        'metadata' => ['tipo' => 'capacitacion', 'capacitacion_pago_id' => (string) $pagoId, 'asignatura_id' => (string) $asignaturaId],
        'payment_intent_data' => ['metadata' => ['tipo' => 'capacitacion', 'capacitacion_pago_id' => (string) $pagoId, 'asignatura_id' => (string) $asignaturaId]],
        'success_url' => atenea_url_absoluta('src/pagos/success-capacitacion.php?session_id={CHECKOUT_SESSION_ID}'),
        'cancel_url' => atenea_url_absoluta('src/website/capacitacion.php?slug=' . rawurlencode((string) $capacitacion['slug'])),
    ], ['idempotency_key' => 'checkout-capacitacion-' . $pagoId]);
    $q = $pdo->prepare("UPDATE capacitacion_pagos SET stripe_checkout_session_id=:session WHERE id=:id AND estado='pendiente' AND stripe_checkout_session_id IS NULL");
    $q->execute(['session' => $session->id, 'id' => $pagoId]);
    if ($q->rowCount() !== 1) { try { $stripe->checkout->sessions->expire($session->id); } catch (Throwable) {} throw new RuntimeException('No se pudo vincular la sesión de Stripe.'); }
    header('Location:' . $session->url, true, 303);
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if ($pagoId) $pdo->prepare("UPDATE capacitacion_pagos SET estado='fallido' WHERE id=:id AND stripe_checkout_session_id IS NULL")->execute(['id' => $pagoId]);
    ateneaFlash('warning', 'No fue posible iniciar el pago', $e instanceof DomainException ? $e->getMessage() : 'No se realizó ningún cobro. Inténtalo nuevamente.');
    header('Location:' . $retorno);
    exit;
}
