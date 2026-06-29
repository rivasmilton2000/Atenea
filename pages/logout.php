<?php

require_once __DIR__ . '/../includes/atenea_auth.php';

session_start();

$fallback = 'homepage.php';
$requestedTarget = '';
$publicPages = [
    'homepage.php',
    'about.php',
    'educacion.php',
    'galeria.php',
    'noticias.php',
    'productos.php',
    'carrito.php',
    'contacto.php',
];

if (isset($_GET['redirect'])) {
    $redirectMode = trim((string) $_GET['redirect']);
    if ($redirectMode !== '' && $redirectMode !== 'public') {
        $requestedTarget = $redirectMode;
    }
}

if ($requestedTarget === '' && isset($_GET['next'])) {
    $requestedTarget = trim((string) $_GET['next']);
}

if ($requestedTarget === '' && !empty($_SERVER['HTTP_REFERER'])) {
    $refererPath = basename((string) parse_url((string) $_SERVER['HTTP_REFERER'], PHP_URL_PATH));
    if (in_array($refererPath, $publicPages, true)) {
        $requestedTarget = $refererPath;
    }
}

$target = atenea_normalize_internal_redirect($requestedTarget !== '' ? $requestedTarget : $fallback, $fallback);

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        (bool) $params['secure'],
        (bool) $params['httponly']
    );
}

session_destroy();

header('Location: ' . $target);
exit;
