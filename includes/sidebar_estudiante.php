<?php

require_once __DIR__ . '/material_module_shell.php';

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);

module_shell_begin([
    'roleLabel' => 'Estudiante',
    'profileUrl' => 'estudiante_vista_perfil.php?action=edit&id=' . $memberId,
    'headerText' => 'Tus vistas académicas ahora siguen el mismo diseño del dashboard nuevo sin tocar entregas, notas ni contenidos.',
    'navSections' => [
        [
            'title' => 'Panel',
            'items' => [
                ['label' => 'Inicio', 'href' => 'estudiante_vista.php', 'icon' => 'dashboard', 'match' => ['estudiante_vista.php']],
            ],
        ],
        [
            'title' => 'Módulos',
            'items' => [
                ['label' => 'Asignaturas', 'href' => 'estudiantes_vista_asignaturas.php', 'icon' => 'menu_book', 'match' => ['estudiantes_vista_asignaturas.php', 'estudiantes_vista_contenidos.php', 'estudiantes_vista_evaluaciones.php', 'estudiantes_vista_entrega.php']],
                ['label' => 'Promedios', 'href' => 'estudiantes_vista_promedios.php', 'icon' => 'analytics', 'match' => ['estudiantes_vista_promedios.php', 'estudiantes_vista_promedio1.php']],
                ['label' => 'Pagos', 'href' => 'estudiante_pagos.php', 'icon' => 'payments', 'match' => ['estudiante_pagos.php']],
                ['label' => 'Cal. actividades', 'href' => 'estudiantes_vista_calendario.php', 'icon' => 'calendar_month', 'match' => ['estudiantes_vista_calendario.php']],
                ['label' => 'Mensajes', 'href' => 'mensajes_estudiante_lista.php', 'icon' => 'menu_book', 'match' => ['mensajes_estudiante_lista.php', 'mensajes_estudiante.php']],
            ],
        ],
    ],
]);
