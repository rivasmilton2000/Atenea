<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';
require_once __DIR__ . '/../includes/atenea_admin.php';

atenea_backoffice_require($db, ['SuperAdmin']);

$studentsCount = atenea_backoffice_registered_students_count($db);
$coursesCount = atenea_backoffice_count_courses($db);
$enrollmentsCount = atenea_backoffice_count_active_enrollments($db);
$videosCount = atenea_backoffice_count_training_videos($db);
$certificatesCount = atenea_backoffice_count_emitted_certificates($db);
$ordersCount = atenea_backoffice_count_paid_orders($db);
$productsCount = atenea_backoffice_count_products($db);
$activeUsersCount = atenea_backoffice_count_active_users($db);
$siteStats = atenea_backoffice_fetch_site_stats($db);

dashboard_render_material_page([
    'pageTitle' => 'Dashboard SuperAdmin',
    'roleLabel' => atenea_backoffice_role_label(),
    'welcomeTitle' => 'Vision global de Atenea',
    'welcomeText' => 'Supervision total de estudiantes reales, cursos, inscripciones, videos, certificados, compras, tienda, usuarios y configuracion del sitio, sin modulos arrastrados del sistema anterior.',
    'profileUrl' => atenea_backoffice_profile_url(),
    'navSections' => atenea_backoffice_nav_sections('sa_vista.php'),
    'cards' => [
        ['title' => 'Estudiantes registrados', 'value' => $studentsCount, 'icon' => 'school', 'accent' => 'primary', 'href' => 'estudiantes.php', 'metricLabel' => 'Perfiles publicos activos', 'footerLabel' => 'Abrir modulo'],
        ['title' => 'Cursos / Capacitaciones', 'value' => $coursesCount, 'icon' => 'menu_book', 'accent' => 'success', 'href' => 'programas_admin.php', 'metricLabel' => 'Oferta formativa vigente', 'footerLabel' => 'Gestionar cursos'],
        ['title' => 'Inscripciones activas', 'value' => $enrollmentsCount, 'icon' => 'fact_check', 'accent' => 'info', 'href' => 'inscripciones_admin.php', 'metricLabel' => 'Seguimiento academico', 'footerLabel' => 'Ver matriculas'],
        ['title' => 'Videos de capacitacion', 'value' => $videosCount, 'icon' => 'smart_display', 'accent' => 'warning', 'href' => 'curso_videos_admin.php', 'metricLabel' => 'Contenido habilitado', 'footerLabel' => 'Administrar videos'],
        ['title' => 'Certificados emitidos', 'value' => $certificatesCount, 'icon' => 'workspace_premium', 'accent' => 'success', 'href' => 'curso_certificados_admin.php', 'metricLabel' => 'Certificados disponibles', 'footerLabel' => 'Revisar certificados'],
        ['title' => 'Pagos / Compras', 'value' => $ordersCount, 'icon' => 'payments', 'accent' => 'danger', 'href' => 'compras_admin.php', 'metricLabel' => 'Ordenes completadas', 'footerLabel' => 'Ver compras'],
        ['title' => 'Productos / Tienda', 'value' => $productsCount, 'icon' => 'storefront', 'accent' => 'dark', 'href' => 'productos_admin.php', 'metricLabel' => 'Catalogo activo', 'footerLabel' => 'Ver tienda'],
        ['title' => 'Usuarios activos', 'value' => $activeUsersCount, 'icon' => 'group', 'accent' => 'primary', 'href' => 'usuarios_admin.php', 'metricLabel' => 'Accesos vigentes', 'footerLabel' => 'Administrar usuarios'],
    ],
    'quickLinks' => [
        ['label' => 'Usuarios del sistema', 'href' => 'usuarios_admin.php', 'icon' => 'group'],
        ['label' => 'Roles y permisos', 'href' => 'roles_permisos.php', 'icon' => 'admin_panel_settings'],
        ['label' => 'Configuracion del sitio', 'href' => 'configuracion_sitio.php', 'icon' => 'settings_suggest'],
        ['label' => 'Pagina publica', 'href' => 'pagina_publica_admin.php', 'icon' => 'language'],
        ['label' => 'Gestionar estudiantes', 'href' => 'estudiantes.php', 'icon' => 'school'],
        ['label' => 'Administrar videos', 'href' => 'curso_videos_admin.php', 'icon' => 'smart_display'],
        ['label' => 'Pagos y DTE', 'href' => 'compras_admin.php', 'icon' => 'payments'],
        ['label' => 'Catalogo y tienda', 'href' => 'productos_admin.php', 'icon' => 'storefront'],
    ],
    'summaryItems' => [
        ['label' => 'Rol activo', 'value' => atenea_backoffice_role_label()],
        ['label' => 'Noticias activas', 'value' => (string) ($siteStats['noticias'] ?? 0)],
        ['label' => 'Galeria activa', 'value' => (string) ($siteStats['galeria'] ?? 0)],
        ['label' => 'Cursos publicados', 'value' => (string) ($siteStats['programas'] ?? 0)],
        ['label' => 'Productos publicados', 'value' => (string) ($siteStats['productos'] ?? 0)],
    ],
    'heroBadges' => [
        $activeUsersCount . ' usuarios activos',
        $studentsCount . ' estudiantes reales',
        $certificatesCount . ' certificados',
        $ordersCount . ' compras pagadas',
    ],
    'heroActions' => [
        ['label' => 'Usuarios', 'href' => 'usuarios_admin.php', 'icon' => 'group'],
        ['label' => 'Permisos', 'href' => 'roles_permisos.php', 'icon' => 'admin_panel_settings', 'variant' => 'outline'],
        ['label' => 'Configuracion', 'href' => 'configuracion_sitio.php', 'icon' => 'settings_suggest', 'variant' => 'outline'],
    ],
]);
