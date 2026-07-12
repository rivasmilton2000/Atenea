<?php

require_once __DIR__ . '/material_module_shell.php';

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);

module_shell_begin([
    'roleLabel' => 'Docente / Facilitador',
    'profileUrl' => 'docentes_vista_perfil.php?action=edit&id=' . $memberId,
    'headerText' => 'El panel de docentes queda reducido a un espacio de compatibilidad mientras Atenea termina la migracion funcional de facilitadores. Ya no expone asignaturas, notas ni evaluaciones heredadas.',
    'navSections' => [
        [
            'title' => 'Panel',
            'items' => [
                ['label' => 'Inicio', 'href' => 'docentes_vista.php', 'icon' => 'dashboard', 'match' => ['docentes_vista.php']],
            ],
        ],
        [
            'title' => 'Sistema',
            'items' => [
                ['label' => 'Perfil', 'href' => 'docentes_vista_perfil.php?action=edit&id=' . $memberId, 'icon' => 'person', 'match' => ['docentes_vista_perfil.php']],
                ['label' => 'Cerrar sesion', 'href' => 'logout.php?redirect=homepage.php', 'icon' => 'logout', 'match' => ['logout.php']],
            ],
        ],
    ],
]);
