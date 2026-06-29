<?php
// Reemplaza los siguientes valores con tu configuración local
$servername = "localhost";
$username = "root";
$password = "";
$database = "u445672402_escuela";

// Crea la conexión
$con = mysqli_connect($servername, $username, $password, $database);

// Verifica la conexión
if (!$con) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($con, 'utf8mb4');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $archivoId = $_GET['id'];

    // Realizar la lógica para eliminar el archivo y su entrada en la base de datos
    $query = "DELETE FROM archivos WHERE id = $archivoId";
    $result = mysqli_query($con, $query);

    if ($result) {
        // Eliminación exitosa
        echo "<script>alert('Archivo eliminado correctamente.'); window.location.href = document.referrer;</script>";
        exit();
    } else {
        // Error en la eliminación
        echo "<script>alert('Error al eliminar el archivo: " . mysqli_error($con) . "'); window.location.href = document.referrer;</script>";
        exit();
    }
} else {
    // ID de archivo no válido
    echo "<script>alert('ID de archivo no válido.'); window.location.href = document.referrer;</script>";
    exit();
}

// Cierra la conexión
mysqli_close($con);
?>
