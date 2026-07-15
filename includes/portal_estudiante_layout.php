<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/portal_estudiante.php';
require_once __DIR__ . '/contenido.php';
require_once __DIR__ . '/perfil_modal.php';
require_once __DIR__ . '/alerts.php';
require_once __DIR__ . '/audit.php';

function datosPortalEstudiante(int $usuarioId): array
{
    $pdo = obtenerConexion();
    $consulta = $pdo->prepare("SELECT COUNT(*) pedidos, COALESCE(SUM(estado='pagado'),0) pagados, COALESCE(SUM(CASE WHEN estado='pagado' THEN total ELSE 0 END),0) invertido FROM pedidos WHERE usuario_id=:usuario");
    $consulta->execute(['usuario' => $usuarioId]);
    $resumen = $consulta->fetch() ?: ['pedidos' => 0, 'pagados' => 0, 'invertido' => 0];
    $consulta = $pdo->prepare('SELECT id,numero,total,moneda,estado,created_at FROM pedidos WHERE usuario_id=:usuario ORDER BY created_at DESC LIMIT 8');
    $consulta->execute(['usuario' => $usuarioId]);
    $pedidos = $consulta->fetchAll();
    $consulta = $pdo->prepare("SELECT COUNT(*) FROM admin_notices WHERE user_id=:usuario AND status IN ('pendiente','visto')");
    $consulta->execute(['usuario' => $usuarioId]);
    return ['resumen' => $resumen, 'pedidos' => $pedidos, 'capacitaciones' => [], 'certificados' => [], 'avisos_pendientes' => (int) $consulta->fetchColumn()];
}

function estadoPedidoEstudiante(string $estado): string
{
    return match ($estado) {
        'pagado' => 'Pagado', 'pendiente' => 'Pendiente', 'esperando_pago' => 'Esperando pago',
        'fallido' => 'Fallido', 'cancelado' => 'Cancelado', 'reembolsado' => 'Reembolsado',
        default => ucfirst(str_replace('_', ' ', $estado)),
    };
}

function claseEstadoPedido(string $estado): string
{
    return match ($estado) { 'pagado' => 'bg-success', 'fallido', 'cancelado' => 'bg-danger', 'reembolsado' => 'bg-info', default => 'bg-warning' };
}

function portalEstudianteCabecera(string $titulo, string $activo = 'inicio', string $descripcion = '', bool $permitirIncompleto = false): array
{
    $permitirIncompleto ? exigirRol(['usuario']) : exigirPerfilCompleto();
    $perfil = obtenerPerfilUsuario((int) $_SESSION['usuario_id']);
    if (!$perfil) { header('Location: ' . atenea_url('src/login/logout.php')); exit; }
    $datos = datosPortalEstudiante((int) $perfil['id']);
    $logo = rutaImagenContenido(obtenerConfiguracionPortalEstudiante('portal_logo'), 'img/atenea-logo.png');
    $avatar = rutaFotoPerfil($perfil);
    $hora = (int) date('G');
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');
    $enlace = static fn(string $clave, string $ruta): string => 'nav-link' . ($activo === $clave ? ' active' : '');
    ?>
<!doctype html>
<html lang="es" dir="ltr"><head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?= atenea_e($titulo) ?> | Atenea</title><meta name="description" content="<?= atenea_e($descripcion ?: 'Portal del estudiante de Atenea') ?>">
  <link rel="shortcut icon" href="<?= atenea_e($logo) ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/core/libs.min.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/vendor/aos/dist/aos.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/hope-ui.min.css?v=4.0.0') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/custom.min.css?v=4.0.0') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/dark.min.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/customizer.min.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/rtl.min.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/estudiantes/assets/css/atenea-branding.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/website/assets/css/perfil-modal.css') ?>">
  <?php ateneaAlertasHead(); ?>
</head><body>
<aside class="sidebar sidebar-default sidebar-white sidebar-base navs-rounded-all">
  <div class="sidebar-header d-flex align-items-center justify-content-start"><a href="<?= atenea_url('src/estudiantes/index.php') ?>" class="navbar-brand atenea-portal-logo"><img src="<?= atenea_e($logo) ?>" class="img-fluid" alt="Atenea Escuela de Naturopatía Holística"></a><div class="sidebar-toggle" data-toggle="sidebar" data-active="true"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none"><path d="M4.25 12.27h15M10.3 18.3l-6.05-6.03 6.05-6.02" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></i></div></div>
  <div class="sidebar-body pt-0 data-scrollbar"><div class="sidebar-list"><ul class="navbar-nav iq-main-menu" id="sidebar-menu">
    <li class="nav-item static-item"><span class="nav-link static-item disabled"><span class="default-icon">Portal del estudiante</span><span class="mini-icon">-</span></span></li>
    <li class="nav-item"><a class="<?= $enlace('inicio','') ?>" href="<?= atenea_url('src/estudiantes/index.php') ?>"><i class="icon"><i class="bi bi-grid"></i></i><span class="item-name">Inicio</span></a></li>
    <li><hr class="hr-horizontal"></li><li class="nav-item static-item"><span class="nav-link static-item disabled"><span class="default-icon">Aprendizaje</span><span class="mini-icon">-</span></span></li>
    <li class="nav-item"><a class="<?= $enlace('cursos','') ?>" href="<?= atenea_url('src/estudiantes/cursos.php') ?>"><i class="icon"><i class="bi bi-journal-bookmark"></i></i><span class="item-name">Mis capacitaciones</span></a></li>
    <li class="nav-item"><a class="<?= $enlace('certificados','') ?>" href="<?= atenea_url('src/estudiantes/certificados.php') ?>"><i class="icon"><i class="bi bi-award"></i></i><span class="item-name">Certificados</span></a></li>
    <li><hr class="hr-horizontal"></li><li class="nav-item static-item"><span class="nav-link static-item disabled"><span class="default-icon">Mi cuenta</span><span class="mini-icon">-</span></span></li>
    <li class="nav-item"><button class="nav-link w-100 border-0 bg-transparent text-start" type="button" data-bs-toggle="modal" data-bs-target="#modalPerfil"><i class="icon"><i class="bi bi-person"></i></i><span class="item-name">Mi perfil</span></button></li>
    <li class="nav-item"><a class="<?= $enlace('pedidos','') ?>" href="<?= atenea_url('src/estudiantes/pedidos.php') ?>"><i class="icon"><i class="bi bi-receipt"></i></i><span class="item-name">Mis pedidos y pagos</span></a></li>
    <li class="nav-item"><a class="<?= $enlace('avisos','') ?>" href="<?= atenea_url('src/estudiantes/avisos.php') ?>"><i class="icon"><i class="bi bi-bell"></i></i><span class="item-name">Avisos administrativos</span><?php if((int)$datos['avisos_pendientes']>0):?><span class="badge bg-danger ms-auto"><?= (int)$datos['avisos_pendientes'] ?></span><?php endif;?></a></li>
    <li class="nav-item"><a class="nav-link" href="<?= atenea_url('index.php') ?>"><i class="icon"><i class="bi bi-globe"></i></i><span class="item-name">Volver al sitio</span></a></li>
    <li class="nav-item"><a class="nav-link" data-atenea-confirm="logout" href="<?= atenea_url('src/login/logout.php') ?>"><i class="icon"><i class="bi bi-box-arrow-right"></i></i><span class="item-name">Cerrar sesión</span></a></li>
  </ul></div></div><div class="sidebar-footer"></div>
</aside>
<main class="main-content">
  <nav class="nav navbar navbar-expand-lg navbar-light iq-navbar"><div class="container-fluid navbar-inner"><a href="<?= atenea_url('src/estudiantes/index.php') ?>" class="navbar-brand d-lg-none atenea-portal-logo-mobile"><img src="<?= atenea_e($logo) ?>" alt="Atenea"></a><div class="sidebar-toggle" data-toggle="sidebar" data-active="true"><i class="icon"><i class="bi bi-list"></i></i></div><button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPortal" aria-controls="navbarPortal" aria-expanded="false" aria-label="Abrir navegación"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="navbarPortal"><ul class="mb-2 navbar-nav ms-auto align-items-center navbar-list mb-lg-0"><li class="nav-item dropdown"><a class="py-0 nav-link d-flex align-items-center" href="#" id="menuPerfilEstudiante" role="button" data-bs-toggle="dropdown" aria-expanded="false"><img src="<?= atenea_e($avatar) ?>" class="theme-color-default-img img-fluid avatar avatar-50 avatar-rounded" alt="Foto de <?= atenea_e((string)$perfil['nombre']) ?>"><div class="caption ms-3 d-none d-md-block"><h6 class="mb-0 caption-title"><?= atenea_e(trim((string)$perfil['nombre'].' '.(string)$perfil['apellido'])) ?></h6><p class="mb-0 caption-sub-title"><?= atenea_e(etiquetaRol((string)$perfil['rol'])) ?></p></div></a><ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuPerfilEstudiante"><li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#modalPerfil">Mi perfil</button></li><li><a class="dropdown-item" href="<?= atenea_url('index.php') ?>">Volver al sitio</a></li><li><hr class="dropdown-divider"></li><li><a class="dropdown-item" href="<?= atenea_url('src/login/logout.php') ?>">Cerrar sesión</a></li></ul></li></ul></div></div></nav>
  <div class="iq-navbar-header" style="height:215px"><div class="container-fluid iq-container"><div class="row"><div class="col-md-12"><div class="flex-wrap d-flex justify-content-between align-items-center"><div><h1><?= atenea_e($saludo) ?>, <?= atenea_e((string)$perfil['nombre']) ?></h1><p><?= atenea_e($descripcion ?: obtenerConfiguracionPortalEstudiante('panel_texto_bienvenida')) ?></p></div></div></div></div></div><div class="iq-header-img"><img src="<?= atenea_url('src/estudiantes/assets/images/dashboard/top-header.png') ?>" alt="" class="theme-color-default-img img-fluid w-100 h-100 animated-scaleX"></div></div>
  <div class="container-fluid content-inner mt-n5 py-0">
<?php return ['perfil' => $perfil, 'datos' => $datos];
}

function portalEstudiantePie(): void
{
    ?>
  </div><footer class="footer"><div class="footer-body text-center"><?= atenea_e(obtenerConfiguracionPortalEstudiante('texto_pie_pagina')) ?> &copy; <?= date('Y') ?></div></footer>
</main>
<?php renderizarModalPerfil('estudiantes'); ?>
<script src="<?= atenea_url('src/estudiantes/assets/js/core/libs.min.js') ?>"></script>
<script src="<?= atenea_url('src/estudiantes/assets/js/core/external.min.js') ?>"></script>
<script src="<?= atenea_url('src/estudiantes/assets/js/charts/widgetcharts.js') ?>"></script>
<script src="<?= atenea_url('src/estudiantes/assets/js/charts/vectore-chart.js') ?>"></script>
<script src="<?= atenea_url('src/estudiantes/assets/js/charts/dashboard.js') ?>"></script>
<script src="<?= atenea_url('src/estudiantes/assets/js/plugins/fslightbox.js') ?>"></script>
<script src="<?= atenea_url('src/estudiantes/assets/js/plugins/setting.js') ?>"></script>
<script src="<?= atenea_url('src/estudiantes/assets/js/plugins/slider-tabs.js') ?>"></script>
<script src="<?= atenea_url('src/estudiantes/assets/js/plugins/form-wizard.js') ?>"></script>
<script src="<?= atenea_url('src/estudiantes/assets/vendor/aos/dist/aos.js') ?>"></script>
<script src="<?= atenea_url('src/estudiantes/assets/js/hope-ui.js') ?>" defer></script>
<script src="<?= atenea_url('src/website/assets/js/perfil-modal.js') ?>"></script>
<?php ateneaAlertasScripts(); ?>
</body></html><?php
}
