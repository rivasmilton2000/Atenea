<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

$pdo = obtenerConexion();
$id = docenteId($_GET['id'] ?? 0);
$docenteId = docenteSupervisadoAtenea($pdo);
$consulta = $pdo->prepare('SELECT c.*,a.nombre asignatura FROM contenidos c JOIN asignaturas a ON a.id=c.asignatura_id WHERE c.id=:id AND c.docente_id=:docente LIMIT 1');
$consulta->execute(['id' => $id, 'docente' => $docenteId]);
$contenido = $consulta->fetch();
if (!$contenido || !docentePuedeCurso($pdo, $docenteId, (int) $contenido['asignatura_id'])) {
    http_response_code(403);
    exit('No tienes acceso a este contenido.');
}

docenteCabecera('Editar contenido', 'contenidos', 'Actualiza información de un contenido de tu curso.');
?>
<div class="card card-rounded"><div class="card-body">
  <p class="text-muted">Curso: <?= atenea_e($contenido['asignatura']) ?></p>
  <form method="post" action="contenido_actualizar.php" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>">
    <input type="hidden" name="docente_id" value="<?= $docenteId ?>">
    <input type="hidden" name="id" value="<?= (int) $contenido['id'] ?>">
    <div class="col-md-9"><label class="form-label">Título</label><input class="form-control" name="titulo" maxlength="190" value="<?= atenea_e($contenido['titulo']) ?>" required></div>
    <div class="col-md-3"><label class="form-label">Estado</label><select class="form-select" name="estado"><?php foreach (['borrador','activo','inactivo'] as $estado): ?><option value="<?= $estado ?>" <?= $contenido['estado'] === $estado ? 'selected' : '' ?>><?= atenea_e($estado) ?></option><?php endforeach; ?></select></div>
    <div class="col-12"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion" maxlength="5000" rows="6"><?= atenea_e((string) $contenido['descripcion']) ?></textarea></div>
    <div class="col-12 d-flex gap-2"><button class="btn btn-primary">Guardar cambios</button><a class="btn btn-outline-secondary" href="<?= docenteUrl('contenidos.php',['curso'=>$contenido['asignatura_id']]) ?>">Cancelar</a></div>
  </form>
</div></div>
<?php docentePie();
