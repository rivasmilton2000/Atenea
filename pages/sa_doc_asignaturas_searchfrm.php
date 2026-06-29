<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Personal' || $Aa == 'Estudiante' || $Aa == 'Docente' || $Aa == 'Admin') {
        if ($Aa == 'Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa == 'Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa == 'Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa == 'Admin') {
            $redirectUrl = "index.php";
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

if (isset($_GET['id'])) {
    $docenteAsignaturaId = $_GET['id'];

    $query = "SELECT g.G_NAME AS grado, CONCAT(e.FIRST_NAME, ' ', e.LAST_NAME) AS profesor, a.A_NAME AS materia, p.p_name AS periodo, da.da_estado
              FROM docentes_asignaturas da
              JOIN grados g ON da.grado_id = g.G_ID
              JOIN employee e ON da.profesor_id = e.EMPLOYEE_ID
              JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
              JOIN periodo p ON da.periodo_id = p.p_id
              WHERE da.da_id = $docenteAsignaturaId";

    $result = mysqli_query($db, $query) or die(mysqli_error($db));

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $grado = $row['grado'];
        $profesor = $row['profesor'];
        $materia = $row['materia'];
        $periodo = $row['periodo'];
        $estado = ($row['da_estado'] == 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
        ?>

        <center>
            <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
                <div class="card-header py-3">
                    <h4 class="m-2 font-weight-bold text-primary">Detalles de asignaturas de docentes</h4>
                </div>
                <a href="sa_doc_asignaturas.php" type="button" class="btn btn-primary bg-gradient-primary btn-block">
                    <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
                </a>
                <div class="card-body">
                    <div class="form-group row text-left">
                        <div class="col-sm-3 text-primary">
                            <h5>Docente</h5>
                        </div>
                        <div class="col-sm-9">
                            <h5>: <?php echo $profesor; ?></h5>
                        </div>
                    </div>
                    <div class="form-group row text-left">
                        <div class="col-sm-3 text-primary">
                            <h5>Asignatura</h5>
                        </div>
                        <div class="col-sm-9">
                            <h5>: <?php echo $materia; ?></h5>
                        </div>
                    </div>
                    <div class="form-group row text-left">
                        <div class="col-sm-3 text-primary">
                            <h5>Grado</h5>
                        </div>
                        <div class="col-sm-9">
                            <h5>: <?php echo $grado; ?></h5>
                        </div>
                    </div>
                    <div class="form-group row text-left">
                        <div class="col-sm-3 text-primary">
                            <h5>Trimestre</h5>
                        </div>
                        <div class="col-sm-9">
                            <h5>: <?php echo $periodo; ?></h5>
                        </div>
                    </div>
                    <div class="form-group row text-left">
                        <div class="col-sm-3 text-primary">
                            <h5>Estado</h5>
                        </div>
                        <div class="col-sm-9">
                            <h5>: <?php echo $estado; ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </center>

        <?php
    } else {
        echo "No se encontraron detalles para la asignatura del docente seleccionada.";
    }
} else {
    echo "No se ha proporcionado el ID de la asignatura del docente.";
}

include '../includes/footer_superadmin.php';
?>