<?php
declare(strict_types=1);

// Compatibilidad: la configuración activa vive en includes/config/services.php.
require_once dirname(__DIR__) . '/includes/config/services.php';

return GoogleConfig::toArray();
