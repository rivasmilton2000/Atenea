<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/google_oauth.php';
require_once dirname(__DIR__, 2) . '/includes/cuenta.php';
require_once dirname(__DIR__, 2) . '/includes/carrito.php';
require_once dirname(__DIR__, 2) . '/includes/errores_sistema.php';

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
    registrarIntentoGoogleFallidoAtenea(['event_type'=>'auth.google_state_failed','module'=>'auth','action'=>'google_callback','result'=>'blocked','description'=>'Se bloqueó un callback de Google por estado inválido o vencido.']);
    falloCallbackGoogle('La verificación de seguridad de Google expiró o no es válida.', $accion);
}
if (isset($_GET['error'])) {
    $errorGoogle = (string) $_GET['error'];
    registrarIntentoGoogleFallidoAtenea([
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
    $usuario = autenticarConPerfilGoogle($perfil,$accion);
    unset($_SESSION['google_intentos_fallidos']);
    iniciarSesionUsuario($usuario);
    if ((string)$usuario['rol'] === 'usuario') try {
        sincronizarCarritoInvitadoAtenea(obtenerConexion(), (int)$usuario['id']);
    } catch (Throwable $syncError) {
        error_log('Sincronización de carrito Atenea: ' . $syncError->getMessage());
    }
    if($accion==='registro')header('Location: '.atenea_url('src/estudiantes/perfil.php?completar=1'));else redirigirPorRol((string) $usuario['rol']);
    exit;
} catch (GoogleCuentaNoVinculadaException|GoogleCuentaRequiereVinculacionException $e) {
    $_SESSION['google_vinculacion_pendiente']=['google_id'=>$perfil['google_id']??'','correo'=>$perfil['correo']??'','expira'=>time()+600];
    $_SESSION['google_opciones_vinculacion']=true;
    registrarIntentoGoogleFallidoAtenea(['event_type'=>'auth.google_unlinked','module'=>'auth','action'=>'google_callback','result'=>'failure','description'=>'Se intentó acceder con una identidad de Google no vinculada.']);
    falloCallbackGoogle('No existe una cuenta vinculada con esta identidad de Google. Puedes registrarte con Google o iniciar sesión con tu contraseña para vincularla.','login');
} catch (GoogleCuentaYaVinculadaException $e) {
    registrarIntentoGoogleFallidoAtenea(['event_type'=>'auth.google_registration_existing','module'=>'auth','action'=>'google_callback','result'=>'failure','description'=>'Se intentó registrar una identidad de Google ya vinculada.']);
    falloCallbackGoogle('Esta identidad de Google ya está vinculada. Utiliza la opción de iniciar sesión con Google.','login');
} catch (GoogleRegistroBloqueadoException $e) {
    registrarIntentoGoogleFallidoAtenea(['event_type'=>'auth.google_registration_blocked','module'=>'auth','action'=>'google_callback','result'=>'blocked','description'=>'Se bloqueó un registro con Google durante el periodo de conservación.']);
    falloCallbackGoogle('No es posible registrar una cuenta con estos datos en este momento.','registro');
} catch (Throwable $e) {
    error_log('OAuth Google Atenea: ' . $e->getMessage());
    try { registrarErrorSistemaAtenea('sistema','google_oauth','Fallo de integración con Google.',['usuario_id'=>isset($_SESSION['usuario_id'])?(int)$_SESSION['usuario_id']:null],'error'); } catch (Throwable) {}
    registrarIntentoGoogleFallidoAtenea([
        'actor_user_id'=>isset($_SESSION['usuario_id'])?(int)$_SESSION['usuario_id']:null,
        'event_type'=>'auth.google_failed','module'=>'auth','action'=>'google_callback','result'=>'failure',
        'description'=>'No fue posible validar una autenticacion con Google.',
        'metadata'=>['error_class'=>get_class($e)],
    ]);
    falloCallbackGoogle('No fue posible iniciar sesión con Google. Intenta nuevamente.', $accion);
}
