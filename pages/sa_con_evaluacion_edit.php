<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

// Verificar el tipo de usuario y redirigir según sea necesario
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'];
$result = mysqli_query($db, $query) or die(mysqli_error($db));

while ($row = mysqli_fetch_assoc($result)) {
    $userType = $row['TYPE'];
    
    // Redirigir según el tipo de usuario
    switch ($userType) {
        case 'Personal':
            $redirectUrl = "empleados_vista.php";
            break;
        case 'Estudiante':
            $redirectUrl = "estudiante_vista.php";
            break;
        case 'Docente':
            $redirectUrl = "docentes_vista.php";
            break;
        case 'Admin':
            $redirectUrl = "index.php";
            break;
        default:
            $redirectUrl = "sa_con_evaluacion.php"; // Redirigir a la página de inventario si el tipo no está definido
            break;
    }

    // Redireccionar y salir del script si el tipo de usuario es válido
    if (in_array($userType, ['Personal', 'Estudiante', 'Docente', 'Admin'])) {
        ?>
        <script type="text/javascript">
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit(); // Terminar la ejecución del script después de la redirección
    }
}

// Obtener el ID del contenido desde el parámetro GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Modificar la consulta para incluir descripcion y material
    $query = "SELECT c.contenido_id, c.titulo, c.descripcion, c.material, c.c_estado, c.da_id, a.A_NAME, g.G_NAME, p.p_name, e.FIRST_NAME, e.LAST_NAME
              FROM contenidos c
              JOIN docentes_asignaturas da ON c.da_id = da.da_id
              JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
              JOIN grados g ON da.grado_id = g.G_ID
              JOIN periodo p ON da.periodo_id = p.p_id
              JOIN employee e ON da.profesor_id = e.EMPLOYEE_ID
              WHERE c.contenido_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Obtener los datos del contenido
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $contenido_id = $row['contenido_id'];
        $titulo = $row['titulo'];
        $descripcion = $row['descripcion'];
        $material = $row['material'];
        $da_id = $row['da_id'];
        $asignatura = $row['A_NAME'];
        $grado = $row['G_NAME'];
        $periodo = $row['p_name'];
        $docente = $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'];
        $estado = $row['c_estado']; // Capturar el estado c_estado
    } else {
        echo "El contenido con ID $id no fue encontrado.";
        exit();
    }
}
?>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Editar contenido</h4>
        </div>
        <a href="sa_con_evaluacion.php" type="button" class="btn btn-primary bg-gradient-primary btn-block">
            <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
        </a>
        <div class="card-body">
            <form role="form" method="post" action="sa_con_evaluacion_edit1.php" enctype="multipart/form-data">
                <input type="hidden" name="contenido_id" value="<?php echo $contenido_id; ?>" />

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Título del contenido:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Título del contenido" minlength="5" maxlength="80" name="titulo" value="<?php echo $titulo; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Descripción:
                    </div>
                    <div class="col-sm-9">
                        <textarea class="form-control" name="descripcion" rows="3" minlength= "15" maxlength="250" required><?php echo $descripcion; ?></textarea>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Material actual:
                    </div>
                    <div class="col-sm-9">
                        <p><?php echo $material; ?></p>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Nuevo material:
                    </div>
                    <div class="col-sm-9">
                        <input type="file" class="form-control-file" name="material">
                        <small class="form-text text-muted">Deje en blanco si no desea cambiar el material.</small>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Asignatura de docente:
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control" id="da_id" name="da_id" required onchange="actualizarDocente()">
                            <?php
                            $query = "SELECT da.da_id, a.A_NAME, g.G_NAME, p.p_name, e.FIRST_NAME, e.LAST_NAME
                                      FROM docentes_asignaturas da
                                      JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
                                      JOIN grados g ON da.grado_id = g.G_ID
                                      JOIN periodo p ON da.periodo_id = p.p_id
                                      JOIN employee e ON da.profesor_id = e.EMPLOYEE_ID
                                      WHERE da.da_estado = 1
                                      ORDER BY a.A_NAME, g.G_NAME, p.p_name";
                            $result = mysqli_query($db, $query) or die(mysqli_error($db));
                            while ($row = mysqli_fetch_assoc($result)) {
                                $selected = ($row['da_id'] == $da_id) ? 'selected' : '';
                                echo "<option value='".$row['da_id']."' data-docente='".$row['FIRST_NAME']." ".$row['LAST_NAME']."' $selected>"
                                    .$row['A_NAME']." - ".$row['G_NAME']." - ".$row['p_name']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Docente encargado:
                    </div>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="docente" name="docente" value="<?php echo $docente; ?>" readonly>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Estado:
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control" name="c_estado" required>
                            <option value="1" <?php if ($estado == 1) echo 'selected'; ?>>Activo</option>
                            <option value="0" <?php if ($estado == 0) echo 'selected'; ?>>Inactivo</option>
                        </select>
                    </div>
                </div>

                <hr>

                <button type="submit" class="btn btn-warning btn-block">
                    <i class="fa fa-edit fa-fw"></i> Actualizar
                </button>    
            </form>  
        </div>
    </div>
</center>

<script>
function actualizarDocente() {
    var select = document.getElementById('da_id');
    var docenteInput = document.getElementById('docente');
    var selectedOption = select.options[select.selectedIndex];
    docenteInput.value = selectedOption.getAttribute('data-docente');
}
</script>

<?php
include '../includes/footer_superadmin.php'
?>