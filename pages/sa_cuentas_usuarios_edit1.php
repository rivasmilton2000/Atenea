<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

// Sección de verificación de permisos de usuario
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Personal' || $Aa == 'Estudiante' || $Aa == 'Docente' || $Aa == 'Admin') {
        if ($Aa == 'Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa == 'Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa == 'Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa == 'Admin') {
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
  
<?php
// Obtener datos del usuario a editar
$query = "SELECT u.ID, u.EMPLOYEE_ID, u.USERNAME, u.PASSWORD, u.TYPE_ID, u.U_ESTADO,
          e.FIRST_NAME, e.LAST_NAME, j.JOB_TITLE
          FROM users u
          JOIN employee e ON u.EMPLOYEE_ID = e.EMPLOYEE_ID
          JOIN job j ON j.JOB_ID = e.JOB_ID
          WHERE u.ID = " . $_GET['id'];
$result = mysqli_query($db, $query) or die(mysqli_error($db));
$user_data = mysqli_fetch_assoc($result);
?>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Editar cuenta de usuario</h4>
        </div>
        <a type="button" class="btn btn-primary bg-gradient-primary btn-block" href="sa_cuentas_usuarios.php"> <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar </a>
        <div class="card-body">
            <form id="editUserForm" role="form" method="post">
                <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>" />
                <input type="hidden" name="empid" value="<?php echo $user_data['EMPLOYEE_ID']; ?>" />

                <div class="form-group">
                    <input class="form-control" placeholder="Empleado" name="employee" value="<?php echo $user_data['FIRST_NAME'] . ' ' . $user_data['LAST_NAME'] . ' - ' . $user_data['JOB_TITLE']; ?>" readonly>
                </div>
                <div class="form-group">
                    <input class="form-control" minlength="5" maxlength="70" placeholder="Usuario" name="username" value="<?php echo $user_data['USERNAME']; ?>" required>
                </div>
                <div class="form-group">
                    <input class="form-control" minlength="8" maxlength="80" placeholder="Contraseña" name="password" type="password" value="">
                    <small class="form-text text-muted">Deje en blanco para mantener la contraseña actual.</small>
                </div>
                <div class="form-group">
                    <input class="form-control" minlength="8" maxlength="80" placeholder="Confirmar contraseña" name="confirm_password" type="password" value="">
                </div>
                <div class="form-group">
                    <select class="form-control" name="type" required>
                        <option value="" disabled selected hidden>Tipo de cuenta</option>
                        <option value="1" <?php echo ($user_data['TYPE_ID'] == 1) ? 'selected' : ''; ?>>Administrador</option>
                        <option value="2" <?php echo ($user_data['TYPE_ID'] == 2) ? 'selected' : ''; ?>>Personal</option>
                        <option value="4" <?php echo ($user_data['TYPE_ID'] == 4) ? 'selected' : ''; ?>>Docente</option>
                    </select>
                </div>
                <div class="form-group">
                    <select class="form-control" name="estado" required>
                        <option value="" disabled selected hidden>Estado del registro</option>
                        <option value="1" <?php echo ($user_data['U_ESTADO'] == 1) ? 'selected' : ''; ?>>Activo</option>
                        <option value="0" <?php echo ($user_data['U_ESTADO'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                <hr>
                <button type="submit" class="btn btn-warning btn-block"><i class="fa fa-edit fa-fw"></i>Actualizar</button>
            </form>  
        </div>
    </div>
</center>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
document.getElementById('editUserForm').addEventListener('submit', function(event) {
    event.preventDefault();

    var password = document.getElementsByName('password')[0].value;
    var confirmPassword = document.getElementsByName('confirm_password')[0].value;

    if (password !== confirmPassword) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Las contraseñas no coinciden. Por favor, inténtalo de nuevo.'
        });
        return;
    }

    var formData = new FormData(this);
    fetch('sa_cuentas_usuarios_edit2.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        Swal.fire({
            icon: data.status === 'success' ? 'success' : (data.status === 'warning' ? 'warning' : 'error'),
            title: data.status === 'success' ? '¡Éxito!' : (data.status === 'warning' ? 'Advertencia' : 'Error'),
            text: data.message
        }).then(() => {
            if (data.status === 'success') {
                window.location = 'sa_cuentas_usuarios.php';
            }
        });
    });
});
</script>

<?php
include '../includes/footer_superadmin.php';
?>