<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'];
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
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit(); // Terminar la ejecución del script después de la redirección
    }
}

// Consulta para el personal administrativo
$queryAdmin = 'SELECT e.EMPLOYEE_ID, e.FIRST_NAME, e.LAST_NAME, e.GENDER, e.EMAIL, e.PHONE_NUMBER, e.JOB_ID, j.JOB_TITLE
              FROM employee e
              JOIN job j ON e.JOB_ID = j.JOB_ID
              WHERE e.JOB_ID = 2 AND e.E_ESTADO = 1';
$resultAdmin = mysqli_query($db, $queryAdmin) or die(mysqli_error($db));

// Consulta para el personal de logística
$queryLogistica = 'SELECT e.EMPLOYEE_ID, e.FIRST_NAME, e.LAST_NAME, e.GENDER, e.EMAIL, e.PHONE_NUMBER, e.JOB_ID, j.JOB_TITLE
                   FROM employee e
                   JOIN job j ON e.JOB_ID = j.JOB_ID
                   WHERE e.JOB_ID = 3 AND e.E_ESTADO = 1';
$resultLogistica = mysqli_query($db, $queryLogistica) or die(mysqli_error($db));
?>

<!-- Personal administrativo -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Personal administrativo <a href="#" data-toggle="modal" data-target="#employeeModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;"><i class="fas fa-fw fa-plus"></i></a></h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered js-datatable" id="dataTableAdminStaff" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>NOMBRE</th>
                        <th>CORREO ELECTRÓNICO</th>
                        <th>ROL</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($resultAdmin)) {
                        echo '<tr>';
                        echo '<td>' . $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'] . '</td>';
                        echo '<td>' . $row['EMAIL'] . '</td>';
                        echo '<td>' . $row['JOB_TITLE'] . '</td>';
                        echo '<td align="right">
                                <div class="btn-group">
                                    <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="personal_searchfrm.php?action=edit&id=' . $row['EMPLOYEE_ID'] . '">
                                        <i class="fas fa-fw fa-list-alt"></i> Detalles
                                    </a>
                                    </div>
                                    <div class="btn-group">
                                    <a type="button" class="btn btn-warning bg-gradient-warning btn-sm" href="personal_edit.php?action=edit&id=' . $row['EMPLOYEE_ID'] . '">
                                        <i class="fas fa-fw fa-edit"></i> Editar
                                    </a>
                                    </div>
                                    <div class="btn-group">
                                    <a type="button" class="btn btn-danger bg-gradient-danger btn-sm" href="#" onclick="confirmDelete(\'' . $row['EMPLOYEE_ID'] . '\')">
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

<!-- Personal de logística -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Personal de logística&nbsp;<a href="#" data-toggle="modal" data-target="#logisticModal" style="border-radius: 0px;"></a></h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered js-datatable" id="dataTableLogisticsStaff" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>NOMBRE</th>
                        <th>CORREO ELECTRÓNICO</th>
                        <th>ROL</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($resultLogistica)) {
                        echo '<tr>';
                        echo '<td>' . $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'] . '</td>';
                        echo '<td>' . $row['EMAIL'] . '</td>';
                        echo '<td>' . $row['JOB_TITLE'] . '</td>';
                        echo '<td align="right">
                                <div class="btn-group">
                                    <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="personal_searchfrm.php?action=edit&id=' . $row['EMPLOYEE_ID'] . '">
                                        <i class="fas fa-fw fa-list-alt"></i> Detalles
                                    </a>
                                    </div>
                                    <div class="btn-group">
                                    <a type="button" class="btn btn-warning bg-gradient-warning btn-sm" href="personal_edit.php?action=edit&id=' . $row['EMPLOYEE_ID'] . '">
                                        <i class="fas fa-fw fa-edit"></i> Editar
                                    </a>
                                    </div>
                                    <div class="btn-group">
                                    <a type="button" class="btn btn-danger bg-gradient-danger btn-sm" href="#" onclick="confirmDelete(\'' . $row['EMPLOYEE_ID'] . '\')">
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

<?php include '../includes/footer.php'; ?>

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
        background-color: #3085d6 !important;
        border-color: #3085d6 !important;
    }
    .custom-cancel-button-class {
        background-color: #d33 !important;
        border-color: #d33 !important;
    }
</style>

<script>
function confirmDelete(employeeId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Deseas eliminar este empleado?",
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
            fetch(`personal_eliminar.php?id=${employeeId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Eliminado',
                            'El empleado ha sido eliminado.',
                            'success'
                        ).then(() => {
                            window.location.href = 'personal.php';
                        });
                    } else {
                        Swal.fire(
                            'Error',
                            'No se pudo eliminar el registro.',
                            'error'
                        );
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire(
                        'Error',
                        'Ocurrió un error al procesar la solicitud.',
                        'error'
                    );
                });
        }
    });
}
</script>
