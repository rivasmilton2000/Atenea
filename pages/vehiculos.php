<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';

// Verificación de la sesión
if (!isset($_SESSION['MEMBER_ID'])) {
    die('Acceso denegado');
}

// Consulta SQL usando consultas preparadas para evitar inyecciones SQL
$member_id = $_SESSION['MEMBER_ID'];
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ?';
$stmt = $db->prepare($query);
$stmt->bind_param('i', $member_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_type = $row['TYPE'];
    
    $redirect_urls = [
        'Personal' => 'empleados_vista.php',
        'Estudiante' => 'estudiante_vista.php',
        'Docente' => 'docentes_vista.php',
        'SuperAdmin' => 'sa_vista.php'
    ];

    if (isset($redirect_urls[$user_type])) {
        ?>
        <script type="text/javascript">
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirect_urls[$user_type]; ?>";
        </script>
        <?php
        exit(); // Terminar la ejecución del script después de la redirección
    }
}

$stmt->close();
?>

<!-- Incluyendo las bibliotecas antes del script de DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">
            Vehiculos&nbsp;
            <a href="#" data-toggle="modal" data-target="#vModal" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;">
                <i class="fas fa-fw fa-plus"></i>
            </a>
        </h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>PLACA</th>
                        <th>ENCARGADO</th>
                        <th>IMAGEN</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = 'SELECT v.id, v.vehicle_license, e.FIRST_NAME, e.LAST_NAME, v.vehicle_image, v.v_estado
                              FROM vehicles v
                              LEFT JOIN employee e ON v.vehicle_attendant = e.EMPLOYEE_ID
                              WHERE v.v_estado = 1
                              GROUP BY v.id';
                    $result = mysqli_query($db, $query) or die(mysqli_error($db));

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . $row['vehicle_license'] . '</td>';
                        echo '<td>' . ($row['FIRST_NAME'] && $row['LAST_NAME'] ? $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'] : 'No asignado') . '</td>';
                        echo '<td>' . ($row['vehicle_image'] ? '<img src="' . $row['vehicle_image'] . '" alt="Vehicle Image" style="max-width: 50px; max-height: 50px;">' : 'Sin imagen') . '</td>';
                        echo '<td align="right">
                                <div class="btn-group">
                                    <a class="btn btn-primary bg-gradient-primary btn-sm" href="vehiculos_searchfrm.php?action=edit&id=' . $row['id'] . '"><i class="fas fa-fw fa-list-alt"></i> Detalles</a>
                                </div>
                                <div class="btn-group">
                                    <a class="btn btn-warning bg-gradient-warning btn-sm" href="vehiculos_edit.php?action=edit&id=' . $row['id'] . '"><i class="fas fa-fw fa-edit"></i> Editar</a>
                                </div>
                                <div class="btn-group">
                                    <a class="btn btn-danger bg-gradient-danger btn-sm" href="#" onclick="confirmDelete(' . $row['id'] . ')"><i class="fas fa-fw fa-trash"></i> Eliminar</a>
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
<script>function confirmDelete(brandId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Quieres eliminar este vehículo?",
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
            fetch('vehiculos_delete.php?id=' + brandId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
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
}</script>


<?php
include '../includes/footer.php';
?>

<!-- Modal para agregar vehículos -->
<div class="modal fade" id="vModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ingresar vehiculo</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="vehiculos_transac.php?action=add" enctype="multipart/form-data">
                    <div class="form-group">
                        <input class="form-control" placeholder="Placa del vehículo" maxlength="7" minlength="5" name="vehicle_license" required>
                    </div>
                    <div class="form-group">
                        <input class="form-control" placeholder="Modelo del vehículo"  minlength="5" maxlength="40" name="vehicle_model" required>
                    </div>
                    <div class="form-group">
                        <label for="vehicle_image">Imagen del vehículo:</label>
                        <input type="file" class="form-control-file" id="vehicle_image" name="vehicle_image">
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