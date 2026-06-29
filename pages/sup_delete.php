<?php
// Conexión a la base de datos (asegúrate de tener las credenciales correctas)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "u445672402_escuela";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

// Obtener el SUPPLIER_ID de la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $supplier_id = $_GET['id'];

    // Preparar la consulta SQL para eliminar el registro
    $sql = "DELETE FROM supplier WHERE SUPPLIER_ID = $supplier_id";

    // Ejecutar la consulta
    if ($conn->query($sql) === TRUE) {
        // Generar script JavaScript para mostrar una alerta
        echo '<script>alert("Registro eliminado correctamente."); window.location.href = "supplier.php";</script>';
    } else {
        // Generar script JavaScript para mostrar una alerta de error
        echo '<script>alert("Error al eliminar el registro: ' . $conn->error . '"); window.location.href = "supplier.php";</script>';
    }
} else {
    // Generar script JavaScript para mostrar una alerta de ID no válido
    echo '<script>alert("ID de proveedor no válido."); window.location.href = "tsupplier.php";</script>';
}

// Cerrar la conexión
$conn->close();
?>
