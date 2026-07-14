<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/google_oauth.php';
require_once dirname(__DIR__, 2) . '/includes/cuenta.php';

function volverDesdeGoogle(string $mensaje, string $destino = 'src/login/sign-in.php'): never
{
    if ($destino === 'src/estudiantes/perfil.php') {
        $_SESSION['perfil_mensaje'] = $mensaje;
        $_SESSION['perfil_mensaje_tipo'] = 'danger';
    } else {
        $_SESSION['mensaje_auth'] = $mensaje;
        $_SESSION['mensaje_auth_tipo'] = 'danger';
    }
    header('Location: ' . atenea_url($destino));
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    volverDesdeGoogle('La solicitud de Google no es válida.');
}

$accion = ($_GET['accion'] ?? '') === 'vincular' ? 'vincular' : 'login';
if ($accion === 'vincular' && !usuarioAutenticado()) {
    volverDesdeGoogle('Inicia sesión antes de vincular una cuenta de Google.');
}
if ($accion === 'login' && usuarioAutenticado()) redirigirPorRol();

$configuracion = obtenerConfiguracionGoogle();
if (!googleDisponible($configuracion)) {
    volverDesdeGoogle('El acceso con Google no está disponible porque falta completar su configuración.', $accion === 'vincular' ? 'src/estudiantes/perfil.php' : 'src/login/sign-in.php');
}

$estados = is_array($_SESSION['google_oauth_states'] ?? null) ? $_SESSION['google_oauth_states'] : [];
$ahora = time();
$estados = array_filter($estados, static fn ($dato): bool => is_array($dato) && (int) ($dato['expira'] ?? 0) >= $ahora);
$state = bin2hex(random_bytes(32));
$estados[$state] = ['valor' => $state, 'expira' => $ahora + 600, 'accion' => $accion, 'retorno' => $accion === 'vincular' ? cuentaRetornoSeguro($_GET['retorno'] ?? null) : ''];
$_SESSION['google_oauth_states'] = array_slice($estados, -5, null, true);

$parametros = [
    'client_id' => (string) $configuracion['client_id'],
    'redirect_uri' => (string) $configuracion['redirect_uri'],
    'response_type' => 'code',
    'scope' => (string) ($configuracion['scopes'] ?? GoogleConfig::scopes()),
    'state' => $state,
    'prompt' => 'select_account',
    'include_granted_scopes' => 'true',
];

header('Cache-Control: no-store');
header('Location: ' . (string) ($configuracion['authorization_uri'] ?? GoogleConfig::authorizationUri()) . '?' . http_build_query($parametros, '', '&', PHP_QUERY_RFC3986));
exit;
