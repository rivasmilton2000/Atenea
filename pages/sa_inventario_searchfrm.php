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
            $redirectUrl = "sa_inventario.php"; // Redirigir a la página de inventario si el tipo no está definido
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

// Obtener el ID del artículo de inventario desde el parámetro GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consultar la base de datos para obtener los detalles del artículo de inventario
    $query = "SELECT i_id, articulo, cantidad, i_estado FROM inventario WHERE i_id = $id";
    $result = mysqli_query($db, $query) or die(mysqli_error($db));

    // Obtener los datos del artículo de inventario
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $i_id = $row['i_id'];
        $articulo = $row['articulo'];
        $cantidad = $row['cantidad'];
        $estado = $row['i_estado'];
    } else {
        // Manejar el caso si no se encuentra el artículo de inventario
        echo "El artículo de inventario con ID $id no fue encontrado.";
        exit(); // Salir del script si no se encuentra el artículo de inventario
    }
} else {
    // Manejar el caso si no se proporciona un ID válido
    echo "ID de artículo de inventario no especificado.";
    exit(); // Salir del script si no se especifica un ID válido
}
?>

<!-- HTML para mostrar los detalles del artículo de inventario -->
<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Detalles del artículo</h4>
        </div>
        <a href="sa_inventario.php" type="button" class="btn btn-primary bg-gradient-primary btn-block">
            <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
        </a>
        <div class="card-body">
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Artículo</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $articulo; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Cantidad</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $cantidad; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Estado</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: 
                    <?php
                    if ($estado == 1) {
                        echo '<span class="badge badge-success">Activo</span>';
                    } elseif ($estado == 0) {
                        echo '<span class="badge badge-danger">Inactivo</span>';
                    } else {
                        echo 'Estado desconocido';
                    }
                    ?>
                    </h5>
                </div>
            </div>
        </div>
    </div>
</center>

<?php
include '../includes/footer_superadmin.php';
?>
