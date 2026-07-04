<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';

dashboard_require_role(
    $db,
    ['SuperAdmin'],
    [
        'Personal' => 'empleados_vista.php',
        'Estudiante' => 'estudiante_vista.php',
        'Docente' => 'docentes_vista.php',
        'Admin' => 'index.php',
    ]
);

$memberId = (int) $_SESSION['MEMBER_ID'];
$profileUrl = 'sa_perfil.php?action=edit&id=' . $memberId;

$asignaturasCount = dashboard_count($db, "SELECT COUNT(*) FROM asignaturas");
$personalCount = dashboard_count($db, "SELECT COUNT(*) FROM employee WHERE JOB_ID IN (2, 3)");
$docentesCount = dashboard_count($db, "SELECT COUNT(*) FROM employee WHERE JOB_ID = 1");
$laboresCount = dashboard_count($db, "SELECT COUNT(*) FROM jobs");
$vehiculosCount = dashboard_count($db, "SELECT COUNT(*) FROM vehicles");
$gradosCount = dashboard_count($db, "SELECT COUNT(*) FROM grados");
$docAsignaturasCount = dashboard_count($db, "SELECT COUNT(*) FROM docentes_asignaturas");
$estudiantesCount = dashboard_count($db, "SELECT COUNT(*) FROM estudiantes");
$distribucionCount = dashboard_count($db, "SELECT COUNT(*) FROM estudiantes_docentes");
$academicPendingCount = atenea_db_has_table($db, 'academic_charges')
    ? dashboard_count($db, "SELECT COUNT(*) FROM academic_charges WHERE status IN ('pending','partial','overdue')")
    : 0;
$inventarioCount = dashboard_count($db, "SELECT COUNT(*) FROM inventario");
$documentacionCount = dashboard_count($db, "SELECT COUNT(*) FROM archivos");
$contenidosCount = dashboard_count($db, "SELECT COUNT(*) FROM contenidos");
$evaluacionesCount = dashboard_count($db, "SELECT COUNT(*) FROM evaluaciones");
$evaluacionesEntregadasCount = dashboard_count($db, "SELECT COUNT(*) FROM ev_entregadas");
$notasCount = dashboard_count($db, "SELECT COUNT(*) FROM notas");
$usuariosCount = dashboard_count($db, "SELECT COUNT(*) FROM users");
$calendarioCount = dashboard_count($db, "SELECT COUNT(*) FROM actividades");
$backupCount = 1;
$dteDocumentsCount = atenea_db_has_table($db, 'dte_documents')
    ? dashboard_count($db, "SELECT COUNT(*) FROM dte_documents")
    : 0;

$navSections = [
    [
        'title' => 'Panel',
        'items' => [
            ['label' => 'Inicio', 'href' => 'sa_vista.php', 'icon' => 'dashboard', 'active' => true],
        ],
    ],
    [
        'title' => 'Módulos',
        'items' => [
            ['label' => 'Asignaturas', 'href' => 'sa_asignaturas.php', 'icon' => 'menu_book'],
            ['label' => 'Personal', 'href' => 'sa_personal.php', 'icon' => 'groups'],
            ['label' => 'Docentes', 'href' => 'sa_docentes.php', 'icon' => 'co_present'],
            ['label' => 'Labores', 'href' => 'sa_labores.php', 'icon' => 'work'],
            ['label' => 'Vehículos', 'href' => 'sa_vehiculos.php', 'icon' => 'directions_car'],
            ['label' => 'Grados', 'href' => 'sa_grados.php', 'icon' => 'domain'],
            ['label' => 'Doc. asignaturas', 'href' => 'sa_doc_asignaturas.php', 'icon' => 'assignment'],
            ['label' => 'Estudiantes', 'href' => 'sa_estudiantes.php', 'icon' => 'school'],
            ['label' => 'Pagos academicos', 'href' => 'sa_pagos_academicos.php', 'icon' => 'payments'],
            ['label' => 'Dis. asignaturas', 'href' => 'sa_dis_asignaturas.php', 'icon' => 'account_tree'],
            ['label' => 'Inventario', 'href' => 'sa_inventario.php', 'icon' => 'inventory_2'],
            ['label' => 'Facturacion DTE', 'href' => 'sa_dte_config.php', 'icon' => 'receipt_long'],
            ['label' => 'Documentos DTE', 'href' => 'sa_dte_documents.php', 'icon' => 'description'],
            ['label' => 'Documentación', 'href' => 'sa_documentacion.php', 'icon' => 'description'],
            ['label' => 'Con. evaluación', 'href' => 'sa_con_evaluacion.php', 'icon' => 'edit_note'],
            ['label' => 'Evaluaciones', 'href' => 'sa_evaluaciones.php', 'icon' => 'fact_check'],
            ['label' => 'Eva. entregadas', 'href' => 'sa_eva_entregadas.php', 'icon' => 'download'],
            ['label' => 'Notas estudiantes', 'href' => 'sa_not_estudiantes.php', 'icon' => 'book_2'],
            ['label' => 'Cuentas de usuario', 'href' => 'sa_cuentas_usuarios.php', 'icon' => 'badge'],
            ['label' => 'Cal. actividades', 'href' => 'sa_calendario.php', 'icon' => 'calendar_month'],
            ['label' => 'Respaldo BD', 'href' => 'sa_respaldo_bd.php', 'icon' => 'database'],
        ],
    ],
];

$cards = [
    ['title' => 'Asignaturas', 'value' => $asignaturasCount, 'icon' => 'menu_book', 'accent' => 'primary', 'href' => 'sa_asignaturas.php', 'metricLabel' => 'Registros globales', 'footerLabel' => 'Abrir módulo'],
    ['title' => 'Personal', 'value' => $personalCount, 'icon' => 'groups', 'accent' => 'success', 'href' => 'sa_personal.php', 'metricLabel' => 'Equipo registrado', 'footerLabel' => 'Gestionar personal'],
    ['title' => 'Docentes', 'value' => $docentesCount, 'icon' => 'co_present', 'accent' => 'info', 'href' => 'sa_docentes.php', 'metricLabel' => 'Docentes registrados', 'footerLabel' => 'Ver docentes'],
    ['title' => 'Labores', 'value' => $laboresCount, 'icon' => 'work', 'accent' => 'warning', 'href' => 'sa_labores.php', 'metricLabel' => 'Puestos creados', 'footerLabel' => 'Abrir labores'],
    ['title' => 'Vehículos', 'value' => $vehiculosCount, 'icon' => 'directions_car', 'accent' => 'danger', 'href' => 'sa_vehiculos.php', 'metricLabel' => 'Vehículos registrados', 'footerLabel' => 'Ver flota'],
    ['title' => 'Grados', 'value' => $gradosCount, 'icon' => 'domain', 'accent' => 'dark', 'href' => 'sa_grados.php', 'metricLabel' => 'Niveles creados', 'footerLabel' => 'Abrir catálogo'],
    ['title' => 'Doc. asignaturas', 'value' => $docAsignaturasCount, 'icon' => 'assignment', 'accent' => 'success', 'href' => 'sa_doc_asignaturas.php', 'metricLabel' => 'Asignaciones registradas', 'footerLabel' => 'Revisar módulo'],
    ['title' => 'Estudiantes', 'value' => $estudiantesCount, 'icon' => 'school', 'accent' => 'info', 'href' => 'sa_estudiantes.php', 'metricLabel' => 'Alumnos registrados', 'footerLabel' => 'Abrir listado'],
    ['title' => 'Dis. asignaturas', 'value' => $distribucionCount, 'icon' => 'account_tree', 'accent' => 'warning', 'href' => 'sa_dis_asignaturas.php', 'metricLabel' => 'Relaciones cargadas', 'footerLabel' => 'Ver distribución'],
    ['title' => 'Pagos academicos', 'value' => $academicPendingCount, 'icon' => 'payments', 'accent' => 'warning', 'href' => 'sa_pagos_academicos.php', 'metricLabel' => 'Cargos pendientes', 'footerLabel' => 'Gestionar pagos'],
    ['title' => 'Inventario', 'value' => $inventarioCount, 'icon' => 'inventory_2', 'accent' => 'danger', 'href' => 'sa_inventario.php', 'metricLabel' => 'Items registrados', 'footerLabel' => 'Abrir inventario'],
    ['title' => 'Documentación', 'value' => $documentacionCount, 'icon' => 'description', 'accent' => 'dark', 'href' => 'sa_documentacion.php', 'metricLabel' => 'Archivos totales', 'footerLabel' => 'Ver documentos'],
    ['title' => 'Con. evaluación', 'value' => $contenidosCount, 'icon' => 'edit_note', 'accent' => 'success', 'href' => 'sa_con_evaluacion.php', 'metricLabel' => 'Contenidos cargados', 'footerLabel' => 'Gestionar contenidos'],
    ['title' => 'Evaluaciones', 'value' => $evaluacionesCount, 'icon' => 'fact_check', 'accent' => 'info', 'href' => 'sa_evaluaciones.php', 'metricLabel' => 'Evaluaciones registradas', 'footerLabel' => 'Abrir panel'],
    ['title' => 'Eva. entregadas', 'value' => $evaluacionesEntregadasCount, 'icon' => 'download', 'accent' => 'warning', 'href' => 'sa_eva_entregadas.php', 'metricLabel' => 'Entregas procesadas', 'footerLabel' => 'Ver entregas'],
    ['title' => 'Notas estudiantes', 'value' => $notasCount, 'icon' => 'book_2', 'accent' => 'danger', 'href' => 'sa_not_estudiantes.php', 'metricLabel' => 'Notas registradas', 'footerLabel' => 'Abrir calificaciones'],
    ['title' => 'Cuentas usuario', 'value' => $usuariosCount, 'icon' => 'badge', 'accent' => 'primary', 'href' => 'sa_cuentas_usuarios.php', 'metricLabel' => 'Accesos creados', 'footerLabel' => 'Gestionar usuarios'],
    ['title' => 'Calendario', 'value' => $calendarioCount, 'icon' => 'calendar_month', 'accent' => 'success', 'href' => 'sa_calendario.php', 'metricLabel' => 'Eventos globales', 'footerLabel' => 'Ver agenda'],
    ['title' => 'Documentos DTE', 'value' => $dteDocumentsCount, 'icon' => 'receipt_long', 'accent' => 'dark', 'href' => 'sa_dte_documents.php', 'metricLabel' => 'DTE registrados', 'footerLabel' => 'Abrir facturacion'],
    ['title' => 'Respaldo BD', 'value' => $backupCount, 'icon' => 'database', 'accent' => 'info', 'href' => 'sa_respaldo_bd.php', 'metricLabel' => 'Módulo disponible', 'footerLabel' => 'Administrar respaldos'],
];

$quickLinks = [
    ['label' => 'Administrar usuarios', 'href' => 'sa_cuentas_usuarios.php', 'icon' => 'badge'],
    ['label' => 'Revisar notas', 'href' => 'sa_not_estudiantes.php', 'icon' => 'book_2'],
    ['label' => 'Controlar evaluaciones', 'href' => 'sa_evaluaciones.php', 'icon' => 'fact_check'],
    ['label' => 'Gestionar pagos academicos', 'href' => 'sa_pagos_academicos.php', 'icon' => 'payments'],
    ['label' => 'Ver entregas', 'href' => 'sa_eva_entregadas.php', 'icon' => 'download'],
    ['label' => 'Abrir inventario', 'href' => 'sa_inventario.php', 'icon' => 'inventory_2'],
    ['label' => 'Configurar DTE', 'href' => 'sa_dte_config.php', 'icon' => 'receipt_long'],
    ['label' => 'Ver documentos DTE', 'href' => 'sa_dte_documents.php', 'icon' => 'description'],
    ['label' => 'Respaldar base de datos', 'href' => 'sa_respaldo_bd.php', 'icon' => 'database'],
];

$summaryItems = [
    ['label' => 'Usuario', 'value' => dashboard_user_name()],
    ['label' => 'Rol', 'value' => 'Super administrador'],
    ['label' => 'Módulos visibles', 'value' => count($navSections[1]['items'])],
    ['label' => 'Cuentas registradas', 'value' => $usuariosCount],
    ['label' => 'DTE registrados', 'value' => $dteDocumentsCount],
];

dashboard_render_material_page([
    'pageTitle' => 'Dashboard superadmin',
    'roleLabel' => 'Super administrador',
    'welcomeTitle' => 'Visión total de la plataforma',
    'welcomeText' => 'Este panel concentra la misma información del sistema anterior, pero montada sobre el diseño Bootstrap nuevo para supervisar módulos, cuentas y operación general.',
    'profileUrl' => $profileUrl,
    'navSections' => $navSections,
    'cards' => $cards,
    'quickLinks' => $quickLinks,
    'summaryItems' => $summaryItems,
    'heroBadges' => [
        $usuariosCount . ' cuentas registradas',
        $evaluacionesEntregadasCount . ' entregas acumuladas',
        $calendarioCount . ' actividades en agenda',
        $dteDocumentsCount . ' DTE registrados',
    ],
]);
