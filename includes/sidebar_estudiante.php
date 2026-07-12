<?php

require_once __DIR__ . '/material_module_shell.php';

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);

module_shell_begin([
    'roleLabel' => 'Estudiante',
    'profileUrl' => 'estudiante_vista_perfil.php?action=edit&id=' . $memberId,
    'headerText' => 'Este panel legado se mantuvo solo para compatibilidad. El flujo principal de Atenea para estudiantes registrados ahora vive en el panel de usuario con cursos, videos, record escolar y certificados.',
    'navSections' => [
        [
            'title' => 'Panel',
            'items' => [
                ['label' => 'Inicio', 'href' => 'estudiante_vista.php', 'icon' => 'dashboard', 'match' => ['estudiante_vista.php']],
            ],
        ],
        [
            'title' => 'Sistema',
            'items' => [
                ['label' => 'Perfil', 'href' => 'estudiante_vista_perfil.php?action=edit&id=' . $memberId, 'icon' => 'person', 'match' => ['estudiante_vista_perfil.php']],
                ['label' => 'Cerrar sesion', 'href' => 'logout.php?redirect=homepage.php', 'icon' => 'logout', 'match' => ['logout.php']],
            ],
        ],
    ],
]);
