<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

if ((string) ($_SESSION['TYPE'] ?? '') !== 'SuperAdmin') {
    atenea_render_auth_alert(
        'warning',
        'Acceso restringido',
        'Solo SuperAdmin puede consultar detalles de cuentas internas.',
        atenea_dashboard_route_for_session()
    );
}

if (!function_exists('sa_user_detail_h')) {
    function sa_user_detail_h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$userId = (int) ($_GET['id'] ?? 0);
if ($userId <= 0) {
    atenea_render_auth_alert(
        'warning',
        'Cuenta no encontrada',
        'No se recibio un identificador valido para la cuenta interna.',
        'sa_cuentas_usuarios.php'
    );
}

$stmt = $db->prepare(
    'SELECT u.ID, u.USERNAME, u.U_ESTADO, t.TYPE,
            e.FIRST_NAME, e.LAST_NAME, e.EMAIL, e.PHONE_NUMBER,
            j.JOB_TITLE, l.PROVINCE, l.CITY
     FROM users u
     INNER JOIN employee e ON e.EMPLOYEE_ID = u.EMPLOYEE_ID
     INNER JOIN job j ON j.JOB_ID = e.JOB_ID
     LEFT JOIN location l ON l.LOCATION_ID = e.LOCATION_ID
     INNER JOIN type t ON t.TYPE_ID = u.TYPE_ID
     WHERE u.ID = ? AND u.TYPE_ID IN (1, 2, 4)
     LIMIT 1'
);

if (!$stmt) {
    atenea_render_auth_alert(
        'error',
        'Detalle no disponible',
        'No fue posible preparar la consulta de la cuenta interna.',
        'sa_cuentas_usuarios.php'
    );
}

$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
if ($result instanceof mysqli_result) {
    mysqli_free_result($result);
}
$stmt->close();

if (!$user) {
    atenea_render_auth_alert(
        'warning',
        'Cuenta no encontrada',
        'La cuenta interna solicitada no existe o ya no puede consultarse.',
        'sa_cuentas_usuarios.php'
    );
}
?>

<center>
  <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
    <div class="card-header py-3">
      <h4 class="m-2 font-weight-bold text-primary">Detalle de cuenta interna</h4>
    </div>
    <a href="sa_cuentas_usuarios.php" type="button" class="btn btn-primary bg-gradient-primary">
      <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
    </a>
    <div class="card-body">
      <h5 class="font-weight-bold text-primary mb-3">Cuenta</h5>
      <hr>
      <div class="form-group row text-left">
        <div class="col-sm-3 text-primary"><h5>Usuario</h5></div>
        <div class="col-sm-9"><h5>: <?php echo sa_user_detail_h($user['USERNAME'] ?? ''); ?></h5></div>
      </div>
      <div class="form-group row text-left">
        <div class="col-sm-3 text-primary"><h5>Rol</h5></div>
        <div class="col-sm-9"><h5>: <?php echo sa_user_detail_h(atenea_role_label((string) ($user['TYPE'] ?? ''))); ?></h5></div>
      </div>

      <h5 class="font-weight-bold text-primary mb-3 mt-4">Persona asociada</h5>
      <hr>
      <div class="form-group row text-left">
        <div class="col-sm-3 text-primary"><h5>Nombre completo</h5></div>
        <div class="col-sm-9"><h5>: <?php echo sa_user_detail_h(trim((string) $user['FIRST_NAME'] . ' ' . (string) $user['LAST_NAME'])); ?></h5></div>
      </div>
      <div class="form-group row text-left">
        <div class="col-sm-3 text-primary"><h5>Correo</h5></div>
        <div class="col-sm-9"><h5>: <?php echo sa_user_detail_h($user['EMAIL'] ?? 'No disponible'); ?></h5></div>
      </div>
      <div class="form-group row text-left">
        <div class="col-sm-3 text-primary"><h5>Telefono</h5></div>
        <div class="col-sm-9"><h5>: <?php echo sa_user_detail_h($user['PHONE_NUMBER'] ?? 'No disponible'); ?></h5></div>
      </div>
      <div class="form-group row text-left">
        <div class="col-sm-3 text-primary"><h5>Cargo</h5></div>
        <div class="col-sm-9"><h5>: <?php echo sa_user_detail_h($user['JOB_TITLE'] ?? 'No disponible'); ?></h5></div>
      </div>
      <div class="form-group row text-left">
        <div class="col-sm-3 text-primary"><h5>Ubicacion</h5></div>
        <div class="col-sm-9">
          <h5>: <?php echo sa_user_detail_h(trim((string) ($user['PROVINCE'] ?? '') . ' ' . (string) ($user['CITY'] ?? '')) ?: 'No disponible'); ?></h5>
        </div>
      </div>

      <h5 class="font-weight-bold text-primary mb-3 mt-4">Estado</h5>
      <hr>
      <div class="form-group row text-left">
        <div class="col-sm-3 text-primary"><h5>Estado</h5></div>
        <div class="col-sm-9">
          <h5>:
            <?php if ((int) ($user['U_ESTADO'] ?? 0) === 1): ?>
              <span class="badge badge-success">Activo</span>
            <?php else: ?>
              <span class="badge badge-danger">Inactivo</span>
            <?php endif; ?>
          </h5>
        </div>
      </div>
    </div>
  </div>
</center>

<?php
include '../includes/footer_superadmin.php';
