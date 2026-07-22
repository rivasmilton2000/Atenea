<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';
require_once dirname(__DIR__, 2) . '/includes/contenido_clase.php';
require_once dirname(__DIR__, 2) . '/includes/audit.php';

exigirPermiso('academic.content.manage');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
    || !validarTokenCsrf((string) ($_POST['csrf_token'] ?? ''))) {
    http_response_code(400);
    exit;
}

$pdo = obtenerConexion();
$id = docenteId($_POST['id'] ?? 0);
$accion = (string) ($_POST['accion'] ?? '');
$seccionId = 0;
$archivoRelativo = '';

try {
    $pdo->beginTransaction();
    $consulta = $pdo->prepare(
        'SELECT * FROM contenidos WHERE id = :id AND eliminado_at IS NULL FOR UPDATE'
    );
    $consulta->execute(['id' => $id]);
    $contenido = $consulta->fetch();
    if (!$contenido || !contenidoClasePuedeAdministrar(
        $pdo,
        $contenido,
        (int) $_SESSION['usuario_id'],
        (string) $_SESSION['usuario_rol']
    )) {
        throw new DomainException('No puedes administrar esta publicación.');
    }
    $seccionId = (int) $contenido['seccion_id'];

    if ($accion === 'publicar') {
        $pdo->prepare(
            "UPDATE contenidos
             SET estado = 'activo', activo = 1, publicado_at = COALESCE(publicado_at, NOW())
             WHERE id = :id"
        )->execute(['id' => $id]);
        if ($contenido['estado'] !== 'activo') {
            notificarPublicacionContenido($pdo, $contenido);
        }
        $mensaje = 'Publicación habilitada.';
    } elseif ($accion === 'borrador') {
        $pdo->prepare(
            "UPDATE contenidos SET estado = 'borrador', activo = 0 WHERE id = :id"
        )->execute(['id' => $id]);
        $mensaje = 'La publicación volvió a borrador.';
    } elseif ($accion === 'eliminar') {
        $pdo->prepare(
            "UPDATE contenidos
             SET estado = 'inactivo', activo = 0, eliminado_at = NOW(), eliminado_por = :usuario_id
             WHERE id = :id"
        )->execute(['usuario_id' => (int) $_SESSION['usuario_id'], 'id' => $id]);
        $archivoRelativo = (string) ($contenido['archivo_relpath'] ?? '');
        $mensaje = 'Publicación eliminada.';
    } else {
        throw new DomainException('Acción no válida.');
    }

    registrarAuditoria([
        'actor_user_id' => (int) $_SESSION['usuario_id'],
        'target_user_id' => (int) $contenido['docente_id'],
        'event_type' => 'academic.content.' . $accion,
        'module' => 'academic',
        'entity_type' => 'content',
        'entity_id' => $id,
        'action' => $accion,
        'result' => 'success',
        'description' => 'Cambio de estado de publicación de clase.',
    ], $pdo);
    $pdo->commit();

    if ($archivoRelativo !== '') {
        $ruta = rutaPrivadaAcademica($archivoRelativo);
        if ($ruta) {
            @unlink($ruta);
        }
    }
    docenteFlash('exito', $mensaje);
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Contenido estado: ' . $error->getMessage());
    docenteFlash(
        'error',
        $error instanceof DomainException
            ? $error->getMessage()
            : 'No fue posible cambiar el estado.'
    );
}

header('Location:' . docenteUrl('contenidos.php', ['seccion' => $seccionId]));
exit;
