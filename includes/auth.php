<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';

function usuarioAutenticado(): bool
{
    return isset($_SESSION['usuario_id'], $_SESSION['usuario_rol'])
        && is_int($_SESSION['usuario_id'])
        && in_array($_SESSION['usuario_rol'], ['admin', 'usuario', 'docente'], true);
}

function obtenerUsuarioActual(): ?array
{
    if (!usuarioAutenticado()) {
        return null;
    }

    return [
        'id' => $_SESSION['usuario_id'],
        'nombre' => (string) ($_SESSION['usuario_nombre'] ?? ''),
        'correo' => (string) ($_SESSION['usuario_correo'] ?? ''),
        'rol' => (string) $_SESSION['usuario_rol'],
        'foto' => $_SESSION['usuario_foto'] ?? null,
    ];
}

function rutaPanelPorRol(string $rol): string
{
    return match ($rol) {
        'admin' => atenea_url('src/admin_auth/index.php'),
        'usuario' => atenea_url('src/estudiantes/index.php'),
        'docente' => atenea_url('src/docente/index.php'),
        default => atenea_url('login.php'),
    };
}

function redirigirPorRol(?string $rol = null): never
{
    $rol ??= (string) ($_SESSION['usuario_rol'] ?? '');
    header('Location: ' . rutaPanelPorRol($rol));
    exit;
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
    header('Location: ' . atenea_url('login.php'));
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
