<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

require_once dirname(__DIR__, 3) . '/includes/config.php';
require_once dirname(__DIR__, 3) . '/includes/session.php';
require_once dirname(__DIR__, 3) . '/includes/mail_config.php';

const CONTACTO_MAX_INTENTOS = 5;
const CONTACTO_VENTANA_SEGUNDOS = 600;
const CONTACTO_TIEMPO_MINIMO = 3;

function registrarErrorContacto(string $mensaje): void
{
    $archivo = dirname(__DIR__, 3) . '/logs/contact.log';
    $linea = '[' . date('Y-m-d H:i:s') . '] ' . str_replace(["\r", "\n"], ' ', $mensaje) . PHP_EOL;
    error_log($linea, 3, $archivo);
}

function volverContacto(string $tipo, string $mensaje, array $datos = []): never
{
    $_SESSION['contacto_flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
    if ($tipo === 'error') $_SESSION['contacto_datos'] = $datos;
    header('Location: ' . atenea_url('src/website/contact.php'));
    exit;
}

function verificarRecaptcha(string $token, string $secreto, string $ip): bool
{
    if ($token === '' || $secreto === '') return false;
    $campos = http_build_query(['secret' => $secreto, 'response' => $token, 'remoteip' => $ip]);

    if (function_exists('curl_init')) {
        $curl = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $campos,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $respuesta = curl_exec($curl);
        $estado = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        if (!is_string($respuesta) || $estado !== 200) return false;
    } else {
        $contexto = stream_context_create(['http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => $campos, 'timeout' => 10]]);
        $respuesta = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $contexto);
        if (!is_string($respuesta)) return false;
    }

    $resultado = json_decode($respuesta, true);
    return is_array($resultado) && ($resultado['success'] ?? false) === true;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . atenea_url('src/website/contact.php'));
    exit;
}

$nombre = trim(strip_tags((string) ($_POST['name'] ?? '')));
$correo = strtolower(trim((string) ($_POST['email'] ?? '')));
$asunto = trim(strip_tags((string) ($_POST['subject'] ?? '')));
$mensaje = trim(strip_tags((string) ($_POST['message'] ?? '')));
$datos = ['name' => $nombre, 'email' => $correo, 'subject' => $asunto, 'message' => $mensaje];
$ip = filter_var($_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP) ?: 'no-disponible';
$ahora = time();

$intentos = array_values(array_filter(
    is_array($_SESSION['contacto_intentos'] ?? null) ? $_SESSION['contacto_intentos'] : [],
    static fn($momento): bool => is_int($momento) && $momento >= $ahora - CONTACTO_VENTANA_SEGUNDOS
));
if (count($intentos) >= CONTACTO_MAX_INTENTOS) {
    volverContacto('error', 'Has realizado demasiados intentos. Espera unos minutos.', $datos);
}
$intentos[] = $ahora;
$_SESSION['contacto_intentos'] = $intentos;

if (!validarTokenCsrf(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
    volverContacto('error', 'La solicitud expiró. Recarga la página e inténtalo nuevamente.', $datos);
}

$formularioId = (string) ($_POST['formulario_id'] ?? '');
$formularios = is_array($_SESSION['contacto_formularios'] ?? null) ? $_SESSION['contacto_formularios'] : [];
$inicioFormulario = $formularios[$formularioId] ?? null;
unset($formularios[$formularioId]);
$_SESSION['contacto_formularios'] = $formularios;
if (!is_int($inicioFormulario) || $ahora - $inicioFormulario < CONTACTO_TIEMPO_MINIMO) {
    volverContacto('error', 'No fue posible validar el envío. Espera unos segundos e inténtalo nuevamente.', $datos);
}

if (trim((string) ($_POST['website'] ?? '')) !== '') {
    registrarErrorContacto('Honeypot activado desde IP ' . $ip);
    volverContacto('exito', 'Tu mensaje fue enviado correctamente.');
}

$errores = [];
if ($nombre === '' || mb_strlen($nombre) > 100) $errores[] = 'Ingresa un nombre válido.';
if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 190) $errores[] = 'Ingresa un correo electrónico válido.';
if ($asunto === '' || mb_strlen($asunto) > 150) $errores[] = 'Ingresa un asunto válido.';
if ($mensaje === '' || mb_strlen($mensaje) > 5000) $errores[] = 'Ingresa un mensaje válido.';
if (preg_match('/[\r\n]/', $nombre . $correo . $asunto)) $errores[] = 'Los datos contienen caracteres no permitidos.';
if ($errores) volverContacto('error', implode(' ', $errores), $datos);

$configuracion = configuracionCorreoAtenea();
$autoload = dirname(__DIR__, 3) . '/includes/mail/vendor/autoload.php';
if (!configuracionContactoCompleta($configuracion) || !is_file($autoload)) {
    registrarErrorContacto('Configuración SMTP, CAPTCHA o autoload incompleta.');
    volverContacto('error', 'No fue posible enviar el mensaje. Inténtalo nuevamente.', $datos);
}

$captcha = (string) ($_POST['g-recaptcha-response'] ?? '');
if (!verificarRecaptcha($captcha, (string) $configuracion['recaptcha_secret_key'], $ip)) {
    volverContacto('error', 'Completa correctamente el CAPTCHA.', $datos);
}

$huella = hash('sha256', $correo . "\n" . $asunto . "\n" . $mensaje);
$ultimoEnvio = is_array($_SESSION['contacto_ultimo_envio'] ?? null) ? $_SESSION['contacto_ultimo_envio'] : [];
if (($ultimoEnvio['huella'] ?? '') === $huella && (int) ($ultimoEnvio['momento'] ?? 0) >= $ahora - 120) {
    volverContacto('error', 'Este mensaje ya fue enviado. Espera unos minutos antes de repetirlo.', $datos);
}

require $autoload;

try {
    $correoSmtp = new PHPMailer(true);
    $correoSmtp->isSMTP();
    $correoSmtp->Host = 'smtp.gmail.com';
    $correoSmtp->Port = 587;
    $correoSmtp->SMTPAuth = true;
    $correoSmtp->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $correoSmtp->Username = (string) $configuracion['smtp_user'];
    $correoSmtp->Password = (string) $configuracion['smtp_app_password'];
    $correoSmtp->Timeout = 15;
    $correoSmtp->CharSet = PHPMailer::CHARSET_UTF8;
    $correoSmtp->setFrom((string) $configuracion['smtp_user'], 'Atenea - Formulario web');
    $correoSmtp->addAddress((string) $configuracion['recipient']);
    $correoSmtp->addReplyTo($correo, $nombre);
    $correoSmtp->Subject = '[Contacto Atenea] ' . $asunto;

    $nombreHtml = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
    $correoHtml = htmlspecialchars($correo, ENT_QUOTES, 'UTF-8');
    $asuntoHtml = htmlspecialchars($asunto, ENT_QUOTES, 'UTF-8');
    $mensajeHtml = nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'));
    $fecha = date('d/m/Y H:i:s');
    $correoSmtp->isHTML(true);
    $correoSmtp->Body = "<h2>Nuevo mensaje desde Atenea</h2><p><strong>Nombre:</strong> {$nombreHtml}</p><p><strong>Correo:</strong> {$correoHtml}</p><p><strong>Asunto:</strong> {$asuntoHtml}</p><p><strong>Mensaje:</strong><br>{$mensajeHtml}</p><p><strong>Fecha y hora de El Salvador:</strong> {$fecha}</p><p><strong>IP:</strong> " . htmlspecialchars($ip, ENT_QUOTES, 'UTF-8') . '</p>';
    $correoSmtp->AltBody = "Nuevo mensaje desde Atenea\nNombre: {$nombre}\nCorreo: {$correo}\nAsunto: {$asunto}\nMensaje:\n{$mensaje}\nFecha y hora de El Salvador: {$fecha}\nIP: {$ip}";
    $correoSmtp->send();

    $_SESSION['contacto_ultimo_envio'] = ['huella' => $huella, 'momento' => $ahora];
    unset($_SESSION['contacto_datos']);
    volverContacto('exito', 'Tu mensaje fue enviado correctamente.');
} catch (MailException $e) {
    registrarErrorContacto('PHPMailer: ' . $e->getMessage());
    volverContacto('error', 'No fue posible enviar el mensaje. Inténtalo nuevamente.', $datos);
} catch (Throwable $e) {
    registrarErrorContacto('Contacto: ' . $e->getMessage());
    volverContacto('error', 'No fue posible enviar el mensaje. Inténtalo nuevamente.', $datos);
}
