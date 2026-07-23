<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/mail_config.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/templates/email/catalog.php';

function enmascararCorreoAtenea(string $correo): string
{
    [$local, $dominio] = array_pad(explode('@', $correo, 2), 2, '');
    if ($dominio === '') return '***';
    return mb_substr($local, 0, 1) . str_repeat('*', max(3, min(8, mb_strlen($local) - 1))) . '@' . $dominio;
}

function sanitizarErrorCorreoAtenea(Throwable $error): string
{
    $mensaje = preg_replace('/[\r\n\t]+/', ' ', $error->getMessage()) ?: 'Error de envío no disponible';
    $mensaje = preg_replace('/(?i)(password|secret|token|authorization)\s*[=:]\s*[^\s,;]+/', '$1=[oculto]', $mensaje) ?: $mensaje;
    $mensaje = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[correo oculto]', $mensaje) ?: $mensaje;
    return mb_substr($mensaje, 0, 500);
}

function tablaCorreoDisponible(PDO $pdo): bool
{
    try { return (bool) $pdo->query("SHOW TABLES LIKE 'correo_envios'")->fetchColumn(); }
    catch (Throwable) { return false; }
}

function correoModoPruebaAtenea(): bool
{
    return filter_var(AppConfig::value('MAIL_TEST_MODE', 'true'), FILTER_VALIDATE_BOOL);
}

function correoEnteroConfiguradoAtenea(string $clave, int $predeterminado, int $minimo, int $maximo): int
{
    $valor = filter_var(AppConfig::value($clave, (string) $predeterminado), FILTER_VALIDATE_INT,
        ['options' => ['min_range' => $minimo, 'max_range' => $maximo]]);
    return $valor === false ? $predeterminado : (int) $valor;
}

function correoDestinatarioPruebaAtenea(): string
{
    $correo = strtolower(AppConfig::value('MAIL_TEST_RECIPIENT'));
    return filter_var($correo, FILTER_VALIDATE_EMAIL) ? $correo : '';
}

function correoPreferenciaHabilitadaAtenea(PDO $pdo, ?int $usuarioId, string $categoria): bool
{
    if (!$usuarioId || in_array($categoria, ['seguridad', 'cuenta', 'transaccional'], true)) return true;
    $q = $pdo->prepare('SELECT correo_habilitado FROM notificacion_preferencias WHERE usuario_id=:u AND categoria=:c');
    try { $q->execute(['u' => $usuarioId, 'c' => mb_substr($categoria, 0, 80)]); }
    catch (Throwable) { return true; }
    $valor = $q->fetchColumn();
    return $valor === false || (int) $valor === 1;
}

function categoriaCorreoAtenea(string $tipo): string
{
    if (preg_match('/(recuper|restable|password|verific|codigo|cuenta_|cambio_rol|inactividad)/i',$tipo)) return 'seguridad';
    if (preg_match('/(compra|comprobante|pedido|pago)/i',$tipo)) return 'comercio';
    if (preg_match('/(academic|docente|capacit|certificado|inscripcion|seccion)/i',$tipo)) return 'academico';
    if (preg_match('/(newsletter|noticia|novedad)/i',$tipo)) return 'novedades';
    if (preg_match('/(mensaje|respuesta|comunicacion|contacto|correo|aviso)/i',$tipo)) return 'comunicaciones';
    return $tipo;
}

function encolarCorreoAtenea(string $destinatario, string $nombre, string $asunto, string $html, string $texto, array $opciones = []): ?int
{
    if (!filter_var($destinatario, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $destinatario . $nombre . $asunto)) {
        throw new InvalidArgumentException('Los datos de cabecera del correo no son válidos.');
    }
    $pdo = obtenerConexion();
    if (!tablaCorreoDisponible($pdo)) throw new RuntimeException('La cola de correo no está disponible.');

    $destinatario = strtolower(trim($destinatario));
    $tipo = mb_substr((string) ($opciones['tipo'] ?? 'general'), 0, 80);
    $categoria = mb_substr((string) ($opciones['categoria'] ?? categoriaCorreoAtenea($tipo)), 0, 80);
    $evento = mb_substr((string) ($opciones['evento_id'] ?? $opciones['idempotency_key'] ?? ''), 0, 190);
    if ($evento === '') $evento = 'correo:' . bin2hex(random_bytes(20));
    $clave = mb_substr((string) ($opciones['idempotency_key'] ?? $evento), 0, 190);
    $grupo = mb_substr(trim((string) ($opciones['grupo_clave'] ?? '')), 0, 190) ?: null;
    $usuarioId = !empty($opciones['usuario_id']) ? (int) $opciones['usuario_id'] : null;
    $permitirPrueba = !empty($opciones['permitir_envio_prueba']);
    $modoPrueba = correoModoPruebaAtenea();
    $destinoPrueba = correoDestinatarioPruebaAtenea();
    if ($permitirPrueba && ($destinoPrueba === '' || !hash_equals($destinoPrueba, $destinatario))) {
        throw new DomainException('La dirección no está autorizada para pruebas.');
    }
    $habilitado = correoPreferenciaHabilitadaAtenea($pdo, $usuarioId, $categoria);
    $cancelado = !$habilitado || ($modoPrueba && !$permitirPrueba);
    $motivo = !$habilitado ? 'Cancelado por las preferencias del usuario.' : ($cancelado ? 'Conservado sin envío por MAIL_TEST_MODE.' : null);
    $maxIntentos = correoEnteroConfiguradoAtenea('MAIL_MAX_RETRIES', 3, 1, 10);
    $opcionesSeguras = $opciones;
    unset($opcionesSeguras['permitir_envio_prueba']);

    $transaccionPropia = !$pdo->inTransaction();
    if ($transaccionPropia) $pdo->beginTransaction();
    try {
        if ($grupo && !$cancelado && !empty($opciones['agrupar'])) {
            $q = $pdo->prepare("SELECT id FROM correo_envios WHERE usuario_id=:u AND grupo_clave=:g AND estado='pendiente' AND created_at>=DATE_SUB(NOW(),INTERVAL 15 MINUTE) ORDER BY id DESC LIMIT 1 FOR UPDATE");
            $q->execute(['u' => $usuarioId, 'g' => $grupo]);
            $principal = (int) $q->fetchColumn();
            if ($principal) {
                $q = $pdo->prepare("INSERT IGNORE INTO correo_envios(tipo,asunto,usuario_id,pedido_id,hilo_id,destinatario_enmascarado,destinatario_hash,destinatario_email,destinatario_nombre,contenido_html,contenido_texto,opciones_json,idempotency_key,evento_id,grupo_clave,estado,cancelado_at,cancelado_motivo,max_intentos,es_modo_prueba) VALUES(:tipo,:asunto,:usuario,:pedido,:hilo,:mascara,:hash,:email,:nombre,:html,:texto,:opciones,:clave,:evento,:grupo,'cancelado',NOW(),'Agrupado en otro correo pendiente.',:maximo,:prueba)");
                $q->execute(['tipo'=>$tipo,'asunto'=>mb_substr($asunto,0,190),'usuario'=>$usuarioId,'pedido'=>$opciones['pedido_id']??null,'hilo'=>$opciones['hilo_id']??null,'mascara'=>enmascararCorreoAtenea($destinatario),'hash'=>hash('sha256',$destinatario),'email'=>$destinatario,'nombre'=>mb_substr($nombre,0,190),'html'=>$html,'texto'=>$texto,'opciones'=>json_encode($opcionesSeguras,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),'clave'=>$clave,'evento'=>$evento,'grupo'=>$grupo,'maximo'=>$maxIntentos,'prueba'=>$modoPrueba?1:0]);
                $pdo->prepare('UPDATE correo_envios SET agrupados=agrupados+1 WHERE id=:id')->execute(['id'=>$principal]);
                if ($transaccionPropia) $pdo->commit();
                return $principal;
            }
        }
        $q = $pdo->prepare("INSERT IGNORE INTO correo_envios(tipo,asunto,usuario_id,pedido_id,hilo_id,destinatario_enmascarado,destinatario_hash,destinatario_email,destinatario_nombre,contenido_html,contenido_texto,opciones_json,idempotency_key,evento_id,grupo_clave,estado,max_intentos,es_modo_prueba,permitir_envio_prueba,cancelado_at,cancelado_motivo) VALUES(:tipo,:asunto,:usuario,:pedido,:hilo,:mascara,:hash,:email,:nombre,:html,:texto,:opciones,:clave,:evento,:grupo,:estado,:maximo,:prueba,:permitir,:cancelado_at,:motivo)");
        $q->execute(['tipo'=>$tipo,'asunto'=>mb_substr($asunto,0,190),'usuario'=>$usuarioId,'pedido'=>$opciones['pedido_id']??null,'hilo'=>$opciones['hilo_id']??null,'mascara'=>enmascararCorreoAtenea($destinatario),'hash'=>hash('sha256',$destinatario),'email'=>$destinatario,'nombre'=>mb_substr($nombre,0,190),'html'=>$html,'texto'=>$texto,'opciones'=>json_encode($opcionesSeguras,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),'clave'=>$clave,'evento'=>$evento,'grupo'=>$grupo,'estado'=>$cancelado?'cancelado':'pendiente','maximo'=>$maxIntentos,'prueba'=>$modoPrueba?1:0,'permitir'=>$permitirPrueba?1:0,'cancelado_at'=>$cancelado?date('Y-m-d H:i:s'):null,'motivo'=>$motivo]);
        $q = $pdo->prepare('SELECT id FROM correo_envios WHERE idempotency_key=:clave');
        $q->execute(['clave'=>$clave]);
        $id = (int) $q->fetchColumn();
        if ($transaccionPropia) $pdo->commit();
        return $id ?: null;
    } catch (Throwable $e) {
        if ($transaccionPropia && $pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

function enviarCorreoAtenea(string $destinatario, string $nombre, string $asunto, string $html, string $texto, array $opciones = []): void
{
    encolarCorreoAtenea($destinatario, $nombre, $asunto, $html, $texto, $opciones);
}

function entregarCorreoSmtpAtenea(array $registro): string
{
    $configuracion = configuracionCorreoAtenea();
    $autoload = __DIR__ . '/mail/vendor/autoload.php';
    if (!configuracionSmtpCompleta($configuracion) || !is_file($autoload)) throw new RuntimeException('La configuración SMTP no está completa.');
    require_once $autoload;
    $correo = new PHPMailer(true);
    $correo->isSMTP();
    $correo->Host = (string) $configuracion['host'];
    $correo->Port = (int) $configuracion['port'];
    $correo->SMTPAuth = true;
    $encryption = strtolower((string) $configuracion['encryption']);
    if ($encryption === 'ssl') $correo->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    elseif ($encryption === 'tls') $correo->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    else { $correo->SMTPAutoTLS = false; $correo->SMTPSecure = ''; }
    $correo->Username = (string) $configuracion['smtp_user'];
    $correo->Password = (string) $configuracion['smtp_app_password'];
    $correo->Timeout = 15;
    $correo->CharSet = PHPMailer::CHARSET_UTF8;
    $correo->setFrom((string) $configuracion['from_email'], (string) $configuracion['from_name']);
    $correo->addAddress((string) $registro['destinatario_email'], (string) $registro['destinatario_nombre']);
    $opciones = json_decode((string) ($registro['opciones_json'] ?? ''), true) ?: [];
    if (!empty($opciones['reply_to']) && filter_var($opciones['reply_to'], FILTER_VALIDATE_EMAIL)) $correo->addReplyTo((string)$opciones['reply_to'], (string)($opciones['reply_to_name']??''));
    foreach (($opciones['attachments'] ?? []) as $adjunto) {
        $ruta = (string)($adjunto['path'] ?? '');
        if ($ruta === '' || !is_file($ruta) || filesize($ruta) < 1) throw new RuntimeException('Un adjunto requerido no está disponible.');
        $correo->addAttachment($ruta, (string)($adjunto['name'] ?? basename($ruta)), PHPMailer::ENCODING_BASE64, (string)($adjunto['type'] ?? 'application/octet-stream'));
    }
    $html = (string) $registro['contenido_html'];
    $logo = str_contains($html,'data-atenea-email-logo="1"') ? rutaFisicaLogoCorreoAtenea() : null;
    $embebido = false;
    if ($logo !== null) {
        try { $embebido = $correo->addEmbeddedImage($logo, ATENEA_EMAIL_LOGO_CID, 'logo-atenea.png'); }
        catch (Throwable) { $embebido = false; }
    }
    if (!$embebido) $html = reemplazarLogoCorreoPorTexto($html);
    $correo->isHTML(true);
    $correo->Subject = (string) $registro['asunto'];
    $correo->Body = $html;
    $correo->AltBody = (string) $registro['contenido_texto'];
    $correo->send();
    return mb_substr((string)$correo->getLastMessageID(), 0, 255);
}

function procesarCorreoEnColaAtenea(int $id): string
{
    $pdo = obtenerConexion();
    $pdo->beginTransaction();
    try {
        $q = $pdo->prepare("SELECT * FROM correo_envios WHERE id=:id AND estado IN('pendiente','fallido') AND disponible_at<=NOW() FOR UPDATE");
        $q->execute(['id'=>$id]);
        $r = $q->fetch();
        if (!$r) { $pdo->commit(); return 'omitido'; }
        if ((int)$r['intento'] >= (int)$r['max_intentos']) { $pdo->commit(); return 'agotado'; }
        $autorizadoPrueba = (int)$r['permitir_envio_prueba'] === 1 && correoDestinatarioPruebaAtenea() !== '' && hash_equals(correoDestinatarioPruebaAtenea(), strtolower((string)$r['destinatario_email']));
        if ((correoModoPruebaAtenea() || (int)$r['es_modo_prueba'] === 1) && !$autorizadoPrueba) {
            $pdo->prepare("UPDATE correo_envios SET estado='cancelado',cancelado_at=NOW(),cancelado_motivo='Bloqueado por el modo de pruebas.' WHERE id=:id")->execute(['id'=>$id]);
            $pdo->commit(); return 'cancelado';
        }
        $limiteGlobal = correoEnteroConfiguradoAtenea('MAIL_MAX_PER_MINUTE',3,1,1000);
        $q = $pdo->query("SELECT COUNT(*) FROM correo_envios WHERE estado='enviado' AND enviado_at>=DATE_SUB(NOW(),INTERVAL 1 MINUTE)");
        if ((int)$q->fetchColumn() >= $limiteGlobal) {
            $pdo->prepare('UPDATE correo_envios SET disponible_at=DATE_ADD(NOW(),INTERVAL 1 MINUTE) WHERE id=:id')->execute(['id'=>$id]);
            $pdo->commit(); return 'limitado';
        }
        if (!empty($r['usuario_id'])) {
            $limiteUsuario = correoEnteroConfiguradoAtenea('MAIL_MAX_PER_USER_PER_HOUR',5,1,1000);
            $q = $pdo->prepare("SELECT COUNT(*) FROM correo_envios WHERE usuario_id=:u AND estado='enviado' AND enviado_at>=DATE_SUB(NOW(),INTERVAL 1 HOUR)");
            $q->execute(['u'=>$r['usuario_id']]);
            if ((int)$q->fetchColumn() >= $limiteUsuario) {
                $pdo->prepare('UPDATE correo_envios SET disponible_at=DATE_ADD(NOW(),INTERVAL 1 HOUR) WHERE id=:id')->execute(['id'=>$id]);
                $pdo->commit(); return 'limitado';
            }
        }
        $pdo->prepare("UPDATE correo_envios SET estado='procesando',intento=intento+1,procesando_desde=NOW(),error_sanitizado=NULL WHERE id=:id")->execute(['id'=>$id]);
        $pdo->commit();
        $messageId = entregarCorreoSmtpAtenea($r);
        $pdo->prepare("UPDATE correo_envios SET estado='enviado',enviado_at=NOW(),message_id=:message,procesando_desde=NULL,error_sanitizado=NULL WHERE id=:id")->execute(['message'=>$messageId?:null,'id'=>$id]);
        if (($r['tipo']??'')==='compra_confirmada'&&!empty($r['pedido_id'])) $pdo->prepare('UPDATE pedidos SET email_sent_at=COALESCE(email_sent_at,NOW()) WHERE id=:id')->execute(['id'=>$r['pedido_id']]);
        return 'enviado';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $q = $pdo->prepare("UPDATE correo_envios SET estado='fallido',procesando_desde=NULL,error_sanitizado=:error,disponible_at=TIMESTAMPADD(MINUTE,LEAST(60,POW(2,intento)),NOW()) WHERE id=:id");
        $q->execute(['error'=>sanitizarErrorCorreoAtenea($e),'id'=>$id]);
        try { require_once __DIR__.'/errores_sistema.php'; registrarErrorSistemaAtenea('correo','cola',sanitizarErrorCorreoAtenea($e),['correo_envio_id'=>$id]); } catch (Throwable) {}
        return 'fallido';
    }
}

function procesarColaCorreoAtenea(int $limite = 25): array
{
    $pdo = obtenerConexion();
    $limite = max(1, min(100, $limite));
    $pdo->exec("UPDATE correo_envios SET estado='fallido',procesando_desde=NULL,error_sanitizado='El procesamiento anterior no concluyó.',disponible_at=NOW() WHERE estado='procesando' AND procesando_desde<DATE_SUB(NOW(),INTERVAL 10 MINUTE) AND intento<max_intentos");
    $ids = $pdo->query("SELECT id FROM correo_envios WHERE estado IN('pendiente','fallido') AND disponible_at<=NOW() AND intento<max_intentos ORDER BY disponible_at,id LIMIT {$limite}")->fetchAll(PDO::FETCH_COLUMN);
    $resultado = ['revisados'=>0,'enviados'=>0,'fallidos'=>0,'cancelados'=>0,'limitados'=>0];
    foreach ($ids as $id) {
        $resultado['revisados']++;
        $estado = procesarCorreoEnColaAtenea((int)$id);
        if (isset($resultado[$estado.'s'])) $resultado[$estado.'s']++;
        elseif ($estado === 'enviado') $resultado['enviados']++;
    }
    return $resultado;
}

function enviarPlantillaCorreoAtenea(string $tipo, string $destinatario, string $nombre, array $datos, array $opciones = []): void
{
    $plantilla = plantillaCorreoAtenea($tipo, array_merge($datos, ['nombre' => $datos['nombre'] ?? $nombre]));
    enviarCorreoAtenea($destinatario, $nombre, $plantilla['subject'], $plantilla['html'], $plantilla['text'], array_merge($opciones, ['tipo' => $tipo]));
}
