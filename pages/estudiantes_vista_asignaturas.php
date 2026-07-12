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
    if ($Aa == 'Admin' || $Aa == 'Docente' || $Aa == 'Personal' || $Aa == 'SuperAdmin') {
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

// Obtener el ID del estudiante
$queryStudent = "SELECT ESTUDIANTE_ID FROM users WHERE ID = " . $_SESSION['MEMBER_ID'];
$resultStudent = mysqli_query($db, $queryStudent);
if (!$resultStudent) {
    die("Error al obtener el ID del estudiante: " . mysqli_error($db));
}

if (mysqli_num_rows($resultStudent) > 0) {
    $rowStudent = mysqli_fetch_assoc($resultStudent);
    $estudianteId = $rowStudent['ESTUDIANTE_ID'];

    // Consulta para obtener las asignaturas del estudiante
    $querySubjects = "
        SELECT ed.ed_id, g.G_NAME, a.A_NAME, a.ASIGNATURA_ID, p.p_name
        FROM estudiantes_docentes ed
        JOIN docentes_asignaturas da ON ed.doc_asi_id = da.da_id
        JOIN grados g ON da.grado_id = g.G_ID
        JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
        JOIN periodo p ON da.periodo_id = p.p_id
        WHERE ed.estudiante_id = '$estudianteId' AND ed.ed_estado = 1
        ORDER BY g.G_ID, p.p_id
    ";
    $resultSubjects = mysqli_query($db, $querySubjects);
    if (!$resultSubjects) {
        die("Error al obtener las asignaturas: " . mysqli_error($db));
    }

    if (mysqli_num_rows($resultSubjects) > 0) {
        // Código para mostrar las asignaturas (sin cambios)
        ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-2 font-weight-bold text-primary">Mis asignaturas</h4>
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
                                    <img src="img/libros.jpg" class="card-img-top img-fluid" alt="Imagen de la materia">
                                </div>
                                <div class="card-body <?php echo $colorClass; ?> text-white">
                                    <h5 class="card-title"><?php echo $rowSubject['A_NAME']; ?></h5>
                                    <p class="card-text">Grado: <?php echo $rowSubject['G_NAME']; ?></p>
                                    <p class="card-text">Trimestre: <?php echo $rowSubject['p_name']; ?></p>
                                    <div class="d-flex flex-wrap justify-content-between">
                                        <a href="estudiantes_vista_contenidos.php?id=<?php echo $rowSubject['ed_id']; ?>" class="btn btn-light mb-2">Acceder</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    } else {
        echo '<p>No se encontraron asignaturas asignadas.</p>';
    }
} else {
    echo '<p>Error: No se encontró el estudiante en la base de datos.</p>';
}
?>

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