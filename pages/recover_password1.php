<?php
require '../includes/connection.php';
require_once '../includes/atenea_auth.php';
require_once '../includes/email_account.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require '../vendor/phpmailer/src/Exception.php';
require '../vendor/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/src/SMTP.php';

header('Content-Type: application/json; charset=UTF-8');

function atenea_recover_response(string $status, string $message, int $code = 200): void
{
    http_response_code($code);
    echo json_encode(
        [
            'status' => $status,
            'message' => $message,
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['btnrecover'])) {
    atenea_recover_response('error', 'Solicitud no valida.', 405);
}

$email = trim((string) ($_POST['email'] ?? ''));
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    atenea_recover_response('error', 'Ingresa un correo electronico valido.', 422);
}

$user = atenea_fetch_user_by_email($db, $email);
if (!$user) {
    atenea_recover_response(
        'error',
        'No encontramos una cuenta activa asociada a ese correo en Atenea.',
        404
    );
}

if (!isset($myemail, $mypassword) || trim((string) $myemail) === '' || trim((string) $mypassword) === '') {
    atenea_recover_response(
        'error',
        'La cuenta de correo de Atenea no esta configurada para enviar mensajes.',
        500
    );
}

$userName = atenea_user_display_name($user);
$username = trim((string) ($user['USERNAME'] ?? ''));
$role = atenea_user_is_student($user) ? 'Estudiante' : trim((string) ($user['TYPE'] ?? 'Usuario'));
$contactEmail = trim((string) ($user['EMAIL'] ?? $user['correo_estudiante'] ?? $email));
$logoPath = __DIR__ . '/../img/Atenea Logo.png';
$logoHtml = '<div style="font-weight:800;font-size:24px;color:#c8a133;letter-spacing:0.08em;">ATENEA</div>';

$mail = new PHPMailer(true);

try {
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $myemail;
    $mail->Password = $mypassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom($myemail, 'Atenea');
    $mail->addAddress($contactEmail, $userName);
    $mail->addReplyTo($myemail, 'Atenea');

    if (is_file($logoPath)) {
      $mail->addEmbeddedImage($logoPath, 'atenea_logo');
      $logoHtml = '<img src="cid:atenea_logo" alt="Atenea" style="max-width: 160px; height: auto;">';
    }

    $mail->isHTML(true);
    $mail->Subject = 'Solicitud de recuperacion de contrasena - Atenea';
    $mail->Body = '
      <div style="margin:0;padding:24px;background:#f5fbf8;font-family:Arial,sans-serif;color:#16352a;">
        <div style="max-width:620px;margin:0 auto;background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 12px 36px rgba(3,82,55,0.12);">
          <div style="padding:28px 28px 18px;background:linear-gradient(135deg,#046845,#0b7a4b);text-align:center;">
            <div style="display:inline-block;background:#ffffff;border-radius:18px;padding:14px 18px;">' . $logoHtml . '</div>
          </div>
          <div style="padding:28px;">
            <h1 style="margin:0 0 12px;font-size:24px;color:#0d2438;">Recibimos tu solicitud</h1>
            <p style="margin:0 0 18px;line-height:1.7;color:#4c675f;">
              Hola <strong>' . htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') . '</strong>, confirmamos que recibimos tu solicitud de recuperacion de contrasena en Atenea.
            </p>
            <div style="padding:18px;border-radius:16px;background:#f4faf7;border:1px solid rgba(4,104,69,0.1);">
              <p style="margin:0 0 8px;"><strong>Usuario:</strong> ' . htmlspecialchars($username !== '' ? $username : $contactEmail, ENT_QUOTES, 'UTF-8') . '</p>
              <p style="margin:0 0 8px;"><strong>Rol:</strong> ' . htmlspecialchars($role, ENT_QUOTES, 'UTF-8') . '</p>
              <p style="margin:0;"><strong>Correo:</strong> ' . htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') . '</p>
            </div>
            <p style="margin:18px 0 0;line-height:1.7;color:#4c675f;">
              Nuestro equipo revisara tu acceso y te ayudara a restablecerlo lo antes posible.
            </p>
          </div>
        </div>
      </div>';
    $mail->AltBody = 'Recibimos tu solicitud de recuperacion de contrasena en Atenea. Usuario: '
        . ($username !== '' ? $username : $contactEmail)
        . '. Rol: ' . $role . '.';

    $mail->send();

    atenea_recover_response(
        'success',
        'Te enviamos un correo de confirmacion con los detalles de tu solicitud de recuperacion.'
    );
} catch (Exception $exception) {
    atenea_recover_response(
        'error',
        'No pudimos enviar el correo en este momento. Detalle tecnico: ' . $mail->ErrorInfo,
        500
    );
}
