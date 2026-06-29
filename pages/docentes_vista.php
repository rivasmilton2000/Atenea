<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';

dashboard_require_role(
    $db,
    ['Docente'],
    [
        'Admin' => 'index.php',
        'Estudiante' => 'estudiante_vista.php',
        'Personal' => 'empleados_vista.php',
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
$profesorId = (int) $row['EMPLOYEE_ID'];
$fullName = $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'];
$jobTitle = $row['JOB_TITLE'];
$profileUrl = 'docentes_vista_perfil.php?action=edit&id=' . (int) $_SESSION['MEMBER_ID'];

$asignaturasCount = dashboard_count($db, "SELECT COUNT(*) FROM docentes_asignaturas WHERE profesor_id = $profesorId AND da_estado = 1");
$promediosCount = $asignaturasCount;
$documentacionCount = dashboard_count($db, "SELECT COUNT(*) FROM archivos WHERE (permisos = 1 OR permisos = 4) AND a_estado = 1");
$calendarioCount = dashboard_count($db, "SELECT COUNT(*) FROM actividades WHERE ACT_ESTADO = 1");
$mensajesCount = dashboard_count($db, "SELECT COUNT(*) FROM mensajes WHERE estado = 1");

$navSections = [
    [
        'title' => 'Panel',
        'items' => [
            ['label' => 'Inicio', 'href' => 'docentes_vista.php', 'icon' => 'dashboard', 'active' => true],
        ],
    ],
    [
        'title' => 'Módulos',
        'items' => [
            ['label' => 'Asignaturas', 'href' => 'docentes_vista_asignaturas.php', 'icon' => 'menu_book'],
            ['label' => 'Promedios', 'href' => 'docentes_vista_promedios.php', 'icon' => 'bar_chart'],
            ['label' => 'Documentación', 'href' => 'docentes_vista_documentacion.php', 'icon' => 'description'],
            ['label' => 'Cal. actividades', 'href' => 'docentes_vista_calendario.php', 'icon' => 'calendar_month'],
            ['label' => 'Mensajes', 'href' => 'mensajes_docente_lista.php', 'icon' => 'menu_book', 'match' => ['mensajes_docente_lista.php', 'mensajes_docente.php']],
        ],
    ],
];

$cards = [
    ['title' => 'Asignaturas', 'value' => $asignaturasCount, 'icon' => 'menu_book', 'accent' => 'primary', 'href' => 'docentes_vista_asignaturas.php', 'metricLabel' => 'Materias activas', 'footerLabel' => 'Abrir asignaturas'],
    ['title' => 'Promedios', 'value' => $promediosCount, 'icon' => 'bar_chart', 'accent' => 'success', 'href' => 'docentes_vista_promedios.php', 'metricLabel' => 'Materias con notas', 'footerLabel' => 'Ver promedios'],
    ['title' => 'Documentación', 'value' => $documentacionCount, 'icon' => 'description', 'accent' => 'info', 'href' => 'docentes_vista_documentacion.php', 'metricLabel' => 'Archivos visibles', 'footerLabel' => 'Abrir biblioteca'],
    ['title' => 'Calendario', 'value' => $calendarioCount, 'icon' => 'calendar_month', 'accent' => 'warning', 'href' => 'docentes_vista_calendario.php', 'metricLabel' => 'Eventos activos', 'footerLabel' => 'Consultar agenda'],
    ['title' => 'Mensajes', 'value' => $mensajesCount, 'icon' => 'menu_book', 'accent' => 'success', 'href' => 'mensajes_docente_lista.php', 'metricLabel' => 'Mensajes visibles', 'footerLabel' => 'Ver mensajes'],
];

$quickLinks = [
    ['label' => 'Entrar a asignaturas', 'href' => 'docentes_vista_asignaturas.php', 'icon' => 'menu_book'],
    ['label' => 'Revisar promedios', 'href' => 'docentes_vista_promedios.php', 'icon' => 'bar_chart'],
    ['label' => 'Abrir documentación', 'href' => 'docentes_vista_documentacion.php', 'icon' => 'description'],
    ['label' => 'Ver calendario', 'href' => 'docentes_vista_calendario.php', 'icon' => 'calendar_month'],
];

$summaryItems = [
    ['label' => 'Nombre', 'value' => $fullName],
    ['label' => 'Rol', 'value' => 'Docente'],
    ['label' => 'Cargo', 'value' => $jobTitle],
    ['label' => 'Asignaturas activas', 'value' => $asignaturasCount],
];

dashboard_render_material_page([
    'pageTitle' => 'Dashboard docente',
    'roleLabel' => 'Docente',
    'welcomeTitle' => 'Tu centro de clases y seguimiento',
    'welcomeText' => 'Se conservó el funcionamiento del panel docente y se montó encima el diseño nuevo para consultar materias, promedios, documentos y agenda.',
    'profileUrl' => $profileUrl,
    'navSections' => $navSections,
    'cards' => $cards,
    'quickLinks' => $quickLinks,
    'summaryItems' => $summaryItems,
    'heroBadges' => [
        $asignaturasCount . ' asignaturas activas',
        $documentacionCount . ' documentos visibles',
        $calendarioCount . ' eventos en agenda',
    ],
]);
