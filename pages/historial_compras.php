<?php
require_once __DIR__ . '/session.php';
if (!logged_in()) {
    header('Location: ' . atenea_build_login_url('historial_compras.php', 'login_required'));
    exit;
}

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/material_dashboard.php';
require_once __DIR__ . '/../includes/public_purchase_history.php';

if (!atenea_session_is_public_user()) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}

if (!function_exists('historial_compras_format_date')) {
    function historial_compras_format_date($value, string $fallback = 'No disponible', bool $includeTime = false): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return $fallback;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $fallback;
        }

        return $includeTime ? date('d/m/Y h:i A', $timestamp) : date('d/m/Y', $timestamp);
    }
}

if (!function_exists('historial_compras_page_url')) {
    function historial_compras_page_url(int $page): string
    {
        $page = max(1, $page);

        return $page === 1 ? 'historial_compras.php' : 'historial_compras.php?page=' . $page;
    }
}

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);
$profile = atenea_fetch_public_profile_by_user_id($db, $memberId);

if (!$profile) {
    atenea_render_auth_alert(
        'warning',
        'Perfil incompleto',
        'No encontramos el perfil público asociado a esta cuenta. Inicia sesión nuevamente.',
        'logout.php?redirect=homepage.php'
    );
}

$freshUser = atenea_fetch_user_by_id($db, $memberId);
if ($freshUser) {
    atenea_apply_session_data(
        $freshUser,
        (string) ($_SESSION['AUTH_PROVIDER'] ?? 'password'),
        [
            'email' => (string) ($_SESSION['GOOGLE_EMAIL'] ?? ($freshUser['GOOGLE_EMAIL'] ?? '')),
            'sub' => (string) ($_SESSION['GOOGLE_SUB'] ?? ($freshUser['GOOGLE_ID'] ?? '')),
        ]
    );
}

$email = trim((string) ($profile['EMAIL'] ?? ($_SESSION['EMAIL'] ?? '')));
$roleLabel = 'Usuario registrado';
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$totalPurchases = atenea_contar_historial_compras_usuario($db, [
    'user_id' => $memberId,
    'email' => $email,
]);
$totalPages = $totalPurchases > 0 ? (int) ceil($totalPurchases / $perPage) : 1;

if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$purchaseHistory = obtenerHistorialComprasUsuario($db, [
    'user_id' => $memberId,
    'email' => $email,
], $currentPage, $perPage);

$rangeStart = $totalPurchases > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
$rangeEnd = $totalPurchases > 0 ? min($currentPage * $perPage, $totalPurchases) : 0;
$pageWindowStart = max(1, $currentPage - 2);
$pageWindowEnd = min($totalPages, $currentPage + 2);

$navSections = [
    [
        'title' => 'Panel',
        'items' => [
            ['label' => 'Inicio', 'href' => 'usuario_vista.php', 'icon' => 'dashboard', 'loaderText' => 'Abriendo tu panel...'],
        ],
    ],
    [
        'title' => 'Explorar',
        'items' => [
            ['label' => 'Planes de clase', 'href' => 'educacion.php', 'icon' => 'school', 'loaderText' => 'Cargando planes...'],
            ['label' => 'Productos', 'href' => 'productos.php', 'icon' => 'storefront', 'loaderText' => 'Cargando productos...'],
            ['label' => 'Carrito y pago', 'href' => 'carrito.php', 'icon' => 'shopping_cart', 'loaderText' => 'Abriendo carrito y pago...'],
            ['label' => 'Historial de compras', 'href' => 'historial_compras.php', 'icon' => 'receipt_long', 'active' => true, 'loaderText' => 'Cargando historial de compras...'],
            ['label' => 'Sitio público', 'href' => 'homepage.php', 'icon' => 'public', 'loaderText' => 'Cargando sitio público...'],
        ],
    ],
];

ob_start();
?>
<style>
  .atenea-purchases-page .card-header {
    border-bottom: 1px solid rgba(15, 23, 42, 0.08);
    background: #fff;
  }

  .atenea-purchases-card {
    border-radius: 1.15rem;
    border: 1px solid rgba(15, 23, 42, 0.08);
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
  }

  .atenea-purchases-table {
    margin-bottom: 0;
  }

  .atenea-purchases-table th {
    font-size: 0.75rem;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #64748b;
    border-bottom-width: 1px;
  }

  .atenea-purchases-table td {
    vertical-align: middle;
    border-color: rgba(15, 23, 42, 0.08);
  }

  .atenea-purchase-concept strong,
  .atenea-purchase-amount strong,
  .atenea-purchase-mobile-card h6 {
    color: #0f172a;
    font-weight: 700;
  }

  .atenea-purchase-concept span,
  .atenea-purchase-amount span,
  .atenea-purchase-method,
  .atenea-purchase-mobile-card p,
  .atenea-purchase-unavailable {
    display: block;
    font-size: 0.82rem;
    color: #64748b;
  }

  .atenea-purchase-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 96px;
    padding: 0.4rem 0.8rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    line-height: 1;
  }

  .atenea-purchase-status.is-paid {
    background: rgba(22, 163, 74, 0.14);
    color: #166534;
  }

  .atenea-purchase-status.is-pending {
    background: rgba(245, 158, 11, 0.16);
    color: #b45309;
  }

  .atenea-purchase-status.is-failed {
    background: rgba(239, 68, 68, 0.14);
    color: #b91c1c;
  }

  .atenea-purchase-status.is-refunded {
    background: rgba(59, 130, 246, 0.14);
    color: #1d4ed8;
  }

  .atenea-purchase-status.is-neutral {
    background: rgba(100, 116, 139, 0.12);
    color: #334155;
  }

  .atenea-purchase-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .atenea-invoice-button {
    border-radius: 0.85rem;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 0.55rem 0.9rem;
  }

  .atenea-purchase-mobile-card {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 1rem;
    padding: 1rem;
    background: #fff;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
  }

  .atenea-purchase-mobile-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem;
    margin-top: 1rem;
  }

  .atenea-purchase-mobile-label {
    display: block;
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #94a3b8;
    margin-bottom: 0.2rem;
  }

  .atenea-purchases-pagination {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.85rem;
    margin-top: 1.5rem;
  }

  .atenea-purchases-pagination .pagination {
    margin-bottom: 0;
  }

  .atenea-purchases-pagination .page-link {
    border-radius: 0.8rem;
    border: 1px solid rgba(15, 23, 42, 0.08);
    color: #0f172a;
    font-weight: 600;
  }

  .atenea-purchases-pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #0f172a, #1f2937);
    border-color: #0f172a;
    color: #fff;
  }

  .atenea-loading-state {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.75rem 1rem;
    border-radius: 0.9rem;
    background: rgba(15, 23, 42, 0.04);
    color: #334155;
    transition: opacity 0.2s ease;
  }

  .atenea-loading-state.is-hidden {
    opacity: 0;
    pointer-events: none;
  }

  .atenea-purchase-empty {
    border: 1px dashed rgba(15, 23, 42, 0.14);
    border-radius: 1rem;
    padding: 2rem 1.5rem;
    text-align: center;
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.92), rgba(255, 255, 255, 1));
  }

  .atenea-purchase-empty-icon {
    width: 4rem;
    height: 4rem;
    margin: 0 auto 1rem;
    border-radius: 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.05);
    color: #046845;
  }

  .atenea-purchase-empty-icon .material-symbols-rounded {
    font-size: 2rem;
  }

  @media (max-width: 767.98px) {
    .atenea-purchases-pagination {
      flex-direction: column;
      align-items: stretch;
    }

    .atenea-purchase-mobile-grid {
      grid-template-columns: 1fr;
    }

    .atenea-purchase-actions .atenea-invoice-button {
      width: 100%;
      text-align: center;
    }
  }
</style>
<div class="row mt-2 atenea-purchases-page">
  <div class="col-12">
    <div class="card atenea-purchases-card">
      <div class="card-header p-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
          <h6 class="mb-1">Historial de compras</h6>
          <p class="text-sm mb-0">Consulta tus compras registradas, el estado del pago y la disponibilidad de tu factura o recibo.</p>
        </div>
        <div class="text-lg-end">
          <span class="badge bg-gradient-dark"><?php echo dashboard_h((string) $totalPurchases); ?> compras</span>
          <p class="text-sm text-muted mb-0 mt-2">
            <?php if ($totalPurchases > 0): ?>
              Mostrando <?php echo dashboard_h((string) $rangeStart); ?> a <?php echo dashboard_h((string) $rangeEnd); ?> de <?php echo dashboard_h((string) $totalPurchases); ?> resultados
            <?php else: ?>
              Aún no hay compras registradas para esta cuenta.
            <?php endif; ?>
          </p>
        </div>
      </div>
      <div class="card-body p-3 p-md-4">
        <div class="atenea-loading-state mb-3" data-atenea-loading-state aria-live="polite">
          <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
          <span>Cargando historial...</span>
        </div>

        <?php if ($purchaseHistory !== []): ?>
          <div class="table-responsive d-none d-md-block">
            <table class="table align-items-center atenea-purchases-table">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Concepto</th>
                  <th>Tipo</th>
                  <th>Monto</th>
                  <th>Estado</th>
                  <th>Factura</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($purchaseHistory as $purchase): ?>
                  <tr>
                    <td><?php echo dashboard_h(historial_compras_format_date((string) $purchase['date'], 'No disponible', true)); ?></td>
                    <td>
                      <div class="atenea-purchase-concept">
                        <strong><?php echo dashboard_h((string) $purchase['concept']); ?></strong>
                        <span><?php echo dashboard_h(((int) $purchase['line_count']) > 0 ? ((int) $purchase['total_quantity']) . ' artículo(s) en la orden' : 'Compra registrada en el sistema'); ?></span>
                      </div>
                    </td>
                    <td><?php echo dashboard_h((string) $purchase['type']); ?></td>
                    <td>
                      <div class="atenea-purchase-amount">
                        <strong><?php echo dashboard_h(atenea_purchase_format_amount((float) $purchase['amount'], (string) $purchase['currency'])); ?></strong>
                        <span><?php echo dashboard_h((string) $purchase['payment_method']); ?></span>
                      </div>
                    </td>
                    <td>
                      <span class="atenea-purchase-status <?php echo dashboard_h((string) ($purchase['status_meta']['class'] ?? 'is-neutral')); ?>">
                        <?php echo dashboard_h((string) ($purchase['status_meta']['label'] ?? 'Desconocido')); ?>
                      </span>
                    </td>
                    <td>
                      <?php if (!empty($purchase['invoice_available'])): ?>
                        <div class="atenea-purchase-actions">
                          <a
                            class="btn btn-outline-dark btn-sm mb-0 atenea-invoice-button"
                            href="<?php echo dashboard_h(atenea_purchase_invoice_url((int) $purchase['order_id'], 'view')); ?>"
                            target="_blank"
                            rel="noopener"
                            data-loader-text="Abriendo factura..."
                          >Ver factura</a>
                          <a
                            class="btn bg-gradient-dark btn-sm mb-0 atenea-invoice-button"
                            href="<?php echo dashboard_h(atenea_purchase_invoice_url((int) $purchase['order_id'], 'download')); ?>"
                            data-loader-text="Preparando descarga de factura..."
                          >Descargar PDF</a>
                        </div>
                      <?php else: ?>
                        <span class="atenea-purchase-unavailable">Factura no disponible</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div class="d-md-none">
            <div class="d-flex flex-column gap-3">
              <?php foreach ($purchaseHistory as $purchase): ?>
                <article class="atenea-purchase-mobile-card">
                  <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                      <h6 class="mb-1"><?php echo dashboard_h((string) $purchase['concept']); ?></h6>
                      <p class="mb-0"><?php echo dashboard_h(historial_compras_format_date((string) $purchase['date'], 'No disponible', true)); ?></p>
                    </div>
                    <span class="atenea-purchase-status <?php echo dashboard_h((string) ($purchase['status_meta']['class'] ?? 'is-neutral')); ?>">
                      <?php echo dashboard_h((string) ($purchase['status_meta']['label'] ?? 'Desconocido')); ?>
                    </span>
                  </div>

                  <div class="atenea-purchase-mobile-grid">
                    <div>
                      <span class="atenea-purchase-mobile-label">Tipo</span>
                      <span class="text-sm font-weight-bold text-dark"><?php echo dashboard_h((string) $purchase['type']); ?></span>
                    </div>
                    <div>
                      <span class="atenea-purchase-mobile-label">Monto</span>
                      <span class="text-sm font-weight-bold text-dark"><?php echo dashboard_h(atenea_purchase_format_amount((float) $purchase['amount'], (string) $purchase['currency'])); ?></span>
                    </div>
                    <div>
                      <span class="atenea-purchase-mobile-label">Método</span>
                      <span class="text-sm text-muted"><?php echo dashboard_h((string) $purchase['payment_method']); ?></span>
                    </div>
                    <div>
                      <span class="atenea-purchase-mobile-label">Detalle</span>
                      <span class="text-sm text-muted"><?php echo dashboard_h(((int) $purchase['line_count']) > 0 ? ((int) $purchase['total_quantity']) . ' artículo(s)' : 'Compra registrada'); ?></span>
                    </div>
                  </div>

                  <div class="mt-3">
                    <?php if (!empty($purchase['invoice_available'])): ?>
                      <div class="atenea-purchase-actions">
                        <a
                          class="btn btn-outline-dark btn-sm mb-0 atenea-invoice-button"
                          href="<?php echo dashboard_h(atenea_purchase_invoice_url((int) $purchase['order_id'], 'view')); ?>"
                          target="_blank"
                          rel="noopener"
                          data-loader-text="Abriendo factura..."
                        >Ver factura</a>
                        <a
                          class="btn bg-gradient-dark btn-sm mb-0 atenea-invoice-button"
                          href="<?php echo dashboard_h(atenea_purchase_invoice_url((int) $purchase['order_id'], 'download')); ?>"
                          data-loader-text="Preparando descarga de factura..."
                        >Descargar PDF</a>
                      </div>
                    <?php else: ?>
                      <span class="atenea-purchase-unavailable">Factura no disponible</span>
                    <?php endif; ?>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </div>

          <?php if ($totalPages > 1): ?>
            <div class="atenea-purchases-pagination">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <a
                  class="btn btn-outline-dark btn-sm mb-0<?php echo $currentPage <= 1 ? ' disabled' : ''; ?>"
                  href="<?php echo dashboard_h($currentPage <= 1 ? '#' : historial_compras_page_url($currentPage - 1)); ?>"
                  <?php if ($currentPage > 1): ?>
                    data-loader-text="Cargando compras anteriores..."
                  <?php endif; ?>
                  <?php echo $currentPage <= 1 ? 'aria-disabled="true" tabindex="-1"' : ''; ?>
                >Anterior</a>

                <ul class="pagination pagination-sm">
                  <?php for ($page = $pageWindowStart; $page <= $pageWindowEnd; $page++): ?>
                    <li class="page-item<?php echo $page === $currentPage ? ' active' : ''; ?>">
                      <a
                        class="page-link"
                        href="<?php echo dashboard_h(historial_compras_page_url($page)); ?>"
                        data-loader-text="Cargando página <?php echo dashboard_h((string) $page); ?> del historial..."
                      ><?php echo dashboard_h((string) $page); ?></a>
                    </li>
                  <?php endfor; ?>
                </ul>

                <a
                  class="btn btn-outline-dark btn-sm mb-0<?php echo $currentPage >= $totalPages ? ' disabled' : ''; ?>"
                  href="<?php echo dashboard_h($currentPage >= $totalPages ? '#' : historial_compras_page_url($currentPage + 1)); ?>"
                  <?php if ($currentPage < $totalPages): ?>
                    data-loader-text="Cargando más compras..."
                  <?php endif; ?>
                  <?php echo $currentPage >= $totalPages ? 'aria-disabled="true" tabindex="-1"' : ''; ?>
                >Siguiente</a>
              </div>
              <p class="text-sm text-muted mb-0">Página <?php echo dashboard_h((string) $currentPage); ?> de <?php echo dashboard_h((string) $totalPages); ?></p>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="atenea-purchase-empty">
            <div class="atenea-purchase-empty-icon" aria-hidden="true">
              <span class="material-symbols-rounded">shopping_bag</span>
            </div>
            <h6 class="mb-2">No tienes compras registradas todavía.</h6>
            <p class="text-sm text-muted mb-3">Cuando compres un plan o producto, aparecerá aquí tu historial.</p>
            <a class="btn bg-gradient-dark mb-0" href="productos.php" data-loader-text="Cargando productos...">Ir a productos</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php
$bodySectionsHtml = ob_get_clean();

ob_start();
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-atenea-loading-state]').forEach(function (loader) {
    loader.classList.add('is-hidden');
    window.setTimeout(function () {
      loader.hidden = true;
    }, 220);
  });
});
</script>
<?php
$extraBodyHtml = ob_get_clean();

dashboard_render_material_page([
    'pageTitle' => 'Historial de compras',
    'roleLabel' => $roleLabel,
    'welcomeTitle' => 'Historial de compras',
    'welcomeText' => 'Consulta tus compras registradas, el estado del pago y la disponibilidad de tu factura o recibo.',
    'profileUrl' => 'usuario_vista.php',
    'logoutUrl' => 'logout.php?redirect=homepage.php',
    'navSections' => $navSections,
    'cards' => [],
    'quickLinks' => [],
    'summaryItems' => [],
    'bodySectionsHtml' => $bodySectionsHtml,
    'heroBadges' => [
        $totalPurchases . ' compras registradas',
        '10 por página',
        'Página ' . $currentPage . ' de ' . $totalPages,
    ],
    'heroActions' => [
        ['label' => 'Volver al panel', 'href' => 'usuario_vista.php', 'icon' => 'dashboard'],
        ['label' => 'Ir a productos', 'href' => 'productos.php', 'icon' => 'storefront', 'variant' => 'outline'],
    ],
    'extraBodyHtml' => $extraBodyHtml,
]);
