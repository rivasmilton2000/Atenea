<?php
require 'session.php';
require_once '../includes/connection.php';
require_once '../includes/material_dashboard.php';
require_once '../includes/atenea_admin.php';

atenea_backoffice_require($db);

if (!function_exists('atenea_roles_h')) {
    function atenea_roles_h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$currentRole = (string) ($_SESSION['TYPE'] ?? '');
include $currentRole === 'SuperAdmin'
    ? '../includes/sidebar_superadmin.php'
    : '../includes/sidebar_admin.php';

$roles = atenea_backoffice_role_permissions();
?>

<div class="alert alert-info">
    La limpieza de Atenea deja activos solo los roles y permisos del flujo actual. Los permisos escolares heredados quedan documentados como obsoletos y ya no deben usarse en el panel.
</div>

<div class="row">
    <?php foreach ($roles as $role) : ?>
        <div class="col-12 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h5 class="m-0 font-weight-bold text-primary"><?php echo atenea_roles_h((string) ($role['label'] ?? 'Rol')); ?></h5>
                </div>
                <div class="card-body">
                    <?php foreach ((array) ($role['permissions'] ?? []) as $permission) : ?>
                        <p class="mb-2">• <?php echo atenea_roles_h((string) $permission); ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include '../includes/footer.php'; ?>
