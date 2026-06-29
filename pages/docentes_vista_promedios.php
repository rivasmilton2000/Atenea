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

    // Consultar la base de datos para obtener las materias/asignaturas asignadas al docente
    $querySubjects = "
        SELECT da.da_id, g.G_NAME, a.A_NAME, a.ASIGNATURA_ID, p.p_name
        FROM docentes_asignaturas da
        JOIN grados g ON da.grado_id = g.G_ID
        JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
        JOIN periodo p ON da.periodo_id = p.p_id
        WHERE da.profesor_id = '$employeeId' AND da.da_estado = 1
        ORDER BY g.G_ID, p.p_id
    ";
    $resultSubjects = mysqli_query($db, $querySubjects);

    // Verificar si se encontraron materias/asignaturas asignadas
    if (mysqli_num_rows($resultSubjects) > 0) {
?>
<div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-2 font-weight-bold text-primary">Promedios de asignaturas</h4>
            </div>
            <div class="card-body">
        <div class="row">
            <?php
            $colorClasses = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger'];
            $colorIndex = 0;
            
            while ($rowSubject = mysqli_fetch_assoc($resultSubjects)) {
                $colorClass = $colorClasses[$colorIndex % count($colorClasses)];
                $colorIndex++;
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-img-container">
                            <img src="img/test.jpg" class="card-img-top img-fluid" alt="Imagen de la materia">
                        </div>
                        <div class="card-body <?php echo $colorClass; ?> text-white">
                            <h5 class="card-title"><?php echo $rowSubject['A_NAME']; ?></h5>
                            <p class="card-text">Grado: <?php echo $rowSubject['G_NAME']; ?></p>
                            <p class="card-text">Trimestre: <?php echo $rowSubject['p_name']; ?></p>
                            <div class="d-flex flex-wrap justify-content-between">
                                <a href="docentes_vista_promedios1.php?id=<?php echo $rowSubject['da_id']; ?>" class="btn btn-light mb-2">Acceder</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
        </div>
<?php
    } else {
        echo '<p>No se encontraron asignaturas asignadas.</p>';
    }
} else {
    echo '<p>Error: No se encontró el empleado en la base de datos.</p>';
}
?>
</div>

<style>
    .card-img-container {
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .card-img-top {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
    }
</style>

<?php
include '../includes/footer.php';
?>