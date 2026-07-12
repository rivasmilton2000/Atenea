<?php
include '../includes/connection.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $evaluacionId = $_GET['id'];

    // Realizar la consulta de actualización
    $query = "UPDATE evaluaciones SET evaluacion_estado = 0 WHERE evaluacion_id = '$evaluacionId'";
    $result = mysqli_query($db, $query);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($db)]);
    }

    mysqli_close($db);
} else {
    echo json_encode(['success' => false, 'error' => 'No se proporcionó un ID válido']);
}
?>