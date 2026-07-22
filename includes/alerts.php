<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';

/**
 * Guarda un mensaje flash compatible con todas las plantillas de Atenea.
 */
function ateneaFlash(string $type, string $title, string $message): void
{
    $_SESSION['flash'] = compact('type', 'title', 'message');
}

function ateneaObtenerFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? ateneaNormalizarAlerta($flash) : null;
}

function ateneaNormalizarAlerta(?array $flash): ?array
{
    if (!$flash) return null;

    $type = strtolower((string) ($flash['type'] ?? $flash['tipo'] ?? 'info'));
    $type = match ($type) {
        'exito', 'success' => 'success',
        'error', 'danger' => 'error',
        'advertencia', 'warning' => 'warning',
        default => 'info',
    };
    $message = trim((string) ($flash['message'] ?? $flash['mensaje'] ?? ''));
    if ($message === '') return null;

    $defaultTitles = [
        'success' => 'Operación completada',
        'error' => 'No fue posible completar la operación',
        'warning' => 'Atención',
        'info' => 'Información',
    ];

    $title = trim((string) ($flash['title'] ?? $flash['titulo'] ?? ''));
    return [
        'type' => $type,
        'title' => $title !== '' ? $title : $defaultTitles[$type],
        'message' => $message,
    ];
}

function ateneaAlertasHead(?string $area = null): void
{
    require_once __DIR__ . '/personalizacion_visual.php';
    if (!in_array($area, ['dashboard', 'estudiantes', 'docente', 'website'], true)) {
        $ruta = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
        $rutaCompartida = str_contains($ruta, '/src/comunicaciones/') || str_contains($ruta, '/src/notificaciones/');
        $rol = (string)($_SESSION['usuario_rol'] ?? '');
        if ($rutaCompartida && in_array($rol, ['admin', 'docente', 'usuario'], true)) {
            $area = ['admin' => 'dashboard', 'docente' => 'docente', 'usuario' => 'estudiantes'][$rol];
        } elseif (str_contains($ruta, '/src/dashboard/')) {
            $area = 'dashboard';
        } elseif (str_contains($ruta, '/src/estudiantes/')) {
            $area = 'estudiantes';
        } elseif (str_contains($ruta, '/src/docente/')) {
            $area = 'docente';
        } else {
            $area = 'website';
        }
    }
    renderizarPersonalizacionVisualAtenea($area);
    ?>
  <!-- SweetAlert2 se centraliza aquí para evitar cargas duplicadas y facilitar su reemplazo por archivos locales. -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="<?= atenea_url('src/shared/assets/css/atenea-theme.css') ?>">
<?php foreach (array_unique($GLOBALS['atenea_layout_styles'] ?? []) as $estilo): ?>
  <link rel="stylesheet" href="<?= atenea_url((string)$estilo) ?>">
<?php endforeach; ?>
<?php
}

function ateneaAlertasScripts(?array $flash = null): void
{
    $flash = $flash === null ? ateneaObtenerFlash() : ateneaNormalizarAlerta($flash);
    $json = json_encode($flash, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    ?>
<script type="application/json" id="atenea-flash-data"><?= $json ?></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="<?= atenea_url('src/shared/assets/js/atenea-alerts.js') ?>"></script>
<?php if(function_exists('usuarioAutenticado')&&usuarioAutenticado()):?><script src="<?=atenea_url('src/shared/assets/js/notificaciones-globales.js')?>" data-atenea-base="<?=atenea_e(ATENEA_BASE_URL)?>" defer></script><?php endif;?>
<?php
}

