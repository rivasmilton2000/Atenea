<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

const ATENEA_AVATAR_FALLBACK = 'src/estudiantes/assets/images/avatars/01.png';

function rutaAvatarLocalSegura(?string $ruta): ?string
{
    $ruta = trim(str_replace('\\', '/', (string) $ruta));
    if ($ruta === '' || preg_match('#^(?:[A-Za-z]:/|/|\\\\)#', $ruta)) return null;

    $ruta = ltrim($ruta, '/');
    if (!str_starts_with($ruta, 'uploads/perfiles/') || str_contains($ruta, '..')) return null;
    if (preg_match('#[^A-Za-z0-9_./-]#', $ruta)) return null;

    $base = realpath(ATENEA_ROOT . '/uploads/perfiles');
    $archivo = realpath(ATENEA_ROOT . '/' . $ruta);
    if ($base === false || $archivo === false || !is_file($archivo)) return null;

    $baseNormalizada = strtolower(str_replace('\\', '/', $base)) . '/';
    $archivoNormalizado = strtolower(str_replace('\\', '/', $archivo));
    return str_starts_with($archivoNormalizado, $baseNormalizada) ? $ruta : null;
}

function urlAvatarAtenea(array|string|null $usuarioOFoto): string
{
    $foto = is_array($usuarioOFoto) ? ($usuarioOFoto['foto'] ?? null) : $usuarioOFoto;
    $foto = trim((string) $foto);

    if ($foto !== '' && filter_var($foto, FILTER_VALIDATE_URL)) {
        $partes = parse_url($foto);
        if (is_array($partes) && strtolower((string) ($partes['scheme'] ?? '')) === 'https'
            && empty($partes['user']) && empty($partes['pass'])) {
            return $foto;
        }
    }

    $local = rutaAvatarLocalSegura($foto);
    return atenea_url($local ?? ATENEA_AVATAR_FALLBACK);
}

function rutaFisicaAvatarAtenea(?string $ruta): ?string
{
    $local = rutaAvatarLocalSegura($ruta);
    return $local === null ? null : ATENEA_ROOT . '/' . $local;
}
