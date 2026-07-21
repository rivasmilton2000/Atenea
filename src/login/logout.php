<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/session.php';
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/audit.php';
require_once dirname(__DIR__, 2) . '/includes/auth_remember.php';

$motivo=(string)($_GET['motivo']??'');$retorno=(string)($_GET['retorno']??'');if(!function_exists('urlRetornoInternaSegura'))require_once dirname(__DIR__,2).'/includes/auth.php';if(!urlRetornoInternaSegura($retorno))$retorno='';

if (isset($_SESSION['usuario_id'])) {
    registrarAuditoria([
        'actor_user_id' => (int) $_SESSION['usuario_id'],
        'target_user_id' => (int) $_SESSION['usuario_id'],
        'event_type' => 'auth.logout',
        'module' => 'auth',
        'entity_type' => 'user',
        'entity_id' => (int) $_SESSION['usuario_id'],
        'action' => 'logout',
        'result' => 'success',
        'description' => 'Cierre de sesion exitoso.',
    ]);
}

revocarTokenRecuerdoActualAtenea();

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => 'Lax',
    ]);
}

session_destroy();
if(in_array($motivo,['inactividad','cuenta_eliminada'],true)){header('Location: '.atenea_url('src/login/sign-in.php?motivo='.$motivo.($retorno!==''?'&retorno='.rawurlencode($retorno):'')));exit;}
header('Location: ' . atenea_url('index.php'));
exit;
