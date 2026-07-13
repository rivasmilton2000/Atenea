<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/cms.php';

final class CategoriaOperacionException extends RuntimeException {}

function categoriaSlugBase(string $nombre): string
{
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombre);
    $slug = strtolower((string)($ascii !== false ? $ascii : $nombre));
    $slug = trim((string)preg_replace('/[^a-z0-9]+/', '-', $slug), '-');
    return mb_substr($slug !== '' ? $slug : 'categoria', 0, 120);
}

function categoriaSlugUnico(PDO $pdo, string $nombre, int $excluirId = 0): string
{
    $base = categoriaSlugBase($nombre);
    $slug = $base;
    $numero = 2;
    $consulta = $pdo->prepare('SELECT id FROM categorias_producto WHERE slug=:slug AND id<>:id LIMIT 1');
    while (true) {
        $consulta->execute(['slug' => $slug, 'id' => $excluirId]);
        if (!$consulta->fetch()) return $slug;
        $sufijo = '-' . $numero++;
        $slug = mb_substr($base, 0, 140 - strlen($sufijo)) . $sufijo;
    }
}

function categoriaNombreDuplicado(PDO $pdo, string $nombre, int $excluirId = 0): bool
{
    $consulta = $pdo->prepare('SELECT id FROM categorias_producto WHERE nombre=:nombre AND id<>:id AND eliminado_at IS NULL LIMIT 1');
    $consulta->execute(['nombre' => $nombre, 'id' => $excluirId]);
    return (bool)$consulta->fetch();
}
