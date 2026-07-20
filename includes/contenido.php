<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/noticias.php';
require_once __DIR__ . '/website_versionado.php';
require_once __DIR__ . '/navbar_contenido.php';

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
    $estado = estadoFuenteWebsite();
    $config = [];
    foreach (($estado['configuracion_sitio'] ?? []) as $fila) {
        $config[$fila['clave']] = $fila['valor'];
    }
    $secciones = array_values(array_filter($estado['secciones'] ?? [],fn($s)=>(int)$s['activo']===1));usort($secciones,fn($a,$b)=>[(int)$a['orden'],(int)$a['id']]<=>[(int)$b['orden'],(int)$b['id']]);$seccionesActivas=array_fill_keys(array_map(fn($s)=>(int)$s['id'],$secciones),true);
    $elementos = array_values(array_filter($estado['elementos_seccion'] ?? [],fn($e)=>(int)$e['activo']===1&&isset($seccionesActivas[(int)$e['seccion_id']])));usort($elementos,fn($a,$b)=>[(int)$a['seccion_id'],(int)$a['orden'],(int)$a['id']]<=>[(int)$b['seccion_id'],(int)$b['orden'],(int)$b['id']]);
    $porSeccion = [];
    foreach ($elementos as $elemento) $porSeccion[(int) $elemento['seccion_id']][] = $elemento;
    foreach ($secciones as &$seccion) {
        $seccion['elementos'] = $porSeccion[(int) $seccion['id']] ?? [];
        if ($seccion['clave'] === 'areas') $seccion['elementos'] = array_slice($seccion['elementos'], 0, 4);
    }
    unset($seccion);
    return ['configuracion' => $config, 'secciones' => $secciones, 'noticias' => obtenerNoticiasPublicadas(3)];
}

function obtenerConfiguracionSitio(): array
{
    try {
        $filas = filasEstadoWebsite('configuracion_sitio');
        $config = [];
        foreach ($filas as $fila) $config[$fila['clave']] = $fila['valor'];
        return $config;
    } catch (Throwable $e) {
        error_log('Configuración Atenea: ' . $e->getMessage());
        return [];
    }
}

function obtenerSeccionPublica(string $clave): ?array
{
    if (preg_match('/^[a-z0-9_-]{1,100}$/', $clave) !== 1) return null;
    foreach(filasEstadoWebsite('secciones') as$seccion)if($seccion['clave']===$clave&&(int)$seccion['activo']===1)return$seccion;return null;
}

function obtenerMenuSitio(): array
{
    $menuPredeterminado = [
        ['texto' => 'Inicio', 'url' => 'index.php', 'nueva_pestana' => 0],
        ['texto' => 'Nosotros', 'url' => 'src/website/about.php', 'nueva_pestana' => 0],
        ['texto' => 'Capacitaciones', 'url' => 'src/website/courses.php', 'nueva_pestana' => 0],
        ['texto' => 'Docentes', 'url' => 'src/website/trainers.php', 'nueva_pestana' => 0],
        ['texto' => 'Eventos', 'url' => 'src/website/events.php', 'nueva_pestana' => 0],
        ['texto' => 'Productos', 'url' => 'src/website/pricing.php', 'nueva_pestana' => 0],
        ['texto' => 'Noticias', 'url' => 'src/website/noticias.php', 'nueva_pestana' => 0],
        ['texto' => 'Contacto', 'url' => 'src/website/contact.php', 'nueva_pestana' => 0],
    ];

    try {
        $autenticado = function_exists('usuarioAutenticado') ? usuarioAutenticado() : !empty($_SESSION['usuario_id']);
        $rol = $autenticado ? (string)($_SESSION['usuario_rol'] ?? '') : '';
        $filasMenu = filasEstadoWebsite('menu_sitio');
        if (!$filasMenu) return $menuPredeterminado;
        $menu = array_values(array_filter($filasMenu, static function($m) use ($autenticado, $rol): bool {
            if ((int)($m['activo'] ?? 0) !== 1 || !empty($m['eliminado_at'])) return false;
            if (($m['visibilidad'] ?? 'publica') === 'autenticada' && !$autenticado) return false;
            $roles = datosContenidoNavbarAtenea($m['roles_json'] ?? null);
            return !$roles || ($autenticado && in_array($rol, $roles, true));
        }));
        usort($menu,fn($a,$b)=>[(int)$a['orden'],(int)$a['id']]<=>[(int)$b['orden'],(int)$b['id']]);
        if (!$menu) return [];
        $porPadre=[]; foreach($menu as $m)$porPadre[(int)($m['padre_id']??0)][]=$m;
        $armar = function(int $padre) use (&$armar, &$porPadre): array {
            $salida=[]; foreach($porPadre[$padre]??[] as$m){$m['nueva_pestana']=(int)$m['nueva_pestana'];$m['icono']=iconoNavbarValidoAtenea((string)($m['icono']??''))?(string)$m['icono']:'';$m['hijos']=$armar((int)$m['id']);$salida[]=$m;} return$salida;
        };
        return $armar(0);
    } catch (Throwable $e) {
        error_log('Menú Atenea: ' . $e->getMessage());
        return $menuPredeterminado;
    }
}
