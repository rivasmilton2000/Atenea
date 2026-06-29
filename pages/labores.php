<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';
  
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

$sql = "SELECT DISTINCT FIRST_NAME, LAST_NAME, EMPLOYEE_ID FROM employee order by FIRST_NAME asc";
$result = mysqli_query($db, $sql) or die ("Bad SQL: $sql");

$emp = "<select class='form-control' name='employee_jobs' required>
        <option disabled selected hidden>Seleccionar empleado encargado</option>";
  while ($row = mysqli_fetch_assoc($result)) {
    $emp .= "<option value='".$row['EMPLOYEE_ID']."'>".$row['FIRST_NAME']." ".$row['LAST_NAME']."</option>";
  }

$emp .= "</select>";

$sql2 = "SELECT DISTINCT EMPLOYEE_ID, FIRST_NAME, LAST_NAME FROM employee order by EMPLOYEE_ID asc";
$result2 = mysqli_query($db, $sql2) or die ("Bad SQL: $sql2");

$sup = "<select class='form-control' name='employee' required>
        <option disabled selected hidden>Seleccionar Empleado</option>";
  while ($row = mysqli_fetch_assoc($result2)) {
    $sup .= "<option value='".$row['EMPLOYEE_ID']."'>".$row['FIRST_NAME']."</option>";
  }

$sup .= "</select>";
?>
            
            <div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Labores&nbsp;<a href="#" data-toggle="modal" data-target="#jModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;"><i class="fas fa-fw fa-plus"></i></a></h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"> 
                <thead>
                    <tr>
                        <th>LABOR</th>
                        <th>ENCARGADO</th>
                        <th>ESTADO DEL LABOR</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>

                <?php
                $query = 'SELECT j.id, j.job, j.description, j.status, j.hour, j.date, j.maxhour, j.maxdate, e.FIRST_NAME, e.LAST_NAME
                          FROM jobs j
                          JOIN employee e ON j.employee = e.EMPLOYEE_ID
                          WHERE j.j_estado = 1
                          GROUP BY j.id';
                $result = mysqli_query($db, $query) or die(mysqli_error($db));

                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<tr>';
                    echo '<td>' . $row['job'] . '</td>';
                    echo '<td>' . $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'] . '</td>';
                    
                    // Modificación para el campo status
    echo '<td>';
    switch ($row['status']) {
      case 'Completado':
        echo '<span style="color: green;">Completado</span>';
        break;
      case 'Incompleto':
        echo '<span style="color: orange;">Incompleto</span>';
        break;
      case 'Tiempo Excedido':
        echo '<span style="color: red;">Tiempo Excedido</span>';
        break;
      default:
        echo $row['status'];
    }
    echo '</td>';
    
                    echo '<td align="right">
                            <div class="btn-group">
                                <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="labores_searchfrm.php?action=edit&id=' . $row['id'] . '"><i class="fas fa-fw fa-list-alt"></i> Detalles</a>
                            </div>
                            <div class="btn-group">
                                <a type="button" class="btn btn-danger bg-gradient-danger btn-sm" href="#" onclick="confirmDelete(' . $row['id'] . ')"><i class="fas fa-fw fa-trash"></i>Eliminar</a>
                            </div>
                          </td>';
                    echo '</tr>';
                }
                ?>

                <!-- <script>
                function confirmDelete(jobsId) {
                    if (confirm("¿Estás seguro de que quieres eliminar este registro?")) {
                        window.location.href = 'labores_delete.php?id=' + jobsId;
                    }
                }
                </script> -->
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(jobsId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas eliminar este labor?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'custom-confirm-button-class',
                cancelButton: 'custom-cancel-button-class'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`labores_delete.php?id=${jobsId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Eliminado',
                                text: 'El labor ha sido eliminado.',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: ''
                                }
                            }).then(() => {
                                window.location.href = 'labores.php?delete=success';
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message,
                                icon: 'error',
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'custom-confirm-button-class'
                                }
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error',
                            text: 'Ha ocurrido un error al intentar eliminar el labor.',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'custom-confirm-button-class'
                            }
                        });
                    });
            }
        });
    }
</script>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include'../includes/footer.php';
?>


<!-- Product Modal -->
<div class="modal fade" id="jModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ingresar labor</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
  <form role="form" method="post" action="labores_transac.php?action=add">
  <div class="form-group">
  <label for="employee_jobs">Empleado:</label>
  <select class="form-control" name="employee_jobs" required>
    <option value="">Seleccione un empleado</option>
    <?php
      $sql = "SELECT e.EMPLOYEE_ID, CONCAT(e.FIRST_NAME, ' ', e.LAST_NAME, ' - ', j.JOB_TITLE) AS EMPLOYEE_NAME
              FROM employee e
              INNER JOIN job j ON e.JOB_ID = j.JOB_ID
              WHERE e.JOB_ID IN (2, 3) AND e.E_ESTADO = 1
              ORDER BY e.LAST_NAME, e.FIRST_NAME";
      $result = mysqli_query($db, $sql);
      while ($row = mysqli_fetch_assoc($result)) {
        echo '<option value="' . $row['EMPLOYEE_ID'] . '">' . $row['EMPLOYEE_NAME'] . '</option>';
      }
    ?>
  </select>
</div>
    <div class="form-group">
      <!-- Campos existentes -->
      <label for="job_jobs">Labor:</label>
      <input class="form-control" placeholder="Labor" name="job_jobs" minlength="5" maxlength="80" required>
    </div>
    <div class="form-group">
      <label for="description_jobs">Descripción del labor:</label>
      <textarea class="form-control" placeholder="Descripción del labor" minlength="5" maxlength="400" name="description_jobs" required></textarea>
    </div>
    <div class="form-group">
      <label for="maxdate_jobs">Fecha máxima de finalizar:</label>
      <input type="date" class="form-control" name="maxdate_jobs" required>
    </div>
    <div class="form-group">
      <?php date_default_timezone_set('America/El_Salvador'); ?>
      <label for="maxhour_jobs">Hora máxima de finalizar:</label>
      <input type="time" value="00:00" class="form-control" name="maxhour_jobs" required>
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
<style>
.custom-confirm-button-class {
    font-family: 'Open Sans', sans-serif !important;
    font-weight: 600 !important;
    background-color: #3085d6 !important;
    color: white !important;
}
.custom-cancel-button-class {
    font-family: 'Open Sans', sans-serif !important;
    font-weight: 600 !important;
    background-color: red !important;
    color: white !important;
}
</style>