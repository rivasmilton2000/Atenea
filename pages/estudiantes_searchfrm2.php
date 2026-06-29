<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa=='Personal' || $Aa=='Estudiante' || $Aa=='Docente' || $Aa=='SuperAdmin'){
        if ($Aa=='Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa=='Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa=='Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa=='SuperAdmin') {
          $redirectUrl = "sa_vista.php";
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

$estudiante_id = $_GET['id'];

$query = "SELECT e.nombres_encargado, e.apellidos_encargado, e.dui_encargado, e.direccion_encargado, e.correo_encargado, e.trabajo_encargado, e.numero_cel_encargado, e.numero_tel_encargado, e.genero_encargado, e.fecha_nac_encargado
FROM estudiantes e
WHERE e.ESTUDIANTE_ID = $estudiante_id";

$result = mysqli_query($db, $query) or die(mysqli_error($db));
$row = mysqli_fetch_assoc($result);
?>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Información del encargado</h4>
        </div>
        <a href="estudiantes.php" type="button" class="btn btn-primary bg-gradient-primary btn-block"> <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar </a>
        <div class="card-body">
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Nombres</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['nombres_encargado']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Apellidos</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['apellidos_encargado']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>DUI</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['dui_encargado']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Dirección</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['direccion_encargado']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Correo electrónico</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['correo_encargado']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Oficio</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['trabajo_encargado']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Número de celular</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['numero_cel_encargado']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Número de teléfono</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['numero_tel_encargado']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Género</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['genero_encargado']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Fecha de nacimiento</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo date('d-m-Y', strtotime($row['fecha_nac_encargado'])); ?></h5>
                </div>
            </div>
        </div>
    </div>
</center>

<?php
include '../includes/footer.php';
?>