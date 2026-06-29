<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';

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
        case 'SuperAdmin':
            $redirectUrl = "sa_vista.php";
            break;
        default:
            $redirectUrl = "inventario.php"; // Redirigir a la página de inventario si el tipo no está definido
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

// Obtener el ID del artículo de inventario desde el parámetro GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consultar la base de datos para obtener los detalles del artículo de inventario
    $query = "SELECT i_id, articulo, cantidad FROM inventario WHERE i_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Obtener los datos del artículo de inventario
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $i_id = $row['i_id'];
        $articulo = $row['articulo'];
        $cantidad = $row['cantidad'];
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

<!-- HTML para editar los detalles del artículo de inventario -->
<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Editar artículo de inventario</h4>
        </div>
        <a href="inventario.php" type="button" class="btn btn-primary bg-gradient-primary btn-block">
            <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
        </a>
        <div class="card-body">
            <form role="form" method="post" action="inventario_edit1.php">
                <input type="hidden" name="id" value="<?php echo $i_id; ?>" />

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Artículo:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Nombre del artículo" name="articulo" value="<?php echo $articulo; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Cantidad:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" type="number" placeholder="Cantidad" name="cantidad" value="<?php echo $cantidad; ?>" required>
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
include '../includes/footer.php';
?>