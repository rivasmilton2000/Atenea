<?php

require_once __DIR__ . '/material_module_shell.php';

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);

module_shell_begin([
    'roleLabel' => 'Administrador',
    'profileUrl' => 'perfil.php?action=edit&id=' . $memberId,
    'headerText' => 'Todo el módulo conserva su funcionamiento original y ahora comparte la misma capa visual del dashboard renovado.',
    'navSections' => [
        [
            'title' => 'Panel',
            'items' => [
                ['label' => 'Inicio', 'href' => 'dashboard_admin.php', 'icon' => 'dashboard', 'match' => ['dashboard_admin.php']],
            ],
        ],
        [
            'title' => 'Página principal',
            'items' => [
                ['label' => 'About', 'href' => 'about_admin.php', 'icon' => 'info', 'match' => ['about_admin.php']],
                ['label' => 'Servicios', 'href' => 'servicios.php', 'icon' => 'design_services', 'match' => ['servicios.php']],
                ['label' => 'Programas de capacitación', 'href' => 'programas_admin.php', 'icon' => 'school', 'match' => ['programas_admin.php', 'programas_edit.php']],
                ['label' => 'Galería', 'href' => 'galeria_home.php', 'icon' => 'photo_library', 'match' => ['galeria_home.php']],
                ['label' => 'Noticias', 'href' => 'noticias_admin.php', 'icon' => 'newspaper', 'match' => ['noticias_admin.php', 'noticias_edit.php']],
            ],
        ],
        [
            'title' => 'Módulos',
            'items' => [
                ['label' => 'Asignaturas', 'href' => 'asignaturas.php', 'icon' => 'menu_book', 'match' => ['asignaturas.php', 'asignaturas_searchfrm.php']],
                ['label' => 'Personal', 'href' => 'personal.php', 'icon' => 'groups', 'match' => ['personal.php', 'personal_searchfrm.php', 'personal_edit.php']],
                ['label' => 'Docentes', 'href' => 'docentes.php', 'icon' => 'co_present', 'match' => ['docentes.php', 'docentes_searchfrm.php', 'docentes_edit.php']],
                ['label' => 'Labores', 'href' => 'labores.php', 'icon' => 'work', 'match' => ['labores.php', 'labores_searchfrm.php']],
                ['label' => 'Vehículos', 'href' => 'vehiculos.php', 'icon' => 'directions_car', 'match' => ['vehiculos.php', 'vehiculos_searchfrm.php', 'vehiculos_edit.php']],
                ['label' => 'Videos curso', 'href' => 'videos_admin.php', 'icon' => 'play_circle', 'match' => ['videos_admin.php', 'videos_edit.php']],
                ['label' => 'Configuración email', 'href' => 'configmail_admin.php', 'icon' => 'mail', 'match' => ['configmail_admin.php']],
                ['label' => 'Grados', 'href' => 'grados.php', 'icon' => 'domain', 'match' => ['grados.php', 'grados_searchfrm.php']],
                ['label' => 'Doc. asignaturas', 'href' => 'doc_asignaturas.php', 'icon' => 'assignment', 'match' => ['doc_asignaturas.php', 'doc_asignaturas_searchfrm.php', 'doc_asignaturas_edit.php']],
                ['label' => 'Estudiantes', 'href' => 'estudiantes.php', 'icon' => 'school', 'match' => ['estudiantes.php', 'estudiantes_searchfrm.php', 'estudiantes_searchfrm2.php', 'estudiantes_edit.php', 'estudiantes_edit2.php']],
                ['label' => 'Pagos academicos', 'href' => 'pagos_academicos.php', 'icon' => 'payments', 'match' => ['pagos_academicos.php']],
                ['label' => 'Dis. asignaturas', 'href' => 'dis_asignaturas.php', 'icon' => 'account_tree', 'match' => ['dis_asignaturas.php', 'dis_asignaturas_searchfrm.php', 'dis_asignaturas_edit.php']],
                ['label' => 'Catálogo Atenea', 'href' => 'productos_admin.php', 'icon' => 'storefront', 'match' => ['productos_admin.php', 'productos_add.php', 'productos_edit.php']],
                ['label' => 'Certificados', 'href' => 'certificados_admin.php', 'icon' => 'workspace_premium', 'match' => ['certificados_admin.php', 'certificado_pdf.php']],
                ['label' => 'Facturacion DTE', 'href' => 'dte_config.php', 'icon' => 'receipt_long', 'match' => ['dte_config.php']],
                ['label' => 'Documentos DTE', 'href' => 'dte_documents.php', 'icon' => 'description', 'match' => ['dte_documents.php']],
                ['label' => 'Categorías catálogo', 'href' => 'categorias_productos.php', 'icon' => 'category', 'match' => ['categorias_productos.php']],
                ['label' => 'Inventario', 'href' => 'inventario.php', 'icon' => 'inventory_2', 'match' => ['inventario.php', 'inventario_searchfrm.php', 'inventario_edit.php']],
                ['label' => 'Documentación', 'href' => 'documentacion.php', 'icon' => 'description', 'match' => ['documentacion.php', 'documentacion_searchfrm.php', 'documentacion_edit.php']],
                ['label' => 'Con. evaluación', 'href' => 'con_evaluacion.php', 'icon' => 'edit_note', 'match' => ['con_evaluacion.php', 'con_evaluacion_searchfrm.php', 'con_evaluacion_edit.php']],
                ['label' => 'Evaluaciones', 'href' => 'evaluaciones.php', 'icon' => 'fact_check', 'match' => ['evaluaciones.php', 'evaluaciones_searchfrm.php', 'evaluaciones_edit.php']],
                ['label' => 'Cal. actividades', 'href' => 'calendario.php', 'icon' => 'calendar_month', 'match' => ['calendario.php', 'calendario_edit.php']],
            ],
        ],
    ],
]);
