<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/conexion.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$tipo = (string) ($_GET['tipo'] ?? '');
$padre = filter_input(INPUT_GET, 'padre', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!in_array($tipo, ['municipios', 'distritos'], true) || $padre === false || $padre === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros inválidos.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($tipo === 'municipios') {
        $consulta = obtenerConexion()->prepare('SELECT id,nombre FROM municipios WHERE departamento_id=:padre ORDER BY nombre');
    } else {
        $consulta = obtenerConexion()->prepare('SELECT id,nombre FROM distritos WHERE municipio_id=:padre ORDER BY nombre');
    }
    $consulta->execute(['padre' => $padre]);
    echo json_encode($consulta->fetchAll(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('Catálogo territorial Atenea: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No fue posible cargar el catálogo.'], JSON_UNESCAPED_UNICODE);
}
