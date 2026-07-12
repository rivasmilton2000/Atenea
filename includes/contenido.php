<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config.php';

function urlContenidoSegura(?string $url, string $fallback = '#'): string
{
    $url = trim((string) $url);
    if ($url === '') return $fallback;
    if (preg_match('/^(?:javascript|data|vbscript):/i', $url)) return $fallback;
    if (preg_match('#^https?://#i', $url)) {
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : $fallback;
    }
    if ($url === '#') return '#';
    if (str_starts_with($url, '#')) return preg_match('/^#[A-Za-z][\w-]*$/', $url) ? $url : $fallback;
    return preg_match('~^[A-Za-z0-9_./?=&%#-]+$~', $url) ? atenea_url($url) : $fallback;
}

function rutaImagenContenido(?string $ruta, string $fallback = 'src/website/assets/img/about.jpg'): string
{
    $ruta = ltrim(str_replace('\\', '/', trim((string) $ruta)), '/');
    if ($ruta === '' || str_contains($ruta, '..') || !is_file(ATENEA_ROOT . '/' . $ruta)) $ruta = $fallback;
    return atenea_url($ruta);
}

function cargarContenidoInicio(): array
{
    $pdo = obtenerConexion();
    $config = [];
    foreach ($pdo->query('SELECT clave, valor FROM configuracion_sitio')->fetchAll() as $fila) {
        $config[$fila['clave']] = $fila['valor'];
    }
    $secciones = $pdo->query('SELECT * FROM secciones WHERE activo = 1 ORDER BY orden, id')->fetchAll();
    $elementos = $pdo->query('SELECT e.* FROM elementos_seccion e INNER JOIN secciones s ON s.id=e.seccion_id WHERE s.activo=1 AND e.activo=1 ORDER BY e.seccion_id,e.orden,e.id')->fetchAll();
    $porSeccion = [];
    foreach ($elementos as $elemento) $porSeccion[(int) $elemento['seccion_id']][] = $elemento;
    foreach ($secciones as &$seccion) $seccion['elementos'] = $porSeccion[(int) $seccion['id']] ?? [];
    unset($seccion);
    return ['configuracion' => $config, 'secciones' => $secciones];
}

function obtenerConfiguracionSitio(): array
{
    try {
        $filas = obtenerConexion()->query('SELECT clave, valor FROM configuracion_sitio')->fetchAll();
        $config = [];
        foreach ($filas as $fila) $config[$fila['clave']] = $fila['valor'];
        return $config;
    } catch (Throwable $e) {
        error_log('Configuración Atenea: ' . $e->getMessage());
        return [];
    }
}

function obtenerMenuSitio(): array
{
    try {
        return obtenerConexion()->query('SELECT texto,url,nueva_pestana FROM menu_sitio WHERE activo=1 ORDER BY orden,id')->fetchAll();
    } catch (Throwable $e) {
        error_log('Menú Atenea: ' . $e->getMessage());
        return [];
    }
}
