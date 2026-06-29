<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';
?>

<?php 
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
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
        <h4 class="m-2 font-weight-bold text-primary">Docentes&nbsp; <a  href="#" data-toggle="modal" data-target="#teacherModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;"><i class="fas fa-fw fa-plus"></i></a></h4>
    </div>
    
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"> 
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
                        $query = "SELECT EMPLOYEE_ID, FIRST_NAME, LAST_NAME, EMAIL, j.JOB_TITLE 
                                  FROM employee e 
                                  JOIN job j ON e.JOB_ID=j.JOB_ID 
                                  WHERE e.JOB_ID = 1 AND e.E_ESTADO = 1";
                        $result = mysqli_query($db, $query) or die(mysqli_error($db));
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>';
                            echo '<td>' . $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'] . '</td>';
                            echo '<td>'. $row['EMAIL'].'</td>';
                            echo '<td>'. $row['JOB_TITLE'].'</td>';

                            echo '<td align="right"> 
                                    <div class="btn-group">
                                        <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="docentes_searchfrm.php?action=edit&id='.$row['EMPLOYEE_ID'].'"><i class="fas fa-fw fa-list-alt"></i> Detalles</a>
                                        </div> 
                                        <div class="btn-group">
                                        <a type="button" class="btn btn-warning bg-gradient-warning btn-sm" href="docentes_edit.php?action=edit&id='.$row['EMPLOYEE_ID'].'">
                                            <i class="fas fa-fw fa-edit"></i> Editar
                                        </a>
                                        </div> 
                                        <div class="btn-group">
                                        <button type="button" class="btn btn-danger bg-gradient-danger btn-sm" onclick="confirmDelete('.$row['EMPLOYEE_ID'].')">
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

<!-- Employee select and script -->
<?php
$sqlforjob = "SELECT DISTINCT JOB_TITLE, JOB_ID FROM job order by JOB_ID asc";
$result = mysqli_query($db, $sqlforjob) or die ("Bad SQL: $sqlforjob");

$job = "<select class='form-control' name='jobs' required>
        <option value='' disabled selected hidden>Seleccionar trabajo</option>";
while ($row = mysqli_fetch_assoc($result)) {
    $job .= "<option value='".$row['JOB_ID']."'>".$row['JOB_TITLE']."</option>";
}

$job .= "</select>";
?>
<script>  
window.onload = function() {  
  // ---------------
  // basic usage
  // ---------------
  var $ = new City();
  $.showProvinces("#province");
  $.showCities("#city");

  // ------------------
  // additional methods 
  // -------------------

  // will return all provinces 
  console.log($.getProvinces());
  
  // will return all cities 
  console.log($.getAllCities());
  
  // will return all cities under specific province (e.g Batangas)
  console.log($.getCities("Batangas")); 
  
}
</script>

<?php
$sqlforjob = "SELECT JOB_TITLE FROM job WHERE JOB_ID = 2";
$result = mysqli_query($db, $sqlforjob) or die ("Bad SQL: $sqlforjob");

$row = mysqli_fetch_assoc($result);
$job_title = $row['JOB_TITLE'];

$job = "<input type='text' class='form-control' name='jobs' value='".$job_title."' readonly>";
?>
<!-- end of Employee select and script -->

<!-- Employee Modal-->
<div class="modal fade" id="teacherModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Ingresar docente</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form role="form" method="post" action="docentes_transac.php?action=add">
          <div class="form-group">
            <input class="form-control" placeholder="Nombres" name="firstname" minlength="5" maxlength="40" required>
          </div>
          <div class="form-group">
            <input class="form-control" placeholder="Apellidos" name="lastname" minlength="5" maxlength="40" required>
          </div>
          <div class="form-group">
            <select class='form-control' name='gender' required>
              <option value="" disabled selected hidden>Seleccionar género</option>
              <option value="Hombre">Hombre</option>
              <option value="Mujer">Mujer</option>
            </select>
          </div>
          <div class="form-group">
            <input class="form-control" placeholder="Correo electrónico" name="email" minlength="10" maxlength="55" required>
          </div>
          <div class="form-group">
            <input class="form-control" placeholder="Número de teléfono" id="phonenumber" name="phonenumber" required>
          </div>
          <div class="form-group">
            <select class="form-control" id="province" placeholder="Provincia" name="province" required></select>
          </div>
          <div class="form-group">
            <select class="form-control" id="city" placeholder="Ciudad" name="city" required></select>
          </div>
          <div class="form-group">
            <input placeholder="Fecha de contratación" type="text" onfocus="(this.type='date')" onblur="(this.type='text')" id="FromDate" name="hireddate" class="form-control" />
          </div>
          <div class="form-group">
            <input type="hidden" name="jobs" value="1">
            <input class="form-control" value="Docente" readonly>
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

<!-- <script>
function confirmDelete(employeeId) {
    if (confirm("¿Estás seguro de que deseas eliminar este registro?")) {
        window.location.href = "docentes_eliminar.php?id=" + employeeId;
    }
}
</script> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(employeeId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Deseas eliminar este docente?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'docentes_eliminar.php',
                type: 'POST',
                data: {id: employeeId},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            '¡Docente eliminado!',
                            response.message,
                            'success'
                        ).then(() => {
                            window.location.href = 'docentes.php';
                        });
                    } else {
                        Swal.fire(
                            'Error',
                            response.message,
                            'error'
                        );
                    }
                },
                error: function() {
                    Swal.fire(
                        'Error',
                        'Hubo un problema con la solicitud',
                        'error'
                    );
                }
            });
        }
    });
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Script de validación de teléfono cargado");

    const phoneInput = document.querySelector('#phonenumber');

    if (phoneInput) {
        console.log("Input de teléfono encontrado");

        phoneInput.addEventListener('input', validatePhone);
        phoneInput.addEventListener('blur', validatePhone);
    } else {
        console.log("Input de teléfono no encontrado");
    }

    function validatePhone() {
        console.log("Validando teléfono:", this.value);

        let phoneValue = this.value;
        
        // Verificar si hay caracteres no permitidos
        if (/[^\d-]/.test(phoneValue)) {
            Swal.fire({
                title: 'Número de Teléfono Inválido',
                text: 'No se permiten letras o caracteres especiales en este campo.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            // Eliminar caracteres no permitidos
            phoneValue = phoneValue.replace(/[^\d-]/g, '');
        }

        // Eliminar guiones extras
        phoneValue = phoneValue.replace(/-+/g, '-');

        // Añadir guion después de los primeros 4 dígitos
        if (phoneValue.length > 4 && phoneValue.charAt(4) !== '-') {
            phoneValue = phoneValue.slice(0, 4) + '-' + phoneValue.slice(4);
        }

        // Limitar a 9 caracteres en total (incluyendo el guion)
        phoneValue = phoneValue.slice(0, 9);

        this.value = phoneValue;

        console.log("Número de teléfono formateado:", phoneValue);
    }
});

</script>