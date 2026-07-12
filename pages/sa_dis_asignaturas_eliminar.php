<?php
include '../includes/connection.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $disasignaturaId = intval($_GET['id']);

    // Iniciar transacción
    mysqli_begin_transaction($db);

    try {
        // Preparar la consulta de eliminación
        $query = "DELETE FROM estudiantes_docentes WHERE ed_id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $disasignaturaId);

        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            // Verificar si se eliminó algún registro
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                // Commit la transacción
                mysqli_commit($db);
                $response = [
                    "status" => "success",
                    "message" => "La asignación se eliminó correctamente."
                ];
            } else {
                // No se encontró el registro para eliminar
                throw new Exception("No se encontró la asignación para eliminar.");
            }
        } else {
            // Error al ejecutar la consulta
            throw new Exception("Error al eliminar la asignación.");
        }

        mysqli_stmt_close($stmt);
    } catch (Exception $e) {
        // Rollback la transacción en caso de error
        mysqli_rollback($db);
        $response = [
            "status" => "error",
            "message" => $e->getMessage()
        ];
    }

    // Cerrar la conexión
    mysqli_close($db);

    // Devolver respuesta en JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
} else {
    // Si no se proporciona un ID válido, devolver error en JSON
    $response = [
        "status" => "error",
        "message" => "ID de asignación no válido."
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>
