<?php
include '../includes/connection.php';
include '../includes/sidebar_docente.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
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
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
<?php
        exit();
    }
}

// Obtener el ID de la asignatura seleccionada
$da_id = $_GET['id'];
?>
            
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Estudiantes asignados</h4>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"> 
                <thead>
                    <tr>
                        <th>NOMBRE</th>
                        <th>CORREO</th>
                        <th>CARNET</th>
                        <th>GRADO</th>
                        <th>NÚMERO DE LISTA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT CONCAT(e.apellidos_estudiante, ', ', e.nombres_estudiante) AS nombre_completo, e.correo_estudiante, e.carnet_estudiante, e.numero_lista_estudiante, g.G_NAME, e.foto_estudiante 
                              FROM estudiantes_docentes ed
                              JOIN estudiantes e ON ed.estudiante_id = e.ESTUDIANTE_ID
                              JOIN grados g ON e.grado_id_estudiante = g.G_ID
                              WHERE ed.doc_asi_id = $da_id AND ed.ed_estado = 1";
                    $result = mysqli_query($db, $query) or die(mysqli_error($db));

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . $row['nombre_completo'] . '</td>';
                        echo '<td>' . $row['correo_estudiante'] . '</td>';
                        echo '<td>' . $row['carnet_estudiante'] . '</td>';
                        echo '<td>' . $row['G_NAME'] . '</td>';
                        echo '<td>' . $row['numero_lista_estudiante'] . '</td>';
                        
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