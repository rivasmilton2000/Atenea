<?php
declare(strict_types=1);

// Compatibilidad: los consumidores actuales conservan estas funciones.
require_once __DIR__ . '/config/services.php';

function configuracionStripe(): array
{
    return StripeConfig::toArray();
}

function stripeConfigurado(array $configuracion): bool
{
    return str_starts_with((string) ($configuracion['publishable_key'] ?? ''), 'pk_')
        && str_starts_with((string) ($configuracion['secret_key'] ?? ''), 'sk_')
        && str_starts_with((string) ($configuracion['webhook_secret'] ?? ''), 'whsec_')
        && preg_match('/^[a-z]{3}$/', (string) ($configuracion['currency'] ?? '')) === 1;
}
