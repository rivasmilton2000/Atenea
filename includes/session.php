<?php
declare(strict_types=1);

function iniciarSesionSegura(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('ATENEA_SESSION');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function obtenerTokenCsrf(): string
{
    iniciarSesionSegura();

    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validarTokenCsrf(?string $token): bool
{
    iniciarSesionSegura();
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function exigirTokenCsrf(?string $token): void
{
    if (validarTokenCsrf($token)) return;
    require_once __DIR__ . '/config.php';
    registrarFalloGlobalAtenea('Token CSRF ausente o vencido.', 419);
    mostrarPaginaErrorAtenea(419);
}

iniciarSesionSegura();
