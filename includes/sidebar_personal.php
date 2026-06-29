<?php

require_once __DIR__ . '/material_module_shell.php';

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);

module_shell_begin([
    'roleLabel' => 'Personal',
    'profileUrl' => 'empleados_vista_perfil.php?action=edit&id=' . $memberId,
    'headerText' => 'El panel operativo del personal ahora comparte la misma interfaz moderna del dashboard sin alterar procesos internos.',
    'navSections' => [
        [
            'title' => 'Panel',
            'items' => [
                ['label' => 'Inicio', 'href' => 'empleados_vista.php', 'icon' => 'dashboard', 'match' => ['empleados_vista.php']],
            ],
        ],
        [
            'title' => 'Módulos',
            'items' => [
                ['label' => 'Labores asignadas', 'href' => 'empleados_vista_labores.php', 'icon' => 'work', 'match' => ['empleados_vista_labores.php']],
                ['label' => 'Vehículos asignados', 'href' => 'empleados_vista_vehiculos.php', 'icon' => 'directions_car', 'match' => ['empleados_vista_vehiculos.php']],
                ['label' => 'Documentación', 'href' => 'empleados_vista_documentacion.php', 'icon' => 'description', 'match' => ['empleados_vista_documentacion.php']],
                ['label' => 'Cal. actividades', 'href' => 'empleados_vista_calendario.php', 'icon' => 'calendar_month', 'match' => ['empleados_vista_calendario.php']],
            ],
        ],
    ],
]);
