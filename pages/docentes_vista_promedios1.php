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

// Obtener el ID del empleado correspondiente al nombre de usuario desde la base de datos
$queryEmployee = "SELECT EMPLOYEE_ID FROM employee WHERE FIRST_NAME = '{$_SESSION['FIRST_NAME']}' AND LAST_NAME = '{$_SESSION['LAST_NAME']}'";
$resultEmployee = mysqli_query($db, $queryEmployee);

// Verificar si se encontró el empleado en la base de datos
if (mysqli_num_rows($resultEmployee) > 0) {
    $rowEmployee = mysqli_fetch_assoc($resultEmployee);
    $employeeId = $rowEmployee['EMPLOYEE_ID'];

    // Obtener el ID de la asignatura seleccionada desde el parámetro de la URL
    $daId = $_GET['id'];

    // Obtener el ID del período correspondiente a la asignatura seleccionada
    $queryPeriod = "SELECT periodo_id FROM docentes_asignaturas WHERE da_id = '$daId'";
    $resultPeriod = mysqli_query($db, $queryPeriod);
    $rowPeriod = mysqli_fetch_assoc($resultPeriod);
    $periodId = $rowPeriod['periodo_id'];

    // Consultar la base de datos para obtener los estudiantes asignados a la asignatura y docente específico
    $queryStudents = "
        SELECT e.ESTUDIANTE_ID, e.nombres_estudiante, e.apellidos_estudiante, e.numero_lista_estudiante, e.carnet_estudiante
        FROM estudiantes_docentes ed
        JOIN estudiantes e ON ed.estudiante_id = e.ESTUDIANTE_ID
        WHERE ed.doc_asi_id = '$daId' AND ed.periodo_id = '$periodId'
        AND ed_estado = 1
        ORDER BY e.apellidos_estudiante, e.nombres_estudiante
    ";
    $resultStudents = mysqli_query($db, $queryStudents);

    // Verificar si se encontraron estudiantes asignados
    if (mysqli_num_rows($resultStudents) > 0) {
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Estudiantes asignados</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ESTUDIANTE</th>
                        <th>CARNET</th>
                        <th>NÚM. LISTA</th>
                        <th>NOTAS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($rowStudent = mysqli_fetch_assoc($resultStudents)) {
                    ?>
                        <tr>
                            <td><?php echo $rowStudent['apellidos_estudiante'] . ', ' . $rowStudent['nombres_estudiante']; ?></td>
                            <td><?php echo $rowStudent['carnet_estudiante']; ?></td>
                            <td><?php echo $rowStudent['numero_lista_estudiante']; ?></td>
                            <td>
                                <a href="docentes_vista_notas.php?id_estudiante=<?php echo $rowStudent['ESTUDIANTE_ID']; ?>&id_asignatura=<?php echo $daId; ?>" class="btn btn-info">
                                    <i class="fas fa-list"></i> Ver notas
                                </a>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
    } else {
        echo '<p>No se encontraron estudiantes asignados.</p>';
    }
} else {
    echo '<p>Error: No se encontró el empleado en la base de datos.</p>';
}

include '../includes/footer.php';
?>