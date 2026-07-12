<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
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
?>

<?php
    // Función para validar y formatear fecha
    function validateDate($date) {
        if (empty($date)) return "NULL";
        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'];
        foreach ($formats as $format) {
            $d = DateTime::createFromFormat($format, $date);
            if ($d && $d->format($format) === $date) {
                return $d->format('Y-m-d');
            }
        }
        return ""; // Si no coincide con ningún formato válido
    }
    
    // Función para validar y formatear hora
    function validateTime($time) {
        if (empty($time)) return "";
        if (preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $time)) {
            return $time;
        }
        return ""; // Si no es un formato de hora válido
    }
    
    // Obtener la información del trabajo
    $id = $_GET['id'];
    $query = "SELECT j.id, j.employee, e.FIRST_NAME, e.LAST_NAME, j.job, j.description, j.status, j.hour, j.date, j.maxhour, j.maxdate, j.j_estado
              FROM jobs j
              LEFT JOIN employee e ON j.employee = e.EMPLOYEE_ID
              WHERE j.id = ?";
    
    $stmt = mysqli_prepare($db, $query);
    if ($stmt === false) {
        die("Error en la preparación de la consulta: " . mysqli_error($db));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (!mysqli_stmt_execute($stmt)) {
        die("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    if ($result === false) {
        die("Error al obtener el resultado: " . mysqli_stmt_error($stmt));
    }
    
    if ($row = mysqli_fetch_array($result)) {
        $job_id = $row['id'];
        $employee_id = $row['employee'];
        $employee_name = $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'];
        $job = $row['job'];
        $description = $row['description'];
        $status = $row['status'];
        $hour = $row['hour'];
        $date = $row['date'];
        $maxhour = $row['maxhour'];
        $maxdate = $row['maxdate'];
        $j_estado = $row['j_estado'];
    } else {
        die("No se encontró ningún trabajo con el ID proporcionado.");
    }
    
    // Aquí comienza el HTML
    ?>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Editar labor</h4>
        </div>
        <a type="button" class="btn btn-primary bg-gradient-primary btn-block" href="sa_labores.php"> <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar </a>
        <div class="card-body">
            <form role="form" method="post" action="sa_labores_edit1.php">
                <input type="hidden" name="id" value="<?php echo $job_id; ?>" />

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Empleado:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" name="employee" value="<?php echo $employee_name; ?>" readonly>
                        <input type="hidden" name="employee_id" value="<?php echo $employee_id; ?>">
                    </div>
                </div>
                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Labor:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Labor" name="job" minlength="5" maxlength="80" value="<?php echo $job; ?>" required>
                    </div>
                </div>
                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Descripción del labor:
                    </div>
                    <div class="col-sm-9">
                        <textarea class="form-control" placeholder="Descripción del labor" name="description" minlength="5" maxlength="400" required><?php echo $description; ?></textarea>
                    </div>
                </div>
                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Estado del labor:
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control" name="status" required>
                            <option value="Completado" <?php if($status == 'Completado') echo 'selected'; ?>>Completado</option>
                            <option value="Incompleto" <?php if($status == 'Incompleto') echo 'selected'; ?>>Incompleto</option>
                            <option value="Tiempo Excedido" <?php if($status == 'Tiempo Excedido') echo 'selected'; ?>>Tiempo Excedido</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Hora:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Hora de finalización" name="hour" value="<?php echo $hour; ?>" readonly>
                    </div>
                </div>
                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Fecha:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Fecha de finalización" name="date" value="<?php echo $date; ?>" readonly>
                    </div>
                </div>
                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Hora máxima:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" type="time" placeholder="Hora máxima" name="maxhour" value="<?php echo $maxhour; ?>" required>
                    </div>
                </div>
                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Fecha máxima:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" type="date" placeholder="Fecha máxima" name="maxdate" value="<?php echo $maxdate; ?>" required>
                    </div>
                </div>
                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Estado:
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control" name="j_estado" required>
                            <option value="1" <?php if($j_estado == 1) echo 'selected'; ?>>Activo</option>
                            <option value="0" <?php if($j_estado == 0) echo 'selected'; ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
                <hr>
                <button type="submit" class="btn btn-warning btn-block"><i class="fa fa-edit fa-fw"></i> Actualizar</button>
            </form>  
        </div>
    </div>
</center>

<?php
include '../includes/footer_superadmin.php';
?>