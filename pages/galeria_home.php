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
        <h4 class="m-2 font-weight-bold text-primary">Galería de Actividades&nbsp;
            <a href="#" data-toggle="modal" data-target="#addGalleryModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;">
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
                        <th>IMAGEN</th>
                        <th>TÍTULO</th>
                        <th>CATEGORÍA</th>
                        <th>ORDEN</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = 'SELECT * FROM galeria ORDER BY orden ASC';
                    $result = mysqli_query($db, $query) or die(mysqli_error($db));

                    while ($row = mysqli_fetch_assoc($result)) {
                        $estadoClass = $row['estado'] == 1 ? 'badge-success' : 'badge-danger';
                        $estadoText = $row['estado'] == 1 ? 'Activo' : 'Inactivo';
                        
                        echo '<tr>';
                        echo '<td>' . $row['id'] . '</td>';
                        echo '<td><img src="../img/' . $row['imagen'] . '" alt="' . $row['titulo'] . '" style="width: 80px; height: 80px; object-fit: cover;" class="rounded"></td>';
                        echo '<td>' . $row['titulo'] . '</td>';
                        echo '<td><span class="badge badge-info">' . ucfirst($row['categoria']) . '</span></td>';
                        echo '<td>' . $row['orden'] . '</td>';
                        echo '<td><span class="badge ' . $estadoClass . '">' . $estadoText . '</span></td>';
                        echo '<td align="right">
                                <div class="btn-group">
                                    <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="#" data-toggle="modal" data-target="#editGalleryModal" 
                                       onclick="editGallery(' . $row['id'] . ', \'' . addslashes($row['titulo']) . '\', \'' . addslashes($row['imagen']) . '\', \'' . addslashes($row['categoria']) . '\', ' . $row['orden'] . ', ' . $row['estado'] . ')">
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
function confirmDelete(galleryId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Deseas eliminar esta imagen de la galería?",
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
            fetch('galeria_delete.php?id=' + galleryId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Eliminado',
                            text: 'La imagen ha sido eliminada.',
                            icon: 'success',
                            customClass: { 
                                popup: 'custom-popup-class',
                                title: 'custom-title-class',
                                confirmButton: 'custom-confirm-button-class'
                            }
                        }).then(() => {
                            window.location.href = 'galeria_home.php';
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

function editGallery(id, titulo, imagen, categoria, orden, estado) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_titulo').value = titulo;
    document.getElementById('edit_categoria').value = categoria;
    document.getElementById('edit_orden').value = orden;
    document.getElementById('edit_estado').value = estado;
    document.getElementById('current_image').innerHTML = '<img src="../img/' + imagen + '" style="width: 100px;" class="rounded">';
    document.getElementById('current_image_name').value = imagen;
}
</script>

<!-- Modal Agregar a Galería -->
<div class="modal fade" id="addGalleryModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Agregar a Galería</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="galeria_transac.php?action=add" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Título</label>
                        <input class="form-control" placeholder="Título de la actividad" name="titulo" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label>Imagen</label>
                        <input type="file" class="form-control-file" name="imagen" accept="image/*" required>
                        <small class="form-text text-muted">Formatos permitidos: JPG, JPEG, PNG. Tamaño máximo: 2MB</small>
                    </div>
                    <div class="form-group">
                        <label>Categoría</label>
                        <select class="form-control" name="categoria" required>
                            <option value="">Seleccionar categoría</option>
                            <option value="terapias">Terapias</option>
                            <option value="nutricion">Nutrición</option>
                            <option value="general">General</option>
                        </select>
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

<!-- Modal Editar Galería -->
<div class="modal fade" id="editGalleryModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Editar Imagen</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="galeria_transac.php?action=edit" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="current_image_name" id="current_image_name">
                    <div class="form-group">
                        <label>Título</label>
                        <input class="form-control" placeholder="Título de la actividad" name="titulo" id="edit_titulo" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label>Imagen Actual</label>
                        <div id="current_image" class="mb-2"></div>
                        <label>Cambiar Imagen (opcional)</label>
                        <input type="file" class="form-control-file" name="imagen" accept="image/*">
                        <small class="form-text text-muted">Deja en blanco si no deseas cambiar la imagen</small>
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