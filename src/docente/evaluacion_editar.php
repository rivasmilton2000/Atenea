<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

$pdo = obtenerConexion();
$id = docenteId($_GET['id'] ?? 0);
$docenteId = docenteSupervisadoAtenea($pdo);
$consulta = $pdo->prepare('SELECT e.*,a.nombre asignatura FROM evaluaciones e JOIN asignaturas a ON a.id=e.asignatura_id WHERE e.id=:id AND e.docente_id=:docente LIMIT 1');
$consulta->execute(['id' => $id, 'docente' => $docenteId]);
$evaluacion = $consulta->fetch();
if (!$evaluacion || !docentePuedeCurso($pdo, $docenteId, (int) $evaluacion['asignatura_id'])) {
    denegarAccesoDocente('Intento de acceso a una evaluación no asignada.');
}

docenteCabecera('Editar evaluación', 'evaluaciones', 'Actualiza una evaluación de tu curso.');
$fechaInput = static fn(?string $fecha): string => $fecha ? date('Y-m-d\TH:i', strtotime($fecha)) : '';
?>
<div class="card card-rounded"><div class="card-body">
  <p class="text-muted">Curso: <?= atenea_e($evaluacion['asignatura']) ?></p>
  <form method="post" action="evaluacion_actualizar.php" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>">
    <input type="hidden" name="docente_id" value="<?= $docenteId ?>">
    <input type="hidden" name="id" value="<?= (int) $evaluacion['id'] ?>">
    <div class="col-md-7"><label class="form-label">Título</label><input class="form-control" name="titulo" maxlength="190" value="<?= atenea_e($evaluacion['titulo']) ?>" required></div>
    <div class="col-md-2"><label class="form-label">Nota máxima</label><input class="form-control" type="number" name="nota_maxima" min="0.01" max="1000" step="0.01" value="<?= atenea_e($evaluacion['nota_maxima']) ?>" required></div>
    <div class="col-md-3"><label class="form-label">Estado</label><select class="form-select" name="estado"><?php foreach (['borrador','publicada','cerrada','inactiva'] as $estado): ?><option value="<?= $estado ?>" <?= $evaluacion['estado'] === $estado ? 'selected' : '' ?>><?= atenea_e($estado) ?></option><?php endforeach; ?></select></div>
    <div class="col-12"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion" maxlength="5000" rows="5"><?= atenea_e((string) $evaluacion['descripcion']) ?></textarea></div>
    <div class="col-md-6"><label class="form-label">Apertura</label><input class="form-control" type="datetime-local" name="fecha_apertura" value="<?= atenea_e($fechaInput($evaluacion['fecha_apertura'])) ?>"></div>
    <div class="col-md-6"><label class="form-label">Cierre</label><input class="form-control" type="datetime-local" name="fecha_cierre" value="<?= atenea_e($fechaInput($evaluacion['fecha_cierre'])) ?>"></div>
    <div class="col-12 d-flex gap-2"><button class="btn btn-primary">Guardar cambios</button><a class="btn btn-outline-secondary" href="<?= docenteUrl('evaluaciones.php',['curso'=>$evaluacion['asignatura_id']]) ?>">Cancelar</a></div>
  </form>
</div></div>
<?php docentePie();
