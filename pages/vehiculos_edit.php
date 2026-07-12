<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';

// Verificar el tipo de usuario y redirigir si es necesario
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $user_type = $row['TYPE'];
    $redirect_urls = [
        'Personal' => 'empleados_vista.php',
        'Estudiante' => 'estudiante_vista.php',
        'Docente' => 'docentes_vista.php',
        'SuperAdmin' => 'sa_vista.php'
    ];

    if (isset($redirect_urls[$user_type])) {
        ?>
        <script type="text/javascript">
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirect_urls[$user_type]; ?>";
        </script>
        <?php
        exit();
    }
}

// Obtener datos del vehículo a editar
$id = $_GET['id'];
$query = 'SELECT id, vehicle_license, vehicle_model, vehicle_attendant, vehicle_image FROM vehicles WHERE id = ' . $id;
$result = mysqli_query($db, $query) or die(mysqli_error($db));
$row = mysqli_fetch_assoc($result);

// Obtener opciones para el encargado del vehículo
$sql = "SELECT DISTINCT CONCAT(e.FIRST_NAME, ' ', e.LAST_NAME, ' - ', j.JOB_TITLE) AS EMPLOYEE_NAME, e.EMPLOYEE_ID
        FROM employee e
        INNER JOIN job j ON e.JOB_ID = j.JOB_ID
        WHERE e.JOB_ID IN (2, 3)
        ORDER BY e.LAST_NAME, e.FIRST_NAME";
$optionResult = mysqli_query($db, $sql) or die("Bad SQL: $sql");
$options = "<select class='form-control' name='vehicle_attendant' required>
            <option value='' disabled selected hidden>Seleccionar encargado</option>
            <option value=''>No asignado</option>";

// Modificar la consulta SQL para filtrar por E_ESTADO = 1
$sql = "SELECT DISTINCT CONCAT(e.FIRST_NAME, ' ', e.LAST_NAME, ' - ', j.JOB_TITLE) AS EMPLOYEE_NAME, e.EMPLOYEE_ID
        FROM employee e
        INNER JOIN job j ON e.JOB_ID = j.JOB_ID
        WHERE e.JOB_ID IN (2, 3) AND e.E_ESTADO = 1
        ORDER BY e.LAST_NAME, e.FIRST_NAME";

$optionResult = mysqli_query($db, $sql) or die("Bad SQL: $sql");

while ($optionRow = mysqli_fetch_assoc($optionResult)) {
    $selected = ($optionRow['EMPLOYEE_ID'] == $row['vehicle_attendant']) ? 'selected' : '';
    $options .= "<option value='" . $optionRow['EMPLOYEE_ID'] . "' $selected>" . $optionRow['EMPLOYEE_NAME'] . "</option>";
}
$options .= "</select>";
?>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Editar vehículo</h4>
        </div>
        <a href="vehiculos.php?action=add" type="button" class="btn btn-primary bg-gradient-primary"><i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar</a>
        <div class="card-body">
            <form role="form" method="post" action="vehiculos_edit1.php" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
                
                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Placa del vehículo:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Placa del vehículo" maxlength="7" minlength="5" name="vehicle_license" value="<?php echo $row['vehicle_license']; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Modelo del vehículo:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Modelo del vehículo" minlength="5" maxlength="40" name="vehicle_model" value="<?php echo $row['vehicle_model']; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Encargado del vehículo:
                    </div>
                    <div class="col-sm-9">
                        <?php echo $options; ?>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Imagen actual del vehículo:
                    </div>
                    <div class="col-sm-9">
                        <?php if ($row['vehicle_image']) : ?>
                            <img src="<?php echo $row['vehicle_image']; ?>" alt="Vehicle Image" style="max-width: 100%;">
                        <?php else : ?>
                            <p>No hay imagen disponible</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Cambiar imagen del vehículo:
                    </div>
                    <div class="col-sm-9">
                        <input type="file" class="form-control-file" name="vehicle_image">
                    </div>
                </div>

                <hr>
                <button type="submit" class="btn btn-warning btn-block"><i class="fa fa-edit fa-fw"></i> Actualizar</button>
            </form>
        </div>
    </div>
</center>

<?php
include '../includes/footer.php';
?>
