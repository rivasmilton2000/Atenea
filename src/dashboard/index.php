<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/cms.php';

$pdo = obtenerConexion();
$usuarioAdmin = obtenerUsuarioActual() ?? [];
$configuracionAdmin = obtenerConfiguracionSitio();
$logoAdmin = rutaImagenContenido($configuracionAdmin['logo'] ?? 'img/atenea-logo.png', 'img/atenea-logo.png');
$faviconAdmin = rutaImagenContenido($configuracionAdmin['favicon'] ?? 'img/atenea-logo.png', 'img/atenea-logo.png');
$nombreAdmin = trim((string) (($usuarioAdmin['nombre'] ?? 'Administrador') . ' ' . ($usuarioAdmin['apellido'] ?? '')));
$correoAdmin = (string) ($usuarioAdmin['correo'] ?? '');
$fotoSesionAdmin = trim((string) ($usuarioAdmin['foto'] ?? ''));
$fotoAdmin = $fotoSesionAdmin !== '' ? rutaImagenContenido($fotoSesionAdmin, 'src/dashboard/assets/images/faces/face8.jpg') : atenea_url('src/dashboard/assets/images/faces/face8.jpg');
$fechaDashboard = date('d/m/Y');
$horaDashboard = (int) date('G');
$saludoDashboard = $horaDashboard < 12 ? 'Buenos días' : ($horaDashboard < 18 ? 'Buenas tardes' : 'Buenas noches');

function contarPanel(PDO $pdo, string $sql, array $params = []): int
{
    try {
        $consulta = $pdo->prepare($sql);
        $consulta->execute($params);
        return (int) $consulta->fetchColumn();
    } catch (Throwable $e) {
        error_log('Dashboard Atenea: ' . $e->getMessage());
        return 0;
    }
}

$totalUsuarios = contarPanel($pdo, 'SELECT COUNT(*) FROM usuarios');
$totalEstudiantes = contarPanel($pdo, "SELECT COUNT(*) FROM usuarios WHERE rol = 'usuario'");
$totalDocentes = contarPanel($pdo, "SELECT COUNT(*) FROM usuarios WHERE rol = 'docente'");
$totalAdministradores = contarPanel($pdo, "SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'");
$seccionesActivas = contarPanel($pdo, 'SELECT COUNT(*) FROM secciones WHERE activo = 1');
$capacitacionesPublicadas = contarPanel($pdo, "SELECT COUNT(*) FROM secciones WHERE clave LIKE '%capacitacion%' AND activo = 1")
    + contarPanel($pdo, "SELECT COUNT(*) FROM elementos_seccion WHERE tipo = 'capacitacion' AND activo = 1");

$usuariosMensuales = array_fill(1, 12, 0);
$contenidoMensual = array_fill(1, 12, 0);
try {
    $filasMes = $pdo->query("SELECT MONTH(created_at) mes, COUNT(*) total FROM usuarios WHERE YEAR(created_at) = YEAR(CURDATE()) AND rol = 'usuario' GROUP BY MONTH(created_at)")->fetchAll();
    foreach ($filasMes as $filaMes) {
        $usuariosMensuales[(int) $filaMes['mes']] = (int) $filaMes['total'];
    }
} catch (Throwable $e) {
    error_log('Dashboard Atenea usuarios mensuales: ' . $e->getMessage());
}
try {
    $filasContenido = $pdo->query("SELECT MONTH(COALESCE(updated_at, created_at)) mes, COUNT(*) total FROM elementos_seccion WHERE YEAR(COALESCE(updated_at, created_at)) = YEAR(CURDATE()) AND activo = 1 GROUP BY MONTH(COALESCE(updated_at, created_at))")->fetchAll();
    foreach ($filasContenido as $filaContenido) {
        $contenidoMensual[(int) $filaContenido['mes']] = (int) $filaContenido['total'];
    }
} catch (Throwable $e) {
    error_log('Dashboard Atenea contenido mensual: ' . $e->getMessage());
}

$ultimosUsuarios = [];
try {
    $ultimosUsuarios = $pdo->query("SELECT nombre, apellido, correo, rol, estado, created_at, foto FROM usuarios ORDER BY created_at DESC, id DESC LIMIT 5")->fetchAll();
} catch (Throwable $e) {
    error_log('Dashboard Atenea ultimos usuarios: ' . $e->getMessage());
}

$seccionesRecientes = [];
try {
    $seccionesRecientes = $pdo->query("SELECT nombre, titulo, activo, updated_at FROM secciones ORDER BY updated_at DESC, id DESC LIMIT 5")->fetchAll();
} catch (Throwable $e) {
    error_log('Dashboard Atenea secciones recientes: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Panel principal | Atenea</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/vendors/typicons/typicons.css">
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css" href="assets/js/select.dataTables.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="<?= $faviconAdmin ?>" />
  </head>
  <body class="with-welcome-text">
    <div class="container-scroller">
      <div class="row p-0 m-0 proBanner" id="proBanner">
        <div class="col-md-12 p-0 m-0">
          <div class="card-body card-body-padding px-3 d-flex align-items-center justify-content-between">
            <div class="ps-lg-3">
              <div class="d-flex align-items-center justify-content-between">
                <p class="mb-0 fw-medium me-3 buy-now-text">Panel administrativo de Atenea Escuela de Naturopatia Holistica</p>
                <a href="<?= atenea_url('index.php') ?>" target="_blank" class="btn me-2 buy-now-btn border-0">Ver sitio</a>
              </div>
            </div>
            <div class="d-flex align-items-center justify-content-between">
              <a href="<?= atenea_url('index.php') ?>" target="_blank"><i class="ti-home me-3 text-white"></i></a>
              <button id="bannerClose" class="btn border-0 p-0">
                <i class="ti-close text-white"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- partial:partials/_navbar.html -->
      <nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
          <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
              <span class="icon-menu"></span>
            </button>
          </div>
          <div>
            <a class="navbar-brand brand-logo" href="<?= atenea_url('src/dashboard/index.php') ?>">
              <img src="<?= $logoAdmin ?>" alt="Atenea" />
            </a>
            <a class="navbar-brand brand-logo-mini" href="<?= atenea_url('src/dashboard/index.php') ?>">
              <img src="<?= $logoAdmin ?>" alt="Atenea" />
            </a>
          </div>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-top">
          <ul class="navbar-nav">
            <li class="nav-item fw-semibold d-none d-lg-block ms-0">
              <h1 class="welcome-text"><?= $saludoDashboard ?>, <span class="text-black fw-bold"><?= atenea_e($nombreAdmin ?: 'Administrador Atenea') ?></span></h1>
              <h3 class="welcome-sub-text">Resumen de la actividad de Atenea esta semana </h3>
            </li>
          </ul>
          <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown d-none d-lg-block">
              <a class="nav-link dropdown-bordered dropdown-toggle dropdown-toggle-split" id="messageDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false"> Seleccionar categoria </a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="messageDropdown">
                <a class="dropdown-item py-3">
                  <p class="mb-0 fw-medium float-start">Seleccionar categoria</p>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis fw-medium text-dark">Contenido del sitio </p>
                    <p class="fw-light small-text mb-0">Secciones, elementos y pagina publica</p>
                  </div>
                </a>
                <a class="dropdown-item preview-item">
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis fw-medium text-dark">Usuarios</p>
                    <p class="fw-light small-text mb-0">Estudiantes, docentes y administradores</p>
                  </div>
                </a>
                <a class="dropdown-item preview-item">
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis fw-medium text-dark">Portal del estudiante</p>
                    <p class="fw-light small-text mb-0">Textos e imagenes del frontend estudiantil</p>
                  </div>
                </a>
                <a class="dropdown-item preview-item">
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis fw-medium text-dark">Configuracion general</p>
                    <p class="fw-light small-text mb-0">Identidad, logo y datos visibles</p>
                  </div>
                </a>
              </div>
            </li>
            <li class="nav-item d-none d-lg-block">
              <div id="datepicker-popup" class="input-group date datepicker navbar-date-picker">
                <span class="input-group-addon input-group-prepend border-right">
                  <span class="icon-calendar input-group-text calendar-icon"></span>
                </span>
                <input type="text" class="form-control" value="<?= atenea_e($fechaDashboard) ?>" aria-label="Fecha actual" readonly>
              </div>
            </li>
            <li class="nav-item">
              <form class="search-form" action="#">
                <i class="icon-search"></i>
                <input type="search" class="form-control" placeholder="Buscar aqui" title="Buscar aqui">
              </form>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link count-indicator" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
                <i class="icon-bell"></i>
                <span class="count"></span>
              </a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="notificationDropdown">
                <a class="dropdown-item py-3 border-bottom">
                  <p class="mb-0 fw-medium float-start">Actividad reciente del sitio </p>
                  <span class="badge badge-pill badge-primary float-end">Ver todo</span>
                </a>
                <a class="dropdown-item preview-item py-3">
                  <div class="preview-thumbnail">
                    <i class="mdi mdi-alert m-auto text-primary"></i>
                  </div>
                  <div class="preview-item-content">
                    <h6 class="preview-subject fw-normal text-dark mb-1">Secciones activas</h6>
                    <p class="fw-light small-text mb-0"> <?= number_format($seccionesActivas) ?> registradas </p>
                  </div>
                </a>
                <a class="dropdown-item preview-item py-3">
                  <div class="preview-thumbnail">
                    <i class="mdi mdi-lock-outline m-auto text-primary"></i>
                  </div>
                  <div class="preview-item-content">
                    <h6 class="preview-subject fw-normal text-dark mb-1">Usuarios registrados</h6>
                    <p class="fw-light small-text mb-0"> <?= number_format($totalUsuarios) ?> cuentas </p>
                  </div>
                </a>
                <a class="dropdown-item preview-item py-3">
                  <div class="preview-thumbnail">
                    <i class="mdi mdi-airballoon m-auto text-primary"></i>
                  </div>
                  <div class="preview-item-content">
                    <h6 class="preview-subject fw-normal text-dark mb-1">Nuevos estudiantes</h6>
                    <p class="fw-light small-text mb-0"> <?= number_format($totalEstudiantes) ?> estudiantes </p>
                  </div>
                </a>
              </div>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link count-indicator" id="countDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="icon-mail icon-lg"></i>
              </a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="countDropdown">
                <a class="dropdown-item py-3">
                  <p class="mb-0 fw-medium float-start">Accesos administrativos </p>
                  <span class="badge badge-pill badge-primary float-end">CMS</span>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="assets/images/faces/face10.jpg" alt="image" class="img-sm profile-pic">
                  </div>
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis fw-medium text-dark">Secciones </p>
                    <p class="fw-light small-text mb-0"> Administrar pagina de inicio </p>
                  </div>
                </a>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="assets/images/faces/face12.jpg" alt="image" class="img-sm profile-pic">
                  </div>
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis fw-medium text-dark">Navbar y menu </p>
                    <p class="fw-light small-text mb-0"> Editar navegacion publica </p>
                  </div>
                </a>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="assets/images/faces/face1.jpg" alt="image" class="img-sm profile-pic">
                  </div>
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis fw-medium text-dark">Configuracion </p>
                    <p class="fw-light small-text mb-0"> Ajustes generales del sitio </p>
                  </div>
                </a>
              </div>
            </li>
            <li class="nav-item dropdown d-none d-lg-block user-dropdown">
              <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="img-xs rounded-circle" src="<?= $fotoAdmin ?>" alt="Profile image"> </a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                <div class="dropdown-header text-center">
                  <img class="img-md rounded-circle" src="<?= $fotoAdmin ?>" alt="Profile image">
                  <p class="mb-1 mt-3 fw-semibold"><?= atenea_e($nombreAdmin ?: 'Administrador Atenea') ?></p>
                  <p class="fw-light text-muted mb-0"><?= atenea_e($correoAdmin) ?></p>
                </div>
                <a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> Mi perfil <span class="badge badge-pill badge-danger">1</span></a>
                <a class="dropdown-item" href="<?= atenea_url('index.php') ?>" target="_blank"><i class="dropdown-item-icon mdi mdi-web text-primary me-2"></i> Ver sitio</a>
                <a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-calendar-check-outline text-primary me-2"></i> Actividad</a>
                <a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-help-circle-outline text-primary me-2"></i> Soporte</a>
                <a class="dropdown-item" href="<?= atenea_url('src/login/logout.php') ?>"><i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Cerrar sesion</a>
              </div>
            </li>
          </ul>
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
          </button>
        </div>
      </nav>
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <nav class="sidebar sidebar-offcanvas" id="sidebar" data-active-managed="server">
          <ul class="nav">
            <li class="nav-item active">
              <a class="nav-link" href="<?= atenea_url('src/dashboard/index.php') ?>">
                <i class="mdi mdi-grid-large menu-icon"></i>
                <span class="menu-title">Panel principal</span>
              </a>
            </li>
            <li class="nav-item nav-category">Gestion del sitio web</li>
            <li class="nav-item">
              <a class="nav-link collapsed" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                <i class="menu-icon mdi mdi-floor-plan"></i>
                <span class="menu-title">Pagina de inicio</span>
                <i class="menu-arrow"></i>
              </a>
              <div class="collapse" id="ui-basic" data-bs-parent="#sidebar">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="<?= atenea_url('src/dashboard/secciones/index.php') ?>">Secciones</a></li>
                  <li class="nav-item"> <a class="nav-link" href="<?= atenea_url('src/dashboard/elementos/index.php') ?>">Elementos</a></li>
                  <li class="nav-item"> <a class="nav-link" href="<?= atenea_url('src/dashboard/secciones/index.php') ?>">Hero principal</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link collapsed" data-bs-toggle="collapse" href="#form-elements" aria-expanded="false" aria-controls="form-elements">
                <i class="menu-icon mdi mdi-card-text-outline"></i>
                <span class="menu-title">Configuracion</span>
                <i class="menu-arrow"></i>
              </a>
              <div class="collapse" id="form-elements" data-bs-parent="#sidebar">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"><a class="nav-link" href="<?= atenea_url('src/dashboard/configuracion/index.php') ?>">Configuracion general</a></li>
                  <li class="nav-item"><a class="nav-link" href="<?= atenea_url('src/dashboard/navbar/index.php') ?>">Navbar y menu</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link collapsed" data-bs-toggle="collapse" href="#charts" aria-expanded="false" aria-controls="charts">
                <i class="menu-icon mdi mdi-chart-line"></i>
                <span class="menu-title">Portal estudiante</span>
                <i class="menu-arrow"></i>
              </a>
              <div class="collapse" id="charts" data-bs-parent="#sidebar">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="<?= atenea_url('src/dashboard/portal-estudiante/index.php') ?>">Apariencia y textos</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item nav-category">Comercio</li>
            <li class="nav-item">
              <a class="nav-link collapsed" data-bs-toggle="collapse" href="#comercio" aria-expanded="false" aria-controls="comercio"><i class="menu-icon mdi mdi-cart-outline"></i><span class="menu-title">Productos y pedidos</span><i class="menu-arrow"></i></a>
              <div class="collapse" id="comercio" data-bs-parent="#sidebar"><ul class="nav flex-column sub-menu"><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/dashboard/productos/index.php')?>">Productos</a></li><li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/dashboard/pedidos/index.php')?>">Pedidos</a></li></ul></div>
            </li>
            <li class="nav-item nav-category">Gestion de usuarios</li>
            <li class="nav-item">
              <a class="nav-link collapsed" data-bs-toggle="collapse" href="#tables" aria-expanded="false" aria-controls="tables">
                <i class="menu-icon mdi mdi-table"></i>
                <span class="menu-title">Usuarios</span>
                <i class="menu-arrow"></i>
              </a>
              <div class="collapse" id="tables" data-bs-parent="#sidebar">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="#">Estudiantes</a></li>
                  <li class="nav-item"> <a class="nav-link" href="#">Docentes</a></li>
                  <li class="nav-item"> <a class="nav-link" href="#">Administradores</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link collapsed" data-bs-toggle="collapse" href="#icons" aria-expanded="false" aria-controls="icons">
                <i class="menu-icon mdi mdi-layers-outline"></i>
                <span class="menu-title">Resumen del sitio</span>
                <i class="menu-arrow"></i>
              </a>
              <div class="collapse" id="icons" data-bs-parent="#sidebar">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="<?= atenea_url('src/dashboard/index.php') ?>">Actividad general</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item nav-category">Cuenta</li>
            <li class="nav-item">
              <a class="nav-link collapsed" data-bs-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
                <i class="menu-icon mdi mdi-account-circle-outline"></i>
                <span class="menu-title">Cuenta</span>
                <i class="menu-arrow"></i>
              </a>
              <div class="collapse" id="auth" data-bs-parent="#sidebar">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="<?= atenea_url('index.php') ?>" target="_blank"> Ver sitio </a></li>
                  <li class="nav-item"> <a class="nav-link" href="#"> Mi perfil </a></li>
                  <li class="nav-item"> <a class="nav-link" href="<?= atenea_url('src/login/logout.php') ?>"> Cerrar sesion </a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= atenea_url('index.php') ?>" target="_blank">
                <i class="menu-icon mdi mdi-file-document"></i>
                <span class="menu-title">Ver sitio</span>
              </a>
            </li>
          </ul>
        </nav>
        <!-- partial -->
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-sm-12">
                <div class="home-tab">
                  <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    <ul class="nav nav-tabs" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link active ps-0" id="home-tab" data-bs-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true">Resumen</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" id="profile-tab" data-bs-toggle="tab" href="#audiences" role="tab" aria-selected="false">Usuarios</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#demographics" role="tab" aria-selected="false">Contenido</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link border-0" id="more-tab" data-bs-toggle="tab" href="#more" role="tab" aria-selected="false">Mas</a>
                      </li>
                    </ul>
                    <div>
                      <div class="btn-wrapper">
                        <a href="#" class="btn btn-otline-dark align-items-center"><i class="icon-share"></i> Compartir</a>
                        <a href="javascript:window.print()" class="btn btn-otline-dark"><i class="icon-printer"></i> Imprimir</a>
                        <a href="#" class="btn btn-primary text-white me-0"><i class="icon-download"></i> Exportar</a>
                      </div>
                    </div>
                  </div>
                  <div class="tab-content tab-content-basic">
                    <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">
                      <div class="row">
                        <div class="col-sm-12">
                          <div class="statistics-details d-flex align-items-center justify-content-between">
                            <div>
                              <p class="statistics-title">Total de usuarios</p>
                              <h3 class="rate-percentage"><?= number_format($totalUsuarios) ?></h3>
                              <p class="text-success d-flex"><i class="mdi mdi-menu-up"></i><span>db_atenea</span></p>
                            </div>
                            <div>
                              <p class="statistics-title">Estudiantes</p>
                              <h3 class="rate-percentage"><?= number_format($totalEstudiantes) ?></h3>
                              <p class="text-success d-flex"><i class="mdi mdi-menu-up"></i><span>rol usuario</span></p>
                            </div>
                            <div>
                              <p class="statistics-title">Docentes</p>
                              <h3 class="rate-percentage"><?= number_format($totalDocentes) ?></h3>
                              <p class="text-success d-flex"><i class="mdi mdi-menu-up"></i><span>rol docente</span></p>
                            </div>
                            <div class="d-none d-md-block">
                              <p class="statistics-title">Administradores</p>
                              <h3 class="rate-percentage"><?= number_format($totalAdministradores) ?></h3>
                              <p class="text-success d-flex"><i class="mdi mdi-menu-up"></i><span>rol admin</span></p>
                            </div>
                            <div class="d-none d-md-block">
                              <p class="statistics-title">Secciones activas</p>
                              <h3 class="rate-percentage"><?= number_format($seccionesActivas) ?></h3>
                              <p class="text-success d-flex"><i class="mdi mdi-menu-up"></i><span>publicadas</span></p>
                            </div>
                            <div class="d-none d-md-block">
                              <p class="statistics-title">Capacitaciones</p>
                              <h3 class="rate-percentage"><?= number_format($capacitacionesPublicadas) ?></h3>
                              <p class="text-success d-flex"><i class="mdi mdi-menu-up"></i><span>activas</span></p>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-lg-8 d-flex flex-column">
                          <div class="row flex-grow">
                            <div class="col-12 grid-margin stretch-card">
                              <div class="card card-rounded">
                                <div class="card-body">
                                  <div class="d-sm-flex justify-content-between align-items-start">
                                    <div>
                                      <h4 class="card-title card-title-dash">Actividad de la plataforma</h4>
                                      <p class="card-subtitle card-subtitle-dash">Registros de estudiantes y contenido publicado por mes</p>
                                    </div>
                                    <div>
                                      <div class="dropdown">
                                        <button class="btn btn-light dropdown-toggle toggle-dark btn-lg mb-0 me-0" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Este mes </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                          <h6 class="dropdown-header">Periodo</h6>
                                          <a class="dropdown-item" href="#">Este mes</a>
                                          <a class="dropdown-item" href="#">Este anio</a>
                                          <a class="dropdown-item" href="#">Historico</a>
                                          <div class="dropdown-divider"></div>
                                          <a class="dropdown-item" href="#">Separated link</a>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="d-sm-flex align-items-center mt-1 justify-content-between">
                                    <div class="d-sm-flex align-items-center mt-4 justify-content-between">
                                      <h2 class="me-2 fw-bold"><?= number_format(array_sum($usuariosMensuales)) ?></h2>
                                      <h4 class="me-2">estudiantes</h4>
                                      <h4 class="text-success"><?= number_format(array_sum($contenidoMensual)) ?> contenidos</h4>
                                    </div>
                                    <div class="me-3">
                                      <div id="marketingOverview-legend"></div>
                                    </div>
                                  </div>
                                  <div class="chartjs-bar-wrapper mt-3">
                                    <canvas id="marketingOverview"></canvas>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row flex-grow">
                            <div class="col-12 grid-margin stretch-card">
                              <div class="card card-rounded">
                                <div class="card-body">
                                  <div class="d-sm-flex justify-content-between align-items-start">
                                    <div>
                                      <h4 class="card-title card-title-dash">Secciones actualizadas</h4>
                                      <p class="card-subtitle card-subtitle-dash">Contenido reciente publicado en Atenea</p>
                                    </div>
                                    <div>
                                      <a class="btn btn-primary btn-lg text-white mb-0 me-0" href="<?= atenea_url('src/dashboard/secciones/crear.php') ?>"><i class="mdi mdi-plus"></i>Nueva seccion</a>
                                    </div>
                                  </div>
                                  <div class="table-responsive  mt-1">
                                    <table class="table select-table">
                                      <thead>
                                        <tr>
                                          <th>
                                            <div class="form-check form-check-flat mt-0">
                                              <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" aria-checked="false" id="check-all"><i class="input-helper"></i></label>
                                            </div>
                                          </th>
                                          <th>Seccion</th>
                                          <th>Titulo</th>
                                          <th>Actualizacion</th>
                                          <th>Estado</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        <?php if ($seccionesRecientes): ?>
                                          <?php foreach ($seccionesRecientes as $indice => $seccion): ?>
                                            <?php
                                              $activa = (int) ($seccion['activo'] ?? 0) === 1;
                                              $progreso = $activa ? 100 : 35;
                                              $barra = $activa ? 'bg-success' : 'bg-warning';
                                              $badge = $activa ? 'badge-opacity-success' : 'badge-opacity-warning';
                                              $estado = $activa ? 'Activa' : 'Inactiva';
                                              $fechaSeccion = !empty($seccion['updated_at']) ? date('d/m/Y', strtotime((string) $seccion['updated_at'])) : 'Sin fecha';
                                            ?>
                                            <tr>
                                              <td>
                                                <div class="form-check form-check-flat mt-0">
                                                  <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input" aria-checked="false"><i class="input-helper"></i></label>
                                                </div>
                                              </td>
                                              <td>
                                                <div class="d-flex ">
                                                  <img src="assets/images/faces/face<?= ($indice % 5) + 1 ?>.jpg" alt="">
                                                  <div>
                                                    <h6><?= atenea_e((string) ($seccion['nombre'] ?? 'Seccion')) ?></h6>
                                                    <p>Pagina de inicio</p>
                                                  </div>
                                                </div>
                                              </td>
                                              <td>
                                                <h6><?= atenea_e((string) ($seccion['titulo'] ?? 'Sin titulo')) ?></h6>
                                                <p>Contenido Atenea</p>
                                              </td>
                                              <td>
                                                <div>
                                                  <div class="d-flex justify-content-between align-items-center mb-1 max-width-progress-wrap">
                                                    <p class="text-success"><?= $progreso ?>%</p>
                                                    <p><?= atenea_e($fechaSeccion) ?></p>
                                                  </div>
                                                  <div class="progress progress-md">
                                                    <div class="progress-bar <?= $barra ?>" role="progressbar" style="width: <?= $progreso ?>%" aria-valuenow="<?= $progreso ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                  </div>
                                                </div>
                                              </td>
                                              <td>
                                                <div class="badge <?= $badge ?>"><?= $estado ?></div>
                                              </td>
                                            </tr>
                                          <?php endforeach; ?>
                                        <?php else: ?>
                                          <tr>
                                            <td colspan="5">
                                              <p class="text-muted mb-0">No hay secciones recientes para mostrar.</p>
                                            </td>
                                          </tr>
                                        <?php endif; ?>
                                      </tbody>
                                    </table>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-lg-4 d-flex flex-column">
                          <div class="row flex-grow">
                            <div class="col-12 grid-margin stretch-card">
                              <div class="card card-rounded">
                                <div class="card-body">
                                  <div class="row">
                                    <div class="col-lg-12">
                                      <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="card-title card-title-dash">Ultimos usuarios</h4>
                                        <div class="add-items d-flex mb-0">
                                          <!-- <input type="text" class="form-control todo-list-input" placeholder="What do you need to do today?"> -->
                                          <button class="add btn btn-icons btn-rounded btn-primary todo-list-add-btn text-white me-0 pl-12p"><i class="mdi mdi-plus"></i></button>
                                        </div>
                                      </div>
                                      <div class="list-wrapper">
                                        <ul class="todo-list todo-list-rounded">
                                          <?php if ($ultimosUsuarios): ?>
                                            <?php foreach ($ultimosUsuarios as $indiceUsuario => $usuarioReciente): ?>
                                              <?php
                                                $nombreUsuario = trim((string) (($usuarioReciente['nombre'] ?? '') . ' ' . ($usuarioReciente['apellido'] ?? '')));
                                                $fechaUsuario = !empty($usuarioReciente['created_at']) ? date('d/m/Y', strtotime((string) $usuarioReciente['created_at'])) : 'Sin fecha';
                                                $estadoUsuario = (string) ($usuarioReciente['estado'] ?? 'activo');
                                                $badgeUsuario = $estadoUsuario === 'activo' ? 'badge-opacity-success' : 'badge-opacity-danger';
                                              ?>
                                              <li class="<?= $indiceUsuario === 0 ? 'd-block' : ($indiceUsuario === count($ultimosUsuarios) - 1 ? 'border-bottom-0' : '') ?>">
                                                <div class="form-check w-100">
                                                  <label class="form-check-label">
                                                    <input class="checkbox" type="checkbox"> <?= atenea_e($nombreUsuario ?: 'Usuario Atenea') ?> <i class="input-helper rounded"></i>
                                                  </label>
                                                  <div class="d-flex mt-2">
                                                    <div class="ps-4 text-small me-3"><?= atenea_e($fechaUsuario) ?></div>
                                                    <div class="badge <?= $badgeUsuario ?> me-3"><?= atenea_e(ucfirst($estadoUsuario)) ?></div>
                                                    <?php if ($indiceUsuario === 0): ?><i class="mdi mdi-flag ms-2 flag-color"></i><?php endif; ?>
                                                  </div>
                                                </div>
                                              </li>
                                            <?php endforeach; ?>
                                          <?php else: ?>
                                            <li class="border-bottom-0">
                                              <div class="form-check w-100">
                                                <label class="form-check-label">
                                                  <input class="checkbox" type="checkbox"> No hay usuarios recientes <i class="input-helper rounded"></i>
                                                </label>
                                              </div>
                                            </li>
                                          <?php endif; ?>
                                        </ul>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row flex-grow">
                            <div class="col-12 grid-margin stretch-card">
                              <div class="card card-rounded">
                                <div class="card-body">
                                  <div class="row">
                                    <div class="col-lg-12">
                                      <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="card-title card-title-dash">Usuarios por rol</h4>
                                      </div>
                                      <div>
                                        <canvas class="my-auto" id="doughnutChart"></canvas>
                                      </div>
                                      <div id="doughnutChart-legend" class="mt-5 text-center"></div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row flex-grow">
                            <div class="col-12 grid-margin stretch-card">
                              <div class="card card-rounded">
                                <div class="card-body">
                                  <div class="row">
                                    <div class="col-lg-12">
                                      <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                          <h4 class="card-title card-title-dash">Actividad mensual</h4>
                                        </div>
                                        <div>
                                          <div class="dropdown">
                                            <button class="btn btn-light dropdown-toggle toggle-dark btn-lg mb-0 me-0" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Por mes </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton3">
                                              <h6 class="dropdown-header">Por semana</h6>
                                              <a class="dropdown-item" href="#">Por anio</a>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="mt-3">
                                        <canvas id="leaveReport"></canvas>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row flex-grow">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- content-wrapper ends -->
          <!-- partial:partials/_footer.html -->
          <footer class="footer">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
              <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Panel administrativo de Atenea.</span>
              <span class="float-none float-sm-end d-block mt-1 mt-sm-0 text-center">Copyright &copy; <?= date('Y') ?>. Todos los derechos reservados.</span>
            </div>
          </footer>
          <!-- partial -->
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="assets/vendors/chart.js/chart.umd.js"></script>
    <script src="assets/vendors/progressbar.js/progressbar.min.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/template.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/hoverable-collapse.js"></script>
    <script src="assets/js/todolist.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
    <script src="assets/js/jquery.cookie.js" type="text/javascript"></script>
    <script>
      window.ateneaDashboardData = {
        labels: ["ENE", "FEB", "MAR", "ABR", "MAY", "JUN", "JUL", "AGO", "SEP", "OCT", "NOV", "DIC"],
        labelsCortas: ["ENE", "FEB", "MAR", "ABR", "MAY"],
        usuariosMensuales: <?= json_encode(array_values($usuariosMensuales), JSON_THROW_ON_ERROR) ?>,
        contenidoMensual: <?= json_encode(array_values($contenidoMensual), JSON_THROW_ON_ERROR) ?>,
        contenidoCorto: <?= json_encode(array_slice(array_values($contenidoMensual), 0, 5), JSON_THROW_ON_ERROR) ?>,
        rolesLabels: ["Total", "Estudiantes", "Docentes", "Admins"],
        rolesData: <?= json_encode([$totalUsuarios, $totalEstudiantes, $totalDocentes, $totalAdministradores], JSON_THROW_ON_ERROR) ?>
      };
    </script>
    <script src="assets/js/dashboard.js"></script>
    <!-- <script src="assets/js/Chart.roundedBarCharts.js"></script> -->
    <!-- End custom js for this page-->
  </body>
</html>
