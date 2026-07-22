<?php
declare(strict_types=1);

require_once __DIR__ . '/notificaciones.php';
require_once __DIR__ . '/mailer.php';

/**
 * Crea la alerta de campana y encola una sola copia para el correo administrativo.
 * Las dos salidas comparten el identificador del evento para hacer la operacion idempotente.
 */
final class AdminNotificationService
{
    public function __construct(private ?PDO $pdo = null)
    {
        $this->pdo ??= obtenerConexion();
    }

    public function notify(
        string $eventType,
        string $title,
        string $message,
        string $level,
        ?int $relatedUserId,
        ?string $relatedUrl,
        string $eventId,
        array $context = []
    ): bool {
        $eventId = mb_substr(trim($eventId), 0, 150);
        if ($eventId === '') throw new InvalidArgumentException('El evento administrativo requiere un identificador unico.');

        $eventType = mb_substr(trim($eventType) ?: 'sistema', 0, 50);
        $title = mb_substr(trim($title) ?: 'Aviso administrativo', 0, 180);
        $message = mb_substr(trim($message), 0, 2000);
        $level = in_array($level, ['informacion', 'advertencia', 'error', 'critico'], true) ? $level : 'informacion';
        $noticeLevel = $level === 'critico' ? 'error' : $level;
        $category = mb_substr((string)($context['category'] ?? 'sistema'), 0, 50);

        if (empty($context['skip_internal'])) crearNotificacionAtenea([
            'rol' => 'admin',
            'created_by' => $context['created_by'] ?? null,
            'tipo' => $eventType,
            'categoria' => $category,
            'nivel' => $noticeLevel,
            'titulo' => $title,
            'descripcion' => $message,
            'url' => $relatedUrl,
            'pedido_id' => $context['pedido_id'] ?? null,
            'hilo_id' => $context['hilo_id'] ?? null,
            'error_id' => $context['error_id'] ?? null,
            'idempotency_key' => $eventId,
        ], $this->pdo);

        $recipient = MailConfig::contactRecipient();
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            error_log('Alerta administrativa ' . $eventId . ': destinatario administrativo no configurado.');
            return false;
        }

        $absoluteUrl = '';
        if ($relatedUrl) {
            if (preg_match('#^https?://#i', $relatedUrl)) $absoluteUrl = $relatedUrl;
            else {
                $relativeUrl = $relatedUrl;
                $basePath = rtrim(ATENEA_BASE_URL, '/');
                if ($basePath !== '' && str_starts_with($relativeUrl, $basePath . '/')) {
                    $relativeUrl = substr($relativeUrl, strlen($basePath) + 1);
                }
                $absoluteUrl = atenea_url_absoluta(ltrim($relativeUrl, '/'));
            }
        }
        $template = (string)($context['email_template'] ?? 'aviso_administrativo');
        $emailData = is_array($context['email_data'] ?? null) ? $context['email_data'] : [
            'asunto' => '[' . strtoupper($level) . '] ' . $title,
            'resumen' => $message,
            'mensaje' => $message
                . ($relatedUserId ? "\n\nUsuario relacionado: #" . $relatedUserId : '')
                . ($absoluteUrl !== '' ? "\nReferencia: " . $absoluteUrl : ''),
            'enlace' => $absoluteUrl,
            'texto_boton' => $absoluteUrl !== '' ? 'Abrir en el dashboard' : '',
        ];

        try {
            enviarPlantillaCorreoAtenea($template, $recipient, 'Administracion Atenea', $emailData, [
                'categoria' => $category,
                'evento_id' => 'admin-alert:' . $eventId,
                'idempotency_key' => 'admin-alert:' . $eventId,
                'reply_to' => $context['reply_to'] ?? null,
                'reply_to_name' => $context['reply_to_name'] ?? null,
                'pedido_id' => $context['pedido_id'] ?? null,
                'hilo_id' => $context['hilo_id'] ?? null,
            ]);
            return true;
        } catch (Throwable $error) {
            error_log('Alerta administrativa ' . $eventId . ': ' . sanitizarErrorCorreoAtenea($error));
            return false;
        }
    }
}

function notificarAdministracionAtenea(
    string $eventType,
    string $title,
    string $message,
    string $level,
    ?int $relatedUserId,
    ?string $relatedUrl,
    string $eventId,
    array $context = [],
    ?PDO $pdo = null
): bool {
    return (new AdminNotificationService($pdo))->notify(
        $eventType, $title, $message, $level, $relatedUserId, $relatedUrl, $eventId, $context
    );
}
