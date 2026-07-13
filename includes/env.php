<?php
declare(strict_types=1);

function cargarEntornoAtenea(?string $archivo = null): void
{
    static $cargado = false;
    if ($cargado) return;
    $cargado = true;

    $archivo ??= dirname(__DIR__) . '/.env';
    if (!is_file($archivo) || !is_readable($archivo)) return;

    foreach (file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $linea) {
        $linea = ltrim(trim($linea), "\xEF\xBB\xBF");
        if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) continue;
        [$clave, $valor] = array_map('trim', explode('=', $linea, 2));
        if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $clave) || getenv($clave) !== false) continue;
        if (strlen($valor) >= 2 && (($valor[0] === '"' && str_ends_with($valor, '"')) || ($valor[0] === "'" && str_ends_with($valor, "'")))) {
            $valor = substr($valor, 1, -1);
        }
        putenv($clave . '=' . $valor);
        $_ENV[$clave] = $valor;
    }
}

function entornoAtenea(string $clave, string $predeterminado = ''): string
{
    cargarEntornoAtenea();
    $valor = getenv($clave);
    return $valor === false ? $predeterminado : trim((string) $valor);
}
