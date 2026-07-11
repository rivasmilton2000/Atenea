<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

if ((string) ($_SESSION['TYPE'] ?? '') !== 'SuperAdmin') {
    atenea_render_auth_alert(
        'warning',
        'Acceso restringido',
        'La gestion de cuentas solo esta disponible para SuperAdmin.',
        atenea_dashboard_route_for_session()
    );
}

if (!function_exists('sa_user_h')) {
    function sa_user_h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$employeeRoleOptions = atenea_employee_role_catalog();

$internalAccounts = [];
$resultInternalAccounts = mysqli_query(
    $db,
    'SELECT u.ID, u.USERNAME, u.U_ESTADO, t.TYPE, e.FIRST_NAME, e.LAST_NAME
     FROM users u
     INNER JOIN employee e ON e.EMPLOYEE_ID = u.EMPLOYEE_ID
     INNER JOIN type t ON t.TYPE_ID = u.TYPE_ID
     WHERE u.TYPE_ID IN (1, 2, 4)
     ORDER BY e.LAST_NAME ASC, e.FIRST_NAME ASC'
);
while ($resultInternalAccounts instanceof mysqli_result && ($row = mysqli_fetch_assoc($resultInternalAccounts))) {
    $internalAccounts[] = $row;
}
if ($resultInternalAccounts instanceof mysqli_result) {
    mysqli_free_result($resultInternalAccounts);
}

$studentAccounts = [];
$resultStudentAccounts = mysqli_query(
    $db,
    'SELECT u.ID, u.USERNAME, u.U_ESTADO, t.TYPE, es.nombres_estudiante, es.apellidos_estudiante, es.carnet_estudiante
     FROM users u
     INNER JOIN estudiantes es ON es.ESTUDIANTE_ID = u.ESTUDIANTE_ID
     INNER JOIN type t ON t.TYPE_ID = u.TYPE_ID
     WHERE u.TYPE_ID = 3
     ORDER BY es.apellidos_estudiante ASC, es.nombres_estudiante ASC'
);
while ($resultStudentAccounts instanceof mysqli_result && ($row = mysqli_fetch_assoc($resultStudentAccounts))) {
    $studentAccounts[] = $row;
}
if ($resultStudentAccounts instanceof mysqli_result) {
    mysqli_free_result($resultStudentAccounts);
}

$employeeOptions = [];
$resultEmployees = mysqli_query(
    $db,
    'SELECT e.EMPLOYEE_ID, e.FIRST_NAME, e.LAST_NAME, j.JOB_TITLE
     FROM employee e
     INNER JOIN job j ON j.JOB_ID = e.JOB_ID
     WHERE e.E_ESTADO = 1
     ORDER BY e.LAST_NAME ASC, e.FIRST_NAME ASC'
);
while ($resultEmployees instanceof mysqli_result && ($row = mysqli_fetch_assoc($resultEmployees))) {
    $employeeOptions[] = $row;
}
if ($resultEmployees instanceof mysqli_result) {
    mysqli_free_result($resultEmployees);
}

$studentOptions = [];
$resultStudents = mysqli_query(
    $db,
    'SELECT ESTUDIANTE_ID, nombres_estudiante, apellidos_estudiante, carnet_estudiante
     FROM estudiantes
     WHERE estado_estudiante = 1
     ORDER BY apellidos_estudiante ASC, nombres_estudiante ASC'
);
while ($resultStudents instanceof mysqli_result && ($row = mysqli_fetch_assoc($resultStudents))) {
    $studentOptions[] = $row;
}
if ($resultStudents instanceof mysqli_result) {
    mysqli_free_result($resultStudents);
}
?>

<div class="row">
  <div class="col-12">
    <div class="card shadow mb-4">
      <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="m-0 font-weight-bold text-primary">Cuentas internas de Atenea</h4>
        <button type="button" class="btn btn-primary bg-gradient-primary" data-toggle="modal" data-target="#personalModal">
          <i class="fas fa-fw fa-plus"></i> Nueva cuenta interna
        </button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered js-datatable" id="dataTableInternalAccounts" width="100%" cellspacing="0">
            <thead>
              <tr>
                <th>Nombre completo</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($internalAccounts as $account): ?>
                <tr>
                  <td><?php echo sa_user_h(trim((string) $account['FIRST_NAME'] . ' ' . (string) $account['LAST_NAME'])); ?></td>
                  <td><?php echo sa_user_h($account['USERNAME'] ?? ''); ?></td>
                  <td><?php echo sa_user_h(atenea_role_label((string) ($account['TYPE'] ?? ''))); ?></td>
                  <td>
                    <?php if ((int) ($account['U_ESTADO'] ?? 0) === 1): ?>
                      <span class="badge badge-success">Activo</span>
                    <?php else: ?>
                      <span class="badge badge-danger">Inactivo</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-right">
                    <div class="btn-group">
                      <a class="btn btn-primary bg-gradient-primary btn-sm" href="sa_cuentas_usuarios_searchfrm1.php?action=edit&id=<?php echo (int) $account['ID']; ?>">
                        <i class="fas fa-fw fa-list-alt"></i> Detalles
                      </a>
                    </div>
                    <div class="btn-group">
                      <a class="btn btn-warning bg-gradient-warning btn-sm" href="sa_cuentas_usuarios_edit1.php?action=edit&id=<?php echo (int) $account['ID']; ?>">
                        <i class="fas fa-fw fa-edit"></i> Editar
                      </a>
                    </div>
                    <div class="btn-group">
                      <button type="button" class="btn btn-danger bg-gradient-danger btn-sm" onclick="confirmDelete(<?php echo (int) $account['ID']; ?>)">
                        <i class="fas fa-fw fa-trash"></i> Eliminar
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card shadow mb-4">
      <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="m-0 font-weight-bold text-primary">Cuentas de estudiantes / usuarios</h4>
        <button type="button" class="btn btn-primary bg-gradient-primary" data-toggle="modal" data-target="#studentModal">
          <i class="fas fa-fw fa-plus"></i> Nueva cuenta de estudiante
        </button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered js-datatable" id="dataTableStudentAccounts" width="100%" cellspacing="0">
            <thead>
              <tr>
                <th>Nombre completo</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($studentAccounts as $account): ?>
                <tr>
                  <td><?php echo sa_user_h(trim((string) $account['apellidos_estudiante'] . ', ' . (string) $account['nombres_estudiante'])); ?></td>
                  <td><?php echo sa_user_h($account['USERNAME'] ?? ''); ?></td>
                  <td><?php echo sa_user_h(atenea_role_label((string) ($account['TYPE'] ?? ''))); ?></td>
                  <td>
                    <?php if ((int) ($account['U_ESTADO'] ?? 0) === 1): ?>
                      <span class="badge badge-success">Activo</span>
                    <?php else: ?>
                      <span class="badge badge-danger">Inactivo</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-right">
                    <div class="btn-group">
                      <a class="btn btn-primary bg-gradient-primary btn-sm" href="sa_cuentas_usuarios_searchfrm2.php?action=edit&id=<?php echo (int) $account['ID']; ?>">
                        <i class="fas fa-fw fa-list-alt"></i> Detalles
                      </a>
                    </div>
                    <div class="btn-group">
                      <a class="btn btn-warning bg-gradient-warning btn-sm" href="sa_cuentas_usuarios_edit3.php?action=edit&id=<?php echo (int) $account['ID']; ?>">
                        <i class="fas fa-fw fa-edit"></i> Editar
                      </a>
                    </div>
                    <div class="btn-group">
                      <button type="button" class="btn btn-danger bg-gradient-danger btn-sm" onclick="confirmDelete(<?php echo (int) $account['ID']; ?>)">
                        <i class="fas fa-fw fa-trash"></i> Eliminar
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="personalModal" tabindex="-1" role="dialog" aria-labelledby="personalModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="personalModalLabel">Agregar cuenta interna</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="addPersonalForm" role="form">
          <div class="form-group">
            <select class="form-control" name="empid" required>
              <option value="" disabled selected hidden>Seleccionar empleado</option>
              <?php foreach ($employeeOptions as $employee): ?>
                <option value="<?php echo (int) $employee['EMPLOYEE_ID']; ?>">
                  <?php echo sa_user_h(trim((string) $employee['FIRST_NAME'] . ' ' . (string) $employee['LAST_NAME'] . ' - ' . (string) $employee['JOB_TITLE'])); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <input class="form-control" placeholder="Usuario" minlength="5" maxlength="70" name="username" required>
          </div>
          <div class="form-group">
            <input class="form-control" placeholder="Contrasena" minlength="8" maxlength="80" name="password" type="password" required>
          </div>
          <div class="form-group">
            <input class="form-control" placeholder="Confirmar contrasena" minlength="8" maxlength="80" name="confirm_password" type="password" required>
          </div>
          <div class="form-group">
            <select class="form-control" name="type" required>
              <option value="" disabled selected hidden>Tipo de cuenta</option>
              <?php foreach ($employeeRoleOptions as $roleId => $role): ?>
                <option value="<?php echo (int) $roleId; ?>"><?php echo sa_user_h($role['label'] ?? ''); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <select class="form-control" name="estado" required>
              <option value="" disabled selected hidden>Estado del registro</option>
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          <hr>
          <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i> Guardar</button>
          <button type="reset" class="btn btn-warning"><i class="fa fa-circle-notch fa-fw"></i> Reiniciar</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i> Cancelar</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="studentModal" tabindex="-1" role="dialog" aria-labelledby="studentModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="studentModalLabel">Agregar cuenta de estudiante / usuario</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="addStudentForm" role="form">
          <div class="form-group">
            <select class="form-control" name="estid" required>
              <option value="" disabled selected hidden>Seleccionar estudiante</option>
              <?php foreach ($studentOptions as $student): ?>
                <option value="<?php echo (int) $student['ESTUDIANTE_ID']; ?>">
                  <?php echo sa_user_h(trim((string) $student['apellidos_estudiante'] . ', ' . (string) $student['nombres_estudiante'] . ' - ' . (string) $student['carnet_estudiante'])); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <input class="form-control" minlength="5" maxlength="70" placeholder="Usuario" name="username" required>
          </div>
          <div class="form-group">
            <input class="form-control" minlength="8" maxlength="80" placeholder="Contrasena" name="password" type="password" required>
          </div>
          <div class="form-group">
            <input class="form-control" minlength="8" maxlength="80" placeholder="Confirmar contrasena" name="confirm_password" type="password" required>
          </div>
          <div class="form-group">
            <input class="form-control" value="<?php echo sa_user_h(atenea_role_label('Estudiante')); ?>" readonly>
            <input type="hidden" name="type" value="3">
          </div>
          <div class="form-group">
            <select class="form-control" name="estado" required>
              <option value="" disabled selected hidden>Estado del registro</option>
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          <hr>
          <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i> Guardar</button>
          <button type="reset" class="btn btn-warning"><i class="fa fa-circle-notch fa-fw"></i> Reiniciar</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i> Cancelar</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  function showResponseAlert(response, onSuccess) {
    var icon = response && response.success ? 'success' : 'error';
    var title = response && response.success ? 'Operacion completada' : 'No fue posible completar la operacion';
    var message = response && response.message ? response.message : 'Ha ocurrido un error inesperado.';

    Swal.fire({
      icon: icon,
      title: title,
      text: message,
      confirmButtonText: 'OK'
    }).then(function () {
      if (response && response.success && typeof onSuccess === 'function') {
        onSuccess();
      }
    });
  }

  function submitAccountForm(formSelector, endpoint) {
    $(formSelector).on('submit', function (event) {
      event.preventDefault();

      var password = $('input[name="password"]', this).val();
      var confirmPassword = $('input[name="confirm_password"]', this).val();
      if (password !== confirmPassword) {
        Swal.fire({
          icon: 'error',
          title: 'Contrasenas distintas',
          text: 'Las contrasenas deben coincidir antes de guardar.'
        });
        return;
      }

      $.ajax({
        url: endpoint,
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function (response) {
          showResponseAlert(response, function () {
            window.location.reload();
          });
        },
        error: function () {
          showResponseAlert({ success: false, message: 'Ha ocurrido un error en la comunicacion con el servidor.' });
        }
      });
    });
  }

  submitAccountForm('#addPersonalForm', 'sa_cuentas_usuarios_transac1.php?action=add');
  submitAccountForm('#addStudentForm', 'sa_cuentas_usuarios_transac2.php');

  window.confirmDelete = function (userId) {
    Swal.fire({
      title: 'Eliminar cuenta',
      text: 'Esta accion removera la cuenta seleccionada del sistema.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Si, eliminar',
      cancelButtonText: 'Cancelar'
    }).then(function (result) {
      if (!result.isConfirmed) {
        return;
      }

      $.ajax({
        url: 'sa_cuentas_usuarios_delete.php',
        type: 'GET',
        data: { id: userId },
        dataType: 'json',
        success: function (response) {
          showResponseAlert(response, function () {
            window.location.reload();
          });
        },
        error: function () {
          showResponseAlert({ success: false, message: 'Ha ocurrido un error en la comunicacion con el servidor.' });
        }
      });
    });
  };
}());
</script>

<?php
include '../includes/footer_superadmin.php';
