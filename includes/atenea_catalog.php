<?php

require_once __DIR__ . '/atenea_auth.php';

if (!function_exists('atenea_catalog_type_options')) {
    function atenea_catalog_type_options(): array
    {
        return [
            'producto' => 'Producto',
            'curso' => 'Curso',
            'certificacion' => 'Certificación',
        ];
    }
}

if (!function_exists('atenea_catalog_normalize_type')) {
    function atenea_catalog_normalize_type(?string $type): string
    {
        $type = strtolower(trim((string) $type));
        $options = atenea_catalog_type_options();

        return array_key_exists($type, $options) ? $type : 'producto';
    }
}

if (!function_exists('atenea_catalog_type_label')) {
    function atenea_catalog_type_label(?string $type): string
    {
        $type = atenea_catalog_normalize_type($type);
        $options = atenea_catalog_type_options();

        return $options[$type] ?? $options['producto'];
    }
}

if (!function_exists('atenea_catalog_product_schema_flags')) {
    function atenea_catalog_product_schema_flags(mysqli $db): array
    {
        static $cache = [];
        $cacheKey = spl_object_hash($db);

        if (!isset($cache[$cacheKey])) {
            $cache[$cacheKey] = [
                'tipo_oferta' => atenea_db_has_column($db, 'productos', 'tipo_oferta'),
                'duracion' => atenea_db_has_column($db, 'productos', 'duracion'),
                'video_url' => atenea_db_has_column($db, 'productos', 'video_url'),
                'video_activo' => atenea_db_has_column($db, 'productos', 'video_activo'),
            ];
        }

        return $cache[$cacheKey];
    }
}

if (!function_exists('atenea_catalog_product_select_sql')) {
    function atenea_catalog_product_select_sql(mysqli $db, string $alias = 'p'): string
    {
        $flags = atenea_catalog_product_schema_flags($db);
        $safeAlias = preg_replace('/[^a-zA-Z0-9_]/', '', $alias) ?: 'p';

        $selects = [
            $flags['tipo_oferta']
                ? "{$safeAlias}.tipo_oferta AS tipo_oferta"
                : "'producto' AS tipo_oferta",
            $flags['duracion']
                ? "{$safeAlias}.duracion AS duracion"
                : "'' AS duracion",
            $flags['video_url']
                ? "{$safeAlias}.video_url AS video_url"
                : "'' AS video_url",
            $flags['video_activo']
                ? "{$safeAlias}.video_activo AS video_activo"
                : '0 AS video_activo',
        ];

        return implode(",\n        ", $selects);
    }
}

if (!function_exists('atenea_catalog_type_filter_sql')) {
    function atenea_catalog_type_filter_sql(mysqli $db, string $type, string $alias = 'p'): string
    {
        $flags = atenea_catalog_product_schema_flags($db);
        if (!$flags['tipo_oferta']) {
            return '';
        }

        $normalized = atenea_catalog_normalize_type($type);
        if ($normalized === 'producto' && trim($type) === '') {
            return '';
        }

        $safeAlias = preg_replace('/[^a-zA-Z0-9_]/', '', $alias) ?: 'p';

        return " AND {$safeAlias}.tipo_oferta = '" . mysqli_real_escape_string($db, $normalized) . "'";
    }
}

if (!function_exists('atenea_catalog_extract_youtube_id')) {
    function atenea_catalog_extract_youtube_id(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url) === 1) {
            return $url;
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = trim((string) ($parts['path'] ?? ''), '/');

        if ($host === 'youtu.be' && $path !== '') {
            return substr($path, 0, 11);
        }

        if (strpos($host, 'youtube.com') !== false || strpos($host, 'youtube-nocookie.com') !== false) {
            parse_str((string) ($parts['query'] ?? ''), $query);
            if (!empty($query['v']) && preg_match('/^[a-zA-Z0-9_-]{11}$/', (string) $query['v']) === 1) {
                return (string) $query['v'];
            }

            foreach (['embed/', 'shorts/', 'live/'] as $needle) {
                $position = strpos($path, $needle);
                if ($position !== false) {
                    $value = substr($path, $position + strlen($needle), 11);
                    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $value) === 1) {
                        return $value;
                    }
                }
            }
        }

        return null;
    }
}

if (!function_exists('atenea_catalog_normalize_video_url')) {
    function atenea_catalog_normalize_video_url(?string $url): string
    {
        $youtubeId = atenea_catalog_extract_youtube_id($url);

        return $youtubeId !== null ? 'https://www.youtube.com/watch?v=' . $youtubeId : '';
    }
}

if (!function_exists('atenea_catalog_video_embed_url')) {
    function atenea_catalog_video_embed_url(?string $url): string
    {
        $youtubeId = atenea_catalog_extract_youtube_id($url);

        return $youtubeId !== null ? 'https://www.youtube.com/embed/' . $youtubeId : '';
    }
}

if (!function_exists('atenea_catalog_has_active_video')) {
    function atenea_catalog_has_active_video(array $item): bool
    {
        return !empty($item['video_activo']) && atenea_catalog_video_embed_url((string) ($item['video_url'] ?? '')) !== '';
    }
}

if (!function_exists('atenea_catalog_stock_label')) {
    function atenea_catalog_stock_label(?string $type): string
    {
        $normalized = atenea_catalog_normalize_type($type);

        return in_array($normalized, ['curso', 'certificacion'], true) ? 'Cupos disponibles' : 'Cantidad disponible';
    }
}

if (!function_exists('atenea_catalog_out_of_stock_label')) {
    function atenea_catalog_out_of_stock_label(?string $type): string
    {
        $normalized = atenea_catalog_normalize_type($type);

        return in_array($normalized, ['curso', 'certificacion'], true) ? 'Sin cupos' : 'Sin stock';
    }
}

if (!function_exists('atenea_catalog_cart_requires_shipping')) {
    function atenea_catalog_cart_requires_shipping(array $items): bool
    {
        foreach ($items as $item) {
            if (atenea_catalog_normalize_type((string) ($item['tipo_oferta'] ?? 'producto')) === 'producto') {
                return true;
            }
        }

        return false;
    }
}
