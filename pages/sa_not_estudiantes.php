<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

// Código de verificación de permisos (se mantiene igual)
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Personal' || $Aa == 'Estudiante' || $Aa == 'Docente' || $Aa == 'Admin') {
        if ($Aa == 'Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa == 'Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa == 'Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa == 'Admin') {
            $redirectUrl = "index.php";
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
        <h4 class="m-2 font-weight-bold text-primary">Notas&nbsp;
            <a href="#" data-toggle="modal" data-target="#addNotaModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;"><i class="fas fa-fw fa-plus"></i></a>
        </h4>
    </div>
    <div class="card-body">
    <div class="table-responsive">
    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"> 
<thead>
    <tr>
        <th>ESTUDIANTE</th>
        <th>EVALUACIÓN</th>
        <th>NOTA</th>
        <th>ESTADO</th>
        <th>ACCIONES</th>
    </tr>
</thead>
<tbody>
<?php
$query = 'SELECT n.nota_id, n.valor_nota, n.nota_estado, 
                 e.ESTUDIANTE_ID, e.apellidos_estudiante, e.nombres_estudiante,
                 ev.titulo as evaluacion_titulo
          FROM notas n
          JOIN ev_entregadas ee ON n.id_ev_entregada = ee.ev_entregada_id
          JOIN evaluaciones ev ON ee.evaluacion_id = ev.evaluacion_id
          JOIN estudiantes e ON ee.alumno_id = e.ESTUDIANTE_ID
          ORDER BY e.apellidos_estudiante, e.nombres_estudiante, ev.titulo';
$result = mysqli_query($db, $query) or die(mysqli_error($db));

while ($row = mysqli_fetch_assoc($result)) {
    echo '<tr>';
    echo '<td>' . $row['apellidos_estudiante'] . ', ' . $row['nombres_estudiante'] . '</td>';
    echo '<td>' . $row['evaluacion_titulo'] . '</td>';
    echo '<td>' . $row['valor_nota'] . '</td>';
    echo '<td>';
    if ($row['nota_estado'] == 1) {
        echo '<span class="badge badge-success">Activo</span>';
    } else {
        echo '<span class="badge badge-danger">Inactivo</span>';
    }
    echo '</td>';
    echo '<td align="right">
        <div class="btn-group">
            <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="sa_not_estudiantes_searchfrm.php?action=edit&id=' . $row['nota_id'] . '"><i class="fas fa-fw fa-list-alt"></i> Detalles</a>
        </div>
        <div class="btn-group">
            <a type="button" class="btn btn-warning bg-gradient-warning btn-sm" href="sa_not_estudiantes_edit.php?action=edit&id=' . $row['nota_id'] . '"><i class="fas fa-fw fa-edit"></i> Editar</a>
        </div>
        <div class="btn-group">
           <a type="button" class="btn btn-danger bg-gradient-danger btn-sm" href="#" onclick="eliminarNota(' . $row['nota_id'] . '); return false;"><i class="fas fa-fw fa-trash"></i> Eliminar</a>
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

<?php
include '../includes/footer_superadmin.php';
?>

<!-- Nota Modal -->
<div class="modal fade" id="addNotaModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ingresar nueva nota</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="sa_not_estudiantes_transac.php">
                    <div class="form-group">
                        <label for="ev_entregada_id">Evaluación entregada:</label>
                        <select class="form-control" id="ev_entregada_id" name="ev_entregada_id" required onchange="actualizarInfoEvaluacionEntregada()">
    <option value="" disabled selected>Seleccione una evaluación entregada</option>
    <?php
    $query = "SELECT ee.ev_entregada_id, 
                     e.titulo AS evaluacion_titulo, 
                     CONCAT(es.apellidos_estudiante, ', ', es.nombres_estudiante) AS nombre_estudiante,
                     g.G_NAME AS grado,
                     es.numero_lista_estudiante,
                     e.fecha AS fecha_evaluacion,
                     e.porcentaje
              FROM ev_entregadas ee
              JOIN evaluaciones e ON ee.evaluacion_id = e.evaluacion_id
              JOIN estudiantes es ON ee.alumno_id = es.ESTUDIANTE_ID
              JOIN grados g ON es.grado_id_estudiante = g.G_ID
              WHERE e.evaluacion_estado = 1 
                AND es.estado_estudiante = 1 
                AND ee.ev_entregada_estado = 1
              ORDER BY e.titulo, es.apellidos_estudiante, es.nombres_estudiante";
    $result = mysqli_query($db, $query) or die(mysqli_error($db));
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<option value='".$row['ev_entregada_id']."' 
                data-estudiante='".$row['nombre_estudiante']."'
                data-grado='".$row['grado']."'
                data-numero-lista='".$row['numero_lista_estudiante']."'
                data-fecha='".$row['fecha_evaluacion']."'
                data-porcentaje='".$row['porcentaje']."'>"
             .$row['evaluacion_titulo']." - ".$row['nombre_estudiante']."</option>";
    }
    ?>
</select>
                    </div>
                    <div class="form-group">
                        <label>Estudiante:</label>
                        <input type="text" class="form-control" id="nombre_estudiante" readonly>
                    </div>
                    <div class="form-group">
                        <label>Grado:</label>
                        <input type="text" class="form-control" id="grado_estudiante" readonly>
                    </div>
                    <div class="form-group">
                        <label>Número de lista:</label>
                        <input type="text" class="form-control" id="numero_lista_estudiante" readonly>
                    </div>
                    <div class="form-group">
                        <label>Fecha de evaluación:</label>
                        <input type="text" class="form-control" id="fecha_evaluacion" readonly>
                    </div>
                    <div class="form-group">
                        <label>Porcentaje de evaluación:</label>
                        <input type="text" class="form-control" id="porcentaje_evaluacion" readonly>
                    </div>
                    <div class="form-group">
                        <label for="valor_nota">Nota:</label>
                        <input type="number" class="form-control" required oninput="validarNota(this)" id="valor_nota" name="valor_nota" step="0.01" min="0" max="10" required>
                    </div>
                    <div class="form-group">
                        <label for="nota_estado">Estado de la nota:</label>
                        <select class="form-control" id="nota_estado" name="nota_estado" required>
                            <option value="" disabled selected>Seleccione el estado de la nota</option>
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

<script>
function actualizarInfoEvaluacionEntregada() {
    var select = document.getElementById('ev_entregada_id');
    var selectedOption = select.options[select.selectedIndex];
    
    document.getElementById('nombre_estudiante').value = selectedOption.getAttribute('data-estudiante');
    document.getElementById('grado_estudiante').value = selectedOption.getAttribute('data-grado');
    document.getElementById('numero_lista_estudiante').value = selectedOption.getAttribute('data-numero-lista');
    document.getElementById('fecha_evaluacion').value = selectedOption.getAttribute('data-fecha');
    document.getElementById('porcentaje_evaluacion').value = selectedOption.getAttribute('data-porcentaje') + '%';
}
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
        return; // No mostrar alerta aquí para valores no válidos
    }

    // Asegurarse de que el valor no exceda 10
    if (parseFloat(value) > 10) {
        input.value = '0'; // Ajustar el valor a 10
        showAlert('error', 'Nota no válida', 'La nota no puede ser mayor a 10.');
    }
}

function showAlert(icon, title, text) {
    Swal.fire({
        icon: icon,
        title: title,
        text: text,
        confirmButtonText: 'OK',
        customClass: {
            popup: 'custom-popup-class',
            title: 'custom-title-class',
            confirmButton: 'custom-confirm-button-class'
        }
    });
}

</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<style>
    .custom-popup-class {
        font-family: 'Open Sans', sans-serif;
    }
    .custom-title-class {
        font-family: 'Open Sans', sans-serif;
        font-weight: 700;
    }
    .custom-confirm-button-class, .custom-cancel-button-class {
        font-family: 'Open Sans', sans-serif;
        font-weight: 600;
    }
</style>

<script>
function eliminarNota(notaId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Deseas eliminar esta nota de este estudiante?",
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
            fetch(`sa_not_estudiantes_delete.php?id=${notaId}`, {
                method: 'GET',
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Eliminado!',
                        text: data.message,
                        icon: 'success',
                        customClass: {
                            popup: 'custom-popup-class',
                            title: 'custom-title-class',
                            confirmButton: 'custom-confirm-button-class'
                        }
                    }).then(() => {
                        window.location.reload(); // Recargar la página para actualizar la lista
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                        customClass: {
                            popup: 'custom-popup-class',
                            title: 'custom-title-class',
                            confirmButton: 'custom-confirm-button-class'
                        }
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Hubo un problema al procesar la solicitud.',
                    icon: 'error',
                    customClass: {
                        popup: 'custom-popup-class',
                        title: 'custom-title-class',
                        confirmButton: 'custom-confirm-button-class'
                    }
                });
            });
        }
    });
}
</script>