<?php
include '../includes/connection.php';

header('Content-Type: application/json');

$response = array('success' => false, 'message' => '');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $jobsId = intval($_GET['id']);

    // Iniciar transacción
    mysqli_begin_transaction($db);

    try {
        // Preparar la consulta de eliminación
        $query = "DELETE FROM jobs WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $jobsId);
            
            // Ejecutar la consulta
            if (mysqli_stmt_execute($stmt)) {
                // Confirmar la transacción
                mysqli_commit($db);
                
                // Cerrar la declaración
                mysqli_stmt_close($stmt);
                
                $response['success'] = true;
            } else {
                throw new Exception("Error al ejecutar la consulta");
            }
        } else {
            throw new Exception("Error al preparar la consulta");
        }
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        mysqli_rollback($db);
        
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'ID inválido';
}

mysqli_close($db);
echo json_encode($response);
?>