<?php 
include '../includes/connection.php';
include '../includes/sidebar_admin.php'; 

// Verificar permisos (código estándar)
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa=='Personal' || $Aa=='Estudiante' || $Aa=='Docente' || $Aa=='SuperAdmin'){
        $redirectUrl = ($Aa=='Personal') ? "empleados_vista.php" : (($Aa=='Estudiante') ? "estudiante_vista.php" : (($Aa=='Docente') ? "docentes_vista.php" : "sa_vista.php"));
        echo "<script>alert('Página restringida! Será redirigido.'); window.location = '$redirectUrl';</script>";
        exit();
    }
}

// Procesar acciones
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $nombre = mysqli_real_escape_string($db, $_POST['nombre']);
        $descripcion = mysqli_real_escape_string($db, $_POST['descripcion']);
        $estado = mysqli_real_escape_string($db, $_POST['estado']);
        
        $sql = "INSERT INTO categorias_productos (nombre, descripcion, estado) VALUES ('$nombre', '$descripcion', '$estado')";
        if (mysqli_query($db, $sql)) {
            echo "<script>alert('Categoría agregada exitosamente!');</script>";
        }
    } elseif ($_POST['action'] == 'edit') {
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $nombre = mysqli_real_escape_string($db, $_POST['nombre']);
        $descripcion = mysqli_real_escape_string($db, $_POST['descripcion']);
        $estado = mysqli_real_escape_string($db, $_POST['estado']);
        
        $sql = "UPDATE categorias_productos SET nombre='$nombre', descripcion='$descripcion', estado='$estado' WHERE id='$id'";
        if (mysqli_query($db, $sql)) {
            echo "<script>alert('Categoría actualizada exitosamente!');</script>";
        }
    }
}

$query = 'SELECT * FROM categorias_productos ORDER BY nombre';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Categorías de Productos
            <a href="#" data-toggle="modal" data-target="#addCatModal" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;">
                <i class="fas fa-fw fa-plus"></i>
            </a>
            <a href="productos_admin.php" class="btn btn-secondary bg-gradient-secondary float-right" style="border-radius: 0px;">
                <i class="fas fa-fw fa-arrow-left"></i> Volver
            </a>
        </h4>
    </div>

    <div class="card-body">
        <table class="table table-bordered" width="100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>NOMBRE</th>
                    <th>DESCRIPCIÓN</th>
                    <th>ESTADO</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) : 
                    $estadoClass = $row['estado'] == 1 ? 'badge-success' : 'badge-danger';
                    $estadoText = $row['estado'] == 1 ? 'Activo' : 'Inactivo';
                ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['nombre']; ?></td>
                        <td><?php echo $row['descripcion']; ?></td>
                        <td><span class="badge <?php echo $estadoClass; ?>"><?php echo $estadoText; ?></span></td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="editCategoria(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nombre']); ?>', '<?php echo addslashes($row['descripcion']); ?>', <?php echo $row['estado']; ?>)">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Agregar -->
<div class="modal fade" id="addCatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Categoría</h5>
                <button class="close" data-dismiss="modal"><span>×</span></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input class="form-control" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select class="form-control" name="estado">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Guardar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editCatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Categoría</h5>
                <button class="close" data-dismiss="modal"><span>×</span></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input class="form-control" name="nombre" id="edit_nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" name="descripcion" id="edit_descripcion" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select class="form-control" name="estado" id="edit_estado">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Actualizar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategoria(id, nombre, descripcion, estado) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_descripcion').value = descripcion;
    document.getElementById('edit_estado').value = estado;
    $('#editCatModal').modal('show');
}
</script>

<?php include '../includes/footer.php'; ?>