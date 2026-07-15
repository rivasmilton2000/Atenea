<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

$pdo = obtenerConexion();
docenteCabecera('Entregas y notas', 'entregas', 'Revisión limitada a evaluaciones y estudiantes relacionados.');
$docenteId = (int) $GLOBALS['docentePortalId'];
docenteSelectorSupervision($pdo);

$evaluacion = docenteId($_GET['evaluacion'] ?? 0);
$curso = docenteId($_GET['curso'] ?? 0);
$estudiante = docenteId($_GET['estudiante'] ?? 0);

if ($curso) exigirCursoDocente($pdo, $docenteId, $curso);
if ($estudiante && !docentePuedeEstudiante($pdo, $docenteId, $estudiante, $curso ?: null)) {
    http_response_code(403);
    exit('No tienes acceso a este estudiante.');
}
if ($evaluacion) {
    $consulta = $pdo->prepare('SELECT asignatura_id FROM evaluaciones WHERE id=:id AND docente_id=:docente LIMIT 1');
    $consulta->execute(['id' => $evaluacion, 'docente' => $docenteId]);
    $cursoEvaluacion = (int) ($consulta->fetchColumn() ?: 0);
    if (!$cursoEvaluacion || !docentePuedeCurso($pdo, $docenteId, $cursoEvaluacion)) {
        http_response_code(403);
        exit('No tienes acceso a esta evaluación.');
    }
}

$where = ' WHERE e.docente_id=:docente';
$parametros = ['docente' => $docenteId];
if ($evaluacion) {
    $where .= ' AND e.id=:evaluacion';
    $parametros['evaluacion'] = $evaluacion;
}
if ($curso) {
    $where .= ' AND e.asignatura_id=:curso';
    $parametros['curso'] = $curso;
}
if ($estudiante) {
    $where .= ' AND ee.estudiante_id=:estudiante';
    $parametros['estudiante'] = $estudiante;
}

$sql = "SELECT ee.*,e.titulo evaluacion,e.nota_maxima,e.asignatura_id,a.nombre asignatura,
               u.nombre,u.apellido,n.id nota_id,n.nota,n.observacion
        FROM ev_entregadas ee
        JOIN evaluaciones e ON e.id=ee.evaluacion_id
        JOIN estudiantes_docentes ed ON ed.estudiante_id=ee.estudiante_id
             AND ed.docente_id=e.docente_id AND ed.asignatura_id=e.asignatura_id AND ed.estado='activo'
        JOIN asignaturas a ON a.id=e.asignatura_id
        JOIN usuarios u ON u.id=ee.estudiante_id
        LEFT JOIN notas n ON n.entrega_id=ee.id"
        . $where . ' ORDER BY ee.entregado_at DESC LIMIT 100';
$consulta = $pdo->prepare($sql);
$consulta->execute($parametros);
$filas = $consulta->fetchAll();
?>
<div class="card card-rounded"><div class="card-body"><div class="table-responsive"><table class="table">
  <thead><tr><th>Estudiante</th><th>Evaluación</th><th>Entrega</th><th>Estado</th><th>Calificación</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($filas as $entrega): ?>
    <tr>
      <td><?= atenea_e(trim($entrega['nombre'] . ' ' . $entrega['apellido'])) ?></td>
      <td><?= atenea_e($entrega['evaluacion']) ?><small class="d-block"><?= atenea_e($entrega['asignatura']) ?></small></td>
      <td><?= date('d/m/Y H:i', strtotime($entrega['entregado_at'])) ?><?php if ($entrega['archivo_nombre']): ?><small class="d-block"><?= atenea_e($entrega['archivo_nombre']) ?></small><?php endif; ?></td>
      <td><?= atenea_e($entrega['estado']) ?></td>
      <td><?= $entrega['nota_id'] ? atenea_e($entrega['nota'] . ' / ' . $entrega['nota_maxima']) : 'Pendiente' ?></td>
      <td><button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#calificar<?= (int) $entrega['id'] ?>">Revisar</button></td>
    </tr>
    <tr class="collapse" id="calificar<?= (int) $entrega['id'] ?>"><td colspan="6">
      <form method="post" action="calificar.php" class="row g-2">
        <input type="hidden" name="csrf_token" value="<?= atenea_e(obtenerTokenCsrf()) ?>">
        <input type="hidden" name="docente_id" value="<?= $docenteId ?>">
        <input type="hidden" name="entrega_id" value="<?= (int) $entrega['id'] ?>">
        <div class="col-md-2"><input class="form-control" type="number" name="nota" min="0" max="<?= atenea_e($entrega['nota_maxima']) ?>" step="0.01" value="<?= atenea_e((string) $entrega['nota']) ?>" required></div>
        <div class="col-md-8"><input class="form-control" name="observacion" maxlength="1000" value="<?= atenea_e((string) $entrega['observacion']) ?>" placeholder="Observación académica"></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Guardar nota</button></div>
      </form>
    </td></tr>
  <?php endforeach; ?>
  <?php if (!$filas): ?><tr><td colspan="6" class="text-center py-5">No hay entregas reales pendientes ni revisadas.</td></tr><?php endif; ?>
  </tbody>
</table></div></div></div>
<?php docentePie();
