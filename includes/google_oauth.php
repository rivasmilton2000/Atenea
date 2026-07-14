<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/services.php';

function obtenerConfiguracionGoogle(): array
{
    return GoogleConfig::toArray();
}

function googleDisponible(?array $configuracion = null): bool
{
    $configuracion ??= obtenerConfiguracionGoogle();
    return trim((string) ($configuracion['client_id'] ?? '')) !== ''
        && trim((string) ($configuracion['client_secret'] ?? '')) !== ''
        && filter_var($configuracion['redirect_uri'] ?? '', FILTER_VALIDATE_URL) !== false
        && extension_loaded('curl');
}

function googleEsDesarrollo(): bool
{
    return AppConfig::isLocal();
}

function diagnosticoGoogle(?array $configuracion = null): array
{
    if (!googleEsDesarrollo()) return [];
    $configuracion ??= obtenerConfiguracionGoogle();
    $faltantes = [];
    if (trim((string) ($configuracion['client_id'] ?? '')) === '') $faltantes[] = 'GOOGLE_CLIENT_ID';
    if (trim((string) ($configuracion['client_secret'] ?? '')) === '') $faltantes[] = 'GOOGLE_CLIENT_SECRET';
    if (filter_var($configuracion['redirect_uri'] ?? '', FILTER_VALIDATE_URL) === false) $faltantes[] = 'GOOGLE_REDIRECT_URI';
    if (!extension_loaded('curl')) $faltantes[] = 'extensión cURL de PHP';
    return $faltantes;
}

function solicitudGoogle(string $url, array $opciones = []): array
{
    $curl = curl_init($url);
    if ($curl === false) throw new RuntimeException('No fue posible iniciar la conexión segura.');
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTPHEADER => array_merge(['Accept: application/json'], $opciones['headers'] ?? []),
    ]);
    if (isset($opciones['post'])) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($opciones['post'], '', '&', PHP_QUERY_RFC3986));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array_merge(['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded'], $opciones['headers'] ?? []));
    }
    $respuesta = curl_exec($curl);
    $estado = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    if (!is_string($respuesta) || $error !== '' || $estado < 200 || $estado >= 300) {
        throw new RuntimeException('Google rechazó la solicitud segura.');
    }
    $datos = json_decode($respuesta, true, 32, JSON_THROW_ON_ERROR);
    if (!is_array($datos)) throw new RuntimeException('Google devolvió una respuesta no válida.');
    return $datos;
}

function obtenerPerfilGoogle(string $codigo, array $configuracion): array
{
    $token = solicitudGoogle((string) ($configuracion['token_uri'] ?? GoogleConfig::tokenUri()), ['post' => [
        'code' => $codigo,
        'client_id' => (string) $configuracion['client_id'],
        'client_secret' => (string) $configuracion['client_secret'],
        'redirect_uri' => (string) $configuracion['redirect_uri'],
        'grant_type' => 'authorization_code',
    ]]);
    $accessToken = (string) ($token['access_token'] ?? '');
    $idToken = (string) ($token['id_token'] ?? '');
    if ($accessToken === '' || $idToken === '') throw new RuntimeException('Google no entregó los datos de acceso esperados.');

    $verificacion = solicitudGoogle((string) ($configuracion['tokeninfo_uri'] ?? GoogleConfig::tokenInfoUri()) . '?id_token=' . rawurlencode($idToken));
    if (!hash_equals((string) $configuracion['client_id'], (string) ($verificacion['aud'] ?? ''))
        || empty($verificacion['sub'])
        || empty($verificacion['email'])
        || !in_array($verificacion['email_verified'] ?? false, [true, 'true', '1', 1], true)) {
        throw new RuntimeException('La identidad devuelta por Google no pudo validarse.');
    }

    $perfil = solicitudGoogle((string) ($configuracion['userinfo_uri'] ?? GoogleConfig::userInfoUri()), [
        'headers' => ['Authorization: Bearer ' . $accessToken],
    ]);
    if (strtolower((string) ($perfil['email'] ?? '')) !== strtolower((string) $verificacion['email'])) {
        throw new RuntimeException('El correo de Google no coincide con la identidad validada.');
    }

    return [
        'google_id' => (string) $verificacion['sub'],
        'correo' => strtolower((string) $verificacion['email']),
        'nombre' => mb_substr(trim((string) ($perfil['given_name'] ?? $perfil['name'] ?? 'Estudiante')), 0, 100),
        'apellido' => mb_substr(trim((string) ($perfil['family_name'] ?? '')), 0, 100),
        'foto' => mb_substr(trim((string) ($perfil['picture'] ?? '')), 0, 500),
    ];
}

function autenticarConPerfilGoogle(array $perfil, bool $vincular = false): array
{
    $pdo = obtenerConexion();
    $pdo->beginTransaction();
    try {
        $consulta = $pdo->prepare('SELECT * FROM usuarios WHERE google_id=:google_id LIMIT 1 FOR UPDATE');
        $consulta->execute(['google_id' => $perfil['google_id']]);
        $usuarioGoogle = $consulta->fetch();

        $consulta = $pdo->prepare('SELECT * FROM usuarios WHERE correo=:correo LIMIT 1 FOR UPDATE');
        $consulta->execute(['correo' => $perfil['correo']]);
        $usuarioCorreo = $consulta->fetch();

        if ($vincular) {
            $idActual = (int) ($_SESSION['usuario_id'] ?? 0);
            if ($idActual < 1 || strtolower((string) ($_SESSION['usuario_correo'] ?? '')) !== $perfil['correo']) {
                throw new RuntimeException('Solo puedes vincular una cuenta con el mismo correo.');
            }
            if (($usuarioGoogle && (int) $usuarioGoogle['id'] !== $idActual) || ($usuarioCorreo && (int) $usuarioCorreo['id'] !== $idActual)) {
                throw new RuntimeException('La identidad de Google ya pertenece a otra cuenta.');
            }
            $consulta = $pdo->prepare("UPDATE usuarios SET google_id=:google_id,proveedor=IF(password IS NULL,'google','mixto'),email_verificado=1 WHERE id=:id AND estado='activo'");
            $consulta->execute(['google_id' => $perfil['google_id'], 'id' => $idActual]);
            $consulta = $pdo->prepare('SELECT * FROM usuarios WHERE id=:id');
            $consulta->execute(['id' => $idActual]);
            $usuario = $consulta->fetch();
        } elseif ($usuarioGoogle || $usuarioCorreo) {
            $usuario = $usuarioGoogle ?: $usuarioCorreo;
            if ($usuarioGoogle && $usuarioCorreo && (int) $usuarioGoogle['id'] !== (int) $usuarioCorreo['id']) {
                throw new RuntimeException('La identidad y el correo pertenecen a cuentas distintas.');
            }
            if (!empty($usuario['google_id']) && !hash_equals((string) $usuario['google_id'], (string) $perfil['google_id'])) {
                throw new RuntimeException('El correo ya está vinculado con otra identidad.');
            }
            $consulta = $pdo->prepare("UPDATE usuarios SET google_id=:google_id,proveedor=IF(password IS NULL,'google','mixto'),email_verificado=1 WHERE id=:id");
            $consulta->execute(['google_id' => $perfil['google_id'], 'id' => (int) $usuario['id']]);
            $usuario['google_id'] = $perfil['google_id'];
            $usuario['email_verificado'] = 1;
        } else {
            $consulta = $pdo->prepare("INSERT INTO usuarios(nombre,apellido,correo,password,google_id,proveedor,email_verificado,foto,rol,estado) VALUES(:nombre,:apellido,:correo,NULL,:google_id,'google',1,:foto,'usuario','activo')");
            $consulta->execute([
                'nombre' => $perfil['nombre'] !== '' ? $perfil['nombre'] : 'Estudiante',
                'apellido' => $perfil['apellido'],
                'correo' => $perfil['correo'],
                'google_id' => $perfil['google_id'],
                'foto' => $perfil['foto'] !== '' ? $perfil['foto'] : null,
            ]);
            $consulta = $pdo->prepare('SELECT * FROM usuarios WHERE id=:id');
            $consulta->execute(['id' => (int) $pdo->lastInsertId()]);
            $usuario = $consulta->fetch();
        }

        if (!is_array($usuario) || ($usuario['estado'] ?? '') !== 'activo') throw new RuntimeException('La cuenta no está activa.');
        $pdo->prepare('UPDATE usuarios SET ultimo_acceso=NOW() WHERE id=:id')->execute(['id' => (int) $usuario['id']]);
        $pdo->commit();
        return $usuario;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
