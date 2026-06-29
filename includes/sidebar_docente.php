<?php

require_once __DIR__ . '/material_module_shell.php';

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);

module_shell_begin([
    'roleLabel' => 'Docente',
    'profileUrl' => 'docentes_vista_perfil.php?action=edit&id=' . $memberId,
    'headerText' => 'Tus herramientas académicas conservan la lógica de siempre y ahora se muestran con el frontend unificado del nuevo dashboard.',
    'navSections' => [
        [
            'title' => 'Panel',
            'items' => [
                ['label' => 'Inicio', 'href' => 'docentes_vista.php', 'icon' => 'dashboard', 'match' => ['docentes_vista.php']],
            ],
        ],
        [
            'title' => 'Módulos',
            'items' => [
                ['label' => 'Asignaturas', 'href' => 'docentes_vista_asignaturas.php', 'icon' => 'menu_book', 'match' => ['docentes_vista_asignaturas.php', 'docentes_vista_contenidos.php', 'docentes_vista_evaluaciones.php', 'docentes_vista_entregas.php', 'docentes_vista_estudiantes.php', 'docentes_vista_notas.php']],
                ['label' => 'Promedios', 'href' => 'docentes_vista_promedios.php', 'icon' => 'analytics', 'match' => ['docentes_vista_promedios.php', 'docentes_vista_promedios1.php']],
                ['label' => 'Documentación', 'href' => 'docentes_vista_documentacion.php', 'icon' => 'description', 'match' => ['docentes_vista_documentacion.php']],
                ['label' => 'Cal. actividades', 'href' => 'docentes_vista_calendario.php', 'icon' => 'calendar_month', 'match' => ['docentes_vista_calendario.php']],
                ['label' => 'Mensajes', 'href' => 'mensajes_docente_lista.php', 'icon' => 'menu_book', 'match' => ['mensajes_docente_lista.php', 'mensajes_docente.php']],
            ],
        ],
    ],
]);
