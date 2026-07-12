<?php
include '../includes/connection.php';

if (isset($_GET['id'])) {
    $ev_entregada_id = intval($_GET['id']);

    // Obtener la información del archivo
    $query = "SELECT material FROM ev_entregadas WHERE ev_entregada_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $ev_entregada_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $fileName = $row['material'];
        $filePath = 'archivos_evaluaciones/' . $fileName;

        if (file_exists($filePath)) {
            // Configurar las cabeceras para la descarga
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            echo "El archivo no existe.";
        }
    } else {
        echo "Evaluación no encontrada.";
    }
} else {
    echo "ID de evaluación no proporcionado.";
}
?>