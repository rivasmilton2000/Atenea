<?php
// Verificar si se ha proporcionado un nombre de archivo
if (isset($_GET['file'])) {
    $filename = $_GET['file'];
    $filepath = 'archivos_contenidos/' . $filename;

    // Verificar si el archivo existe
    if (file_exists($filepath)) {
        // Configurar los encabezados para la descarga
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        
        // Limpiar el buffer de salida
        ob_clean();
        flush();
        
        // Leer y enviar el archivo
        readfile($filepath);
        exit;
    } else {
        echo "El archivo no existe.";
    }
} else {
    echo "No se especificó ningún archivo para descargar.";
}
?>