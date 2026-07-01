<?php

require_once __DIR__ . '/material_module_shell.php';

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);

module_shell_begin([
    'roleLabel' => 'Super administrador',
    'profileUrl' => 'sa_perfil.php?action=edit&id=' . $memberId,
    'headerText' => 'La operación global se mantiene intacta mientras todos los módulos comparten el mismo frontend nuevo del dashboard.',
    'navSections' => [
        [
            'title' => 'Panel',
            'items' => [
                ['label' => 'Inicio', 'href' => 'sa_vista.php', 'icon' => 'dashboard', 'match' => ['sa_vista.php']],
            ],
        ],
        [
            'title' => 'Módulos',
            'items' => [
                ['label' => 'Asignaturas', 'href' => 'sa_asignaturas.php', 'icon' => 'menu_book', 'match' => ['sa_asignaturas.php', 'sa_asignaturas_searchfrm.php', 'sa_asignaturas_edit.php']],
                ['label' => 'Personal', 'href' => 'sa_personal.php', 'icon' => 'groups', 'match' => ['sa_personal.php', 'sa_personal_searchfrm.php', 'sa_personal_edit.php']],
                ['label' => 'Docentes', 'href' => 'sa_docentes.php', 'icon' => 'co_present', 'match' => ['sa_docentes.php', 'sa_docentes_searchfrm.php', 'sa_docentes_edit.php']],
                ['label' => 'Labores', 'href' => 'sa_labores.php', 'icon' => 'work', 'match' => ['sa_labores.php', 'sa_labores_searchfrm.php', 'sa_labores_edit.php']],
                ['label' => 'Vehículos', 'href' => 'sa_vehiculos.php', 'icon' => 'directions_car', 'match' => ['sa_vehiculos.php', 'sa_vehiculos_searchfrm.php', 'sa_vehiculos_edit.php']],
                ['label' => 'Grados', 'href' => 'sa_grados.php', 'icon' => 'domain', 'match' => ['sa_grados.php', 'sa_grados_searchfrm.php', 'sa_grados_edit.php']],
                ['label' => 'Doc. asignaturas', 'href' => 'sa_doc_asignaturas.php', 'icon' => 'assignment', 'match' => ['sa_doc_asignaturas.php', 'sa_doc_asignaturas_searchfrm.php', 'sa_doc_asignaturas_edit.php']],
                ['label' => 'Estudiantes', 'href' => 'sa_estudiantes.php', 'icon' => 'school', 'match' => ['sa_estudiantes.php', 'sa_estudiantes_searchfrm.php', 'sa_estudiantes_searchfrm2.php', 'sa_estudiantes_edit.php', 'sa_estudiantes_edit2.php']],
                ['label' => 'Dis. asignaturas', 'href' => 'sa_dis_asignaturas.php', 'icon' => 'account_tree', 'match' => ['sa_dis_asignaturas.php', 'sa_dis_asignaturas_searchfrm.php', 'sa_dis_asignaturas_edit.php']],
                ['label' => 'Inventario', 'href' => 'sa_inventario.php', 'icon' => 'inventory_2', 'match' => ['sa_inventario.php', 'sa_inventario_searchfrm.php', 'sa_inventario_edit.php']],
                ['label' => 'Facturacion DTE', 'href' => 'sa_dte_config.php', 'icon' => 'receipt_long', 'match' => ['sa_dte_config.php']],
                ['label' => 'Documentos DTE', 'href' => 'sa_dte_documents.php', 'icon' => 'description', 'match' => ['sa_dte_documents.php']],
                ['label' => 'Documentación', 'href' => 'sa_documentacion.php', 'icon' => 'description', 'match' => ['sa_documentacion.php', 'sa_documentacion_searchfrm.php', 'sa_documentacion_edit.php']],
                ['label' => 'Con. evaluación', 'href' => 'sa_con_evaluacion.php', 'icon' => 'edit_note', 'match' => ['sa_con_evaluacion.php', 'sa_con_evaluacion_searchfrm.php', 'sa_con_evaluacion_edit.php']],
                ['label' => 'Evaluaciones', 'href' => 'sa_evaluaciones.php', 'icon' => 'fact_check', 'match' => ['sa_evaluaciones.php', 'sa_evaluaciones_searchfrm.php', 'sa_evaluaciones_edit.php']],
                ['label' => 'Eva. entregadas', 'href' => 'sa_eva_entregadas.php', 'icon' => 'download', 'match' => ['sa_eva_entregadas.php', 'sa_eva_entregadas_searchfrm.php', 'sa_eva_entregadas_edit.php']],
                ['label' => 'Notas estudiantes', 'href' => 'sa_not_estudiantes.php', 'icon' => 'book_2', 'match' => ['sa_not_estudiantes.php', 'sa_not_estudiantes_searchfrm.php', 'sa_not_estudiantes_edit.php']],
                ['label' => 'Cuentas de usuario', 'href' => 'sa_cuentas_usuarios.php', 'icon' => 'badge', 'match' => ['sa_cuentas_usuarios.php', 'sa_cuentas_usuarios_searchfrm1.php', 'sa_cuentas_usuarios_searchfrm2.php', 'sa_cuentas_usuarios_edit1.php', 'sa_cuentas_usuarios_edit3.php']],
                ['label' => 'Cal. actividades', 'href' => 'sa_calendario.php', 'icon' => 'calendar_month', 'match' => ['sa_calendario.php', 'sa_calendario_edit.php']],
                ['label' => 'Respaldo BD', 'href' => 'sa_respaldo_bd.php', 'icon' => 'database', 'match' => ['sa_respaldo_bd.php']],
            ],
        ],
    ],
]);
