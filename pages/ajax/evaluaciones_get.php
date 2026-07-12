<?php
include '../../includes/connection.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $evaluacion_id = $_GET['id'];

    $query = "SELECT evaluacion_id, titulo, descripcion, fecha, porcentaje 
              FROM evaluaciones 
              WHERE evaluacion_id = $evaluacion_id";
    $result = mysqli_query($db, $query) or die(mysqli_error($db));

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode($row);
    } else {
        echo json_encode(array('error' => 'Evaluación no encontrada'));
    }
} else {
    echo json_encode(array('error' => 'ID de evaluación no proporcionado'));
}
?>