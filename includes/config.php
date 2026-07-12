<?php
declare(strict_types=1);

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

function atenea_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
