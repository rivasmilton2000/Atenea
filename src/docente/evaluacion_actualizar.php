<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';
require_once dirname(__DIR__, 2) . '/includes/audit.php';
exigirPermiso('academic.evaluations.manage');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !validarTokenCsrf((string) ($_POST['csrf_token'] ?? ''))) {
    http_response_code(400);
    exit;
}

$pdo = obtenerConexion();
$docenteId = docenteSupervisadoAtenea($pdo);
$id = docenteId($_POST['id'] ?? 0);
$titulo = trim(strip_tags((string) ($_POST['titulo'] ?? '')));
$descripcion = trim(strip_tags((string) ($_POST['descripcion'] ?? '')));
$estado = (string) ($_POST['estado'] ?? 'borrador');
$nota = (string) ($_POST['nota_maxima'] ?? '');
$aperturaTexto = trim((string) ($_POST['fecha_apertura'] ?? ''));
$cierreTexto = trim((string) ($_POST['fecha_cierre'] ?? ''));
$curso = 0;
try {
    $apertura = $aperturaTexto !== '' ? DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $aperturaTexto) : null;
    $cierre = $cierreTexto !== '' ? DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $cierreTexto) : null;
    if ($titulo === '' || mb_strlen($titulo) > 190 || mb_strlen($descripcion) > 5000 || !in_array($estado, ['borrador','publicada','cerrada','inactiva'], true) || !is_numeric($nota) || (float) $nota <= 0 || (float) $nota > 1000 || ($aperturaTexto !== '' && !$apertura) || ($cierreTexto !== '' && !$cierre) || ($apertura && $cierre && $cierre <= $apertura)) {
        throw new DomainException('Revisa los datos de la evaluación.');
    }
    $pdo->beginTransaction();
    $consulta = $pdo->prepare('SELECT asignatura_id FROM evaluaciones WHERE id=:id AND docente_id=:docente FOR UPDATE');
    $consulta->execute(['id' => $id, 'docente' => $docenteId]);
    $curso = (int) ($consulta->fetchColumn() ?: 0);
    if (!$curso || !docentePuedeCurso($pdo, $docenteId, $curso)) throw new DomainException('La evaluación no pertenece a uno de tus cursos activos.');
    $consulta = $pdo->prepare('UPDATE evaluaciones SET titulo=:titulo,descripcion=:descripcion,fecha_apertura=:apertura,fecha_cierre=:cierre,nota_maxima=:nota,estado=:estado WHERE id=:id AND docente_id=:docente');
    $consulta->execute(['titulo'=>$titulo,'descripcion'=>$descripcion ?: null,'apertura'=>$apertura?->format('Y-m-d H:i:s'),'cierre'=>$cierre?->format('Y-m-d H:i:s'),'nota'=>number_format((float)$nota,2,'.',''),'estado'=>$estado,'id'=>$id,'docente'=>$docenteId]);
    registrarAuditoria(['actor_user_id'=>$_SESSION['usuario_id'],'event_type'=>'academic.evaluation.updated','module'=>'academic','entity_type'=>'evaluation','entity_id'=>$id,'action'=>'update','result'=>'success','description'=>'Evaluación actualizada dentro de un curso autorizado.'], $pdo);
    $pdo->commit();
    docenteFlash('exito', 'Evaluación actualizada.');
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    docenteFlash('error', $error instanceof DomainException ? $error->getMessage() : 'No fue posible actualizar la evaluación.');
}
header('Location:' . docenteUrl('evaluaciones.php', ['curso' => $curso]));
