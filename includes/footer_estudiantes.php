<?php
// session.php
session_start();
include('db_connection.php');

$user_check = $_SESSION['login_user'];

$ses_sql = mysqli_query($db, "SELECT nombres_estudiante, apellidos_estudiante, direccion_estudiante FROM estudiantes WHERE correo_estudiante = '$user_check'");

$row = mysqli_fetch_array($ses_sql, MYSQLI_ASSOC);

$_SESSION['nombres_estudiante'] = $row['nombres_estudiante'];
$_SESSION['apellidos_estudiante'] = $row['apellidos_estudiante'];
$_SESSION['direccion_estudiante'] = $row['direccion_estudiante'];

if (!isset($_SESSION['login_user'])) {
    header("location:login.php");
    die();
}
?>

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

<?php
// logout.php
session_start();
if (session_destroy()) {
    header("Location: login.php");
}
?>
<!-- Overlay para Perfil -->
<div id="overlay" onclick="off()">
  <div id="text">
    Soy <?php echo $_SESSION['nombres_estudiante'] . ' ' . $_SESSION['apellidos_estudiante']; ?><br>
    de <?php echo $_SESSION['direccion_estudiante']; ?>
  </div>
</div>
<script>
    function on() {
  document.getElementById("overlay").style.display = "block";
}

function off() {
  document.getElementById("overlay").style.display = "none";
}

function isNumberKey(evt) {
  var charCode = (evt.which) ? evt.which : evt.keyCode;
  if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57))
    return false;
  return true;
}
</script>
<!-- Modal para Editar Información del Estudiante -->
<div class="modal fade" id="settingsModal" tabindex="-1" role="dialog" aria-labelledby="settingsModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="settingsModalLabel">Editar Información del Estudiante</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <form action="update_student.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <!-- Información del Estudiante -->
          <div class="form-group">
            <label for="nombres_estudiante">Nombres</label>
            <input type="text" class="form-control" id="nombres_estudiante" name="nombres_estudiante" required>
          </div>
          <div class="form-group">
            <label for="apellidos_estudiante">Apellidos</label>
            <input type="text" class="form-control" id="apellidos_estudiante" name="apellidos_estudiante" required>
          </div>
          <div class="form-group">
            <label for="direccion_estudiante">Dirección</label>
            <input type="text" class="form-control" id="direccion_estudiante" name="direccion_estudiante" required>
          </div>
          <div class="form-group">
            <label for="correo_estudiante">Correo</label>
            <input type="email" class="form-control" id="correo_estudiante" name="correo_estudiante" required>
          </div>
          <div class="form-group">
          <div class="form-group">
            <label for="telefono_estudiante">Teléfono</label>
            <input type="text" class="form-control" id="telefono_estudiante" name="telefono_estudiante" onkeypress="return isNumberKey(event)" required>
          </div>
          <div class="form-group">
            <label for="foto_estudiante">Foto</label>
            <input type="file" class="form-control-file" id="foto_estudiante" name="foto_estudiante">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>