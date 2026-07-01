<?php
require '../includes/connection.php';
require 'session.php';
require_once '../includes/atenea_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['btnlogin'])) {
    header('Location: login.php');
    exit;
}

$username = trim((string) ($_POST['user'] ?? ''));
$password = trim((string) ($_POST['password'] ?? ''));
$requestedRedirect = trim((string) ($_POST['redirect'] ?? ($_SESSION['ATENEA_LOGIN_REDIRECT'] ?? '')));
$messageCode = trim((string) ($_POST['msg'] ?? ''));
$loginReturnUrl = $requestedRedirect !== ''
    ? atenea_build_login_url($requestedRedirect, $messageCode)
    : 'login.php';

if ($username === '' || $password === '') {
    atenea_render_auth_alert(
        'warning',
        'Completa tu acceso',
        'Ingresa tu usuario y tu contrasena para continuar.',
        $loginReturnUrl
    );
}

$user = atenea_fetch_user_by_credentials($db, $username, sha1($password));

if (!$user) {
    atenea_render_auth_alert(
        'error',
        'Acceso denegado',
        'Credenciales incorrectas o cuenta inactiva. Si el problema continua, comunicate con administracion.',
        $loginReturnUrl
    );
}

session_regenerate_id(true);
atenea_apply_session_data($user, 'password');

$defaultRedirect = atenea_dashboard_route_for_user($user);
$redirect = $requestedRedirect !== ''
    ? atenea_resolve_login_redirect($requestedRedirect, $defaultRedirect)
    : $defaultRedirect;

unset($_SESSION['ATENEA_LOGIN_REDIRECT']);
$welcomeName = atenea_user_display_name($user, true);

atenea_render_auth_alert(
    'success',
    'Bienvenido a Atenea',
    'Hola ' . $welcomeName . ', tu sesion esta lista.',
    $redirect
);
