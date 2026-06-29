<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';

dashboard_require_role(
    $db,
    ['Admin'],
    [
        'Personal' => 'empleados_vista.php',
        'Estudiante' => 'estudiante_vista.php',
        'Docente' => 'docentes_vista.php',
        'SuperAdmin' => 'sa_vista.php',
    ]
);

$memberId = (int) $_SESSION['MEMBER_ID'];
$profileUrl = 'perfil.php?action=edit&id=' . $memberId;

$asignaturasCount = dashboard_count($db, "SELECT COUNT(*) FROM asignaturas WHERE A_ESTADO = 1");
$personalCount = dashboard_count($db, "SELECT COUNT(*) FROM employee WHERE JOB_ID IN (2, 3) AND E_ESTADO = 1");
$docentesCount = dashboard_count($db, "SELECT COUNT(*) FROM employee WHERE JOB_ID = 1 AND E_ESTADO = 1");
$laboresCount = dashboard_count($db, "SELECT COUNT(*) FROM jobs WHERE j_estado = 1");
$vehiculosCount = dashboard_count($db, "SELECT COUNT(*) FROM vehicles WHERE v_estado = 1");
$gradosCount = dashboard_count($db, "SELECT COUNT(*) FROM grados WHERE G_ESTADO = 1");
$docAsignaturasCount = dashboard_count($db, "SELECT COUNT(*) FROM docentes_asignaturas WHERE da_estado = 1");
$estudiantesCount = dashboard_count($db, "SELECT COUNT(*) FROM estudiantes WHERE estado_estudiante = 1");
$distribucionCount = dashboard_count($db, "SELECT COUNT(*) FROM estudiantes_docentes WHERE ed_estado = 1");
$inventarioCount = dashboard_count($db, "SELECT COUNT(*) FROM inventario WHERE i_estado = 1");
$documentacionCount = dashboard_count($db, "SELECT COUNT(*) FROM archivos WHERE a_estado = 1");
$contenidosCount = dashboard_count($db, "SELECT COUNT(*) FROM contenidos WHERE c_estado = 1");
$evaluacionesCount = dashboard_count($db, "SELECT COUNT(*) FROM evaluaciones WHERE evaluacion_estado = 1");
$calendarioCount = dashboard_count($db, "SELECT COUNT(*) FROM actividades WHERE ACT_ESTADO = 1");

$navSections = [
    [
        'title' => 'Panel',
        'items' => [
            ['label' => 'Inicio', 'href' => 'index.php', 'icon' => 'dashboard', 'active' => true],
        ],
    ],
    [
        'title' => 'Página principal',
        'items' => [
            ['label' => 'About', 'href' => 'about_admin.php', 'icon' => 'info'],
            ['label' => 'Servicios', 'href' => 'servicios.php', 'icon' => 'design_services'],
            ['label' => 'Programas educativos', 'href' => 'programas_admin.php', 'icon' => 'school'],
            ['label' => 'Galería', 'href' => 'galeria_home.php', 'icon' => 'photo_library'],
            ['label' => 'Noticias', 'href' => 'noticias_admin.php', 'icon' => 'newspaper'],
        ],
    ],
    [
        'title' => 'Módulos',
        'items' => [
            ['label' => 'Asignaturas', 'href' => 'asignaturas.php', 'icon' => 'menu_book'],
            ['label' => 'Personal', 'href' => 'personal.php', 'icon' => 'groups'],
            ['label' => 'Docentes', 'href' => 'docentes.php', 'icon' => 'co_present'],
            ['label' => 'Labores', 'href' => 'labores.php', 'icon' => 'work'],
            ['label' => 'Vehículos', 'href' => 'vehiculos.php', 'icon' => 'directions_car'],
            ['label' => 'Videos curso', 'href' => 'videos_admin.php', 'icon' => 'play_circle'],
            ['label' => 'Configuración email', 'href' => 'configmail_admin.php', 'icon' => 'mail'],
            ['label' => 'Grados', 'href' => 'grados.php', 'icon' => 'domain'],
            ['label' => 'Doc. asignaturas', 'href' => 'doc_asignaturas.php', 'icon' => 'assignment'],
            ['label' => 'Estudiantes', 'href' => 'estudiantes.php', 'icon' => 'school'],
            ['label' => 'Dis. asignaturas', 'href' => 'dis_asignaturas.php', 'icon' => 'account_tree'],
            ['label' => 'Productos', 'href' => 'productos_admin.php', 'icon' => 'storefront'],
            ['label' => 'Categoría productos', 'href' => 'categorias_productos.php', 'icon' => 'category'],
            ['label' => 'Inventario', 'href' => 'inventario.php', 'icon' => 'inventory_2'],
            ['label' => 'Documentación', 'href' => 'documentacion.php', 'icon' => 'description'],
            ['label' => 'Con. evaluación', 'href' => 'con_evaluacion.php', 'icon' => 'edit_note'],
            ['label' => 'Evaluaciones', 'href' => 'evaluaciones.php', 'icon' => 'fact_check'],
            ['label' => 'Cal. actividades', 'href' => 'calendario.php', 'icon' => 'calendar_month'],
        ],
    ],
];

$cards = [
    ['title' => 'Asignaturas', 'value' => $asignaturasCount, 'icon' => 'menu_book', 'accent' => 'primary', 'href' => 'asignaturas.php', 'metricLabel' => 'Registros activos', 'footerLabel' => 'Administrar módulo'],
    ['title' => 'Personal', 'value' => $personalCount, 'icon' => 'groups', 'accent' => 'success', 'href' => 'personal.php', 'metricLabel' => 'Colaboradores activos', 'footerLabel' => 'Ver equipo'],
    ['title' => 'Docentes', 'value' => $docentesCount, 'icon' => 'co_present', 'accent' => 'info', 'href' => 'docentes.php', 'metricLabel' => 'Docentes activos', 'footerLabel' => 'Gestionar docentes'],
    ['title' => 'Labores', 'value' => $laboresCount, 'icon' => 'work', 'accent' => 'warning', 'href' => 'labores.php', 'metricLabel' => 'Puestos activos', 'footerLabel' => 'Revisar labores'],
    ['title' => 'Vehículos', 'value' => $vehiculosCount, 'icon' => 'directions_car', 'accent' => 'danger', 'href' => 'vehiculos.php', 'metricLabel' => 'Vehículos activos', 'footerLabel' => 'Ver flota'],
    ['title' => 'Grados', 'value' => $gradosCount, 'icon' => 'domain', 'accent' => 'dark', 'href' => 'grados.php', 'metricLabel' => 'Niveles disponibles', 'footerLabel' => 'Abrir catálogo'],
    ['title' => 'Doc. asignaturas', 'value' => $docAsignaturasCount, 'icon' => 'assignment', 'accent' => 'success', 'href' => 'doc_asignaturas.php', 'metricLabel' => 'Asignaciones activas', 'footerLabel' => 'Ver distribución'],
    ['title' => 'Estudiantes', 'value' => $estudiantesCount, 'icon' => 'school', 'accent' => 'info', 'href' => 'estudiantes.php', 'metricLabel' => 'Alumnos activos', 'footerLabel' => 'Revisar estudiantes'],
    ['title' => 'Dis. asignaturas', 'value' => $distribucionCount, 'icon' => 'account_tree', 'accent' => 'warning', 'href' => 'dis_asignaturas.php', 'metricLabel' => 'Asignaciones vigentes', 'footerLabel' => 'Abrir módulo'],
    ['title' => 'Inventario', 'value' => $inventarioCount, 'icon' => 'inventory_2', 'accent' => 'danger', 'href' => 'inventario.php', 'metricLabel' => 'Items activos', 'footerLabel' => 'Ver inventario'],
    ['title' => 'Documentación', 'value' => $documentacionCount, 'icon' => 'description', 'accent' => 'dark', 'href' => 'documentacion.php', 'metricLabel' => 'Archivos disponibles', 'footerLabel' => 'Abrir documentos'],
    ['title' => 'Con. evaluación', 'value' => $contenidosCount, 'icon' => 'edit_note', 'accent' => 'success', 'href' => 'con_evaluacion.php', 'metricLabel' => 'Contenidos activos', 'footerLabel' => 'Gestionar contenidos'],
    ['title' => 'Evaluaciones', 'value' => $evaluacionesCount, 'icon' => 'fact_check', 'accent' => 'info', 'href' => 'evaluaciones.php', 'metricLabel' => 'Pruebas activas', 'footerLabel' => 'Abrir evaluaciones'],
    ['title' => 'Calendario', 'value' => $calendarioCount, 'icon' => 'calendar_month', 'accent' => 'warning', 'href' => 'calendario.php', 'metricLabel' => 'Eventos activos', 'footerLabel' => 'Ver agenda'],
];

$quickLinks = [
    ['label' => 'Gestionar asignaturas', 'href' => 'asignaturas.php', 'icon' => 'menu_book'],
    ['label' => 'Revisar docentes', 'href' => 'docentes.php', 'icon' => 'co_present'],
    ['label' => 'Administrar estudiantes', 'href' => 'estudiantes.php', 'icon' => 'school'],
    ['label' => 'Controlar inventario', 'href' => 'inventario.php', 'icon' => 'inventory_2'],
    ['label' => 'Abrir documentación', 'href' => 'documentacion.php', 'icon' => 'description'],
    ['label' => 'Ver calendario', 'href' => 'calendario.php', 'icon' => 'calendar_month'],
];

$summaryItems = [
    ['label' => 'Usuario', 'value' => dashboard_user_name()],
    ['label' => 'Rol', 'value' => 'Administrador'],
    ['label' => 'Módulos visibles', 'value' => count($navSections[2]['items'])],
    ['label' => 'Registros monitoreados', 'value' => $asignaturasCount + $personalCount + $docentesCount + $estudiantesCount],
];

dashboard_render_material_page([
    'pageTitle' => 'Dashboard administrativo',
    'roleLabel' => 'Administrador',
    'welcomeTitle' => 'Control general del campus virtual',
    'welcomeText' => 'Se reemplazó la vista del dashboard por el diseño Bootstrap nuevo, manteniendo intactos los permisos, rutas y consultas del sistema.',
    'profileUrl' => $profileUrl,
    'navSections' => $navSections,
    'cards' => $cards,
    'quickLinks' => $quickLinks,
    'summaryItems' => $summaryItems,
    'heroBadges' => [
        $asignaturasCount . ' asignaturas activas',
        $estudiantesCount . ' estudiantes activos',
        $calendarioCount . ' actividades vigentes',
    ],
]);
