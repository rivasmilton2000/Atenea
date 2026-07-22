<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/dte.php';
exigirRol(['usuario', 'admin', 'administracion_docente', 'administrador_docente']);

$pedido = filter_var($_GET['pedido'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$tipo = (string) ($_GET['tipo'] ?? 'pdf');
$descargar = ($_GET['descargar'] ?? '') === '1';
$pdo = obtenerConexion();
$sql = "SELECT d.*,p.usuario_id,p.numero,p.payment_status FROM dte_documentos d JOIN pedidos p ON p.id=d.pedido_id WHERE d.pedido_id=:pedido AND p.payment_status='paid'";
$args = ['pedido' => $pedido];
if (!in_array(($_SESSION['usuario_rol']??''),['admin','administracion_docente','administrador_docente'],true)) {
    $sql .= ' AND p.usuario_id=:usuario';
    $args['usuario'] = $_SESSION['usuario_id'];
}
$q = $pdo->prepare($sql);
$q->execute($args);
$documento = $q->fetch();
if (!$documento) mostrarPaginaErrorAtenea(404);

$nombre = preg_replace('/[^A-Z0-9-]/i', '-', $documento['numero_control']);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
if ($tipo === 'json') {
    header('Content-Type: application/json; charset=UTF-8');
    header('Content-Disposition: ' . ($descargar ? 'attachment' : 'inline') . '; filename="' . $nombre . '.json"');
    echo json_encode(json_decode($documento['json_documento'], true, 512, JSON_THROW_ON_ERROR), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    exit;
}

$base = entornoAtenea('DTE_STORAGE_PATH', dirname(ATENEA_ROOT) . '/atenea-private/dte');
$root = realpath($base);
$path = realpath(rtrim($base, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $documento['pdf_relpath']));
if (!$root || !$path || !str_starts_with(strtolower($path), strtolower($root . DIRECTORY_SEPARATOR)) || !is_file($path)) mostrarPaginaErrorAtenea(404);

header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($path));
header('Content-Disposition: ' . ($descargar ? 'attachment' : 'inline') . '; filename="' . $nombre . '.pdf"');
readfile($path);
