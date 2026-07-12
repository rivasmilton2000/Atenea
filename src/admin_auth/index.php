<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/auth.php';
exigirRol(['admin']);
header('Location: ' . atenea_url('src/dashboard/index.php'));
exit;
