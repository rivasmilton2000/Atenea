<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';

dashboard_require_role(
    $db,
    ['Docente'],
    [
        'Admin' => 'dashboard_admin.php',
        'Estudiante' => 'estudiante_vista.php',
        'Personal' => 'empleados_vista.php',
        'SuperAdmin' => 'sa_vista.php',
    ]
);

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);
$profileUrl = 'docentes_vista_perfil.php?action=edit&id=' . $memberId;
$fullName = dashboard_user_name();
$jobTitle = 'Docente / Facilitador';

$stmt = $db->prepare(
    'SELECT COALESCE(e.FIRST_NAME, ""), COALESCE(e.LAST_NAME, ""), COALESCE(j.JOB_TITLE, "Docente / Facilitador")
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
    'pageTitle' => 'Panel docente',
    'roleLabel' => 'Docente / Facilitador',
    'welcomeTitle' => 'Espacio docente en transicion',
    'welcomeText' => 'El panel docente se redujo a un modo de compatibilidad mientras Atenea termina de definir el flujo operativo de facilitadores. Ya no expone asignaturas, promedios, evaluaciones ni documentos heredados.',
    'profileUrl' => $profileUrl,
    'navSections' => [
        [
            'title' => 'Panel',
            'items' => [
                ['label' => 'Inicio', 'href' => 'docentes_vista.php', 'icon' => 'dashboard', 'active' => true],
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
        ['title' => 'Rol', 'value' => 'Activo', 'icon' => 'co_present', 'accent' => 'success', 'href' => 'homepage.php', 'metricLabel' => 'Cuenta docente habilitada', 'footerLabel' => 'Ir al sitio'],
        ['title' => 'Estado del panel', 'value' => 'Compatibilidad', 'icon' => 'info', 'accent' => 'warning', 'href' => 'homepage.php', 'metricLabel' => 'Sin modulos legacy', 'footerLabel' => 'Ver homepage'],
    ],
    'quickLinks' => [
        ['label' => 'Mi perfil', 'href' => $profileUrl, 'icon' => 'person'],
        ['label' => 'Abrir sitio publico', 'href' => 'homepage.php', 'icon' => 'language'],
    ],
    'summaryItems' => [
        ['label' => 'Nombre', 'value' => $fullName],
        ['label' => 'Cargo', 'value' => $jobTitle !== '' ? $jobTitle : 'Docente / Facilitador'],
        ['label' => 'Rol', 'value' => 'Docente legacy'],
    ],
    'heroBadges' => [
        'Perfil disponible',
        'Rol docente',
        'Panel simplificado',
    ],
]);
