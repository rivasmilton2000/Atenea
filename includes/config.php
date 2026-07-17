<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';
cargarEntornoAtenea();

date_default_timezone_set('America/El_Salvador');

if (!defined('ATENEA_ROOT')) {
    define('ATENEA_ROOT', dirname(__DIR__));
}

if (!defined('ATENEA_BASE_URL')) {
    $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath((string) $_SERVER['DOCUMENT_ROOT']) : false;
    $projectRoot = realpath(ATENEA_ROOT);
    $baseUrl = '/Atenea';

    if ($documentRoot && $projectRoot) {
        $normalizedDocumentRoot = str_replace('\\', '/', $documentRoot);
        $normalizedProjectRoot = str_replace('\\', '/', $projectRoot);

        if (str_starts_with(strtolower($normalizedProjectRoot), strtolower($normalizedDocumentRoot))) {
            $baseUrl = substr($normalizedProjectRoot, strlen($normalizedDocumentRoot));
        }
    }

    define('ATENEA_BASE_URL', rtrim('/' . ltrim($baseUrl, '/'), '/'));
}

function atenea_url(string $path = ''): string
{
    return ATENEA_BASE_URL . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function atenea_app_url_configurada(): string
{
    $environment = strtolower(entornoAtenea('APP_ENV', entornoAtenea('ATENEA_ENV', 'production')));
    $local = in_array($environment, ['dev', 'development', 'local'], true);
    $specific = entornoAtenea($local ? 'APP_URL_LOCAL' : 'APP_URL_PRODUCTION');
    $legacy = entornoAtenea('APP_URL', entornoAtenea('ATENEA_APP_URL'));
    return rtrim($specific !== '' ? $specific : $legacy, '/');
}

function atenea_url_absoluta(string $path = ''): string
{
    $baseConfigurada = atenea_app_url_configurada();
    if ($baseConfigurada !== '') {
        return $baseConfigurada . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    if (!preg_match('/^[a-z0-9.-]+(?::\d{1,5})?$/i', $host)) {
        $host = 'localhost';
    }

    return ($https ? 'https' : 'http') . '://' . $host . atenea_url($path);
}

function atenea_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
