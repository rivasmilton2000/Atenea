<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/config.php';
$incidente=registrarFalloGlobalAtenea('Conexión a la base de datos no disponible.',503);
mostrarPaginaErrorAtenea(503,$incidente,'base_datos');
