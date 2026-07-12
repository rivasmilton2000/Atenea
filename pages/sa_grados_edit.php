<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
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

$id = $_GET['id'];

$query = "SELECT G_ID, G_NAME, G_ESTADO FROM grados WHERE G_ID = ?";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_array($result)) {
    $zz = $row['G_ID'];
    $a = $row['G_NAME'];
    $b = $row['G_ESTADO'];
}
?>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Editar grado</h4>
        </div>
        <a type="button" class="btn btn-primary bg-gradient-primary btn-block" href="sa_grados.php"> 
            <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar 
        </a>
        <div class="card-body">
            <form role="form" method="post" action="sa_grados_edit1.php">
                <input type="hidden" name="id" value="<?php echo $zz; ?>" />

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Nombre del grado:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" minlength="5" maxlength="30" placeholder="Nombre del grado" name="grado" value="<?php echo $a; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Estado del grado:
                    </div>
                    <div class="col-sm-9">
                        <select class='form-control' name='estado' required>
                            <option value="" disabled selected hidden>Seleccionar estado</option>
                            <option value="1" <?php if($b == 1) echo 'selected'; ?>>Activo</option>
                            <option value="0" <?php if($b == 0) echo 'selected'; ?>>Inactivo</option>
                        </select>
                    </div>
                </div>

                <hr>

                <button type="submit" class="btn btn-warning btn-block">
                    <i class="fa fa-edit fa-fw"></i>Actualizar
                </button>    
            </form>  
        </div>
    </div>
</center>

<?php
include '../includes/footer_superadmin.php';
?>