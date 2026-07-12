<?php
include '../includes/connection.php';

if (isset($_GET['id'])) {
    $ev_entregada_id = $_GET['id'];
    
    // Obtener la información del archivo
    $query = "SELECT material FROM ev_entregadas WHERE ev_entregada_id = $ev_entregada_id";
    $result = mysqli_query($db, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $filename = $row['material'];
        $filepath = 'archivos_evaluaciones/' . $filename;
        
        if (file_exists($filepath)) {
            // Forzar la descarga del archivo
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            flush(); // Flush system output buffer
            readfile($filepath);
            exit;
        } else {
            echo "El archivo no existe.";
        }
    } else {
        echo "No se encontró la información del archivo.";
    }
} else {
    echo "ID de entrega no proporcionado.";
}
?>