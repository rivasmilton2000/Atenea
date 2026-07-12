<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

if ((string) ($_SESSION['TYPE'] ?? '') !== 'SuperAdmin') {
    atenea_render_auth_alert(
        'warning',
        'Acceso restringido',
        'Solo SuperAdmin puede editar cuentas de estudiantes.',
        atenea_dashboard_route_for_session()
    );
}

if (!function_exists('sa_student_edit_h')) {
    function sa_student_edit_h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$userId = (int) ($_GET['id'] ?? 0);
if ($userId <= 0) {
    atenea_render_auth_alert(
        'warning',
        'Cuenta no encontrada',
        'No se recibio un identificador valido para la cuenta del estudiante.',
        'sa_cuentas_usuarios.php'
    );
}

$stmt = $db->prepare(
    'SELECT u.ID, u.ESTUDIANTE_ID, u.USERNAME, u.TYPE_ID, u.U_ESTADO,
            e.nombres_estudiante, e.apellidos_estudiante, e.carnet_estudiante
     FROM users u
     INNER JOIN estudiantes e ON e.ESTUDIANTE_ID = u.ESTUDIANTE_ID
     WHERE u.ID = ? AND u.TYPE_ID = 3
     LIMIT 1'
);

if (!$stmt) {
    atenea_render_auth_alert(
        'error',
        'Edicion no disponible',
        'No fue posible preparar la consulta de la cuenta del estudiante.',
        'sa_cuentas_usuarios.php'
    );
}

$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
if ($result instanceof mysqli_result) {
    mysqli_free_result($result);
}
$stmt->close();

if (!$userData) {
    atenea_render_auth_alert(
        'warning',
        'Cuenta no encontrada',
        'La cuenta de estudiante solicitada no existe o ya no esta disponible.',
        'sa_cuentas_usuarios.php'
    );
}
?>

<center>
  <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
    <div class="card-header py-3">
      <h4 class="m-2 font-weight-bold text-primary">Editar cuenta de estudiante / usuario</h4>
    </div>
    <a type="button" class="btn btn-primary bg-gradient-primary btn-block" href="sa_cuentas_usuarios.php">
      <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
    </a>
    <div class="card-body">
      <form id="editStudentForm" role="form" method="post">
        <input type="hidden" name="id" value="<?php echo (int) $userData['ID']; ?>">
        <input type="hidden" name="estid" value="<?php echo (int) $userData['ESTUDIANTE_ID']; ?>">

        <div class="form-group">
          <input
            class="form-control"
            placeholder="Estudiante"
            name="estudiante"
            value="<?php echo sa_student_edit_h(trim((string) $userData['apellidos_estudiante'] . ', ' . (string) $userData['nombres_estudiante'] . ' - ' . (string) $userData['carnet_estudiante'])); ?>"
            readonly
          >
        </div>
        <div class="form-group">
          <input class="form-control" placeholder="Usuario" name="username" minlength="5" maxlength="70" value="<?php echo sa_student_edit_h($userData['USERNAME'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
          <input class="form-control" placeholder="Contrasena" name="password" minlength="8" maxlength="80" type="password" value="">
          <small class="form-text text-muted">Deja este campo vacio si no quieres cambiar la contrasena.</small>
        </div>
        <div class="form-group">
          <input class="form-control" placeholder="Confirmar contrasena" name="confirm_password" minlength="8" maxlength="80" type="password">
        </div>
        <div class="form-group">
          <input class="form-control" value="<?php echo sa_student_edit_h(atenea_role_label('Estudiante')); ?>" readonly>
          <input type="hidden" name="type" value="3">
        </div>
        <div class="form-group">
          <select class="form-control" name="estado" required>
            <option value="" disabled hidden>Estado del registro</option>
            <option value="1" <?php echo (int) $userData['U_ESTADO'] === 1 ? 'selected' : ''; ?>>Activo</option>
            <option value="0" <?php echo (int) $userData['U_ESTADO'] === 0 ? 'selected' : ''; ?>>Inactivo</option>
          </select>
        </div>
        <hr>
        <button type="submit" class="btn btn-warning btn-block"><i class="fa fa-edit fa-fw"></i> Actualizar</button>
      </form>
    </div>
  </div>
</center>

<script>
document.getElementById('editStudentForm').addEventListener('submit', function (event) {
  event.preventDefault();

  var password = document.getElementsByName('password')[0].value;
  var confirmPassword = document.getElementsByName('confirm_password')[0].value;

  if (password !== confirmPassword) {
    Swal.fire({
      icon: 'error',
      title: 'Contrasenas distintas',
      text: 'Las contrasenas deben coincidir antes de guardar.'
    });
    return;
  }

  var formData = new FormData(this);
  fetch('sa_cuentas_usuarios_edit4.php', {
    method: 'POST',
    body: formData
  })
    .then(function (response) { return response.json(); })
    .then(function (data) {
      Swal.fire({
        icon: data.status === 'success' ? 'success' : (data.status === 'warning' ? 'warning' : 'error'),
        title: data.status === 'success' ? 'Operacion completada' : (data.status === 'warning' ? 'Sin cambios' : 'No fue posible actualizar'),
        text: data.message
      }).then(function () {
        if (data.status === 'success') {
          window.location = 'sa_cuentas_usuarios.php';
        }
      });
    })
    .catch(function () {
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
