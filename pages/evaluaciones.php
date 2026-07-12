<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';

// Código de verificación de permisos (se mantiene igual)
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Personal' || $Aa == 'Estudiante' || $Aa == 'Docente' || $Aa == 'SuperAdmin') {
        if ($Aa == 'Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa == 'Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa == 'Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa == 'SuperAdmin') {
            $redirectUrl = "sa_vista.php";
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
        <h4 class="m-2 font-weight-bold text-primary">Evaluaciones&nbsp;
            <a href="#" data-toggle="modal" data-target="#vGraModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;"><i class="fas fa-fw fa-plus"></i></a>
        </h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"> 
    <thead>
        <tr>
            <th>EVALUACIÓN</th>
            <th>FECHA</th>
            <th>PORCENTAJE</th>
            <th>ACCIONES</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $query = 'SELECT evaluacion_id, titulo, DATE_FORMAT(fecha, "%d-%m-%Y") as fecha_formateada, porcentaje
              FROM evaluaciones
              WHERE evaluacion_estado = 1';
    $result = mysqli_query($db, $query) or die(mysqli_error($db));

    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>';
        echo '<td>' . $row['titulo'] . '</td>';
        echo '<td>' . $row['fecha_formateada'] . '</td>';
        echo '<td>' . $row['porcentaje'] . '%</td>';
        echo '<td align="right">
            <div class="btn-group">
                <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="evaluaciones_searchfrm.php?action=edit&id=' . $row['evaluacion_id'] . '"><i class="fas fa-fw fa-list-alt"></i> Detalles</a>
            </div>
            <div class="btn-group">
                <a type="button" class="btn btn-warning bg-gradient-warning btn-sm" href="evaluaciones_edit.php?action=edit&id=' . $row['evaluacion_id'] . '"><i class="fas fa-fw fa-edit"></i> Editar</a>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-danger bg-gradient-danger btn-sm" onclick="confirmDelete(' . $row['evaluacion_id'] . ')"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
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
include '../includes/footer.php';
?>

<!-- Evaluacion Modal -->
<div class="modal fade" id="vGraModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ingresar nueva evaluación</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="evaluaciones_transac.php">
                    <div class="form-group">
                        <label for="titulo">Nombre de la evaluación:</label>
                        <input type="text" class="form-control" id="titulo" minlength="5"  maxlength="80" name="titulo" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción de la evaluación:</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" minlength="5"  maxlength="250" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="fecha">Fecha de la evaluación:</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" required>
                    </div>
                    <div class="form-group">
                        <label for="porcentaje">Porcentaje de la evaluación:</label>
                        <input type="number" class="form-control" id="porcentaje" name="porcentaje" min="0" max="100" required>
                    </div>
                    <div class="form-group">
                        <label for="contenido_id">Contenido de evaluación:</label>
                        <select class="form-control" id="contenido_id" name="contenido_id" required onchange="actualizarInfoContenido()">
                            <option value="" disabled selected>Seleccione un contenido</option>
                            <?php
                            $query = "SELECT c.contenido_id, c.titulo, c.descripcion, 
                                             a.A_NAME, g.G_NAME, p.p_name, e.FIRST_NAME, e.LAST_NAME
                                      FROM contenidos c
                                      JOIN docentes_asignaturas da ON c.da_id = da.da_id
                                      JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
                                      JOIN grados g ON da.grado_id = g.G_ID
                                      JOIN periodo p ON da.periodo_id = p.p_id
                                      JOIN employee e ON da.profesor_id = e.EMPLOYEE_ID
                                      WHERE c.c_estado = 1
                                      ORDER BY c.titulo";
                            $result = mysqli_query($db, $query) or die(mysqli_error($db));
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='".$row['contenido_id']."' 
                                        data-descripcion='".$row['descripcion']."'
                                        data-asignatura='".$row['A_NAME']."'
                                        data-grado='".$row['G_NAME']."'
                                        data-periodo='".$row['p_name']."'
                                        data-docente='".$row['FIRST_NAME']." ".$row['LAST_NAME']."'>"
                                    .$row['titulo']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div id="infoContenido" style="display: none;">
                        <h6>Información del contenido seleccionado:</h6>
                        <div class="form-group">
                            <label>Descripción:</label>
                            <textarea class="form-control" id="contenidoDescripcion" readonly></textarea>
                        </div>
                        <div class="form-group">
                            <label>Asignatura:</label>
                            <input type="text" class="form-control" id="contenidoAsignatura" readonly>
                        </div>
                        <div class="form-group">
                            <label>Grado:</label>
                            <input type="text" class="form-control" id="contenidoGrado" readonly>
                        </div>
                        <div class="form-group">
                            <label>Periodo:</label>
                            <input type="text" class="form-control" id="contenidoPeriodo" readonly>
                        </div>
                        <div class="form-group">
                            <label>Docente:</label>
                            <input type="text" class="form-control" id="contenidoDocente" readonly>
                        </div>
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
function actualizarInfoContenido() {
    var select = document.getElementById('contenido_id');
    var infoContenido = document.getElementById('infoContenido');
    var selectedOption = select.options[select.selectedIndex];

    if (select.value) {
        document.getElementById('contenidoDescripcion').value = selectedOption.getAttribute('data-descripcion');
        document.getElementById('contenidoAsignatura').value = selectedOption.getAttribute('data-asignatura');
        document.getElementById('contenidoGrado').value = selectedOption.getAttribute('data-grado');
        document.getElementById('contenidoPeriodo').value = selectedOption.getAttribute('data-periodo');
        document.getElementById('contenidoDocente').value = selectedOption.getAttribute('data-docente');
        infoContenido.style.display = 'block';
    } else {
        infoContenido.style.display = 'none';
    }
}
// Validación de porcentaje
document.getElementById('porcentaje').addEventListener('input', function() {
    var input = this;
    var value = input.value;

    // Permitir solo números enteros entre 1 y 100
    if (value !== '' && (value < 0 || value > 100 || !Number.isInteger(Number(value)))) {
        input.value = value.slice(0, -1);
    }
});

// Limitar la cantidad de dígitos a 3
document.getElementById('porcentaje').addEventListener('input', function() {
    var input = this;
    var value = input.value;

    if (value.length > 3) {
        input.value = value.slice(0, 3);
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(evaluacionId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Quieres eliminar esta evaluación?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('evaluaciones_delete.php?id=' + evaluacionId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            '¡Eliminado!',
                            'La evaluación ha sido eliminada.',
                            'success'
                        ).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Error',
                            'No se pudo eliminar la evaluación.',
                            'error'
                        );
                    }
                });
        }
    });
}
</script>   