<?php
include '../includes/connection.php';
include '../includes/sidebar_docente.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Admin' || $Aa == 'Estudiante' || $Aa == 'Personal' || $Aa == 'SuperAdmin') {
        if ($Aa == 'Admin') {
            $redirectUrl = "index.php";
        } elseif ($Aa == 'Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa == 'Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa == 'SuperAdmin') {
            $redirectUrl = "sa_vista.php";
        }
        ?>
        <script type="text/javascript">
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit(); // Terminar la ejecución del script después de la redirección
    }
}

// Obtener el ID de la asignatura seleccionada
$da_id = $_GET['id'];
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Contenidos a evaluar&nbsp;
            <a href="#" data-toggle="modal" data-target="#vConModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;"><i class="fas fa-fw fa-plus"></i></a>
        </h4>
    </div>

    <div class="card-body">
<?php
$query = "SELECT c.contenido_id, c.titulo, c.descripcion, c.material,
(SELECT COUNT(*) FROM evaluaciones e WHERE e.contenido_id = c.contenido_id AND e.evaluacion_estado = 1) as evaluaciones_count
FROM contenidos c
INNER JOIN docentes_asignaturas da ON c.da_id = da.da_id
WHERE da.da_id = $da_id AND c.c_estado = 1";
$result = mysqli_query($db, $query) or die(mysqli_error($db));

while ($row = mysqli_fetch_assoc($result)) {
    // El resto del código permanece igual
    echo '<div class="card mb-3">';
    echo '<div class="card-body">';
    echo '<h5 class="card-title">Título: ' . $row['titulo'] . '</h5>';
    echo '<p class="card-text">Descipción: ' . $row['descripcion'] . '</p>';
    echo '<div class="d-flex justify-content-between">';
    
    echo '</div>';
    echo '<hr>';
    echo '<a href="docentes_vista_evaluaciones.php?id=' . $row['contenido_id'] . '" class="btn btn-success btn-sm mr-2"><i class="fas fa-plus"></i> Asignar evaluación</a>';
    echo '<a href="docentes_vista_descargar2.php?file=' . urlencode($row['material']) . '" class="btn btn-info btn-sm mr-2"><i class="fas fa-download"></i> Descargar material</a>';
    echo '<a href="#" onclick="editContenido(' . $row['contenido_id'] . ', ' . $da_id . ')" class="btn btn-warning btn-sm mr-2" data-toggle="modal" data-target="#editConModal"><i class="fas fa-edit"></i> Editar</a>';
    echo '<a href="#" onclick="deleteContenido(' . $row['contenido_id'] . ')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Eliminar</a>';
    
    // Agregar el contador de evaluaciones
    echo '<a class="mt-2 mb-0 text-muted" style="font-size: 0.9em;">&nbsp; &nbsp; &nbsp;Evaluaciones asignadas: ' . $row['evaluaciones_count'] . '</a>';
    
    echo '</div>';
    echo '</div>';
}
?>
</div>
</div>

<?php
include '../includes/footer.php';
?>

<!-- Contenido Modal -->
<div class="modal fade" id="vConModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ingresar contenido a evaluar</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="docentes_vista_contenidos_transac.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="titulo">Título del contenido:</label>
                        <input type="text" class="form-control" id="titulo" minlength="5" maxlength="90" name="titulo" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción del contenido:</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" minlength="5" maxlength="250" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="material">Material del contenido:</label>
                        <input type="file" class="form-control-file" id="material" name="material" required>
                    </div>
                    <input type="hidden" name="da_id" value="<?php echo $da_id; ?>">
                    <hr>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Guardar</button>
                    <button type="reset" class="btn btn-warning"><i class="fa fa-circle-notch fa-fw"></i>Reiniciar</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i>Cancelar</button>      
                </form>  
            </div>
        </div>
    </div>
</div>

<!-- Contenido Edit Modal -->
<div class="modal fade" id="editConModal" tabindex="-1" role="dialog" aria-labelledby="editConModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editConModalLabel">Editar contenido</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
            <form role="form" method="post" action="docentes_vista_contenidos_update.php" enctype="multipart/form-data">
                <input type="hidden" id="edit_contenido_id" name="contenido_id">
                <input type="hidden" id="edit_da_id" name="da_id"> <!-- Agregar este campo oculto -->
                    <div class="form-group">
                        <label for="edit_titulo">Título del contenido:</label>
                        <input type="text" class="form-control" id="edit_titulo" minlength="5" maxlength="90" name="titulo" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_descripcion">Descripción del contenido:</label>
                        <textarea class="form-control" id="edit_descripcion" name="descripcion" minlength="5" maxlength="250" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_material">Material del contenido:</label>
                        <input type="file" class="form-control-file" id="edit_material" name="material">
                        <small class="form-text text-muted">Deja este campo vacío si no deseas cambiar el archivo.</small>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Guardar cambios</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i>Cancelar</button>      
                </form>  
            </div>
        </div>
    </div>
</div>

<script>
function editContenido(contenidoId, da_id) {
    // Realizar una solicitud AJAX para obtener los detalles del contenido
    $.ajax({
        url: 'ajax/contenidos_get.php',
        type: 'GET',
        data: { id: contenidoId },
        dataType: 'json',
        success: function(response) {
            // Rellenar los campos del modal de edición con los detalles del contenido
            $('#edit_contenido_id').val(response.contenido_id);
            $('#edit_titulo').val(response.titulo);
            $('#edit_descripcion').val(response.descripcion);
            $('#edit_da_id').val(da_id); // Agregar esta línea para establecer el valor de da_id en un campo oculto
        },
        error: function(xhr, status, error) {
            console.log(xhr.responseText);
        }
    });
}

function deleteContenido(contenidoId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Deseas eliminar este contenido?",
        icon: 'warning',
        showCancelButton: true,
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
            // Realizar una solicitud AJAX para eliminar la evaluación
            $.ajax({
                url: 'ajax/contenidos_delete.php',
                type: 'POST',
                data: { id: contenidoId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: 'El contenido se ha eliminado correctamente.',
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'custom-popup-class',
                                title: 'custom-title-class',
                                confirmButton: 'custom-confirm-button-class'
                            }
                        }).then(() => {
                            location.reload(); // Recargar la página después de la eliminación exitosa
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ha ocurrido un error al eliminar el contenido.',
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'custom-popup-class',
                                title: 'custom-title-class',
                                confirmButton: 'custom-confirm-button-class'
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        }
    });
}
</script>
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
    .custom-confirm-button-class {
        background-color: #007BFF !important; /* Azul */
        color: white !important;
    }
    .custom-cancel-button-class {
        background-color: #e74a3b !important; /* Color por defecto de cancelación */
        color: white !important;
    }
</style>