<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';
require_once __DIR__ . '/../includes/atenea_capacitacion.php';

if (!atenea_session_is_public_user()) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}

if (!function_exists('usuario_profile_flash_set')) {
    function usuario_profile_flash_set(string $type, string $title, string $message, bool $reopenModal = false): void
    {
        $_SESSION['ATENEA_PROFILE_FLASH'] = [
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ];

        if ($reopenModal) {
            $_SESSION['ATENEA_PROFILE_REOPEN'] = '1';
        } else {
            unset($_SESSION['ATENEA_PROFILE_REOPEN']);
        }
    }
}

if (!function_exists('usuario_profile_flash_pull')) {
    function usuario_profile_flash_pull(): ?array
    {
        $flash = $_SESSION['ATENEA_PROFILE_FLASH'] ?? null;
        unset($_SESSION['ATENEA_PROFILE_FLASH']);

        return is_array($flash) ? $flash : null;
    }
}

if (!function_exists('usuario_profile_should_reopen_modal')) {
    function usuario_profile_should_reopen_modal(): bool
    {
        $shouldOpen = !empty($_SESSION['ATENEA_PROFILE_REOPEN']);
        unset($_SESSION['ATENEA_PROFILE_REOPEN']);

        return $shouldOpen;
    }
}

if (!function_exists('usuario_profile_tab_set')) {
    function usuario_profile_tab_set(string $tabId): void
    {
        $_SESSION['ATENEA_PROFILE_ACTIVE_TAB'] = $tabId;
    }
}

if (!function_exists('usuario_profile_active_tab_pull')) {
    function usuario_profile_active_tab_pull(): string
    {
        $allowedTabs = ['profile-resumen', 'profile-editar', 'profile-seguridad', 'profile-google'];
        $tabId = (string) ($_SESSION['ATENEA_PROFILE_ACTIVE_TAB'] ?? 'profile-resumen');
        unset($_SESSION['ATENEA_PROFILE_ACTIVE_TAB']);

        return in_array($tabId, $allowedTabs, true) ? $tabId : 'profile-resumen';
    }
}

if (!function_exists('usuario_profile_display_value')) {
    function usuario_profile_display_value($value, string $fallback = 'No especificado'): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : $fallback;
    }
}

if (!function_exists('usuario_profile_format_date')) {
    function usuario_profile_format_date($value, string $fallback = 'No disponible', bool $includeTime = false): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return $fallback;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $fallback;
        }

        return $includeTime ? date('d/m/Y h:i A', $timestamp) : date('d/m/Y', $timestamp);
    }
}

if (!function_exists('usuario_profile_initials')) {
    function usuario_profile_initials(string $fullName): string
    {
        $fullName = trim($fullName);
        if ($fullName === '') {
            return 'AT';
        }

        $parts = preg_split('/\s+/', $fullName) ?: [];
        $initials = '';

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $initials .= function_exists('mb_substr')
                ? mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8')
                : strtoupper(substr($part, 0, 1));

            if (strlen($initials) >= 2) {
                break;
            }
        }

        return $initials !== '' ? $initials : 'AT';
    }
}

if (!function_exists('usuario_profile_is_valid_name')) {
    function usuario_profile_is_valid_name(string $value): bool
    {
        return (bool) preg_match("/^[\p{L}\p{M}\s'.-]{2,100}$/u", $value);
    }
}

if (!function_exists('usuario_profile_password_errors')) {
    function usuario_profile_password_errors(string $password, string $confirmPassword): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'La nueva contraseña debe incluir al menos una letra mayúscula.';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'La nueva contraseña debe incluir al menos una letra minúscula.';
        }

        if (!preg_match('/\d/', $password)) {
            $errors[] = 'La nueva contraseña debe incluir al menos un número.';
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'La nueva contraseña debe incluir al menos un símbolo.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'La confirmación de la nueva contraseña no coincide.';
        }

        return $errors;
    }
}
if (!function_exists('usuario_profile_photo_public_url')) {
    function usuario_profile_photo_public_url(string $relativePath): string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        if ($relativePath === '') {
            return '';
        }

        $projectRoot = realpath(__DIR__ . '/..');
        $absolutePath = realpath(__DIR__ . '/../' . $relativePath);

        if ($projectRoot === false || $absolutePath === false || strpos($absolutePath, $projectRoot) !== 0 || !is_file($absolutePath)) {
            return '';
        }

        return '../' . $relativePath;
    }
}

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);
$profile = atenea_fetch_public_profile_by_user_id($db, $memberId);

if (!$profile) {
    atenea_render_auth_alert(
        'warning',
        'Perfil incompleto',
        'No encontramos el perfil público asociado a esta cuenta. Inicia sesión nuevamente.',
        'logout.php?redirect=homepage.php'
    );
}

$userStateExpr = atenea_db_has_column($db, 'users', 'U_ESTADO') ? 'u.U_ESTADO' : '1';
$stmtUserAccount = $db->prepare(
    "SELECT u.ID, u.USERNAME, u.PASSWORD, {$userStateExpr} AS U_ESTADO, COALESCE(t.TYPE, 'Usuario registrado') AS TYPE_LABEL
     FROM users u
     LEFT JOIN type t ON t.TYPE_ID = u.TYPE_ID
     WHERE u.ID = ?
     LIMIT 1"
);

if (!$stmtUserAccount) {
    atenea_render_auth_alert(
        'error',
        'Acceso no disponible',
        'No pudimos cargar la cuenta asociada a este perfil en este momento.',
        'logout.php?redirect=homepage.php'
    );
}

$stmtUserAccount->bind_param('i', $memberId);
$stmtUserAccount->execute();
$userAccountResult = $stmtUserAccount->get_result();
$userAccount = $userAccountResult instanceof mysqli_result ? $userAccountResult->fetch_assoc() : null;

if ($userAccountResult instanceof mysqli_result) {
    mysqli_free_result($userAccountResult);
}

$stmtUserAccount->close();

if (!$userAccount) {
    atenea_render_auth_alert(
        'error',
        'Cuenta no encontrada',
        'No pudimos localizar la cuenta principal asociada al perfil público.',
        'logout.php?redirect=homepage.php'
    );
}

$hasBirthdateColumn = atenea_db_has_column($db, 'public_users', 'BIRTHDATE');
$hasProfilePhotoColumn = atenea_db_has_column($db, 'public_users', 'PROFILE_PHOTO');
$hasGoogleIdColumn = atenea_db_has_column($db, 'public_users', 'GOOGLE_ID');
$hasGoogleEmailColumn = atenea_db_has_column($db, 'public_users', 'GOOGLE_EMAIL');
$hasPublicUpdatedAtColumn = atenea_db_has_column($db, 'public_users', 'UPDATED_AT');
$hasPublicCreatedAtColumn = atenea_db_has_column($db, 'public_users', 'CREATED_AT');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accountAction = trim((string) ($_POST['account_action'] ?? ''));

    if ($accountAction === 'update_profile') {
        $firstName = trim((string) ($_POST['first_name'] ?? ''));
        $lastName = trim((string) ($_POST['last_name'] ?? ''));
        $phoneNumber = trim((string) ($_POST['phone_number'] ?? ''));
        $birthdate = trim((string) ($_POST['birthdate'] ?? ''));
        $billingValidation = atenea_validate_billing_profile_input(
            [
                'phone_number' => $phoneNumber,
                'billing_tipo_documento' => (string) ($_POST['billing_tipo_documento'] ?? ($profile['TIPO_DOCUMENTO'] ?? '')),
                'billing_numero_documento' => (string) ($_POST['billing_numero_documento'] ?? ($profile['NUMERO_DOCUMENTO'] ?? '')),
                'billing_departamento' => (string) ($_POST['billing_departamento'] ?? ''),
                'billing_municipio' => (string) ($_POST['billing_municipio'] ?? ''),
                'billing_distrito' => (string) ($_POST['billing_distrito'] ?? ''),
                'billing_address' => (string) ($_POST['billing_address'] ?? ''),
                'billing_nrc' => (string) ($profile['BILLING_NRC'] ?? ''),
                'billing_has_nrc' => !empty($profile['BILLING_NRC']) ? '1' : '',
            ],
            [
                'require_name' => false,
                'require_email' => false,
                'require_phone' => true,
                'require_document' => trim((string) ($profile['NUMERO_DOCUMENTO'] ?? '')) !== ''
                    || trim((string) ($profile['TIPO_DOCUMENTO'] ?? '')) !== '',
                'require_location' => true,
                'require_address' => true,
            ]
        );

        try {
            if ($firstName === '' || $lastName === '') {
                throw new RuntimeException('Debes completar tus nombres y apellidos para actualizar el perfil.');
            }

            if (!usuario_profile_is_valid_name($firstName) || !usuario_profile_is_valid_name($lastName)) {
                throw new RuntimeException('Los nombres y apellidos solo pueden contener letras, espacios y signos básicos.');
            }

            if ($phoneNumber !== '' && !preg_match('/^[0-9+\s().-]{7,25}$/', $phoneNumber)) {
                throw new RuntimeException('El teléfono o WhatsApp debe tener entre 7 y 25 caracteres válidos.');
            }

            if ($billingValidation['errors'] !== []) {
                throw new RuntimeException((string) $billingValidation['errors'][0]);
            }

            $billingData = $billingValidation['data'];
            $phoneNumber = (string) ($billingData['phone_number'] ?? '');
            $submittedDocumentType = (string) ($billingData['tipo_documento'] ?? '');
            $submittedDocumentNumber = (string) ($billingData['numero_documento'] ?? '');
            $storedDocumentType = strtoupper(trim((string) ($profile['TIPO_DOCUMENTO'] ?? '')));
            $storedDocumentDigits = preg_replace('/\D+/', '', (string) ($profile['NUMERO_DOCUMENTO'] ?? ''));
            if ($storedDocumentType === '' && is_string($storedDocumentDigits)) {
                if (strlen($storedDocumentDigits) === 14) {
                    $storedDocumentType = 'NIT';
                } elseif (strlen($storedDocumentDigits) === 9) {
                    $storedDocumentType = 'DUI';
                }
            }
            $storedDocumentNumber = atenea_billing_normalize_document(
                $storedDocumentType !== '' ? $storedDocumentType : 'DUI',
                (string) ($profile['NUMERO_DOCUMENTO'] ?? '')
            );

            if (($storedDocumentType !== '' || $storedDocumentNumber !== '')
                && ($storedDocumentType !== $submittedDocumentType || $storedDocumentNumber !== $submittedDocumentNumber)
            ) {
                throw new RuntimeException('El documento fiscal esta bloqueado y no se puede editar desde esta seccion.');
            }

            if ($birthdate !== '') {
                if (!$hasBirthdateColumn) {
                    throw new RuntimeException('La base de datos aún no tiene el campo de fecha de nacimiento. Ejecuta `Database/atenea_profile_updates.sql` y vuelve a intentarlo.');
                }

                $birthdateTime = strtotime($birthdate);
                if ($birthdateTime === false) {
                    throw new RuntimeException('La fecha de nacimiento no tiene un formato válido.');
                }

                if ($birthdateTime > time()) {
                    throw new RuntimeException('La fecha de nacimiento no puede estar en el futuro.');
                }

                $birthdate = date('Y-m-d', $birthdateTime);
            }

            $newProfilePhotoRelativePath = null;
            if (isset($_FILES['profile_photo']) && (int) ($_FILES['profile_photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                if (!$hasProfilePhotoColumn) {
                    throw new RuntimeException('La base de datos aún no tiene el campo para foto de perfil. Ejecuta `Database/atenea_profile_updates.sql` y vuelve a intentarlo.');
                }

                if ((int) $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
                    throw new RuntimeException('No fue posible subir la nueva foto de perfil.');
                }

                if ((int) $_FILES['profile_photo']['size'] > 2 * 1024 * 1024) {
                    throw new RuntimeException('La foto de perfil no puede exceder los 2 MB.');
                }

                $originalName = (string) ($_FILES['profile_photo']['name'] ?? '');
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($extension, $allowedExtensions, true)) {
                    throw new RuntimeException('La foto de perfil debe ser JPG, JPEG, PNG o WEBP.');
                }

                $mimeType = '';
                if (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    if ($finfo !== false) {
                        $mimeType = (string) finfo_file($finfo, (string) $_FILES['profile_photo']['tmp_name']);
                        finfo_close($finfo);
                    }
                }

                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if ($mimeType !== '' && !in_array($mimeType, $allowedMimeTypes, true)) {
                    throw new RuntimeException('El archivo seleccionado no es una imagen válida.');
                }

                $profileUploadDir = __DIR__ . '/../uploads/perfiles';
                if (!is_dir($profileUploadDir) && !mkdir($profileUploadDir, 0775, true) && !is_dir($profileUploadDir)) {
                    throw new RuntimeException('No fue posible preparar la carpeta de fotos de perfil.');
                }

                $newFileName = 'perfil_usuario_' . $memberId . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
                $destinationPath = $profileUploadDir . DIRECTORY_SEPARATOR . $newFileName;

                if (!move_uploaded_file((string) $_FILES['profile_photo']['tmp_name'], $destinationPath)) {
                    throw new RuntimeException('No fue posible guardar la nueva foto de perfil.');
                }

                $newProfilePhotoRelativePath = 'uploads/perfiles/' . $newFileName;
            }

            $setClauses = [
                'FIRST_NAME = ?',
                'LAST_NAME = ?',
                'PHONE_NUMBER = ?',
                'BILLING_NAME = ?',
                'BILLING_EMAIL = ?',
                'TIPO_DOCUMENTO = ?',
                'NUMERO_DOCUMENTO = ?',
                'BILLING_DEPARTAMENTO = ?',
                'BILLING_MUNICIPIO = ?',
                'BILLING_DISTRITO = ?',
                'BILLING_DIRECCION = ?',
                'BILLING_PROFILE_COMPLETED = ?',
            ];
            $types = 'sssssssssssi';
            $values = [
                $firstName,
                $lastName,
                $phoneNumber,
                trim($firstName . ' ' . $lastName),
                strtolower(trim((string) ($profile['EMAIL'] ?? ($_SESSION['EMAIL'] ?? ($profile['BILLING_EMAIL'] ?? ''))))),
                $submittedDocumentType,
                $submittedDocumentNumber,
                (string) ($billingData['billing_departamento'] ?? ''),
                (string) ($billingData['billing_municipio'] ?? ''),
                (string) ($billingData['billing_distrito'] ?? ''),
                (string) ($billingData['billing_direccion'] ?? ''),
                atenea_billing_profile_is_complete([
                    'phone_number' => $phoneNumber,
                    'tipo_documento' => $submittedDocumentType,
                    'numero_documento' => $submittedDocumentNumber,
                    'billing_departamento' => (string) ($billingData['billing_departamento'] ?? ''),
                    'billing_municipio' => (string) ($billingData['billing_municipio'] ?? ''),
                    'billing_direccion' => (string) ($billingData['billing_direccion'] ?? ''),
                ]) ? 1 : 0,
            ];

            if ($hasBirthdateColumn) {
                $setClauses[] = 'BIRTHDATE = ?';
                $types .= 's';
                $values[] = $birthdate !== '' ? $birthdate : null;
            }

            if ($newProfilePhotoRelativePath !== null && $hasProfilePhotoColumn) {
                $setClauses[] = 'PROFILE_PHOTO = ?';
                $types .= 's';
                $values[] = $newProfilePhotoRelativePath;
            }

            $sql = 'UPDATE public_users SET ' . implode(', ', $setClauses) . ' WHERE USER_ID = ? LIMIT 1';
            $stmtUpdateProfile = $db->prepare($sql);
            if (!$stmtUpdateProfile) {
                throw new RuntimeException('No fue posible preparar la actualización del perfil.');
            }

            $types .= 'i';
            $values[] = $memberId;
            $stmtUpdateProfile->bind_param($types, ...$values);

            if (!$stmtUpdateProfile->execute()) {
                throw new RuntimeException('No fue posible guardar los cambios del perfil.');
            }

            $stmtUpdateProfile->close();

            $freshUser = atenea_fetch_user_by_id($db, $memberId);
            if ($freshUser) {
                atenea_apply_session_data(
                    $freshUser,
                    (string) ($_SESSION['AUTH_PROVIDER'] ?? 'password'),
                    [
                        'email' => (string) ($_SESSION['GOOGLE_EMAIL'] ?? ($freshUser['GOOGLE_EMAIL'] ?? '')),
                        'sub' => (string) ($_SESSION['GOOGLE_SUB'] ?? ($freshUser['GOOGLE_ID'] ?? '')),
                    ]
                );
            }

            usuario_profile_tab_set('profile-editar');
            usuario_profile_flash_set('success', 'Perfil actualizado', 'Tus datos se guardaron correctamente.');
        } catch (Throwable $exception) {
            usuario_profile_tab_set('profile-editar');
            usuario_profile_flash_set('error', 'No pudimos actualizar tu perfil', $exception->getMessage(), true);
        }

        header('Location: usuario_vista.php');
        exit;
    }

    if ($accountAction === 'change_password') {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        try {
            $passwordErrors = usuario_profile_password_errors($newPassword, $confirmPassword);
            if ($passwordErrors !== []) {
                throw new RuntimeException(implode(' ', $passwordErrors));
            }

            $storedPasswordHash = trim((string) ($userAccount['PASSWORD'] ?? ''));
            $profileGoogleId = trim((string) ($profile['GOOGLE_ID'] ?? ''));
            $profileGoogleEmail = trim((string) ($profile['GOOGLE_EMAIL'] ?? ''));
            $isGoogleLinked = $profileGoogleId !== '' || $profileGoogleEmail !== '' || !empty($_SESSION['GOOGLE_EMAIL']) || !empty($_SESSION['GOOGLE_SUB']);
            $canSetLocalPasswordWithoutCurrent = $isGoogleLinked && trim($currentPassword) === '';

            if ($storedPasswordHash !== '' && !$canSetLocalPasswordWithoutCurrent) {
                // TODO: Migrar esta validación heredada de SHA1 a password_hash/password_verify.
                if (sha1($currentPassword) !== $storedPasswordHash) {
                    throw new RuntimeException('La contraseña actual no es correcta.');
                }
            }

            $stmtUpdatePassword = $db->prepare('UPDATE users SET PASSWORD = ? WHERE ID = ? LIMIT 1');
            if (!$stmtUpdatePassword) {
                throw new RuntimeException('No fue posible preparar el cambio de contraseña.');
            }

            // TODO: Migrar este almacenamiento heredado de SHA1 a password_hash/password_verify.
            $newPasswordHash = sha1($newPassword);
            $stmtUpdatePassword->bind_param('si', $newPasswordHash, $memberId);

            if (!$stmtUpdatePassword->execute()) {
                throw new RuntimeException('No fue posible guardar la nueva contraseña.');
            }

            $stmtUpdatePassword->close();

            usuario_profile_tab_set('profile-seguridad');
            usuario_profile_flash_set('success', 'Contraseña actualizada', 'Tu contraseña se guardó correctamente.');
        } catch (Throwable $exception) {
            usuario_profile_tab_set('profile-seguridad');
            usuario_profile_flash_set('error', 'No pudimos cambiar la contraseña', $exception->getMessage(), true);
        }

        header('Location: usuario_vista.php');
        exit;
    }
}

$profile = atenea_fetch_public_profile_by_user_id($db, $memberId);
$freshUser = atenea_fetch_user_by_id($db, $memberId);
if ($freshUser) {
    atenea_apply_session_data(
        $freshUser,
        (string) ($_SESSION['AUTH_PROVIDER'] ?? 'password'),
        [
            'email' => (string) ($_SESSION['GOOGLE_EMAIL'] ?? ($freshUser['GOOGLE_EMAIL'] ?? '')),
            'sub' => (string) ($_SESSION['GOOGLE_SUB'] ?? ($freshUser['GOOGLE_ID'] ?? '')),
        ]
    );
}

$fullName = trim((string) ($profile['FIRST_NAME'] ?? '') . ' ' . (string) ($profile['LAST_NAME'] ?? ''));
$email = (string) ($profile['EMAIL'] ?? ($_SESSION['EMAIL'] ?? ''));
$planStatus = (string) ($profile['PLAN_STATUS'] ?? ($_SESSION['PLAN_STATUS'] ?? 'pending'));
$planStatusLabel = $planStatus === 'active' ? 'Activo' : 'Pendiente de activar';
$accountStatus = (string) ($profile['ACCOUNT_STATUS'] ?? ($_SESSION['PUBLIC_ACCOUNT_STATUS'] ?? '1'));
$accountStatusLabel = (string) $accountStatus === '1' ? 'Cuenta activa' : 'Cuenta restringida';
$birthdate = $hasBirthdateColumn ? trim((string) ($profile['BIRTHDATE'] ?? ($_SESSION['BIRTHDATE'] ?? ''))) : '';
$billingDocumentType = strtoupper(trim((string) ($profile['TIPO_DOCUMENTO'] ?? '')));
$billingDocumentNumber = trim((string) ($profile['NUMERO_DOCUMENTO'] ?? ''));
$billingDocumentDigits = preg_replace('/\D+/', '', $billingDocumentNumber);
if ($billingDocumentType === '' && is_string($billingDocumentDigits)) {
    if (strlen($billingDocumentDigits) === 14) {
        $billingDocumentType = 'NIT';
    } elseif (strlen($billingDocumentDigits) === 9) {
        $billingDocumentType = 'DUI';
    }
}
$billingDepartment = trim((string) ($profile['BILLING_DEPARTAMENTO'] ?? ''));
$billingMunicipality = trim((string) ($profile['BILLING_MUNICIPIO'] ?? ''));
$billingDistrict = trim((string) ($profile['BILLING_DISTRITO'] ?? ''));
$billingAddress = trim((string) ($profile['BILLING_DIRECCION'] ?? ''));
$createdAt = $hasPublicCreatedAtColumn ? (string) ($profile['CREATED_AT'] ?? ($_SESSION['PUBLIC_CREATED_AT'] ?? '')) : '';
$updatedAt = $hasPublicUpdatedAtColumn ? (string) ($profile['UPDATED_AT'] ?? ($_SESSION['PUBLIC_UPDATED_AT'] ?? '')) : '';
$profilePhotoRelative = $hasProfilePhotoColumn ? trim((string) ($profile['PROFILE_PHOTO'] ?? ($_SESSION['PROFILE_PHOTO'] ?? ''))) : trim((string) ($_SESSION['PROFILE_PHOTO'] ?? ''));
$profilePhotoUrl = usuario_profile_photo_public_url($profilePhotoRelative);
$profileInitials = usuario_profile_initials($fullName);
$username = (string) ($userAccount['USERNAME'] ?? '');
$roleLabel = 'Usuario registrado';
$googleId = $hasGoogleIdColumn ? trim((string) ($profile['GOOGLE_ID'] ?? ($_SESSION['GOOGLE_SUB'] ?? ''))) : trim((string) ($_SESSION['GOOGLE_SUB'] ?? ''));
$googleEmail = $hasGoogleEmailColumn ? trim((string) ($profile['GOOGLE_EMAIL'] ?? ($_SESSION['GOOGLE_EMAIL'] ?? ''))) : trim((string) ($_SESSION['GOOGLE_EMAIL'] ?? ''));
$isGoogleLinked = $googleId !== '' || $googleEmail !== '' || (string) ($_SESSION['AUTH_PROVIDER'] ?? '') === 'google';
$flash = usuario_profile_flash_pull();
$shouldReopenModal = usuario_profile_should_reopen_modal();
$activeProfileTab = usuario_profile_active_tab_pull();

$programCount = atenea_db_has_table($db, 'programas_educativos')
    ? dashboard_count($db, "SELECT COUNT(*) FROM programas_educativos WHERE estado = 1")
    : 0;
$productCount = atenea_db_has_table($db, 'productos')
    ? dashboard_count($db, "SELECT COUNT(*) FROM productos WHERE estado = 1")
    : 0;
$paidOrdersCount = 0;

if (atenea_db_has_table($db, 'ordenes') && $email !== '') {
    $stmtPaidOrders = $db->prepare('SELECT COUNT(*) FROM ordenes WHERE billing_email = ? AND estado = ?');
    if ($stmtPaidOrders) {
        $paidStatus = 'paid';
        $stmtPaidOrders->bind_param('ss', $email, $paidStatus);
        $stmtPaidOrders->execute();
        $stmtPaidOrders->bind_result($paidOrdersCount);
        $stmtPaidOrders->fetch();
        $stmtPaidOrders->close();
        $paidOrdersCount = (int) $paidOrdersCount;
    }
}

$publicUserId = (int) ($_SESSION['PUBLIC_USER_ID'] ?? 0);
$phaseTwoReady = atenea_capacitacion_phase_two_ready($db);
$activeEnrollment = $phaseTwoReady ? atenea_capacitacion_fetch_active_enrollment_for_public_user($db, $publicUserId) : null;
$courseEnrollments = $phaseTwoReady ? atenea_capacitacion_fetch_enrollments_for_public_user($db, $publicUserId) : [];
$accessibleVideos = ($phaseTwoReady && $activeEnrollment)
    ? atenea_capacitacion_fetch_accessible_videos_for_public_user($db, $publicUserId, (int) ($activeEnrollment['programa_id'] ?? 0))
    : [];
$activeCourseStatusMeta = $activeEnrollment
    ? atenea_capacitacion_course_status_meta((string) ($activeEnrollment['estado_curso'] ?? ''))
    : ['label' => 'Sin curso activo', 'class' => 'secondary'];
$activeCourseTitle = $activeEnrollment ? (string) ($activeEnrollment['programa_titulo'] ?? 'Curso activo') : 'Sin curso activo';
$activeCourseProgress = $activeEnrollment ? atenea_capacitacion_progress_percentage($activeEnrollment['progreso'] ?? 0) : 0;

$navSections = atenea_capacitacion_user_nav_sections('usuario_vista.php'); /*
    [
        'title' => 'Panel',
        'items' => [
            ['label' => 'Inicio', 'href' => 'usuario_vista.php', 'icon' => 'dashboard', 'active' => true],
        ],
    ],
    [
        'title' => 'Explorar',
        'items' => [
            ['label' => 'Capacitación', 'href' => 'educacion.php', 'icon' => 'school'],
            ['label' => 'Productos', 'href' => 'productos.php', 'icon' => 'storefront'],
            ['label' => 'Carrito y pago', 'href' => 'carrito.php', 'icon' => 'shopping_cart'],
            ['label' => 'Historial de compras', 'href' => 'historial_compras.php', 'icon' => 'receipt_long', 'loaderText' => 'Cargando historial de compras...'],
            ['label' => 'Sitio público', 'href' => 'homepage.php', 'icon' => 'public'],
        ],
    ],
];

*/
$cards = [
    ['title' => 'Mi curso activo', 'value' => $activeCourseStatusMeta['label'], 'icon' => 'workspace_premium', 'accent' => 'success', 'href' => 'mi_curso_activo.php', 'metricLabel' => $activeCourseTitle, 'footerLabel' => 'Entrar al curso'],
    ['title' => 'Capacitacion', 'value' => $programCount, 'icon' => 'school', 'accent' => 'primary', 'href' => 'educacion.php', 'metricLabel' => 'Opciones disponibles', 'footerLabel' => 'Explorar oferta'],
    ['title' => 'Productos', 'value' => $productCount, 'icon' => 'storefront', 'accent' => 'success', 'href' => 'productos.php', 'metricLabel' => 'Items visibles', 'footerLabel' => 'Ir a tienda'],
    ['title' => 'Pagos confirmados', 'value' => $paidOrdersCount, 'icon' => 'payments', 'accent' => 'warning', 'href' => 'carrito.php', 'metricLabel' => 'Compras finalizadas', 'footerLabel' => 'Revisar carrito'],
];
/*
$cards = [
    ['title' => 'Capacitación', 'value' => $programCount, 'icon' => 'school', 'accent' => 'primary', 'href' => 'educacion.php', 'metricLabel' => 'Opciones disponibles', 'footerLabel' => 'Explorar oferta'],
    ['title' => 'Productos', 'value' => $productCount, 'icon' => 'storefront', 'accent' => 'success', 'href' => 'productos.php', 'metricLabel' => 'Ítems visibles', 'footerLabel' => 'Ir a tienda'],
    ['title' => 'Pagos confirmados', 'value' => $paidOrdersCount, 'icon' => 'payments', 'accent' => 'warning', 'href' => 'carrito.php', 'metricLabel' => 'Compras finalizadas', 'footerLabel' => 'Revisar carrito'],
];

*/
$quickLinks = [
    ['label' => 'Explorar Atenea', 'href' => 'homepage.php', 'icon' => 'public'],
    ['label' => 'Mi curso activo', 'href' => 'mi_curso_activo.php', 'icon' => 'workspace_premium'],
    ['label' => 'Videos del curso', 'href' => 'curso_videos.php', 'icon' => 'play_circle'],
    ['label' => 'Record escolar', 'href' => 'record_escolar.php', 'icon' => 'school'],
    ['label' => 'Ver capacitacion', 'href' => 'educacion.php', 'icon' => 'school'],
    ['label' => 'Comprar productos', 'href' => 'productos.php', 'icon' => 'storefront'],
    ['label' => 'Abrir carrito', 'href' => 'carrito.php', 'icon' => 'shopping_cart'],
    ['label' => 'Hablar con Atenea', 'href' => 'contacto.php', 'icon' => 'support_agent'],
];
/*
$quickLinks = [
    ['label' => 'Explorar Atenea', 'href' => 'homepage.php', 'icon' => 'public'],
    ['label' => 'Ver capacitación', 'href' => 'educacion.php', 'icon' => 'school'],
    ['label' => 'Comprar productos', 'href' => 'productos.php', 'icon' => 'storefront'],
    ['label' => 'Abrir carrito', 'href' => 'carrito.php', 'icon' => 'shopping_cart'],
    ['label' => 'Hablar con Atenea', 'href' => 'contacto.php', 'icon' => 'support_agent'],
];

*/
$summaryItems = [
    ['label' => 'Nombre', 'value' => usuario_profile_display_value($fullName, 'Pendiente de completar')],
    ['label' => 'Correo', 'value' => usuario_profile_display_value($email, 'No registrado')],
    ['label' => 'Cuenta', 'value' => $roleLabel],
    ['label' => 'Curso activo', 'value' => $activeCourseTitle],
    ['label' => 'Estado academico', 'value' => $activeCourseStatusMeta['label']],
    ['label' => 'Videos habilitados', 'value' => (string) count($accessibleVideos)],
    ['label' => 'Progreso del curso', 'value' => $activeCourseProgress . '%'],
    ['label' => 'Estado del plan', 'value' => $planStatusLabel],
    ['label' => 'Google', 'value' => $isGoogleLinked ? 'Conectado' : 'No conectado'],
    ['label' => 'Miembro desde', 'value' => usuario_profile_format_date($createdAt, 'No disponible')],
];
/*
$summaryItems = [
    ['label' => 'Nombre', 'value' => usuario_profile_display_value($fullName, 'Pendiente de completar')],
    ['label' => 'Correo', 'value' => usuario_profile_display_value($email, 'No registrado')],
    ['label' => 'Cuenta', 'value' => $roleLabel],
    ['label' => 'Estado del plan', 'value' => $planStatusLabel],
    ['label' => 'Google', 'value' => $isGoogleLinked ? 'Conectado' : 'No conectado'],
    ['label' => 'Miembro desde', 'value' => usuario_profile_format_date($createdAt, 'No disponible')],
];

*/
$accountMetaItems = [
    ['label' => 'Rol', 'value' => $roleLabel],
    ['label' => 'Estado', 'value' => $accountStatusLabel],
    ['label' => 'Miembro desde', 'value' => usuario_profile_format_date($createdAt, 'No disponible')],
];


ob_start();
?>
<div class="modal fade profile-modal-overlay" id="usuarioProfileModal" tabindex="-1" aria-labelledby="usuarioProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered atenea-profile-dialog">
    <div class="modal-content atenea-profile-modal profile-modal border-0">
      <div class="modal-header atenea-profile-modal__header border-0">
        <div class="atenea-profile-modal__heading">
          <p class="atenea-profile-modal__eyebrow">Panel de cuenta</p>
          <h4 class="modal-title mb-1" id="usuarioProfileModalLabel">Mi cuenta</h4>
          <p class="atenea-profile-modal__subtitle text-muted mb-0">Consulta y actualiza tu información principal, tu foto y la seguridad de acceso desde un solo lugar.</p>
        </div>
        <button type="button" class="atenea-profile-close profile-close-btn" data-bs-dismiss="modal" aria-label="Cerrar modal">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body atenea-profile-modal__body profile-modal-body">
        <div class="atenea-profile-shell">
          <section class="atenea-profile-hero profile-hero" aria-label="Resumen principal de la cuenta">
            <div class="atenea-profile-hero__avatar">
              <?php if ($profilePhotoUrl !== ''): ?>
                <img src="<?php echo dashboard_h($profilePhotoUrl); ?>" alt="Foto de perfil">
              <?php else: ?>
                <span><?php echo dashboard_h($profileInitials); ?></span>
              <?php endif; ?>
            </div>

            <div class="atenea-profile-hero__main profile-info">
              <div class="atenea-profile-hero__identity profile-identity">
                <p class="atenea-profile-hero__kicker">Perfil de usuario</p>
                <h3 class="atenea-profile-hero__name"><?php echo dashboard_h(usuario_profile_display_value($fullName, 'Pendiente de completar')); ?></h3>
                <p class="atenea-profile-hero__username">@<?php echo dashboard_h(usuario_profile_display_value($username, 'no-disponible')); ?></p>
                <p class="atenea-profile-hero__email">
                  <span class="material-symbols-rounded" aria-hidden="true">mail</span>
                  <span><?php echo dashboard_h(usuario_profile_display_value($email, 'No disponible')); ?></span>
                </p>
                <div class="atenea-profile-badges">
                  <span class="badge bg-gradient-success"><?php echo dashboard_h($roleLabel); ?></span>
                  <span class="badge bg-gradient-dark"><?php echo dashboard_h($accountStatusLabel); ?></span>
                  <span class="badge bg-gradient-warning text-dark"><?php echo dashboard_h($planStatusLabel); ?></span>
                </div>
              </div>
            </div>

            <div class="atenea-profile-hero__aside profile-side-cards" aria-label="Estado general de la cuenta">
              <div class="atenea-profile-hero__status profile-side-card">
                <div class="atenea-profile-side-card__icon" aria-hidden="true">
                  <span class="material-symbols-rounded">verified_user</span>
                </div>
                <div class="atenea-profile-side-card__content">
                  <span>Estado de la cuenta</span>
                  <strong><?php echo dashboard_h($accountStatusLabel); ?></strong>
                  <small>Tu acceso está habilitado y listo para usarse dentro de Atenea.</small>
                </div>
              </div>
              <div class="atenea-profile-hero__meta profile-side-card">
                <div class="atenea-profile-side-card__icon" aria-hidden="true">
                  <span class="material-symbols-rounded">link</span>
                </div>
                <div class="atenea-profile-side-card__content">
                  <span>Google</span>
                  <strong><?php echo dashboard_h($isGoogleLinked ? 'Cuenta conectada con Google' : 'Sin conexión con Google'); ?></strong>
                  <small><?php echo dashboard_h(usuario_profile_display_value($googleEmail, $isGoogleLinked ? 'Correo de Google no disponible' : 'Aún no hay una cuenta de Google vinculada.')); ?></small>
                </div>
              </div>
              <div class="atenea-profile-hero__meta profile-side-card">
                <div class="atenea-profile-side-card__icon" aria-hidden="true">
                  <span class="material-symbols-rounded">calendar_month</span>
                </div>
                <div class="atenea-profile-side-card__content">
                  <span>Miembro desde</span>
                  <strong><?php echo dashboard_h(usuario_profile_format_date($createdAt, 'No disponible')); ?></strong>
                  <small>Fecha de registro de tu cuenta en Atenea.</small>
                </div>
              </div>
            </div>
          </section>

          <div class="atenea-profile-tabs-wrap profile-tabs" role="tablist" aria-label="Secciones del perfil">
            <button
              type="button"
              class="atenea-profile-tab-btn<?php echo $activeProfileTab === 'profile-resumen' ? ' is-active' : ''; ?>"
              id="profile-resumen-tab"
              data-profile-tab-target="profile-resumen"
              role="tab"
              aria-controls="profile-resumen-panel"
              aria-selected="<?php echo $activeProfileTab === 'profile-resumen' ? 'true' : 'false'; ?>"
            >Información de cuenta</button>
            <button
              type="button"
              class="atenea-profile-tab-btn<?php echo $activeProfileTab === 'profile-editar' ? ' is-active' : ''; ?>"
              id="profile-editar-tab"
              data-profile-tab-target="profile-editar"
              role="tab"
              aria-controls="profile-editar-panel"
              aria-selected="<?php echo $activeProfileTab === 'profile-editar' ? 'true' : 'false'; ?>"
            >Editar perfil</button>
            <button
              type="button"
              class="atenea-profile-tab-btn<?php echo $activeProfileTab === 'profile-seguridad' ? ' is-active' : ''; ?>"
              id="profile-seguridad-tab"
              data-profile-tab-target="profile-seguridad"
              role="tab"
              aria-controls="profile-seguridad-panel"
              aria-selected="<?php echo $activeProfileTab === 'profile-seguridad' ? 'true' : 'false'; ?>"
            >Seguridad y contraseña</button>
            <button
              type="button"
              class="atenea-profile-tab-btn<?php echo $activeProfileTab === 'profile-google' ? ' is-active' : ''; ?>"
              id="profile-google-tab"
              data-profile-tab-target="profile-google"
              role="tab"
              aria-controls="profile-google-panel"
              aria-selected="<?php echo $activeProfileTab === 'profile-google' ? 'true' : 'false'; ?>"
            >Google</button>
          </div>

          <div class="atenea-profile-tab-panels profile-tab-content">
            <section
              class="atenea-profile-tab-panel<?php echo $activeProfileTab === 'profile-resumen' ? ' is-active' : ''; ?>"
              id="profile-resumen-panel"
              data-profile-tab-panel="profile-resumen"
              role="tabpanel"
              aria-labelledby="profile-resumen-tab"
              <?php echo $activeProfileTab === 'profile-resumen' ? '' : 'hidden'; ?>
            >
              <div class="atenea-profile-section atenea-profile-section--readonly">
                <div class="atenea-profile-section__header">
                  <h5>Información de cuenta</h5>
                  <p>Consulta tus datos principales, el estado actual del acceso y la información base con la que opera tu cuenta.</p>
                </div>
                <div class="profile-info-grid">
                  <article class="profile-info-item profile-info-item--wide atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                      <span class="material-symbols-rounded">badge</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                      <span>Nombre completo</span>
                      <strong><?php echo dashboard_h(usuario_profile_display_value($fullName, 'Pendiente de completar')); ?></strong>
                    </div>
                  </article>
                  <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                      <span class="material-symbols-rounded">alternate_email</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                      <span>Nombre de usuario</span>
                      <strong><?php echo dashboard_h(usuario_profile_display_value($username, 'No disponible')); ?></strong>
                    </div>
                  </article>
                  <article class="profile-info-item profile-info-item--wide atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                      <span class="material-symbols-rounded">mail</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                      <span>Correo</span>
                      <strong><?php echo dashboard_h(usuario_profile_display_value($email, 'No disponible')); ?></strong>
                    </div>
                  </article>
                  <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                      <span class="material-symbols-rounded">call</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                      <span>Teléfono o WhatsApp</span>
                      <strong><?php echo dashboard_h(usuario_profile_display_value($profile['PHONE_NUMBER'] ?? '', 'Pendiente de completar')); ?></strong>
                    </div>
                  </article>
                  <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                      <span class="material-symbols-rounded">credit_card</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                      <span>Documento</span>
                      <strong><?php echo dashboard_h(usuario_profile_display_value(trim($billingDocumentType . ($billingDocumentNumber !== '' ? ': ' . $billingDocumentNumber : '')), 'Pendiente de completar')); ?></strong>
                    </div>
                  </article>
                  <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                      <span class="material-symbols-rounded">map</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                      <span>Departamento</span>
                      <strong><?php echo dashboard_h(usuario_profile_display_value($billingDepartment, 'Pendiente de completar')); ?></strong>
                    </div>
                  </article>
                  <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                      <span class="material-symbols-rounded">location_city</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                      <span>Municipio</span>
                      <strong><?php echo dashboard_h(usuario_profile_display_value($billingMunicipality, 'Pendiente de completar')); ?></strong>
                      <?php if ($billingDistrict !== ''): ?>
                        <small><?php echo dashboard_h('Distrito/Ciudad: ' . $billingDistrict); ?></small>
                      <?php endif; ?>
                    </div>
                  </article>
                  <article class="profile-info-item profile-info-item--wide atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                      <span class="material-symbols-rounded">home</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                      <span>Direccion</span>
                      <strong><?php echo dashboard_h(usuario_profile_display_value($billingAddress, 'Pendiente de completar')); ?></strong>
                    </div>
                  </article>
                  <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                      <span class="material-symbols-rounded">cake</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                      <span>Fecha de nacimiento</span>
                      <strong><?php echo dashboard_h(usuario_profile_format_date($birthdate, 'Pendiente de completar')); ?></strong>
                    </div>
                  </article>
                  <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                      <span class="material-symbols-rounded">person</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                      <span>Tipo de cuenta</span>
                      <strong><?php echo dashboard_h($roleLabel); ?></strong>
                    </div>
                  </article>
                  <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                      <span class="material-symbols-rounded">shield</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                      <span>Estado</span>
                      <strong><?php echo dashboard_h($accountStatusLabel); ?></strong>
                    </div>
                  </article>
                  <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                      <span class="material-symbols-rounded">calendar_month</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                      <span>Miembro desde</span>
                      <strong><?php echo dashboard_h(usuario_profile_format_date($createdAt, 'No disponible', true)); ?></strong>
                    </div>
                  </article>
                  <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                      <span class="material-symbols-rounded">update</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                      <span>Última actualización</span>
                      <strong><?php echo dashboard_h(usuario_profile_format_date($updatedAt, 'No disponible', true)); ?></strong>
                    </div>
                  </article>
                </div>
                <div class="atenea-profile-inline-note atenea-profile-inline-note--google" role="note">
                  <div class="atenea-profile-inline-note__icon" aria-hidden="true">
                    <span class="material-symbols-rounded">info</span>
                  </div>
                  <div class="atenea-profile-inline-note__content">
                    <strong>Acceso con Google</strong>
                    <p class="mb-0"><?php echo dashboard_h($isGoogleLinked ? 'Tu cuenta ya está conectada con Google. Puedes seguir usando ese acceso y revisar los detalles desde la sección dedicada.' : 'Tu cuenta todavía no está conectada con Google. Cuando el flujo seguro esté disponible, podrás iniciarlo desde la sección dedicada.'); ?></p>
                  </div>
                  <button type="button" class="btn btn-outline-dark atenea-profile-btn atenea-profile-btn--secondary atenea-profile-btn--inline" data-profile-tab-target="profile-google">Ver detalles</button>
                </div>
              </div>
            </section>

            <section
              class="atenea-profile-tab-panel<?php echo $activeProfileTab === 'profile-editar' ? ' is-active' : ''; ?>"
              id="profile-editar-panel"
              data-profile-tab-panel="profile-editar"
              role="tabpanel"
              aria-labelledby="profile-editar-tab"
              <?php echo $activeProfileTab === 'profile-editar' ? '' : 'hidden'; ?>
            >
              <div class="atenea-profile-section atenea-profile-section--form">
                <div class="atenea-profile-section__header">
                  <h5>Editar perfil</h5>
                  <p>Actualiza tus datos personales y tu foto sin alterar la información sensible con la que se autentica tu cuenta.</p>
                </div>
                <form method="post" enctype="multipart/form-data" data-atenea-billing-form data-atenea-loading-form data-loader-text="Guardando perfil...">
                  <input type="hidden" name="account_action" value="update_profile">
                  <input type="hidden" name="billing_tipo_documento" value="<?php echo dashboard_h($billingDocumentType); ?>" data-document-type>
                  <input type="hidden" name="billing_distrito" value="<?php echo dashboard_h($billingDistrict); ?>" data-billing-district>
                  <div class="profile-form-grid">
                    <div class="profile-form-field atenea-profile-field">
                      <label class="form-label" for="profileFirstName">Nombres</label>
                      <input id="profileFirstName" class="form-control" type="text" name="first_name" maxlength="100" required value="<?php echo dashboard_h((string) ($profile['FIRST_NAME'] ?? '')); ?>">
                    </div>
                    <div class="profile-form-field atenea-profile-field">
                      <label class="form-label" for="profileLastName">Apellidos</label>
                      <input id="profileLastName" class="form-control" type="text" name="last_name" maxlength="100" required value="<?php echo dashboard_h((string) ($profile['LAST_NAME'] ?? '')); ?>">
                    </div>
                    <div class="profile-form-field atenea-profile-field">
                      <label class="form-label" for="profilePhone">Teléfono o WhatsApp</label>
                      <input id="profilePhone" class="form-control" type="text" name="phone_number" maxlength="25" required value="<?php echo dashboard_h((string) ($profile['PHONE_NUMBER'] ?? '')); ?>">
                    </div>
                    <div class="profile-form-field atenea-profile-field">
                      <label class="form-label" for="profileBirthdate">Fecha de nacimiento</label>
                      <input id="profileBirthdate" class="form-control" type="date" name="birthdate" value="<?php echo dashboard_h($birthdate); ?>">
                    </div>
                    <div class="profile-form-field atenea-profile-field">
                      <label class="form-label" for="profileDocumentType">Tipo de documento</label>
                      <input id="profileDocumentType" class="form-control" type="text" readonly value="<?php echo dashboard_h($billingDocumentType !== '' ? $billingDocumentType : 'No registrado'); ?>">
                    </div>
                    <div class="profile-form-field atenea-profile-field">
                      <label class="form-label" for="profileDocumentNumber">Numero de documento</label>
                      <input
                        id="profileDocumentNumber"
                        class="form-control"
                        type="text"
                        name="billing_numero_documento"
                        maxlength="25"
                        readonly
                        data-document-number
                        value="<?php echo dashboard_h($billingDocumentNumber); ?>"
                      >
                      <small class="atenea-profile-field__help" data-document-help>Tu documento fiscal se muestra solo como referencia y no puede editarse desde esta seccion.</small>
                    </div>
                    <div class="profile-form-field atenea-profile-field">
                      <label class="form-label" for="profileDepartment">Departamento</label>
                      <select
                        id="profileDepartment"
                        class="form-control"
                        name="billing_departamento"
                        data-billing-department
                        data-selected="<?php echo dashboard_h($billingDepartment); ?>"
                        required
                      ></select>
                    </div>
                    <div class="profile-form-field atenea-profile-field">
                      <label class="form-label" for="profileMunicipality">Municipio</label>
                      <select
                        id="profileMunicipality"
                        class="form-control"
                        name="billing_municipio"
                        data-billing-municipality
                        data-selected="<?php echo dashboard_h($billingMunicipality); ?>"
                        required
                      ></select>
                    </div>
                    <div class="profile-form-field profile-form-field--wide atenea-profile-field">
                      <label class="form-label" for="profileBillingAddress">Direccion completa</label>
                      <textarea id="profileBillingAddress" class="form-control" name="billing_address" maxlength="255" rows="3" required><?php echo dashboard_h($billingAddress); ?></textarea>
                    </div>
                    <div class="profile-form-field profile-form-field--wide atenea-profile-field">
                      <label class="form-label" for="profilePhoto">Foto de perfil</label>
                      <div class="atenea-profile-upload">
                        <input
                          id="profilePhoto"
                          class="form-control atenea-profile-file-input"
                          type="file"
                          name="profile_photo"
                          accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                          aria-describedby="profilePhotoHelp"
                        >
                        <label class="atenea-profile-upload__control" for="profilePhoto">
                          <span class="atenea-profile-upload__button">
                            <span class="material-symbols-rounded" aria-hidden="true">upload</span>
                            Seleccionar foto
                          </span>
                          <span class="atenea-profile-upload__filename is-placeholder" data-profile-file-name aria-live="polite">Ningún archivo seleccionado</span>
                        </label>
                        <small class="atenea-profile-field__help" id="profilePhotoHelp">JPG, PNG o WEBP. Máximo 2 MB.</small>
                      </div>
                    </div>
                  </div>
                  <div class="profile-form-actions atenea-profile-actions-bar">
                    <button type="button" class="btn btn-outline-dark atenea-profile-btn atenea-profile-btn--secondary" data-profile-tab-target="profile-resumen">Cancelar</button>
                    <button type="submit" class="btn btn-success atenea-profile-btn atenea-profile-btn--primary">Guardar cambios</button>
                  </div>
                </form>
              </div>
            </section>

            <section
              class="atenea-profile-tab-panel<?php echo $activeProfileTab === 'profile-seguridad' ? ' is-active' : ''; ?>"
              id="profile-seguridad-panel"
              data-profile-tab-panel="profile-seguridad"
              role="tabpanel"
              aria-labelledby="profile-seguridad-tab"
              <?php echo $activeProfileTab === 'profile-seguridad' ? '' : 'hidden'; ?>
            >
              <div class="atenea-profile-section atenea-profile-section--security">
                <div class="atenea-profile-section__header">
                  <h5>Seguridad y contraseña</h5>
                  <p>Cambia tu contraseña local desde una sección clara y separada del resto de tu información personal.</p>
                </div>
                <?php if ($isGoogleLinked): ?>
                  <div class="alert alert-light border mb-3 atenea-profile-inline-note" role="alert">
                    <div class="atenea-profile-inline-note__icon" aria-hidden="true">
                      <span class="material-symbols-rounded">info</span>
                    </div>
                    <div class="atenea-profile-inline-note__content">
                      <strong>Cuenta conectada con Google</strong>
                      <p class="mb-0">Puedes establecer una contraseña local si deseas iniciar sesión también con usuario y contraseña. Si tu cuenta nació con Google y nunca definiste una clave local, puedes dejar vacía la contraseña actual para crearla ahora.</p>
                    </div>
                  </div>
                <?php endif; ?>
                <form method="post" data-atenea-loading-form data-loader-text="Actualizando contraseña...">
                  <input type="hidden" name="account_action" value="change_password">
                  <div class="profile-form-grid">
                    <div class="profile-form-field atenea-profile-field">
                      <label class="form-label" for="currentPassword">Contraseña actual</label>
                      <div class="atenea-profile-password-input">
                        <input id="currentPassword" class="form-control" type="password" name="current_password" autocomplete="current-password">
                        <button type="button" class="atenea-profile-password-toggle" data-password-toggle="#currentPassword" aria-label="Mostrar contraseña actual" aria-pressed="false">
                          <span class="material-symbols-rounded" aria-hidden="true">visibility</span>
                        </button>
                      </div>
                    </div>
                    <div class="profile-form-field atenea-profile-field">
                      <label class="form-label" for="newPassword">Nueva contraseña</label>
                      <div class="atenea-profile-password-input">
                        <input
                          id="newPassword"
                          class="form-control"
                          type="password"
                          name="new_password"
                          autocomplete="new-password"
                          data-password-strength-input
                          data-password-strength-target="#passwordStrengthMeter"
                          data-password-strength-text="#passwordStrengthText"
                          data-password-strength-label="#passwordStrengthBadge"
                          data-password-checklist="#passwordChecklist"
                          required
                        >
                        <button type="button" class="atenea-profile-password-toggle" data-password-toggle="#newPassword" aria-label="Mostrar nueva contraseña" aria-pressed="false">
                          <span class="material-symbols-rounded" aria-hidden="true">visibility</span>
                        </button>
                      </div>
                    </div>
                    <div class="profile-form-field profile-form-field--wide atenea-profile-field">
                      <label class="form-label" for="confirmPassword">Confirmar nueva contraseña</label>
                      <div class="atenea-profile-password-input">
                        <input
                          id="confirmPassword"
                          class="form-control"
                          type="password"
                          name="confirm_password"
                          autocomplete="new-password"
                          data-password-confirm="#newPassword"
                          data-password-confirm-text="#passwordMatchText"
                          required
                        >
                        <button type="button" class="atenea-profile-password-toggle" data-password-toggle="#confirmPassword" aria-label="Mostrar confirmación de contraseña" aria-pressed="false">
                          <span class="material-symbols-rounded" aria-hidden="true">visibility</span>
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="atenea-password-panel mt-3">
                    <div class="atenea-password-panel__top">
                      <div>
                        <p class="atenea-password-panel__title">Seguridad de la contraseña</p>
                        <p class="atenea-password-panel__hint">Mantén las reglas actuales y procura cumplirlas todas antes de guardar.</p>
                      </div>
                      <span class="atenea-password-level" id="passwordStrengthBadge" data-state="empty" aria-live="polite">Sin evaluar</span>
                    </div>
                    <div class="atenea-password-meter" id="passwordStrengthMeter">
                      <span></span>
                      <span></span>
                      <span></span>
                      <span></span>
                    </div>
                    <div class="atenea-password-status">
                      <small id="passwordStrengthText" class="text-muted">La nueva contraseña aún no ha sido evaluada.</small>
                      <small id="passwordMatchText" class="text-muted">La confirmación debe coincidir exactamente.</small>
                    </div>
                    <ul class="atenea-password-checklist mt-3 mb-0" id="passwordChecklist">
                      <li data-rule="length">Mínimo 8 caracteres.</li>
                      <li data-rule="upper">Al menos una mayúscula.</li>
                      <li data-rule="lower">Al menos una minúscula.</li>
                      <li data-rule="number">Al menos un número.</li>
                      <li data-rule="symbol">Al menos un símbolo.</li>
                    </ul>
                  </div>
                  <div class="profile-form-actions atenea-profile-actions-bar">
                    <button type="button" class="btn btn-outline-dark atenea-profile-btn atenea-profile-btn--secondary" data-profile-tab-target="profile-resumen">Cancelar</button>
                    <button type="submit" class="btn btn-success atenea-profile-btn atenea-profile-btn--primary">Actualizar contraseña</button>
                  </div>
                </form>
              </div>
            </section>

            <section
              class="atenea-profile-tab-panel<?php echo $activeProfileTab === 'profile-google' ? ' is-active' : ''; ?>"
              id="profile-google-panel"
              data-profile-tab-panel="profile-google"
              role="tabpanel"
              aria-labelledby="profile-google-tab"
              <?php echo $activeProfileTab === 'profile-google' ? '' : 'hidden'; ?>
            >
              <div class="atenea-profile-section atenea-profile-section--google">
                <div class="atenea-profile-section__header">
                  <h5>Google</h5>
                  <p>Revisa el estado de conexión con Google sin mezclarlo con el resto de la configuración de tu perfil.</p>
                </div>
                <div class="atenea-profile-google-card profile-google-card">
                  <div class="atenea-profile-google-card__icon">G</div>
                  <div class="atenea-profile-google-card__content">
                    <strong><?php echo dashboard_h($isGoogleLinked ? 'Cuenta conectada con Google' : 'Sin conexión con Google'); ?></strong>
                    <p class="mb-0"><?php echo dashboard_h(usuario_profile_display_value($googleEmail, $isGoogleLinked ? 'Correo de Google no disponible' : 'Aún no hay una cuenta de Google vinculada.')); ?></p>
                  </div>
                </div>
                <div class="atenea-profile-google-note">
                  <?php if ($isGoogleLinked): ?>
                    Tu cuenta ya tiene una relación activa con Google. Si en el futuro se habilita una gestión directa desde este modal, la verás aquí.
                  <?php else: ?>
                    Puedes preparar la conexión con Google desde aquí sin alterar tu cuenta actual. El botón se mantiene visible hasta confirmar un flujo seguro de vinculación.
                  <?php endif; ?>
                </div>
                <div class="profile-form-actions atenea-profile-actions-bar">
                  <button type="button" class="btn btn-outline-dark atenea-profile-btn atenea-profile-btn--secondary" data-profile-tab-target="profile-resumen">Volver a la información</button>
                  <button type="button" class="btn btn-success atenea-profile-btn atenea-profile-btn--primary" id="googleConnectButton">
                    <?php echo $isGoogleLinked ? 'Gestionar conexión de Google' : 'Conectar con Google'; ?>
                  </button>
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="../js/sv-location-catalog.js"></script>
<script src="../js/atenea-billing.js"></script>
<script src="../js/atenea-password-strength.js" defer></script>
<script>
window.addEventListener('load', function () {
  var profileModalElement = document.getElementById('usuarioProfileModal');
  if (!profileModalElement) {
    return;
  }

  var profileModal = typeof bootstrap !== 'undefined'
    ? bootstrap.Modal.getOrCreateInstance(profileModalElement)
    : null;
  var activeProfileTab = <?php echo json_encode($activeProfileTab, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  var profileModalBody = profileModalElement.querySelector('.atenea-profile-modal__body');
  var tabButtons = Array.prototype.slice.call(profileModalElement.querySelectorAll('.atenea-profile-tab-btn'));
  var tabPanels = Array.prototype.slice.call(profileModalElement.querySelectorAll('[data-profile-tab-panel]'));
  var tabActions = Array.prototype.slice.call(profileModalElement.querySelectorAll('[data-profile-tab-target]'));

  function activateProfileTab(tabId) {
    if (!tabId) {
      return;
    }

    tabButtons.forEach(function (button) {
      var isActive = button.getAttribute('data-profile-tab-target') === tabId;
      button.classList.toggle('is-active', isActive);
      button.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    tabPanels.forEach(function (panel) {
      var isActive = panel.getAttribute('data-profile-tab-panel') === tabId;
      panel.classList.toggle('is-active', isActive);
      panel.hidden = !isActive;
    });

    if (profileModalBody) {
      profileModalBody.scrollTop = 0;
    }
  }

  tabActions.forEach(function (control) {
    control.addEventListener('click', function () {
      activateProfileTab(control.getAttribute('data-profile-tab-target'));
    });
  });

  activateProfileTab(activeProfileTab || 'profile-resumen');

  <?php if ($flash): ?>
  if (window.AteneaAlerts) {
    window.AteneaAlerts.<?php echo dashboard_h((string) ($flash['type'] ?? 'info')); ?>(
      <?php echo json_encode((string) ($flash['title'] ?? 'Atenea'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
      <?php echo json_encode((string) ($flash['message'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
    );
  }
  <?php endif; ?>

  <?php if ($shouldReopenModal): ?>
  if (profileModal) {
    profileModal.show();
  }
  <?php endif; ?>

  var googleConnectButton = document.getElementById('googleConnectButton');
  if (googleConnectButton) {
    googleConnectButton.addEventListener('click', function () {
      if (window.AteneaAlerts) {
        window.AteneaAlerts.info(
          'Integración de Google',
          'El flujo visual para conectar Google ya está preparado. La vinculación directa desde este modal requiere un nonce y una confirmación segura adicional, así que por ahora no se activa automáticamente para evitar una integración inconsistente.'
        );
      }
    });
  }

  var profilePhotoInput = profileModalElement.querySelector('#profilePhoto');
  var profileFileName = profileModalElement.querySelector('[data-profile-file-name]');

  function syncProfileFileName() {
    if (!profilePhotoInput || !profileFileName) {
      return;
    }

    var hasFile = profilePhotoInput.files && profilePhotoInput.files.length > 0;
    profileFileName.textContent = hasFile ? profilePhotoInput.files[0].name : 'Ningún archivo seleccionado';
    profileFileName.classList.toggle('is-placeholder', !hasFile);
  }

  if (profilePhotoInput) {
    profilePhotoInput.addEventListener('change', syncProfileFileName);
    syncProfileFileName();
  }

  Array.prototype.slice.call(profileModalElement.querySelectorAll('[data-password-toggle]')).forEach(function (toggleButton) {
    toggleButton.addEventListener('click', function () {
      var targetSelector = toggleButton.getAttribute('data-password-toggle');
      var targetInput = targetSelector ? profileModalElement.querySelector(targetSelector) : null;
      var icon = toggleButton.querySelector('.material-symbols-rounded');

      if (!targetInput) {
        return;
      }

      var shouldShow = targetInput.type === 'password';
      targetInput.type = shouldShow ? 'text' : 'password';
      toggleButton.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
      toggleButton.setAttribute('aria-label', shouldShow ? 'Ocultar contraseña' : 'Mostrar contraseña');

      if (icon) {
        icon.textContent = shouldShow ? 'visibility_off' : 'visibility';
      }
    });
  });
});
</script>
<?php
$extraBodyHtml = ob_get_clean();

dashboard_render_material_page([
    'bodyClass' => 'atenea-user-dashboard',
    'contentClass' => 'cecsb-dashboard-page',
    'stylesheets' => ['../css/usuario_vista.css'],
    'pageTitle' => 'Dashboard usuario',
    'roleLabel' => $roleLabel,
    'welcomeTitle' => 'Tu panel de acceso a Atenea',
    'welcomeText' => 'Desde aquí puedes explorar la oferta de capacitación, gestionar pagos y mantener tu perfil al día. Cuando tu plan académico esté activo, esta misma cuenta podrá redirigirte al dashboard estudiantil.',
    'welcomeText' => 'Desde aqui puedes explorar la oferta de capacitacion, gestionar pagos y entrar directamente a tu curso activo, sus videos y tu record escolar.',
    'profileUrl' => 'usuario_vista.php',
    'profileAction' => [
        'type' => 'modal',
        'target' => '#usuarioProfileModal',
        'enableTopTrigger' => true,
    ],
    'logoutUrl' => 'logout.php?redirect=homepage.php',
    'navSections' => $navSections,
    'cardsColumnClass' => 'col-12 col-md-6 col-xl-3 mb-4',
    'cards' => $cards,
    'quickLinks' => $quickLinks,
    'summaryItems' => $summaryItems,
    'accountMetaItems' => $accountMetaItems,
    'heroBadges' => [
        $planStatusLabel,
        $programCount . ' opciones de capacitación',
        $paidOrdersCount . ' pagos confirmados',
    ],
    'heroActions' => [
        ['label' => 'Ver página principal', 'href' => 'homepage.php', 'icon' => 'home'],
    ],
    'heroBadges' => [
        $activeCourseStatusMeta['label'],
        $planStatusLabel,
        count($accessibleVideos) . ' videos habilitados',
        $paidOrdersCount . ' pagos confirmados',
    ],
    'heroActions' => [
        ['label' => 'Mi curso activo', 'href' => 'mi_curso_activo.php', 'icon' => 'workspace_premium'],
        ['label' => 'Videos del curso', 'href' => 'curso_videos.php', 'icon' => 'play_circle', 'variant' => 'outline'],
        ['label' => 'Ver pagina principal', 'href' => 'homepage.php', 'icon' => 'home'],
    ],
    'extraBodyHtml' => $extraBodyHtml,
]);
