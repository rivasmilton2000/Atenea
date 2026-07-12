<?php
require 'session.php';
require_once '../includes/connection.php';
require_once '../includes/material_dashboard.php';
require_once '../includes/atenea_admin.php';

atenea_backoffice_require($db);

$currentRole = (string) ($_SESSION['TYPE'] ?? '');
include $currentRole === 'SuperAdmin'
    ? '../includes/sidebar_superadmin.php'
    : '../includes/sidebar_admin.php';

$dteConfigUrl = $currentRole === 'SuperAdmin' ? 'sa_dte_config.php' : 'dte_config.php';
$dteDocumentsUrl = $currentRole === 'SuperAdmin' ? 'sa_dte_documents.php' : 'dte_documents.php';
?>

<div class="alert alert-info">
    Este espacio concentra accesos de configuracion vigentes para Atenea. Las opciones heredadas de inventario, reportes y settings del sistema anterior quedaron fuera del menu y documentadas como obsoletas.
</div>

<div class="row">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow h-100">
            <div class="card-body">
                <h5 class="font-weight-bold text-primary">Correo del sitio</h5>
                <p class="text-muted">Administra el correo principal y token de envio.</p>
                <a class="btn btn-primary btn-block" href="configmail_admin.php">Abrir configuracion</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow h-100">
            <div class="card-body">
                <h5 class="font-weight-bold text-primary">Pagina publica</h5>
                <p class="text-muted">Acceso a About, servicios, noticias y galeria.</p>
                <a class="btn btn-outline-primary btn-block" href="pagina_publica_admin.php">Gestionar homepage</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow h-100">
            <div class="card-body">
                <h5 class="font-weight-bold text-primary">Facturacion DTE</h5>
                <p class="text-muted">Configuracion tecnica de facturacion electronica.</p>
                <a class="btn btn-outline-dark btn-block" href="<?php echo htmlspecialchars($dteConfigUrl, ENT_QUOTES, 'UTF-8'); ?>">Abrir DTE</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow h-100">
            <div class="card-body">
                <h5 class="font-weight-bold text-primary">Documentos DTE</h5>
                <p class="text-muted">Consulta documentos emitidos desde pagos y compras.</p>
                <a class="btn btn-outline-secondary btn-block" href="<?php echo htmlspecialchars($dteDocumentsUrl, ENT_QUOTES, 'UTF-8'); ?>">Ver documentos</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
