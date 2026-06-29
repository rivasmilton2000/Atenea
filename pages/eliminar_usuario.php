<?php
// Verifica si se proporciona un ID en la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = $_GET['id'];

    // Conecta a la base de datos (asegúrate de tener las credenciales correctas)
    $db = mysqli_connect('localhost', 'root', '', 'u445672402_escuela');

    if (!$db) {
        die("Error al conectar a la base de datos: " . mysqli_connect_error());
    }

    mysqli_set_charset($db, 'utf8mb4');

    // Elimina el usuario de la tabla 'users'
    $delete_query = "DELETE FROM users WHERE ID = $user_id";
    $result = mysqli_query($db, $delete_query);

    // Verifica si la eliminación fue exitosa
    if ($result) {
        echo "<script>alert('Usuario eliminado correctamente.');</script>";
    } else {
        echo "<script>alert('Error al eliminar usuario: " . mysqli_error($db) . "');</script>";
    }

    // Cierra la conexión a la base de datos
    mysqli_close($db);
} else {
    echo "<script>alert('ID de usuario no válido.');</script>";
}
?>
