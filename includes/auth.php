<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/perfil_usuario.php';
require_once __DIR__ . '/auth_remember.php';

const ATENEA_SESSION_WARNING_SECONDS = 300;
const ATENEA_SESSION_TIMEOUT_SECONDS = 600;

function limpiarAutenticacionSesionAtenea(): void
{
    unset($_SESSION['usuario_id'],$_SESSION['usuario_nombre'],$_SESSION['usuario_apellido'],$_SESSION['usuario_correo'],$_SESSION['usuario_rol'],$_SESSION['usuario_foto'],$_SESSION['usuario_perfil_completo'],$_SESSION['usuario_session_version'],$_SESSION['usuario_inicio_sesion'],$_SESSION['usuario_ultima_actividad'],$_SESSION['usuario_actividad_persistida']);
}

function solicitudConActividadHumanaAtenea(): bool
{
    if(strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH']??''))==='xmlhttprequest')return false;
    $destino=strtolower((string)($_SERVER['HTTP_SEC_FETCH_DEST']??''));if($destino!=='')return $destino==='document';
    return str_contains(strtolower((string)($_SERVER['HTTP_ACCEPT']??'')),'text/html');
}

function registrarActividadSesionAtenea(bool $forzar=false): void
{
    if(empty($_SESSION['usuario_id']))return;$ahora=time();$ultima=(int)($_SESSION['usuario_ultima_actividad']??0);
    if(!$forzar&&!solicitudConActividadHumanaAtenea())return;
    $_SESSION['usuario_ultima_actividad']=$ahora;
    if($forzar||$ahora-(int)($_SESSION['usuario_actividad_persistida']??0)>=60){
        try{obtenerConexion()->prepare('UPDATE usuarios SET last_activity_at=NOW() WHERE id=:id')->execute(['id'=>(int)$_SESSION['usuario_id']]);$_SESSION['usuario_actividad_persistida']=$ahora;}catch(Throwable $e){error_log('Actividad de sesión Atenea: '.$e->getMessage());}
    }
}

function usuarioAutenticado(): bool
{
    $estructuraValida = isset($_SESSION['usuario_id'], $_SESSION['usuario_rol'], $_SESSION['usuario_session_version'])
        && is_int($_SESSION['usuario_id'])
        && in_array($_SESSION['usuario_rol'], ['admin', 'usuario', 'docente'], true);
    if(!$estructuraValida&&!isset($_SESSION['recordarme_intentado'])){
        $_SESSION['recordarme_intentado']=true;
        try{$recordado=usuarioDesdeTokenRecuerdoAtenea(obtenerConexion());if($recordado){iniciarSesionUsuario($recordado);$estructuraValida=true;$_SESSION['mensaje_auth']='Tu acceso se restauró de forma segura.';$_SESSION['mensaje_auth_tipo']='info';}}catch(Throwable $e){error_log('Acceso recordado Atenea: '.$e->getMessage());borrarCookieRecuerdoAtenea();}
    }
    if (!$estructuraValida) return false;

    $ultima=(int)($_SESSION['usuario_ultima_actividad']??$_SESSION['usuario_inicio_sesion']??time());
    if(time()-$ultima>=ATENEA_SESSION_TIMEOUT_SECONDS){
        $uri=(string)($_SERVER['REQUEST_URI']??'');if(($_SERVER['REQUEST_METHOD']??'GET')==='GET'&&urlRetornoInternaSegura($uri))$_SESSION['url_retorno']=mb_substr($uri,0,500);
        revocarTokenRecuerdoActualAtenea();limpiarAutenticacionSesionAtenea();$_SESSION['mensaje_auth']='Tu sesión se cerró después de 10 minutos sin actividad.';$_SESSION['mensaje_auth_tipo']='warning';return false;
    }

    static $validacion = null;
    if ($validacion !== null) return $validacion;
    try {
        $consulta = obtenerConexion()->prepare('SELECT estado,rol,session_version,deleted_at FROM usuarios WHERE id=:id LIMIT 1');
        $consulta->execute(['id' => $_SESSION['usuario_id']]);
        $estado = $consulta->fetch();
        $validacion = is_array($estado)
            && ($estado['estado'] ?? '') === 'activo'
            && empty($estado['deleted_at'])
            && hash_equals((string) ($estado['rol'] ?? ''), (string) $_SESSION['usuario_rol'])
            && (int) $estado['session_version'] === (int) $_SESSION['usuario_session_version'];
    } catch (Throwable $e) {
        error_log('Validación de sesión Atenea: ' . $e->getMessage());
        $validacion = false;
    }
    if (!$validacion) {
        limpiarAutenticacionSesionAtenea();
    } else {
        registrarActividadSesionAtenea();
    }
    return $validacion;
}

function obtenerUsuarioActual(): ?array
{
    if (!usuarioAutenticado()) {
        return null;
    }

    return [
        'id' => $_SESSION['usuario_id'],
        'nombre' => (string) ($_SESSION['usuario_nombre'] ?? ''),
        'apellido' => (string) ($_SESSION['usuario_apellido'] ?? ''),
        'correo' => (string) ($_SESSION['usuario_correo'] ?? ''),
        'rol' => (string) $_SESSION['usuario_rol'],
        'foto' => $_SESSION['usuario_foto'] ?? null,
    ];
}

function iniciarSesionUsuario(array $usuario): void
{
    session_regenerate_id(true);
    unset($_SESSION['login_intentos'], $_SESSION['login_correo'], $_SESSION['csrf_token']);
    $_SESSION['usuario_id'] = (int) $usuario['id'];
    $_SESSION['usuario_nombre'] = (string) $usuario['nombre'];
    $_SESSION['usuario_apellido'] = (string) ($usuario['apellido'] ?? '');
    $_SESSION['usuario_correo'] = (string) $usuario['correo'];
    $_SESSION['usuario_rol'] = (string) $usuario['rol'];
    $_SESSION['usuario_foto'] = !empty($usuario['foto']) ? (string) $usuario['foto'] : null;
    $_SESSION['usuario_session_version'] = (int) ($usuario['session_version'] ?? 1);
    $_SESSION['usuario_perfil_completo'] = datosPerfilCompletos($usuario);
    $_SESSION['usuario_inicio_sesion'] = time();
    $_SESSION['usuario_ultima_actividad'] = time();
    $_SESSION['usuario_actividad_persistida'] = time();
    unset($_SESSION['recordarme_intentado']);
}

function rutaPanelPorRol(string $rol): string
{
    return match ($rol) {
        'admin' => atenea_url('src/dashboard/index.php'),
        'usuario' => !($_SESSION['usuario_perfil_completo'] ?? false)
            ? atenea_url('src/estudiantes/perfil.php?completar=1')
            : atenea_url('src/estudiantes/index.php'),
        'docente' => atenea_url('src/docente/index.php'),
        default => atenea_url('src/login/sign-in.php'),
    };
}

function exigirPerfilCompleto(): void
{
    exigirRol(['usuario']);
    if (!($_SESSION['usuario_perfil_completo'] ?? false)) {
        header('Location: ' . atenea_url('src/estudiantes/perfil.php?completar=1'));
        exit;
    }
}

function redirigirPorRol(?string $rol = null): never
{
    $rol ??= (string) ($_SESSION['usuario_rol'] ?? '');
    $retorno = (string) ($_SESSION['url_retorno'] ?? '');
    unset($_SESSION['url_retorno']);
    if (urlRetornoInternaSegura($retorno) && !($rol === 'usuario' && !($_SESSION['usuario_perfil_completo'] ?? false))) {
        header('Location: ' . $retorno);
        exit;
    }
    header('Location: ' . rutaPanelPorRol($rol));
    exit;
}

function urlRetornoInternaSegura(string $url): bool
{
    if ($url === '' || str_contains($url, "\\") || preg_match('/[\x00-\x1F\x7F]/', $url)) return false;
    $rutaBase = ATENEA_BASE_URL === '' ? '/' : ATENEA_BASE_URL . '/';
    return str_starts_with($url, $rutaBase)
        && !str_starts_with(substr($url, strlen(ATENEA_BASE_URL)), '//')
        && parse_url($url, PHP_URL_HOST) === null
        && parse_url($url, PHP_URL_SCHEME) === null;
}

function exigirAutenticacion(): void
{
    if (usuarioAutenticado()) {
        return;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        if ($uri !== '' && str_starts_with($uri, ATENEA_BASE_URL . '/')) {
            $_SESSION['url_retorno'] = substr($uri, 0, 500);
        }
    }

    if(empty($_SESSION['mensaje_auth']))$_SESSION['mensaje_auth'] = 'Debes iniciar sesión para acceder a esa página.';
    header('Location: ' . atenea_url('src/login/sign-in.php'));
    exit;
}

function exigirRol(array $roles): void
{
    exigirAutenticacion();
    $permitidos = array_values(array_intersect($roles, ['admin', 'usuario', 'docente']));

    if (!in_array((string) $_SESSION['usuario_rol'], $permitidos, true)) {
        registrarFalloGlobalAtenea('Intento de acceso a una ruta no autorizada.', 403);
        mostrarPaginaErrorAtenea(403);
    }
}

function renderizarControlSesionAtenea(): void
{
    if(!usuarioAutenticado())return;$transcurrido=max(0,time()-(int)($_SESSION['usuario_ultima_actividad']??time()));$uri=(string)($_SERVER['REQUEST_URI']??rutaPanelPorRol((string)$_SESSION['usuario_rol']));if(!urlRetornoInternaSegura($uri))$uri=rutaPanelPorRol((string)$_SESSION['usuario_rol']);
    $salida=atenea_url('src/login/logout.php?motivo=inactividad&retorno='.rawurlencode($uri));
    ?><div data-atenea-session data-endpoint="<?=atenea_e(atenea_url('src/auth/session-activity.php'))?>" data-logout="<?=atenea_e($salida)?>" data-csrf="<?=atenea_e(obtenerTokenCsrf())?>" data-warning="<?=ATENEA_SESSION_WARNING_SECONDS?>" data-timeout="<?=ATENEA_SESSION_TIMEOUT_SECONDS?>" data-elapsed="<?=$transcurrido?>"><aside class="atenea-session-warning" data-session-warning hidden role="alert" aria-live="assertive"><div class="atenea-session-warning__top"><span class="atenea-session-warning__icon" aria-hidden="true">⏱</span><div><h2>Tu sesión está por cerrarse</h2><p>Detectamos 5 minutos sin actividad. Por seguridad, la sesión se cerrará al llegar a 10 minutos.</p></div></div><div class="atenea-session-warning__actions"><span class="atenea-session-warning__count" data-session-count>5:00</span><button class="btn-continue" type="button" data-session-continue>Continuar sesión</button></div></aside></div><?php
}

function generarNombreUsuarioDisponible(PDO $pdo, string $correo, string $nombre = ''): string
{
    $base = trim($nombre) !== '' ? trim($nombre) : (string) strstr($correo, '@', true);
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base);
    $base = strtolower((string) ($ascii === false ? $base : $ascii));
    $base = trim((string) preg_replace('/[^a-z0-9._-]+/', '.', $base), '.-_');
    if ($base === '') $base = 'usuario';
    $base = mb_substr($base, 0, 65);

    $consulta = $pdo->prepare('SELECT 1 FROM usuarios WHERE nombre_usuario=:nombre_usuario LIMIT 1');
    for ($intento = 0; $intento < 100; $intento++) {
        $sufijo = $intento === 0 ? '' : '-' . ($intento + 1);
        $candidato = mb_substr($base, 0, 80 - strlen($sufijo)) . $sufijo;
        $consulta->execute(['nombre_usuario' => $candidato]);
        if (!$consulta->fetchColumn()) return $candidato;
    }
    return 'usuario-' . bin2hex(random_bytes(6));
}
