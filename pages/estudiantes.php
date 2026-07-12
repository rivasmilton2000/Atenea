<?php
require 'session.php';
require_once '../includes/connection.php';
require_once '../includes/material_dashboard.php';
require_once '../includes/atenea_admin.php';
require_once '../includes/atenea_capacitacion.php';

atenea_backoffice_require($db);

if (!function_exists('atenea_students_h')) {
    function atenea_students_h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('atenea_students_alert_set')) {
    function atenea_students_alert_set(string $type, string $message): void
    {
        $_SESSION['ATENEA_STUDENTS_ALERT'] = [
            'type' => $type,
            'message' => $message,
        ];
    }
}

if (!function_exists('atenea_students_alert_pull')) {
    function atenea_students_alert_pull(): ?array
    {
        $alert = $_SESSION['ATENEA_STUDENTS_ALERT'] ?? null;
        unset($_SESSION['ATENEA_STUDENTS_ALERT']);

        return is_array($alert) ? $alert : null;
    }
}

if (!function_exists('atenea_students_validate_password')) {
    function atenea_students_validate_password(string $password): array
    {
        $errors = [];

        if ($password !== '' && strlen($password) < 8) {
            $errors[] = 'La contrasena debe tener al menos 8 caracteres.';
        }

        if ($password !== '' && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'La contrasena debe incluir al menos una letra mayuscula.';
        }

        if ($password !== '' && !preg_match('/[a-z]/', $password)) {
            $errors[] = 'La contrasena debe incluir al menos una letra minuscula.';
        }

        if ($password !== '' && !preg_match('/\d/', $password)) {
            $errors[] = 'La contrasena debe incluir al menos un numero.';
        }

        return $errors;
    }
}

if (!function_exists('atenea_students_username_base')) {
    function atenea_students_username_base(string $firstName, string $lastName, string $email): string
    {
        $base = trim((string) preg_replace('/[^a-z0-9._-]/i', '', strtolower($firstName . '.' . $lastName)));
        if ($base === '') {
            $base = trim((string) preg_replace('/[^a-z0-9._-]/i', '', strtolower((string) strtok($email, '@'))));
        }

        return $base !== '' ? $base : 'estudiante';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['student_action'] ?? '') === 'create_student') {
    $firstName = trim((string) ($_POST['first_name'] ?? ''));
    $lastName = trim((string) ($_POST['last_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phoneNumber = trim((string) ($_POST['phone_number'] ?? ''));
    $password = trim((string) ($_POST['password'] ?? ''));
    $status = isset($_POST['account_status']) && (int) $_POST['account_status'] === 0 ? 0 : 1;

    if ($firstName === '' || $lastName === '' || $email === '') {
        atenea_students_alert_set('danger', 'Nombre, apellido y correo son obligatorios para crear el estudiante.');
        header('Location: estudiantes.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        atenea_students_alert_set('danger', 'El correo del estudiante no tiene un formato valido.');
        header('Location: estudiantes.php');
        exit;
    }

    $passwordErrors = atenea_students_validate_password($password);
    if ($passwordErrors !== []) {
        atenea_students_alert_set('danger', implode(' ', $passwordErrors));
        header('Location: estudiantes.php');
        exit;
    }

    if (atenea_email_exists_for_any_account($db, $email)) {
        atenea_students_alert_set('danger', 'Ese correo ya esta vinculado a otra cuenta de Atenea.');
        header('Location: estudiantes.php');
        exit;
    }

    $usernameBase = atenea_students_username_base($firstName, $lastName, $email);
    $username = $usernameBase;
    $suffix = 1;
    while (atenea_username_exists($db, $username)) {
        $username = $usernameBase . $suffix;
        $suffix++;
    }

    $generatedPassword = '';
    if ($password === '') {
        $generatedPassword = 'Atenea' . random_int(1000, 9999);
        $password = $generatedPassword;
    }

    mysqli_begin_transaction($db);

    try {
        $passwordHash = sha1($password);

        if (atenea_db_has_column($db, 'users', 'U_ESTADO')) {
            $stmtUser = $db->prepare(
                'INSERT INTO users (EMPLOYEE_ID, USERNAME, PASSWORD, TYPE_ID, ESTUDIANTE_ID, U_ESTADO)
                 VALUES (NULL, ?, ?, 3, NULL, ?)'
            );
            $stmtUser->bind_param('ssi', $username, $passwordHash, $status);
        } else {
            $stmtUser = $db->prepare(
                'INSERT INTO users (EMPLOYEE_ID, USERNAME, PASSWORD, TYPE_ID, ESTUDIANTE_ID)
                 VALUES (NULL, ?, ?, 3, NULL)'
            );
            $stmtUser->bind_param('ss', $username, $passwordHash);
        }

        if (!$stmtUser || !$stmtUser->execute()) {
            throw new RuntimeException('No se pudo crear la cuenta principal del estudiante.');
        }

        $userId = (int) $stmtUser->insert_id;
        $stmtUser->close();

        $billingName = trim($firstName . ' ' . $lastName);
        $planStatus = 'pending';
        $stmtPublic = $db->prepare(
            'INSERT INTO public_users (
                USER_ID,
                FIRST_NAME,
                LAST_NAME,
                EMAIL,
                PHONE_NUMBER,
                BILLING_NAME,
                BILLING_EMAIL,
                PLAN_STATUS,
                ACCOUNT_STATUS
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        if (!$stmtPublic) {
            throw new RuntimeException('No se pudo crear el perfil publico del estudiante.');
        }

        $stmtPublic->bind_param(
            'isssssssi',
            $userId,
            $firstName,
            $lastName,
            $email,
            $phoneNumber,
            $billingName,
            $email,
            $planStatus,
            $status
        );

        if (!$stmtPublic->execute()) {
            throw new RuntimeException('No se pudo guardar la informacion publica del estudiante.');
        }

        $stmtPublic->close();
        atenea_set_public_registration_source($db, $userId, 'admin');
        mysqli_commit($db);

        $message = 'Estudiante creado correctamente.';
        if ($generatedPassword !== '') {
            $message .= ' Contrasena temporal generada: ' . $generatedPassword;
        }

        atenea_students_alert_set('success', $message);
        header('Location: estudiantes.php');
        exit;
    } catch (Throwable $exception) {
        mysqli_rollback($db);
        atenea_students_alert_set('danger', $exception->getMessage());
        header('Location: estudiantes.php');
        exit;
    }
}

$currentRole = (string) ($_SESSION['TYPE'] ?? '');
include $currentRole === 'SuperAdmin'
    ? '../includes/sidebar_superadmin.php'
    : '../includes/sidebar_admin.php';

$search = trim((string) ($_GET['q'] ?? ''));
$students = atenea_backoffice_fetch_registered_students($db, $search);
$alert = atenea_students_alert_pull();
$activeCount = 0;
$googleCount = 0;
$incompleteCount = 0;

foreach ($students as $student) {
    if (!empty($student['is_active'])) {
        $activeCount++;
    }

    if (strtolower((string) ($student['registration_source'] ?? '')) === 'google') {
        $googleCount++;
    }

    if (!empty($student['profile_incomplete'])) {
        $incompleteCount++;
    }
}

$legacyStudentsCount = atenea_db_has_table($db, 'estudiantes')
    ? dashboard_count($db, 'SELECT COUNT(*) FROM estudiantes')
    : 0;
?>

<?php if ($alert !== null) : ?>
    <div class="alert alert-<?php echo atenea_students_h((string) ($alert['type'] ?? 'info')); ?> mb-4">
        <?php echo atenea_students_h((string) ($alert['message'] ?? '')); ?>
    </div>
<?php endif; ?>

<div class="alert alert-info">
    Este modulo ya usa usuarios reales registrados en Atenea desde <code>users</code> + <code>public_users</code>.
    La tabla legacy <code>estudiantes</code> queda fuera del flujo principal y solo se conserva por compatibilidad.
    <?php if ($legacyStudentsCount > 0) : ?>
        Actualmente existen <?php echo (int) $legacyStudentsCount; ?> registros legacy documentados como obsoletos.
    <?php endif; ?>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-left-primary shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Estudiantes registrados</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($students); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-left-success shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Activos</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $activeCount; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-left-info shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Registro con Google</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $googleCount; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-left-warning shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Perfiles incompletos</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $incompleteCount; ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 1rem;">
        <h4 class="m-0 font-weight-bold text-primary">Estudiantes reales de Atenea</h4>
        <div class="d-flex flex-wrap" style="gap: 0.5rem;">
            <form method="get" class="form-inline">
                <input
                    class="form-control mr-2"
                    type="search"
                    name="q"
                    value="<?php echo atenea_students_h($search); ?>"
                    placeholder="Buscar por nombre, correo, telefono o usuario"
                    style="min-width: 280px;"
                >
                <button type="submit" class="btn btn-outline-primary">Buscar</button>
            </form>
            <a href="#" data-toggle="modal" data-target="#addStudentModal" class="btn btn-primary bg-gradient-primary">
                <i class="fas fa-fw fa-plus"></i> Agregar estudiante
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>NOMBRE COMPLETO</th>
                        <th>CORREO</th>
                        <th>TELEFONO</th>
                        <th>REGISTRO</th>
                        <th>TIPO</th>
                        <th>ESTADO</th>
                        <th>CURSO ACTIVO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student) : ?>
                        <?php
                        $statusClass = !empty($student['is_active']) ? 'badge-success' : 'badge-danger';
                        $statusLabel = !empty($student['is_active']) ? 'Activo' : 'Inactivo';
                        $createdAt = trim((string) ($student['created_at'] ?? ''));
                        $createdLabel = $createdAt !== '' ? date('d/m/Y h:i A', strtotime($createdAt)) : 'No disponible';
                        $courseLabel = trim((string) ($student['current_course'] ?? '')) !== ''
                            ? (string) $student['current_course']
                            : 'Sin inscripcion activa';
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo atenea_students_h((string) ($student['full_name'] ?? 'Perfil incompleto')); ?></strong><br>
                                <small class="text-muted">Usuario: <?php echo atenea_students_h((string) ($student['USERNAME'] ?? '')); ?></small>
                                <?php if (!empty($student['profile_incomplete'])) : ?>
                                    <br><span class="badge badge-warning">Perfil incompleto</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo atenea_students_h((string) ($student['email'] ?? 'Sin correo')); ?><br>
                                <small class="text-muted">Alta: <?php echo atenea_students_h($createdLabel); ?></small>
                            </td>
                            <td><?php echo atenea_students_h(trim((string) ($student['phone_number'] ?? '')) !== '' ? (string) $student['phone_number'] : 'No registrado'); ?></td>
                            <td><?php echo atenea_students_h((string) ($student['registration_source_label'] ?? 'Normal')); ?></td>
                            <td><?php echo atenea_students_h((string) ($student['role_name'] ?? 'Estudiante')); ?></td>
                            <td><span class="badge <?php echo atenea_students_h($statusClass); ?>"><?php echo atenea_students_h($statusLabel); ?></span></td>
                            <td><?php echo atenea_students_h($courseLabel); ?></td>
                            <td style="min-width: 260px;">
                                <div class="d-flex flex-wrap" style="gap: 0.4rem;">
                                    <a class="btn btn-primary btn-sm" href="estudiante_usuario.php?id=<?php echo (int) ($student['ID'] ?? 0); ?>">Ver perfil</a>
                                    <a class="btn btn-outline-primary btn-sm" href="estudiante_usuario.php?id=<?php echo (int) ($student['ID'] ?? 0); ?>#editar">Editar</a>
                                    <a class="btn btn-outline-success btn-sm" href="inscripciones_admin.php?user_id=<?php echo (int) ($student['ID'] ?? 0); ?>">Ver cursos</a>
                                    <a class="btn btn-outline-dark btn-sm" href="record_escolar_admin.php?user_id=<?php echo (int) ($student['ID'] ?? 0); ?>">Record escolar</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear usuario estudiante real</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="student_action" value="create_student">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Nombre</strong></label>
                                <input class="form-control" name="first_name" maxlength="100" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Apellido</strong></label>
                                <input class="form-control" name="last_name" maxlength="100" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Correo</strong></label>
                                <input class="form-control" type="email" name="email" maxlength="150" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Telefono</strong> (opcional)</label>
                                <input class="form-control" name="phone_number" maxlength="25">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Contrasena</strong> (opcional)</label>
                                <input class="form-control" type="text" name="password" maxlength="80" placeholder="Si se deja vacio se genera una temporal">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Estado</strong></label>
                                <select class="form-control" name="account_status">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i> Crear estudiante</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i> Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
