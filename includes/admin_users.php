<?php
declare(strict_types=1);

require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/audit.php';
require_once __DIR__ . '/perfil_usuario.php';

function adminUsuarioPorId(int $id, bool $bloquear = false, ?PDO $pdo = null): ?array
{
    $pdo ??= obtenerConexion();
    $sql = 'SELECT u.id,u.nombre,u.apellido,u.correo,u.rol,u.estado,u.proveedor,u.google_id,u.email_verificado,u.foto,u.fecha_nacimiento,u.dui,u.codigo_telefono,u.telefono,u.departamento_id,u.municipio_id,u.distrito_id,u.direccion,u.ultimo_acceso,u.last_activity_at,u.session_version,u.created_at,u.updated_at,u.deleted_at,u.deleted_by,u.deletion_reason,u.deletion_scheduled_at,u.anonymized_at,u.retention_hold,u.under_investigation,d.nombre departamento,m.nombre municipio,di.nombre distrito FROM usuarios u LEFT JOIN departamentos d ON d.id=u.departamento_id LEFT JOIN municipios m ON m.id=u.municipio_id LEFT JOIN distritos di ON di.id=u.distrito_id WHERE u.id=:id LIMIT 1' . ($bloquear ? ' FOR UPDATE' : '');
    $q = $pdo->prepare($sql);
    $q->execute(['id' => $id]);
    $usuario = $q->fetch();
    return is_array($usuario) ? $usuario : null;
}

function duiEnmascarado(?string $dui): string
{
    $normalizado = normalizarDui($dui);
    if (!$normalizado) return 'No registrado';
    return '****-****' . substr($normalizado, -1);
}

function reautenticacionAdminValida(?string $password, bool $permitirReciente = true): bool
{
    $marca = (int) ($_SESSION['admin_reauthenticated_at'] ?? 0);
    if ($permitirReciente && $marca >= time() - 600) return true;
    if (!is_string($password) || $password === '') return false;
    $id = (int) ($_SESSION['usuario_id'] ?? 0);
    $q = obtenerConexion()->prepare("SELECT password FROM usuarios WHERE id=:id AND rol='admin' AND estado='activo' AND deleted_at IS NULL LIMIT 1");
    $q->execute(['id' => $id]);
    $hash = $q->fetchColumn();
    if (!is_string($hash) || !password_verify($password, $hash)) return false;
    $_SESSION['admin_reauthenticated_at'] = time();
    return true;
}

function cantidadAdministradoresActivos(?PDO $pdo = null): int
{
    $pdo ??= obtenerConexion();
    return (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='admin' AND estado='activo' AND deleted_at IS NULL")->fetchColumn();
}

function validarPasswordRobustaAtenea(string $password): ?string
{
    if (strlen($password) < 12) return 'La contrasena debe tener al menos 12 caracteres.';
    if (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
        return 'Combina mayusculas, minusculas, numeros y simbolos.';
    }
    return null;
}

