<?php
include '../includes/connection.php';
include '../includes/sidebar_estudiante.php';

// Verificación del tipo de usuario y redirección
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
$result = mysqli_query($db, $query);
if (!$result) {
    die("Error en la consulta: " . mysqli_error($db));
}

while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa != 'Estudiante') {
        $redirectUrl = ($Aa == 'Admin') ? "index.php" : 
                       (($Aa == 'Docente') ? "docentes_vista.php" : 
                       (($Aa == 'Personal') ? "empleados_vista.php" : "sa_vista.php"));
        echo "<script type='text/javascript'>
                alert('Página restringida! Será redirigido.');
                window.location = '$redirectUrl';
              </script>";
        exit();
    }
}

/// Obtener el ID del estudiante
$queryStudent = "SELECT ESTUDIANTE_ID FROM users WHERE ID = " . $_SESSION['MEMBER_ID'];
$resultStudent = mysqli_query($db, $queryStudent);
if (!$resultStudent) {
    die("Error al obtener el ID del estudiante: " . mysqli_error($db));
}

if (mysqli_num_rows($resultStudent) > 0) {
    $rowStudent = mysqli_fetch_assoc($resultStudent);
    $estudianteId = $rowStudent['ESTUDIANTE_ID'];

    // Obtener el ed_id de la URL
    $edId = isset($_GET['ed_id']) ? $_GET['ed_id'] : null;

    if ($edId) {
        // Consulta para obtener las notas del estudiante para la asignatura específica
        $queryNotas = "
            SELECT e.titulo AS evaluacion, n.valor_nota AS nota, n.fecha, e.porcentaje
            FROM notas n
            JOIN ev_entregadas ev ON n.id_ev_entregada = ev.ev_entregada_id
            JOIN evaluaciones e ON ev.evaluacion_id = e.evaluacion_id
            JOIN contenidos c ON e.contenido_id = c.contenido_id
            JOIN docentes_asignaturas da ON c.da_id = da.da_id
            JOIN estudiantes_docentes ed ON da.da_id = ed.doc_asi_id
            WHERE ev.alumno_id = '$estudianteId' 
            AND ed.ed_id = '$edId' 
            AND n.nota_estado = 1
        ";
        $resultNotas = mysqli_query($db, $queryNotas);

        if (mysqli_num_rows($resultNotas) > 0) {
            $sumaPonderada = 0;
            $sumaPorcentajes = 0;
            ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h4 class="m-2 font-weight-bold text-primary">Mis notas
                    <a href="#" type="button" class="btn btn-primary bg-gradient-info" style="border-radius: 0px;" onclick="history.back()" value="volver atrás"><i class="fas fa-fw fa-backward"></i> Regresar</a>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>EVALUACIÓN</th>
                                    <th>NOTA</th>
                                    <th>PORCENTAJE</th>
                                    <th>FECHA</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($rowNota = mysqli_fetch_assoc($resultNotas)) {
                                    $nota = $rowNota['nota'];
                                    $porcentaje = $rowNota['porcentaje'];
                                    
                                    $notaPonderada = $nota * $porcentaje / 100;
                                    $sumaPonderada += $notaPonderada;
                                    $sumaPorcentajes += $porcentaje;
                                    ?>
                                    <tr>
                                        <td><?php echo $rowNota['evaluacion']; ?></td>
                                        <td><?php echo $nota; ?></td>
                                        <td><?php echo $porcentaje; ?>%</td>
                                        <td><?php echo date('d-m-Y', strtotime($rowNota['fecha'])); ?></td>
                                    </tr>
                                    <?php
                                }
                                
                                $promedioFinal = $sumaPonderada / $sumaPorcentajes * 100;
                                $promedioFinal = round($promedioFinal, 2);
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
            echo '<p>No se encontraron notas para esta asignatura.</p>';
            echo '<a href="#" type="button" class="btn btn-primary bg-gradient-info" style="border-radius: 0px;" onclick="history.back()" value="volver atrás"><i class="fas fa-fw fa-backward"></i> Regresar</a>';
        }
    } else {
        echo '<p>No se ha seleccionado ninguna asignatura.</p>';
        echo '<a href="#" type="button" class="btn btn-primary bg-gradient-info" style="border-radius: 0px;" onclick="history.back()" value="volver atrás"><i class="fas fa-fw fa-backward"></i> Regresar</a>';
    }
} else {
    echo '<p>Error: No se encontró el estudiante en la base de datos.</p>';
}

include '../includes/footer.php';
?>