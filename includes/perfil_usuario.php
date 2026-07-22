<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/avatar.php';

function normalizarDui(?string $dui): ?string
{
    $digitos = preg_replace('/\D+/', '', (string) $dui);
    return $digitos === '' ? null : (strlen($digitos) === 9 ? substr($digitos, 0, 8) . '-' . $digitos[8] : '');
}

function duiValidoExacto(?string $dui): bool
{
    return is_string($dui) && preg_match('/^\d{8}-\d$/D', $dui) === 1;
}

function normalizarNombrePersona(?string $valor): string
{
    return trim((string) preg_replace('/\s+/u', ' ', (string) $valor));
}

function nombrePersonaValido(?string $valor): bool
{
    if (!is_string($valor) || $valor !== strip_tags($valor) || preg_match('/[\x00-\x1F\x7F]/u', $valor)) {
        return false;
    }

    $normalizado = normalizarNombrePersona($valor);
    $longitud = mb_strlen($normalizado);
    return $longitud >= 2
        && $longitud <= 60
        && preg_match("/^(?=.*\\p{L})[\\p{L}\\p{M} '\\x{2019}-]+$/u", $normalizado) === 1;
}

function fechaNacimientoValida(?string $fecha): bool
{
    if (!is_string($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        return false;
    }

    $zona = new DateTimeZone('America/El_Salvador');
    $valor = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha, $zona);
    $hoy = new DateTimeImmutable('today', $zona);
    return $valor !== false
        && $valor->format('Y-m-d') === $fecha
        && $valor >= $hoy->modify('-120 years')
        && $valor <= $hoy->modify('-18 years');
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

function normalizarTelefonoParaCodigo(string $codigo, ?string $telefono): string
{
    $digitos = normalizarTelefono($telefono);
    $prefijo = preg_replace('/\D+/', '', $codigo) ?: '';
    if ($prefijo !== '' && str_starts_with($digitos, $prefijo)) {
        $digitos = substr($digitos, strlen($prefijo));
    }
    return substr($digitos, 0, 15);
}

function telefonoValido(string $codigo, string $telefono): bool
{
    if (!preg_match('/^\+[1-9]\d{0,3}$/', $codigo)) {
        return false;
    }

    $longitudes = [
        '+503' => 8, '+502' => 8, '+504' => 8, '+505' => 8,
        '+506' => 8, '+507' => 8, '+52' => 10, '+1' => 10,
    ];
    $longitud = $longitudes[$codigo] ?? null;
    if ($longitud !== null && preg_match('/^\d{' . $longitud . '}$/D', $telefono) !== 1) {
        return false;
    }
    if ($longitud === null && preg_match('/^\d{7,15}$/D', $telefono) !== 1) {
        return false;
    }

    return $codigo !== '+503' || preg_match('/^[267]\d{7}$/D', $telefono) === 1;
}

function normalizarDireccionPerfil(?string $direccion): string
{
    return trim((string) preg_replace('/\s+/u', ' ', (string) $direccion));
}

function direccionPerfilValida(?string $direccion): bool
{
    if (!is_string($direccion) || $direccion !== strip_tags($direccion) || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $direccion)) {
        return false;
    }
    $normalizada = normalizarDireccionPerfil($direccion);
    $longitud = mb_strlen($normalizada);
    return $longitud >= 8 && $longitud <= 250 && preg_match('/[\p{L}\p{N}]/u', $normalizada) === 1;
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
    $dui = isset($usuario['dui']) ? (string) $usuario['dui'] : null;
    $codigo = normalizarCodigoTelefono(isset($usuario['codigo_telefono']) ? (string) $usuario['codigo_telefono'] : null);
    $datosBasicos=fechaNacimientoValida(isset($usuario['fecha_nacimiento']) ? (string) $usuario['fecha_nacimiento'] : null)
        && duiValidoExacto($dui)
        && telefonoValido(
            $codigo,
            normalizarTelefonoParaCodigo($codigo, isset($usuario['telefono']) ? (string) $usuario['telefono'] : null)
        )
        && (int) ($usuario['departamento_id'] ?? 0) > 0
        && (int) ($usuario['municipio_id'] ?? 0) > 0
        && (int) ($usuario['distrito_id'] ?? 0) > 0;
    if(($usuario['perfil_estado']??'completo')!=='pendiente')return $datosBasicos;
    return $datosBasicos
        && nombrePersonaValido((string)($usuario['nombre']??''))
        && nombrePersonaValido((string)($usuario['apellido']??''))
        && direccionPerfilValida((string)($usuario['direccion']??''))
        && !empty($usuario['terminos_aceptados_at']);
}

function obtenerPerfilUsuario(int $usuarioId): ?array
{
    $consulta = obtenerConexion()->prepare(
        'SELECT u.id,u.nombre,u.apellido,u.correo,u.password,u.rol,u.foto,u.proveedor,u.google_id,
                u.email_verificado,u.fecha_nacimiento,u.dui,u.codigo_telefono,u.telefono,
                u.departamento_id,u.municipio_id,u.distrito_id,u.direccion,u.estado,u.ultimo_acceso,
                u.session_version,u.perfil_estado,u.terminos_aceptados_at,u.google_registro_iniciado_at,u.created_at,u.updated_at,d.nombre departamento,m.nombre municipio,
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
          'administracion_docente', 'administrador_docente' => 'Administrador docente',
        default => 'Estudiante',
    };
}

function rutaFotoPerfil(array $usuario): string
{
    return urlAvatarAtenea($usuario);
}

function obtenerDepartamentos(): array
{
    return obtenerConexion()->query('SELECT id,nombre FROM departamentos ORDER BY nombre')->fetchAll();
}

