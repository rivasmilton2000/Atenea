<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';

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
        case 'SuperAdmin':
            $redirectUrl = "sa_vista.php";
            break;
        default:
            $redirectUrl = "documentacion.php"; // Redirigir a la página de inventario si el tipo no está definido
            break;
    }

    // Redireccionar y salir del script si el tipo de usuario es válido
    if (in_array($userType, ['Personal', 'Estudiante', 'Docente', 'SuperAdmin'])) {
        ?>
        <script type="text/javascript">
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit(); // Terminar la ejecución del script después de la redirección
    }
}

// Obtener el ID del archivo desde el parámetro GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consultar la base de datos para obtener los detalles del archivo
    $query = "SELECT a_id, nombre_archivo, permisos, fecha_subida FROM archivos WHERE a_id = $id AND a_estado = 1";
    $result = mysqli_query($db, $query) or die(mysqli_error($db));

    // Obtener los datos del archivo
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $a_id = $row['a_id'];
        $nombre_archivo = $row['nombre_archivo'];
        $permisos = $row['permisos'];
        $fecha_subida = $row['fecha_subida'];
    } else {
        // Manejar el caso si no se encuentra el archivo
        echo "El archivo con ID $id no fue encontrado.";
        exit(); // Salir del script si no se encuentra el archivo
    }
} else {
    // Manejar el caso si no se proporciona un ID válido
    echo "ID de archivo no especificado.";
    exit(); // Salir del script si no se especifica un ID válido
}

// Función para convertir el valor de permisos en texto
function obtenerPermisosTexto($permisos) {
    switch ($permisos) {
        case 1:
            return 'Todos';
        case 2:
            return 'Administración';
        case 3:
            return 'Administración y personal';
        case 4:
            return 'Administración y docente';
        default:
            return 'Desconocido';
    }
}

$permisosTexto = obtenerPermisosTexto($permisos);
$fechaFormateada = date('d-m-Y', strtotime($fecha_subida));
?>

<!-- HTML para mostrar los detalles del archivo -->
<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Detalles del documento</h4>
        </div>
        <a href="documentacion.php" type="button" class="btn btn-primary bg-gradient-primary btn-block">
            <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
        </a>
        <div class="card-body">
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Nombre del documento</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $nombre_archivo; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Permisos</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $permisosTexto; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Fecha de subida</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $fechaFormateada; ?></h5>
                </div>
            </div>
        </div>
    </div>
</center>

<?php
include '../includes/footer.php';
?>