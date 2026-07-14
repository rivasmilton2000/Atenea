<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/mail_config.php';

function enviarCorreoAtenea(string $destinatario, string $nombre, string $asunto, string $html, string $texto): void
{
    $configuracion = configuracionCorreoAtenea();
    $autoload = __DIR__ . '/mail/vendor/autoload.php';
    if (!configuracionSmtpCompleta($configuracion) || !is_file($autoload)) {
        throw new RuntimeException('La configuración SMTP no está completa.');
    }
    require_once $autoload;

    $correo = new PHPMailer(true);
    $correo->isSMTP();
    $correo->Host = (string) $configuracion['host'];
    $correo->Port = (int) ($configuracion['port'] ?? 587);
    $correo->SMTPAuth = true;
    $encryption = strtolower((string) ($configuracion['encryption'] ?? 'tls'));
    if ($encryption === 'ssl') {
        $correo->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } elseif ($encryption === 'tls') {
        $correo->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } else {
        $correo->SMTPAutoTLS = false;
        $correo->SMTPSecure = '';
    }
    $correo->Username = (string) $configuracion['smtp_user'];
    $correo->Password = (string) $configuracion['smtp_app_password'];
    $correo->Timeout = 15;
    $correo->CharSet = PHPMailer::CHARSET_UTF8;
    $correo->setFrom((string) $configuracion['from_email'], (string) $configuracion['from_name']);
    $correo->addAddress($destinatario, $nombre);
    $correo->isHTML(true);
    $correo->Subject = $asunto;
    $correo->Body = $html;
    $correo->AltBody = $texto;
    $correo->send();
}
