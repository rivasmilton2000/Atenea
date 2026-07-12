<?php
require 'session.php';
require_once '../includes/connection.php';
require_once '../includes/material_dashboard.php';
require_once '../includes/atenea_admin.php';
require_once '../includes/atenea_capacitacion.php';

atenea_backoffice_require($db);

if (!function_exists('atenea_student_profile_h')) {
    function atenea_student_profile_h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('atenea_student_profile_alert_set')) {
    function atenea_student_profile_alert_set(string $type, string $message): void
    {
        $_SESSION['ATENEA_STUDENT_PROFILE_ALERT'] = [
            'type' => $type,
            'message' => $message,
        ];
    }
}

if (!function_exists('atenea_student_profile_alert_pull')) {
    function atenea_student_profile_alert_pull(): ?array
    {
        $alert = $_SESSION['ATENEA_STUDENT_PROFILE_ALERT'] ?? null;
        unset($_SESSION['ATENEA_STUDENT_PROFILE_ALERT']);

        return is_array($alert) ? $alert : null;
    }
}

$userId = max(0, (int) ($_GET['id'] ?? $_POST['user_id'] ?? 0));
if ($userId <= 0) {
    atenea_student_profile_alert_set('danger', 'No se encontro el estudiante solicitado.');
    header('Location: estudiantes.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['student_action'] ?? '') === 'update_student') {
    $firstName = trim((string) ($_POST['first_name'] ?? ''));
    $lastName = trim((string) ($_POST['last_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phoneNumber = trim((string) ($_POST['phone_number'] ?? ''));
    $birthdate = trim((string) ($_POST['birthdate'] ?? ''));
    $status = isset($_POST['account_status']) && (int) $_POST['account_status'] === 0 ? 0 : 1;
    $newPassword = trim((string) ($_POST['new_password'] ?? ''));

    if ($firstName === '' || $lastName === '' || $email === '') {
        atenea_student_profile_alert_set('danger', 'Nombre, apellido y correo son obligatorios.');
        header('Location: estudiante_usuario.php?id=' . $userId);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        atenea_student_profile_alert_set('danger', 'El correo no tiene un formato valido.');
        header('Location: estudiante_usuario.php?id=' . $userId);
        exit;
    }

    if (atenea_email_exists_for_any_account($db, $email, $userId)) {
        atenea_student_profile_alert_set('danger', 'Ese correo ya esta siendo usado por otra cuenta.');
        header('Location: estudiante_usuario.php?id=' . $userId);
        exit;
    }

    mysqli_begin_transaction($db);

    try {
        if (atenea_db_has_column($db, 'users', 'U_ESTADO')) {
            $stmtUser = $db->prepare('UPDATE users SET TYPE_ID = 3, U_ESTADO = ? WHERE ID = ? LIMIT 1');
            $stmtUser->bind_param('ii', $status, $userId);
        } else {
            $stmtUser = $db->prepare('UPDATE users SET TYPE_ID = 3 WHERE ID = ? LIMIT 1');
            $stmtUser->bind_param('i', $userId);
        }

        if (!$stmtUser || !$stmtUser->execute()) {
            throw new RuntimeException('No se pudo actualizar la cuenta principal.');
        }

        $stmtUser->close();

        if ($newPassword !== '') {
            $passwordHash = sha1($newPassword);
            $stmtPassword = $db->prepare('UPDATE users SET PASSWORD = ? WHERE ID = ? LIMIT 1');
            if (!$stmtPassword) {
                throw new RuntimeException('No se pudo preparar el cambio de contrasena.');
            }

            $stmtPassword->bind_param('si', $passwordHash, $userId);
            if (!$stmtPassword->execute()) {
                throw new RuntimeException('No se pudo actualizar la contrasena del estudiante.');
            }
            $stmtPassword->close();
        }

        $birthdateValue = $birthdate !== '' ? $birthdate : null;
        $stmtPublic = $db->prepare(
            'UPDATE public_users
             SET FIRST_NAME = ?,
                 LAST_NAME = ?,
                 EMAIL = ?,
                 PHONE_NUMBER = ?,
                 BIRTHDATE = ?,
                 BILLING_NAME = ?,
                 BILLING_EMAIL = ?,
                 ACCOUNT_STATUS = ?
             WHERE USER_ID = ?
             LIMIT 1'
        );

        if (!$stmtPublic) {
            throw new RuntimeException('No se pudo actualizar el perfil publico.');
        }

        $billingName = trim($firstName . ' ' . $lastName);
        $stmtPublic->bind_param(
            'sssssssii',
            $firstName,
            $lastName,
            $email,
            $phoneNumber,
            $birthdateValue,
            $billingName,
            $email,
            $status,
            $userId
        );

        if (!$stmtPublic->execute()) {
            throw new RuntimeException('No se pudo guardar el perfil publico del estudiante.');
        }

        $stmtPublic->close();
        mysqli_commit($db);

        atenea_student_profile_alert_set('success', 'Perfil del estudiante actualizado correctamente.');
        header('Location: estudiante_usuario.php?id=' . $userId);
        exit;
    } catch (Throwable $exception) {
        mysqli_rollback($db);
        atenea_student_profile_alert_set('danger', $exception->getMessage());
        header('Location: estudiante_usuario.php?id=' . $userId);
        exit;
    }
}

$student = atenea_backoffice_fetch_registered_student($db, $userId);
if ($student === null) {
    atenea_student_profile_alert_set('danger', 'No se encontro un estudiante real vinculado a ese usuario.');
    header('Location: estudiantes.php');
    exit;
}

$currentRole = (string) ($_SESSION['TYPE'] ?? '');
include $currentRole === 'SuperAdmin'
    ? '../includes/sidebar_superadmin.php'
    : '../includes/sidebar_admin.php';

$alert = atenea_student_profile_alert_pull();
$publicUserId = (int) ($student['public_user_id'] ?? 0);
$enrollments = $publicUserId > 0
    ? atenea_capacitacion_fetch_enrollments_for_public_user($db, $publicUserId)
    : [];
$activeEnrollments = 0;

foreach ($enrollments as $enrollment) {
    if (in_array((string) ($enrollment['estado_curso'] ?? ''), ['curso_activo', 'activo'], true)
        || in_array((string) ($enrollment['estado_aprobacion'] ?? ''), ['en_proceso', 'aprobado'], true)) {
        $activeEnrollments++;
    }
}

$createdAt = trim((string) ($student['created_at'] ?? ''));
$createdLabel = $createdAt !== '' ? date('d/m/Y h:i A', strtotime($createdAt)) : 'No disponible';
$birthdateLabel = trim((string) ($student['birthdate'] ?? ''));
?>

<?php if ($alert !== null) : ?>
    <div class="alert alert-<?php echo atenea_student_profile_h((string) ($alert['type'] ?? 'info')); ?> mb-4">
        <?php echo atenea_student_profile_h((string) ($alert['message'] ?? '')); ?>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-left-primary shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Estado</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo !empty($student['is_active']) ? 'Activo' : 'Inactivo'; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-left-success shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Registro</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo atenea_student_profile_h((string) ($student['registration_source_label'] ?? 'Normal')); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-left-info shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Inscripciones</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($enrollments); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-left-warning shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Cursos activos</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $activeEnrollments; ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h5 class="m-0 font-weight-bold text-primary">Resumen del estudiante</h5>
            </div>
            <div class="card-body">
                <p><strong>Nombre completo:</strong> <?php echo atenea_student_profile_h((string) ($student['full_name'] ?? 'Perfil incompleto')); ?></p>
                <p><strong>Correo:</strong> <?php echo atenea_student_profile_h((string) ($student['email'] ?? 'Sin correo')); ?></p>
                <p><strong>Telefono:</strong> <?php echo atenea_student_profile_h(trim((string) ($student['phone_number'] ?? '')) !== '' ? (string) $student['phone_number'] : 'No registrado'); ?></p>
                <p><strong>Usuario:</strong> <?php echo atenea_student_profile_h((string) ($student['USERNAME'] ?? '')); ?></p>
                <p><strong>Alta:</strong> <?php echo atenea_student_profile_h($createdLabel); ?></p>
                <p><strong>Fecha de nacimiento:</strong> <?php echo atenea_student_profile_h($birthdateLabel !== '' ? date('d/m/Y', strtotime($birthdateLabel)) : 'No registrada'); ?></p>
                <p><strong>Curso activo:</strong> <?php echo atenea_student_profile_h(trim((string) ($student['current_course'] ?? '')) !== '' ? (string) $student['current_course'] : 'Sin inscripcion activa'); ?></p>
                <?php if (!empty($student['profile_incomplete'])) : ?>
                    <div class="alert alert-warning mb-0">Este perfil sigue incompleto. Atenea usa nombre y apellido reales para su visibilidad academica y certificados.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-7 mb-4" id="editar">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h5 class="m-0 font-weight-bold text-primary">Editar perfil</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="student_action" value="update_student">
                    <input type="hidden" name="user_id" value="<?php echo (int) $userId; ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Nombre</strong></label>
                                <input class="form-control" name="first_name" maxlength="100" value="<?php echo atenea_student_profile_h((string) ($student['first_name'] ?? '')); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Apellido</strong></label>
                                <input class="form-control" name="last_name" maxlength="100" value="<?php echo atenea_student_profile_h((string) ($student['last_name'] ?? '')); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Correo</strong></label>
                                <input class="form-control" type="email" name="email" maxlength="150" value="<?php echo atenea_student_profile_h((string) ($student['email'] ?? '')); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Telefono</strong></label>
                                <input class="form-control" name="phone_number" maxlength="25" value="<?php echo atenea_student_profile_h((string) ($student['phone_number'] ?? '')); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Fecha de nacimiento</strong></label>
                                <input class="form-control" type="date" name="birthdate" value="<?php echo atenea_student_profile_h((string) ($student['birthdate'] ?? '')); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Estado</strong></label>
                                <select class="form-control" name="account_status">
                                    <option value="1" <?php echo !empty($student['is_active']) ? 'selected' : ''; ?>>Activo</option>
                                    <option value="0" <?php echo empty($student['is_active']) ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><strong>Nueva contrasena</strong> (opcional)</label>
                        <input class="form-control" name="new_password" maxlength="80" placeholder="Solo completa si deseas cambiarla">
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i> Guardar cambios</button>
                    <a href="inscripciones_admin.php?user_id=<?php echo (int) $userId; ?>" class="btn btn-outline-primary">Ver cursos</a>
                    <a href="record_escolar_admin.php?user_id=<?php echo (int) $userId; ?>" class="btn btn-outline-dark">Ver record escolar</a>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 0.75rem;">
        <h5 class="m-0 font-weight-bold text-primary">Cursos e inscripciones</h5>
        <a class="btn btn-outline-primary btn-sm" href="inscripciones_admin.php?user_id=<?php echo (int) $userId; ?>">Abrir modulo de inscripciones</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>CURSO</th>
                        <th>PROGRESO</th>
                        <th>ESTADO CURSO</th>
                        <th>APROBACION</th>
                        <th>INSCRIPCION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($enrollments === []) : ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Este estudiante aun no tiene inscripciones registradas.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($enrollments as $enrollment) : ?>
                        <tr>
                            <td><?php echo atenea_student_profile_h((string) ($enrollment['programa_titulo'] ?? 'Curso')); ?></td>
                            <td><?php echo atenea_student_profile_h((string) ($enrollment['progreso'] ?? '0')); ?>%</td>
                            <td><?php echo atenea_student_profile_h((string) ($enrollment['estado_curso'] ?? 'pendiente')); ?></td>
                            <td><?php echo atenea_student_profile_h((string) ($enrollment['estado_aprobacion'] ?? 'pendiente')); ?></td>
                            <td><?php echo atenea_student_profile_h(trim((string) ($enrollment['fecha_inscripcion'] ?? '')) !== '' ? date('d/m/Y h:i A', strtotime((string) $enrollment['fecha_inscripcion'])) : 'No disponible'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
