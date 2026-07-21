<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

/**
 * Permisos administrativos disponibles. Las acciones especialmente sensibles
 * vuelven a comprobar la marca es_superadmin además del rol administrativo.
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
            'notifications.view',
            'communications.view',
            'communications.reply',
            'mail.manage',
            'system_errors.manage',
            'appearance.manage',
            'backups.view',
            'backups.manage',
            'backups.restore',
            'dte.manage',
            'academic.supervise',
            'academic.courses.view',
            'academic.students.view',
            'academic.content.manage',
            'academic.evaluations.manage',
            'academic.grades.manage',
            'academic.communications.send',
        ],
        'docente' => [
            'academic.courses.view',
            'academic.students.view',
            'academic.content.manage',
            'academic.evaluations.manage',
            'academic.grades.manage',
            'academic.communications.send',
        ],
        'usuario' => [],
    ];

    return $mapa[$rol] ?? [];
}

function usuarioTienePermiso(string $permiso, ?array $usuario = null): bool
{
    $usuario ??= obtenerUsuarioActual();
    if ($usuario === null || !in_array($permiso, permisosPorRolAtenea((string) ($usuario['rol'] ?? '')), true)) return false;
    if (!in_array($permiso, ['users.delete_admin','backups.restore'], true)) return true;
    $consulta = obtenerConexion()->prepare("SELECT 1 FROM usuarios WHERE id=:id AND rol='admin' AND es_superadmin=1 AND estado='activo' AND deleted_at IS NULL");
    $consulta->execute(['id'=>(int)$usuario['id']]);
    return (bool)$consulta->fetchColumn();
}

function exigirPermiso(string $permiso): void
{
    exigirAutenticacion();
    if (!usuarioTienePermiso($permiso)) {
        registrarFalloGlobalAtenea('Permiso denegado: ' . $permiso, 403);
        mostrarPaginaErrorAtenea(403);
    }
}

function rolesAdministrablesAtenea(): array
{
    return ['usuario', 'docente', 'admin'];
}
