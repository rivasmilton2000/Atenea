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
    $correo->SMTPSecure = strtolower((string) ($configuracion['encryption'] ?? 'tls')) === 'ssl'
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;
    $correo->Username = (string) $configuracion['smtp_user'];
    $correo->Password = (string) $configuracion['smtp_app_password'];
    $correo->Timeout = 15;
    $correo->CharSet = PHPMailer::CHARSET_UTF8;
    $correo->setFrom((string) $configuracion['smtp_user'], 'Atenea');
    $correo->addAddress($destinatario, $nombre);
    $correo->isHTML(true);
    $correo->Subject = $asunto;
    $correo->Body = $html;
    $correo->AltBody = $texto;
    $correo->send();
}
