<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';

function normalizarDui(?string $dui): ?string
{
    $digitos = preg_replace('/\D+/', '', (string) $dui);
    return $digitos === '' ? null : (strlen($digitos) === 9 ? substr($digitos, 0, 8) . '-' . $digitos[8] : '');
}

function fechaNacimientoValida(?string $fecha): bool
{
    if (!is_string($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        return false;
    }

    $valor = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha, new DateTimeZone('America/El_Salvador'));
    return $valor !== false && $valor->format('Y-m-d') === $fecha && $valor <= new DateTimeImmutable('today');
}

function normalizarCodigoTelefono(?string $codigo): string
{
    $digitos = preg_replace('/\D+/', '', (string) $codigo);
    return $digitos === '' ? '' : '+' . substr($digitos, 0, 4);
}

function normalizarTelefono(?string $telefono): string
{
    return substr((string) preg_replace('/\D+/', '', (string) $telefono), 0, 15);
}

function telefonoValido(string $codigo, string $telefono): bool
{
    if (!preg_match('/^\+[1-9]\d{0,3}$/', $codigo) || !preg_match('/^\d{7,15}$/', $telefono)) {
        return false;
    }

    return $codigo !== '+503' || preg_match('/^[267]\d{7}$/', $telefono) === 1;
}

function ubicacionValida(PDO $pdo, int $departamentoId, int $municipioId, int $distritoId): bool
{
    $consulta = $pdo->prepare(
        'SELECT COUNT(*) FROM distritos d
         INNER JOIN municipios m ON m.id = d.municipio_id
         WHERE d.id = :distrito AND m.id = :municipio AND m.departamento_id = :departamento'
    );
    $consulta->execute([
        'distrito' => $distritoId,
        'municipio' => $municipioId,
        'departamento' => $departamentoId,
    ]);
    return (int) $consulta->fetchColumn() === 1;
}

function datosPerfilCompletos(array $usuario): bool
{
    return fechaNacimientoValida(isset($usuario['fecha_nacimiento']) ? (string) $usuario['fecha_nacimiento'] : null)
        && normalizarDui(isset($usuario['dui']) ? (string) $usuario['dui'] : null) !== null
        && normalizarDui(isset($usuario['dui']) ? (string) $usuario['dui'] : null) !== ''
        && telefonoValido(
            normalizarCodigoTelefono(isset($usuario['codigo_telefono']) ? (string) $usuario['codigo_telefono'] : null),
            normalizarTelefono(isset($usuario['telefono']) ? (string) $usuario['telefono'] : null)
        )
        && (int) ($usuario['departamento_id'] ?? 0) > 0
        && (int) ($usuario['municipio_id'] ?? 0) > 0
        && (int) ($usuario['distrito_id'] ?? 0) > 0;
}

function obtenerPerfilUsuario(int $usuarioId): ?array
{
    $consulta = obtenerConexion()->prepare(
        'SELECT u.id,u.nombre,u.apellido,u.correo,u.password,u.rol,u.foto,u.proveedor,u.google_id,
                u.email_verificado,u.fecha_nacimiento,u.dui,u.codigo_telefono,u.telefono,
                u.departamento_id,u.municipio_id,u.distrito_id,u.direccion,u.estado,u.ultimo_acceso,
                u.session_version,u.created_at,u.updated_at,d.nombre departamento,m.nombre municipio,
                di.nombre distrito
         FROM usuarios u
         LEFT JOIN departamentos d ON d.id=u.departamento_id
         LEFT JOIN municipios m ON m.id=u.municipio_id
         LEFT JOIN distritos di ON di.id=u.distrito_id
         WHERE u.id = :id AND u.estado = \'activo\' AND u.deleted_at IS NULL LIMIT 1'
    );
    $consulta->execute(['id' => $usuarioId]);
    $usuario = $consulta->fetch();
    return is_array($usuario) ? $usuario : null;
}

function etiquetaRol(string $rol): string
{
    return match ($rol) {
        'admin' => 'Administrador',
        'docente' => 'Docente',
        default => 'Estudiante',
    };
}

function rutaFotoPerfil(array $usuario): string
{
    $foto = trim((string) ($usuario['foto'] ?? ''));
    if ($foto !== '') {
        if (preg_match('#^https://#i', $foto)) return $foto;
        return atenea_url(ltrim($foto, '/'));
    }
    return atenea_url('src/estudiantes/assets/images/avatars/01.png');
}

function obtenerDepartamentos(): array
{
    return obtenerConexion()->query('SELECT id,nombre FROM departamentos ORDER BY nombre')->fetchAll();
}

