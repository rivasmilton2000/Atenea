<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';

dashboard_require_role(
    $db,
    ['Personal'],
    [
        'Admin' => 'index.php',
        'Estudiante' => 'estudiante_vista.php',
        'Docente' => 'docentes_vista.php',
        'SuperAdmin' => 'sa_vista.php',
    ]
);

$query = "SELECT u.EMPLOYEE_ID, e.FIRST_NAME, e.LAST_NAME, j.JOB_TITLE
          FROM users u
          JOIN employee e ON u.EMPLOYEE_ID = e.EMPLOYEE_ID
          JOIN job j ON e.JOB_ID = j.JOB_ID
          WHERE u.ID = " . (int) $_SESSION['MEMBER_ID'];
$result = mysqli_query($db, $query) or die(mysqli_error($db));
$row = mysqli_fetch_assoc($result);
$employeeId = (int) $row['EMPLOYEE_ID'];
$fullName = $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'];
$jobTitle = $row['JOB_TITLE'];
$profileUrl = 'empleados_vista_perfil.php?action=edit&id=' . (int) $_SESSION['MEMBER_ID'];

$laboresCount = dashboard_count($db, "SELECT COUNT(*) FROM jobs WHERE employee = $employeeId AND j_estado = 1");
$vehiculosCount = dashboard_count($db, "SELECT COUNT(*) FROM vehicles WHERE vehicle_attendant = $employeeId AND v_estado = 1");
$documentacionCount = dashboard_count($db, "SELECT COUNT(*) FROM archivos WHERE (permisos = 1 OR permisos = 3) AND a_estado = 1");
$calendarioCount = dashboard_count($db, "SELECT COUNT(*) FROM actividades WHERE ACT_ESTADO = 1");

$navSections = [
    [
        'title' => 'Panel',
        'items' => [
            ['label' => 'Inicio', 'href' => 'empleados_vista.php', 'icon' => 'dashboard', 'active' => true],
        ],
    ],
    [
        'title' => 'Módulos',
        'items' => [
            ['label' => 'Labores asignadas', 'href' => 'empleados_vista_labores.php', 'icon' => 'work'],
            ['label' => 'Vehículos asignados', 'href' => 'empleados_vista_vehiculos.php', 'icon' => 'directions_car'],
            ['label' => 'Documentación', 'href' => 'empleados_vista_documentacion.php', 'icon' => 'description'],
            ['label' => 'Cal. actividades', 'href' => 'empleados_vista_calendario.php', 'icon' => 'calendar_month'],
        ],
    ],
];

$cards = [
    ['title' => 'Labores asignadas', 'value' => $laboresCount, 'icon' => 'work', 'accent' => 'primary', 'href' => 'empleados_vista_labores.php', 'metricLabel' => 'Pendientes activas', 'footerLabel' => 'Abrir detalle'],
    ['title' => 'Vehículos asignados', 'value' => $vehiculosCount, 'icon' => 'directions_car', 'accent' => 'success', 'href' => 'empleados_vista_vehiculos.php', 'metricLabel' => 'Asignaciones vigentes', 'footerLabel' => 'Ver unidad'],
    ['title' => 'Documentación', 'value' => $documentacionCount, 'icon' => 'description', 'accent' => 'info', 'href' => 'empleados_vista_documentacion.php', 'metricLabel' => 'Archivos disponibles', 'footerLabel' => 'Abrir documentos'],
    ['title' => 'Calendario', 'value' => $calendarioCount, 'icon' => 'calendar_month', 'accent' => 'warning', 'href' => 'empleados_vista_calendario.php', 'metricLabel' => 'Eventos activos', 'footerLabel' => 'Consultar agenda'],
];

$quickLinks = [
    ['label' => 'Ir a labores', 'href' => 'empleados_vista_labores.php', 'icon' => 'work'],
    ['label' => 'Revisar vehículos', 'href' => 'empleados_vista_vehiculos.php', 'icon' => 'directions_car'],
    ['label' => 'Abrir documentación', 'href' => 'empleados_vista_documentacion.php', 'icon' => 'description'],
    ['label' => 'Consultar calendario', 'href' => 'empleados_vista_calendario.php', 'icon' => 'calendar_month'],
];

$summaryItems = [
    ['label' => 'Nombre', 'value' => $fullName],
    ['label' => 'Rol', 'value' => 'Personal'],
    ['label' => 'Cargo', 'value' => $jobTitle],
    ['label' => 'Vehículos activos', 'value' => $vehiculosCount],
];

dashboard_render_material_page([
    'pageTitle' => 'Dashboard personal',
    'roleLabel' => 'Personal',
    'welcomeTitle' => 'Tu panel operativo del día',
    'welcomeText' => 'Aquí mantienes el mismo acceso a tus labores, vehículos, documentos y calendario, ahora con la interfaz nueva del dashboard.',
    'profileUrl' => $profileUrl,
    'navSections' => $navSections,
    'cards' => $cards,
    'quickLinks' => $quickLinks,
    'summaryItems' => $summaryItems,
    'heroBadges' => [
        $laboresCount . ' labores activas',
        $vehiculosCount . ' vehículos asignados',
        $documentacionCount . ' documentos visibles',
    ],
]);
