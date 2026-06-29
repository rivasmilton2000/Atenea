<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa=='Personal' || $Aa=='Estudiante' || $Aa=='Docente' || $Aa=='Admin'){
        if ($Aa=='Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa=='Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa=='Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa=='Admin') {
            $redirectUrl = "index.php";
        }
        ?>
        <script type="text/javascript">
            //then it will be redirected
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit(); // Terminar la ejecución del script después de la redirección
    }
}

$query = 'SELECT ed.ed_id, a.A_NAME AS materia, g.G_NAME AS grado, emp.FIRST_NAME, emp.LAST_NAME, p.p_name AS periodo,
                 e.nombres_estudiante, e.apellidos_estudiante, e.carnet_estudiante, e.numero_lista_estudiante, ge.G_NAME AS grado_estudiante,
                 ed.ed_estado
          FROM estudiantes_docentes ed
          JOIN estudiantes e ON ed.estudiante_id = e.ESTUDIANTE_ID
          JOIN grados ge ON e.grado_id_estudiante = ge.G_ID
          JOIN docentes_asignaturas da ON ed.doc_asi_id = da.da_id
          JOIN grados g ON da.grado_id = g.G_ID
          JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
          JOIN employee emp ON da.profesor_id = emp.EMPLOYEE_ID
          JOIN periodo p ON ed.periodo_id = p.p_id
          WHERE ed.ed_id = ' . $_GET['id'];
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_array($result)) {
    $materia = $row['materia'];
    $grado = $row['grado'];
    $docente = $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'];
    $periodo = $row['periodo'];
    $estudiante = $row['nombres_estudiante'] . ' ' . $row['apellidos_estudiante'];
    $carnet = $row['carnet_estudiante'];
    $numero_lista = $row['numero_lista_estudiante'];
    $grado_estudiante = $row['grado_estudiante'];
    $estado = $row['ed_estado'];
}
$id = $_GET['id'];
?>
<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Detalles de la asignación</h4>
        </div>
        <a href="sa_dis_asignaturas.php" type="button" class="btn btn-primary bg-gradient-primary btn-block"> <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar </a>
        <div class="card-body">
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
                    <h5>Docente</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $docente; ?></h5>
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
            <hr>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Estudiante</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $estudiante; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Grado del estudiante</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $grado_estudiante; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Carnet</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $carnet; ?></h5>
                </div>
            </div>
            
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Número de lista</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $numero_lista; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Estado</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: 
                        <?php 
                        if ($estado == 1) {
                            echo '<span class="badge badge-success">Activo</span>';
                        } else {
                            echo '<span class="badge badge-danger">Inactivo</span>';
                        }
                        ?>
                    </h5>
                </div>
            </div>
        </div>
    </div>
</center>

<?php
include '../includes/footer_superadmin.php';
?>