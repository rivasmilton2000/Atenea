<?php
session_start();
include '../includes/connection.php';
header('Content-Type: application/json');

$response = [];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $ev_entregada_id = mysqli_real_escape_string($db, $_GET['id']);

    // Verificar si el registro de evaluación entregada existe y obtener el nombre del archivo
    $checkQuery = "SELECT material FROM ev_entregadas WHERE ev_entregada_id = ?";
    $stmt = mysqli_prepare($db, $checkQuery);
    mysqli_stmt_bind_param($stmt, "i", $ev_entregada_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $archivo = $row['material'];
        $filePath = 'archivos_evaluaciones/' . $archivo;

        // Iniciar transacción
        mysqli_begin_transaction($db);

        try {
            // Eliminar el registro de la base de datos
            $deleteQuery = "DELETE FROM ev_entregadas WHERE ev_entregada_id = ?";
            $stmt = mysqli_prepare($db, $deleteQuery);
            mysqli_stmt_bind_param($stmt, "i", $ev_entregada_id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Si el registro se eliminó correctamente, intentar eliminar el archivo
                if (!empty($archivo) && file_exists($filePath)) {
                    if (unlink($filePath)) {
                        $response['status'] = 'success';
                        $response['message'] = "Registro de evaluación entregada y archivo eliminados exitosamente.";
                    } else {
                        // Si no se pudo eliminar el archivo, lanzar una excepción
                        throw new Exception("Error al eliminar el archivo físico.");
                    }
                } else {
                    $response['status'] = 'success';
                    $response['message'] = "Registro de evaluación entregada eliminado exitosamente. No se encontró archivo asociado.";
                }
                
                // Confirmar la transacción
                mysqli_commit($db);
            } else {
                throw new Exception("Error al eliminar el registro de la base de datos.");
            }
        } catch (Exception $e) {
            // Si ocurre un error, revertir la transacción
            mysqli_rollback($db);
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = "El registro de evaluación entregada no existe.";
    }
} else {
    $response['status'] = 'error';
    $response['message'] = "ID de evaluación entregada no proporcionado.";
}

echo json_encode($response);
mysqli_close($db);
?>