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

$estudiante_id = $_GET['id'];

$query = "SELECT e.nombres_estudiante, e.apellidos_estudiante, e.direccion_estudiante, e.correo_estudiante, e.fecha_nac_estudiante, e.edad_estudiante, e.genero_estudiante, g.G_NAME AS grado, e.carnet_estudiante, e.info_medica_estudiante, e.numero_lista_estudiante, e.fecha_reg_estudiante, e.foto_estudiante, e.estado_estudiante
FROM estudiantes e
JOIN grados g ON e.grado_id_estudiante = g.G_ID
WHERE e.ESTUDIANTE_ID = $estudiante_id";

$result = mysqli_query($db, $query) or die(mysqli_error($db));
$row = mysqli_fetch_assoc($result);
?>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Información del estudiante</h4>
        </div>
        <a href="sa_estudiantes.php" type="button" class="btn btn-primary bg-gradient-primary btn-block"> <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar </a>
        <div class="card-body">
            <?php if ($row['foto_estudiante']) : ?>
                <div class="form-group row text-center">
                    <div class="col-sm-12">
                        <img src="imagenes_estudiantes/<?php echo $row['foto_estudiante']; ?>" alt="Foto del estudiante" style="max-width: 250px; max-height: 250px; object-fit: cover;">
                    </div>
                </div>
            <?php else : ?>
                <div class="form-group row text-center">
                    <div class="col-sm-12">
                        <h5>Estudiante sin foto</h5>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Nombres</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['nombres_estudiante']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Apellidos</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['apellidos_estudiante']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Dirección</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['direccion_estudiante']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Correo electrónico</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['correo_estudiante']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Fecha de nacimiento</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo date('d-m-Y', strtotime($row['fecha_nac_estudiante'])); ?></h5>
                </div>
            </div>

            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Edad</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['edad_estudiante']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Género</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['genero_estudiante']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Grado</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['grado']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Carnet</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['carnet_estudiante']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Número de lista</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['numero_lista_estudiante']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Observaciones médicas</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['info_medica_estudiante']; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Fecha de registro</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $row['fecha_reg_estudiante']; ?></h5>
                </div>
            </div>

            <!-- Estado del estudiante con formato de badge -->
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Estado</h5>
                </div>
                <div class="col-sm-9">
                    <h5>:
                    <?php if ($row['estado_estudiante'] == 1) : ?>
                        <span class="badge badge-success">Activo</span>
                    <?php else : ?>
                        <span class="badge badge-danger">Inactivo</span>
                    <?php endif; ?>
                    </h5>
                </div>
            </div>
        </div>
    </div>
</center>

<?php
include '../includes/footer_superadmin.php';
?>