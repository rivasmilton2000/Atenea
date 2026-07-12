<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/config.php';
header('Location: ' . atenea_url('login.php'));
exit;
