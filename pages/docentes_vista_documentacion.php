<?php
include '../includes/connection.php';
include '../includes/sidebar_docente.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Admin' || $Aa == 'Estudiante' || $Aa == 'Personal' || $Aa == 'SuperAdmin') {
        if ($Aa == 'Admin') {
            $redirectUrl = "index.php";
        } elseif ($Aa == 'Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa == 'Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa == 'SuperAdmin') {
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

$sql = "SELECT DISTINCT G_NAME, G_ID FROM grados ORDER BY G_NAME ASC";
$result = mysqli_query($db, $sql) or die ("Bad SQL: $sql");

$aaa = "<select class='form-control' name='grado' required>
        <option disabled selected hidden>Seleccionar grado</option>";
while ($row = mysqli_fetch_assoc($result)) {
    $aaa .= "<option value='".$row['G_ID']."'>".$row['G_NAME']."</option>";
}

$aaa .= "</select>";
?>
            
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Documentación&nbsp;</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"> 
    <thead>
        <tr>
            <th>DOCUMENTO</th>
            <th>PERMISOS</th>
            <th>FECHA DE SUBIDA</th>
            <th>ACCIONES</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $query = 'SELECT a_id, nombre_archivo, archivo, permisos, fecha_subida FROM archivos WHERE a_estado = 1 AND (permisos = 1 OR permisos = 4)';
        $result = mysqli_query($db, $query) or die(mysqli_error($db));

        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . $row['nombre_archivo'] . '</td>';
            echo '<td>';
            switch ($row['permisos']) {
                case 1:
                    echo 'Todos';
                    break;
                case 2:
                    echo 'Administración';
                    break;
                case 3:
                    echo 'Administración y personal';
                    break;
                case 4:
                    echo 'Administración y docente';
                    break;
            }
            echo '</td>';
            echo '<td>' . date('d-m-Y', strtotime($row['fecha_subida'])) . '</td>';
            echo '<td align="right">
                    <div class="btn-group">
                        <a type="button" class="btn btn-info bg-gradient-info btn-sm" href="docentes_vista_documentacion_descargar.php?file=' . urlencode($row['archivo']) . '"><i class="fas fa-fw fa-download"></i> Descargar</a>
                    </div>
                  </td>';
            echo '</tr>';
        }
        ?>
    </tbody>
</table>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>