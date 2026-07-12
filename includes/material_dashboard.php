<?php

require_once __DIR__ . '/atenea_auth.php';

if (!function_exists('dashboard_h')) {
    function dashboard_h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('dashboard_count')) {
    function dashboard_count(mysqli $db, string $sql): int
    {
        $result = mysqli_query($db, $sql) or die(mysqli_error($db));
        $row = mysqli_fetch_row($result);

        return isset($row[0]) ? (int) $row[0] : 0;
    }
}

if (!function_exists('dashboard_require_role')) {
    function dashboard_require_role(mysqli $db, array $allowedRoles, array $redirectMap): void
    {
        $currentRole = '';

        if (atenea_session_is_public_user()) {
            $currentRole = 'PublicUser';
        } else {
            $memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);
            $query = 'SELECT t.TYPE
                      FROM users u
                      LEFT JOIN type t ON t.TYPE_ID = u.TYPE_ID
                      WHERE u.ID = ' . $memberId;
            $result = mysqli_query($db, $query) or die(mysqli_error($db));
            $row = mysqli_fetch_assoc($result);
            $currentRole = $row['TYPE'] ?? '';
        }

        if (!in_array($currentRole, $allowedRoles, true)) {
            $redirectUrl = $redirectMap[$currentRole] ?? atenea_dashboard_route_for_session();
            atenea_render_auth_alert(
                'warning',
                'Acceso restringido',
                'Este módulo no está disponible para tu rol actual.',
                $redirectUrl
            );
        }
    }
}

if (!function_exists('dashboard_user_name')) {
    function dashboard_user_name(): string
    {
        if (!empty($_SESSION['nombres_estudiante']) || !empty($_SESSION['apellidos_estudiante'])) {
            return trim((string) ($_SESSION['nombres_estudiante'] ?? '') . ' ' . (string) ($_SESSION['apellidos_estudiante'] ?? ''));
        }

        return trim((string) ($_SESSION['FIRST_NAME'] ?? '') . ' ' . (string) ($_SESSION['LAST_NAME'] ?? ''));
    }
}

if (!function_exists('dashboard_user_gender')) {
    function dashboard_user_gender(): string
    {
        if (!empty($_SESSION['genero_estudiante'])) {
            return (string) $_SESSION['genero_estudiante'];
        }

        return (string) ($_SESSION['GENDER'] ?? '');
    }
}

if (!function_exists('dashboard_avatar_url')) {
    function dashboard_avatar_url(): string
    {
        $profilePhoto = trim((string) ($_SESSION['PROFILE_PHOTO'] ?? ''));
        if ($profilePhoto !== '' && preg_match('/^[A-Za-z0-9_\/.-]+$/', $profilePhoto)) {
            $normalizedPhoto = ltrim(str_replace('\\', '/', $profilePhoto), '/');
            $projectRoot = realpath(__DIR__ . '/..');
            $photoPath = realpath(__DIR__ . '/../' . $normalizedPhoto);

            if ($projectRoot !== false && $photoPath !== false && strpos($photoPath, $projectRoot) === 0 && is_file($photoPath)) {
                return '../' . $normalizedPhoto;
            }
        }

        $gender = function_exists('mb_strtolower')
            ? mb_strtolower(dashboard_user_gender(), 'UTF-8')
            : strtolower(dashboard_user_gender());

        if ($gender === 'hombre') {
            return 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTS0rikanm-OEchWDtCAWQ_s1hQq1nOlQUeJr242AdtgqcdEgm0Dg';
        }

        if ($gender === 'mujer') {
            return 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSNngF0RFPjyGl4ybo78-XYxxeap88Nvsyj1_txm6L4eheH8ZBu';
        }

        return 'https://ui-avatars.com/api/?background=046845&color=ffffff&name=' . rawurlencode(dashboard_user_name());
    }
}

if (!function_exists('dashboard_material_asset')) {
    function dashboard_material_asset(string $path): string
    {
        return 'dasboard/dashboard_new/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('dashboard_accent_map')) {
    function dashboard_accent_map(string $accent): array
    {
        $map = [
            'primary' => ['icon' => 'bg-gradient-primary shadow-primary', 'text' => 'text-primary'],
            'success' => ['icon' => 'bg-gradient-success shadow-success', 'text' => 'text-success'],
            'info' => ['icon' => 'bg-gradient-info shadow-info', 'text' => 'text-info'],
            'warning' => ['icon' => 'bg-gradient-warning shadow-warning', 'text' => 'text-warning'],
            'danger' => ['icon' => 'bg-gradient-danger shadow-danger', 'text' => 'text-danger'],
            'dark' => ['icon' => 'bg-gradient-dark shadow-dark', 'text' => 'text-dark'],
        ];

        return $map[$accent] ?? $map['primary'];
    }
}

if (!function_exists('dashboard_format_value')) {
    function dashboard_format_value($value): string
    {
        if (is_int($value) || ctype_digit((string) $value)) {
            return number_format((int) $value);
        }

        if (is_float($value) || is_numeric($value)) {
            return number_format((float) $value, 2);
        }

        return (string) $value;
    }
}

if (!function_exists('dashboard_resolve_profile_action')) {
    function dashboard_resolve_profile_action(array $config): array
    {
        $action = $config['profileAction'] ?? [];
        if (($action['type'] ?? 'link') === 'modal') {
            return [
                'type' => 'modal',
                'target' => (string) ($action['target'] ?? '#profileModal'),
                'enableTopTrigger' => !empty($action['enableTopTrigger']),
                'href' => '#',
            ];
        }

        return [
            'type' => 'link',
            'target' => '',
            'enableTopTrigger' => false,
            'href' => (string) ($config['profileUrl'] ?? '#'),
        ];
    }
}

if (!function_exists('dashboard_profile_link_attrs')) {
    function dashboard_profile_link_attrs(array $action, string $className): string
    {
        $attrs = ['class="' . dashboard_h($className) . '"'];

        if (($action['type'] ?? 'link') === 'modal') {
            $attrs[] = 'href="#"';
            $attrs[] = 'data-bs-toggle="modal"';
            $attrs[] = 'data-bs-target="' . dashboard_h((string) ($action['target'] ?? '#profileModal')) . '"';
        } else {
            $attrs[] = 'href="' . dashboard_h((string) ($action['href'] ?? '#')) . '"';
        }

        return implode(' ', $attrs);
    }
}

if (!function_exists('dashboard_user_initials')) {
    function dashboard_user_initials(?string $name = null): string
    {
        $name = trim((string) ($name ?? dashboard_user_name()));
        if ($name === '') {
            return 'AT';
        }

        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $initials = '';

        foreach ($parts as $part) {
            $firstChar = function_exists('mb_substr')
                ? mb_substr((string) $part, 0, 1, 'UTF-8')
                : substr((string) $part, 0, 1);

            if ($firstChar === '') {
                continue;
            }

            $initials .= $firstChar;

            if (function_exists('mb_strlen')) {
                if (mb_strlen($initials, 'UTF-8') >= 2) {
                    break;
                }
            } elseif (strlen($initials) >= 2) {
                break;
            }
        }

        if ($initials === '') {
            $initials = function_exists('mb_substr')
                ? mb_substr($name, 0, 2, 'UTF-8')
                : substr($name, 0, 2);
        }

        return function_exists('mb_strtoupper')
            ? mb_strtoupper($initials, 'UTF-8')
            : strtoupper($initials);
    }
}

if (!function_exists('dashboard_render_user_menu')) {
    function dashboard_render_user_menu(array $profileAction, string $userName, string $roleLabel, string $logoutUrl, array $options = []): void
    {
        $triggerId = (string) ($options['triggerId'] ?? 'ateneaUserMenuTrigger');
        $menuId = (string) ($options['menuId'] ?? 'ateneaUserDropdown');
        $badgeLabel = trim($roleLabel) !== '' ? $roleLabel : 'Mi cuenta';
        $metaLabel = (string) ($options['metaLabel'] ?? 'Cuenta activa');
        $profileLabel = (string) ($options['profileLabel'] ?? 'Ver mi perfil');
        $logoutLabel = (string) ($options['logoutLabel'] ?? 'Cerrar sesión');
        $initials = dashboard_user_initials($userName);
        ?>
        <div class="atenea-user-menu" data-atenea-user-menu>
          <button
            type="button"
            class="atenea-user-menu-trigger"
            id="<?php echo dashboard_h($triggerId); ?>"
            aria-expanded="false"
            aria-haspopup="menu"
            aria-controls="<?php echo dashboard_h($menuId); ?>"
            aria-label="Abrir menú de cuenta"
          >
            <span class="atenea-user-menu-trigger-main">
              <span class="atenea-user-menu-trigger-avatar" aria-hidden="true"><?php echo dashboard_h($initials); ?></span>
              <span class="atenea-user-menu-trigger-copy">
                <span class="atenea-user-menu-trigger-badge"><?php echo dashboard_h($badgeLabel); ?></span>
                <span class="atenea-user-menu-trigger-name"><?php echo dashboard_h($userName); ?></span>
                <span class="atenea-user-menu-trigger-meta"><?php echo dashboard_h($metaLabel); ?></span>
              </span>
            </span>
            <span class="material-symbols-rounded atenea-user-menu-trigger-icon" aria-hidden="true">expand_more</span>
          </button>

          <div class="atenea-user-dropdown" id="<?php echo dashboard_h($menuId); ?>" role="menu" aria-labelledby="<?php echo dashboard_h($triggerId); ?>" hidden>
            <div class="atenea-user-dropdown-header">
              <span class="atenea-user-dropdown-avatar" aria-hidden="true"><?php echo dashboard_h($initials); ?></span>
              <div class="atenea-user-dropdown-copy">
                <strong><?php echo dashboard_h($userName); ?></strong>
                <span><?php echo dashboard_h($badgeLabel); ?></span>
              </div>
            </div>

            <a <?php echo dashboard_profile_link_attrs($profileAction, 'atenea-user-dropdown-item'); ?> role="menuitem" data-atenea-user-menu-close="true">
              <span class="material-symbols-rounded atenea-user-dropdown-item-icon" aria-hidden="true">person</span>
              <span><?php echo dashboard_h($profileLabel); ?></span>
            </a>


            <a
              class="atenea-user-dropdown-item atenea-user-dropdown-logout"
              href="<?php echo dashboard_h($logoutUrl); ?>"
              role="menuitem"
              data-atenea-user-menu-close="true"
              data-loader-text="Cerrando sesión..."
            >
              <span class="material-symbols-rounded atenea-user-dropdown-item-icon" aria-hidden="true">logout</span>
              <span><?php echo dashboard_h($logoutLabel); ?></span>
            </a>
          </div>
        </div>
        <?php
    }
}

if (!function_exists('dashboard_logout_url')) {
    function dashboard_logout_url(array $config): string
    {
        return (string) ($config['logoutUrl'] ?? 'logout.php?redirect=homepage.php');
    }
}

if (!function_exists('dashboard_render_material_page')) {
    function dashboard_render_material_page(array $config): void
    {
        $pageTitle = $config['pageTitle'] ?? 'Dashboard';
        $roleLabel = $config['roleLabel'] ?? 'Usuario';
        $welcomeTitle = $config['welcomeTitle'] ?? 'Dashboard';
        $welcomeText = $config['welcomeText'] ?? '';
        $profileUrl = $config['profileUrl'] ?? '#';
        $navSections = $config['navSections'] ?? [];
        $cards = $config['cards'] ?? [];
        $quickLinks = $config['quickLinks'] ?? [];
        $summaryItems = $config['summaryItems'] ?? [];
        $heroBadges = $config['heroBadges'] ?? [];
        $heroActions = $config['heroActions'] ?? [];
        $bodySectionsHtml = $config['bodySectionsHtml'] ?? '';
        $extraBodyHtml = $config['extraBodyHtml'] ?? '';
        $extraHeadHtml = $config['extraHeadHtml'] ?? '';
        $stylesheets = array_values(array_filter((array) ($config['stylesheets'] ?? []), static function ($value): bool {
            return is_string($value) && trim($value) !== '';
        }));
        $bodyClass = trim((string) ($config['bodyClass'] ?? ''));
        $contentClass = trim((string) ($config['contentClass'] ?? ''));
        $cardsColumnClass = trim((string) ($config['cardsColumnClass'] ?? 'col-xl-3 col-sm-6 mb-4'));
        $accountMetaItems = $config['accountMetaItems'] ?? [
            ['label' => 'Rol', 'value' => $roleLabel],
            ['label' => 'Fecha', 'value' => date('d/m/Y')],
        ];
        $footerText = $config['footerText'] ?? '2026 © Derechos reservados Atenea Escuela de Naturopatía Holística';
        $userName = dashboard_user_name();
        if (!isset($config['footerText'])) {
            $footerText = '2026 © Derechos reservados Atenea Escuela de Naturopatía Holística';
        }
        $avatarUrl = dashboard_avatar_url();
        $profileAction = dashboard_resolve_profile_action($config);
        $logoutUrl = dashboard_logout_url($config);
        $bodyClasses = trim('g-sidenav-show bg-gray-100 cecsb-dashboard-body ' . $bodyClass);
        $contentClasses = trim('container-fluid py-4 ' . $contentClass);
        ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?php echo dashboard_h($pageTitle); ?> | Aula Virtual Atenea</title>
  <link rel="icon" type="image/png" href="../img/Atenea Logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link href="<?php echo dashboard_h(dashboard_material_asset('css/nucleo-icons.css')); ?>" rel="stylesheet" />
  <link href="<?php echo dashboard_h(dashboard_material_asset('css/nucleo-svg.css')); ?>" rel="stylesheet" />
  <link id="pagestyle" href="<?php echo dashboard_h(dashboard_material_asset('css/material-dashboard.min.css')); ?>" rel="stylesheet" />
  <link href="../css/cecsb-material-dashboard.css" rel="stylesheet" />
  <link href="../css/atenea-ui.css" rel="stylesheet" />
  <?php foreach ($stylesheets as $stylesheet): ?>
  <link href="<?php echo dashboard_h($stylesheet); ?>" rel="stylesheet" />
  <?php endforeach; ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/atenea-ui.js" defer></script>
  <?php echo $extraHeadHtml; ?>
</head>
<body class="<?php echo dashboard_h($bodyClasses); ?>" data-loader-text="Cargando aula virtual...">
  <div class="cecsb-sidenav-backdrop d-xl-none" id="cecsbSidenavBackdrop"></div>
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
            <?php $linkClasses = !empty($item['active']) ? 'nav-link active bg-gradient-dark text-white' : 'nav-link text-dark'; ?>
            <li class="nav-item">
              <a
                class="<?php echo $linkClasses; ?>"
                href="<?php echo dashboard_h($item['href'] ?? '#'); ?>"
                <?php if (!empty($item['loaderText'])): ?>
                  data-loader-text="<?php echo dashboard_h((string) $item['loaderText']); ?>"
                <?php endif; ?>
              >
                <i class="material-symbols-rounded opacity-5"><?php echo dashboard_h($item['icon'] ?? 'dashboard'); ?></i>
                <span class="nav-link-text ms-1"><?php echo dashboard_h($item['label'] ?? ''); ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </ul>
    </div>
  </aside>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl cecsb-dashboard-topbar" id="navbarBlur" data-scroll="true">
      <div class="container-fluid py-2 px-0 cecsb-dashboard-topbar__inner">
        <div class="cecsb-dashboard-heading">
          <ol class="breadcrumb bg-transparent mb-1 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Aula Virtual Atenea</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page"><?php echo dashboard_h($pageTitle); ?></li>
          </ol>
          <h6 class="font-weight-bolder mb-0"><?php echo dashboard_h($pageTitle); ?></h6>
        </div>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4 cecsb-dashboard-topbar__actions" id="navbar">
          <ul class="navbar-nav ms-md-auto justify-content-end align-items-center cecsb-dashboard-topbar__nav">
            <li class="nav-item d-xl-none d-flex align-items-center cecsb-dashboard-topbar__toggle-item">
              <button type="button" class="nav-link text-body p-0 border-0 bg-transparent cecsb-sidenav-toggle" id="iconNavbarSidenav" aria-label="Abrir menu">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                </div>
              </button>
            </li>
            <li class="nav-item d-flex align-items-center me-3 cecsb-user-role-badge">
              <span class="badge badge-sm bg-gradient-dark"><?php echo dashboard_h($roleLabel); ?></span>
            </li>
            <li class="nav-item dropdown pe-2 d-flex align-items-center cecsb-user-legacy">
              <?php if (!empty($profileAction['enableTopTrigger'])): ?>
                <div class="cecsb-user-trigger-group">
                  <a <?php echo dashboard_profile_link_attrs($profileAction, 'nav-link text-body p-0 d-flex align-items-center cecsb-user-trigger'); ?>>
                    <img src="<?php echo dashboard_h($avatarUrl); ?>" class="avatar avatar-sm me-2 border-radius-lg" alt="avatar">
                    <span class="d-sm-inline d-none font-weight-bold"><?php echo dashboard_h($userName); ?></span>
                  </a>
                  <button class="btn btn-link text-body p-0 cecsb-user-trigger-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Abrir menu de cuenta">
                    <i class="material-symbols-rounded">expand_more</i>
                  </button>
                </div>
              <?php else: ?>
                <a href="javascript:;" class="nav-link text-body p-0 d-flex align-items-center" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  <img src="<?php echo dashboard_h($avatarUrl); ?>" class="avatar avatar-sm me-2 border-radius-lg" alt="avatar">
                  <span class="d-sm-inline d-none font-weight-bold"><?php echo dashboard_h($userName); ?></span>
                </a>
              <?php endif; ?>
              <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="userDropdown">
                <li>
                  <a <?php echo dashboard_profile_link_attrs($profileAction, 'dropdown-item border-radius-md'); ?>>
                    <div class="d-flex py-1 align-items-center">
                      <i class="material-symbols-rounded text-dark me-2">person</i>
                      <span>Mi perfil</span>
                    </div>
                  </a>
                </li>
                <li>
                  <button class="dropdown-item border-radius-md" type="button" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <div class="d-flex py-1 align-items-center">
                      <i class="material-symbols-rounded text-dark me-2">logout</i>
                      <span>Cerrar sesión</span>
                    </div>
                  </button>
                </li>
              </ul>
            </li>
            <li class="nav-item d-flex align-items-center">
              <?php dashboard_render_user_menu($profileAction, $userName, $roleLabel, $logoutUrl); ?>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="<?php echo dashboard_h($contentClasses); ?>">
      <div class="row mb-4 cecsb-dashboard-overview">
        <div class="col-lg-8 cecsb-dashboard-overview__hero">
          <div class="card cecsb-hero cecsb-dashboard-hero h-100">
            <div class="card-body p-4">
              <p class="text-sm text-uppercase font-weight-bold mb-2 opacity-8"><?php echo dashboard_h($roleLabel); ?></p>
              <h3 class="text-white mb-3"><?php echo dashboard_h($welcomeTitle); ?></h3>
              <p class="text-white-50 mb-4"><?php echo dashboard_h($welcomeText); ?></p>
              <?php if (!empty($heroBadges)): ?>
                <div class="d-flex flex-wrap gap-2 cecsb-dashboard-hero__badges">
                  <?php foreach ($heroBadges as $badge): ?>
                    <span class="cecsb-hero-badge"><?php echo dashboard_h($badge); ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
              <?php if (!empty($heroActions)): ?>
                <div class="d-flex flex-wrap gap-2 mt-3 cecsb-dashboard-hero__actions">
                  <?php foreach ($heroActions as $heroAction): ?>
                    <?php
                    $variant = (string) ($heroAction['variant'] ?? 'light');
                    $buttonClass = $variant === 'outline'
                        ? 'btn btn-outline-light mb-0 cecsb-dashboard-hero-action'
                        : 'btn bg-white text-dark mb-0 cecsb-dashboard-hero-action';
                    ?>
                    <a class="<?php echo dashboard_h($buttonClass); ?>" href="<?php echo dashboard_h((string) ($heroAction['href'] ?? '#')); ?>">
                      <?php if (!empty($heroAction['icon'])): ?>
                        <i class="material-symbols-rounded align-middle me-1"><?php echo dashboard_h((string) $heroAction['icon']); ?></i>
                      <?php endif; ?>
                      <?php echo dashboard_h((string) ($heroAction['label'] ?? 'Abrir')); ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-lg-4 mt-4 mt-lg-0 cecsb-dashboard-overview__account">
          <div class="card h-100 cecsb-dashboard-account">
            <div class="card-body p-4">
              <div class="d-flex align-items-center mb-4 cecsb-dashboard-account__header">
                <img src="<?php echo dashboard_h($avatarUrl); ?>" class="cecsb-user-avatar" alt="usuario">
                <div class="ms-3">
                  <p class="text-sm mb-1 text-uppercase font-weight-bold opacity-7 cecsb-dashboard-account__eyebrow">Cuenta activa</p>
                  <h5 class="mb-0"><?php echo dashboard_h($userName); ?></h5>
                </div>
              </div>
              <div class="cecsb-meta-grid cecsb-dashboard-account__meta">
                <?php foreach ($accountMetaItems as $metaItem): ?>
                  <div class="cecsb-dashboard-account__meta-item">
                    <span class="cecsb-meta-label"><?php echo dashboard_h((string) ($metaItem['label'] ?? '')); ?></span>
                    <span class="cecsb-meta-value"><?php echo dashboard_h((string) ($metaItem['value'] ?? '')); ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="mt-4 d-flex gap-2 flex-wrap cecsb-dashboard-account__actions">
                <a <?php echo dashboard_profile_link_attrs($profileAction, 'btn bg-gradient-dark mb-0'); ?>>Ver perfil</a>
                <button class="btn btn-outline-dark mb-0" type="button" data-bs-toggle="modal" data-bs-target="#logoutModal">Salir</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <?php if (!empty($cards)): ?>
      <div class="row cecsb-dashboard-stats">
        <?php foreach ($cards as $card): ?>
          <?php
          $accent = dashboard_accent_map($card['accent'] ?? 'primary');
          $title = $card['title'] ?? '';
          $value = dashboard_format_value($card['value'] ?? 0);
          $icon = $card['icon'] ?? 'dashboard';
          $metricLabel = $card['metricLabel'] ?? 'Registros activos';
          $footerLabel = $card['footerLabel'] ?? 'Abrir módulo';
          $href = $card['href'] ?? '';
          $columnClass = trim((string) ($card['columnClass'] ?? $cardsColumnClass));
          ?>
          <div class="<?php echo dashboard_h($columnClass); ?> cecsb-dashboard-stat-column">
            <?php if ($href !== ''): ?>
              <a class="card cecsb-stat-card h-100" href="<?php echo dashboard_h($href); ?>">
            <?php else: ?>
              <div class="card cecsb-stat-card h-100">
            <?php endif; ?>
              <div class="card-header p-3">
                <div class="row">
                  <div class="col-8">
                    <p class="text-sm mb-1 text-uppercase font-weight-bold <?php echo dashboard_h($accent['text']); ?>"><?php echo dashboard_h($title); ?></p>
                    <h4 class="mb-0"><?php echo dashboard_h($value); ?></h4>
                  </div>
                  <div class="col-4 text-end">
                    <div class="icon icon-shape icon-md <?php echo dashboard_h($accent['icon']); ?> shadow text-center border-radius-xl">
                      <i class="material-symbols-rounded opacity-10"><?php echo dashboard_h($icon); ?></i>
                    </div>
                  </div>
                </div>
              </div>
              <hr class="dark horizontal my-0">
              <div class="card-footer p-3">
                <p class="mb-0 text-sm">
                  <span class="<?php echo dashboard_h($accent['text']); ?> font-weight-bolder"><?php echo dashboard_h($metricLabel); ?></span>
                  <span class="text-secondary"> · <?php echo dashboard_h($footerLabel); ?></span>
                </p>
              </div>
            <?php if ($href !== ''): ?>
              </a>
            <?php else: ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($quickLinks) || !empty($summaryItems)): ?>
      <div class="row mt-2 cecsb-dashboard-secondary">
        <?php if (!empty($quickLinks)): ?>
        <div class="col-lg-7 mb-4">
          <div class="card h-100 cecsb-dashboard-panel cecsb-dashboard-panel--links">
            <div class="card-header pb-0">
              <h6 class="mb-0">Accesos rápidos</h6>
              <p class="text-sm mb-0">Entradas directas a los módulos más usados de este perfil.</p>
            </div>
            <div class="card-body p-3">
              <div class="row">
                <?php foreach ($quickLinks as $quickLink): ?>
                  <div class="col-md-6 mb-3">
                    <a
                      class="cecsb-quick-link"
                      href="<?php echo dashboard_h($quickLink['href'] ?? '#'); ?>"
                      <?php if (!empty($quickLink['loaderText'])): ?>
                        data-loader-text="<?php echo dashboard_h((string) $quickLink['loaderText']); ?>"
                      <?php endif; ?>
                    >
                      <i class="material-symbols-rounded"><?php echo dashboard_h($quickLink['icon'] ?? 'arrow_forward'); ?></i>
                      <span><?php echo dashboard_h($quickLink['label'] ?? 'Abrir'); ?></span>
                    </a>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($summaryItems)): ?>
        <div class="col-lg-<?php echo !empty($quickLinks) ? '5' : '12'; ?> mb-4">
          <div class="card h-100 cecsb-dashboard-panel cecsb-dashboard-panel--summary">
            <div class="card-header pb-0">
              <h6 class="mb-0">Resumen del perfil</h6>
              <p class="text-sm mb-0">Datos clave visibles sin tocar la lógica del sistema.</p>
            </div>
            <div class="card-body p-3">
              <ul class="list-group list-group-flush cecsb-summary-list">
                <?php foreach ($summaryItems as $summaryItem): ?>
                  <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                    <span class="text-sm text-secondary"><?php echo dashboard_h($summaryItem['label'] ?? ''); ?></span>
                    <span class="text-sm font-weight-bold text-dark text-end"><?php echo dashboard_h($summaryItem['value'] ?? ''); ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if (trim((string) $bodySectionsHtml) !== ''): ?>
        <?php echo $bodySectionsHtml; ?>
      <?php endif; ?>

      <footer class="footer py-4">
        <div class="container-fluid px-0">
          <div class="row align-items-center justify-content-between">
            <div class="col-lg-6 mb-lg-0 mb-4">
              <div class="copyright text-center text-sm text-muted text-lg-start">
                <?php echo dashboard_h($footerText); ?>
              </div>
            </div>
            <div class="col-lg-6">
              <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                <li class="nav-item">
                  <a <?php echo dashboard_profile_link_attrs($profileAction, 'nav-link text-muted'); ?>>Mi perfil</a>
                </li>
                <li class="nav-item">
                  <button class="nav-link text-muted border-0 bg-transparent p-0" type="button" data-bs-toggle="modal" data-bs-target="#logoutModal">Cerrar sesión</button>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </main>

  <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0">
        <div class="modal-header">
          <h5 class="modal-title" id="logoutModalLabel">Cerrar sesión</h5>
          <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          ¿Deseas cerrar la sesión actual?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-dark mb-0" data-bs-dismiss="modal">Cancelar</button>
          <a class="btn bg-gradient-dark mb-0" href="<?php echo dashboard_h($logoutUrl); ?>">Cerrar sesión</a>
        </div>
      </div>
    </div>
  </div>

  <?php echo $extraBodyHtml; ?>

  <script src="<?php echo dashboard_h(dashboard_material_asset('js/core/bootstrap.bundle.min.js')); ?>"></script>
  <script src="<?php echo dashboard_h(dashboard_material_asset('js/material-dashboard.min.js')); ?>"></script>
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

      body.classList.toggle('cecsb-sidebar-open', !!isOpen);
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
      var handleDesktopChange = function (event) {
        if (event.matches) {
          setSidebarState(false);
        }
      };

      if (typeof desktopBreakpoint.addEventListener === 'function') {
        desktopBreakpoint.addEventListener('change', handleDesktopChange);
      } else if (typeof desktopBreakpoint.addListener === 'function') {
        desktopBreakpoint.addListener(handleDesktopChange);
      }
    }
  }());
  </script>
</body>
</html>
<?php
    }
}
