<?php
include '../includes/connection.php';
include '../includes/sidebar_estudiante.php';

// 🔐 Validación
$query = 'SELECT ID, t.TYPE FROM users u 
          JOIN type t ON t.TYPE_ID=u.TYPE_ID 
          WHERE ID = ' . $_SESSION['MEMBER_ID'];

$result = mysqli_query($db, $query) or die(mysqli_error($db));

while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Admin' || $Aa == 'Docente' || $Aa == 'Personal' || $Aa == 'SuperAdmin') {

        $redirectUrl = "index.php";

        if ($Aa == 'Docente') $redirectUrl = "docentes_vista.php";
        if ($Aa == 'Personal') $redirectUrl = "empleados_vista.php";
        if ($Aa == 'SuperAdmin') $redirectUrl = "sa_vista.php";

        echo "<script>
            alert('Página restringida');
            window.location = '$redirectUrl';
        </script>";
        exit();
    }
}

// 📌 Obtener estudiante
$queryStudent = "SELECT ESTUDIANTE_ID FROM users WHERE ID = " . $_SESSION['MEMBER_ID'];
$resultStudent = mysqli_query($db, $queryStudent);

$rowStudent = mysqli_fetch_assoc($resultStudent);
$estudianteId = $rowStudent['ESTUDIANTE_ID'];

// 📚 Materias del estudiante
$querySubjects = "
    SELECT g.G_NAME, a.A_NAME, a.ASIGNATURA_ID, p.p_name
    FROM estudiantes_docentes ed
    JOIN docentes_asignaturas da ON ed.doc_asi_id = da.da_id
    JOIN grados g ON da.grado_id = g.G_ID
    JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
    JOIN periodo p ON da.periodo_id = p.p_id
    WHERE ed.estudiante_id = '$estudianteId' AND ed.ed_estado = 1
    ORDER BY g.G_ID, p.p_id
";

$resultSubjects = mysqli_query($db, $querySubjects);
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">📢 Anuncios por materia</h4>
    </div>

    <div class="card-body">
        <div class="row">

        <?php
        $colorClasses = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger'];
        $colorIndex = 0;

        while ($rowSubject = mysqli_fetch_assoc($resultSubjects)) {

            $colorClass = $colorClasses[$colorIndex % count($colorClasses)];
            $colorIndex++;

            $asignatura_id = $rowSubject['ASIGNATURA_ID'];

            // 🔔 Contar mensajes
            $queryMsg = "SELECT COUNT(*) as total 
                         FROM mensajes 
                         WHERE asignatura_id = $asignatura_id";

            $resMsg = mysqli_query($db, $queryMsg);
            $msgData = mysqli_fetch_assoc($resMsg);
            $totalMensajes = $msgData['total'];
        ?>

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">

                    <div class="card-img-container">
                        <img src="img/libros.jpg" class="card-img-top img-fluid">
                    </div>

                    <div class="card-body <?php echo $colorClass; ?> text-white">

                        <h5 class="card-title">
                            <?php echo $rowSubject['A_NAME']; ?>
                        </h5>

                        <p>Grado: <?php echo $rowSubject['G_NAME']; ?></p>
                        <p>Trimestre: <?php echo $rowSubject['p_name']; ?></p>

                        <!-- 🔔 MENSAJES -->
                        <p>
                            📩 Mensajes: 
                            <strong><?php echo $totalMensajes; ?></strong>
                        </p>

                        <!-- 💬 BOTÓN CHAT -->
                        <a href="mensajes_estudiante.php?asignatura_id=<?php echo $asignatura_id; ?>" 
                           class="btn btn-light btn-block">
                           Ver anuncios
                        </a>

                    </div>
                </div>
            </div>

        <?php } ?>

        </div>
    </div>
</div>

<style>
.card-img-container {
    height: 180px;
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

<?php include '../includes/footer.php'; ?>