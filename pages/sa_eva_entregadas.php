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

// La consulta para obtener los grados se mantiene igual para el modal de agregar
$sql = "SELECT DISTINCT G_NAME, G_ID FROM grados order by G_NAME asc";
$result = mysqli_query($db, $sql) or die ("Bad SQL: $sql");

$aaa = "<select class='form-control' name='grado' required>
        <option disabled selected hidden>Seleccionar grado</option>";
while ($row = mysqli_fetch_assoc($result)) {
    $aaa .= "<option value='".$row['G_ID']."'>".$row['G_NAME']."</option>";
}
$aaa .= "</select>";
?>
            
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Evaluaciones entregadas&nbsp;
            <a href="#" data-toggle="modal" data-target="#vGraModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;"><i class="fas fa-fw fa-plus"></i></a>
        </h4>
    </div>
    <div class="card-body">
    <div class="table-responsive">
    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"> 
<thead>
    <tr>
        <th>ESTUDIANTE</th>
        <th>EVALUACIÓN</th>
        <th>PORCENTAJE</th>
        <th>ESTADO</th>
        <th>ACCIONES</th>
    </tr>
</thead>
<tbody>
<?php
$query = 'SELECT ee.ev_entregada_id, e.titulo, e.porcentaje, ee.ev_entregada_estado,
                 es.nombres_estudiante, es.apellidos_estudiante, ee.material
          FROM ev_entregadas ee
          JOIN evaluaciones e ON ee.evaluacion_id = e.evaluacion_id
          JOIN estudiantes es ON ee.alumno_id = es.ESTUDIANTE_ID';
$result = mysqli_query($db, $query) or die(mysqli_error($db));

while ($row = mysqli_fetch_assoc($result)) {
    echo '<tr>';
    echo '<td>' . $row['apellidos_estudiante'] . ', ' . $row['nombres_estudiante'] . '</td>';
    echo '<td>' . $row['titulo'] . '</td>';
    echo '<td>' . $row['porcentaje'] . '%</td>';
    echo '<td>';
    if ($row['ev_entregada_estado'] == 1) {
        echo '<span class="badge badge-success">Activo</span>';
    } else {
        echo '<span class="badge badge-danger">Inactivo</span>';
    }
    echo '</td>';
    echo '<td align="right">
        <div class="btn-group">
            <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="sa_eva_entregadas_searchfrm.php?action=edit&id=' . $row['ev_entregada_id'] . '"><i class="fas fa-fw fa-list-alt"></i> Detalles</a>
        </div>
        <div class="btn-group">
            <a type="button" class="btn btn-warning bg-gradient-warning btn-sm" href="sa_eva_entregadas_edit.php?action=edit&id=' . $row['ev_entregada_id'] . '"><i class="fas fa-fw fa-edit"></i> Editar</a>
        </div>';
    
    // Agregamos el botón de descarga si existe un archivo
    if (!empty($row['material'])) {
        echo '<div class="btn-group">
            <a type="button" class="btn btn-info bg-gradient-info btn-sm" href="sa_eva_entregadas_descargar.php?id=' . $row['ev_entregada_id'] . '"><i class="fas fa-fw fa-download"></i> Descargar</a>
        </div>';
    }
    
    echo '<div class="btn-group">
             <a type="button" class="btn btn-danger bg-gradient-danger btn-sm" href="javascript:void(0);" onclick="confirmDelete(' . $row['ev_entregada_id'] . ');"><i class="fas fa-fw fa-trash"></i> Eliminar</a>
        </div>';
    
    echo '</td>';
    echo '</tr>';
}
?>
</tbody>
</table>
    </div>
</div>
</div>
<!-- Incluye SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(ev_entregada_id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Deseas eliminar esta evaluación entregada?",
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
            fetch('sa_eva_entregadas_delete.php?id=' + encodeURIComponent(ev_entregada_id), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
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
                        window.location.href = 'sa_eva_entregadas.php';
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
                    text: 'Hubo un problema al realizar la solicitud.',
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
<style>
    .custom-popup-class, .custom-title-class, .custom-confirm-button-class, .custom-cancel-button-class {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    .custom-title-class {
        font-weight: 700;
    }
    .custom-confirm-button-class, .custom-cancel-button-class {
        font-weight: 600;
    }
</style>
<?php
include '../includes/footer_superadmin.php';
?>

<!-- Evaluacion entregada Modal -->
<div class="modal fade" id="vGraModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ingresar evaluación entregada</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="sa_eva_entregadas_transac.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="evaluacion_id">Evaluación:</label>
                        <select class="form-control" id="evaluacion_id" name="evaluacion_id" required onchange="actualizarInfoEvaluacion()">
                            <option value="" disabled selected>Seleccione una evaluación</option>
                            <?php
                            $query = "SELECT evaluacion_id, titulo, fecha, porcentaje FROM evaluaciones WHERE evaluacion_estado = 1 ORDER BY titulo";
                            $result = mysqli_query($db, $query) or die(mysqli_error($db));
                            while ($row = mysqli_fetch_assoc($result)) {
                                $fecha = date('d-m-Y', strtotime($row['fecha'])); // Convertir fecha a formato dia-mes-año
                                echo "<option value='".$row['evaluacion_id']."' 
                                        data-fecha='".$fecha."'
                                        data-porcentaje='".$row['porcentaje']."%'>"
                                    .$row['titulo']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fecha de la evaluación:</label>
                        <input type="text" class="form-control" id="fecha_evaluacion" readonly>
                    </div>
                    <div class="form-group">
                        <label>Porcentaje de la evaluación:</label>
                        <input type="text" class="form-control" id="porcentaje_evaluacion" readonly>
                    </div>
                    <div class="form-group">
                        <label for="alumno_id">Alumno:</label>
                        <select class="form-control" id="alumno_id" name="alumno_id" required onchange="actualizarInfoAlumno()">
                            <option value="" disabled selected>Seleccione un alumno</option>
                            <?php
                            $query = "SELECT e.ESTUDIANTE_ID, e.nombres_estudiante, e.apellidos_estudiante, e.grado_id_estudiante, e.numero_lista_estudiante, g.G_NAME 
                                      FROM estudiantes e
                                      JOIN grados g ON e.grado_id_estudiante = g.G_ID
                                      WHERE e.estado_estudiante = 1 
                                      ORDER BY e.apellidos_estudiante, e.nombres_estudiante";
                            $result = mysqli_query($db, $query) or die(mysqli_error($db));
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='".$row['ESTUDIANTE_ID']."' 
                                        data-grado='".$row['G_NAME']."'
                                        data-numero_lista='".$row['numero_lista_estudiante']."'>"
                                    .$row['apellidos_estudiante'].", ".$row['nombres_estudiante']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Grado y número de lista del alumno:</label>
                        <input type="text" class="form-control" id="grado_numero_alumno" readonly>
                    </div>
                    <div class="form-group">
                        <label for="material">Material:</label>
                        <input type="file" class="form-control" id="material" name="material" required>
                    </div>
                    <div class="form-group">
                        <label for="observacion">Observación:</label>
                        <textarea class="form-control" id="observacion" name="observacion" minlength="5" maxlength="300" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="ev_entregada_estado">Estado de la evaluación entregada:</label>
                        <select class="form-control" id="ev_entregada_estado" name="ev_entregada_estado" required>
                            <option value="" disabled selected>Seleccione el estado del registro</option>
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
function actualizarInfoEvaluacion() {
    var select = document.getElementById('evaluacion_id');
    var selectedOption = select.options[select.selectedIndex];

    if (select.value) {
        document.getElementById('fecha_evaluacion').value = selectedOption.getAttribute('data-fecha');
        document.getElementById('porcentaje_evaluacion').value = selectedOption.getAttribute('data-porcentaje');
    } else {
        document.getElementById('fecha_evaluacion').value = '';
        document.getElementById('porcentaje_evaluacion').value = '';
    }
}

function actualizarInfoAlumno() {
    var select = document.getElementById('alumno_id');
    var selectedOption = select.options[select.selectedIndex];

    if (select.value) {
        var grado = selectedOption.getAttribute('data-grado');
        var numeroLista = selectedOption.getAttribute('data-numero_lista');
        document.getElementById('grado_numero_alumno').value = "Grado: " + grado + " - Número de lista: " + numeroLista;
    } else {
        document.getElementById('grado_numero_alumno').value = '';
    }
}
</script>