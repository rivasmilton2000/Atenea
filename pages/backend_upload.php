<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "u445672402_escuela";

$con = mysqli_connect($servername, $username, $password, $database);

if (!$con) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($con, 'utf8mb4');

$uploadDirectory = 'uploads/';
$allowedExtensions = ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'pdf'];
$maxFileSize = 5 * 1024 * 1024;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["archivo"])) {
    $nombreArchivo = $_FILES["archivo"]["name"];
    $tipoArchivo = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
    $tamanoArchivo = $_FILES["archivo"]["size"];

    if (in_array(strtolower($tipoArchivo), $allowedExtensions) && $tamanoArchivo <= $maxFileSize) {

        $archivoUnico = uniqid('file_', true) . '.' . $tipoArchivo;
        $rutaCompleta = $uploadDirectory . $archivoUnico;

        $queryCheckExistence = "SELECT id FROM archivos WHERE nombre_archivo = '$archivoUnico'";
        $resultCheckExistence = mysqli_query($con, $queryCheckExistence);

        if (mysqli_num_rows($resultCheckExistence) > 0) {
            echo "<script>alert('Error: El archivo ya existe.'); window.location.href = document.referrer;</script>";
            exit();
        }
        
        move_uploaded_file($_FILES["archivo"]["tmp_name"], $rutaCompleta);

        $queryInsert = "INSERT INTO archivos (nombre_archivo, fecha_subida) VALUES ('$archivoUnico', NOW())";
        $resultInsert = mysqli_query($con, $queryInsert);

        if ($resultInsert) {
            echo "<script>alert('Archivo subido y datos insertados correctamente.'); window.location.href = document.referrer;</script>";
            exit();
        } else {
            echo "<script>alert('Error al insertar datos en la base de datos: " . mysqli_error($con) . "'); window.location.href = document.referrer;</script>";
            exit();
        }
    } else {
        echo "<script>alert('Archivo no permitido. Asegúrate de que sea un documento y no exceda el tamaño máximo de " . round($maxFileSize / (1024 * 1024)) . " MB.'); window.location.href = document.referrer;</script>";
        exit();
    }
}
?>
