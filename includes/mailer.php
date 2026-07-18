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
    static $disponible = null;
    if ($disponible !== null) return $disponible;
    try {
        $disponible = (bool) $pdo->query("SHOW TABLES LIKE 'correo_envios'")->fetchColumn();
    } catch (Throwable) {
        $disponible = false;
    }
    return $disponible;
}

function reservarEnvioCorreoAtenea(array $contexto): ?int
{
    $pdo = obtenerConexion();
    if (!tablaCorreoDisponible($pdo)) return 0;
    $clave = substr((string) ($contexto['idempotency_key'] ?? ''), 0, 190);
    if ($clave === '') $clave = 'correo:' . bin2hex(random_bytes(20));
    $destinatario = strtolower((string) $contexto['destinatario']);
    $pdo->beginTransaction();
    try {
        $q = $pdo->prepare("INSERT IGNORE INTO correo_envios(tipo,asunto,usuario_id,pedido_id,hilo_id,destinatario_enmascarado,destinatario_hash,idempotency_key) VALUES(:tipo,:asunto,:usuario,:pedido,:hilo,:mascara,:hash,:clave)");
        $q->execute(['tipo' => substr((string) $contexto['tipo'], 0, 80), 'asunto'=>mb_substr((string)($contexto['asunto']??''),0,190)?:null, 'usuario' => $contexto['usuario_id'] ?? null, 'pedido' => $contexto['pedido_id'] ?? null, 'hilo'=>$contexto['hilo_id']??null, 'mascara' => enmascararCorreoAtenea($destinatario), 'hash' => hash('sha256', $destinatario), 'clave' => $clave]);
        $q = $pdo->prepare('SELECT id,estado,procesando_desde FROM correo_envios WHERE idempotency_key=:clave FOR UPDATE');
        $q->execute(['clave' => $clave]);
        $registro = $q->fetch();
        if (!$registro || $registro['estado'] === 'enviado' || ($registro['estado'] === 'procesando' && strtotime((string) $registro['procesando_desde']) > time() - 600)) {
            $pdo->commit();
            return null;
        }
        $pdo->prepare("UPDATE correo_envios SET estado='procesando',intento=intento+1,procesando_desde=NOW(),error_sanitizado=NULL WHERE id=:id")->execute(['id' => $registro['id']]);
        $pdo->commit();
        return (int) $registro['id'];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

function finalizarEnvioCorreoAtenea(?int $registroId, bool $enviado, ?Throwable $error = null): void
{
    if (!$registroId) return;
    $pdo = obtenerConexion();
    if (!tablaCorreoDisponible($pdo)) return;
    $q = $pdo->prepare("UPDATE correo_envios SET estado=:estado,enviado_at=IF(:enviado=1,NOW(),enviado_at),procesando_desde=NULL,error_sanitizado=:error WHERE id=:id");
    $q->execute(['estado' => $enviado ? 'enviado' : 'fallido', 'enviado' => $enviado ? 1 : 0, 'error' => $error ? sanitizarErrorCorreoAtenea($error) : null, 'id' => $registroId]);
}

function enviarCorreoAtenea(string $destinatario, string $nombre, string $asunto, string $html, string $texto, array $opciones = []): void
{
    if (!filter_var($destinatario,FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/',$destinatario.$nombre.$asunto)) {
        throw new InvalidArgumentException('Los datos de cabecera del correo no son válidos.');
    }
    $registroId = reservarEnvioCorreoAtenea(array_merge($opciones, ['destinatario' => $destinatario, 'tipo' => $opciones['tipo'] ?? 'general', 'asunto'=>$asunto]));
    if ($registroId === null) return;
    try {
        $configuracion = configuracionCorreoAtenea();
        $autoload = __DIR__ . '/mail/vendor/autoload.php';
        if (!configuracionSmtpCompleta($configuracion) || !is_file($autoload)) throw new RuntimeException('La configuración SMTP no está completa.');
        require_once $autoload;
        $correo = new PHPMailer(true);
        $correo->isSMTP();
        $correo->Host = (string) $configuracion['host'];
        $correo->Port = (int) ($configuracion['port'] ?? 587);
        $correo->SMTPAuth = true;
        $encryption = strtolower((string) ($configuracion['encryption'] ?? 'tls'));
        if ($encryption === 'ssl') $correo->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        elseif ($encryption === 'tls') $correo->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        else { $correo->SMTPAutoTLS = false; $correo->SMTPSecure = ''; }
        $correo->Username = (string) $configuracion['smtp_user'];
        $correo->Password = (string) $configuracion['smtp_app_password'];
        $correo->Timeout = 15;
        $correo->CharSet = PHPMailer::CHARSET_UTF8;
        $correo->setFrom((string) $configuracion['from_email'], (string) $configuracion['from_name']);
        $correo->addAddress($destinatario, $nombre);
        if (!empty($opciones['reply_to']) && filter_var($opciones['reply_to'], FILTER_VALIDATE_EMAIL)) {
            $correo->addReplyTo((string) $opciones['reply_to'], (string) ($opciones['reply_to_name'] ?? ''));
        }
        foreach (($opciones['attachments'] ?? []) as $adjunto) {
            $ruta = (string)($adjunto['path'] ?? '');
            if ($ruta !== '' && is_file($ruta)) $correo->addAttachment($ruta, (string)($adjunto['name'] ?? basename($ruta)));
        }
        $logoEmbebido = false;
        $logoPath = rutaFisicaLogoCorreoAtenea();
        if ($logoPath !== null) {
            try {
                $mimeLogo = (new finfo(FILEINFO_MIME_TYPE))->file($logoPath) ?: 'image/png';
                $logoEmbebido = $correo->addEmbeddedImage(
                    $logoPath,
                    ATENEA_EMAIL_LOGO_CID,
                    'logo-atenea.png',
                    PHPMailer::ENCODING_BASE64,
                    $mimeLogo,
                    'inline'
                );
            } catch (Throwable) {
                error_log('Logo correo Atenea: no se pudo incrustar el recurso institucional.');
            }
        }
        if (!$logoEmbebido) $html = reemplazarLogoCorreoPorTexto($html);
        $correo->isHTML(true);
        $correo->Subject = $asunto;
        $correo->Body = $html;
        $correo->AltBody = $texto;
        $correo->send();
        finalizarEnvioCorreoAtenea($registroId, true);
    } catch (Throwable $e) {
        finalizarEnvioCorreoAtenea($registroId, false, $e);
        try {
            require_once __DIR__.'/errores_sistema.php';
            registrarErrorSistemaAtenea('correo','mailer',sanitizarErrorCorreoAtenea($e),['correo_envio_id'=>$registroId,'pedido_id'=>$opciones['pedido_id']??null,'usuario_id'=>$opciones['usuario_id']??null]);
        } catch (Throwable) {}
        throw $e;
    }
}

function enviarPlantillaCorreoAtenea(string $tipo, string $destinatario, string $nombre, array $datos, array $opciones = []): void
{
    $plantilla = plantillaCorreoAtenea($tipo, array_merge($datos, ['nombre' => $datos['nombre'] ?? $nombre]));
    enviarCorreoAtenea($destinatario, $nombre, $plantilla['subject'], $plantilla['html'], $plantilla['text'], array_merge($opciones, ['tipo' => $tipo]));
}
