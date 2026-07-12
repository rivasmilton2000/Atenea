<?php

require_once __DIR__ . '/material_module_shell.php';
require_once __DIR__ . '/atenea_admin.php';

$currentPage = basename((string) ($_SERVER['PHP_SELF'] ?? ''));

module_shell_begin([
    'roleLabel' => atenea_backoffice_role_label(),
    'profileUrl' => atenea_backoffice_profile_url(),
    'headerText' => 'Panel administrativo depurado para la operacion real de Atenea, con accesos directos a estudiantes, cursos, inscripciones, videos, certificados, compras, tienda y contenido del sitio.',
    'navSections' => atenea_backoffice_nav_sections($currentPage),
]);
