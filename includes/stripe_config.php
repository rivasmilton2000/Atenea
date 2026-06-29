<?php

// Modo de Stripe:
// - test: sandbox (por defecto)
// - live: produccion
if (!defined('STRIPE_MODE')) {
    $stripe_mode = getenv('STRIPE_MODE') ?: 'test';
    define('STRIPE_MODE', strtolower($stripe_mode) === 'live' ? 'live' : 'test');
}

//Clave secreta para stripe 
if (!defined('STRIPE_SECRET_KEY')) {
    if (STRIPE_MODE === 'live') {
        define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY_LIVE') ?: 'sk_live_REEMPLAZA_AQUI');
    } else {
        define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY_TEST') ?: 'sk_test_51T09gWE8YH5P1jJkx2icAKfbPwb8K8bEeppD4Dzpkti0PRgqeBZU5x1g7oHLCLyqWOVfpn35xRILyinNVDY8CZtL00Yrlsb1bT');
    }
}
//clave publica para stripe
if (!defined('STRIPE_PUBLISHABLE_KEY')) {
    if (STRIPE_MODE === 'live') {
        define('STRIPE_PUBLISHABLE_KEY', getenv('STRIPE_PUBLISHABLE_KEY_LIVE') ?: 'pk_live_REEMPLAZA_AQUI');
    } else {
        define('STRIPE_PUBLISHABLE_KEY', getenv('STRIPE_PUBLISHABLE_KEY_TEST') ?: 'pk_test_51T09gWE8YH5P1jJksI3DLYSKwIeLbdUYjJNEm9xGW3UcaA9qzwo1BXsOJz9VMVi89VE7bJh2apVXpoNLJ1B9CbeF00aCVIvyt2');
    }
}

if (!defined('APP_BASE_URL')) {
    // Base URL local de este proyecto en XAMPP.
    define('APP_BASE_URL', getenv('APP_BASE_URL') ?: 'http://localhost/Atenea');
}
