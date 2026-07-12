<?php 
include '../includes/connection.php';
include '../includes/sidebar_admin.php'; 

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa=='Personal' || $Aa=='Estudiante' || $Aa=='Docente'){
        if ($Aa=='Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa=='Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa=='Docente') {
            $redirectUrl = "docentes_vista.php";
        }
        ?>
        <script type="text/javascript">
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit();
    }
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Servicios&nbsp;
            <a href="#" data-toggle="modal" data-target="#addServiceModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;">
                <i class="fas fa-fw fa-plus"></i>
            </a>
        </h4>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"> 
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ORDEN</th>
                        <th>TÍTULO</th>
                        <th>DESCRIPCIÓN</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = 'SELECT * FROM facilities ORDER BY orden ASC';
                    $result = mysqli_query($db, $query) or die(mysqli_error($db));

                    while ($row = mysqli_fetch_assoc($result)) {
                        $estadoClass = $row['estado'] == 1 ? 'badge-success' : 'badge-danger';
                        $estadoText = $row['estado'] == 1 ? 'Activo' : 'Inactivo';
                        
                        echo '<tr>';
                        echo '<td>' . $row['id'] . '</td>';
                        echo '<td>' . $row['orden'] . '</td>';
                        echo '<td>' . $row['titulo'] . '</td>';
                        echo '<td>' . substr($row['descripcion'], 0, 50) . '...</td>';
                        echo '<td><span class="badge ' . $estadoClass . '">' . $estadoText . '</span></td>';
                        echo '<td align="right">
                                <div class="btn-group">
                                    <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="#" data-toggle="modal" data-target="#editServiceModal" 
                                       onclick="editService(' . $row['id'] . ', \'' . addslashes($row['titulo']) . '\', \'' . addslashes($row['descripcion']) . '\', ' . $row['orden'] . ', ' . $row['estado'] . ')">
                                        <i class="fas fa-fw fa-edit"></i> Editar
                                    </a>
                                </div>
                                <div class="btn-group">
                                    <a type="button" class="btn btn-danger bg-gradient-danger btn-sm" href="#" onclick="confirmDelete(' . $row['id'] . ')">
                                        <i class="fas fa-fw fa-trash"></i> Eliminar
                                    </a>
                                </div>
                            </td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .custom-popup-class, .custom-title-class, .custom-confirm-button-class, .custom-cancel-button-class {
        font-family: 'Open Sans', sans-serif;
    }
    .custom-title-class {
        font-weight: 700;
    }
    .custom-confirm-button-class, .custom-cancel-button-class {
        font-weight: 600;
    }
</style>

<script>
function confirmDelete(serviceId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Deseas eliminar este servicio?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'custom-popup-class',
            title: 'custom-title-class',
            confirmButton: 'custom-confirm-button-class',
            cancelButton: 'custom-cancel-button-class'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('servicios_delete.php?id=' + serviceId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Eliminado',
                            text: 'El servicio ha sido eliminado.',
                            icon: 'success',
                            customClass: { 
                                popup: 'custom-popup-class',
                                title: 'custom-title-class',
                                confirmButton: 'custom-confirm-button-class'
                            }
                        }).then(() => {
                            window.location.href = 'facilities.php';
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Hubo un problema al eliminar el registro.',
                            icon: 'error',
                            customClass: {
                                popup: 'custom-popup-class',
                                title: 'custom-title-class',
                                confirmButton: 'custom-confirm-button-class'
                            }
                        });
                    }
                });
        }
    });
}

function editService(id, titulo, descripcion, orden, estado) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_titulo').value = titulo;
    document.getElementById('edit_descripcion').value = descripcion;
    document.getElementById('edit_orden').value = orden;
    document.getElementById('edit_estado').value = estado;
}
</script>

<!-- Modal Agregar Servicio -->
<div class="modal fade" id="addServiceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Agregar Servicio</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="servicios_transac.php?action=add">
                    <div class="form-group">
                        <label>Título</label>
                        <input class="form-control" placeholder="Título del servicio" name="titulo" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" placeholder="Descripción del servicio" name="descripcion" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Orden</label>
                        <input type="number" class="form-control" placeholder="Orden de visualización" name="orden" required min="1">
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select class="form-control" name="estado" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Guardar</button>
                    <button type="reset" class="btn btn-warning"><i class="fa fa-circle-notch fa-fw"></i>Reiniciar</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i>Cancelar</button>      
                </form>  
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Servicio -->
<div class="modal fade" id="editServiceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Editar Servicio</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="servicios_transac.php?action=edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Título</label>
                        <input class="form-control" placeholder="Título del servicio" name="titulo" id="edit_titulo" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" placeholder="Descripción del servicio" name="descripcion" id="edit_descripcion" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Orden</label>
                        <input type="number" class="form-control" placeholder="Orden de visualización" name="orden" id="edit_orden" required min="1">
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select class="form-control" name="estado" id="edit_estado" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Actualizar</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i>Cancelar</button>      
                </form>  
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>