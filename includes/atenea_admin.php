<?php

require_once __DIR__ . '/material_dashboard.php';
require_once __DIR__ . '/atenea_capacitacion.php';

if (!function_exists('atenea_backoffice_is_superadmin')) {
    function atenea_backoffice_is_superadmin(): bool
    {
        return (string) ($_SESSION['TYPE'] ?? '') === 'SuperAdmin';
    }
}

if (!function_exists('atenea_backoffice_primary_role')) {
    function atenea_backoffice_primary_role(): string
    {
        return atenea_backoffice_is_superadmin() ? 'SuperAdmin' : 'Admin';
    }
}

if (!function_exists('atenea_backoffice_role_label')) {
    function atenea_backoffice_role_label(): string
    {
        return atenea_backoffice_is_superadmin() ? 'Super administrador' : 'Administrador';
    }
}

if (!function_exists('atenea_backoffice_dashboard_url')) {
    function atenea_backoffice_dashboard_url(): string
    {
        return atenea_backoffice_is_superadmin() ? 'sa_vista.php' : 'dashboard_admin.php';
    }
}

if (!function_exists('atenea_backoffice_profile_url')) {
    function atenea_backoffice_profile_url(): string
    {
        $memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);

        return atenea_backoffice_is_superadmin()
            ? 'sa_perfil.php?action=edit&id=' . $memberId
            : 'perfil.php?action=edit&id=' . $memberId;
    }
}

if (!function_exists('atenea_backoffice_redirect_map')) {
    function atenea_backoffice_redirect_map(): array
    {
        return [
            'Personal' => 'empleados_vista.php',
            'Estudiante' => 'usuario_vista.php',
            'Docente' => 'docentes_vista.php',
        ];
    }
}

if (!function_exists('atenea_backoffice_require')) {
    function atenea_backoffice_require(mysqli $db, array $allowedRoles = ['Admin', 'SuperAdmin']): string
    {
        dashboard_require_role($db, $allowedRoles, atenea_backoffice_redirect_map());

        return atenea_backoffice_primary_role();
    }
}

if (!function_exists('atenea_backoffice_item_is_active')) {
    function atenea_backoffice_item_is_active(array $item, string $currentPage): bool
    {
        $patterns = (array) ($item['match'] ?? []);
        $href = basename((string) ($item['href'] ?? ''));

        if ($href !== '') {
            $patterns[] = $href;
        }

        foreach ($patterns as $pattern) {
            if (atenea_simple_pattern_match($currentPage, (string) $pattern)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('atenea_backoffice_nav_sections')) {
    function atenea_backoffice_nav_sections(string $currentPage = ''): array
    {
        $dashboardHref = atenea_backoffice_dashboard_url();

        $sections = [
            [
                'title' => 'Panel',
                'items' => [
                    [
                        'label' => 'Inicio',
                        'href' => $dashboardHref,
                        'icon' => 'dashboard',
                        'match' => ['dashboard_admin.php', 'sa_vista.php'],
                    ],
                ],
            ],
            [
                'title' => 'Gestion',
                'items' => [
                    ['label' => 'Estudiantes', 'href' => 'estudiantes.php', 'icon' => 'school', 'match' => ['estudiantes.php', 'sa_estudiantes.php', 'estudiante_usuario.php']],
                    ['label' => 'Docentes / Facilitadores', 'href' => atenea_backoffice_is_superadmin() ? 'sa_docentes.php' : 'docentes.php', 'icon' => 'co_present', 'match' => ['docentes.php', 'docentes_searchfrm.php', 'docentes_edit.php', 'sa_docentes.php', 'sa_docentes_searchfrm.php', 'sa_docentes_edit.php']],
                    ['label' => 'Cursos / Capacitacion', 'href' => 'programas_admin.php', 'icon' => 'menu_book', 'match' => ['programas_admin.php', 'programas_edit.php']],
                    ['label' => 'Inscripciones', 'href' => 'inscripciones_admin.php', 'icon' => 'fact_check', 'match' => ['inscripciones_admin.php']],
                    ['label' => 'Videos de capacitacion', 'href' => 'curso_videos_admin.php', 'icon' => 'smart_display', 'match' => ['curso_videos_admin.php', 'curso_videos_edit.php']],
                    ['label' => 'Record escolar', 'href' => 'record_escolar_admin.php', 'icon' => 'history_edu', 'match' => ['record_escolar_admin.php']],
                    ['label' => 'Certificados', 'href' => 'curso_certificados_admin.php', 'icon' => 'workspace_premium', 'match' => ['curso_certificados_admin.php', 'curso_certificados_transac.php', 'certificado_curso_pdf.php']],
                    ['label' => 'Pagos / Compras', 'href' => 'compras_admin.php', 'icon' => 'payments', 'match' => ['compras_admin.php', 'dte_documents.php', 'sa_dte_documents.php', 'dte_config.php', 'sa_dte_config.php']],
                    ['label' => 'Productos / Tienda', 'href' => 'productos_admin.php', 'icon' => 'storefront', 'match' => ['productos_admin.php', 'productos_add.php', 'productos_edit.php', 'categorias_productos.php']],
                ],
            ],
            [
                'title' => 'Contenido',
                'items' => [
                    ['label' => 'Noticias', 'href' => 'noticias_admin.php', 'icon' => 'newspaper', 'match' => ['noticias_admin.php', 'noticias_edit.php']],
                    ['label' => 'Galeria', 'href' => 'galeria_home.php', 'icon' => 'photo_library', 'match' => ['galeria_home.php']],
                    ['label' => 'Pagina publica / Homepage', 'href' => 'pagina_publica_admin.php', 'icon' => 'language', 'match' => ['pagina_publica_admin.php', 'about_admin.php', 'servicios.php']],
                    ['label' => 'Configuracion del sitio', 'href' => 'configuracion_sitio.php', 'icon' => 'settings_suggest', 'match' => ['configuracion_sitio.php', 'configmail_admin.php', 'dte_config.php', 'sa_dte_config.php']],
                ],
            ],
            [
                'title' => 'Sistema',
                'items' => [
                    ['label' => 'Usuarios', 'href' => 'usuarios_admin.php', 'icon' => 'group', 'match' => ['usuarios_admin.php', 'sa_cuentas_usuarios.php']],
                    ['label' => 'Roles y permisos', 'href' => 'roles_permisos.php', 'icon' => 'admin_panel_settings', 'match' => ['roles_permisos.php']],
                    ['label' => 'Perfil', 'href' => atenea_backoffice_profile_url(), 'icon' => 'person', 'match' => ['perfil.php', 'sa_perfil.php']],
                    ['label' => 'Cerrar sesion', 'href' => 'logout.php?redirect=homepage.php', 'icon' => 'logout', 'match' => ['logout.php']],
                ],
            ],
        ];

        if ($currentPage === '') {
            return $sections;
        }

        foreach ($sections as $sectionIndex => $section) {
            foreach (($section['items'] ?? []) as $itemIndex => $item) {
                $sections[$sectionIndex]['items'][$itemIndex]['active'] = atenea_backoffice_item_is_active($item, $currentPage);
            }
        }

        return $sections;
    }
}

if (!function_exists('atenea_backoffice_page_config')) {
    function atenea_backoffice_page_config(string $pageTitle, string $currentPage, array $overrides = []): array
    {
        $config = [
            'pageTitle' => $pageTitle,
            'roleLabel' => atenea_backoffice_role_label(),
            'profileUrl' => atenea_backoffice_profile_url(),
            'logoutUrl' => 'logout.php?redirect=homepage.php',
            'navSections' => atenea_backoffice_nav_sections($currentPage),
        ];

        return array_replace_recursive($config, $overrides);
    }
}

if (!function_exists('atenea_backoffice_user_status_expr')) {
    function atenea_backoffice_user_status_expr(mysqli $db): string
    {
        return atenea_db_has_column($db, 'users', 'U_ESTADO') ? 'COALESCE(u.U_ESTADO, 1)' : '1';
    }
}

if (!function_exists('atenea_backoffice_registration_source_expr')) {
    function atenea_backoffice_registration_source_expr(mysqli $db): string
    {
        if (atenea_db_has_column($db, 'public_users', 'REGISTRATION_SOURCE')) {
            return "COALESCE(NULLIF(TRIM(pu.REGISTRATION_SOURCE), ''), CASE
                        WHEN COALESCE(pu.GOOGLE_ID, '') <> '' OR COALESCE(pu.GOOGLE_EMAIL, '') <> '' THEN 'google'
                        ELSE 'normal'
                    END)";
        }

        return "CASE
                    WHEN COALESCE(pu.GOOGLE_ID, '') <> '' OR COALESCE(pu.GOOGLE_EMAIL, '') <> '' THEN 'google'
                    ELSE 'normal'
                END";
    }
}

if (!function_exists('atenea_backoffice_registration_source_label')) {
    function atenea_backoffice_registration_source_label(?string $source): string
    {
        switch (strtolower(trim((string) $source))) {
            case 'google':
                return 'Google';
            case 'admin':
                return 'Admin';
            default:
                return 'Normal';
        }
    }
}

if (!function_exists('atenea_backoffice_registered_students_count')) {
    function atenea_backoffice_registered_students_count(mysqli $db): int
    {
        atenea_ensure_public_user_schema($db);
        atenea_sync_public_student_accounts($db);

        $userStatusExpr = atenea_backoffice_user_status_expr($db);
        $sql = "SELECT COUNT(*)
                FROM users u
                INNER JOIN public_users pu ON pu.USER_ID = u.ID
                WHERE (u.EMPLOYEE_ID IS NULL OR u.EMPLOYEE_ID = 0)
                  AND (u.ESTUDIANTE_ID IS NULL OR u.ESTUDIANTE_ID = 0)
                  AND COALESCE(u.TYPE_ID, 3) = 3
                  AND {$userStatusExpr} = 1
                  AND COALESCE(pu.ACCOUNT_STATUS, 1) = 1";

        return dashboard_count($db, $sql);
    }
}

if (!function_exists('atenea_backoffice_fetch_registered_students')) {
    function atenea_backoffice_fetch_registered_students(mysqli $db, string $search = ''): array
    {
        atenea_ensure_public_user_schema($db);
        atenea_sync_public_student_accounts($db);

        $userStatusExpr = atenea_backoffice_user_status_expr($db);
        $registrationExpr = atenea_backoffice_registration_source_expr($db);
        $sql = "SELECT
                    u.ID,
                    u.USERNAME,
                    {$userStatusExpr} AS user_status,
                    COALESCE(pu.ACCOUNT_STATUS, 1) AS account_status,
                    COALESCE(pu.PUBLIC_USER_ID, 0) AS public_user_id,
                    COALESCE(pu.FIRST_NAME, '') AS first_name,
                    COALESCE(pu.LAST_NAME, '') AS last_name,
                    COALESCE(pu.EMAIL, '') AS email,
                    COALESCE(pu.PHONE_NUMBER, '') AS phone_number,
                    COALESCE(pu.BIRTHDATE, NULL) AS birthdate,
                    COALESCE(pu.CREATED_AT, NULL) AS created_at,
                    COALESCE(t.TYPE, 'Estudiante') AS role_name,
                    {$registrationExpr} AS registration_source,
                    (
                        SELECT pe.titulo
                        FROM course_enrollments ce
                        INNER JOIN programas_educativos pe ON pe.id = ce.programa_id
                        WHERE ce.user_id = u.ID
                        ORDER BY
                            CASE
                                WHEN ce.estado_curso IN ('curso_activo', 'activo') THEN 0
                                WHEN ce.estado_aprobacion = 'aprobado' THEN 1
                                WHEN ce.estado_aprobacion = 'en_proceso' THEN 2
                                ELSE 3
                            END,
                            COALESCE(ce.updated_at, ce.fecha_inscripcion) DESC,
                            ce.id DESC
                        LIMIT 1
                    ) AS current_course,
                    (
                        SELECT COUNT(*)
                        FROM course_enrollments ce_total
                        WHERE ce_total.user_id = u.ID
                    ) AS enrollment_count,
                    (
                        SELECT COUNT(*)
                        FROM course_enrollments ce_active
                        WHERE ce_active.user_id = u.ID
                          AND (
                                ce_active.estado_curso IN ('curso_activo', 'activo')
                                OR ce_active.estado_aprobacion IN ('en_proceso', 'aprobado')
                              )
                    ) AS active_enrollment_count
                FROM users u
                INNER JOIN public_users pu ON pu.USER_ID = u.ID
                LEFT JOIN type t ON t.TYPE_ID = u.TYPE_ID
                WHERE (u.EMPLOYEE_ID IS NULL OR u.EMPLOYEE_ID = 0)
                  AND (u.ESTUDIANTE_ID IS NULL OR u.ESTUDIANTE_ID = 0)
                  AND COALESCE(u.TYPE_ID, 3) = 3";

        $search = trim($search);
        $stmt = null;

        if ($search !== '') {
            $sql .= " AND (
                        CONCAT(COALESCE(pu.FIRST_NAME, ''), ' ', COALESCE(pu.LAST_NAME, '')) LIKE ?
                        OR COALESCE(pu.EMAIL, '') LIKE ?
                        OR COALESCE(pu.PHONE_NUMBER, '') LIKE ?
                        OR COALESCE(u.USERNAME, '') LIKE ?
                    )";
            $sql .= ' ORDER BY COALESCE(pu.CREATED_AT, NOW()) DESC, u.ID DESC';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                return [];
            }

            $needle = '%' . $search . '%';
            $stmt->bind_param('ssss', $needle, $needle, $needle, $needle);
        } else {
            $sql .= ' ORDER BY COALESCE(pu.CREATED_AT, NOW()) DESC, u.ID DESC';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                return [];
            }
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];

        while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
            $fullName = trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? ''));
            $row['full_name'] = $fullName !== '' ? $fullName : 'Perfil incompleto';
            $row['profile_incomplete'] = $fullName === '' || trim((string) ($row['email'] ?? '')) === '';
            $row['is_active'] = (int) ($row['user_status'] ?? 0) === 1 && (int) ($row['account_status'] ?? 0) === 1;
            $row['registration_source_label'] = atenea_backoffice_registration_source_label((string) ($row['registration_source'] ?? 'normal'));
            $rows[] = $row;
        }

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        $stmt->close();

        return $rows;
    }
}

if (!function_exists('atenea_backoffice_fetch_registered_student')) {
    function atenea_backoffice_fetch_registered_student(mysqli $db, int $userId): ?array
    {
        if ($userId <= 0) {
            return null;
        }

        $rows = atenea_backoffice_fetch_registered_students($db);
        foreach ($rows as $row) {
            if ((int) ($row['ID'] ?? 0) === $userId) {
                return $row;
            }
        }

        return null;
    }
}

if (!function_exists('atenea_backoffice_count_courses')) {
    function atenea_backoffice_count_courses(mysqli $db): int
    {
        if (!atenea_db_has_table($db, 'programas_educativos')) {
            return 0;
        }

        return dashboard_count($db, 'SELECT COUNT(*) FROM programas_educativos WHERE estado = 1');
    }
}

if (!function_exists('atenea_backoffice_count_active_enrollments')) {
    function atenea_backoffice_count_active_enrollments(mysqli $db): int
    {
        if (!atenea_db_has_table($db, 'course_enrollments')) {
            return 0;
        }

        return dashboard_count(
            $db,
            "SELECT COUNT(*)
             FROM course_enrollments
             WHERE estado_curso IN ('curso_activo', 'activo')
                OR estado_aprobacion IN ('en_proceso', 'aprobado')"
        );
    }
}

if (!function_exists('atenea_backoffice_count_training_videos')) {
    function atenea_backoffice_count_training_videos(mysqli $db): int
    {
        if (!atenea_db_has_table($db, 'course_videos')) {
            return 0;
        }

        return dashboard_count($db, 'SELECT COUNT(*) FROM course_videos WHERE estado = 1');
    }
}

if (!function_exists('atenea_backoffice_count_emitted_certificates')) {
    function atenea_backoffice_count_emitted_certificates(mysqli $db): int
    {
        if (!atenea_db_has_table($db, 'course_enrollments')) {
            return 0;
        }

        if (atenea_db_has_column($db, 'course_enrollments', 'certificado_generado_at')) {
            return dashboard_count(
                $db,
                "SELECT COUNT(*)
                 FROM course_enrollments
                 WHERE certificado_generado_at IS NOT NULL
                   AND certificado_generado_at <> ''"
            );
        }

        return dashboard_count(
            $db,
            "SELECT COUNT(*)
             FROM course_enrollments
             WHERE estado_aprobacion = 'aprobado'"
        );
    }
}

if (!function_exists('atenea_backoffice_count_paid_orders')) {
    function atenea_backoffice_count_paid_orders(mysqli $db): int
    {
        if (!atenea_db_has_table($db, 'ordenes')) {
            return 0;
        }

        return dashboard_count($db, "SELECT COUNT(*) FROM ordenes WHERE estado = 'paid'");
    }
}

if (!function_exists('atenea_backoffice_count_products')) {
    function atenea_backoffice_count_products(mysqli $db): int
    {
        if (!atenea_db_has_table($db, 'productos')) {
            return 0;
        }

        return dashboard_count($db, 'SELECT COUNT(*) FROM productos WHERE estado = 1');
    }
}

if (!function_exists('atenea_backoffice_count_active_users')) {
    function atenea_backoffice_count_active_users(mysqli $db): int
    {
        $userStatusExpr = atenea_backoffice_user_status_expr($db);

        if (atenea_db_has_table($db, 'public_users')) {
            return dashboard_count(
                $db,
                "SELECT COUNT(*)
                 FROM users u
                 LEFT JOIN public_users pu ON pu.USER_ID = u.ID
                 WHERE {$userStatusExpr} = 1
                   AND (pu.PUBLIC_USER_ID IS NULL OR COALESCE(pu.ACCOUNT_STATUS, 1) = 1)"
            );
        }

        return dashboard_count($db, "SELECT COUNT(*) FROM users u WHERE {$userStatusExpr} = 1");
    }
}

if (!function_exists('atenea_backoffice_fetch_site_stats')) {
    function atenea_backoffice_fetch_site_stats(mysqli $db): array
    {
        return [
            'noticias' => atenea_db_has_table($db, 'noticias')
                ? dashboard_count($db, 'SELECT COUNT(*) FROM noticias WHERE estado = 1')
                : 0,
            'galeria' => atenea_db_has_table($db, 'galeria')
                ? dashboard_count($db, 'SELECT COUNT(*) FROM galeria WHERE estado = 1')
                : 0,
            'programas' => atenea_backoffice_count_courses($db),
            'productos' => atenea_backoffice_count_products($db),
        ];
    }
}

if (!function_exists('atenea_backoffice_fetch_orders')) {
    function atenea_backoffice_fetch_orders(mysqli $db, string $search = ''): array
    {
        if (!atenea_db_has_table($db, 'ordenes')) {
            return [];
        }

        $sql = "SELECT
                    o.id,
                    o.session_id,
                    o.billing_name,
                    o.billing_email,
                    o.total_amount,
                    o.estado,
                    o.paid_at,
                    o.created_at,
                    o.stripe_session_id,
                    o.stripe_payment_intent,
                    dd.estado AS dte_status,
                    dd.codigo_generacion,
                    (
                        SELECT COUNT(*)
                        FROM orden_detalles od
                        WHERE od.orden_id = o.id
                    ) AS items_count
                FROM ordenes o
                LEFT JOIN dte_documents dd ON dd.order_id = o.id";

        $stmt = null;
        $search = trim($search);

        if ($search !== '') {
            $sql .= " WHERE (
                        COALESCE(o.billing_name, '') LIKE ?
                        OR COALESCE(o.billing_email, '') LIKE ?
                        OR COALESCE(o.estado, '') LIKE ?
                        OR COALESCE(dd.codigo_generacion, '') LIKE ?
                    )";
            $sql .= ' ORDER BY COALESCE(o.paid_at, o.created_at) DESC, o.id DESC';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                return [];
            }

            $needle = '%' . $search . '%';
            $stmt->bind_param('ssss', $needle, $needle, $needle, $needle);
        } else {
            $sql .= ' ORDER BY COALESCE(o.paid_at, o.created_at) DESC, o.id DESC';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                return [];
            }
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];

        while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
            $rows[] = $row;
        }

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        $stmt->close();

        return $rows;
    }
}

if (!function_exists('atenea_backoffice_role_permissions')) {
    function atenea_backoffice_role_permissions(): array
    {
        return [
            [
                'role' => 'SuperAdmin',
                'label' => 'SuperAdmin',
                'permissions' => [
                    'Acceso total a panel, configuracion y modulos de Atenea.',
                    'Gestion completa de estudiantes, cursos, inscripciones, videos, certificados, compras y tienda.',
                    'Acceso a usuarios, roles, permisos y configuracion DTE.',
                ],
            ],
            [
                'role' => 'Admin',
                'label' => 'Admin',
                'permissions' => [
                    'Gestion operativa de estudiantes, docentes, cursos, inscripciones y videos.',
                    'Administracion de certificados, productos, noticias, galeria y compras.',
                    'Acceso a configuracion del sitio y seguimiento de pagos / DTE.',
                ],
            ],
            [
                'role' => 'Estudiante',
                'label' => 'Estudiante / Usuario',
                'permissions' => [
                    'Ver perfil, compras, curso activo, videos habilitados y record escolar.',
                    'Descargar certificados habilitados y gestionar sus datos de facturacion.',
                ],
            ],
            [
                'role' => 'Docente',
                'label' => 'Docente / Facilitador',
                'permissions' => [
                    'Acceso acotado a perfil y panel propio mientras se completa la migracion funcional de facilitadores.',
                ],
            ],
            [
                'role' => 'Personal',
                'label' => 'Personal',
                'permissions' => [
                    'Acceso solo a perfil y panel operativo minimo mientras se valida si este rol seguira activo.',
                ],
            ],
        ];
    }
}
