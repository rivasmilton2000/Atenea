<?php
include '../../includes/connection.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $contenidoId = $_GET['id'];

    $query = "SELECT contenido_id, titulo, descripcion FROM contenidos WHERE contenido_id = $contenidoId";
    $result = mysqli_query($db, $query) or die(mysqli_error($db));

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode($row);
    } else {
        echo json_encode(array('error' => 'Contenido no encontrado'));
    }
} else {
    echo json_encode(array('error' => 'ID de contenido no proporcionado'));
}
?>