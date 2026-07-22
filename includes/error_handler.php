<?php
declare(strict_types=1);

require_once __DIR__ . '/json_response.php';

function idSeguimientoAtenea(): string
{
    static $id = null;
    return $id ??= strtoupper(bin2hex(random_bytes(6)));
}

function registrarFalloGlobalAtenea(Throwable|string $error, int $status = 500): string
{
    static $registrando = false;
    $id = idSeguimientoAtenea();
    $mensaje = $error instanceof Throwable ? $error->getMessage() : $error;
    $usuario = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : 0;
    $url = mb_substr(preg_replace('/[\r\n\t]+/',' ',(string)($_SERVER['REQUEST_URI']??'CLI'))?:'CLI',0,500);
    error_log(sprintf('Atenea incidente=%s http=%d usuario=%d url=%s mensaje=%s', $id, $status, $usuario, $url, preg_replace('/[\r\n\t]+/', ' ', $mensaje)));
    if ($registrando) return $id;
    $registrando = true;
    try {
        require_once __DIR__ . '/errores_sistema.php';
        registrarErrorSistemaAtenea('sistema', 'global', $mensaje, [
            'tracking_id' => $id,
            'status' => $status,
            'uri' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
            'method' => (string) ($_SERVER['REQUEST_METHOD'] ?? ''),
            'usuario_id' => $_SESSION['usuario_id'] ?? null,
        ], $status >= 500 ? 'critico' : 'advertencia');
    } catch (Throwable $registroError) {
        error_log('Atenea no pudo persistir el error ' . $id . ': ' . $registroError->getMessage());
    } finally {
        $registrando = false;
    }
    return $id;
}

function datosPaginaErrorAtenea(int $status, string $tipo = ''): array
{
    if($tipo==='base_datos')return ['title'=>'Información temporalmente no disponible','message'=>'No podemos acceder a la información en este momento. Intenta nuevamente dentro de unos minutos.','icon'=>'bi-database-exclamation'];
    return match ($status) {
        403 => ['title' => 'Acceso denegado', 'message' => 'No tienes permiso para acceder a esta sección.', 'icon' => 'bi-shield-lock'],
        404 => ['title' => 'Página no encontrada', 'message' => 'La dirección solicitada no existe o fue trasladada.', 'icon' => 'bi-compass'],
        419 => ['title' => 'La sesión de seguridad venció', 'message' => 'Recarga la página e inténtalo nuevamente para proteger tu cuenta.', 'icon' => 'bi-hourglass-split'],
        503 => ['title' => 'Atenea está en mantenimiento', 'message' => 'Estamos realizando mejoras. Vuelve a intentarlo dentro de unos minutos.', 'icon' => 'bi-tools'],
        default => ['title' => 'No pudimos completar la solicitud', 'message' => 'Ocurrió un inconveniente interno. Nuestro equipo puede identificarlo con el código mostrado.', 'icon' => 'bi-exclamation-diamond'],
    };
}

function mostrarPaginaErrorAtenea(int $status, ?string $trackingId = null, string $tipo = ''): never
{
    $status = in_array($status, [403, 404, 419, 500, 503], true) ? $status : 500;
    $transportStatus = $status === 419 ? 403 : $status;
    $trackingId ??= idSeguimientoAtenea();
    if (solicitudEsJsonAtenea()) {
        $datos = datosPaginaErrorAtenea($status,$tipo);
        responderJsonErrorAtenea('HTTP_' . $status, $datos['message'], $transportStatus, ['tracking_id' => $trackingId]);
    }

    http_response_code($transportStatus);
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');

    $datos = datosPaginaErrorAtenea($status,$tipo);
    $inicio = atenea_url('index.php');
    $panel = null;
    if (isset($_SESSION['usuario_rol']) && in_array($_SESSION['usuario_rol'], ['admin', 'docente', 'usuario', 'administracion_docente'], true)
        && function_exists('rutaPanelPorRol')) {
        $panel = rutaPanelPorRol((string) $_SESSION['usuario_rol']);
    }
    $logo = atenea_url('img/atenea-logo.png');
    require __DIR__ . '/templates/error_page.php';
    exit;
}

function instalarManejadorErroresAtenea(): void
{
    static $instalado = false;
    if ($instalado || PHP_SAPI === 'cli') return;
    $instalado = true;
    ini_set('display_errors', '0');

    set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) return false;
        registrarFalloGlobalAtenea($message, 500);
        return true;
    });
    set_exception_handler(static function (Throwable $error): void {
        $id = registrarFalloGlobalAtenea($error, 500);
        if (!headers_sent()) mostrarPaginaErrorAtenea(500, $id);
        exit;
    });
    register_shutdown_function(static function (): void {
        $ultimo = error_get_last();
        if (!$ultimo || !in_array($ultimo['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) return;
        $id = registrarFalloGlobalAtenea((string) $ultimo['message'], 500);
        if (!headers_sent()) mostrarPaginaErrorAtenea(500, $id);
    });
}

instalarManejadorErroresAtenea();
