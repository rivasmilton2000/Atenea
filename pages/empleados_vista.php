<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';

dashboard_require_role(
    $db,
    ['Personal'],
    [
        'Admin' => 'dashboard_admin.php',
        'Estudiante' => 'estudiante_vista.php',
        'Docente' => 'docentes_vista.php',
        'SuperAdmin' => 'sa_vista.php',
    ]
);

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);
$profileUrl = 'empleados_vista_perfil.php?action=edit&id=' . $memberId;
$fullName = dashboard_user_name();
$jobTitle = 'Personal';

$stmt = $db->prepare(
    'SELECT COALESCE(e.FIRST_NAME, ""), COALESCE(e.LAST_NAME, ""), COALESCE(j.JOB_TITLE, "Personal")
     FROM users u
     LEFT JOIN employee e ON e.EMPLOYEE_ID = u.EMPLOYEE_ID
     LEFT JOIN job j ON j.JOB_ID = e.JOB_ID
     WHERE u.ID = ?
     LIMIT 1'
);

if ($stmt) {
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $stmt->bind_result($firstName, $lastName, $jobTitleValue);
    if ($stmt->fetch()) {
        $candidateName = trim($firstName . ' ' . $lastName);
        if ($candidateName !== '') {
            $fullName = $candidateName;
        }
        $jobTitle = (string) $jobTitleValue;
    }
    $stmt->close();
}

dashboard_render_material_page([
    'pageTitle' => 'Panel personal',
    'roleLabel' => 'Personal',
    'welcomeTitle' => 'Rol interno en modo reducido',
    'welcomeText' => 'El panel de Personal se mantiene solo para compatibilidad administrativa. Se retiraron labores, vehiculos, documentacion y calendarios del sistema heredado mientras se define si este rol seguira operativo en Atenea.',
    'profileUrl' => $profileUrl,
    'navSections' => [
        [
            'title' => 'Panel',
            'items' => [
                ['label' => 'Inicio', 'href' => 'empleados_vista.php', 'icon' => 'dashboard', 'active' => true],
            ],
        ],
        [
            'title' => 'Sistema',
            'items' => [
                ['label' => 'Perfil', 'href' => $profileUrl, 'icon' => 'person'],
                ['label' => 'Cerrar sesion', 'href' => 'logout.php?redirect=homepage.php', 'icon' => 'logout'],
            ],
        ],
    ],
    'cards' => [
        ['title' => 'Perfil', 'value' => 'Disponible', 'icon' => 'person', 'accent' => 'primary', 'href' => $profileUrl, 'metricLabel' => 'Actualiza tus datos', 'footerLabel' => 'Abrir perfil'],
        ['title' => 'Estado del rol', 'value' => 'Activo', 'icon' => 'badge', 'accent' => 'success', 'href' => 'homepage.php', 'metricLabel' => 'Cuenta interna vigente', 'footerLabel' => 'Ir al sitio'],
        ['title' => 'Panel', 'value' => 'Reducido', 'icon' => 'info', 'accent' => 'warning', 'href' => 'homepage.php', 'metricLabel' => 'Sin modulos heredados', 'footerLabel' => 'Ver homepage'],
    ],
    'quickLinks' => [
        ['label' => 'Mi perfil', 'href' => $profileUrl, 'icon' => 'person'],
        ['label' => 'Abrir sitio publico', 'href' => 'homepage.php', 'icon' => 'language'],
    ],
    'summaryItems' => [
        ['label' => 'Nombre', 'value' => $fullName],
        ['label' => 'Cargo', 'value' => $jobTitle !== '' ? $jobTitle : 'Personal'],
        ['label' => 'Rol', 'value' => 'Personal legacy'],
    ],
    'heroBadges' => [
        'Modo reducido',
        'Sin modulos legacy',
        'Perfil disponible',
    ],
]);
