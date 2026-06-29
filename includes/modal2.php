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
  
  // Mostrar solo las ciudades de El Salvador
  $.showCities("#city", "El Salvador");

  // ------------------
  // additional methods 
  // -------------------

  // will return all provinces 
  console.log($.getProvinces());
  
  // will return all cities 
  console.log($.getAllCities());
  
  // will return all cities under specific province (e.g El Salvador)
  console.log($.getCities("El Salvador")); 
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

  <!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Cerrar sesión</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">¿Deseas cerrar la sesión?</div>
      <div class="modal-footer">
        <button class="btn btn-danger" type="button" data-dismiss="modal">
          <i class="fas fa-times"></i> Cancelar
        </button>
        <a class="btn btn-primary" href="logout.php">
          <i class="fas fa-sign-out-alt"></i> Cerrar sesión
        </a>
      </div>
    </div>
  </div>
</div>

  <!-- Customer Modal-->
  <div class="modal fade" id="customerModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ingresar cliente</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <form role="form" method="post" action="cust_transac.php?action=add">
            <div class="form-group">
              <input class="form-control" placeholder="Nombre de la empresa" name="firstname" required>
            </div>
            <div class="form-group">
              <input class="form-control" placeholder="Dirección de la empresa" name="lastname" required>
            </div>
            <div class="form-group">
              <input class="form-control" placeholder="Número de teléfono de la empresa" name="phonenumber" required>
            </div>
            <hr>
            <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Guardar</button>
            <button type="reset" class="btn btn-danger"><i class="fa fa-times fa-fw"></i>Reiniciar</button>
            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>      
          </form>  
        </div>
      </div>
    </div>
  </div>
  <!-- Customer Modal-->
  <div class="modal fade" id="poscustomerModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ingresar cliente</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <form role="form" method="post" action="cust_pos_trans.php?action=add">
            <div class="form-group">
              <input class="form-control" placeholder="Nombre de la empresa" name="firstname" required>
            </div>
            <div class="form-group">
              <input class="form-control" placeholder="Dirección de la empresa" name="lastname" required>
            </div>
            <div class="form-group">
              <input class="form-control" placeholder="Número de teléfono de la empresa" name="phonenumber" required>
            </div>
            <hr>
            <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Guardar</button>
            <button type="reset" class="btn btn-danger"><i class="fa fa-times fa-fw"></i>Reiniciar</button>
            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>      
          </form>  
        </div>
      </div>
    </div>
  </div>
  
  <!-- Employee Modal-->
<div class="modal fade" id="employeeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Ingresar personal</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form role="form" method="post" action="sa_personal_transac.php?action=add">
              <div class="form-group">
                <input class="form-control" placeholder="Nombres" name="firstname" minlength="3" maxlength="40" required>
              </div>
              <div class="form-group">
                <input class="form-control" placeholder="Apellidos" name="lastname" minlength="3" maxlength="40" required>
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
            <select class="form-control" name="jobs" required>
              <option value="" disabled selected hidden>Seleccionar rol</option>
              <option value="2">Administrativo</option>
              <option value="3">Logistica</option>
            </select>
          </div>

          <div class="form-group">
            <select class="form-control" name="status" required>
              <option value="" disabled selected hidden>Seleccionar estado</option>
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

<!-- Logistica Modal-->
<div class="modal fade" id="logisticModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Ingresar personal administrativo</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form role="form" method="post" action="personal_transac.php?action=add">   
              <div class="form-group">
                <input class="form-control" placeholder="Nombres" name="firstname" required>
              </div>
              <div class="form-group">
                <input class="form-control" placeholder="Apellidos" name="lastname" required>
              </div>
              <div class="form-group">
                  <select class='form-control' name='gender' required>
                    <option value="" disabled selected hidden>Seleccionar género</option>
                    <option value="Hombre">Hombre</option>
                    <option value="Mujer">Mujer</option>
                  </select>
              </div>
              <div class="form-group">
                <input class="form-control" placeholder="Correo electrónico" name="email" required>
              </div>
              <div class="form-group">
                <input class="form-control" placeholder="Número de teléfono" name="phonenumber" required>
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
            <input type="hidden" name="jobs" value="2">
            <input type="text" class="form-control" value="<?php echo $job_title; ?>" readonly>
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



  <!-- Delete Modal-->
  <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="DeleteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Confirmar eliminar</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">¿Estás seguro de que quieres eliminar?</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
          <a class="btn btn-danger btn-ok">Eliminar</a>
        </div>
      </div>
    </div>
  </div>
    <script>
        $('#confirm-delete').on('show.bs.modal', function(e) {
            $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            
            $('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
        });
    </script>
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