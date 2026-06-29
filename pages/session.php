<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../includes/atenea_auth.php';

if (!function_exists('atenea_session_page_requires_auth')) {
    function atenea_session_page_requires_auth(): bool
    {
        $publicPages = [
            'homepage.php',
            'about.php',
            'educacion.php',
            'galeria.php',
            'noticias.php',
            'noticia_detalle.php',
            'productos.php',
            'producto_detalle.php',
            'carrito.php',
            'contacto.php',
            'checkout_success.php',
            'login.php',
            'registro.php',
            'recover_password.php',
            'recover_password1.php',
            'processlogin.php',
            'processregister.php',
            'process_google_login.php',
            'process_google_register.php',
        ];

        $currentPage = basename((string) ($_SERVER['PHP_SELF'] ?? ''));

        return !in_array($currentPage, $publicPages, true);
    }
}

atenea_handle_session_timeout([
    'redirect_on_expire' => atenea_session_page_requires_auth(),
    'redirect_url' => 'login.php?expired=1',
]);

function logged_in()
{
    return isset($_SESSION['MEMBER_ID']);
}

function confirm_logged_in()
{
    if (!logged_in()) {
        header('Location: login.php');
        exit;
    }
}
?>
