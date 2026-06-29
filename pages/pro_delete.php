<?php
// Verifica si se proporciona un ID válido en la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    // Conecta a la base de datos (debes reemplazar los valores con los tuyos)
    $conn = mysqli_connect('localhost', 'root', '', 'u445672402_escuela');

    // Verifica la conexión
    if (!$conn) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    mysqli_set_charset($conn, 'utf8mb4');

    // Escapa el ID para evitar inyección de SQL
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // Consulta para eliminar el producto
    $sql = "DELETE FROM product WHERE PRODUCT_ID = '$id'";

    // Ejecuta la consulta
    if (mysqli_query($conn, $sql)) {
        // Alerta de éxito en JavaScript
        echo '<script>alert("Producto eliminado con éxito."); window.location.href = "product.php";</script>';
    } else {
        // Alerta de error en JavaScript
        echo '<script>alert("Error al eliminar el producto: ' . mysqli_error($conn) . '"); window.location.href = "product.php";</script>';
    }

    // Cierra la conexión a la base de datos
    mysqli_close($conn);
} else {
    // Alerta de ID no válido en JavaScript
    echo '<script>alert("ID no válido."); window.location.href = "product.php";</script>';
}
?>
