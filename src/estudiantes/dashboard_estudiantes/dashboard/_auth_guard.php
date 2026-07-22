<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/includes/auth.php';
require_once dirname(__DIR__, 3) . '/includes/permissions.php';
exigirRol(['administracion_docente']);
$rutaActual = strtolower(str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '')));
if (str_contains($rutaActual, '/src/administador_docente/dashboard/')
    && !str_ends_with($rutaActual, '/src/administador_docente/dashboard/index.php')) {
    registrarFalloGlobalAtenea('Ruta heredada del template hibrido denegada.', 403);
    mostrarPaginaErrorAtenea(403);
}
