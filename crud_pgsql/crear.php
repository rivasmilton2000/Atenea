<?php
include "conexion.php";

if ($_POST) {
    $sql = "INSERT INTO usuarios (nombre, email) VALUES (?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$_POST['nombre'], $_POST['email']]);
    header("Location: index.php");
}
?>

<form method="POST">
    <input name="nombre" placeholder="Nombre" required>
    <input name="email" placeholder="Email" required>
    <button>Guardar</button>
</form>
