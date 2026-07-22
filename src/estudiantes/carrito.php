<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/auth.php';
exigirRol(['usuario']);
header('Location: ' . atenea_url('src/carrito/index.php'), true, 302);
exit;
