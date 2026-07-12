<?php
include '../includes/connection.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $contenidoId = $_GET['id'];

    // Realizar la consulta de actualización
    $query = "UPDATE contenidos SET c_estado = 0 WHERE contenido_id = '$contenidoId'";
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