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

// Obtener el ID de la evaluación seleccionada
$evaluacion_id = $_GET['id'];

// Obtener los detalles de la evaluación
$query_evaluacion = "SELECT evaluaciones.*, contenidos.contenido_id
                     FROM evaluaciones
                     JOIN contenidos ON evaluaciones.contenido_id = contenidos.contenido_id
                     WHERE evaluaciones.evaluacion_id = $evaluacion_id";
$result_evaluacion = mysqli_query($db, $query_evaluacion) or die(mysqli_error($db));
$row_evaluacion = mysqli_fetch_assoc($result_evaluacion);
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Evaluaciones entregadas
        <a href="#" type="button" class="btn btn-primary bg-gradient-info" style="border-radius: 0px;" onclick="history.back()" value="volver atrás"><i class="fas fa-fw fa-backward"></i> Regresar</a>
        </h4>
    </div>

    <div class="card-body">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Título: <?php echo $row_evaluacion['titulo']; ?></h5>
                <p class="card-text">Descripción: <?php echo $row_evaluacion['descripcion']; ?></p>
                <p class="card-text">Fecha: <?php echo date('d-m-Y', strtotime($row_evaluacion['fecha'])); ?></p>
                <p class="card-text">Porcentaje de la evaluación: <?php echo $row_evaluacion['porcentaje']; ?>%</p>
            </div>
        </div>

        <?php
// Obtener las entregas de la evaluación
$query_entregas = "SELECT ev.ev_entregada_id, ev.alumno_id, ev.material, ev.observacion,
                          e.nombres_estudiante, e.apellidos_estudiante, e.numero_lista_estudiante
                   FROM ev_entregadas ev
                   JOIN estudiantes e ON ev.alumno_id = e.ESTUDIANTE_ID
                   WHERE ev.evaluacion_id = $evaluacion_id AND ev.ev_entregada_estado = 1";
$result_entregas = mysqli_query($db, $query_entregas) or die(mysqli_error($db));
?>
<div class="table-responsive">
    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>ESTUDIANTE</th>
                <th>NUM. LISTA</th>
                <th>OBSERVACIÓN</th>
                <th>MATERIAL</th>
                <th>ESTADO</th>
                <th>NOTA</th>
            </tr>
        </thead>
        <tbody>
        <?php
while ($row_entrega = mysqli_fetch_assoc($result_entregas)) {
    echo '<tr>';
    echo '<td>' . $row_entrega['apellidos_estudiante'] . ', ' . $row_entrega['nombres_estudiante'] . '</td>';
    echo '<td>' . $row_entrega['numero_lista_estudiante'] . '</td>';
    echo '<td>' . $row_entrega['observacion'] . '</td>';
    
    // Botón de descarga
    if (!empty($row_entrega['material'])) {
        echo '<td><a href="docentes_vista_descargar.php?id=' . $row_entrega['ev_entregada_id'] . '" class="btn btn-primary btn-sm"><i class="fas fa-download"></i> Descargar</a></td>';
    } else {
        echo '<td>No hay archivo</td>';
    }
    
    // Verificar si existe una nota para la entrega
    $query_nota = "SELECT * FROM notas WHERE id_ev_entregada = " . $row_entrega['ev_entregada_id'];
    $result_nota = mysqli_query($db, $query_nota) or die(mysqli_error($db));
    
    if (mysqli_num_rows($result_nota) > 0) {
        $row_nota = mysqli_fetch_assoc($result_nota);
        if ($row_nota['nota_estado'] == 1) {
            echo '<td><span style="color: green; font-weight: bold;">CALIFICADO</span> <a href="#" class="btn btn-sm btn-warning editar-nota-btn" data-toggle="modal" data-target="#editarNotaModal" onclick="$(\'#editar_ev_entregada_id\').val(' . $row_entrega['ev_entregada_id'] . '); $(\'#editar_valor_nota\').val(' . $row_nota['valor_nota'] . ');">Editar</a></td>';
            echo '<td>' . $row_nota['valor_nota'] . '</td>';
        } else {
            echo '<td><span style="color: orange; font-weight: bold;">NOTA INACTIVA</span></td>';
            echo '<td>-</td>';
        }
    } else {
        echo '<td><span style="color: #ff0000; font-weight: bold;">SIN CALIFICAR</span></td>';
        echo '<td><a href="#" class="btn btn-warning btn-sm cargar-nota-btn" data-toggle="modal" data-target="#cargarNotaModal" data-entrega-id="' . $row_entrega['ev_entregada_id'] . '">Cargar Nota</a></td>';
    }
    
    echo '</tr>';
}
?>
</tbody>
    </table>
</div>
    </div>
</div>

<!-- Modal para cargar nota -->
<div class="modal fade" id="cargarNotaModal" tabindex="-1" role="dialog" aria-labelledby="cargarNotaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cargarNotaModalLabel">Cargar nota</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="cargarNotaForm">
                    <input type="hidden" id="ev_entregada_id" name="ev_entregada_id">
                    <div class="form-group">
                        <label for="valor_nota">Nota:</label>
                        <input type="number" class="form-control" required oninput="validarNota(this)" id="valor_nota"  name="valor_nota" min="0.01" max="10.00" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i>Cancelar</button>
                <button type="button" class="btn btn-success" id="guardarNotaBtn"><i class="fa fa-check fa-fw"></i>Guardar nota</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar nota -->
<div class="modal fade" id="editarNotaModal" tabindex="-1" role="dialog" aria-labelledby="editarNotaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarNotaModalLabel">Editar nota</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editarNotaForm">
                    <input type="hidden" id="editar_ev_entregada_id" name="ev_entregada_id">
                    <div class="form-group">
                        <label for="editar_valor_nota">Nota:</label>
                        <input type="number" class="form-control" required oninput="validarNota(this)" required="" id="editar_valor_nota" name="valor_nota" step="0.01" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i>Cancelar</button>
                <button type="button" class="btn btn-success" id="actualizarNotaBtn"><i class="fa fa-check fa-fw"></i>Actualizar nota</button>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#dataTable');

        // Verificar si la tabla ya está inicializada
        if (!table.hasClass('dataTable')) {
            // Inicializar DataTables si no está inicializada
            table.DataTable({
                "order": [] // Evitar ordenar automáticamente al cargar
            });
        }

        // Función para abrir el modal y establecer el ID de la entrega
        $('#cargarNotaModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var entregaId = button.data('entrega-id');
            $('#ev_entregada_id').val(entregaId);

            console.log("Modal abierto - Entrega ID:", entregaId);
        });

        // Función para guardar la nota
        $('#guardarNotaBtn').on('click', function() {
            var entregaId = $('#ev_entregada_id').val();
            var valorNota = $('#valor_nota').val();

            console.log("Entrega ID:", entregaId);
            console.log("Valor Nota:", valorNota);

            $.ajax({
        url: 'ajax/guardar_nota.php',
        type: 'POST',
        data: {
            ev_entregada_id: entregaId,
            valor_nota: valorNota
        },
        success: function (response) {
            if (response === "success") {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'Nota añadida exitosamente.'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Recargar la página después de que el usuario cierre la alerta
                        location.reload();
                    }
                });
            } else {
                console.log("Error al guardar la nota: " + response);
            }
        },
        error: function (xhr, status, error) {
            console.log(xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al guardar la nota.'
            });
        }
    });
});

        $(document).on('click', '.cargar-nota-btn', function() {
            var entregaId = $(this).data('entrega-id');
            $('#ev_entregada_id').val(entregaId);
        });

        // Función para abrir el modal de editar nota y cargar la nota existente
        $('#editarNotaModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var entregaId = button.data('entrega-id');
            var notaActual = button.data('nota');

            $('#editar_ev_entregada_id').val(entregaId);
            $('#editar_valor_nota').val(notaActual);

            console.log("Modal de editar nota abierto - Entrega ID:", entregaId);
        });

        // Función para actualizar la nota
        $('#actualizarNotaBtn').on('click', function() {
            var entregaId = $('#editar_ev_entregada_id').val();
            var valorNota = $('#editar_valor_nota').val();

            console.log("Entrega ID:", entregaId);
            console.log("Valor Nota:", valorNota);

            $.ajax({
                url: 'ajax/actualizar_nota.php',
                type: 'POST',
                data: {
                    ev_entregada_id: entregaId,
                    valor_nota: valorNota
                },
                success: function (response) {
                    console.log("Respuesta del servidor:", response);
                    if (response === "success") {
                        // Actualizar la tabla después de actualizar la nota
                        location.reload();
                    } else {
                        console.log("Error al actualizar la nota: " + response);
                    }
                },
                error: function (xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        });
    });
    function validarNota(input) {
    var value = input.value;
    var regex = /^(?:[0-9]|[1-9][0-9]|10)(?:\.[0-9]?)?$/;

    // Si el campo está vacío, no mostrar alerta
    if (value === '') {
        return;
    }

    // Validar que el valor cumpla con el formato
    if (!regex.test(value)) {
        input.value = '';
        Swal.fire({
            icon: 'error',
            title: 'Nota no válida',
            text: 'Por favor, ingrese una nota válida (0-10).'
        });
        return;
    }

    // Asegurarse de que el valor no exceda 10
    if (parseFloat(value) > 10) {
        input.value = '0';
        Swal.fire({
            icon: 'error',
            title: 'Nota no válida',
            text: 'La nota no puede ser mayor a 10.'
        });
    }
}

// Función para actualizar la nota
$('#actualizarNotaBtn').on('click', function() {
    var entregaId = $('#editar_ev_entregada_id').val();
    var valorNota = $('#editar_valor_nota').val();

    console.log("Entrega ID:", entregaId);
    console.log("Valor Nota:", valorNota);

    $.ajax({
        url: 'ajax/actualizar_nota.php',
        type: 'POST',
        data: {
            ev_entregada_id: entregaId,
            valor_nota: valorNota
        },
        success: function (response) {
            console.log("Respuesta del servidor:", response);
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'Nota actualizada correctamente.'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        },
        error: function (xhr, status, error) {
            console.log(xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al actualizar la nota.'
            });
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>