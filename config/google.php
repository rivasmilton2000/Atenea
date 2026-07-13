<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/env.php';
require_once dirname(__DIR__) . '/includes/config.php';

$configuracionLocal = [];
$archivoLocal = __DIR__ . '/google.local.php';
if (is_file($archivoLocal)) {
    $datosLocales = require $archivoLocal;
    if (is_array($datosLocales)) {
        $configuracionLocal = $datosLocales;
    }
}

$valor = static function (string $variable, string $clave) use ($configuracionLocal): string {
    $entorno = entornoAtenea($variable);
    return $entorno !== '' ? $entorno : trim((string) ($configuracionLocal[$clave] ?? ''));
};

$redirectConfigurado = $valor('GOOGLE_REDIRECT_URI', 'redirect_uri');

return [
    'client_id' => $valor('GOOGLE_CLIENT_ID', 'client_id'),
    'client_secret' => $valor('GOOGLE_CLIENT_SECRET', 'client_secret'),
    'redirect_uri' => $redirectConfigurado !== ''
        ? $redirectConfigurado
        : atenea_url_absoluta('src/auth/google-callback.php'),
];
