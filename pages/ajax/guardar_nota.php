<?php
include '../../includes/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['ev_entregada_id']) && !empty($_POST['ev_entregada_id']) && isset($_POST['valor_nota'])) {
        $entregaId = $_POST['ev_entregada_id'];
        $valorNota = $_POST['valor_nota'];

        // Establecer la zona horaria de El Salvador
        date_default_timezone_set('America/El_Salvador');

        // Obtener la fecha y hora actual de El Salvador
        $fechaHora = date('Y-m-d H:i:s');

        // Insertar o actualizar la nota en la tabla "notas"
        $query = "INSERT INTO notas (id_ev_entregada, valor_nota, fecha) VALUES ('$entregaId', '$valorNota', '$fechaHora') ON DUPLICATE KEY UPDATE valor_nota = '$valorNota', fecha = '$fechaHora'";
        mysqli_query($db, $query) or die(mysqli_error($db));

        echo "success";
    } else {
        echo json_encode(array('error' => 'Datos de entrega o nota no proporcionados'));
    }
}
?>