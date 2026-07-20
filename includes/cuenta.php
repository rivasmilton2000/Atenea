<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/notificaciones.php';

function cuentaRetornoSeguro(?string $retorno): string
{
    $retorno = trim((string) $retorno);
    return $retorno !== '' && str_starts_with($retorno, ATENEA_BASE_URL . '/') && !str_contains($retorno, "\r") && !str_contains($retorno, "\n")
        ? $retorno
        : rutaPanelPorRol((string) ($_SESSION['usuario_rol'] ?? ''));
}

function cuentaIpHash(): string
{
    return hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'desconocida'));
}

function registrarCambioCuenta(PDO $pdo, int $usuarioId, string $accion, array $campos): void
{
    $sesion = session_id() !== '' ? session_id() : 'sin-sesion';
    $consulta = $pdo->prepare('INSERT INTO historial_cambios_cuenta(usuario_id,accion,campos_modificados,ip_hash,sesion_hash) VALUES(:usuario,:accion,:campos,:ip,:sesion)');
    $consulta->execute([
        'usuario' => $usuarioId,
        'accion' => mb_substr($accion, 0, 60),
        'campos' => mb_substr(implode(', ', $campos), 0, 500),
        'ip' => cuentaIpHash(),
        'sesion' => hash('sha256', $sesion),
    ]);
}

function notificarCambioCuenta(array $usuario, array $campos, string $destinoAlternativo = ''): void
{
    $destino = $destinoAlternativo !== '' ? $destinoAlternativo : (string) ($usuario['correo'] ?? '');
    if (!filter_var($destino, FILTER_VALIDATE_EMAIL)) return;
    $fecha = date('d/m/Y H:i');
    $lista = implode(', ', $campos);
    $evento = 'cambio-cuenta:' . (int) ($usuario['id'] ?? 0) . ':' . hash('sha256', $fecha . $lista . $destino);
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'no disponible');
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $partes = explode('.', $ip);
        $ipAproximada = $partes[0] . '.' . $partes[1] . '.x.x';
    } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $partes = array_slice(explode(':', $ip), 0, 3);
        $ipAproximada = implode(':', $partes) . '::/48';
    } else {
        $ipAproximada = 'no disponible';
    }
    try {
        if(!empty($usuario['id']))crearNotificacionAtenea(['usuario_id'=>(int)$usuario['id'],'tipo'=>'cambio_cuenta','categoria'=>'seguridad','nivel'=>'informacion','titulo'=>'Tu cuenta fue actualizada','descripcion'=>'Se modificaron estos datos: '.$lista.'. Si no reconoces el cambio, contacta a soporte.','url'=>atenea_url('src/notificaciones/index.php'),'idempotency_key'=>$evento]);
        enviarPlantillaCorreoAtenea(
            'aviso_administrativo',
            $destino,
            trim((string) (($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''))),
            ['asunto' => 'Confirmación de cambios en tu cuenta Atenea', 'resumen' => 'Se modificó información de tu cuenta.', 'mensaje' => "Se modificó tu cuenta el {$fecha}.\nCampos: {$lista}\nOrigen aproximado: {$ipAproximada}\nSi no reconoces este cambio, contacta al soporte de Atenea."],
            ['usuario_id' => isset($usuario['id']) ? (int) $usuario['id'] : null, 'categoria'=>'seguridad','evento_id'=>$evento,'idempotency_key' => $evento]
        );
    } catch (Throwable $e) {
        error_log('Notificación de cuenta Atenea: ' . $e->getMessage());
    }
}

function crearVerificacionCuenta(PDO $pdo, int $usuarioId, string $tipo, array $datos, string $correo): array
{
    $limite = $pdo->prepare('SELECT COUNT(*) FROM verificaciones_cuenta WHERE usuario_id=:usuario AND tipo=:tipo AND created_at>=DATE_SUB(NOW(),INTERVAL 1 HOUR)');
    $limite->execute(['usuario' => $usuarioId, 'tipo' => $tipo]);
    if ((int) $limite->fetchColumn() >= 5) throw new RuntimeException('Has solicitado demasiados códigos. Espera una hora.');

    $codigo = strtoupper(bin2hex(random_bytes(4)));
    $pdo->prepare('UPDATE verificaciones_cuenta SET usado_at=NOW() WHERE usuario_id=:usuario AND tipo=:tipo AND usado_at IS NULL')->execute(['usuario' => $usuarioId, 'tipo' => $tipo]);
    $consulta = $pdo->prepare('INSERT INTO verificaciones_cuenta(usuario_id,tipo,codigo_hash,datos_pendientes,expira_at) VALUES(:usuario,:tipo,:hash,:datos,DATE_ADD(NOW(),INTERVAL 15 MINUTE))');
    $consulta->execute([
        'usuario' => $usuarioId,
        'tipo' => $tipo,
        'hash' => hash('sha256', $codigo),
        'datos' => json_encode($datos, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
    ]);
    $id = (int) $pdo->lastInsertId();
    $perfil = obtenerPerfilUsuario($usuarioId) ?? [];
    try {
        enviarPlantillaCorreoAtenea(
            'verificacion_cuenta',
            $correo,
            trim((string) (($perfil['nombre'] ?? '') . ' ' . ($perfil['apellido'] ?? ''))),
            ['codigo' => $codigo],
            ['usuario_id' => $usuarioId, 'idempotency_key' => 'verificacion-cuenta:' . $id]
        );
    } catch (Throwable $e) {
        $pdo->prepare('UPDATE verificaciones_cuenta SET usado_at=NOW() WHERE id=:id')->execute(['id' => $id]);
        throw $e;
    }
    return ['id' => $id, 'tipo' => $tipo];
}

function guardarFotoPerfil(array $archivo, ?string $anterior): string
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return (string) $anterior;
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException('No fue posible recibir la fotografía.');
    if ((int) ($archivo['size'] ?? 0) < 1 || (int) $archivo['size'] > 3 * 1024 * 1024) throw new RuntimeException('La fotografía debe pesar como máximo 3 MB.');
    $tmp = (string) ($archivo['tmp_name'] ?? '');
    if (!is_uploaded_file($tmp)) throw new RuntimeException('No fue posible validar la fotografía recibida.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $contenido = file_get_contents($tmp);
    if (!isset($permitidos[$mime]) || !is_string($contenido) || getimagesizefromstring($contenido) === false) {
        throw new RuntimeException('Selecciona una imagen JPG, PNG o WEBP válida.');
    }
    if (preg_match('/\.(?:php\d*|phtml|phar|html?|svg)(?:\.|$)/i', (string) ($archivo['name'] ?? ''))) throw new RuntimeException('El nombre del archivo no es seguro.');
    $directorio = ATENEA_ROOT . '/uploads/perfiles';
    if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) throw new RuntimeException('No fue posible preparar la carpeta de perfiles.');
    $ruta = 'uploads/perfiles/' . bin2hex(random_bytes(20)) . '.' . $permitidos[$mime];
    if (!move_uploaded_file($tmp, ATENEA_ROOT . '/' . $ruta)) throw new RuntimeException('No fue posible guardar la fotografía.');
    return $ruta;
}

function eliminarFotoPerfilLocal(?string $ruta): void
{
    require_once __DIR__ . '/avatar.php';
    $archivo = rutaFisicaAvatarAtenea($ruta);
    if ($archivo !== null) {
            $detalle = '';
            set_error_handler(static function (int $nivel, string $mensaje) use (&$detalle): bool {
                $detalle = $mensaje;
                return true;
            });
            try {
                $eliminada = unlink($archivo);
            } finally {
                restore_error_handler();
            }
            if (!$eliminada) {
                error_log('Atenea no pudo eliminar una fotografía de perfil local: ' . ($detalle !== '' ? $detalle : 'error desconocido'));
            }
    }
}

function cuentaFlash(array $errores = [], string $mensaje = '', ?array $verificacion = null): void
{
    $_SESSION['cuenta_modal'] = ['errores' => $errores, 'mensaje' => $mensaje, 'verificacion' => $verificacion, 'abrir' => true];
}
