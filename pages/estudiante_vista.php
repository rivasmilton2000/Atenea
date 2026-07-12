<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';

dashboard_require_role(
    $db,
    ['Estudiante'],
    [
        'Admin' => 'dashboard_admin.php',
        'Docente' => 'docentes_vista.php',
        'Personal' => 'empleados_vista.php',
        'SuperAdmin' => 'sa_vista.php',
    ]
);

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);
$fullName = trim((string) ($_SESSION['nombres_estudiante'] ?? '') . ' ' . (string) ($_SESSION['apellidos_estudiante'] ?? ''));
$email = '';

$stmt = $db->prepare(
    'SELECT COALESCE(e.nombres_estudiante, ""), COALESCE(e.apellidos_estudiante, ""), COALESCE(e.correo_estudiante, "")
     FROM users u
     LEFT JOIN estudiantes e ON e.ESTUDIANTE_ID = u.ESTUDIANTE_ID
     WHERE u.ID = ?
     LIMIT 1'
);

if ($stmt) {
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $stmt->bind_result($firstName, $lastName, $studentEmail);
    if ($stmt->fetch()) {
        $candidateName = trim($firstName . ' ' . $lastName);
        if ($candidateName !== '') {
            $fullName = $candidateName;
        }
        $email = (string) $studentEmail;
    }
    $stmt->close();
}

if ($fullName === '') {
    $fullName = dashboard_user_name() !== '' ? dashboard_user_name() : 'Cuenta legacy de estudiante';
}

$profileUrl = 'estudiante_vista_perfil.php?action=edit&id=' . $memberId;

dashboard_render_material_page([
    'pageTitle' => 'Panel de estudiante',
    'roleLabel' => 'Estudiante',
    'welcomeTitle' => 'Acceso legado en modo compatibilidad',
    'welcomeText' => 'Este rol se mantiene solo para cuentas antiguas. El flujo principal de Atenea para estudiantes registrados ahora vive en el panel de usuario con cursos, videos, record escolar, certificados y compras.',
    'profileUrl' => $profileUrl,
    'navSections' => [
        [
            'title' => 'Panel',
            'items' => [
                ['label' => 'Inicio', 'href' => 'estudiante_vista.php', 'icon' => 'dashboard', 'active' => true],
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
        ['title' => 'Rol', 'value' => 'Legacy', 'icon' => 'info', 'accent' => 'warning', 'href' => 'homepage.php', 'metricLabel' => 'Compatibilidad temporal', 'footerLabel' => 'Ir al sitio'],
        ['title' => 'Sesion', 'value' => 'Activa', 'icon' => 'verified_user', 'accent' => 'success', 'href' => 'logout.php?redirect=homepage.php', 'metricLabel' => 'Cuenta autenticada', 'footerLabel' => 'Cerrar sesion'],
    ],
    'quickLinks' => [
        ['label' => 'Ver mi perfil', 'href' => $profileUrl, 'icon' => 'person'],
        ['label' => 'Abrir sitio publico', 'href' => 'homepage.php', 'icon' => 'language'],
    ],
    'summaryItems' => [
        ['label' => 'Nombre', 'value' => $fullName],
        ['label' => 'Correo', 'value' => $email !== '' ? $email : 'No disponible'],
        ['label' => 'Rol', 'value' => 'Estudiante legacy'],
    ],
    'heroBadges' => [
        'Panel simplificado',
        'Sin modulos legacy',
        'Perfil disponible',
    ],
]);
