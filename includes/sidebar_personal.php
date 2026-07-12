<?php

require_once __DIR__ . '/material_module_shell.php';

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);

module_shell_begin([
    'roleLabel' => 'Personal',
    'profileUrl' => 'empleados_vista_perfil.php?action=edit&id=' . $memberId,
    'headerText' => 'El rol de Personal permanece en modo reducido mientras se valida si seguira activo dentro de Atenea. Se retiraron labores, vehiculos, documentos y calendarios del sistema anterior.',
    'navSections' => [
        [
            'title' => 'Panel',
            'items' => [
                ['label' => 'Inicio', 'href' => 'empleados_vista.php', 'icon' => 'dashboard', 'match' => ['empleados_vista.php']],
            ],
        ],
        [
            'title' => 'Sistema',
            'items' => [
                ['label' => 'Perfil', 'href' => 'empleados_vista_perfil.php?action=edit&id=' . $memberId, 'icon' => 'person', 'match' => ['empleados_vista_perfil.php']],
                ['label' => 'Cerrar sesion', 'href' => 'logout.php?redirect=homepage.php', 'icon' => 'logout', 'match' => ['logout.php']],
            ],
        ],
    ],
]);
