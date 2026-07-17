<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/google_oauth.php';
require_once dirname(__DIR__, 2) . '/includes/cuenta.php';

function falloCallbackGoogle(string $mensaje, string $accion = 'login'): never
{
    if ($accion === 'vincular') {
        cuentaFlash(['general' => $mensaje]);
        header('Location: ' . rutaPanelPorRol((string)($_SESSION['usuario_rol'] ?? '')));
    } else {
        $_SESSION['mensaje_auth'] = $mensaje;
        $_SESSION['mensaje_auth_tipo'] = 'danger';
        header('Location: ' . atenea_url($accion === 'registro' ? 'src/login/sign-up.php' : 'src/login/sign-in.php'));
    }
    exit;
}

$state = (string) ($_GET['state'] ?? '');
$estados = is_array($_SESSION['google_oauth_states'] ?? null) ? $_SESSION['google_oauth_states'] : [];
$datosEstado = $state !== '' && isset($estados[$state]) && is_array($estados[$state]) ? $estados[$state] : null;
if ($state !== '') unset($estados[$state]);
$_SESSION['google_oauth_states'] = $estados;
$accionEstado = is_array($datosEstado) ? (string) ($datosEstado['accion'] ?? 'login') : 'login';
$accion = in_array($accionEstado, ['login', 'registro', 'vincular'], true) ? $accionEstado : 'login';
$retornoVinculacion = is_array($datosEstado) ? cuentaRetornoSeguro($datosEstado['retorno'] ?? null) : rutaPanelPorRol((string)($_SESSION['usuario_rol'] ?? ''));

if (!is_array($datosEstado)
    || !isset($datosEstado['valor'])
    || !is_string($datosEstado['valor'])
    || !hash_equals($datosEstado['valor'], $state)
    || (int) ($datosEstado['expira'] ?? 0) < time()
    || !is_string($datosEstado['nonce'] ?? null)
    || !is_string($datosEstado['code_verifier'] ?? null)) {
    registrarAuditoria(['event_type'=>'auth.google_state_failed','module'=>'auth','action'=>'google_callback','result'=>'blocked','description'=>'Se bloqueo un callback de Google por state invalido o vencido.']);
    falloCallbackGoogle('La verificación de seguridad de Google expiró o no es válida.', $accion);
}
if (isset($_GET['error'])) {
    $errorGoogle = (string) $_GET['error'];
    registrarAuditoria([
        'actor_user_id'=>isset($_SESSION['usuario_id'])?(int)$_SESSION['usuario_id']:null,
        'event_type'=>$errorGoogle === 'access_denied' ? 'auth.google_access_denied' : 'auth.google_failed',
        'module'=>'auth','action'=>'google_callback','result'=>'failure',
        'description'=>$errorGoogle === 'access_denied' ? 'El usuario cancelo la autorizacion de Google.' : 'Google devolvio un error de autorizacion.',
        'metadata'=>['oauth_error'=>$errorGoogle],
    ]);
    falloCallbackGoogle($errorGoogle === 'access_denied'
        ? 'Cancelaste el acceso con Google. Puedes intentarlo nuevamente cuando quieras.'
        : 'Google no pudo autorizar el acceso. Intenta nuevamente.', $accion);
}
$codigo = (string) ($_GET['code'] ?? '');
if ($codigo === '' || strlen($codigo) > 4096) falloCallbackGoogle('Google no devolvió un código de autorización válido.', $accion);

try {
    $configuracion = obtenerConfiguracionGoogle();
    if (!googleDisponible($configuracion)) throw new RuntimeException('Configuración incompleta.');
    $perfil = obtenerPerfilGoogle($codigo, $configuracion, $datosEstado['nonce'], $datosEstado['code_verifier']);
    if ($accion === 'vincular') {
        exigirAutenticacion();
        $actual = obtenerPerfilUsuario((int)$_SESSION['usuario_id']);
        if (!$actual || strtolower((string)$actual['correo']) !== strtolower((string)$perfil['correo'])) throw new RuntimeException('El correo de Google no coincide con la cuenta actual.');
        $pdo = obtenerConexion();
        $consulta = $pdo->prepare('SELECT id FROM usuarios WHERE google_id=:google AND id<>:id LIMIT 1');
        $consulta->execute(['google'=>$perfil['google_id'],'id'=>$actual['id']]);
        if ($consulta->fetch()) throw new RuntimeException('La identidad de Google ya pertenece a otra cuenta.');
        $verificacion = crearVerificacionCuenta($pdo,(int)$actual['id'],'vincular_google',['google_id'=>$perfil['google_id']],(string)$actual['correo']);
        cuentaFlash([], 'Enviamos un código a tu correo para confirmar la vinculación.', $verificacion);
        header('Location: ' . $retornoVinculacion);
        exit;
    }
    $usuario = autenticarConPerfilGoogle($perfil);
    iniciarSesionUsuario($usuario);
    redirigirPorRol((string) $usuario['rol']);
} catch (Throwable $e) {
    error_log('OAuth Google Atenea: ' . $e->getMessage());
    registrarAuditoria([
        'actor_user_id'=>isset($_SESSION['usuario_id'])?(int)$_SESSION['usuario_id']:null,
        'event_type'=>'auth.google_failed','module'=>'auth','action'=>'google_callback','result'=>'failure',
        'description'=>'No fue posible validar una autenticacion con Google.',
        'metadata'=>['error_class'=>get_class($e)],
    ]);
    falloCallbackGoogle('No fue posible iniciar sesión con Google. Intenta nuevamente.', $accion);
}
