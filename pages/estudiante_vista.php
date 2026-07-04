<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';

dashboard_require_role(
    $db,
    ['Estudiante'],
    [
        'Personal' => 'empleados_vista.php',
        'Admin' => 'index.php',
        'Docente' => 'docentes_vista.php',
        'SuperAdmin' => 'sa_vista.php',
    ]
);

$query = "SELECT u.ESTUDIANTE_ID, e.nombres_estudiante, e.apellidos_estudiante, e.carnet_estudiante, g.G_NAME
          FROM users u
          JOIN estudiantes e ON u.ESTUDIANTE_ID = e.ESTUDIANTE_ID
          LEFT JOIN grados g ON g.G_ID = e.grado_id_estudiante
          WHERE u.ID = " . (int) $_SESSION['MEMBER_ID'];
$result = mysqli_query($db, $query) or die(mysqli_error($db));
$row = mysqli_fetch_assoc($result);
$estudianteId = (int) $row['ESTUDIANTE_ID'];
$fullName = $row['nombres_estudiante'] . ' ' . $row['apellidos_estudiante'];
$carnet = $row['carnet_estudiante'];
$grado = $row['G_NAME'] ?: 'No asignado';
$profileUrl = 'estudiante_vista_perfil.php?action=edit&id=' . (int) $_SESSION['MEMBER_ID'];

$asignaturasCount = dashboard_count($db, "SELECT COUNT(*) FROM estudiantes_docentes WHERE estudiante_id = $estudianteId AND ed_estado = 1");
$promediosCount = $asignaturasCount;
$academicPendingCount = atenea_db_has_table($db, 'academic_charges')
    ? dashboard_count($db, "SELECT COUNT(*) FROM academic_charges WHERE student_id = $estudianteId AND status IN ('pending','partial','overdue')")
    : 0;
$calendarioCount = dashboard_count($db, "SELECT COUNT(*) FROM actividades WHERE ACT_ESTADO = 1");
$mensajesCount = dashboard_count($db, "SELECT COUNT(*) FROM mensajes WHERE estado = 1");

$navSections = [
    [
        'title' => 'Panel',
        'items' => [
            ['label' => 'Inicio', 'href' => 'estudiante_vista.php', 'icon' => 'dashboard', 'active' => true],
        ],
    ],
    [
        'title' => 'Módulos',
        'items' => [
            ['label' => 'Asignaturas', 'href' => 'estudiantes_vista_asignaturas.php', 'icon' => 'menu_book'],
            ['label' => 'Promedios', 'href' => 'estudiantes_vista_promedios.php', 'icon' => 'bar_chart'],
            ['label' => 'Pagos', 'href' => 'estudiante_pagos.php', 'icon' => 'payments'],
            ['label' => 'Cal. actividades', 'href' => 'estudiantes_vista_calendario.php', 'icon' => 'calendar_month'],
            ['label' => 'Mensajes', 'href' => 'mensajes_estudiante_lista.php', 'icon' => 'menu_book', 'match' => ['mensajes_estudiante_lista.php', 'mensajes_estudiante.php']],
        ],
    ],
];

$cards = [
    ['title' => 'Mis asignaturas', 'value' => $asignaturasCount, 'icon' => 'menu_book', 'accent' => 'primary', 'href' => 'estudiantes_vista_asignaturas.php', 'metricLabel' => 'Materias asignadas', 'footerLabel' => 'Abrir materias'],
    ['title' => 'Promedios', 'value' => $promediosCount, 'icon' => 'bar_chart', 'accent' => 'success', 'href' => 'estudiantes_vista_promedios.php', 'metricLabel' => 'Asignaturas evaluables', 'footerLabel' => 'Consultar notas'],
    ['title' => 'Pagos', 'value' => $academicPendingCount, 'icon' => 'payments', 'accent' => 'warning', 'href' => 'estudiante_pagos.php', 'metricLabel' => 'Cargos pendientes', 'footerLabel' => 'Ver estado de cuenta'],
    ['title' => 'Calendario', 'value' => $calendarioCount, 'icon' => 'calendar_month', 'accent' => 'info', 'href' => 'estudiantes_vista_calendario.php', 'metricLabel' => 'Eventos visibles', 'footerLabel' => 'Ver agenda'],
    ['title' => 'Mensajes', 'value' => $mensajesCount, 'icon' => 'menu_book', 'accent' => 'success', 'href' => 'mensajes_estudiante_lista.php', 'metricLabel' => 'Mensajes visibles', 'footerLabel' => 'Ver mensajes'],
];

$quickLinks = [
    ['label' => 'Entrar a mis asignaturas', 'href' => 'estudiantes_vista_asignaturas.php', 'icon' => 'menu_book'],
    ['label' => 'Consultar promedios', 'href' => 'estudiantes_vista_promedios.php', 'icon' => 'bar_chart'],
    ['label' => 'Ver pagos academicos', 'href' => 'estudiante_pagos.php', 'icon' => 'payments'],
    ['label' => 'Abrir calendario', 'href' => 'estudiantes_vista_calendario.php', 'icon' => 'calendar_month'],
    ['label' => 'Ver mi perfil', 'href' => $profileUrl, 'icon' => 'person'],
];

$summaryItems = [
    ['label' => 'Nombre', 'value' => $fullName],
    ['label' => 'Rol', 'value' => 'Estudiante'],
    ['label' => 'Grado', 'value' => $grado],
    ['label' => 'Carnet', 'value' => $carnet],
];

dashboard_render_material_page([
    'pageTitle' => 'Dashboard estudiante',
    'roleLabel' => 'Estudiante',
    'welcomeTitle' => 'Tu espacio académico',
    'welcomeText' => 'Tus accesos a materias, promedios y calendario siguen funcionando igual; solo se renovó la presentación visual del dashboard con el diseño nuevo.',
    'profileUrl' => $profileUrl,
    'navSections' => $navSections,
    'cards' => $cards,
    'quickLinks' => $quickLinks,
    'summaryItems' => $summaryItems,
    'heroBadges' => [
        $grado,
        $asignaturasCount . ' asignaturas activas',
        $calendarioCount . ' actividades disponibles',
    ],
]);
