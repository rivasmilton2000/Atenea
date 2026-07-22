<?php
declare(strict_types=1);
require_once dirname(__DIR__,4).'/includes/auth.php';
exigirAutenticacion();
mostrarPaginaErrorAtenea(404);
