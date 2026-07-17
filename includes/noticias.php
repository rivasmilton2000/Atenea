<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config.php';

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
    $sql = "SELECT id,titulo,slug,resumen,contenido,imagen_portada,fecha_publicacion,autor,destacado,created_at,updated_at
            FROM noticias
            WHERE estado='publicado' AND activo=1 AND deleted_at IS NULL
              AND fecha_publicacion IS NOT NULL AND fecha_publicacion<=NOW()
            ORDER BY destacado DESC,fecha_publicacion DESC,id DESC";
    if ($limite > 0) $sql .= ' LIMIT ' . $limite;
    return obtenerConexion()->query($sql)->fetchAll();
}

function obtenerNoticiaPublicada(string $slug): ?array
{
    if (!slugNoticiaValido($slug)) return null;
    $consulta = obtenerConexion()->prepare(
        "SELECT id,titulo,slug,resumen,contenido,imagen_portada,fecha_publicacion,autor,destacado,created_at,updated_at
         FROM noticias
         WHERE slug=:slug AND estado='publicado' AND activo=1 AND deleted_at IS NULL
           AND fecha_publicacion IS NOT NULL AND fecha_publicacion<=NOW()
         LIMIT 1"
    );
    $consulta->execute(['slug' => $slug]);
    $noticia = $consulta->fetch();
    return is_array($noticia) ? $noticia : null;
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
