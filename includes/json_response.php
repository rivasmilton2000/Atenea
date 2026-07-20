<?php
declare(strict_types=1);

function solicitudEsJsonAtenea(): bool
{
    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
    $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
    return str_contains($accept, 'application/json')
        || str_contains($contentType, 'application/json')
        || $requestedWith === 'xmlhttprequest';
}

function responderJsonAtenea(bool $ok, mixed $data = null, ?array $error = null, int $status = 200, array $meta = []): never
{
    if ($status < 100 || $status > 599) $status = $ok ? 200 : 500;
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: private, no-store');
    header('X-Content-Type-Options: nosniff');

    $payload = ['ok' => $ok, 'data' => $data, 'error' => $error];
    if ($meta !== []) $payload['meta'] = $meta;

    try {
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    } catch (Throwable) {
        http_response_code(500);
        echo '{"ok":false,"data":null,"error":{"code":"JSON_ENCODING_ERROR","message":"No fue posible preparar la respuesta."}}';
    }
    exit;
}

function responderJsonExitoAtenea(mixed $data = null, int $status = 200, array $meta = []): never
{
    responderJsonAtenea(true, $data, null, $status, $meta);
}

function responderJsonErrorAtenea(string $code, string $message, int $status = 400, array $details = []): never
{
    $error = ['code' => preg_replace('/[^A-Z0-9_]/', '_', strtoupper($code)) ?: 'REQUEST_ERROR', 'message' => $message];
    if ($details !== []) $error['details'] = $details;
    responderJsonAtenea(false, null, $error, $status);
}
