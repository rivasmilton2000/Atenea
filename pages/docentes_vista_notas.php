<?php
include '../includes/connection.php';
include '../includes/sidebar_docente.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Admin' || $Aa == 'Estudiante' || $Aa == 'Personal' || $Aa == 'SuperAdmin') {
        if ($Aa == 'Admin') {
            $redirectUrl = "index.php";
        } elseif ($Aa == 'Estudiante') {
            $redirectUrl = "estudiante_vista.php";
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

// Obtener el ID del estudiante y el ID de la asignatura desde los parámetros de la URL
$estudianteId = $_GET['id_estudiante'];
$asignaturaId = $_GET['id_asignatura'];

// Consultar la base de datos para obtener el nombre del estudiante
$queryEstudiante = "SELECT nombres_estudiante, apellidos_estudiante FROM estudiantes WHERE ESTUDIANTE_ID = '$estudianteId'";
$resultEstudiante = mysqli_query($db, $queryEstudiante);
$rowEstudiante = mysqli_fetch_assoc($resultEstudiante);
$nombreEstudiante = $rowEstudiante['apellidos_estudiante'] . ', ' . $rowEstudiante['nombres_estudiante'];

// Modificar la consulta para incluir la condición nota_estado = 1
$queryNotas = "
    SELECT e.titulo AS evaluacion, n.valor_nota AS nota, n.fecha, e.porcentaje
    FROM notas n
    JOIN ev_entregadas ev ON n.id_ev_entregada = ev.ev_entregada_id
    JOIN evaluaciones e ON ev.evaluacion_id = e.evaluacion_id
    JOIN contenidos c ON e.contenido_id = c.contenido_id
    JOIN docentes_asignaturas da ON c.da_id = da.da_id
    WHERE ev.alumno_id = '$estudianteId' AND da.da_id = '$asignaturaId' AND n.nota_estado = 1
";
$resultNotas = mysqli_query($db, $queryNotas);

// Verificar si se encontraron notas para el estudiante en la asignatura
if (mysqli_num_rows($resultNotas) > 0) {
    // Variables para almacenar la suma de las notas ponderadas y la suma de los porcentajes
    $sumaPonderada = 0;
    $sumaPorcentajes = 0;
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Notas de <?php echo $nombreEstudiante; ?>
        <a href="#" type="button" class="btn btn-primary bg-gradient-info" style="border-radius: 0px;" onclick="history.back()" value="volver atrás"><i class="fas fa-fw fa-backward"></i> Regresar</a>
        </h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ESTUDIANTE</th>
                        <th>EVALUACIÓN</th>
                        <th>NOTA</th>
                        <th>PORCENTAJE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($rowNota = mysqli_fetch_assoc($resultNotas)) {
                        $nota = $rowNota['nota'];
                        $porcentaje = $rowNota['porcentaje'];
                        
                        // Calcular la nota ponderada y acumular la suma ponderada y la suma de porcentajes
                        $notaPonderada = $nota * $porcentaje / 100;
                        $sumaPonderada += $notaPonderada;
                        $sumaPorcentajes += $porcentaje;
                    ?>
                        <tr>
                            <td><?php echo $nombreEstudiante; ?></td>
                            <td><?php echo $rowNota['evaluacion']; ?></td>
                            <td><?php echo $nota; ?></td>
                            <td><?php echo $porcentaje; ?>%</td>
                        </tr>
                    <?php
                    }
                    
                    // Calcular el promedio final
                    $promedioFinal = $sumaPonderada / $sumaPorcentajes * 100;
                    $promedioFinal = round($promedioFinal, 2); // Redondear a 2 decimales
                    ?>
                </tbody>
            </table>
        </div>
        <div class="text-center mt-3">
            <h4>Promedio final: <?php echo $promedioFinal; ?></h4>
        </div>
    </div>
</div>
<?php
} else {
    echo '<p>No se encontraron notas activas para este estudiante en la asignatura seleccionada.</p>';
    echo '<a href="#" type="button" class="btn btn-primary bg-gradient-info" style="border-radius: 0px;" onclick="history.back()" value="volver atrás"><i class="fas fa-fw fa-backward"></i> Regresar</a>';
}

include '../includes/footer.php';
?>