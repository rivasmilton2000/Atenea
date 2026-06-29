<?php
include '../includes/connection.php';
include '../includes/sidebar_estudiante.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Admin' || $Aa == 'Docente' || $Aa == 'Personal' || $Aa == 'SuperAdmin') {
        if ($Aa == 'Admin') {
            $redirectUrl = "index.php";
        } elseif ($Aa == 'Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa == 'Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa == 'SuperAdmin') {
            $redirectUrl = "sa_vista.php";
        }
        ?>
        <script type="text/javascript">
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit();
    }
}

// Obtener el ID del contenido y el ID de estudiantes_docentes
$contenido_id = isset($_GET['contenido_id']) ? $_GET['contenido_id'] : null;
$ed_id = isset($_GET['ed_id']) ? $_GET['ed_id'] : null;

if (!$contenido_id || !$ed_id) {
    echo "Error: Faltan parámetros necesarios.";
    exit;
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Evaluaciones asignadas&nbsp;
            <a href="#" type="button" class="btn btn-primary bg-gradient-info" style="border-radius: 0px;" onclick="history.back()" value="volver atrás"><i class="fas fa-fw fa-backward"></i> Regresar</a>
        </h4>
    </div>

    <div class="card-body">
    <?php
    // Modificar esta parte para incluir información sobre entregas
$query = "SELECT e.evaluacion_id, e.titulo, e.descripcion, e.fecha, e.porcentaje,
CASE WHEN ev.ev_entregada_id IS NOT NULL THEN 1 ELSE 0 END AS entregada
FROM evaluaciones e
LEFT JOIN ev_entregadas ev ON e.evaluacion_id = ev.evaluacion_id AND ev.alumno_id = ? AND ev.ev_entregada_estado = 1
WHERE e.contenido_id = ? AND e.evaluacion_estado = 1";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "ii", $_SESSION['MEMBER_ID'], $contenido_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
while ($row = mysqli_fetch_assoc($result)) {
echo '<div class="card mb-3">';
echo '<div class="card-body">';
echo '<h5 class="card-title">Título: ' . htmlspecialchars($row['titulo']) . '</h5>';
echo '<p class="card-text">Descripción: ' . htmlspecialchars($row['descripcion']) . '</p>';
echo '<p class="card-text">Fecha: ' . date('d-m-Y', strtotime($row['fecha'])) . '</p>';
echo '<p class="card-text">Porcentaje de la evaluación: ' . htmlspecialchars($row['porcentaje']) . '%</p>';
echo '<hr>';
if ($row['entregada']) {
  echo '<a href="estudiantes_vista_entrega.php?evaluacion_id=' . $row['evaluacion_id'] . '&ed_id=' . $ed_id . '" class="btn btn-info btn-sm mr-2"><i class="fas fa-eye"></i> Ver entrega</a>';
} else {
  echo '<a href="estudiantes_vista_entrega.php?evaluacion_id=' . $row['evaluacion_id'] . '&ed_id=' . $ed_id . '" class="btn btn-success btn-sm mr-2"><i class="fas fa-pencil-ruler"></i> Realizar entrega</a>';
}
echo '</div>';
echo '</div>';
}
} else {
echo '<p>No hay evaluaciones asignadas para este contenido.</p>';
}
mysqli_stmt_close($stmt);
?>
    </div>
</div>

<?php
include '../includes/footer.php';
?>