<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/perfil_usuario.php';

function usuarioAutenticado(): bool
{
    $estructuraValida = isset($_SESSION['usuario_id'], $_SESSION['usuario_rol'], $_SESSION['usuario_session_version'])
        && is_int($_SESSION['usuario_id'])
        && in_array($_SESSION['usuario_rol'], ['admin', 'usuario', 'docente'], true);
    if (!$estructuraValida) return false;

    static $validacion = null;
    if ($validacion !== null) return $validacion;
    try {
        $consulta = obtenerConexion()->prepare('SELECT estado,session_version FROM usuarios WHERE id=:id LIMIT 1');
        $consulta->execute(['id' => $_SESSION['usuario_id']]);
        $estado = $consulta->fetch();
        $validacion = is_array($estado)
            && ($estado['estado'] ?? '') === 'activo'
            && (int) $estado['session_version'] === (int) $_SESSION['usuario_session_version'];
    } catch (Throwable $e) {
        error_log('Validación de sesión Atenea: ' . $e->getMessage());
        $validacion = false;
    }
    if (!$validacion) {
        unset($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], $_SESSION['usuario_apellido'], $_SESSION['usuario_correo'], $_SESSION['usuario_rol'], $_SESSION['usuario_foto'], $_SESSION['usuario_perfil_completo'], $_SESSION['usuario_session_version']);
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
    if ($rol === 'usuario' && urlRetornoInternaSegura($retorno)) {
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

    $_SESSION['mensaje_auth'] = 'Debes iniciar sesión para acceder a esa página.';
    header('Location: ' . atenea_url('src/login/sign-in.php'));
    exit;
}

function exigirRol(array $roles): void
{
    exigirAutenticacion();
    $permitidos = array_values(array_intersect($roles, ['admin', 'usuario', 'docente']));

    if (!in_array((string) $_SESSION['usuario_rol'], $permitidos, true)) {
        $_SESSION['mensaje_auth'] = 'No tienes permiso para acceder a esa sección.';
        redirigirPorRol();
    }
}
