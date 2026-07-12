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

// JOB SELECT OPTION TAB
$sql = "SELECT DISTINCT JOB_TITLE, JOB_ID FROM job";
$result = mysqli_query($db, $sql) or die ("Bad SQL: $sql");

$opt = "<select class='form-control' name='type'>";
while ($row = mysqli_fetch_assoc($result)) {
    $opt .= "<option value='".$row['JOB_ID']."'>".$row['JOB_TITLE']."</option>";
}
$opt .= "</select>";

$id = $_GET['id'];

$query = "SELECT e.EMPLOYEE_ID, e.FIRST_NAME, e.LAST_NAME, e.GENDER, e.EMAIL, e.PHONE_NUMBER, j.JOB_TITLE, e.HIRED_DATE, t.TYPE, l.PROVINCE, l.CITY, e.LOCATION_ID
          FROM employee e
          LEFT JOIN users u ON u.EMPLOYEE_ID = e.EMPLOYEE_ID
          LEFT JOIN job j ON e.JOB_ID = j.JOB_ID
          LEFT JOIN location l ON e.LOCATION_ID = l.LOCATION_ID
          LEFT JOIN type t ON u.TYPE_ID = t.TYPE_ID
          WHERE e.EMPLOYEE_ID = ?";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_array($result)) {
    $zz = $row['EMPLOYEE_ID'];
    $a = $row['FIRST_NAME'];
    $b = $row['LAST_NAME'];
    $c = $row['GENDER'];
    $f = $row['EMAIL'];
    $g = $row['PHONE_NUMBER'];
    $h = $row['JOB_TITLE'];
    $i = $row['HIRED_DATE'];
    $j = $row['PROVINCE'];
    $k = $row['CITY'];
    $l = $row['TYPE'];

    // Convertir la fecha al formato DD-MM-YYYY
    $i = date("d-m-Y", strtotime($i));

    $province_name = $row['PROVINCE'];
    $city_name = $row['CITY'];
}

$id = $_GET['id'];

// Obtener opciones de ubicación
$location_query = "SELECT LOCATION_ID, PROVINCE, CITY FROM location";
$location_result = mysqli_query($db, $location_query);

?>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
            <div class="card-header py-3">
              <h4 class="m-2 font-weight-bold text-primary">Editar cuenta de personal</h4>
            </div><a  type="button" class="btn btn-primary bg-gradient-primary btn-block" href="personal.php?"> <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar </a>
            <div class="card-body">
      

            <form role="form" method="post" action="personal_edit1.php">
              <input type="hidden" name="id" value="<?php echo $zz; ?>" />

              <div class="form-group row text-left text-warning">
                <div class="col-sm-3" style="padding-top: 5px;">
                 Nombres:
                </div>
                <div class="col-sm-9">
                  <input class="form-control" placeholder="Nombres" minlength="3" maxlength="40" name="firstname" value="<?php echo $a; ?>" required>
                </div>
              </div>
              <div class="form-group row text-left text-warning">
                <div class="col-sm-3" style="padding-top: 5px;">
                 Apellidos:
                </div>
                <div class="col-sm-9">
                  <input class="form-control" placeholder="Apellidos" minlength="3" maxlength="49" name="lastname" value="<?php echo $b; ?>" required>
                </div>
              </div>

              <div class="form-group row text-left text-warning">
                <div class="col-sm-3" style="padding-top: 5px;">
                Género:
                </div>
                <div class="col-sm-9">
                    <select class='form-control' name='gender' required>
                    <option value="" disabled selected hidden>Seleccionar genéro</option>
                    <option value="Hombre" <?php if($c == 'Hombre') echo 'selected'; ?>>Hombre</option>
                    <option value="Mujer" <?php if($c == 'Mujer') echo 'selected'; ?>>Mujer</option>
                    </select>
                </div>
                </div>
             
              <div class="form-group row text-left text-warning">
                <div class="col-sm-3" style="padding-top: 5px;">
                 Correo electrónico:
                </div>
                <div class="col-sm-9">
                  <input class="form-control" placeholder="Correo electrónico" minlength="10" maxlength="55" name="email" value="<?php echo $f; ?>" required>
                </div>
              </div>

              <div class="form-group row text-left text-warning">
                <div class="col-sm-3" style="padding-top: 5px;">
                Contacto teléfonico:
                </div>
                <div class="col-sm-9">
                   <input class="form-control" placeholder="Contacto teléfonico" id="phonenumber" name="phone" value="<?php echo $g; ?>" required>
                </div>
              </div>

              <div class="form-group row text-left text-warning">
    <div class="col-sm-3" style="padding-top: 5px;">
        Municipio:
    </div>
    <div class="col-sm-9">
        <select class="form-control" name="city" onchange="updateProvince(this)" required>
            <option value="" disabled selected hidden>Seleccionar municipio</option>
            <?php
            mysqli_data_seek($location_result, 0); // Reiniciar el puntero del resultado
            while ($location_row = mysqli_fetch_assoc($location_result)) {
                $selected = ($location_row['CITY'] == $city_name) ? 'selected' : '';
                echo '<option value="' . $location_row['LOCATION_ID'] . '" data-province="' . $location_row['PROVINCE'] . '" ' . $selected . '>' . $location_row['CITY'] . '</option>';
            }
            ?>
        </select>
    </div>
</div>

<div class="form-group row text-left text-warning">
    <div class="col-sm-3" style="padding-top: 5px;">
        Departamento:
    </div>
    <div class="col-sm-9">
        <input class="form-control" name="province" readonly value="<?php echo $province_name; ?>">
    </div>
</div>

<script>
function updateProvince(selectElement) {
    var selectedOption = selectElement.options[selectElement.selectedIndex];
    var provinceInput = document.querySelector('input[name="province"]');
    provinceInput.value = selectedOption.getAttribute('data-province');
}
</script>

              <div class="form-group row text-left text-warning">
                <div class="col-sm-3" style="padding-top: 5px;">
                 Rol:
                </div>
                <div class="col-sm-9">
                  <input class="form-control" placeholder="Rol" name="role" value="<?php echo $h; ?>" readonly>
                </div>
              </div>
              <div class="form-group row text-left text-warning">
                <div class="col-sm-3" style="padding-top: 5px;">
                Fecha de contratación:
                </div>
                <div class="col-sm-9">
                <input type="text" class="form-control" name="hireddate" value="<?php echo $i; ?>" readonly>
                </div>
              </div>
              
              <hr>

                <button type="submit" class="btn btn-warning btn-block"><i class="fa fa-edit fa-fw"></i>Actualizar</button>    
              </form>  
            </div>
          </div></center>
          <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Script de validación del teléfono -->
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
<?php
include'../includes/footer.php';
?>
