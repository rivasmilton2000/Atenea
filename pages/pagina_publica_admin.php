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

$siteStats = atenea_backoffice_fetch_site_stats($db);
?>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-left-primary shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Noticias activas</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo (int) ($siteStats['noticias'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-left-success shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Galeria activa</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo (int) ($siteStats['galeria'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-left-info shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Cursos visibles</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo (int) ($siteStats['programas'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-left-warning shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Productos visibles</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo (int) ($siteStats['productos'] ?? 0); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-0 font-weight-bold text-primary">Gestion de la pagina publica</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 col-xl-3 mb-3">
                <a class="btn btn-primary btn-block" href="about_admin.php">Sobre nosotros</a>
            </div>
            <div class="col-md-6 col-xl-3 mb-3">
                <a class="btn btn-outline-primary btn-block" href="servicios.php">Servicios</a>
            </div>
            <div class="col-md-6 col-xl-3 mb-3">
                <a class="btn btn-outline-primary btn-block" href="noticias_admin.php">Noticias</a>
            </div>
            <div class="col-md-6 col-xl-3 mb-3">
                <a class="btn btn-outline-primary btn-block" href="galeria_home.php">Galeria</a>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6 mb-3">
                <a class="btn btn-outline-dark btn-block" href="homepage.php" target="_blank" rel="noopener">Abrir homepage</a>
            </div>
            <div class="col-md-6 mb-3">
                <a class="btn btn-outline-secondary btn-block" href="configuracion_sitio.php">Configuracion del sitio</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
