<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';
require_once __DIR__ . '/../includes/atenea_admin.php';

atenea_backoffice_require($db, ['Admin']);

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
    'pageTitle' => 'Dashboard Atenea',
    'roleLabel' => atenea_backoffice_role_label(),
    'welcomeTitle' => 'Operacion central de la plataforma',
    'welcomeText' => 'Este panel ya muestra solo modulos vigentes de Atenea: estudiantes reales registrados, cursos, inscripciones, videos, certificados, compras, tienda y administracion del sitio.',
    'profileUrl' => atenea_backoffice_profile_url(),
    'navSections' => atenea_backoffice_nav_sections('dashboard_admin.php'),
    'cards' => [
        ['title' => 'Estudiantes registrados', 'value' => $studentsCount, 'icon' => 'school', 'accent' => 'primary', 'href' => 'estudiantes.php', 'metricLabel' => 'Usuarios reales en la plataforma', 'footerLabel' => 'Ver estudiantes'],
        ['title' => 'Cursos / Capacitaciones', 'value' => $coursesCount, 'icon' => 'menu_book', 'accent' => 'success', 'href' => 'programas_admin.php', 'metricLabel' => 'Oferta formativa activa', 'footerLabel' => 'Gestionar cursos'],
        ['title' => 'Inscripciones activas', 'value' => $enrollmentsCount, 'icon' => 'fact_check', 'accent' => 'info', 'href' => 'inscripciones_admin.php', 'metricLabel' => 'Cursos en seguimiento', 'footerLabel' => 'Ver inscripciones'],
        ['title' => 'Videos de capacitacion', 'value' => $videosCount, 'icon' => 'smart_display', 'accent' => 'warning', 'href' => 'curso_videos_admin.php', 'metricLabel' => 'Material audiovisual activo', 'footerLabel' => 'Administrar videos'],
        ['title' => 'Certificados emitidos', 'value' => $certificatesCount, 'icon' => 'workspace_premium', 'accent' => 'success', 'href' => 'curso_certificados_admin.php', 'metricLabel' => 'Certificados habilitados', 'footerLabel' => 'Revisar certificados'],
        ['title' => 'Pagos / Compras', 'value' => $ordersCount, 'icon' => 'payments', 'accent' => 'danger', 'href' => 'compras_admin.php', 'metricLabel' => 'Ordenes pagadas', 'footerLabel' => 'Ver compras'],
        ['title' => 'Productos / Tienda', 'value' => $productsCount, 'icon' => 'storefront', 'accent' => 'dark', 'href' => 'productos_admin.php', 'metricLabel' => 'Catalogo disponible', 'footerLabel' => 'Abrir tienda'],
        ['title' => 'Usuarios activos', 'value' => $activeUsersCount, 'icon' => 'group', 'accent' => 'primary', 'href' => 'usuarios_admin.php', 'metricLabel' => 'Cuentas habilitadas', 'footerLabel' => 'Gestionar usuarios'],
    ],
    'quickLinks' => [
        ['label' => 'Gestionar estudiantes', 'href' => 'estudiantes.php', 'icon' => 'school'],
        ['label' => 'Abrir cursos', 'href' => 'programas_admin.php', 'icon' => 'menu_book'],
        ['label' => 'Ver inscripciones', 'href' => 'inscripciones_admin.php', 'icon' => 'fact_check'],
        ['label' => 'Administrar videos', 'href' => 'curso_videos_admin.php', 'icon' => 'smart_display'],
        ['label' => 'Seguimiento academico', 'href' => 'record_escolar_admin.php', 'icon' => 'history_edu'],
        ['label' => 'Control de certificados', 'href' => 'curso_certificados_admin.php', 'icon' => 'workspace_premium'],
        ['label' => 'Pagos y compras', 'href' => 'compras_admin.php', 'icon' => 'payments'],
        ['label' => 'Contenido del sitio', 'href' => 'pagina_publica_admin.php', 'icon' => 'language'],
    ],
    'summaryItems' => [
        ['label' => 'Rol activo', 'value' => atenea_backoffice_role_label()],
        ['label' => 'Noticias activas', 'value' => (string) ($siteStats['noticias'] ?? 0)],
        ['label' => 'Imagenes en galeria', 'value' => (string) ($siteStats['galeria'] ?? 0)],
        ['label' => 'Cursos publicados', 'value' => (string) ($siteStats['programas'] ?? 0)],
        ['label' => 'Productos publicados', 'value' => (string) ($siteStats['productos'] ?? 0)],
    ],
    'heroBadges' => [
        $studentsCount . ' estudiantes',
        $coursesCount . ' cursos',
        $enrollmentsCount . ' inscripciones',
        $ordersCount . ' compras pagadas',
    ],
    'heroActions' => [
        ['label' => 'Estudiantes', 'href' => 'estudiantes.php', 'icon' => 'school'],
        ['label' => 'Videos', 'href' => 'curso_videos_admin.php', 'icon' => 'smart_display', 'variant' => 'outline'],
        ['label' => 'Compras', 'href' => 'compras_admin.php', 'icon' => 'payments', 'variant' => 'outline'],
    ],
]);
