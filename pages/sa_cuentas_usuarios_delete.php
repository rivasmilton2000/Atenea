<?php
include('../includes/connection.php');

// Configura la cabecera para indicar que la respuesta será JSON
header('Content-Type: application/json');

$response = array('success' => false, 'message' => '');

// Verifica si se proporciona un ID en la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = mysqli_real_escape_string($db, $_GET['id']);

    // Inicia una transacción
    mysqli_begin_transaction($db);

    try {
        // Primero, verifica si el usuario existe
        $check_query = "SELECT * FROM users WHERE ID = '$user_id'";
        $check_result = mysqli_query($db, $check_query);

        if (mysqli_num_rows($check_result) == 0) {
            throw new Exception("Usuario no encontrado.");
        }

        // Elimina el usuario de la tabla 'users'
        $delete_query = "DELETE FROM users WHERE ID = '$user_id'";
        $result = mysqli_query($db, $delete_query);

        if (!$result) {
            throw new Exception("Error al eliminar usuario: " . mysqli_error($db));
        }

        // Si todo está bien, confirma la transacción
        mysqli_commit($db);

        $response['success'] = true;
        $response['message'] = 'Usuario eliminado correctamente.';
    } catch (Exception $e) {
        // Si hay algún error, revierte la transacción
        mysqli_rollback($db);

        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'ID de usuario no válido.';
}

// Cierra la conexión a la base de datos
mysqli_close($db);

// Envía la respuesta JSON
echo json_encode($response);
?>