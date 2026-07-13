<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

function configuracionCorreoAtenea(): array
{
    $privada = __DIR__ . '/config/mail.php';
    if (is_file($privada)) {
        $configuracion = require $privada;
        if (is_array($configuracion)) return $configuracion;
    }

    return [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'smtp_user' => entornoAtenea('GMAIL_SMTP_USER'),
        'smtp_app_password' => entornoAtenea('GMAIL_SMTP_APP_PASSWORD'),
        'recipient' => entornoAtenea('CONTACT_RECIPIENT'),
        'recaptcha_site_key' => entornoAtenea('RECAPTCHA_SITE_KEY'),
        'recaptcha_secret_key' => entornoAtenea('RECAPTCHA_SECRET_KEY'),
    ];
}

function configuracionSmtpCompleta(array $configuracion): bool
{
    return trim((string) ($configuracion['host'] ?? '')) !== ''
        && filter_var($configuracion['smtp_user'] ?? '', FILTER_VALIDATE_EMAIL) !== false
        && trim((string) ($configuracion['smtp_app_password'] ?? '')) !== '';
}

function configuracionContactoCompleta(array $configuracion): bool
{
    return filter_var($configuracion['smtp_user'] ?? '', FILTER_VALIDATE_EMAIL) !== false
        && trim((string) ($configuracion['smtp_app_password'] ?? '')) !== ''
        && filter_var($configuracion['recipient'] ?? '', FILTER_VALIDATE_EMAIL) !== false
        && trim((string) ($configuracion['recaptcha_site_key'] ?? '')) !== ''
        && trim((string) ($configuracion['recaptcha_secret_key'] ?? '')) !== '';
}
