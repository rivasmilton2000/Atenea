<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__, 2) . '/includes/pedidos_pago.php';

$pdo = obtenerConexion();
if (!tablaCorreoDisponible($pdo)) {
    fwrite(STDERR, "La migración de correo no está aplicada.\n");
    exit(1);
}

$consulta = $pdo->query("SELECT pedido_id FROM correo_envios WHERE tipo='compra_confirmada' AND (estado='fallido' OR (estado='procesando' AND procesando_desde<DATE_SUB(NOW(),INTERVAL 10 MINUTE))) AND pedido_id IS NOT NULL ORDER BY updated_at LIMIT 25");
$procesados = 0;
$enviados = 0;
foreach ($consulta->fetchAll(PDO::FETCH_COLUMN) as $pedidoId) {
    $procesados++;
    if (enviarConfirmacionCompraAtenea((int) $pedidoId)) $enviados++;
}

echo "Intentos revisados: {$procesados}; confirmaciones enviadas: {$enviados}.\n";
