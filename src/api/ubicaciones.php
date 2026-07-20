<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/conexion.php';
require_once dirname(__DIR__, 2) . '/includes/json_response.php';

$tipo = (string) ($_GET['tipo'] ?? '');
$padre = filter_input(INPUT_GET, 'padre', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!in_array($tipo, ['municipios', 'distritos'], true) || $padre === false || $padre === null) {
    responderJsonErrorAtenea('INVALID_LOCATION_PARAMS', 'Parámetros inválidos.', 400);
}

try {
    if ($tipo === 'municipios') {
        $consulta = obtenerConexion()->prepare('SELECT id,nombre FROM municipios WHERE departamento_id=:padre ORDER BY nombre');
    } else {
        $consulta = obtenerConexion()->prepare('SELECT id,nombre FROM distritos WHERE municipio_id=:padre ORDER BY nombre');
    }
    $consulta->execute(['padre' => $padre]);
    responderJsonExitoAtenea($consulta->fetchAll());
} catch (Throwable $e) {
    error_log('Catálogo territorial Atenea: ' . $e->getMessage());
    responderJsonErrorAtenea('LOCATION_CATALOG_UNAVAILABLE', 'No fue posible cargar el catálogo.', 500);
}
