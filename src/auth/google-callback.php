<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/google_oauth.php';

function falloCallbackGoogle(string $mensaje, string $accion = 'login'): never
{
    if ($accion === 'vincular') {
        $_SESSION['perfil_mensaje'] = $mensaje;
        $_SESSION['perfil_mensaje_tipo'] = 'danger';
        header('Location: ' . atenea_url('src/estudiantes/perfil.php'));
    } else {
        $_SESSION['mensaje_auth'] = $mensaje;
        $_SESSION['mensaje_auth_tipo'] = 'danger';
        header('Location: ' . atenea_url('src/login/sign-in.php'));
    }
    exit;
}

$state = (string) ($_GET['state'] ?? '');
$estados = is_array($_SESSION['google_oauth_states'] ?? null) ? $_SESSION['google_oauth_states'] : [];
$datosEstado = $state !== '' && isset($estados[$state]) && is_array($estados[$state]) ? $estados[$state] : null;
if ($state !== '') unset($estados[$state]);
$_SESSION['google_oauth_states'] = $estados;
$accion = is_array($datosEstado) && ($datosEstado['accion'] ?? '') === 'vincular' ? 'vincular' : 'login';

if (!is_array($datosEstado)
    || !isset($datosEstado['valor'])
    || !is_string($datosEstado['valor'])
    || !hash_equals($datosEstado['valor'], $state)
    || (int) ($datosEstado['expira'] ?? 0) < time()) {
    falloCallbackGoogle('La verificación de seguridad de Google expiró o no es válida.', $accion);
}
if (isset($_GET['error'])) {
    falloCallbackGoogle('El acceso con Google fue cancelado o no pudo autorizarse.', $accion);
}
$codigo = (string) ($_GET['code'] ?? '');
if ($codigo === '' || strlen($codigo) > 4096) falloCallbackGoogle('Google no devolvió un código de autorización válido.', $accion);

try {
    $configuracion = obtenerConfiguracionGoogle();
    if (!googleDisponible($configuracion)) throw new RuntimeException('Configuración incompleta.');
    $perfil = obtenerPerfilGoogle($codigo, $configuracion);
    $usuario = autenticarConPerfilGoogle($perfil, $accion === 'vincular');
    iniciarSesionUsuario($usuario);
    if ($accion === 'vincular') {
        $_SESSION['perfil_mensaje'] = 'La cuenta de Google se vinculó correctamente.';
        $_SESSION['perfil_mensaje_tipo'] = 'success';
        header('Location: ' . atenea_url('src/estudiantes/perfil.php'));
        exit;
    }
    redirigirPorRol((string) $usuario['rol']);
} catch (Throwable $e) {
    error_log('OAuth Google Atenea: ' . $e->getMessage());
    falloCallbackGoogle('No fue posible iniciar sesión con Google. Intenta nuevamente.', $accion);
}
