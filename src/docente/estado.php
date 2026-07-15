<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';
require_once dirname(__DIR__, 2) . '/includes/audit.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !validarTokenCsrf((string) ($_POST['csrf_token'] ?? ''))) {
    http_response_code(400);
    exit('Solicitud no válida.');
}

$tipo = (string) ($_POST['tipo'] ?? '');
$id = docenteId($_POST['id'] ?? 0);
$estado = (string) ($_POST['estado'] ?? '');
$pdo = obtenerConexion();
$docenteId = docenteSupervisadoAtenea($pdo);

$definiciones = [
    'contenido' => [
        'permiso' => 'academic.content.manage',
        'tabla' => 'contenidos',
        'estados' => ['borrador', 'activo', 'inactivo'],
        'retorno' => 'contenidos.php',
        'evento' => 'academic.content.status_changed',
    ],
    'evaluacion' => [
        'permiso' => 'academic.evaluations.manage',
        'tabla' => 'evaluaciones',
        'estados' => ['borrador', 'publicada', 'cerrada', 'inactiva'],
        'retorno' => 'evaluaciones.php',
        'evento' => 'academic.evaluation.status_changed',
    ],
];

if (!isset($definiciones[$tipo])) {
    http_response_code(404);
    exit;
}

$definicion = $definiciones[$tipo];
exigirPermiso($definicion['permiso']);
if ($id < 1 || !in_array($estado, $definicion['estados'], true)) {
    docenteFlash('error', 'El estado solicitado no es válido.');
    header('Location:' . docenteUrl($definicion['retorno']));
    exit;
}

$registro = null;
try {
    $pdo->beginTransaction();
    $consulta = $pdo->prepare("SELECT asignatura_id,estado FROM {$definicion['tabla']} WHERE id=:id AND docente_id=:docente FOR UPDATE");
    $consulta->execute(['id' => $id, 'docente' => $docenteId]);
    $registro = $consulta->fetch();
    if (!$registro || !docentePuedeCurso($pdo, $docenteId, (int) $registro['asignatura_id'])) {
        throw new DomainException('El registro no pertenece a uno de tus cursos activos.');
    }

    $consulta = $pdo->prepare("UPDATE {$definicion['tabla']} SET estado=:estado WHERE id=:id AND docente_id=:docente");
    $consulta->execute(['estado' => $estado, 'id' => $id, 'docente' => $docenteId]);
    registrarAuditoria([
        'actor_user_id' => $_SESSION['usuario_id'],
        'event_type' => $definicion['evento'],
        'module' => 'academic',
        'entity_type' => $tipo,
        'entity_id' => $id,
        'action' => 'update',
        'result' => 'success',
        'description' => 'Estado académico cambiado de ' . $registro['estado'] . ' a ' . $estado . '.',
    ], $pdo);
    $pdo->commit();
    docenteFlash('exito', 'Estado actualizado.');
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    docenteFlash('error', $error instanceof DomainException ? $error->getMessage() : 'No fue posible actualizar el estado.');
}

header('Location:' . docenteUrl($definicion['retorno'], ['curso' => $registro['asignatura_id'] ?? 0]));
