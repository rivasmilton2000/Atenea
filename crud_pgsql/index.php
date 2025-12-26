<?php
include "conexion.php";
$usuarios = $conexion->query("SELECT * FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Usuarios</h2>
<a href="crear.php">➕ Nuevo</a>
<table border="1">
<tr>
    <th>ID</th><th>Nombre</th><th>Email</th><th>Acciones</th>
</tr>

<?php foreach ($usuarios as $u): ?>
<tr>
    <td><?= $u['id'] ?></td>
    <td><?= $u['nombre'] ?></td>
    <td><?= $u['email'] ?></td>
    <td>
        <a href="editar.php?id=<?= $u['id'] ?>">✏️</a>
        <a href="eliminar.php?id=<?= $u['id'] ?>">🗑️</a>
    </td>
</tr>
<?php endforeach; ?>
</table>
