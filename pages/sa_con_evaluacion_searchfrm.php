<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

// Verificar permisos de usuario según el tipo
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
    $id = intval($_GET['id']);

    // Consultar la base de datos para obtener los detalles del contenido
    $query = "SELECT c.contenido_id, c.titulo, c.descripcion, c.material, c.c_estado,
                     a.A_NAME as asignatura, g.G_NAME as grado, p.p_name as periodo, 
                     CONCAT(e.FIRST_NAME, ' ', e.LAST_NAME) as docente
              FROM contenidos c
              JOIN docentes_asignaturas da ON c.da_id = da.da_id
              JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
              JOIN grados g ON da.grado_id = g.G_ID
              JOIN periodo p ON da.periodo_id = p.p_id
              JOIN employee e ON da.profesor_id = e.EMPLOYEE_ID
              WHERE c.contenido_id = $id";
    $result = mysqli_query($db, $query) or die(mysqli_error($db));

    // Obtener los datos del contenido
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $contenido_id = $row['contenido_id'];
        $titulo = $row['titulo'];
        $descripcion = $row['descripcion'];
        $material = $row['material'];
        $asignatura = $row['asignatura'];
        $grado = $row['grado'];
        $periodo = $row['periodo'];
        $docente = $row['docente'];
        $estado = $row['c_estado']; // Capturar el estado c_estado
    } else {
        // Manejar el caso si no se encuentra el contenido
        echo "El contenido con ID $id no fue encontrado.";
        exit();
    }
} else {
    // Manejar el caso si no se proporciona un ID válido
    echo "ID de contenido no especificado.";
    exit();
}
?>

<!-- HTML para mostrar los detalles del contenido -->
<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Detalles del contenido de evaluación</h4>
        </div>
        <a href="sa_con_evaluacion.php" type="button" class="btn btn-primary bg-gradient-primary btn-block">
            <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
        </a>
        <div class="card-body">
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Título</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $titulo; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Descripción</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $descripcion; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Asignatura</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $asignatura; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Grado</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $grado; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Periodo</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $periodo; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Docente</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $docente; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Estado</h5>
                </div>
                <div class="col-sm-9">
                    <?php
                    if ($estado == 1) {
                        echo '<h5>: <span class="badge badge-success">Activo</span></h5>';
                    } elseif ($estado == 0) {
                        echo '<h5>: <span class="badge badge-danger">Inactivo</span></h5>';
                    } else {
                        echo '<h5>: <span class="badge badge-secondary">Desconocido</span></h5>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</center>

<?php
include '../includes/footer_superadmin.php';
?>