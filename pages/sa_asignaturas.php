<?php
// Sección de inclusión de archivos
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

// Sección de verificación de permisos de usuario
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa=='Personal' || $Aa=='Estudiante' || $Aa=='Docente' || $Aa=='Admin'){
        if ($Aa=='Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa=='Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa=='Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa=='Admin') {
            $redirectUrl = "index.php";
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

// Sección de consulta de asignaturas para el selector
$sql = "SELECT DISTINCT A_NAME, ASIGNATURA_ID FROM asignaturas order by A_NAME asc";
$result = mysqli_query($db, $sql) or die ("Bad SQL: $sql");

$aaa = "<select class='form-control' name='asignatura' required>
        <option disabled selected hidden>Seleccionar asignatura</option>";
  while ($row = mysqli_fetch_assoc($result)) {
    $aaa .= "<option value='".$row['ASIGNATURA_ID']."'>".$row['A_NAME']."</option>";
  }

$aaa .= "</select>";
?>

<!-- Sección de visualización de la tabla de asignaturas -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Asignaturas&nbsp;
            <a  href="#" data-toggle="modal" data-target="#vAsiModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;"><i class="fas fa-fw fa-plus"></i></a>
        </h4>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"> 
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ASIGNATURA</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Sección de consulta y visualización de asignaturas
                    $query = 'SELECT ASIGNATURA_ID, A_NAME, A_ESTADO FROM asignaturas GROUP BY ASIGNATURA_ID';
                    $result = mysqli_query($db, $query) or die(mysqli_error($db));

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . $row['ASIGNATURA_ID'] . '</td>';
                        echo '<td>' . $row['A_NAME'] . '</td>';
                        echo '<td>';
                        if ($row['A_ESTADO'] == '1') {
                            echo '<span class="badge badge-success">Activo</span>';
                        } else {
                            echo '<span class="badge badge-danger">Inactivo</span>';
                        }
                        echo '</td>';
                        echo '<td align="right">
                                <div class="btn-group">
                                    <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="sa_asignaturas_searchfrm.php?action=edit&id=' . $row['ASIGNATURA_ID'] . '">
                                        <i class="fas fa-fw fa-list-alt"></i> Detalles
                                    </a>
                                    </div>
                                    <div class="btn-group">
                                    <a type="button" class="btn btn-warning bg-gradient-warning btn-sm" href="sa_asignaturas_edit.php?action=edit&id=' . $row['ASIGNATURA_ID'] . '">
                                        <i class="fas fa-fw fa-edit"></i> Editar
                                    </a>
                                    </div>
                                    <div class="btn-group">
                                    <a type="button" class="btn btn-danger bg-gradient-danger btn-sm" href="#" onclick="confirmDelete(' . $row['ASIGNATURA_ID'] . ')">
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

    <!-- Sección de script para confirmación de eliminación -->

    <link href='https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap' rel='stylesheet'>  <!-- Fuente opens sans -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- libreria online para las alertas -->
    <script>
    function confirmDelete(asignaturaId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas eliminar esta asignatura?",
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
                // Si el usuario confirma, realizamos la eliminación
                fetch('sa_asignaturas_delete.php?id=' + asignaturaId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Eliminada',
                            text: 'La asignatura ha sido eliminada correctamente.',
                            icon: 'success',
                            customClass: {
                                popup: 'custom-popup-class',
                                title: 'custom-title-class',
                                confirmButton: 'custom-confirm-button-class'
                            }
                        }).then(() => {
                            // Recargamos la página o actualizamos la tabla
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudo eliminar la asignatura.',
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

<?php
// Sección de inclusión del pie de página
include'../includes/footer_superadmin.php';
?>

<!-- Sección de modal para agregar asignatura -->
<div class="modal fade" id="vAsiModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ingresar asignatura</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="sa_asignaturas_transac.php?action=add">
                    <div class="form-group">
                        <input class="form-control" placeholder="Nombre de la asignatura" minlength="3" maxlength="30"  name="asignatura" required>
                    </div>
                    <div class="form-group">
                        <select class="form-control" name="estado" id="estado" required>
                            <option disabled selected value="">Estado de la asignatura</option>
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