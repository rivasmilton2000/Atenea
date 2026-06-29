<?php
include '../../includes/connection.php';

header('Content-Type: application/json');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['ev_entregada_id']) && !empty($_POST['ev_entregada_id']) && isset($_POST['valor_nota'])) {
        $entregaId = $_POST['ev_entregada_id'];
        $valorNota = $_POST['valor_nota'];

        // Validar la nota
        if (is_numeric($valorNota) && $valorNota >= 0 && $valorNota <= 10) {
            // Actualizar la nota en la tabla "notas"
            $query = "UPDATE notas SET valor_nota = '$valorNota', fecha = NOW() WHERE id_ev_entregada = '$entregaId'";
            if (mysqli_query($db, $query)) {
                $response['success'] = true;
                $response['message'] = 'La nota ha sido actualizada correctamente.';
            } else {
                $response['success'] = false;
                $response['message'] = 'Ocurrió un error al actualizar la nota.';
            }
        } else {
            $response['success'] = false;
            $response['message'] = 'La nota debe estar en el rango de 0 a 10.';
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Datos de entrega o nota no proporcionados.';
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Método de solicitud no válido.';
}

echo json_encode($response);
?>
