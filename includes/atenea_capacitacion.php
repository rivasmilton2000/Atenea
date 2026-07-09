<?php

require_once __DIR__ . '/atenea_auth.php';

if (!function_exists('atenea_capacitacion_type_options')) {
    function atenea_capacitacion_type_options(): array
    {
        return [
            'curso' => 'Curso',
            'certificacion' => 'Certificación',
        ];
    }
}

if (!function_exists('atenea_capacitacion_normalize_type')) {
    function atenea_capacitacion_normalize_type(?string $type): string
    {
        $normalized = strtolower(trim((string) $type));
        $options = atenea_capacitacion_type_options();

        return array_key_exists($normalized, $options) ? $normalized : 'curso';
    }
}

if (!function_exists('atenea_capacitacion_type_label')) {
    function atenea_capacitacion_type_label(?string $type): string
    {
        $normalized = atenea_capacitacion_normalize_type($type);
        $options = atenea_capacitacion_type_options();

        return $options[$normalized] ?? $options['curso'];
    }
}

if (!function_exists('atenea_capacitacion_schema_flags')) {
    function atenea_capacitacion_schema_flags(mysqli $db): array
    {
        static $cache = [];
        $cacheKey = spl_object_hash($db);

        if (!isset($cache[$cacheKey])) {
            $cache[$cacheKey] = [
                'tipo_programa' => atenea_db_has_column($db, 'programas_educativos', 'tipo_programa'),
                'precio' => atenea_db_has_column($db, 'programas_educativos', 'precio'),
                'duracion' => atenea_db_has_column($db, 'programas_educativos', 'duracion'),
                'modalidad' => atenea_db_has_column($db, 'programas_educativos', 'modalidad'),
                'detalles_programa' => atenea_db_has_column($db, 'programas_educativos', 'detalles_programa'),
                'beneficios' => atenea_db_has_column($db, 'programas_educativos', 'beneficios'),
                'requisitos' => atenea_db_has_column($db, 'programas_educativos', 'requisitos'),
            ];
        }

        return $cache[$cacheKey];
    }
}

if (!function_exists('atenea_capacitacion_select_sql')) {
    function atenea_capacitacion_select_sql(mysqli $db, string $alias = 'pe'): string
    {
        $flags = atenea_capacitacion_schema_flags($db);
        $safeAlias = preg_replace('/[^a-zA-Z0-9_]/', '', $alias) ?: 'pe';

        $selects = [
            $flags['tipo_programa']
                ? "{$safeAlias}.tipo_programa AS tipo_programa"
                : "'curso' AS tipo_programa",
            $flags['precio']
                ? "{$safeAlias}.precio AS precio"
                : '100.00 AS precio',
            $flags['duracion']
                ? "{$safeAlias}.duracion AS duracion"
                : "'' AS duracion",
            $flags['modalidad']
                ? "{$safeAlias}.modalidad AS modalidad"
                : "'' AS modalidad",
            $flags['detalles_programa']
                ? "{$safeAlias}.detalles_programa AS detalles_programa"
                : "'' AS detalles_programa",
            $flags['beneficios']
                ? "{$safeAlias}.beneficios AS beneficios"
                : "'' AS beneficios",
            $flags['requisitos']
                ? "{$safeAlias}.requisitos AS requisitos"
                : "'' AS requisitos",
        ];

        return implode(",\n               ", $selects);
    }
}

if (!function_exists('atenea_capacitacion_price')) {
    function atenea_capacitacion_price(array $programa): float
    {
        $price = $programa['precio'] ?? 100;

        return is_numeric($price) ? (float) $price : 100.0;
    }
}

if (!function_exists('atenea_capacitacion_text_value')) {
    function atenea_capacitacion_text_value($value): string
    {
        return trim((string) $value);
    }
}

if (!function_exists('atenea_capacitacion_text_items')) {
    function atenea_capacitacion_text_items($value): array
    {
        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", $value);
        $lines = preg_split('/\n+/', $normalized) ?: [];
        $items = [];

        foreach ($lines as $line) {
            $line = trim((string) preg_replace('/^[\-\*\x{2022}\d\.\)\s]+/u', '', (string) $line));
            if ($line !== '') {
                $items[] = $line;
            }
        }

        return $items;
    }
}

if (!function_exists('atenea_capacitacion_detail_url')) {
    function atenea_capacitacion_detail_url(int $programId): string
    {
        return 'programa_detalle.php?id=' . $programId;
    }
}

if (!function_exists('atenea_capacitacion_quote_url')) {
    function atenea_capacitacion_quote_url(int $programId): string
    {
        return 'programa_cotizar.php?id=' . $programId;
    }
}

if (!function_exists('atenea_capacitacion_login_quote_url')) {
    function atenea_capacitacion_login_quote_url(int $programId): string
    {
        return atenea_build_login_url(atenea_capacitacion_detail_url($programId), 'quote_required');
    }
}
