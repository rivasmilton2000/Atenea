<?php
include'../includes/connection.php';

include'../includes/sidebar_superadmin.php';

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

?>
<!-- ADMIN TABLE -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Personal administrativo <a  href="#" data-toggle="modal" data-target="#employeeModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;"><i class="fas fa-fw fa-plus"></i></a></h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered js-datatable" id="dataTableSuperadminStaff" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>NOMBRE</th>
                        <th>CORREO ELECTRÓNICO</th>
                        <th>ROL</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php                  
                        $query = 'SELECT e.EMPLOYEE_ID, e.FIRST_NAME, e.LAST_NAME, e.GENDER, e.EMAIL, e.PHONE_NUMBER, e.JOB_ID, e.E_ESTADO, j.JOB_TITLE
                        FROM employee e
                        JOIN job j ON e.JOB_ID = j.JOB_ID
                        WHERE e.JOB_ID = 2';
                        $result = mysqli_query($db, $query) or die(mysqli_error($db));
                        
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>';
                            echo '<td>' . $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'] . '</td>';
                            echo '<td>' . $row['EMAIL'] . '</td>';
                            echo '<td>' . $row['JOB_TITLE'] . '</td>';
                            echo '<td align="right">';
                            if ($row['E_ESTADO'] == 1) {
                                echo '<span class="badge badge-success">Activo</span>';
                            } else {
                                echo '<span class="badge badge-danger">Inactivo</span>';
                            }
                            echo '</td>';
                            echo '<td align="right">
                                <div class="btn-group">
                                    <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="sa_personal_searchfrm.php?action=edit&id=' . $row['EMPLOYEE_ID'] . '">
                                        <i class="fas fa-fw fa-list-alt"></i> Detalles
                                    </a>
                                    </div>
                                    <div class="btn-group">
                                    <a type="button" class="btn btn-warning bg-gradient-warning btn-sm" href="sa_personal_edit.php?action=edit&id=' . $row['EMPLOYEE_ID'] . '">
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



<div class="card shadow mb-4"> 
<div class="card-header py-3"> 
<h4 class="m-2 font-weight-bold text-primary">Personal de logistica&nbsp;
<a href="#" data-toggle="modal" data-target="#logisticModal" style="border-radius: 0px;"></a>
</h4> 
</div> 
<div class="card-body"> 
<div class="table-responsive"> 
<table class="table table-bordered js-datatable" id="dataTableSuperadminLogistics" width="100%" cellspacing="0"> 
<thead> 
<tr> 
<th>NOMBRE</th> 
<th>CORREO ELECTRÓNICO</th> 
<th>ROL</th> <th>ESTADO</th> 
<th>ACCIONES</th> 
</tr> 
</thead> 
<tbody> 
<?php                  
                        $query = 'SELECT e.EMPLOYEE_ID, e.FIRST_NAME, e.LAST_NAME, e.GENDER, e.EMAIL, e.PHONE_NUMBER, e.JOB_ID, e.E_ESTADO, j.JOB_TITLE
                        FROM employee e
                        JOIN job j ON e.JOB_ID = j.JOB_ID
                        WHERE e.JOB_ID = 3';
                        $result = mysqli_query($db, $query) or die(mysqli_error($db));
                        
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>';
                            echo '<td>' . $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'] . '</td>';
                            echo '<td>' . $row['EMAIL'] . '</td>';
                            echo '<td>' . $row['JOB_TITLE'] . '</td>';
                            echo '<td align="right">';
                            if ($row['E_ESTADO'] == 1) {
                                echo '<span class="badge badge-success">Activo</span>';
                            } else {
                                echo '<span class="badge badge-danger">Inactivo</span>';
                            }
                            echo '</td>';
                            echo '<td align="right">
                                <div class="btn-group">
                                    <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="sa_personal_searchfrm.php?action=edit&id=' . $row['EMPLOYEE_ID'] . '">
                                        <i class="fas fa-fw fa-list-alt"></i> Detalles
                                    </a>
                                    </div>
                                    <div class="btn-group">
                                    <a type="button" class="btn btn-warning bg-gradient-warning btn-sm" href="sa_personal_edit.php?action=edit&id=' . $row['EMPLOYEE_ID'] . '">
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

<?php
include'../includes/footer_superadmin.php';

$sql = "SELECT EMPLOYEE_ID, FIRST_NAME, LAST_NAME, j.JOB_TITLE
        FROM employee e
        JOIN job j ON j.JOB_ID=e.JOB_ID
        order by e.LAST_NAME asc";
$res = mysqli_query($db, $sql) or die ("Bad SQL: $sql");

$opt = "<select class='form-control' name='empid' required>
        <option value='' disabled selected hidden>Seleccionar empleado</option>";
  while ($row = mysqli_fetch_assoc($res)) {
    $opt .= "<option value='".$row['EMPLOYEE_ID']."'>".$row['LAST_NAME'].', '.$row['FIRST_NAME'].' - '.$row['JOB_TITLE']."</option>";
  }
$opt .= "</select>";
?>



  <!-- <script>
function confirmDelete(employeeId) {
    if (confirm("¿Estás seguro de que deseas eliminar este registro?")) {
        window.location.href = "sa_personal_eliminar.php?id=" + employeeId;
    }
}
</script> -->
<script>
function confirmDelete(employeeId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Deseas eliminar este empleado?",
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
            // Realizar la solicitud AJAX para eliminar
            $.ajax({
                url: 'sa_personal_eliminar.php',
                type: 'POST',
                data: {id: employeeId},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: 'El registro ha sido eliminado.',
                            icon: 'success',
                            customClass: {
                                popup: 'custom-popup-class',
                                title: 'custom-title-class',
                                confirmButton: 'custom-confirm-button-class'
                            }
                        }).then(() => {
                            // Recargar la página o actualizar la tabla
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudo eliminar el registro.',
                            icon: 'error',
                            customClass: {
                                popup: 'custom-popup-class',
                                title: 'custom-title-class',
                                confirmButton: 'custom-confirm-button-class'
                            }
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un problema con la solicitud.',
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
