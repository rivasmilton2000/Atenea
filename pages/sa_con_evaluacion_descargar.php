<?php
include '../includes/connection.php';

if (isset($_GET['file'])) {
    $file = urldecode($_GET['file']); // Decodificar el nombre del archivo
    $filepath = 'archivos_contenidos/' . $file; // Ruta completa del archivo

    if (file_exists($filepath)) {
        // Establecer encabezados para la descarga del archivo
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        flush(); // Limpiar el búfer del sistema
        readfile($filepath); // Leer el archivo
        exit();
    } else {
        echo "El archivo no existe.";
    }
} else {
    echo "No se ha proporcionado ningún archivo para descargar.";
}
?>