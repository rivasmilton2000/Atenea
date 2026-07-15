<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';
require_once dirname(__DIR__, 2) . '/includes/audit.php';
exigirPermiso('academic.content.manage');
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
$curso = 0;
try {
    if ($titulo === '' || mb_strlen($titulo) > 190 || mb_strlen($descripcion) > 5000 || !in_array($estado, ['borrador','activo','inactivo'], true)) {
        throw new DomainException('Revisa los datos del contenido.');
    }
    $pdo->beginTransaction();
    $consulta = $pdo->prepare('SELECT asignatura_id FROM contenidos WHERE id=:id AND docente_id=:docente FOR UPDATE');
    $consulta->execute(['id' => $id, 'docente' => $docenteId]);
    $curso = (int) ($consulta->fetchColumn() ?: 0);
    if (!$curso || !docentePuedeCurso($pdo, $docenteId, $curso)) throw new DomainException('El contenido no pertenece a uno de tus cursos activos.');
    $consulta = $pdo->prepare('UPDATE contenidos SET titulo=:titulo,descripcion=:descripcion,estado=:estado WHERE id=:id AND docente_id=:docente');
    $consulta->execute(['titulo'=>$titulo,'descripcion'=>$descripcion ?: null,'estado'=>$estado,'id'=>$id,'docente'=>$docenteId]);
    registrarAuditoria(['actor_user_id'=>$_SESSION['usuario_id'],'event_type'=>'academic.content.updated','module'=>'academic','entity_type'=>'content','entity_id'=>$id,'action'=>'update','result'=>'success','description'=>'Contenido académico actualizado dentro de un curso autorizado.'], $pdo);
    $pdo->commit();
    docenteFlash('exito', 'Contenido actualizado.');
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    docenteFlash('error', $error instanceof DomainException ? $error->getMessage() : 'No fue posible actualizar el contenido.');
}
header('Location:' . docenteUrl('contenidos.php', ['curso' => $curso]));
