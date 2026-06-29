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

if (isset($_GET['file_id']) && is_numeric($_GET['file_id'])) {
    $fileId = $_GET['file_id'];

    // Obtener información del archivo desde la base de datos
    $query = "SELECT nombre_archivo FROM archivos WHERE id = $fileId";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $nombreArchivo = $row['nombre_archivo'];

        // Ruta completa al archivo
        $rutaCompleta = 'uploads/' . $nombreArchivo;

        // Verificar si el archivo existe
        if (file_exists($rutaCompleta)) {
            // Configurar las cabeceras para la descarga
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($rutaCompleta) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($rutaCompleta));
            readfile($rutaCompleta);
            exit;
        } else {
            echo 'El archivo no existe.';
        }
    } else {
        echo 'Archivo no encontrado en la base de datos.';
    }
} else {
    echo 'ID de archivo no válido.';
}

// Cierra la conexión
mysqli_close($con);
?>
