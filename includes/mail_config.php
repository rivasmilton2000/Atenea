<?php
declare(strict_types=1);

require_once __DIR__ . '/config/services.php';

function configuracionCorreoAtenea(): array
{
    return MailConfig::toArray();
}

function configuracionSmtpCompleta(array $configuracion): bool
{
    return trim((string) ($configuracion['host'] ?? '')) !== ''
        && (int) ($configuracion['port'] ?? 0) >= 1
        && trim((string) ($configuracion['smtp_user'] ?? '')) !== ''
        && trim((string) ($configuracion['smtp_app_password'] ?? '')) !== ''
        && in_array((string) ($configuracion['encryption'] ?? ''), ['tls', 'ssl', 'none'], true)
        && filter_var($configuracion['from_email'] ?? '', FILTER_VALIDATE_EMAIL) !== false
        && trim((string) ($configuracion['from_name'] ?? '')) !== '';
}

function configuracionContactoCompleta(array $configuracion): bool
{
    return configuracionSmtpCompleta($configuracion)
        && filter_var($configuracion['recipient'] ?? '', FILTER_VALIDATE_EMAIL) !== false
        && trim((string) ($configuracion['recaptcha_site_key'] ?? '')) !== ''
        && trim((string) ($configuracion['recaptcha_secret_key'] ?? '')) !== '';
}
