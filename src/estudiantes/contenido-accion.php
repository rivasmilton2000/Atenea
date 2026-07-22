<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/academico_flujo.php';

exigirRol(['usuario']);
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
    || !validarTokenCsrf((string) ($_POST['csrf_token'] ?? ''))) {
    http_response_code(400);
    exit;
}

$id = filter_var($_POST['contenido_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$accion = (string) ($_POST['accion'] ?? '');
$pdo = obtenerConexion();
$archivos = [];

try {
    $pdo->beginTransaction();
    $consulta = $pdo->prepare(
        "SELECT c.*, i.id AS inscripcion_id, i.usuario_id
         FROM contenidos c
         INNER JOIN inscripciones_capacitacion i
            ON i.seccion_id = c.seccion_id
           AND i.asignatura_id = c.asignatura_id
         WHERE c.id = :contenido_id
           AND i.usuario_id = :usuario_id
           AND i.estado IN ('inscrito', 'finalizado')
           AND c.estado = 'activo'
           AND c.activo = 1
           AND c.eliminado_at IS NULL
           AND (c.fecha_publicacion IS NULL OR c.fecha_publicacion <= NOW())
         FOR UPDATE"
    );
    $consulta->execute(['contenido_id' => $id, 'usuario_id' => (int) $_SESSION['usuario_id']]);
    $contenido = $consulta->fetch();
    if (!$contenido) {
        throw new DomainException('El contenido no está disponible para tu sección.');
    }

    if ($accion === 'completar') {
        if (in_array($contenido['tipo'], ['actividad', 'evaluacion'], true)) {
            throw new DomainException('Este contenido requiere una entrega aprobada.');
        }
        $pdo->prepare(
            'INSERT INTO progreso_contenido
                (inscripcion_id, contenido_id, visto_at, completado_at, ultima_actividad_at)
             VALUES (:inscripcion_id, :contenido_id, NOW(), NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                visto_at = COALESCE(visto_at, NOW()),
                completado_at = NOW(),
                ultima_actividad_at = NOW()'
        )->execute(['inscripcion_id' => $contenido['inscripcion_id'], 'contenido_id' => $id]);
        $mensaje = 'Contenido marcado como estudiado.';
    } elseif ($accion === 'entregar') {
        if (!in_array($contenido['tipo'], ['actividad', 'evaluacion'], true)) {
            throw new DomainException('Este contenido no recibe entregas.');
        }

        $comentario = trim(strip_tags((string) ($_POST['comentario'] ?? '')));
        $resultado = trim(strip_tags((string) ($_POST['resultado'] ?? '')));
        if ($comentario === '' || mb_strlen($comentario) > 5000 || mb_strlen($resultado) > 10000) {
            throw new DomainException('El comentario es obligatorio y debe tener un tamaño válido.');
        }

        $consulta = $pdo->prepare(
            'SELECT intento, estado
             FROM entregas_contenido
             WHERE contenido_id = :contenido_id AND estudiante_id = :estudiante_id
             ORDER BY intento DESC LIMIT 1 FOR UPDATE'
        );
        $consulta->execute(['contenido_id' => $id, 'estudiante_id' => (int) $_SESSION['usuario_id']]);
        $anterior = $consulta->fetch();
        if ($anterior && !in_array($anterior['estado'], ['rechazada', 'requiere_correccion'], true)) {
            throw new DomainException('La entrega actual todavía no permite otro intento.');
        }
        $intento = $anterior ? (int) $anterior['intento'] + 1 : 1;

        $consulta = $pdo->prepare(
            "INSERT INTO entregas_contenido
                (contenido_id, estudiante_id, asignatura_id, seccion_id, intento, comentario, resultado, estado, enviado_at)
             VALUES
                (:contenido_id, :estudiante_id, :asignatura_id, :seccion_id, :intento, :comentario, :resultado, 'enviada', NOW())"
        );
        $consulta->execute([
            'contenido_id' => $id,
            'estudiante_id' => (int) $_SESSION['usuario_id'],
            'asignatura_id' => $contenido['asignatura_id'],
            'seccion_id' => $contenido['seccion_id'],
            'intento' => $intento,
            'comentario' => $comentario,
            'resultado' => $resultado !== '' ? $resultado : null,
        ]);
        $entregaId = (int) $pdo->lastInsertId();

        if (isset($_FILES['evidencias']['name']) && is_array($_FILES['evidencias']['name'])) {
            if (count(array_filter($_FILES['evidencias']['name'])) > 5) {
                throw new DomainException('Solo se permiten cinco evidencias por intento.');
            }
            foreach ($_FILES['evidencias']['name'] as $indice => $nombre) {
                if (($_FILES['evidencias']['error'][$indice] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $_FILES['_evidencia'] = [
                    'name' => $nombre,
                    'type' => $_FILES['evidencias']['type'][$indice] ?? '',
                    'tmp_name' => $_FILES['evidencias']['tmp_name'][$indice] ?? '',
                    'error' => $_FILES['evidencias']['error'][$indice] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $_FILES['evidencias']['size'][$indice] ?? 0,
                ];
                $archivo = guardarArchivoAcademico('_evidencia', 'evidencias', 'evidencia');
                $archivos[] = $archivo['relpath'];
                $pdo->prepare(
                    'INSERT INTO entrega_evidencias
                        (entrega_id, archivo_relpath, archivo_nombre, archivo_mime, archivo_tamano)
                     VALUES (:entrega_id, :ruta, :nombre, :mime, :tamano)'
                )->execute([
                    'entrega_id' => $entregaId,
                    'ruta' => $archivo['relpath'],
                    'nombre' => $archivo['nombre'],
                    'mime' => $archivo['mime'],
                    'tamano' => $archivo['tamano'],
                ]);
            }
            unset($_FILES['_evidencia']);
        }

        $pdo->prepare(
            'INSERT INTO progreso_contenido
                (inscripcion_id, contenido_id, visto_at, ultima_actividad_at)
             VALUES (:inscripcion_id, :contenido_id, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                visto_at = COALESCE(visto_at, NOW()),
                ultima_actividad_at = NOW()'
        )->execute(['inscripcion_id' => $contenido['inscripcion_id'], 'contenido_id' => $id]);

        crearNotificacionAtenea([
            'usuario_id' => (int) $contenido['docente_id'],
            'created_by' => (int) $_SESSION['usuario_id'],
            'tipo' => 'entrega_nueva',
            'categoria' => 'academico',
            'nivel' => 'informacion',
            'titulo' => 'Nueva entrega para revisar',
            'descripcion' => $contenido['titulo'] . ' · intento ' . $intento,
            'url' => atenea_url('src/docente/entregas.php'),
            'idempotency_key' => 'entrega:' . $entregaId,
        ], $pdo);
        registrarAuditoria([
            'actor_user_id' => (int) $_SESSION['usuario_id'],
            'event_type' => 'academic.submission.sent',
            'module' => 'academic',
            'entity_type' => 'submission',
            'entity_id' => $entregaId,
            'action' => 'create',
            'result' => 'success',
            'description' => 'Estudiante envió una entrega para su sección.',
        ], $pdo);
        $mensaje = 'Entrega enviada para revisión.';
    } else {
        throw new DomainException('Acción inválida.');
    }

    $pdo->commit();
    $_SESSION['portal_flash'] = ['tipo' => 'success', 'mensaje' => $mensaje];
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    foreach ($archivos as $rutaRelativa) {
        $ruta = rutaPrivadaAcademica($rutaRelativa);
        if ($ruta) {
            unlink($ruta);
        }
    }
    $_SESSION['portal_flash'] = [
        'tipo' => 'danger',
        'mensaje' => $error instanceof DomainException
            ? $error->getMessage()
            : 'No fue posible completar la acción.',
    ];
}

header('Location: contenido.php?id=' . $id);
exit;
