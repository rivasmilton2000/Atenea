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
$query = "SELECT u.ID, u.ESTUDIANTE_ID, u.USERNAME, u.PASSWORD, u.TYPE_ID, u.U_ESTADO,
          e.nombres_estudiante, e.apellidos_estudiante, e.carnet_estudiante
          FROM users u
          JOIN estudiantes e ON u.ESTUDIANTE_ID = e.ESTUDIANTE_ID
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
            <form role="form" method="post" action="sa_cuentas_usuarios_edit4.php">
                <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>" />
                <input type="hidden" name="estid" value="<?php echo $user_data['ESTUDIANTE_ID']; ?>" />

                <div class="form-group">
                    <input class="form-control" placeholder="Estudiante" name="estudiante" value="<?php echo $user_data['apellidos_estudiante'] . ', ' . $user_data['nombres_estudiante'] . ' - ' . $user_data['carnet_estudiante']; ?>" readonly>
                </div>
                <div class="form-group">
                    <input class="form-control" placeholder="Usuario" name="username" value="<?php echo $user_data['USERNAME']; ?>" required>
                </div>
                <div class="form-group">
                    <input class="form-control" placeholder="Contraseña" name="password" type="password" value="">
                    <small class="form-text text-muted">Deje en blanco para mantener la contraseña actual.</small>
                </div>
                <div class="form-group">
    <input class="form-control" placeholder="Confirmar contraseña" name="confirm_password" type="password">
</div>
                <div class="form-group">
                    <input class="form-control" value="Estudiante" readonly>
                    <input type="hidden" name="type" value="3">
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
<script>
document.querySelector('form').addEventListener('submit', function (e) {
    e.preventDefault(); // Evitar el envío del formulario

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

    fetch('sa_cuentas_usuarios_edit4.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: data.message,
            }).then((result) => {
                if (result.isConfirmed || result.isDismissed) {
                    window.location = 'sa_cuentas_usuarios.php';
                }
            });
        } else if (data.status === 'error') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        } else if (data.status === 'warning') {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un problema al procesar la solicitud.'
        });
    });
});
</script>
<?php
include '../includes/footer_superadmin.php';
?>