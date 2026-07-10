<?php
include '../includes/connection.php';
include '../includes/sidebar.php';

$servername = "localhost"; // Puede variar según tu configuración local
$username = "root"; // Nombre de usuario de la base de datos
$password = ""; // Contraseña de la base de datos
$database = "u445672402_escuela"; // Nombre de la base de datos

// Crea la conexión
$con = mysqli_connect($servername, $username, $password, $database);

// Verifica la conexión
if (!$con) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($con, 'utf8mb4');

// Manejo de la inserción de archivos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["archivo"])) {
    $nombreArchivo = $_FILES["archivo"]["name"];
}

// Manejo de la eliminación de archivos
if (isset($_GET["eliminar"]) && is_numeric($_GET["eliminar"])) {
    $archivoId = $_GET["eliminar"];
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <title>Tu Título</title>
</head>
<body>



<div class="container mt-5">
    <form action="backend_upload.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="archivo">Selecciona un archivo:</label>
            <input type="file" class="form-control-file" name="archivo" id="archivo" required>
        </div>
        <button type="submit" class="btn btn-primary">Subir Archivo</button>
    </form>

    <table class="table mt-4">
        <thead class="thead-dark">
            <tr>
                <th>Fecha de Subida</th>
                <th>Nombre de Archivo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($con) {
                $query = "SELECT id, nombre_archivo, fecha_subida FROM archivos";
                $result = mysqli_query($con, $query);

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>{$row['fecha_subida']}</td>";
                    echo "<td>{$row['nombre_archivo']}</td>";
                    echo "<td>
                            <a href=\"backend_download.php?file_id={$row['id']}\" target='_blank' class='btn btn-success'>Descargar</a>
                            <a href=\"backend_delete.php?id={$row['id']}\" class='btn btn-danger'>Eliminar</a>
                          </td>";
                    echo "</tr>";
                }

                mysqli_free_result($result);
            } else {
                echo "<tr><td colspan='3'>Error de conexión a la base de datos</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Agrega las bibliotecas de Bootstrap y otros scripts al final del body -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

</body>
</html>

