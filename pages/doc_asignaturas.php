<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';
?>

<?php
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
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
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Asignaturas de docentes&nbsp;
            <a href="#" data-toggle="modal" data-target="#do_asModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;">
                <i class="fas fa-fw fa-plus"></i>
            </a>
        </h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>DOCENTE</th>
                        <th>ASIGNATURA</th>
                        <th>GRADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = 'SELECT da.da_id, e.FIRST_NAME, e.LAST_NAME, a.A_NAME, g.G_NAME, p.p_name AS periodo
                              FROM docentes_asignaturas da
                              JOIN employee e ON da.profesor_id = e.EMPLOYEE_ID
                              JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
                              JOIN grados g ON da.grado_id = g.G_ID
                              JOIN periodo p ON da.periodo_id = p.p_id
                              WHERE da.da_estado = 1
                              ORDER BY da.periodo_id';
                    $result = mysqli_query($db, $query) or die(mysqli_error($db));
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'] . '</td>';
                        echo '<td>' . $row['A_NAME'] . '</td>';
                        echo '<td>' . $row['G_NAME'] . '</td>';
                        echo '<td align="right">
                                    <div class="btn-group">
                                      <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="doc_asignaturas_searchfrm.php?action=edit&id=' . $row['da_id'] . '">
                                          <i class="fas fa-fw fa-list-alt"></i> Detalles
                                      </a>
                                    </div>  

                                    <div class="btn-group">
                                        <a type="button" class="btn btn-warning bg-gradient-warning btn-sm" href="doc_asignaturas_edit.php?action=edit&id='.$row['da_id'].'">
                                            <i class="fas fa-fw fa-edit"></i> Editar
                                        </a>
                                    </div> 

                                    <div class="btn-group">
                                      <button type="button" class="btn btn-danger bg-gradient-danger btn-sm" onclick="confirmDelete(' . $row['da_id'] . ')">
                                          <i class="fas fa-fw fa-trash"></i> Eliminar
                                      </button>
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
include '../includes/footer2.php';
?>

<!-- Employee Modal-->
<div class="modal fade" id="do_asModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ingresar asignatura a docente</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="doc_asignaturas_transac.php?action=add">
                <div class="form-group">
                    <select class="form-control" id="profesor" name="profesor_id" required>
                    <option value="" disabled selected hidden>Seleccionar docente</option>
                    <?php
                    $query = 'SELECT EMPLOYEE_ID, FIRST_NAME, LAST_NAME FROM employee WHERE JOB_ID = 1 AND E_ESTADO = 1';
                    $result = mysqli_query($db, $query) or die(mysqli_error($db));
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<option value="' . $row['EMPLOYEE_ID'] . '">' . $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'] . '</option>';
                    }
                    ?>
                    </select>
                </div>
                <div class="form-group">
                    <select class="form-control" id="materia" name="materia_id" required>
                    <option value="" disabled selected hidden>Seleccionar asignatura</option>
                    <?php
                    $query = 'SELECT ASIGNATURA_ID, A_NAME FROM asignaturas WHERE A_ESTADO = 1';
                    $result = mysqli_query($db, $query) or die(mysqli_error($db));
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<option value="' . $row['ASIGNATURA_ID'] . '">' . $row['A_NAME'] . '</option>';
                    }
                    ?>
                    </select>
                </div>
                <div class="form-group">
                    <select class="form-control" id="grado" name="grado_id" required>
                    <option value="" disabled selected hidden>Seleccionar grado</option>
                    <?php
                    $query = 'SELECT G_ID, G_NAME FROM grados WHERE G_ESTADO = 1';
                    $result = mysqli_query($db, $query) or die(mysqli_error($db));
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<option value="' . $row['G_ID'] . '">' . $row['G_NAME'] . '</option>';
                    }
                    ?>
                    </select>
                </div>
                    <div class="form-group">
                        <select class="form-control" id="periodo" name="periodo_id" required>
                            <option value="" disabled selected hidden>Seleccionar trimestre</option>
                            <?php
                            $query = 'SELECT p_id, p_name FROM periodo';
                            $result = mysqli_query($db, $query) or die(mysqli_error($db));
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '<option value="' . $row['p_id'] . '">' . $row['p_name'] . '</option>';
                            }
                            ?>
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
    function confirmDelete(employeeId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas eliminar la asignación de esta asignatura de este docente?",
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
                fetch('doc_asignaturas_eliminar.php?id=' + employeeId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
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
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message,
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
    </style>