<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/website_versionado.php';

function normalizarSlugNoticia(string $valor): string
{
    $valor = trim(mb_strtolower($valor));
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor);
    $valor = strtolower((string) ($ascii === false ? $valor : $ascii));
    $valor = trim((string) preg_replace('/[^a-z0-9]+/', '-', $valor), '-');
    return substr($valor, 0, 190);
}

function slugNoticiaValido(string $slug): bool
{
    return strlen($slug) <= 190 && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) === 1;
}

function obtenerNoticiasPublicadas(int $limite = 0): array
{
    $limite = max(0, min($limite, 100));
    $filas=array_values(array_filter(filasEstadoWebsite('noticias'),fn($n)=>$n['estado']==='publicado'&&(int)$n['activo']===1&&empty($n['deleted_at'])&&!empty($n['fecha_publicacion'])&&strtotime($n['fecha_publicacion'])<=time()));usort($filas,fn($a,$b)=>[(int)$b['destacado'],strtotime($b['fecha_publicacion']),(int)$b['id']]<=>[(int)$a['destacado'],strtotime($a['fecha_publicacion']),(int)$a['id']]);return$limite>0?array_slice($filas,0,$limite):$filas;
}

function obtenerNoticiaPublicada(string $slug): ?array
{
    if (!slugNoticiaValido($slug)) return null;
    foreach(obtenerNoticiasPublicadas() as$noticia)if($noticia['slug']===$slug)return$noticia;return null;
}

function urlNoticia(array $noticia): string
{
    return atenea_url('src/website/noticia.php?slug=' . rawurlencode((string) ($noticia['slug'] ?? '')));
}

function fechaNoticia(?string $fecha): string
{
    if (!$fecha) return '';
    try {
        return (new DateTimeImmutable($fecha))->format('d/m/Y');
    } catch (Throwable) {
        return '';
    }
}
