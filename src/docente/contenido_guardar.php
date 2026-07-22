<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';
require_once dirname(__DIR__, 2) . '/includes/contenido_clase.php';
require_once dirname(__DIR__, 2) . '/includes/audit.php';

exigirPermiso('academic.content.manage');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !validarTokenCsrf((string) ($_POST['csrf_token'] ?? ''))) {
    http_response_code(400);
    exit;
}

$pdo = obtenerConexion();
$docenteId = docenteSupervisadoAtenea($pdo);
$seccionId = docenteId($_POST['seccion_id'] ?? 0);
$retorno = docenteUrl('contenidos.php', ['seccion'=>$seccionId]);
$archivoGuardado = null;

try {
    if ($docenteId < 1 || !docentePoseeSeccion($pdo, $docenteId, $seccionId)) {
        throw new DomainException('Solo puedes publicar en una sección que tengas asignada.');
    }
    $q = $pdo->prepare('SELECT asignatura_id FROM capacitacion_secciones WHERE id=:id AND docente_id=:docente');
    $q->execute(['id'=>$seccionId, 'docente'=>$docenteId]);
    $asignaturaId = (int) $q->fetchColumn();
    if (!$asignaturaId) throw new DomainException('La clase seleccionada no está disponible.');

    $modulo = textoPlanoContenido($_POST['modulo'] ?? '', 120, true);
    $titulo = textoPlanoContenido($_POST['titulo'] ?? '', 190, true);
    $descripcion = textoPlanoContenido($_POST['descripcion'] ?? '', 10000);
    $tipoRecurso = (string) ($_POST['tipo_recurso'] ?? 'ninguno');
    $url = trim((string) ($_POST['recurso_url'] ?? ''));
    $tieneArchivo = archivoPresente();
    validarEntradaRecursoContenido($tipoRecurso, $url, $tieneArchivo);
    $fechaPublicacion = fechaHoraContenido($_POST['fecha_publicacion'] ?? null);
    $estado = ($_POST['estado'] ?? '') === 'publicado' ? 'activo' : 'borrador';

    if ($tieneArchivo) {
        $categoria = $tipoRecurso === 'video_archivo' ? 'video' : 'contenido';
        $archivoGuardado = guardarArchivoAcademico('archivo', 'contenidos', $categoria);
    }
    $tipo = match ($tipoRecurso) {
        'video_archivo', 'youtube' => 'video',
        'documento' => 'documento',
        'google_drive', 'enlace' => 'enlace',
        default => 'texto',
    };

    $pdo->beginTransaction();
    $insertar = $pdo->prepare("INSERT INTO contenidos(asignatura_id,seccion_id,docente_id,modulo,tipo,titulo,descripcion,orden,video_url,archivo_relpath,archivo_nombre,archivo_mime,archivo_tamano,fecha_publicacion,publicado_at,estado,activo,obligatorio,peso_progreso) VALUES(:asignatura,:seccion,:docente,:modulo,:tipo,:titulo,:descripcion,0,:url,:ruta,:nombre,:mime,:tamano,:fecha,IF(:estado_publicado='activo',NOW(),NULL),:estado,:activo,0,0)");
    $insertar->execute([
        'asignatura'=>$asignaturaId, 'seccion'=>$seccionId, 'docente'=>$docenteId,
        'modulo'=>$modulo, 'tipo'=>$tipo, 'titulo'=>$titulo, 'descripcion'=>$descripcion ?: null,
        'url'=>$url ?: null, 'ruta'=>$archivoGuardado['relpath'] ?? null,
        'nombre'=>$archivoGuardado['nombre'] ?? null, 'mime'=>$archivoGuardado['mime'] ?? null,
        'tamano'=>$archivoGuardado['tamano'] ?? null, 'fecha'=>$fechaPublicacion,
        'estado_publicado'=>$estado, 'estado'=>$estado, 'activo'=>$estado === 'activo' ? 1 : 0,
    ]);
    $contenidoId = (int) $pdo->lastInsertId();
    $contenido = ['id'=>$contenidoId,'seccion_id'=>$seccionId,'asignatura_id'=>$asignaturaId,'docente_id'=>$docenteId,'titulo'=>$titulo];
    if ($estado === 'activo') notificarPublicacionContenido($pdo, $contenido);
    registrarAuditoria([
        'actor_user_id'=>(int)$_SESSION['usuario_id'], 'target_user_id'=>$docenteId,
        'event_type'=>'academic.content.created', 'module'=>'academic', 'entity_type'=>'content',
        'entity_id'=>$contenidoId, 'action'=>'create', 'result'=>'success',
        'description'=>'Se creó una publicación de clase en una sección asignada.',
        'metadata'=>['estado'=>$estado,'tipo_recurso'=>$tipoRecurso,'seccion_id'=>$seccionId],
    ], $pdo);
    $pdo->commit();
    docenteFlash('exito', $estado === 'activo' ? 'Publicación disponible para la clase.' : 'Borrador guardado.');
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if ($archivoGuardado) { $ruta=rutaPrivadaAcademica((string)$archivoGuardado['relpath']); if($ruta) @unlink($ruta); }
    error_log('Contenido de clase crear: ' . $error->getMessage());
    docenteFlash('error', $error instanceof DomainException ? $error->getMessage() : 'No fue posible guardar la publicación.');
}
header('Location:' . $retorno);
