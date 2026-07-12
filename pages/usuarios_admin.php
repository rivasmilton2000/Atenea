<?php
require 'session.php';
require_once '../includes/connection.php';
require_once '../includes/material_dashboard.php';
require_once '../includes/atenea_admin.php';

atenea_backoffice_require($db);

if (!function_exists('atenea_users_h')) {
    function atenea_users_h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$currentRole = (string) ($_SESSION['TYPE'] ?? '');
include $currentRole === 'SuperAdmin'
    ? '../includes/sidebar_superadmin.php'
    : '../includes/sidebar_admin.php';

$search = trim((string) ($_GET['q'] ?? ''));
$userStatusExpr = atenea_db_has_column($db, 'users', 'U_ESTADO') ? 'COALESCE(u.U_ESTADO, 1)' : '1';
$query = "SELECT
            u.ID,
            u.USERNAME,
            {$userStatusExpr} AS user_status,
            COALESCE(t.TYPE, 'Sin rol') AS role_name,
            COALESCE(pu.PUBLIC_USER_ID, 0) AS public_user_id,
            COALESCE(pu.FIRST_NAME, '') AS public_first_name,
            COALESCE(pu.LAST_NAME, '') AS public_last_name,
            COALESCE(pu.EMAIL, '') AS public_email,
            COALESCE(pu.ACCOUNT_STATUS, 1) AS public_status,
            COALESCE(pu.CREATED_AT, NULL) AS public_created_at,
            COALESCE(e.FIRST_NAME, '') AS employee_first_name,
            COALESCE(e.LAST_NAME, '') AS employee_last_name,
            COALESCE(e.EMAIL, '') AS employee_email,
            COALESCE(j.JOB_TITLE, '') AS employee_job_title,
            COALESCE(es.nombres_estudiante, '') AS legacy_first_name,
            COALESCE(es.apellidos_estudiante, '') AS legacy_last_name
          FROM users u
          LEFT JOIN type t ON t.TYPE_ID = u.TYPE_ID
          LEFT JOIN public_users pu ON pu.USER_ID = u.ID
          LEFT JOIN employee e ON e.EMPLOYEE_ID = u.EMPLOYEE_ID
          LEFT JOIN job j ON j.JOB_ID = e.JOB_ID
          LEFT JOIN estudiantes es ON es.ESTUDIANTE_ID = u.ESTUDIANTE_ID
          ORDER BY u.ID DESC";
$result = mysqli_query($db, $query) or die(mysqli_error($db));
$rows = [];
$publicCount = 0;
$internalCount = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $displayName = trim((string) $row['public_first_name'] . ' ' . (string) $row['public_last_name']);
    $origin = 'Publico';
    $email = (string) $row['public_email'];

    if ($displayName === '' && ((int) ($row['EMPLOYEE_ID'] ?? 0) > 0 || trim((string) ($row['employee_first_name'] ?? '')) !== '')) {
        $displayName = trim((string) $row['employee_first_name'] . ' ' . (string) $row['employee_last_name']);
        $email = (string) $row['employee_email'];
        $origin = 'Interno';
    }

    if ($displayName === '' && trim((string) ($row['legacy_first_name'] ?? '')) !== '') {
        $displayName = trim((string) $row['legacy_first_name'] . ' ' . (string) $row['legacy_last_name']);
        $origin = 'Legacy';
    }

    if ($displayName === '') {
        $displayName = (string) $row['USERNAME'];
    }

    $row['display_name'] = $displayName;
    $row['origin'] = $origin;
    $row['display_email'] = $email !== '' ? $email : 'No disponible';
    $row['is_active'] = (int) ($row['user_status'] ?? 0) === 1 && (int) ($row['public_status'] ?? 1) === 1;

    if ($origin === 'Publico') {
        $publicCount++;
    } else {
        $internalCount++;
    }

    if ($search !== '') {
        $haystack = strtolower($displayName . ' ' . $row['display_email'] . ' ' . (string) $row['USERNAME'] . ' ' . (string) $row['role_name']);
        if (strpos($haystack, strtolower($search)) === false) {
            continue;
        }
    }

    $rows[] = $row;
}
?>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card border-left-primary shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Usuarios listados</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($rows); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-success shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Cuentas publicas</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $publicCount; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-info shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Cuentas internas / legacy</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $internalCount; ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 1rem;">
        <h4 class="m-0 font-weight-bold text-primary">Usuarios y cuentas de Atenea</h4>
        <form method="get" class="form-inline">
            <input class="form-control mr-2" type="search" name="q" value="<?php echo atenea_users_h($search); ?>" placeholder="Buscar por nombre, correo, usuario o rol">
            <button type="submit" class="btn btn-outline-primary">Buscar</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>NOMBRE</th>
                        <th>USUARIO</th>
                        <th>CORREO</th>
                        <th>ROL</th>
                        <th>ORIGEN</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row) : ?>
                        <tr>
                            <td>
                                <strong><?php echo atenea_users_h((string) ($row['display_name'] ?? 'Usuario')); ?></strong>
                                <?php if ((string) ($row['origin'] ?? '') === 'Interno' && trim((string) ($row['employee_job_title'] ?? '')) !== '') : ?>
                                    <br><small><?php echo atenea_users_h((string) $row['employee_job_title']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo atenea_users_h((string) ($row['USERNAME'] ?? '')); ?></td>
                            <td><?php echo atenea_users_h((string) ($row['display_email'] ?? 'No disponible')); ?></td>
                            <td><?php echo atenea_users_h((string) ($row['role_name'] ?? 'Sin rol')); ?></td>
                            <td><?php echo atenea_users_h((string) ($row['origin'] ?? 'Publico')); ?></td>
                            <td><span class="badge <?php echo !empty($row['is_active']) ? 'badge-success' : 'badge-danger'; ?>"><?php echo !empty($row['is_active']) ? 'Activo' : 'Inactivo'; ?></span></td>
                            <td>
                                <?php if ((int) ($row['public_user_id'] ?? 0) > 0) : ?>
                                    <a class="btn btn-outline-primary btn-sm" href="estudiante_usuario.php?id=<?php echo (int) ($row['ID'] ?? 0); ?>">Ver cuenta</a>
                                <?php else : ?>
                                    <span class="text-muted">Gestion interna</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
