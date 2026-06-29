<?php
include '../includes/connection.php';
include '../includes/sidebar_docente.php';

// 🔐 Validación de usuario
$query = 'SELECT ID, t.TYPE FROM users u 
          JOIN type t ON t.TYPE_ID=u.TYPE_ID 
          WHERE ID = ' . $_SESSION['MEMBER_ID'];

$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa != 'Docente') {
        echo "<script>
            alert('Página restringida');
            window.location = 'index.php';
        </script>";
        exit();
    }
}

// 🔎 Obtener ID del docente (employee)
$queryEmployee = "SELECT EMPLOYEE_ID 
                  FROM employee 
                  WHERE FIRST_NAME = '{$_SESSION['FIRST_NAME']}' 
                  AND LAST_NAME = '{$_SESSION['LAST_NAME']}'";

$resultEmployee = mysqli_query($db, $queryEmployee);

if (mysqli_num_rows($resultEmployee) > 0) {

    $rowEmployee = mysqli_fetch_assoc($resultEmployee);
    $employeeId = $rowEmployee['EMPLOYEE_ID'];

    // 📚 Materias del docente
    $querySubjects = "
        SELECT da.da_id, g.G_NAME, a.A_NAME, a.ASIGNATURA_ID, p.p_name
        FROM docentes_asignaturas da
        JOIN grados g ON da.grado_id = g.G_ID
        JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
        JOIN periodo p ON da.periodo_id = p.p_id
        WHERE da.profesor_id = '$employeeId' 
        AND da.da_estado = 1
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
                $colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger'];
                $i = 0;

                while ($rowSubject = mysqli_fetch_assoc($resultSubjects)) {
                    $color = $colors[$i % count($colors)];
                    $i++;
                ?>

                    <div class="col-md-4 mb-4">
                        <div class="card shadow">

                            <div class="card-img-container">
                                <img src="img/libros.jpg" class="card-img-top">
                            </div>

                            <div class="card-body <?php echo $color; ?> text-white">
                                <h5><?php echo $rowSubject['A_NAME']; ?></h5>
                                <p>Grado: <?php echo $rowSubject['G_NAME']; ?></p>
                                <p>Periodo: <?php echo $rowSubject['p_name']; ?></p>

                                <!-- 🔥 BOTÓN CHAT -->
                                <a href="mensajes_docente.php?asignatura_id=<?php echo $rowSubject['ASIGNATURA_ID']; ?>"
                                    class="btn btn-light btn-block">
                                    Entrar al chat 💬
                                </a>
                            </div>

                        </div>
                    </div>

                <?php } ?>
            </div>
        </div>
    </div>

<?php } ?>

<style>
    .card-img-container {
        height: 180px;
        overflow: hidden;
    }

    .card-img-top {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

<?php include '../includes/footer.php'; ?>