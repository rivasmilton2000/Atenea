<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

/**
 * Permisos administrativos disponibles. El esquema actual no contiene SuperAdmin;
 * por ello el rol admin recibe los permisos sensibles y se protege al ultimo admin.
 */
function permisosPorRolAtenea(string $rol): array
{
    $mapa = [
        'admin' => [
            'users.view',
            'users.view_sensitive',
            'users.edit',
            'users.change_role',
            'users.send_notice',
            'users.start_password_recovery',
            'users.delete',
            'audit.view',
            'payments.view_summary',
        ],
        'docente' => [],
        'usuario' => [],
    ];

    return $mapa[$rol] ?? [];
}

function usuarioTienePermiso(string $permiso, ?array $usuario = null): bool
{
    $usuario ??= obtenerUsuarioActual();
    return $usuario !== null
        && in_array($permiso, permisosPorRolAtenea((string) ($usuario['rol'] ?? '')), true);
}

function exigirPermiso(string $permiso): void
{
    exigirAutenticacion();
    if (!usuarioTienePermiso($permiso)) {
        $_SESSION['mensaje_auth'] = 'No tienes permiso para realizar esa accion.';
        redirigirPorRol();
    }
}

function rolesAdministrablesAtenea(): array
{
    return ['usuario', 'docente', 'admin'];
}

