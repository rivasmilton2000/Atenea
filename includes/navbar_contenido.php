<?php
declare(strict_types=1);

function tiposContenidoNavbarAtenea(): array
{
    return [
        'pagina_informativa'=>'Página informativa','texto_enriquecido'=>'Texto enriquecido','imagenes'=>'Imágenes',
        'galeria'=>'Galería','noticias'=>'Noticias','productos'=>'Productos','capacitaciones'=>'Capacitaciones',
        'formulario'=>'Formulario','video'=>'Video','archivo_descargable'=>'Archivo descargable',
        'enlace_interno'=>'Enlace interno','enlace_externo'=>'Enlace externo','bloques_reutilizables'=>'Bloques reutilizables',
    ];
}

function slugNavbarAtenea(string $valor): string
{
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', mb_strtolower(trim($valor)));
    return mb_substr(trim((string)preg_replace('/[^a-z0-9]+/', '-', (string)($ascii === false ? $valor : $ascii)), '-'), 0, 190);
}

function iconoNavbarValidoAtenea(string $icono): bool
{
    return $icono === '' || preg_match('/^bi bi-[a-z0-9-]{1,70}$/', $icono) === 1;
}

function contieneCodigoProhibidoNavbarAtenea(string $html): bool
{
    return preg_match('/<\?(?:php|=)?|<\s*(?:script|style|iframe|object|embed|form|input|button)\b|\bon[a-z]+\s*=|(?:javascript|data|vbscript)\s*:/i', $html) === 1;
}

function sanitizarHtmlNavbarAtenea(string $html): string
{
    $html = trim($html);
    if ($html === '') return '';
    $permitidos = ['p','br','strong','b','em','i','u','s','ul','ol','li','h2','h3','h4','blockquote','a','img','figure','figcaption','hr','span'];
    $atributos = ['a'=>['href','title','target','rel'],'img'=>['src','alt','title'],'span'=>['class']];
    $dom = new DOMDocument('1.0', 'UTF-8');
    $anterior = libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?><div id="atenea-contenido">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors(); libxml_use_internal_errors($anterior);
    $raiz = $dom->getElementById('atenea-contenido');
    if (!$raiz) return '';
    $recorrer = function (DOMNode $nodo) use (&$recorrer, $permitidos, $atributos): void {
        foreach (iterator_to_array($nodo->childNodes) as $hijo) {
            if ($hijo instanceof DOMComment) { $nodo->removeChild($hijo); continue; }
            if (!($hijo instanceof DOMElement)) continue;
            $tag = strtolower($hijo->tagName);
            if (!in_array($tag, $permitidos, true)) {
                while ($hijo->firstChild) $nodo->insertBefore($hijo->firstChild, $hijo);
                $nodo->removeChild($hijo); continue;
            }
            foreach (iterator_to_array($hijo->attributes) as $attr) {
                $nombre = strtolower($attr->name);
                if (!in_array($nombre, $atributos[$tag] ?? [], true)) { $hijo->removeAttribute($nombre); continue; }
                $valor = trim($attr->value);
                if (($nombre === 'href' || $nombre === 'src') && (preg_match('/^(?:javascript|data|vbscript):/i', $valor) || str_contains($valor, '..'))) $hijo->removeAttribute($nombre);
                if ($tag === 'a' && $nombre === 'target' && $valor !== '_blank') $hijo->removeAttribute($nombre);
                if ($tag === 'span' && $nombre === 'class' && preg_match('/^(?:lead|text-(?:start|center|end|muted))$/', $valor) !== 1) $hijo->removeAttribute($nombre);
            }
            if ($tag === 'a' && $hijo->getAttribute('target') === '_blank') $hijo->setAttribute('rel', 'noopener noreferrer');
            $recorrer($hijo);
        }
    };
    $recorrer($raiz);
    $salida = '';
    foreach ($raiz->childNodes as $hijo) $salida .= $dom->saveHTML($hijo);
    return trim($salida);
}

function datosContenidoNavbarAtenea(?string $json): array
{
    if (!$json) return [];
    try { $datos = json_decode($json, true, 32, JSON_THROW_ON_ERROR); return is_array($datos) ? $datos : []; }
    catch (Throwable) { return []; }
}
