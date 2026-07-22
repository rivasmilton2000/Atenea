<?php
declare(strict_types=1);

require_once __DIR__ . '/academico_flujo.php';
require_once __DIR__ . '/notificaciones.php';
require_once __DIR__ . '/avatar.php';

const CONTENIDO_COMENTARIO_MIN = 2;
const CONTENIDO_COMENTARIO_MAX = 2000;
const CONTENIDO_COMENTARIO_ESPERA = 10;

function tiposRecursoContenidoClase(): array
{
    return [
        'ninguno' => 'Solo texto',
        'video_archivo' => 'Video subido al servidor',
        'youtube' => 'Enlace de YouTube',
        'google_drive' => 'Enlace de Google Drive',
        'enlace' => 'Otro enlace HTTPS',
        'documento' => 'Archivo educativo',
    ];
}

function textoPlanoContenido(?string $valor, int $maximo, bool $obligatorio = false): string
{
    $original = (string) $valor;
    if ($original !== strip_tags($original) || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $original)) {
        throw new DomainException('No se permite HTML ni contenido ejecutable.');
    }
    $normalizado = trim((string) preg_replace("/\r\n?|\n/u", "\n", $original));
    if (($obligatorio && $normalizado === '') || mb_strlen($normalizado) > $maximo) {
        throw new DomainException('Revisa los campos de texto y sus límites de longitud.');
    }
    return $normalizado;
}

function fechaHoraContenido(?string $valor): ?string
{
    $valor = trim((string) $valor);
    if ($valor === '') return null;
    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $valor, new DateTimeZone('America/El_Salvador'));
    if (!$fecha || $fecha->format('Y-m-d\TH:i') !== $valor) throw new DomainException('La fecha de publicación no es válida.');
    return $fecha->format('Y-m-d H:i:s');
}

function analizarUrlRecursoContenido(string $url): array
{
    $url = trim($url);
    if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) throw new DomainException('Ingresa un enlace válido.');
    $partes = parse_url($url);
    $esquema = strtolower((string) ($partes['scheme'] ?? ''));
    $host = strtolower(rtrim((string) ($partes['host'] ?? ''), '.'));
    if (isset($partes['user'], $partes['pass']) || $host === '') throw new DomainException('El enlace contiene credenciales o un host inválido.');
    $local = in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    if ($esquema !== 'https' && !($esquema === 'http' && $local)) throw new DomainException('Los enlaces externos deben usar HTTPS.');

    $youtubeHosts = ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'youtube-nocookie.com', 'www.youtube-nocookie.com'];
    if (in_array($host, $youtubeHosts, true)) {
        $id = '';
        if ($host === 'youtu.be') $id = trim((string) ($partes['path'] ?? ''), '/');
        elseif (preg_match('~^/(?:embed|shorts)/([A-Za-z0-9_-]{11})~', (string) ($partes['path'] ?? ''), $m)) $id = $m[1];
        else { parse_str((string) ($partes['query'] ?? ''), $query); $id = (string) ($query['v'] ?? ''); }
        if (!preg_match('/^[A-Za-z0-9_-]{11}$/D', $id)) throw new DomainException('No se pudo identificar el video de YouTube.');
        return ['proveedor'=>'youtube', 'url'=>$url, 'embed'=>'https://www.youtube-nocookie.com/embed/' . $id];
    }

    if ($host === 'drive.google.com') {
        $id = '';
        if (preg_match('~/file/d/([A-Za-z0-9_-]{10,})~', (string) ($partes['path'] ?? ''), $m)) $id = $m[1];
        else { parse_str((string) ($partes['query'] ?? ''), $query); $id = (string) ($query['id'] ?? ''); }
        if (!preg_match('/^[A-Za-z0-9_-]{10,}$/D', $id)) throw new DomainException('No se pudo identificar el archivo compartido de Google Drive.');
        return ['proveedor'=>'google_drive', 'url'=>$url, 'embed'=>'https://drive.google.com/file/d/' . $id . '/preview'];
    }

    return ['proveedor'=>'externo', 'url'=>$url, 'embed'=>null];
}

function tipoRecursoDeContenido(array $contenido): string
{
    if (!empty($contenido['archivo_relpath'])) return str_starts_with((string) $contenido['archivo_mime'], 'video/') ? 'video_archivo' : 'documento';
    if (!empty($contenido['video_url'])) {
        try {
            $proveedor = analizarUrlRecursoContenido((string) $contenido['video_url'])['proveedor'];
            return $proveedor === 'youtube' ? 'youtube' : ($proveedor === 'google_drive' ? 'google_drive' : 'enlace');
        }
        catch (Throwable) { return 'enlace'; }
    }
    return 'ninguno';
}

function archivoPresente(string $campo = 'archivo'): bool
{
    return isset($_FILES[$campo]) && (int) ($_FILES[$campo]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
}

function validarEntradaRecursoContenido(string $tipoRecurso, string $url, bool $tieneArchivo, bool $yaTieneArchivo = false): ?array
{
    if (!array_key_exists($tipoRecurso, tiposRecursoContenidoClase())) throw new DomainException('Selecciona un tipo de recurso válido.');
    if ($tieneArchivo && trim($url) !== '') throw new DomainException('Adjunta un archivo o proporciona un enlace, no ambos.');
    if (in_array($tipoRecurso, ['video_archivo','documento'], true)) {
        if (trim($url) !== '') throw new DomainException('Este tipo de recurso no acepta un enlace externo.');
        if (!$tieneArchivo && !$yaTieneArchivo) throw new DomainException('Selecciona el archivo que deseas publicar.');
        return null;
    }
    if ($tieneArchivo) throw new DomainException('El tipo de recurso seleccionado no acepta archivos.');
    if ($tipoRecurso === 'ninguno') {
        if (trim($url) !== '') throw new DomainException('Selecciona un tipo de enlace antes de guardar la URL.');
        return null;
    }
    $analisis = analizarUrlRecursoContenido($url);
    if ($tipoRecurso === 'youtube' && $analisis['proveedor'] !== 'youtube') throw new DomainException('El enlace no corresponde a YouTube.');
    if ($tipoRecurso === 'google_drive' && $analisis['proveedor'] !== 'google_drive') throw new DomainException('El enlace no corresponde a Google Drive.');
    return $analisis;
}

function contenidoClasePuedeAdministrar(PDO $pdo, array $contenido, int $usuarioId, string $rol): bool
{
    if ($rol === 'admin') return true;
    return $rol === 'docente'
        && (int) $contenido['docente_id'] === $usuarioId
        && docentePoseeSeccion($pdo, $usuarioId, (int) $contenido['seccion_id']);
}

function contenidoClasePuedeVerEstudiante(PDO $pdo, int $contenidoId, int $usuarioId): bool
{
    $q = $pdo->prepare("SELECT 1 FROM contenidos c JOIN inscripciones_capacitacion i ON i.seccion_id=c.seccion_id AND i.asignatura_id=c.asignatura_id WHERE c.id=:c AND i.usuario_id=:u AND i.estado IN('inscrito','finalizado') AND c.estado='activo' AND c.activo=1 AND c.eliminado_at IS NULL AND (c.fecha_publicacion IS NULL OR c.fecha_publicacion<=NOW()) LIMIT 1");
    $q->execute(['c'=>$contenidoId, 'u'=>$usuarioId]);
    return (bool) $q->fetchColumn();
}

function notificarPublicacionContenido(PDO $pdo, array $contenido): void
{
    $q = $pdo->prepare("SELECT DISTINCT usuario_id FROM inscripciones_capacitacion WHERE seccion_id=:s AND asignatura_id=:a AND estado='inscrito'");
    $q->execute(['s'=>$contenido['seccion_id'], 'a'=>$contenido['asignatura_id']]);
    foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $usuarioId) {
        crearNotificacionAtenea([
            'usuario_id'=>(int)$usuarioId, 'created_by'=>(int)$contenido['docente_id'],
            'tipo'=>'contenido_publicado', 'categoria'=>'academico', 'nivel'=>'informacion',
            'titulo'=>'Nuevo contenido en tu clase', 'descripcion'=>(string)$contenido['titulo'],
            'url'=>atenea_url('src/estudiantes/contenido.php?id='.(int)$contenido['id']),
            'idempotency_key'=>'contenido-publicado:'.(int)$contenido['id'],
        ], $pdo);
    }
}

function comentariosContenidoClase(PDO $pdo, int $contenidoId, int $pagina = 1, int $limite = 20): array
{
    $pagina=max(1,$pagina);$limite=max(1,min(50,$limite));$offset=($pagina-1)*$limite;
    $q=$pdo->prepare("SELECT COUNT(*) FROM contenido_comentarios WHERE contenido_id=:c AND parent_id IS NULL AND estado<>'eliminado'");$q->execute(['c'=>$contenidoId]);$total=(int)$q->fetchColumn();
    $q=$pdo->prepare("SELECT cc.*,u.nombre,u.apellido,u.rol,u.foto FROM contenido_comentarios cc JOIN usuarios u ON u.id=cc.usuario_id WHERE cc.contenido_id=:c AND cc.parent_id IS NULL AND cc.estado<>'eliminado' ORDER BY cc.created_at DESC,cc.id DESC LIMIT :lim OFFSET :off");$q->bindValue(':c',$contenidoId,PDO::PARAM_INT);$q->bindValue(':lim',$limite,PDO::PARAM_INT);$q->bindValue(':off',$offset,PDO::PARAM_INT);$q->execute();$principales=$q->fetchAll();
    if($principales){$ids=array_map('intval',array_column($principales,'id'));$marcas=implode(',',array_fill(0,count($ids),'?'));$r=$pdo->prepare("SELECT cc.*,u.nombre,u.apellido,u.rol,u.foto FROM contenido_comentarios cc JOIN usuarios u ON u.id=cc.usuario_id WHERE cc.parent_id IN ($marcas) AND cc.estado<>'eliminado' ORDER BY cc.created_at,cc.id");$r->execute($ids);$respuestas=[];foreach($r->fetchAll() as$f)$respuestas[(int)$f['parent_id']][]=$f;foreach($principales as&$p)$p['respuestas']=$respuestas[(int)$p['id']]??[];unset($p);}
    return ['comentarios'=>$principales,'total'=>$total,'pagina'=>$pagina,'paginas'=>max(1,(int)ceil($total/$limite))];
}

function cantidadComentariosContenido(PDO $pdo, int $contenidoId): int
{
    $q=$pdo->prepare("SELECT COUNT(*) FROM contenido_comentarios WHERE contenido_id=:c AND estado='visible'");$q->execute(['c'=>$contenidoId]);return(int)$q->fetchColumn();
}

function renderizarRecursoContenidoClase(array $contenido): void
{
    if (!empty($contenido['archivo_relpath'])) {
        $url=atenea_url('src/academico/archivo.php?tipo=contenido&id='.(int)$contenido['id']);
        if(str_starts_with((string)$contenido['archivo_mime'],'video/')) echo '<video class="w-100 rounded bg-dark" controls preload="metadata" src="'.atenea_e($url).'" controlslist="nodownload"><p>Tu navegador no puede reproducir este video.</p></video>';
        else echo '<a class="btn btn-outline-primary" href="'.atenea_e($url).'"><i class="bi bi-file-earmark-arrow-down me-2"></i>Descargar '.atenea_e((string)($contenido['archivo_nombre']?:'recurso educativo')).'</a>';
        return;
    }
    if(empty($contenido['video_url']))return;
    try{$r=analizarUrlRecursoContenido((string)$contenido['video_url']);}catch(Throwable){$r=['url'=>'#','embed'=>null,'proveedor'=>'externo'];}
    if($r['embed']&&in_array($r['proveedor'],['youtube','google_drive'],true))echo '<div class="ratio ratio-16x9"><iframe src="'.atenea_e($r['embed']).'" title="Vista previa del recurso" loading="lazy" sandbox="allow-scripts allow-same-origin allow-presentation" referrerpolicy="strict-origin-when-cross-origin" allow="fullscreen; encrypted-media; picture-in-picture" allowfullscreen></iframe></div>';
    else echo '<a class="btn btn-outline-primary" href="'.atenea_e($r['url']).'" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right me-2"></i>Abrir recurso</a>';
}

function renderizarConversacionContenidoClase(PDO $pdo, array $contenido, string $urlRetorno): void
{
    $actorId=(int)($_SESSION['usuario_id']??0);$rol=(string)($_SESSION['usuario_rol']??'');$puedeAdministrar=contenidoClasePuedeAdministrar($pdo,$contenido,$actorId,$rol);$esEstudiante=$rol==='usuario';
    $pagina=max(1,(int)($_GET['comentarios_pagina']??1));$datos=comentariosContenidoClase($pdo,(int)$contenido['id'],$pagina,15);$accion=atenea_url('src/academico/comentario-accion.php');$token=atenea_e(obtenerTokenCsrf());
    ?><section class="card mt-4" id="conversacion"><div class="card-body"><div class="d-flex justify-content-between align-items-center mb-3"><div><h2 class="h5 mb-1">Conversación de la clase</h2><p class="text-muted mb-0"><?=(int)$datos['total']?> dudas o comentarios</p></div><i class="bi bi-chat-square-text fs-3 text-primary"></i></div>
    <?php if($esEstudiante):?><form method="post" action="<?=$accion?>" class="mb-4"><input type="hidden" name="csrf_token" value="<?=$token?>"><input type="hidden" name="accion" value="crear"><input type="hidden" name="contenido_id" value="<?=(int)$contenido['id']?>"><input type="hidden" name="retorno" value="<?=atenea_e($urlRetorno)?>"><label class="form-label" for="nuevoComentario">Escribe una duda o comentario</label><textarea class="form-control" id="nuevoComentario" name="cuerpo" minlength="2" maxlength="2000" rows="3" required></textarea><div class="d-flex justify-content-between mt-2"><small class="text-muted">Texto plano, máximo 2000 caracteres.</small><button class="btn btn-primary btn-sm">Publicar comentario</button></div></form><?php endif;?>
    <div class="d-grid gap-3"><?php foreach($datos['comentarios'] as$comentario):$oculto=$comentario['estado']==='oculto';?><article class="border rounded p-3"><div class="d-flex gap-3"><img class="rounded-circle flex-shrink-0" src="<?=atenea_e(urlAvatarAtenea($comentario))?>" width="42" height="42" alt=""><div class="flex-grow-1"><div class="d-flex flex-wrap justify-content-between gap-2"><div><strong><?=atenea_e(trim($comentario['nombre'].' '.$comentario['apellido']))?></strong><span class="badge bg-light text-dark ms-1"><?=$comentario['rol']==='docente'?'Docente':'Estudiante'?></span></div><small class="text-muted"><?=date('d/m/Y h:i A',strtotime($comentario['created_at']))?><?=$comentario['editado_at']?' · editado':''?></small></div><p class="mt-2 mb-2 <?=$oculto?'text-muted fst-italic':''?>"><?=$oculto?'Comentario ocultado por moderación.':nl2br(atenea_e($comentario['cuerpo']))?></p>
    <div class="d-flex flex-wrap gap-2"><?php if((int)$comentario['usuario_id']===$actorId&&!$oculto):?><details><summary class="btn btn-sm btn-link p-0">Editar</summary><form class="mt-2" method="post" action="<?=$accion?>"><input type="hidden" name="csrf_token" value="<?=$token?>"><input type="hidden" name="accion" value="editar"><input type="hidden" name="comentario_id" value="<?=$comentario['id']?>"><input type="hidden" name="retorno" value="<?=atenea_e($urlRetorno)?>"><textarea class="form-control" name="cuerpo" minlength="2" maxlength="2000" rows="2" required><?=atenea_e($comentario['cuerpo'])?></textarea><button class="btn btn-sm btn-primary mt-2">Guardar edición</button></form></details><form method="post" action="<?=$accion?>" data-atenea-confirm="danger" data-atenea-confirm-title="Eliminar comentario" data-atenea-confirm-message="El comentario dejará de mostrarse en la conversación."><input type="hidden" name="csrf_token" value="<?=$token?>"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="comentario_id" value="<?=$comentario['id']?>"><input type="hidden" name="retorno" value="<?=atenea_e($urlRetorno)?>"><button class="btn btn-sm btn-link text-danger p-0" data-atenea-confirm="danger">Eliminar</button></form><?php endif;?><?php if($puedeAdministrar&&!$oculto):?><form method="post" action="<?=$accion?>"><input type="hidden" name="csrf_token" value="<?=$token?>"><input type="hidden" name="accion" value="ocultar"><input type="hidden" name="comentario_id" value="<?=$comentario['id']?>"><input type="hidden" name="retorno" value="<?=atenea_e($urlRetorno)?>"><button class="btn btn-sm btn-link text-muted p-0">Ocultar</button></form><?php endif;?></div>
    <?php foreach($comentario['respuestas'] as$respuesta):?><div class="border-start border-primary border-3 ps-3 mt-3"><div class="d-flex justify-content-between gap-2"><div><strong><?=atenea_e(trim($respuesta['nombre'].' '.$respuesta['apellido']))?></strong><span class="badge bg-primary ms-1">Respuesta oficial</span></div><small class="text-muted"><?=date('d/m/Y h:i A',strtotime($respuesta['created_at']))?></small></div><p class="mb-1 mt-2"><?=$respuesta['estado']==='oculto'?'<em>Respuesta ocultada.</em>':nl2br(atenea_e($respuesta['cuerpo']))?></p><?php if((int)$respuesta['usuario_id']===$actorId&&$respuesta['estado']==='visible'):?><div class="d-flex gap-2"><details><summary class="btn btn-sm btn-link p-0">Editar</summary><form method="post" action="<?=$accion?>" class="mt-2"><input type="hidden" name="csrf_token" value="<?=$token?>"><input type="hidden" name="accion" value="editar"><input type="hidden" name="comentario_id" value="<?=$respuesta['id']?>"><input type="hidden" name="retorno" value="<?=atenea_e($urlRetorno)?>"><textarea class="form-control" name="cuerpo" minlength="2" maxlength="2000" required><?=atenea_e($respuesta['cuerpo'])?></textarea><button class="btn btn-sm btn-primary mt-2">Guardar</button></form></details></div><?php endif;?></div><?php endforeach;?>
    <?php if($puedeAdministrar&&!$oculto):?><form method="post" action="<?=$accion?>" class="mt-3"><input type="hidden" name="csrf_token" value="<?=$token?>"><input type="hidden" name="accion" value="responder"><input type="hidden" name="contenido_id" value="<?=(int)$contenido['id']?>"><input type="hidden" name="parent_id" value="<?=$comentario['id']?>"><input type="hidden" name="retorno" value="<?=atenea_e($urlRetorno)?>"><div class="input-group"><textarea class="form-control" name="cuerpo" minlength="2" maxlength="2000" rows="1" placeholder="Responder como docente" required></textarea><button class="btn btn-outline-primary">Responder</button></div></form><?php endif;?></div></div></article><?php endforeach;?><?php if(!$datos['comentarios']):?><div class="text-center py-4 text-muted"><i class="bi bi-chat-dots fs-2"></i><p class="mb-0 mt-2">Todavía no hay preguntas en esta publicación.</p></div><?php endif;?></div>
    <?php if($datos['paginas']>1):?><nav class="mt-3" aria-label="Páginas de comentarios"><ul class="pagination pagination-sm justify-content-end"><?php for($i=1;$i<=$datos['paginas'];$i++):?><li class="page-item <?=$i===$datos['pagina']?'active':''?>"><a class="page-link" href="<?=atenea_e($urlRetorno.(str_contains($urlRetorno,'?')?'&':'?').'comentarios_pagina='.$i.'#conversacion')?>"><?=$i?></a></li><?php endfor;?></ul></nav><?php endif;?></div></section><?php
}
