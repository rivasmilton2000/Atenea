<?php

require_once __DIR__ . '/material_module_shell.php';

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);
$roleLabel = module_shell_user_role() !== '' ? module_shell_user_role() : 'Panel general';

module_shell_begin([
    'roleLabel' => $roleLabel,
    'profileUrl' => 'settings.php?action=edit&id=' . $memberId,
    'headerText' => 'Las herramientas auxiliares del sistema también quedaron enlazadas al frontend nuevo para mantener una experiencia visual consistente.',
    'navSections' => [
        [
            'title' => 'Panel',
            'items' => [
                ['label' => 'Inicio', 'href' => 'index.php', 'icon' => 'home', 'match' => ['index.php']],
            ],
        ],
        [
            'title' => 'Gestión',
            'items' => [
                ['label' => 'Productos', 'href' => 'product.php', 'icon' => 'inventory', 'match' => ['product.php', 'pro_add.php', 'pro_edit.php', 'pro_searchfrm.php', 'pro_transac.php']],
                ['label' => 'Proveedores', 'href' => 'supplier.php', 'icon' => 'local_shipping', 'match' => ['supplier.php', 'sup_add.php', 'sup_edit.php', 'sup_searchfrm.php', 'sup_transac.php']],
                ['label' => 'Transacciones', 'href' => 'transaction.php', 'icon' => 'receipt_long', 'match' => ['transaction.php', 'trans_view.php']],
                ['label' => 'Cuentas', 'href' => 'user.php', 'icon' => 'badge', 'match' => ['user.php', 'us_add.php', 'us_edit.php', 'us_searchfrm.php', 'us_transac.php']],
                ['label' => 'Reportes', 'href' => 'reports.php', 'icon' => 'assessment', 'match' => ['reports.php', 'archivos.php']],
                ['label' => 'Configuración', 'href' => 'settings.php', 'icon' => 'settings', 'match' => ['settings.php']],
            ],
        ],
    ],
]);
