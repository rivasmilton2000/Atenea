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
            'users.delete_admin',
            'audit.view',
            'payments.view_summary',
            'orders.view',
            'orders.manage',
            'dte.configure',
        ],
        'docente' => [],
        'usuario' => [],
    ];

    return $mapa[$rol] ?? [];
}

function usuarioTienePermiso(string $permiso, ?array $usuario = null): bool
{
    $usuario ??= obtenerUsuarioActual();
    if ($usuario === null || !in_array($permiso, permisosPorRolAtenea((string) ($usuario['rol'] ?? '')), true)) return false;
    if ($permiso !== 'users.delete_admin') return true;
    $consulta = obtenerConexion()->prepare("SELECT 1 FROM usuarios WHERE id=:id AND rol='admin' AND es_superadmin=1 AND estado='activo' AND deleted_at IS NULL");
    $consulta->execute(['id'=>(int)$usuario['id']]);
    return (bool)$consulta->fetchColumn();
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
