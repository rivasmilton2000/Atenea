<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'];
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
            //then it will be redirected
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit(); // Terminar la ejecución del script después de la redirección
    }
}

$query = 'SELECT EMPLOYEE_ID, FIRST_NAME, LAST_NAME, GENDER, EMAIL, PHONE_NUMBER, j.JOB_TITLE, HIRED_DATE, l.PROVINCE, l.CITY, e.E_ESTADO 
          FROM employee e 
          JOIN location l ON e.LOCATION_ID = l.LOCATION_ID 
          JOIN job j ON j.JOB_ID = e.JOB_ID 
          WHERE e.EMPLOYEE_ID =' . $_GET['id'];
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_array($result)) {
    $zz = $row['EMPLOYEE_ID'];
    $i = $row['FIRST_NAME'];
    $ii = $row['LAST_NAME'];
    $iii = $row['GENDER'];
    $a = $row['EMAIL'];
    $b = $row['PHONE_NUMBER'];
    $c = $row['JOB_TITLE'];
    $d = date("d-m-Y", strtotime($row['HIRED_DATE'])); // Formatear la fecha a dd-mm-yyyy
    $f = $row['PROVINCE'];
    $g = $row['CITY'];
    $estado = $row['E_ESTADO'] == 1 ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
}
$id = $_GET['id'];
?>
<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Detalles del docente</h4>
        </div>
        <a href="sa_docentes.php" type="button" class="btn btn-primary bg-gradient-primary btn-block"> <i
                class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar </a>
        <div class="card-body">


            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>
                        Nombre completo<br>
                    </h5>
                </div>
                <div class="col-sm-9">
                    <h5>
                        : <?php echo $i; ?> <?php echo $ii; ?> <br>
                    </h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>
                        Género<br>
                    </h5>
                </div>
                <div class="col-sm-9">
                    <h5>
                        : <?php echo $iii; ?> <br>
                    </h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>
                        Correo electrónico<br>
                    </h5>
                </div>
                <div class="col-sm-9">
                    <h5>
                        : <?php echo $a; ?> <br>
                    </h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>
                        Número teléfonico<br>
                    </h5>
                </div>
                <div class="col-sm-9">
                    <h5>
                        : <?php echo $b; ?> <br>
                    </h5>
                </div>
            </div>

            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>
                        Dirección<br>
                    </h5>
                </div>
                <div class="col-sm-9">
                    <h5>
                        : <?php echo $g; ?>, <?php echo $f; ?> <br>
                    </h5>
                </div>
            </div>

            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>
                        Rol<br>
                    </h5>
                </div>
                <div class="col-sm-9">
                    <h5>
                        : <?php echo $c; ?> <br>
                    </h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>
                        Fecha de contratación<br>
                    </h5>
                </div>
                <div class="col-sm-9">
                    <h5>
                        : <?php echo $d; ?> <br>
                    </h5>
                </div>
            </div>

            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>
                        Estado<br>
                    </h5>
                </div>
                <div class="col-sm-9">
                    <h5>
                        : <?php echo $estado; ?> <br>
                    </h5>
                </div>
            </div>


        </div>
    </div>

</div>
</div>

<?php
include '../includes/footer_superadmin.php';
?>
