<?php
include "conexion.php";
$id = $_GET['id'];

$usuario = $conexion->query("SELECT * FROM usuarios WHERE id=$id")->fetch();

if ($_POST) {
    $sql = "UPDATE usuarios SET nombre=?, email=? WHERE id=?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$_POST['nombre'], $_POST['email'], $id]);
    header("Location: index.php");
}
?>

<form method="POST">
    <input name="nombre" value="<?= $usuario['nombre'] ?>">
    <input name="email" value="<?= $usuario['email'] ?>">
    <button>Actualizar</button>
</form>
