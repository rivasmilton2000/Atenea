<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/env.php';
cargarEntornoAtenea();
require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__, 2) . '/contenido.php';

const ATENEA_EMAIL_LOGO_CID = 'atenea-logo';

function rutaFisicaLogoCorreoAtenea(): ?string
{
    $sitio = obtenerConfiguracionSitio();
    $candidatas = [(string)($sitio['logo'] ?? ''), 'img/atenea-logo.png'];
    $raiz = realpath(ATENEA_ROOT);
    if ($raiz === false) return null;
    $prefijoRaiz = rtrim(str_replace('\\', '/', $raiz), '/') . '/';

    foreach ($candidatas as $ruta) {
        $ruta = trim(str_replace('\\', '/', $ruta));
        if ($ruta === '' || preg_match('#^(?:https?:)?//#i', $ruta) || str_contains($ruta, '..')) continue;
        if (ATENEA_BASE_URL !== '' && str_starts_with($ruta, ATENEA_BASE_URL . '/')) {
            $ruta = substr($ruta, strlen(ATENEA_BASE_URL) + 1);
        }
        $real = realpath(ATENEA_ROOT . '/' . ltrim($ruta, '/'));
        if ($real === false || !is_file($real) || !is_readable($real)) continue;
        $normalizada = str_replace('\\', '/', $real);
        if (!str_starts_with(strtolower($normalizada), strtolower($prefijoRaiz))) continue;
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($real);
        if (in_array($mime, ['image/png','image/jpeg','image/gif','image/webp'], true)) return $real;
    }
    return null;
}

function reemplazarLogoCorreoPorTexto(string $html): string
{
    return preg_replace(
        '/<img\b[^>]*data-atenea-email-logo="1"[^>]*>/i',
        '<strong style="display:block;color:#ffffff;font-size:22px;line-height:1.3;">Atenea</strong>',
        $html
    ) ?: $html;
}

function renderizarLayoutCorreoAtenea(string $titulo, string $preencabezado, string $contenidoHtml, string $contenidoTexto): array
{
    $sitio = obtenerConfiguracionSitio();
    $nombre = (string) ($sitio['nombre_sitio'] ?? 'Atenea Escuela de Naturopatía Holística');
    $logoDisponible = rutaFisicaLogoCorreoAtenea() !== null;
    $correo = (string) ($sitio['correo'] ?? '');
    $direccion = (string) ($sitio['direccion'] ?? 'El Salvador');
    $fecha = date('d/m/Y H:i');
    $tituloHtml = atenea_e($titulo);
    $preencabezadoHtml = atenea_e($preencabezado);
    $nombreHtml = atenea_e($nombre);
    $cabeceraLogo = $logoDisponible
        ? '<img data-atenea-email-logo="1" src="cid:' . ATENEA_EMAIL_LOGO_CID . '" width="150" alt="' . $nombreHtml . '" style="display:block;margin:0 auto;max-width:150px;height:auto;border:0;outline:none;text-decoration:none;">'
        : '<strong style="display:block;color:#ffffff;font-size:22px;line-height:1.3;">' . $nombreHtml . '</strong>';
    $pie = atenea_e(trim($direccion . ($correo !== '' ? ' · ' . $correo : '')));

    $html = '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
        . '<body style="margin:0;padding:0;background:#f7f4ec;color:#20251f;font-family:Arial,Helvetica,sans-serif;">'
        . '<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">' . $preencabezadoHtml . '</div>'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f7f4ec;"><tr><td align="center" style="padding:24px 12px;">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:640px;background:#ffffff;border:1px solid #e2dccd;border-radius:12px;overflow:hidden;">'
        . '<tr><td align="center" style="padding:24px;background:#173f35;">' . $cabeceraLogo . '</td></tr>'
        . '<tr><td style="padding:32px 28px;"><h1 style="margin:0 0 20px;color:#173f35;font-size:26px;line-height:1.25;">' . $tituloHtml . '</h1>' . $contenidoHtml . '</td></tr>'
        . '<tr><td style="padding:20px 28px;background:#f7f4ec;border-top:1px solid #e2dccd;text-align:center;color:#5a625b;font-size:12px;line-height:1.6;">'
        . $nombreHtml . '<br>' . $pie . '<br>Enviado el ' . atenea_e($fecha) . ' (hora de El Salvador)</td></tr>'
        . '</table></td></tr></table></body></html>';

    return ['html' => $html, 'text' => $titulo . "\n\n" . $contenidoTexto . "\n\n" . $nombre . "\n" . $direccion . "\nEnviado el {$fecha} (hora de El Salvador)"];
}

function botonCorreoAtenea(string $texto, string $url): string
{
    if (str_starts_with($url, 'mailto:')) {
        $correo = substr($url, 7);
        if (filter_var($correo, FILTER_VALIDATE_EMAIL) === false) $url = '#';
    } elseif (filter_var($url, FILTER_VALIDATE_URL) === false || !in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)) {
        $url = '#';
    }
    return '<p style="margin:24px 0;"><a href="' . atenea_e($url) . '" style="display:inline-block;padding:13px 22px;background:#c49a3a;color:#ffffff;text-decoration:none;border-radius:7px;font-weight:700;">' . atenea_e($texto) . '</a></p>';
}
