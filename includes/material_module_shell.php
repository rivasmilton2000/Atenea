<?php

require_once __DIR__ . '/material_dashboard.php';
require_once __DIR__ . '/../pages/session.php';

if (!function_exists('module_shell_context')) {
    function module_shell_context(?array $context = null): array
    {
        static $storedContext = [];

        if ($context !== null) {
            $storedContext = array_merge($storedContext, $context);
        }

        return $storedContext;
    }
}

if (!function_exists('module_shell_current_file')) {
    function module_shell_current_file(): string
    {
        return basename((string) ($_SERVER['PHP_SELF'] ?? ''));
    }
}

if (!function_exists('module_shell_user_role')) {
    function module_shell_user_role(): string
    {
        return trim((string) ($_SESSION['TYPE'] ?? ''));
    }
}

if (!function_exists('module_shell_match_pattern')) {
    function module_shell_match_pattern(string $currentFile, string $pattern): bool
    {
        $escaped = preg_quote($pattern, '/');
        $regex = '/^' . str_replace('\*', '.*', $escaped) . '$/i';

        return (bool) preg_match($regex, $currentFile);
    }
}

if (!function_exists('module_shell_item_matches')) {
    function module_shell_item_matches(array $item, string $currentFile): bool
    {
        $href = basename((string) ($item['href'] ?? ''));
        $patterns = $item['match'] ?? [];

        if ($href !== '') {
            $patterns[] = $href;
        }

        foreach ($patterns as $pattern) {
            if (module_shell_match_pattern($currentFile, (string) $pattern)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('module_shell_find_active_item')) {
    function module_shell_find_active_item(array $navSections, string $currentFile): ?array
    {
        foreach ($navSections as $section) {
            foreach (($section['items'] ?? []) as $item) {
                if (module_shell_item_matches($item, $currentFile)) {
                    return $item;
                }
            }
        }

        return null;
    }
}

if (!function_exists('module_shell_infer_title')) {
    function module_shell_infer_title(array $navSections, string $currentFile): string
    {
        $activeItem = module_shell_find_active_item($navSections, $currentFile);
        if ($activeItem !== null && !empty($activeItem['label'])) {
            return (string) $activeItem['label'];
        }

        $base = preg_replace('/\.php$/i', '', $currentFile);
        $base = str_replace(['_', '-'], ' ', (string) $base);

        return ucwords(trim($base)) ?: 'Modulo';
    }
}

if (!function_exists('module_shell_avatar_url')) {
    function module_shell_avatar_url(): string
    {
        return dashboard_avatar_url();
    }
}

if (!function_exists('module_shell_user_name')) {
    function module_shell_user_name(): string
    {
        return dashboard_user_name();
    }
}

if (!function_exists('module_shell_render_sidebar')) {
    function module_shell_render_sidebar(array $navSections, string $currentFile): void
    {
        ?>
        <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-xl fixed-start ms-3 my-3 bg-white shadow-sm" id="sidenav-main">
          <div class="sidenav-header px-3">
            <button class="cecsb-sidenav-close d-xl-none" type="button" id="iconSidenavClose" aria-label="Cerrar menu">
              <span class="material-symbols-rounded">close</span>
            </button>
            <a class="navbar-brand m-0 d-flex align-items-center gap-2" href="#">
              <img src="../img/Atenea Logo.png" class="navbar-brand-img" alt="Atenea">
              <div class="cecsb-brand-copy">
                <span class="cecsb-brand-title">Aula Virtual</span>
                <span class="cecsb-brand-subtitle">Atenea</span>
              </div>
            </a>
          </div>
          <hr class="horizontal dark mt-0 mb-2">
          <div class="collapse navbar-collapse w-auto cecsb-sidenav-scroll" id="sidenav-collapse-main">
            <ul class="navbar-nav">
              <?php foreach ($navSections as $section): ?>
                <?php if (!empty($section['title'])): ?>
                  <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5"><?php echo dashboard_h($section['title']); ?></h6>
                  </li>
                <?php endif; ?>
                <?php foreach (($section['items'] ?? []) as $item): ?>
                  <?php
                  $isActive = module_shell_item_matches($item, $currentFile);
                  $linkClasses = $isActive ? 'nav-link active bg-gradient-dark text-white' : 'nav-link text-dark';
                  ?>
                  <li class="nav-item">
                    <a class="<?php echo $linkClasses; ?>" href="<?php echo dashboard_h($item['href'] ?? '#'); ?>">
                      <i class="material-symbols-rounded opacity-5"><?php echo dashboard_h($item['icon'] ?? 'dashboard'); ?></i>
                      <span class="nav-link-text ms-1"><?php echo dashboard_h($item['label'] ?? ''); ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              <?php endforeach; ?>
            </ul>
          </div>
        </aside>
        <?php
    }
}

if (!function_exists('module_shell_render_topbar')) {
    function module_shell_render_topbar(array $config, string $pageTitle, string $userName, string $avatarUrl): void
    {
        $roleLabel = $config['roleLabel'] ?? 'Usuario';
        $profileUrl = $config['profileUrl'] ?? '#';
        $profileAction = dashboard_resolve_profile_action($config);
        $logoutUrl = dashboard_logout_url($config);
        $topActions = $config['topActions'] ?? [];
        ?>
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
          <div class="container-fluid py-2 px-0">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb bg-transparent mb-1 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Aula Virtual Atenea</a></li>
                <li class="breadcrumb-item text-sm text-dark active" aria-current="page"><?php echo dashboard_h($pageTitle); ?></li>
              </ol>
              <h6 class="font-weight-bolder mb-0"><?php echo dashboard_h($pageTitle); ?></h6>
            </nav>
            <div class="d-flex align-items-center ms-auto gap-2 flex-wrap justify-content-end cecsb-topbar-tools">
              <button class="btn btn-link text-body p-0 d-xl-none cecsb-sidenav-toggle" type="button" id="iconNavbarSidenav" aria-label="Abrir menu">
                <span class="material-symbols-rounded">menu</span>
              </button>
              <?php foreach ($topActions as $action): ?>
                <a class="btn btn-outline-dark btn-sm mb-0 d-none d-md-inline-flex align-items-center gap-1" href="<?php echo dashboard_h($action['href'] ?? '#'); ?>">
                  <?php if (!empty($action['icon'])): ?>
                    <span class="material-symbols-rounded cecsb-inline-icon"><?php echo dashboard_h($action['icon']); ?></span>
                  <?php endif; ?>
                  <span><?php echo dashboard_h($action['label'] ?? 'Abrir'); ?></span>
                </a>
              <?php endforeach; ?>
              <span class="badge badge-sm bg-gradient-dark cecsb-user-role-badge"><?php echo dashboard_h($roleLabel); ?></span>
              <div class="dropdown cecsb-user-legacy">
                <a href="#" class="nav-link text-body p-0 d-flex align-items-center" id="userDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <img src="<?php echo dashboard_h($avatarUrl); ?>" class="avatar avatar-sm me-2 border-radius-lg" alt="avatar">
                  <span class="d-sm-inline d-none font-weight-bold"><?php echo dashboard_h($userName); ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow border-0" aria-labelledby="userDropdown">
                  <a class="dropdown-item" href="<?php echo dashboard_h($profileUrl); ?>">
                    <span class="material-symbols-rounded text-dark me-2">person</span>
                    Mi perfil
                  </a>
                  <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <span class="material-symbols-rounded text-dark me-2">logout</span>
                    Cerrar sesión
                  </a>
                </div>
              </div>
              <?php dashboard_render_user_menu($profileAction, $userName, $roleLabel, $logoutUrl); ?>
            </div>
          </div>
        </nav>
        <?php
    }
}

if (!function_exists('module_shell_render_header')) {
    function module_shell_render_header(array $config, string $pageTitle): void
    {
        $roleLabel = $config['roleLabel'] ?? 'Usuario';
        $headerText = $config['headerText'] ?? 'Mantenemos la lógica del módulo y unificamos su presentación con el nuevo frontend.';
        $currentFile = module_shell_current_file();
        ?>
        <div class="row mb-4">
          <div class="col-12">
            <div class="card cecsb-module-hero shadow-sm border-0">
              <div class="card-body p-4 p-lg-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
                  <div>
                    <p class="text-sm text-uppercase font-weight-bold mb-2 opacity-8 cecsb-module-kicker"><?php echo dashboard_h($roleLabel); ?></p>
                    <h3 class="mb-2"><?php echo dashboard_h($pageTitle); ?></h3>
                    <p class="mb-0 text-muted"><?php echo dashboard_h($headerText); ?></p>
                  </div>
                  <div class="cecsb-module-file-pill">
                    <span class="material-symbols-rounded">description</span>
                    <span><?php echo dashboard_h($currentFile); ?></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php
    }
}

if (!function_exists('module_shell_begin')) {
    function module_shell_begin(array $config): void
    {
        confirm_logged_in();

        $navSections = $config['navSections'] ?? [];
        $currentFile = module_shell_current_file();
        $pageTitle = $config['pageTitle'] ?? module_shell_infer_title($navSections, $currentFile);
        $userName = module_shell_user_name();
        $avatarUrl = module_shell_avatar_url();

        module_shell_context([
            'active' => true,
            'pageTitle' => $pageTitle,
            'profileUrl' => $config['profileUrl'] ?? '#',
            'roleLabel' => $config['roleLabel'] ?? 'Usuario',
            'logoutUrl' => $config['logoutUrl'] ?? 'logout.php?redirect=homepage.php',
        ]);
        ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title><?php echo dashboard_h($pageTitle); ?> | Aula Virtual Atenea</title>
  <link rel="icon" type="image/png" href="../img/Atenea Logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0">
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
  <link href="../css/sb-admin-2.min.css" rel="stylesheet">
  <link href="../css/atenea-ui.css" rel="stylesheet">
  <link href="<?php echo dashboard_h(dashboard_material_asset('css/nucleo-icons.css')); ?>" rel="stylesheet">
  <link href="<?php echo dashboard_h(dashboard_material_asset('css/nucleo-svg.css')); ?>" rel="stylesheet">
  <link href="<?php echo dashboard_h(dashboard_material_asset('css/material-dashboard.min.css')); ?>" rel="stylesheet">
  <link href="../css/cecsb-material-dashboard.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/atenea-ui.js" defer></script>
</head>
<body id="page-top" class="g-sidenav-show bg-gray-100 cecsb-dashboard-body cecsb-module-layout" data-loader-text="Cargando módulo...">
  <div class="cecsb-sidenav-backdrop d-xl-none" id="cecsbSidenavBackdrop"></div>
  <?php module_shell_render_sidebar($navSections, $currentFile); ?>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php module_shell_render_topbar($config, $pageTitle, $userName, $avatarUrl); ?>
    <div class="container-fluid py-4 cecsb-module-page">
      <?php module_shell_render_header($config, $pageTitle); ?>
        <?php
    }
}

if (!function_exists('module_shell_register_close')) {
    function module_shell_register_close(): void
    {
        $context = module_shell_context();
        if (empty($context['active']) || !empty($context['close_registered'])) {
            return;
        }

        module_shell_context(['close_registered' => true]);
        register_shutdown_function(static function (): void {
            $contextAtClose = module_shell_context();
            if (!empty($contextAtClose['active'])) {
                echo "\n</body>\n</html>";
                module_shell_context(['active' => false]);
            }
        });
    }
}

if (!function_exists('module_shell_render_logout_modal')) {
    function module_shell_render_logout_modal(): void
    {
        $context = module_shell_context();
        $logoutUrl = $context['logoutUrl'] ?? 'logout.php?redirect=homepage.php';
        ?>
        <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
              <div class="modal-header border-0">
                <h5 class="modal-title" id="logoutModalLabel">Cerrar sesión</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body pt-0">¿Deseas cerrar la sesión actual?</div>
              <div class="modal-footer border-0">
                <button class="btn btn-outline-dark" type="button" data-dismiss="modal">Cancelar</button>
                <a class="btn btn-primary bg-gradient-primary" href="<?php echo dashboard_h($logoutUrl); ?>">Cerrar sesión</a>
              </div>
            </div>
          </div>
        </div>
        <?php
    }
}

if (!function_exists('module_shell_render_assets')) {
    function module_shell_render_assets(): void
    {
        ?>
        <script src="../vendor/jquery/jquery.min.js"></script>
        <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
        <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
        <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
        <script src="../js/demo/datatables-demo.js"></script>
        <script src="../js/city.js"></script>
        <script>
        (function () {
          var body = document.body;
          var openTrigger = document.getElementById('iconNavbarSidenav');
          var closeTrigger = document.getElementById('iconSidenavClose');
          var backdrop = document.getElementById('cecsbSidenavBackdrop');
          var desktopBreakpoint = window.matchMedia('(min-width: 1200px)');

          function setSidebarState(isOpen) {
            if (!body) {
              return;
            }

            body.classList.toggle('cecsb-sidebar-open', isOpen);
          }

          if (openTrigger) {
            openTrigger.addEventListener('click', function (event) {
              event.preventDefault();
              setSidebarState(true);
            });
          }

          if (closeTrigger) {
            closeTrigger.addEventListener('click', function () {
              setSidebarState(false);
            });
          }

          if (backdrop) {
            backdrop.addEventListener('click', function () {
              setSidebarState(false);
            });
          }

          document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
              setSidebarState(false);
            }
          });

          if (desktopBreakpoint) {
            var syncSidebarState = function () {
              if (desktopBreakpoint.matches) {
                setSidebarState(false);
              }
            };

            if (typeof desktopBreakpoint.addEventListener === 'function') {
              desktopBreakpoint.addEventListener('change', syncSidebarState);
            } else if (typeof desktopBreakpoint.addListener === 'function') {
              desktopBreakpoint.addListener(syncSidebarState);
            }
          }
        }());
        </script>
        <?php
    }
}

if (!function_exists('module_shell_render_footer')) {
    function module_shell_render_footer(array $options = []): void
    {
        $context = module_shell_context();
        if (empty($context['active']) || !empty($context['footer_rendered'])) {
            return;
        }

        $profileUrl = $context['profileUrl'] ?? '#';
        $footerText = $options['footerText'] ?? '2026 - Derechos reservados Aula Virtual Atenea';
        ?>
        <footer class="footer py-4 mt-4">
          <div class="container-fluid px-0">
            <div class="row align-items-center justify-content-between">
              <div class="col-lg-6 mb-lg-0 mb-3">
                <div class="copyright text-center text-sm text-muted text-lg-start">
                  <?php echo dashboard_h($footerText); ?>
                </div>
              </div>
              <div class="col-lg-6">
                <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                  <li class="nav-item">
                    <a href="<?php echo dashboard_h($profileUrl); ?>" class="nav-link text-muted">Mi perfil</a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link text-muted" data-toggle="modal" data-target="#logoutModal">Cerrar sesión</a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </main>
        <a class="cecsb-scroll-top" href="#page-top" aria-label="Ir arriba">
          <span class="material-symbols-rounded">north</span>
        </a>
        <?php

        if (!empty($options['modalBundle'])) {
            $modalBundlePath = __DIR__ . '/' . ltrim((string) $options['modalBundle'], '/');
            if (is_file($modalBundlePath)) {
                global $db;
                include $modalBundlePath;
            }
        }

        if (!empty($options['renderLogoutModal'])) {
            module_shell_render_logout_modal();
        }

        module_shell_render_assets();
        module_shell_context(['footer_rendered' => true]);
        module_shell_register_close();
    }
}
