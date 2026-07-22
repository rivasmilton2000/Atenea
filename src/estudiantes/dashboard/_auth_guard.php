<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/includes/auth.php';
exigirPerfilCompleto();
header('Location: '.atenea_url('src/estudiantes/index.php'),true,302);
exit;
