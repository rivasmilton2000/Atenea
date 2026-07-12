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
            //then it will be redirected
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit(); // Terminar la ejecución del script después de la redirección
    }
}

// Consulta para obtener los grados (se mantiene para el modal de agregar)
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
        <h4 class="m-2 font-weight-bold text-primary">Contenidos de evaluación&nbsp;
            <a href="#" data-toggle="modal" data-target="#vGraModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;"><i class="fas fa-fw fa-plus"></i></a>
        </h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"> 
    <thead>
        <tr>
            <th>TÍTULO DE CONTENIDO</th>
            <th>ASIGNATURA</th>
            <th>GRADO</th>
            <th>ACCIONES</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $query = 'SELECT c.contenido_id, c.titulo, c.material, a.A_NAME as asignatura, g.G_NAME as grado
              FROM contenidos c
              JOIN docentes_asignaturas da ON c.da_id = da.da_id
              JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
              JOIN grados g ON da.grado_id = g.G_ID
              WHERE c.c_estado = 1';  // Añadir la condición para c_estado
    $result = mysqli_query($db, $query) or die(mysqli_error($db));

    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>';
        echo '<td>' . $row['titulo'] . '</td>';
        echo '<td>' . $row['asignatura'] . '</td>';
        echo '<td>' . $row['grado'] . '</td>';
        echo '<td align="right">
            <div class="btn-group">
                <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="con_evaluacion_searchfrm.php?action=edit&id=' . $row['contenido_id'] . '"><i class="fas fa-fw fa-list-alt"></i> Detalles</a>
            </div>
            <div class="btn-group">
                <a type="button" class="btn btn-warning bg-gradient-warning btn-sm" href="con_evaluacion_edit.php?action=edit&id=' . $row['contenido_id'] . '"><i class="fas fa-fw fa-edit"></i> Editar</a>
            </div>
            <div class="btn-group">
                <a type="button" class="btn btn-info bg-gradient-info btn-sm" href="con_evaluacion_descargar.php?file=' . urlencode($row['material']) . '"><i class="fas fa-fw fa-download"></i> Descargar</a>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-danger bg-gradient-danger btn-sm" onclick="confirmDelete(' . $row['contenido_id'] . ')"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(contenidoId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Quieres eliminar este contenido de evaluación?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('con_evaluacion_delete.php?id=' + contenidoId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            '¡Eliminado!',
                            'El contenido de evaluación ha sido eliminado.',
                            'success'
                        ).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Error',
                            'No se pudo eliminar el registro.',
                            'error'
                        );
                    }
                });
        }
    });
}
</script>

<?php
include '../includes/footer.php';
?>

<!-- Contenido Modal -->
<div class="modal fade" id="vGraModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ingresar contenido a evaluar</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="con_evaluacion_transac.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="titulo">Título del contenido:</label>
                        <input type="text" class="form-control" id="titulo"  minlength="5" maxlength="80" name="titulo" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción del contenido:</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" minlength= "15" maxlength="250" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="material">Material del contenido:</label>
                        <input type="file" class="form-control-file" id="material" name="material" required>
                    </div>
                    <div class="form-group">
                        <label for="da_id">Asignatura de docente:</label>
                        <select class="form-control" id="da_id" name="da_id" required onchange="actualizarDocente()">
                            <option value="" disabled selected>Seleccione una opción</option>
                            <?php
                            $query = "SELECT da.da_id, a.A_NAME, g.G_NAME, p.p_name, e.FIRST_NAME, e.LAST_NAME
                                FROM docentes_asignaturas da
                                JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
                                JOIN grados g ON da.grado_id = g.G_ID
                                JOIN periodo p ON da.periodo_id = p.p_id
                                JOIN employee e ON da.profesor_id = e.EMPLOYEE_ID
                                WHERE da.da_estado = 1
                                ORDER BY a.A_NAME, g.G_NAME, p.p_name";
                            $result = mysqli_query($db, $query) or die(mysqli_error($db));
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='".$row['da_id']."' data-docente='".$row['FIRST_NAME']." ".$row['LAST_NAME']."'>"
                                .$row['A_NAME']." - ".$row['G_NAME']." - ".$row['p_name']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="docente">Docente encargado:</label>
                        <input type="text" class="form-control" id="docente" name="docente" readonly>
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
function actualizarDocente() {
    var select = document.getElementById('da_id');
    var docenteInput = document.getElementById('docente');
    var selectedOption = select.options[select.selectedIndex];
    docenteInput.value = selectedOption.getAttribute('data-docente');
}
</script>