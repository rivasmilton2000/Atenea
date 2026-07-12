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

// Obtener el ID del contenido seleccionado
$contenido_id = $_GET['id'];
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Evaluaciones asignadas&nbsp;
            <a href="#" data-toggle="modal" data-target="#vConModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;"><i class="fas fa-fw fa-plus"></i></a>
            <a href="#" type="button" class="btn btn-primary bg-gradient-info" style="border-radius: 0px;" onclick="history.back()" value="volver atrás"><i class="fas fa-fw fa-backward"></i> Regresar</a>
        </h4>
    </div>

    <div class="card-body">
<?php
$query = "SELECT evaluacion_id, titulo, descripcion, fecha, porcentaje 
          FROM evaluaciones
          WHERE contenido_id = $contenido_id AND evaluacion_estado = 1";
$result = mysqli_query($db, $query) or die(mysqli_error($db));

while ($row = mysqli_fetch_assoc($result)) {
    echo '<div class="card mb-3">';
    echo '<div class="card-body">';
    echo '<h5 class="card-title">Título: ' . $row['titulo'] . '</h5>';
    echo '<p class="card-text">Descripción: ' . $row['descripcion'] . '</p>';
    echo '<p class="card-text">Fecha: ' . date('d-m-Y', strtotime($row['fecha'])) . '</p>';
    echo '<p class="card-text">Porcentaje de la evaluación: ' . $row['porcentaje'] . '%</p>';
    echo '<div class="d-flex justify-content-between">';
    
    echo '</div>';
    echo '<hr>';
    echo '<a href="#" onclick="editEvaluacion(' . $row['evaluacion_id'] . ')" class="btn btn-warning btn-sm mr-2" data-toggle="modal" data-target="#editEvalModal"><i class="fas fa-edit"></i> Editar</a>';
    echo '<a href="#" onclick="deleteEvaluacion(' . $row['evaluacion_id'] . ')" class="btn btn-danger btn-sm mr-2"><i class="fas fa-trash"></i> Eliminar</a>';
    echo '<a href="docentes_vista_entregas.php?id=' . $row['evaluacion_id'] . '" class="btn btn-success btn-sm mr-2"><i class="fas fa-book-open"></i> Ver entregas</a>';
    echo '</div>';
    echo '</div>';
}
?>
</div>
</div>

<?php
include '../includes/footer.php';
?>

<!-- Evaluacion Modal -->
<div class="modal fade" id="vConModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ingresar evaluación</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="docentes_vista_evaluaciones_transac.php">
                    <div class="form-group">
                        <label for="titulo">Título de la evaluación:</label>
                        <input type="text" class="form-control" id="titulo" minlength="5" maxlength="80" name="titulo" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción de la evaluación:</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" minlength="5" maxlength="250"  rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="fecha">Fecha de la evaluación:</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" required>
                    </div>
                    <div class="form-group">
                        <label for="porcentaje">Porcentaje de la evaluación:</label>
                        <input type="number" class="form-control" id="porcentaje" name="porcentaje" min="0" max="100" required>
                    </div>
                    <input type="hidden" name="contenido_id" value="<?php echo $contenido_id; ?>">
                    <hr>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Guardar</button>
                    <button type="reset" class="btn btn-warning"><i class="fa fa-circle-notch fa-fw"></i>Reiniciar</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i>Cancelar</button>      
                </form>  
            </div>
        </div>
    </div>
</div>

<!-- Evaluacion Edit Modal -->
<div class="modal fade" id="editEvalModal" tabindex="-1" role="dialog" aria-labelledby="editEvalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEvalModalLabel">Editar evaluación</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
            <form role="form" method="post" action="docentes_vista_evaluaciones_update.php">
                <input type="hidden" id="edit_evaluacion_id" name="evaluacion_id">
                    <div class="form-group">
                        <label for="edit_titulo">Título de la evaluación:</label>
                        <input type="text" class="form-control" id="edit_titulo" name="titulo" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_descripcion">Descripción de la evaluación:</label>
                        <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_fecha">Fecha de la evaluación:</label>
                        <input type="date" class="form-control" id="edit_fecha" name="fecha" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_porcentaje">Porcentaje de la evaluación:</label>
                        <input type="number" class="form-control" id="edit_porcentaje" name="porcentaje" min="0" max="100" required>
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
function editEvaluacion(evaluacionId) {
    // Realizar una solicitud AJAX para obtener los detalles de la evaluación
    $.ajax({
        url: 'ajax/evaluaciones_get.php',
        type: 'GET',
        data: { id: evaluacionId },
        dataType: 'json',
        success: function(response) {
            // Rellenar los campos del modal de edición con los detalles de la evaluación
            $('#edit_evaluacion_id').val(response.evaluacion_id);
            $('#edit_titulo').val(response.titulo);
            $('#edit_descripcion').val(response.descripcion);
            $('#edit_fecha').val(response.fecha);
            $('#edit_porcentaje').val(response.porcentaje);
        },
        error: function(xhr, status, error) {
            console.log(xhr.responseText);
        }
    });
}

function deleteEvaluacion(evaluacionId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Deseas eliminar esta evaluación?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Realizar una solicitud AJAX para eliminar la evaluación
            $.ajax({
                url: 'ajax/evaluaciones_delete.php',
                type: 'POST',
                data: { id: evaluacionId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            'Eliminado!',
                            'La evaluación ha sido eliminada.',
                            'success'
                        ).then(() => {
                            location.reload(); // Recargar la página después de la eliminación exitosa
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            'Ha ocurrido un error al eliminar la evaluación.',
                            'error'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire(
                        'Error!',
                        'Ha ocurrido un error al eliminar la evaluación.',
                        'error'
                    );
                    console.log(xhr.responseText);
                }
            });
        }
    });
}

</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const porcentajeInput = document.getElementById('porcentaje');

    porcentajeInput.addEventListener('input', function () {
        let value = parseInt(porcentajeInput.value, 10);

        // Check if value is out of range
        if (value > 100 || value < 1) {
            showAlert('El porcentaje de esta evaluación no puede exceder del 100%');
            porcentajeInput.value = ''; // Clear the input value
        }
    });

    porcentajeInput.addEventListener('keypress', function (event) {
        // Allow only digits
        if (event.key < '0' || event.key > '9') {
            event.preventDefault();
        }

        // Allow only up to 3 digits
        if (porcentajeInput.value.length >= 3) {
            event.preventDefault();
        }
    });

    function showAlert(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonText: 'OK',
            customClass: {
                popup: 'swal2-popup',
                title: 'swal2-title',
                content: 'swal2-content',
                confirmButton: 'swal2-confirm'
            }
        });
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const porcentajeInput = document.getElementById('edit_porcentaje');

    porcentajeInput.addEventListener('input', function () {
        let value = parseInt(porcentajeInput.value, 10);

        // Check if value is out of range
        if (value > 100 || value < 1) {
            showAlert('El porcentaje de esta evaluación no puede exceder del 100%');
            porcentajeInput.value = ''; // Clear the input value
        }
    });

    porcentajeInput.addEventListener('keypress', function (event) {
        // Allow only digits
        if (event.key < '0' || event.key > '9') {
            event.preventDefault();
        }

        // Allow only up to 3 digits
        if (porcentajeInput.value.length >= 3) {
            event.preventDefault();
        }
    });

    function showAlert(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonText: 'OK',
            customClass: {
                popup: 'swal2-popup',
                title: 'swal2-title',
                content: 'swal2-content',
                confirmButton: 'swal2-confirm'
            }
        });
    }
});
</script>