<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
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

// Obtener la información de la asignatura del docente
$id = $_GET['id'];
$query = "SELECT da.da_id, da.profesor_id, CONCAT(e.FIRST_NAME, ' ', e.LAST_NAME) AS profesor_nombre, 
          da.materia_id, a.A_NAME AS materia_nombre, 
          da.grado_id, g.G_NAME AS grado_nombre, 
          da.periodo_id, p.p_name AS periodo_nombre
          FROM docentes_asignaturas da
          JOIN employee e ON da.profesor_id = e.EMPLOYEE_ID
          JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
          JOIN grados g ON da.grado_id = g.G_ID
          JOIN periodo p ON da.periodo_id = p.p_id
          WHERE da.da_id = ?";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_array($result)) {
    $da_id = $row['da_id'];
    $profesor_id = $row['profesor_id'];
    $profesor_nombre = $row['profesor_nombre'];
    $materia_id = $row['materia_id'];
    $materia_nombre = $row['materia_nombre'];
    $grado_id = $row['grado_id'];
    $grado_nombre = $row['grado_nombre'];
    $periodo_id = $row['periodo_id'];
    $periodo_nombre = $row['periodo_nombre'];
} else {
    die("No se encontró ninguna asignatura de docente con el ID proporcionado.");
}
?>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Editar Asignatura de Docente</h4>
        </div>
        <a type="button" class="btn btn-primary bg-gradient-primary btn-block" href="doc_asignaturas.php"> <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar </a>
        <div class="card-body">
            <form role="form" method="post" action="doc_asignaturas_edit1.php">
                <input type="hidden" name="da_id" value="<?php echo $da_id; ?>" />

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Docente:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" name="profesor_nombre" value="<?php echo $profesor_nombre; ?>" readonly>
                        <input type="hidden" name="profesor_id" value="<?php echo $profesor_id; ?>">
                    </div>
                </div>
                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Asignatura:
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control" name="materia_id" required>
                            <?php
                            $query = 'SELECT ASIGNATURA_ID, A_NAME FROM asignaturas WHERE A_ESTADO = 1';
                            $result = mysqli_query($db, $query) or die(mysqli_error($db));
                            while ($row = mysqli_fetch_assoc($result)) {
                                $selected = ($row['ASIGNATURA_ID'] == $materia_id) ? 'selected' : '';
                                echo "<option value='".$row['ASIGNATURA_ID']."' ".$selected.">".$row['A_NAME']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Grado:
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control" name="grado_id" required>
                            <?php
                            $query = 'SELECT G_ID, G_NAME FROM grados WHERE G_ESTADO = 1';
                            $result = mysqli_query($db, $query) or die(mysqli_error($db));
                            while ($row = mysqli_fetch_assoc($result)) {
                                $selected = ($row['G_ID'] == $grado_id) ? 'selected' : '';
                                echo "<option value='".$row['G_ID']."' ".$selected.">".$row['G_NAME']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Trimestre:
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control" name="periodo_id" required>
                            <?php
                            $query = 'SELECT p_id, p_name FROM periodo';
                            $result = mysqli_query($db, $query) or die(mysqli_error($db));
                            while ($row = mysqli_fetch_assoc($result)) {
                                $selected = ($row['p_id'] == $periodo_id) ? 'selected' : '';
                                echo "<option value='".$row['p_id']."' ".$selected.">".$row['p_name']."</option>";
                            }
                            ?>
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
include '../includes/footer2.php';
?>