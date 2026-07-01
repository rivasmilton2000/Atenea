<?php

if (!function_exists('atenea_google_client_ids')) {
    function atenea_google_client_ids(): array
    {
        static $ids;

        if (is_array($ids)) {
            return $ids;
        }

        $ids = [];
        $configPath = __DIR__ . '/google_auth_config.php';

        if (is_file($configPath)) {
            $config = require $configPath;
            if (is_array($config)) {
                $configuredIds = $config['client_ids'] ?? [];
                if (is_string($configuredIds)) {
                    $configuredIds = [$configuredIds];
                }

                if (is_array($configuredIds)) {
                    $ids = array_merge($ids, $configuredIds);
                }
            }
        }

        foreach ([getenv('ATENEA_GOOGLE_CLIENT_ID'), getenv('GOOGLE_CLIENT_ID')] as $envId) {
            if (is_string($envId) && trim($envId) !== '') {
                $ids[] = trim($envId);
            }
        }

        $ids = array_values(array_unique(array_filter(array_map('trim', $ids), static function ($value): bool {
            if ($value === '') {
                return false;
            }

            return stripos($value, 'YOUR_GOOGLE_CLIENT_ID') === false;
        })));

        return $ids;
    }
}

if (!function_exists('atenea_google_client_id')) {
    function atenea_google_client_id(): string
    {
        $clientIds = atenea_google_client_ids();

        return $clientIds[0] ?? '';
    }
}

if (!function_exists('atenea_google_is_enabled')) {
    function atenea_google_is_enabled(): bool
    {
        return atenea_google_client_id() !== '';
    }
}

if (!defined('ATENEA_SESSION_TIMEOUT_SECONDS')) {
    define('ATENEA_SESSION_TIMEOUT_SECONDS', 30 * 60);
}

if (!function_exists('atenea_session_timeout_seconds')) {
    function atenea_session_timeout_seconds(): int
    {
        return max(0, (int) ATENEA_SESSION_TIMEOUT_SECONDS);
    }
}

if (!function_exists('atenea_session_is_authenticated')) {
    function atenea_session_is_authenticated(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['MEMBER_ID']);
    }
}

if (!function_exists('atenea_destroy_active_session')) {
    function atenea_destroy_active_session(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies') && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }

        session_destroy();
    }
}

if (!function_exists('atenea_handle_session_timeout')) {
    function atenea_handle_session_timeout(array $options = []): bool
    {
        if (!atenea_session_is_authenticated()) {
            return false;
        }

        $timeout = atenea_session_timeout_seconds();
        $now = time();
        $lastActivity = isset($_SESSION['last_activity']) ? (int) $_SESSION['last_activity'] : 0;

        if ($lastActivity > 0 && ($now - $lastActivity) > $timeout) {
            atenea_destroy_active_session();

            if (!empty($options['redirect_on_expire']) && !headers_sent()) {
                $redirectUrl = (string) ($options['redirect_url'] ?? 'login.php?expired=1');
                header('Location: ' . $redirectUrl);
                exit;
            }

            return true;
        }

        $_SESSION['last_activity'] = $now;

        return false;
    }
}

if (!function_exists('atenea_db_has_column')) {
    function atenea_db_has_column(mysqli $db, string $table, string $column): bool
    {
        $tableName = str_replace('`', '``', $table);
        $columnName = str_replace('`', '``', $column);
        $sql = "SHOW COLUMNS FROM `{$tableName}` LIKE '{$columnName}'";
        $result = mysqli_query($db, $sql);
        $exists = $result instanceof mysqli_result && $result->num_rows > 0;

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        return $exists;
    }
}

if (!function_exists('atenea_db_has_table')) {
    function atenea_db_has_table(mysqli $db, string $table): bool
    {
        $table = trim($table);
        if ($table === '') {
            return false;
        }

        $tableName = str_replace('`', '``', $table);
        $result = mysqli_query($db, "SHOW TABLES LIKE '{$tableName}'");
        $exists = $result instanceof mysqli_result && $result->num_rows > 0;

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        return $exists;
    }
}

if (!function_exists('atenea_ensure_public_user_schema')) {
    function atenea_ensure_public_user_schema(mysqli $db): bool
    {
        static $initialized = false;

        $sql = "CREATE TABLE IF NOT EXISTS `public_users` (
                    `PUBLIC_USER_ID` INT(11) NOT NULL AUTO_INCREMENT,
                    `USER_ID` INT(11) NOT NULL,
                    `FIRST_NAME` VARCHAR(100) NOT NULL,
                    `LAST_NAME` VARCHAR(100) NOT NULL,
                    `EMAIL` VARCHAR(150) NOT NULL,
                    `PHONE_NUMBER` VARCHAR(25) DEFAULT NULL,
                    `BIRTHDATE` DATE DEFAULT NULL,
                    `PROFILE_PHOTO` VARCHAR(255) DEFAULT NULL,
                    `GOOGLE_ID` VARCHAR(191) DEFAULT NULL,
                    `GOOGLE_EMAIL` VARCHAR(150) DEFAULT NULL,
                    `BILLING_NAME` VARCHAR(150) DEFAULT NULL,
                    `BILLING_EMAIL` VARCHAR(150) DEFAULT NULL,
                    `TIPO_DOCUMENTO` VARCHAR(10) DEFAULT NULL,
                    `NUMERO_DOCUMENTO` VARCHAR(25) DEFAULT NULL,
                    `BILLING_DEPARTAMENTO` VARCHAR(100) DEFAULT NULL,
                    `BILLING_MUNICIPIO` VARCHAR(100) DEFAULT NULL,
                    `BILLING_DISTRITO` VARCHAR(100) DEFAULT NULL,
                    `BILLING_DIRECCION` TEXT DEFAULT NULL,
                    `BILLING_NRC` VARCHAR(20) DEFAULT NULL,
                    `BILLING_PROFILE_COMPLETED` TINYINT(1) NOT NULL DEFAULT 0,
                    `PLAN_STATUS` VARCHAR(30) NOT NULL DEFAULT 'pending',
                    `ACCOUNT_STATUS` TINYINT(1) NOT NULL DEFAULT 1,
                    `CREATED_AT` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `UPDATED_AT` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`PUBLIC_USER_ID`),
                    UNIQUE KEY `uq_public_users_user` (`USER_ID`),
                    UNIQUE KEY `uq_public_users_email` (`EMAIL`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (mysqli_query($db, $sql) !== true) {
            return false;
        }

        if (!atenea_db_has_table($db, 'public_users')) {
            return false;
        }

        if (!$initialized) {
            $columns = [
                'BIRTHDATE' => 'DATE DEFAULT NULL AFTER `PHONE_NUMBER`',
                'PROFILE_PHOTO' => 'VARCHAR(255) DEFAULT NULL AFTER `BIRTHDATE`',
                'GOOGLE_ID' => 'VARCHAR(191) DEFAULT NULL AFTER `PROFILE_PHOTO`',
                'GOOGLE_EMAIL' => 'VARCHAR(150) DEFAULT NULL AFTER `GOOGLE_ID`',
                'BILLING_NAME' => 'VARCHAR(150) DEFAULT NULL AFTER `GOOGLE_EMAIL`',
                'BILLING_EMAIL' => 'VARCHAR(150) DEFAULT NULL AFTER `BILLING_NAME`',
                'TIPO_DOCUMENTO' => 'VARCHAR(10) DEFAULT NULL AFTER `BILLING_EMAIL`',
                'NUMERO_DOCUMENTO' => 'VARCHAR(25) DEFAULT NULL AFTER `TIPO_DOCUMENTO`',
                'BILLING_DEPARTAMENTO' => 'VARCHAR(100) DEFAULT NULL AFTER `NUMERO_DOCUMENTO`',
                'BILLING_MUNICIPIO' => 'VARCHAR(100) DEFAULT NULL AFTER `BILLING_DEPARTAMENTO`',
                'BILLING_DISTRITO' => 'VARCHAR(100) DEFAULT NULL AFTER `BILLING_MUNICIPIO`',
                'BILLING_DIRECCION' => 'TEXT DEFAULT NULL AFTER `BILLING_DISTRITO`',
                'BILLING_NRC' => 'VARCHAR(20) DEFAULT NULL AFTER `BILLING_DIRECCION`',
                'BILLING_PROFILE_COMPLETED' => 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `BILLING_NRC`',
            ];

            foreach ($columns as $column => $definition) {
                if (atenea_db_has_column($db, 'public_users', $column)) {
                    continue;
                }

                $columnName = str_replace('`', '``', $column);
                $alter = "ALTER TABLE `public_users` ADD COLUMN `{$columnName}` {$definition}";
                if (mysqli_query($db, $alter) !== true) {
                    return false;
                }
            }

            $initialized = true;
        }

        return true;
    }
}

if (!function_exists('atenea_public_user_select_expr')) {
    function atenea_public_user_select_expr(mysqli $db, string $column, string $alias, string $fallbackSql = "''"): string
    {
        $safeAlias = str_replace('`', '``', $alias);
        if (!atenea_db_has_column($db, 'public_users', $column)) {
            return $fallbackSql . " AS `{$safeAlias}`";
        }

        $safeColumn = str_replace('`', '``', $column);

        return "pu.`{$safeColumn}` AS `{$safeAlias}`";
    }
}

if (!function_exists('atenea_auth_select_sql')) {
    function atenea_auth_select_sql(mysqli $db, string $whereClause): string
    {
        atenea_ensure_public_user_schema($db);

        $conditions = [$whereClause];

        if (atenea_db_has_column($db, 'users', 'U_ESTADO')) {
            $conditions[] = 'u.U_ESTADO = 1';
        }

        $employeeState = atenea_db_has_column($db, 'employee', 'E_ESTADO') ? 'e.E_ESTADO = 1' : '1 = 1';
        $studentState = atenea_db_has_column($db, 'estudiantes', 'estado_estudiante') ? 'es.estado_estudiante = 1' : '1 = 1';
        $publicState = atenea_db_has_column($db, 'public_users', 'ACCOUNT_STATUS') ? 'pu.ACCOUNT_STATUS = 1' : '1 = 1';

        $profilePhotoExpr = atenea_public_user_select_expr($db, 'PROFILE_PHOTO', 'PROFILE_PHOTO');
        $birthdateExpr = atenea_public_user_select_expr($db, 'BIRTHDATE', 'BIRTHDATE', 'NULL');
        $googleIdExpr = atenea_public_user_select_expr($db, 'GOOGLE_ID', 'GOOGLE_ID');
        $googleEmailExpr = atenea_public_user_select_expr($db, 'GOOGLE_EMAIL', 'GOOGLE_EMAIL');
        $billingNameExpr = atenea_public_user_select_expr($db, 'BILLING_NAME', 'BILLING_NAME');
        $billingEmailExpr = atenea_public_user_select_expr($db, 'BILLING_EMAIL', 'BILLING_EMAIL');
        $billingDocumentTypeExpr = atenea_public_user_select_expr($db, 'TIPO_DOCUMENTO', 'TIPO_DOCUMENTO');
        $billingDocumentNumberExpr = atenea_public_user_select_expr($db, 'NUMERO_DOCUMENTO', 'NUMERO_DOCUMENTO');
        $billingDepartmentExpr = atenea_public_user_select_expr($db, 'BILLING_DEPARTAMENTO', 'BILLING_DEPARTAMENTO');
        $billingMunicipalityExpr = atenea_public_user_select_expr($db, 'BILLING_MUNICIPIO', 'BILLING_MUNICIPIO');
        $billingDistrictExpr = atenea_public_user_select_expr($db, 'BILLING_DISTRITO', 'BILLING_DISTRITO');
        $billingAddressExpr = atenea_public_user_select_expr($db, 'BILLING_DIRECCION', 'BILLING_DIRECCION');
        $billingNrcExpr = atenea_public_user_select_expr($db, 'BILLING_NRC', 'BILLING_NRC');
        $billingProfileCompletedExpr = atenea_public_user_select_expr($db, 'BILLING_PROFILE_COMPLETED', 'BILLING_PROFILE_COMPLETED', '0');
        $accountStatusExpr = atenea_public_user_select_expr($db, 'ACCOUNT_STATUS', 'PUBLIC_ACCOUNT_STATUS', '1');
        $createdAtExpr = atenea_public_user_select_expr($db, 'CREATED_AT', 'PUBLIC_CREATED_AT', 'NULL');
        $updatedAtExpr = atenea_public_user_select_expr($db, 'UPDATED_AT', 'PUBLIC_UPDATED_AT', 'NULL');

        $conditions[] = "((
                u.EMPLOYEE_ID IS NOT NULL
                AND u.EMPLOYEE_ID <> 0
                AND {$employeeState}
            ) OR (
                u.ESTUDIANTE_ID IS NOT NULL
                AND u.ESTUDIANTE_ID <> 0
                AND {$studentState}
            ) OR (
                pu.PUBLIC_USER_ID IS NOT NULL
                AND {$publicState}
            ))";

        return "SELECT
                    u.ID,
                    u.USERNAME,
                    u.EMPLOYEE_ID,
                    u.ESTUDIANTE_ID,
                    pu.PUBLIC_USER_ID,
                    COALESCE(e.FIRST_NAME, pu.FIRST_NAME, '') AS FIRST_NAME,
                    COALESCE(e.LAST_NAME, pu.LAST_NAME, '') AS LAST_NAME,
                    COALESCE(e.GENDER, '') AS GENDER,
                    COALESCE(e.EMAIL, pu.EMAIL, '') AS EMAIL,
                    COALESCE(e.PHONE_NUMBER, pu.PHONE_NUMBER, '') AS PHONE_NUMBER,
                    j.JOB_TITLE,
                    l.PROVINCE,
                    l.CITY,
                    COALESCE(t.TYPE, '') AS TYPE,
                    COALESCE(pu.PLAN_STATUS, 'pending') AS PLAN_STATUS,
                    {$profilePhotoExpr},
                    {$birthdateExpr},
                    {$googleIdExpr},
                    {$googleEmailExpr},
                    {$billingNameExpr},
                    {$billingEmailExpr},
                    {$billingDocumentTypeExpr},
                    {$billingDocumentNumberExpr},
                    {$billingDepartmentExpr},
                    {$billingMunicipalityExpr},
                    {$billingDistrictExpr},
                    {$billingAddressExpr},
                    {$billingNrcExpr},
                    {$billingProfileCompletedExpr},
                    {$accountStatusExpr},
                    {$createdAtExpr},
                    {$updatedAtExpr},
                    es.nombres_estudiante,
                    es.apellidos_estudiante,
                    es.direccion_estudiante,
                    es.correo_estudiante,
                    es.foto_estudiante,
                    es.fecha_nac_estudiante,
                    es.edad_estudiante,
                    es.genero_estudiante,
                    es.grado_id_estudiante,
                    es.carnet_estudiante,
                    es.numero_lista_estudiante,
                    es.info_medica_estudiante,
                    es.fecha_reg_estudiante,
                    es.u_acceso_estudiante,
                    es.nombres_encargado,
                    es.apellidos_encargado,
                    es.dui_encargado,
                    es.direccion_encargado,
                    es.correo_encargado,
                    es.trabajo_encargado,
                    es.numero_cel_encargado,
                    es.numero_tel_encargado,
                    es.genero_encargado,
                    es.fecha_nac_encargado
                FROM users u
                LEFT JOIN employee e ON e.EMPLOYEE_ID = u.EMPLOYEE_ID
                LEFT JOIN location l ON e.LOCATION_ID = l.LOCATION_ID
                LEFT JOIN job j ON e.JOB_ID = j.JOB_ID
                LEFT JOIN type t ON t.TYPE_ID = u.TYPE_ID
                LEFT JOIN estudiantes es ON es.ESTUDIANTE_ID = u.ESTUDIANTE_ID
                LEFT JOIN public_users pu ON pu.USER_ID = u.ID
                WHERE " . implode(' AND ', $conditions) . "
                LIMIT 1";
    }
}

if (!function_exists('atenea_fetch_user_by_credentials')) {
    function atenea_fetch_user_by_credentials(mysqli $db, string $username, string $passwordHash): ?array
    {
        $sql = atenea_auth_select_sql($db, 'u.USERNAME = ? AND u.PASSWORD = ?');
        $stmt = $db->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('ss', $username, $passwordHash);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result instanceof mysqli_result ? $result->fetch_assoc() : null;

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        $stmt->close();

        return $user ?: null;
    }
}

if (!function_exists('atenea_fetch_user_by_email')) {
    function atenea_fetch_user_by_email(mysqli $db, string $email): ?array
    {
        $sql = atenea_auth_select_sql(
            $db,
            '(LOWER(e.EMAIL) = LOWER(?) OR LOWER(es.correo_estudiante) = LOWER(?) OR LOWER(pu.EMAIL) = LOWER(?))'
        );
        $stmt = $db->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('sss', $email, $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result instanceof mysqli_result ? $result->fetch_assoc() : null;

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        $stmt->close();

        return $user ?: null;
    }
}

if (!function_exists('atenea_fetch_user_by_id')) {
    function atenea_fetch_user_by_id(mysqli $db, int $userId): ?array
    {
        $sql = atenea_auth_select_sql($db, 'u.ID = ?');
        $stmt = $db->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result instanceof mysqli_result ? $result->fetch_assoc() : null;

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        $stmt->close();

        return $user ?: null;
    }
}

if (!function_exists('atenea_user_is_student')) {
    function atenea_user_is_student(array $user): bool
    {
        return (int) ($user['ESTUDIANTE_ID'] ?? 0) > 0 && (int) ($user['EMPLOYEE_ID'] ?? 0) === 0;
    }
}

if (!function_exists('atenea_user_is_public')) {
    function atenea_user_is_public(array $user): bool
    {
        return (int) ($user['PUBLIC_USER_ID'] ?? 0) > 0
            && (int) ($user['EMPLOYEE_ID'] ?? 0) === 0
            && (int) ($user['ESTUDIANTE_ID'] ?? 0) === 0;
    }
}

if (!function_exists('atenea_session_is_public_user')) {
    function atenea_session_is_public_user(): bool
    {
        return !empty($_SESSION['PUBLIC_USER_ID'])
            && empty($_SESSION['EMPLOYEE_ID'])
            && empty($_SESSION['ESTUDIANTE_ID']);
    }
}

if (!function_exists('atenea_user_display_name')) {
    function atenea_user_display_name(array $user, bool $short = false): string
    {
        if (atenea_user_is_student($user)) {
            $fullName = trim((string) ($user['nombres_estudiante'] ?? '') . ' ' . (string) ($user['apellidos_estudiante'] ?? ''));
            if ($short) {
                return trim((string) ($user['nombres_estudiante'] ?? ''));
            }

            return $fullName;
        }

        $fullName = trim((string) ($user['FIRST_NAME'] ?? '') . ' ' . (string) ($user['LAST_NAME'] ?? ''));
        if ($short) {
            return trim((string) ($user['FIRST_NAME'] ?? ''));
        }

        return $fullName;
    }
}

if (!function_exists('atenea_dashboard_route_for_type')) {
    function atenea_dashboard_route_for_type(string $type): string
    {
        switch ($type) {
            case 'Admin':
                return 'dashboard_admin.php';
            case 'Personal':
                return 'empleados_vista.php';
            case 'Docente':
                return 'docentes_vista.php';
            case 'SuperAdmin':
                return 'sa_vista.php';
            case 'Estudiante':
                return 'estudiante_vista.php';
            default:
                return 'homepage.php';
        }
    }
}

if (!function_exists('atenea_dashboard_route_for_user')) {
    function atenea_dashboard_route_for_user(array $user): string
    {
        if (atenea_user_is_public($user)) {
            return 'usuario_vista.php';
        }

        if (atenea_user_is_student($user)) {
            return 'estudiante_vista.php';
        }

        return atenea_dashboard_route_for_type((string) ($user['TYPE'] ?? ''));
    }
}

if (!function_exists('atenea_dashboard_route_for_session')) {
    function atenea_dashboard_route_for_session(): string
    {
        if (atenea_session_is_public_user()) {
            return 'usuario_vista.php';
        }

        if (!empty($_SESSION['ESTUDIANTE_ID']) && empty($_SESSION['EMPLOYEE_ID'])) {
            return 'estudiante_vista.php';
        }

        return atenea_dashboard_route_for_type((string) ($_SESSION['TYPE'] ?? ''));
    }
}

if (!function_exists('atenea_dashboard_label_for_session')) {
    function atenea_dashboard_label_for_session(): string
    {
        if (atenea_session_is_public_user()) {
            return 'Mi cuenta';
        }

        switch ((string) ($_SESSION['TYPE'] ?? '')) {
            case 'Estudiante':
                return 'Aula Virtual';
            case 'Docente':
                return 'Panel docente';
            case 'Admin':
            case 'SuperAdmin':
                return 'Panel administrativo';
            case 'Personal':
                return 'Panel personal';
            default:
                return 'Mi panel';
        }
    }
}

if (!function_exists('atenea_apply_session_data')) {
    function atenea_apply_session_data(array $user, string $provider = 'password', array $providerMeta = []): void
    {
        $_SESSION['MEMBER_ID'] = (int) ($user['ID'] ?? 0);
        $_SESSION['EMPLOYEE_ID'] = (int) ($user['EMPLOYEE_ID'] ?? 0);
        $_SESSION['ESTUDIANTE_ID'] = (int) ($user['ESTUDIANTE_ID'] ?? 0);
        $_SESSION['PUBLIC_USER_ID'] = (int) ($user['PUBLIC_USER_ID'] ?? 0);
        $_SESSION['FIRST_NAME'] = (string) ($user['FIRST_NAME'] ?? '');
        $_SESSION['LAST_NAME'] = (string) ($user['LAST_NAME'] ?? '');
        $_SESSION['GENDER'] = (string) ($user['GENDER'] ?? '');
        $_SESSION['EMAIL'] = (string) ($user['EMAIL'] ?? '');
        $_SESSION['PHONE_NUMBER'] = (string) ($user['PHONE_NUMBER'] ?? '');
        $_SESSION['JOB_TITLE'] = (string) ($user['JOB_TITLE'] ?? '');
        $_SESSION['PROVINCE'] = (string) ($user['PROVINCE'] ?? '');
        $_SESSION['CITY'] = (string) ($user['CITY'] ?? '');
        $_SESSION['TYPE'] = (string) ($user['TYPE'] ?? '');
        $_SESSION['PLAN_STATUS'] = (string) ($user['PLAN_STATUS'] ?? 'pending');
        $_SESSION['PROFILE_PHOTO'] = (string) ($user['PROFILE_PHOTO'] ?? '');
        $_SESSION['BIRTHDATE'] = (string) ($user['BIRTHDATE'] ?? '');
        $_SESSION['GOOGLE_ID'] = (string) ($user['GOOGLE_ID'] ?? '');
        $_SESSION['BILLING_NAME'] = (string) ($user['BILLING_NAME'] ?? '');
        $_SESSION['BILLING_EMAIL'] = (string) ($user['BILLING_EMAIL'] ?? '');
        $_SESSION['TIPO_DOCUMENTO'] = (string) ($user['TIPO_DOCUMENTO'] ?? '');
        $_SESSION['NUMERO_DOCUMENTO'] = (string) ($user['NUMERO_DOCUMENTO'] ?? '');
        $_SESSION['BILLING_DEPARTAMENTO'] = (string) ($user['BILLING_DEPARTAMENTO'] ?? '');
        $_SESSION['BILLING_MUNICIPIO'] = (string) ($user['BILLING_MUNICIPIO'] ?? '');
        $_SESSION['BILLING_DISTRITO'] = (string) ($user['BILLING_DISTRITO'] ?? '');
        $_SESSION['BILLING_DIRECCION'] = (string) ($user['BILLING_DIRECCION'] ?? '');
        $_SESSION['BILLING_NRC'] = (string) ($user['BILLING_NRC'] ?? '');
        $_SESSION['BILLING_PROFILE_COMPLETED'] = (string) ($user['BILLING_PROFILE_COMPLETED'] ?? '0');
        $_SESSION['PUBLIC_ACCOUNT_STATUS'] = (string) ($user['PUBLIC_ACCOUNT_STATUS'] ?? '1');
        $_SESSION['PUBLIC_CREATED_AT'] = (string) ($user['PUBLIC_CREATED_AT'] ?? '');
        $_SESSION['PUBLIC_UPDATED_AT'] = (string) ($user['PUBLIC_UPDATED_AT'] ?? '');

        $_SESSION['nombres_estudiante'] = (string) ($user['nombres_estudiante'] ?? '');
        $_SESSION['apellidos_estudiante'] = (string) ($user['apellidos_estudiante'] ?? '');
        $_SESSION['direccion_estudiante'] = (string) ($user['direccion_estudiante'] ?? '');
        $_SESSION['correo_estudiante'] = (string) ($user['correo_estudiante'] ?? '');
        $_SESSION['foto_estudiante'] = (string) ($user['foto_estudiante'] ?? '');
        $_SESSION['fecha_nac_estudiante'] = (string) ($user['fecha_nac_estudiante'] ?? '');
        $_SESSION['edad_estudiante'] = (string) ($user['edad_estudiante'] ?? '');
        $_SESSION['genero_estudiante'] = (string) ($user['genero_estudiante'] ?? '');
        $_SESSION['grado_id_estudiante'] = (string) ($user['grado_id_estudiante'] ?? '');
        $_SESSION['carnet_estudiante'] = (string) ($user['carnet_estudiante'] ?? '');
        $_SESSION['numero_lista_estudiante'] = (string) ($user['numero_lista_estudiante'] ?? '');
        $_SESSION['info_medica_estudiante'] = (string) ($user['info_medica_estudiante'] ?? '');
        $_SESSION['fecha_reg_estudiante'] = (string) ($user['fecha_reg_estudiante'] ?? '');
        $_SESSION['u_acceso_estudiante'] = (string) ($user['u_acceso_estudiante'] ?? '');
        $_SESSION['nombres_encargado'] = (string) ($user['nombres_encargado'] ?? '');
        $_SESSION['apellidos_encargado'] = (string) ($user['apellidos_encargado'] ?? '');
        $_SESSION['dui_encargado'] = (string) ($user['dui_encargado'] ?? '');
        $_SESSION['direccion_encargado'] = (string) ($user['direccion_encargado'] ?? '');
        $_SESSION['correo_encargado'] = (string) ($user['correo_encargado'] ?? '');
        $_SESSION['trabajo_encargado'] = (string) ($user['trabajo_encargado'] ?? '');
        $_SESSION['numero_cel_encargado'] = (string) ($user['numero_cel_encargado'] ?? '');
        $_SESSION['numero_tel_encargado'] = (string) ($user['numero_tel_encargado'] ?? '');
        $_SESSION['genero_encargado'] = (string) ($user['genero_encargado'] ?? '');
        $_SESSION['fecha_nac_encargado'] = (string) ($user['fecha_nac_encargado'] ?? '');

        $_SESSION['AUTH_PROVIDER'] = $provider;
        $_SESSION['GOOGLE_EMAIL'] = (string) ($providerMeta['email'] ?? ($user['GOOGLE_EMAIL'] ?? ''));
        $_SESSION['GOOGLE_SUB'] = (string) ($providerMeta['sub'] ?? ($user['GOOGLE_ID'] ?? ''));
        $_SESSION['last_activity'] = time();
    }
}

if (!function_exists('atenea_profile_full_name')) {
    function atenea_profile_full_name(string $firstName, string $lastName): string
    {
        return trim($firstName . ' ' . $lastName);
    }
}

if (!function_exists('atenea_billing_clean_text')) {
    function atenea_billing_clean_text($value, int $maxLength = 255): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        if (!is_string($value)) {
            $value = '';
        }

        if ($maxLength > 0 && function_exists('mb_substr')) {
            return mb_substr($value, 0, $maxLength);
        }

        return $maxLength > 0 ? substr($value, 0, $maxLength) : $value;
    }
}

if (!function_exists('atenea_billing_clean_phone')) {
    function atenea_billing_clean_phone($value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/[^0-9+\s().-]/', '', $value);

        return is_string($value) ? substr($value, 0, 25) : '';
    }
}

if (!function_exists('atenea_billing_clean_nrc')) {
    function atenea_billing_clean_nrc($value): string
    {
        $value = strtoupper(trim((string) $value));
        $value = preg_replace('/[^0-9A-Z-]/', '', $value);

        return is_string($value) ? substr($value, 0, 20) : '';
    }
}

if (!function_exists('atenea_billing_normalize_document')) {
    function atenea_billing_normalize_document(string $documentType, $documentNumber): string
    {
        $documentType = strtoupper(trim($documentType));
        $digits = preg_replace('/\D+/', '', (string) $documentNumber);
        if (!is_string($digits)) {
            $digits = '';
        }

        if ($documentType === 'NIT') {
            if (strlen($digits) !== 14) {
                return '';
            }

            return substr($digits, 0, 4) . '-' . substr($digits, 4, 6) . '-' . substr($digits, 10, 3) . '-' . substr($digits, 13, 1);
        }

        if (strlen($digits) !== 9) {
            return '';
        }

        return substr($digits, 0, 8) . '-' . substr($digits, 8, 1);
    }
}

if (!function_exists('atenea_billing_profile_is_complete')) {
    function atenea_billing_profile_is_complete(array $billingData): bool
    {
        return trim((string) ($billingData['phone_number'] ?? '')) !== ''
            && trim((string) ($billingData['tipo_documento'] ?? '')) !== ''
            && trim((string) ($billingData['numero_documento'] ?? '')) !== ''
            && trim((string) ($billingData['billing_departamento'] ?? '')) !== ''
            && trim((string) ($billingData['billing_municipio'] ?? '')) !== ''
            && trim((string) ($billingData['billing_direccion'] ?? '')) !== '';
    }
}

if (!function_exists('atenea_validate_billing_profile_input')) {
    function atenea_validate_billing_profile_input(array $input, array $options = []): array
    {
        $defaults = [
            'require_name' => false,
            'require_email' => false,
            'require_phone' => true,
            'require_document' => true,
            'require_location' => true,
            'require_address' => true,
        ];
        $options = array_merge($defaults, $options);

        $billingName = atenea_billing_clean_text($input['billing_name'] ?? $input['full_name'] ?? '', 150);
        $billingEmail = strtolower(trim((string) ($input['billing_email'] ?? $input['email'] ?? '')));
        $phoneNumber = atenea_billing_clean_phone($input['billing_phone'] ?? $input['phone_number'] ?? '');
        $documentType = strtoupper(trim((string) ($input['billing_tipo_documento'] ?? $input['tipo_documento'] ?? '')));
        $documentNumber = atenea_billing_normalize_document($documentType, $input['billing_numero_documento'] ?? $input['numero_documento'] ?? '');
        $department = atenea_billing_clean_text($input['billing_departamento'] ?? '', 100);
        $municipality = atenea_billing_clean_text($input['billing_municipio'] ?? '', 100);
        $district = atenea_billing_clean_text($input['billing_distrito'] ?? '', 100);
        $address = atenea_billing_clean_text($input['billing_address'] ?? $input['billing_direccion'] ?? '', 255);
        $hasNrc = !empty($input['billing_has_nrc']) || trim((string) ($input['billing_nrc'] ?? '')) !== '';
        $nrc = $hasNrc ? atenea_billing_clean_nrc($input['billing_nrc'] ?? '') : null;

        $errors = [];

        if (!empty($options['require_name']) && $billingName === '') {
            $errors[] = 'Debes indicar el nombre para el comprobante.';
        }

        if (!empty($options['require_email'])) {
            if ($billingEmail === '') {
                $errors[] = 'Debes indicar el correo para el comprobante.';
            } elseif (!filter_var($billingEmail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'El correo para el comprobante no es válido.';
            }
        } elseif ($billingEmail !== '' && !filter_var($billingEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo para el comprobante no es válido.';
        }

        if (!empty($options['require_phone']) && $phoneNumber === '') {
            $errors[] = 'Debes indicar un teléfono o WhatsApp.';
        } elseif ($phoneNumber !== '' && !preg_match('/^[0-9+\s().-]{7,25}$/', $phoneNumber)) {
            $errors[] = 'El teléfono o WhatsApp debe tener entre 7 y 25 caracteres válidos.';
        }

        if (!empty($options['require_document'])) {
            if (!in_array($documentType, ['DUI', 'NIT'], true)) {
                $errors[] = 'Debes seleccionar un tipo de documento válido.';
            }

            if ($documentNumber === '') {
                $errors[] = $documentType === 'NIT'
                    ? 'Debes ingresar un NIT válido en formato 0000-000000-000-0.'
                    : 'Debes ingresar un DUI válido en formato 00000000-0.';
            }
        }

        if (!empty($options['require_location'])) {
            if ($department === '') {
                $errors[] = 'Debes seleccionar el departamento.';
            }

            if ($municipality === '') {
                $errors[] = 'Debes seleccionar el municipio.';
            }
        }

        if (!empty($options['require_address']) && $address === '') {
            $errors[] = 'Debes completar la dirección.';
        }

        if ($hasNrc && ($nrc === null || $nrc === '')) {
            $errors[] = 'Debes indicar el NRC cuando compras como contribuyente o empresa inscrita en IVA.';
        }

        return [
            'errors' => $errors,
            'data' => [
                'billing_name' => $billingName,
                'billing_email' => $billingEmail,
                'phone_number' => $phoneNumber,
                'tipo_documento' => $documentType,
                'numero_documento' => $documentNumber,
                'billing_departamento' => $department,
                'billing_municipio' => $municipality,
                'billing_distrito' => $district,
                'billing_direccion' => $address,
                'billing_nrc' => $nrc,
                'billing_has_nrc' => $hasNrc ? '1' : '',
            ],
        ];
    }
}

if (!function_exists('atenea_sync_public_billing_profile')) {
    function atenea_sync_public_billing_profile(mysqli $db, int $userId, array $billingData): void
    {
        atenea_ensure_public_user_schema($db);

        if ($userId <= 0 || !atenea_db_has_table($db, 'public_users')) {
            return;
        }

        $billingName = atenea_billing_clean_text($billingData['billing_name'] ?? '', 150);
        $billingEmail = strtolower(trim((string) ($billingData['billing_email'] ?? '')));
        $phoneNumber = atenea_billing_clean_phone($billingData['phone_number'] ?? '');
        $documentType = strtoupper(trim((string) ($billingData['tipo_documento'] ?? '')));
        $documentNumber = atenea_billing_normalize_document($documentType, $billingData['numero_documento'] ?? '');
        $department = atenea_billing_clean_text($billingData['billing_departamento'] ?? '', 100);
        $municipality = atenea_billing_clean_text($billingData['billing_municipio'] ?? '', 100);
        $district = atenea_billing_clean_text($billingData['billing_distrito'] ?? '', 100);
        $address = atenea_billing_clean_text($billingData['billing_direccion'] ?? '', 255);
        $nrc = atenea_billing_clean_nrc($billingData['billing_nrc'] ?? '');
        $completed = atenea_billing_profile_is_complete([
            'phone_number' => $phoneNumber,
            'tipo_documento' => $documentType,
            'numero_documento' => $documentNumber,
            'billing_departamento' => $department,
            'billing_municipio' => $municipality,
            'billing_direccion' => $address,
        ]) ? 1 : 0;

        $stmt = $db->prepare(
            'UPDATE public_users
             SET BILLING_NAME = ?,
                 BILLING_EMAIL = ?,
                 PHONE_NUMBER = ?,
                 TIPO_DOCUMENTO = ?,
                 NUMERO_DOCUMENTO = ?,
                 BILLING_DEPARTAMENTO = ?,
                 BILLING_MUNICIPIO = ?,
                 BILLING_DISTRITO = ?,
                 BILLING_DIRECCION = ?,
                 BILLING_NRC = ?,
                 BILLING_PROFILE_COMPLETED = ?
             WHERE USER_ID = ?
             LIMIT 1'
        );

        if (!$stmt) {
            return;
        }

        $nrcValue = $nrc !== '' ? $nrc : null;
        $stmt->bind_param(
            'ssssssssssii',
            $billingName,
            $billingEmail,
            $phoneNumber,
            $documentType,
            $documentNumber,
            $department,
            $municipality,
            $district,
            $address,
            $nrcValue,
            $completed,
            $userId
        );
        $stmt->execute();
        $stmt->close();
    }
}

if (!function_exists('atenea_sync_public_billing_profile_from_order')) {
    function atenea_sync_public_billing_profile_from_order(mysqli $db, int $userId, array $order): void
    {
        atenea_sync_public_billing_profile($db, $userId, [
            'billing_name' => (string) ($order['billing_name'] ?? ''),
            'billing_email' => (string) ($order['billing_email'] ?? ''),
            'phone_number' => (string) ($order['billing_telefono'] ?? ''),
            'tipo_documento' => (string) ($order['billing_tipo_documento'] ?? ''),
            'numero_documento' => (string) ($order['billing_numero_documento'] ?? ''),
            'billing_departamento' => (string) ($order['billing_departamento'] ?? ''),
            'billing_municipio' => (string) ($order['billing_municipio'] ?? ''),
            'billing_distrito' => (string) ($order['billing_distrito'] ?? ''),
            'billing_direccion' => (string) ($order['billing_address'] ?? ''),
            'billing_nrc' => (string) ($order['billing_nrc'] ?? ''),
        ]);
    }
}

if (!function_exists('atenea_username_exists')) {
    function atenea_username_exists(mysqli $db, string $username, ?int $ignoreUserId = null): bool
    {
        $sql = 'SELECT ID FROM users WHERE LOWER(USERNAME) = LOWER(?)';
        if ($ignoreUserId !== null) {
            $sql .= ' AND ID <> ?';
        }
        $sql .= ' LIMIT 1';

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        if ($ignoreUserId !== null) {
            $stmt->bind_param('si', $username, $ignoreUserId);
        } else {
            $stmt->bind_param('s', $username);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result instanceof mysqli_result && $result->num_rows > 0;

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        $stmt->close();

        return $exists;
    }
}

if (!function_exists('atenea_email_exists_for_any_account')) {
    function atenea_email_exists_for_any_account(mysqli $db, string $email, ?int $ignoreUserId = null): bool
    {
        atenea_ensure_public_user_schema($db);

        $checks = [
            [
                'sql' => 'SELECT PUBLIC_USER_ID FROM public_users WHERE LOWER(EMAIL) = LOWER(?)' . ($ignoreUserId !== null ? ' AND USER_ID <> ?' : '') . ' LIMIT 1',
                'types' => $ignoreUserId !== null ? 'si' : 's',
                'values' => $ignoreUserId !== null ? [$email, $ignoreUserId] : [$email],
            ],
            [
                'sql' => 'SELECT EMPLOYEE_ID FROM employee WHERE LOWER(EMAIL) = LOWER(?) LIMIT 1',
                'types' => 's',
                'values' => [$email],
            ],
            [
                'sql' => 'SELECT ESTUDIANTE_ID FROM estudiantes WHERE LOWER(correo_estudiante) = LOWER(?) LIMIT 1',
                'types' => 's',
                'values' => [$email],
            ],
        ];

        foreach ($checks as $check) {
            $stmt = $db->prepare($check['sql']);
            if (!$stmt) {
                continue;
            }

            $stmt->bind_param($check['types'], ...$check['values']);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result instanceof mysqli_result && $result->num_rows > 0;

            if ($result instanceof mysqli_result) {
                mysqli_free_result($result);
            }

            $stmt->close();

            if ($exists) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('atenea_fetch_public_profile_by_user_id')) {
    function atenea_fetch_public_profile_by_user_id(mysqli $db, int $userId): ?array
    {
        atenea_ensure_public_user_schema($db);

        $stmt = $db->prepare('SELECT * FROM public_users WHERE USER_ID = ? AND ACCOUNT_STATUS = 1 LIMIT 1');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $profile = $result instanceof mysqli_result ? $result->fetch_assoc() : null;

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        $stmt->close();

        return $profile ?: null;
    }
}

if (!function_exists('atenea_sync_public_google_identity')) {
    function atenea_sync_public_google_identity(mysqli $db, int $userId, string $googleEmail, string $googleSub): void
    {
        atenea_ensure_public_user_schema($db);

        if (!atenea_db_has_table($db, 'public_users')) {
            return;
        }

        $setClauses = [];
        $types = '';
        $values = [];

        if ($googleSub !== '' && atenea_db_has_column($db, 'public_users', 'GOOGLE_ID')) {
            $setClauses[] = 'GOOGLE_ID = ?';
            $types .= 's';
            $values[] = $googleSub;
        }

        if ($googleEmail !== '' && atenea_db_has_column($db, 'public_users', 'GOOGLE_EMAIL')) {
            $setClauses[] = 'GOOGLE_EMAIL = ?';
            $types .= 's';
            $values[] = $googleEmail;
        }

        if ($setClauses === []) {
            return;
        }

        $sql = 'UPDATE public_users SET ' . implode(', ', $setClauses) . ' WHERE USER_ID = ? LIMIT 1';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return;
        }

        $types .= 'i';
        $values[] = $userId;
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $stmt->close();
    }
}

if (!function_exists('atenea_normalize_internal_redirect')) {
    function atenea_normalize_internal_redirect(string $candidate, string $fallback = 'homepage.php'): string
    {
        $candidate = trim($candidate);
        if ($candidate === '') {
            return $fallback;
        }

        $parts = parse_url($candidate);
        if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) {
            return $fallback;
        }

        $path = ltrim((string) ($parts['path'] ?? ''), '/');
        if ($path === '' || strpos($path, '..') !== false || strpos($path, '\\') !== false || strpos($path, '/') !== false) {
            return $fallback;
        }

        if (!preg_match('/^[A-Za-z0-9_.-]+\.(php|html)$/', $path)) {
            return $fallback;
        }

        $pagesPath = realpath(__DIR__ . '/../pages/' . $path);
        $rootPath = realpath(__DIR__ . '/../' . $path);
        $pagesRoot = realpath(__DIR__ . '/../pages');
        $projectRoot = realpath(__DIR__ . '/..');

        $validPagesTarget = $pagesPath !== false && $pagesRoot !== false && strpos($pagesPath, $pagesRoot) === 0;
        $validRootTarget = $rootPath !== false && $projectRoot !== false && strpos($rootPath, $projectRoot) === 0;

        if (!$validPagesTarget && !$validRootTarget) {
            return $fallback;
        }

        $normalized = $path;
        if (!empty($parts['query']) && preg_match('/^[A-Za-z0-9&=_.%-]*$/', $parts['query'])) {
            $normalized .= '?' . $parts['query'];
        }

        return $normalized;
    }
}

if (!function_exists('atenea_allowed_login_redirects')) {
    function atenea_allowed_login_redirects(): array
    {
        return [
            'productos.php',
            'carrito.php',
            'checkout_success.php',
            'historial_compras.php',
            'usuario_vista.php',
        ];
    }
}

if (!function_exists('atenea_resolve_login_redirect')) {
    function atenea_resolve_login_redirect(string $candidate, string $fallback = 'productos.php'): string
    {
        $allowed = atenea_allowed_login_redirects();
        $fallback = atenea_normalize_internal_redirect($fallback, 'productos.php');
        $fallbackPath = strtok($fallback, '?');

        if (!in_array($fallbackPath, $allowed, true)) {
            $fallback = 'productos.php';
        }

        $normalized = atenea_normalize_internal_redirect($candidate, $fallback);
        $normalizedPath = strtok($normalized, '?');

        return in_array($normalizedPath, $allowed, true) ? $normalized : $fallback;
    }
}

if (!function_exists('atenea_login_message_for_code')) {
    function atenea_login_message_for_code(string $messageCode): string
    {
        switch (trim($messageCode)) {
            case 'cart_required':
                return 'Inicia sesión para ver tu carrito.';
            case 'checkout_required':
                return 'Debes iniciar sesión antes de pagar.';
            case 'login_required':
                return 'Inicia sesión para continuar con tu compra.';
            default:
                return '';
        }
    }
}

if (!function_exists('atenea_build_login_url')) {
    function atenea_build_login_url(string $redirect = 'productos.php', string $messageCode = 'login_required'): string
    {
        $params = [
            'redirect' => atenea_resolve_login_redirect($redirect, 'productos.php'),
        ];

        if (trim($messageCode) !== '') {
            $params['msg'] = trim($messageCode);
        }

        return 'login.php?' . http_build_query($params);
    }
}

if (!function_exists('atenea_login_required_response')) {
    function atenea_login_required_response(string $redirect = 'productos.php', string $messageCode = 'login_required', bool $json = false): void
    {
        $loginUrl = atenea_build_login_url($redirect, $messageCode);
        $message = atenea_login_message_for_code($messageCode);

        if ($json) {
            http_response_code(401);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(
                [
                    'success' => false,
                    'login_required' => true,
                    'message' => $message !== '' ? $message : 'Debes iniciar sesión para continuar.',
                    'redirect' => $loginUrl,
                ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
            exit;
        }

        header('Location: ' . $loginUrl);
        exit;
    }
}

if (!function_exists('atenea_http_get_json')) {
    function atenea_http_get_json(string $url): ?array
    {
        $responseBody = false;

        if (filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOL) || ini_get('allow_url_fopen') === '1') {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 15,
                    'header' => "Accept: application/json\r\n",
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);

            $responseBody = @file_get_contents($url, false, $context);
        }

        if ($responseBody === false && function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_HTTPHEADER => ['Accept: application/json'],
            ]);
            $responseBody = curl_exec($ch);
            curl_close($ch);
        }

        if (!is_string($responseBody) || trim($responseBody) === '') {
            return null;
        }

        $decoded = json_decode($responseBody, true);

        return is_array($decoded) ? $decoded : null;
    }
}

if (!function_exists('atenea_verify_google_credential')) {
    function atenea_verify_google_credential(string $credential, array $allowedClientIds): array
    {
        if (trim($credential) === '') {
            return ['ok' => false, 'message' => 'No se recibió una credencial válida desde Google.'];
        }

        $payload = atenea_http_get_json('https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($credential));
        if (!$payload) {
            return ['ok' => false, 'message' => 'No fue posible validar la credencial con Google en este momento.'];
        }

        if (!empty($payload['error']) || !empty($payload['error_description'])) {
            return ['ok' => false, 'message' => 'Google rechazó la credencial proporcionada.'];
        }

        $audience = (string) ($payload['aud'] ?? $payload['issued_to'] ?? '');
        if ($allowedClientIds !== [] && !in_array($audience, $allowedClientIds, true)) {
            return ['ok' => false, 'message' => 'La credencial de Google no pertenece a esta aplicación.'];
        }

        $issuer = (string) ($payload['iss'] ?? '');
        if ($issuer !== '' && !in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            return ['ok' => false, 'message' => 'El emisor del token de Google no es válido.'];
        }

        $email = trim((string) ($payload['email'] ?? ''));
        if ($email === '') {
            return ['ok' => false, 'message' => 'Google no devolvió un correo electrónico utilizable.'];
        }

        $verifiedEmail = $payload['email_verified'] ?? true;
        if (in_array($verifiedEmail, [false, 'false', 0, '0'], true)) {
            return ['ok' => false, 'message' => 'La cuenta de Google debe tener el correo verificado para continuar.'];
        }

        return ['ok' => true, 'payload' => $payload];
    }
}

if (!function_exists('atenea_render_auth_alert')) {
    function atenea_render_auth_alert(string $icon, string $title, string $text, string $redirectUrl = 'login.php'): void
    {
        $iconJson = json_encode($icon, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $titleJson = json_encode($title, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $textJson = json_encode($text, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $redirectJson = json_encode($redirectUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        echo '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Atenea</title>
  <link rel="icon" href="../img/Atenea Logo.png" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Handlee&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link href="../css/atenea-ui.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/atenea-ui.js" defer></script>
</head>
<body class="atenea-auth-feedback" data-loader-text="Preparando mensaje...">
  <script>
    window.addEventListener("load", function () {
      if (window.AteneaUI) {
        window.AteneaUI.hideLoader(true);
      }

      var alertOptions = {
        icon: ' . $iconJson . ',
        title: ' . $titleJson . ',
        text: ' . $textJson . ',
        confirmButtonText: "Continuar"
      };

      var fireAlert = window.AteneaAlerts && typeof window.AteneaAlerts.fire === "function"
        ? window.AteneaAlerts.fire(alertOptions)
        : Swal.fire(alertOptions);

      fireAlert.then(function () {
        window.location = ' . $redirectJson . ';
      });
    });
  </script>
</body>
</html>';
        exit;
    }
}
