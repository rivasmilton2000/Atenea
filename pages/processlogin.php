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

if ($username === '' || $password === '') {
    atenea_render_auth_alert(
        'warning',
        'Completa tu acceso',
        'Ingresa tu usuario y tu contrasena para continuar.',
        'login.php'
    );
}

$user = atenea_fetch_user_by_credentials($db, $username, sha1($password));

if (!$user) {
    atenea_render_auth_alert(
        'error',
        'Acceso denegado',
        'Credenciales incorrectas o cuenta inactiva. Si el problema continua, comunicate con administracion.',
        'login.php'
    );
}

session_regenerate_id(true);
atenea_apply_session_data($user, 'password');

$redirect = atenea_dashboard_route_for_user($user);
$welcomeName = atenea_user_display_name($user, true);

atenea_render_auth_alert(
    'success',
    'Bienvenido a Atenea',
    'Hola ' . $welcomeName . ', tu sesion esta lista.',
    $redirect
);
