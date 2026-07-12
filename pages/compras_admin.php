<?php
require 'session.php';
require_once '../includes/connection.php';
require_once '../includes/material_dashboard.php';
require_once '../includes/atenea_admin.php';

atenea_backoffice_require($db);

if (!function_exists('atenea_orders_h')) {
    function atenea_orders_h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$currentRole = (string) ($_SESSION['TYPE'] ?? '');
include $currentRole === 'SuperAdmin'
    ? '../includes/sidebar_superadmin.php'
    : '../includes/sidebar_admin.php';

$search = trim((string) ($_GET['q'] ?? ''));
$orders = atenea_backoffice_fetch_orders($db, $search);
$paidCount = 0;
$pendingCount = 0;
$dteCount = 0;

foreach ($orders as $order) {
    if ((string) ($order['estado'] ?? '') === 'paid') {
        $paidCount++;
    } else {
        $pendingCount++;
    }

    if (trim((string) ($order['codigo_generacion'] ?? '')) !== '') {
        $dteCount++;
    }
}

$dteDocumentsUrl = $currentRole === 'SuperAdmin' ? 'sa_dte_documents.php' : 'dte_documents.php';
$dteConfigUrl = $currentRole === 'SuperAdmin' ? 'sa_dte_config.php' : 'dte_config.php';
?>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card border-left-primary shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Ordenes registradas</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($orders); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-success shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Pagadas</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $paidCount; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-info shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">DTE asociados</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $dteCount; ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 1rem;">
        <h4 class="m-0 font-weight-bold text-primary">Pagos y compras</h4>
        <div class="d-flex flex-wrap" style="gap: 0.5rem;">
            <form method="get" class="form-inline">
                <input class="form-control mr-2" type="search" name="q" value="<?php echo atenea_orders_h($search); ?>" placeholder="Buscar por comprador, correo, estado o DTE">
                <button type="submit" class="btn btn-outline-primary">Buscar</button>
            </form>
            <a class="btn btn-outline-dark" href="<?php echo atenea_orders_h($dteDocumentsUrl); ?>">Documentos DTE</a>
            <a class="btn btn-outline-secondary" href="<?php echo atenea_orders_h($dteConfigUrl); ?>">Configuracion DTE</a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ORDEN</th>
                        <th>COMPRADOR</th>
                        <th>TOTAL</th>
                        <th>ESTADO</th>
                        <th>DTE</th>
                        <th>FECHA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order) : ?>
                        <?php
                        $orderDate = trim((string) ($order['paid_at'] ?? '')) !== ''
                            ? date('d/m/Y h:i A', strtotime((string) $order['paid_at']))
                            : (trim((string) ($order['created_at'] ?? '')) !== '' ? date('d/m/Y h:i A', strtotime((string) $order['created_at'])) : 'No disponible');
                        $dteLabel = trim((string) ($order['codigo_generacion'] ?? '')) !== ''
                            ? (string) $order['codigo_generacion']
                            : 'Sin DTE';
                        ?>
                        <tr>
                            <td>
                                <strong>#<?php echo (int) ($order['id'] ?? 0); ?></strong><br>
                                <small><?php echo atenea_orders_h((string) ($order['session_id'] ?? '')); ?></small>
                            </td>
                            <td>
                                <?php echo atenea_orders_h((string) ($order['billing_name'] ?? 'Sin nombre')); ?><br>
                                <small><?php echo atenea_orders_h((string) ($order['billing_email'] ?? 'Sin correo')); ?></small>
                            </td>
                            <td>$<?php echo number_format((float) ($order['total_amount'] ?? 0), 2); ?><br><small><?php echo (int) ($order['items_count'] ?? 0); ?> item(s)</small></td>
                            <td><?php echo atenea_orders_h((string) ($order['estado'] ?? 'pending_payment')); ?></td>
                            <td><?php echo atenea_orders_h($dteLabel); ?></td>
                            <td><?php echo atenea_orders_h($orderDate); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
