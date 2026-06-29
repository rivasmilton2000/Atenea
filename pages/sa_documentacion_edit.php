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
            $redirectUrl = "sa_documentacion.php"; // Redirigir a la página de inventario si el tipo no está definido
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

// Obtener el ID del archivo desde el parámetro GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consultar la base de datos para obtener los detalles del archivo
    $query = "SELECT a_id, nombre_archivo, permisos, fecha_subida, a_estado FROM archivos WHERE a_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Obtener los datos del archivo
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $a_id = $row['a_id'];
        $nombre_archivo = $row['nombre_archivo'];
        $permisos = $row['permisos'];
        $fecha_subida = $row['fecha_subida'];
        $estado = $row['a_estado'];
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
?>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Editar archivo</h4>
        </div>
        <a href="sa_documentacion.php" type="button" class="btn btn-primary bg-gradient-primary btn-block">
            <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
        </a>
        <div class="card-body">
            <form role="form" method="post" action="sa_documentacion_edit1.php" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $a_id; ?>" />

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Nombre del archivo:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Nombre del archivo" minlength="5"  maxlength="75" name="nombre_archivo" value="<?php echo $nombre_archivo; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Permisos:
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control" name="permisos" required>
                            <option value="1" <?php if ($permisos == 1) echo 'selected'; ?>>Todos</option>
                            <option value="2" <?php if ($permisos == 2) echo 'selected'; ?>>Administración</option>
                            <option value="3" <?php if ($permisos == 3) echo 'selected'; ?>>Administración y personal</option>
                            <option value="4" <?php if ($permisos == 4) echo 'selected'; ?>>Administración y docente</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Estado:
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control" name="a_estado" required>
                            <option value="1" <?php if ($estado == 1) echo 'selected'; ?>>Activo</option>
                            <option value="0" <?php if ($estado == 0) echo 'selected'; ?>>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Archivo nuevo:
                    </div>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" name="archivo">
                        <small class="form-text text-muted">
                            Seleccione un archivo nuevo solo si desea actualizarlo. De lo contrario, deje este campo en blanco.
                        </small>
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

<?php
include '../includes/footer_superadmin.php';
?>