<?php

require_once __DIR__ . '/../material_dashboard.php';
require_once __DIR__ . '/bootstrap.php';

if (!function_exists('atenea_dte_role_meta')) {
    function atenea_dte_role_meta(string $scope): array
    {
        $scope = strtolower(trim($scope));
        if ($scope === 'superadmin') {
            return [
                'role' => 'SuperAdmin',
                'label' => 'Super administrador',
                'profile_url' => 'sa_perfil.php?action=edit&id=' . (int) ($_SESSION['MEMBER_ID'] ?? 0),
                'redirects' => [
                    'Personal' => 'empleados_vista.php',
                    'Estudiante' => 'estudiante_vista.php',
                    'Docente' => 'docentes_vista.php',
                    'Admin' => 'index.php',
                ],
            ];
        }

        return [
            'role' => 'Admin',
            'label' => 'Administrador',
            'profile_url' => 'perfil.php?action=edit&id=' . (int) ($_SESSION['MEMBER_ID'] ?? 0),
            'redirects' => [
                'Personal' => 'empleados_vista.php',
                'Estudiante' => 'estudiante_vista.php',
                'Docente' => 'docentes_vista.php',
                'SuperAdmin' => 'sa_vista.php',
            ],
        ];
    }
}

if (!function_exists('atenea_dte_require_role')) {
    function atenea_dte_require_role(mysqli $db, string $scope): array
    {
        $meta = atenea_dte_role_meta($scope);
        dashboard_require_role($db, [$meta['role']], $meta['redirects']);

        return $meta;
    }
}

if (!function_exists('atenea_dte_flash_key')) {
    function atenea_dte_flash_key(): string
    {
        return 'ATENEA_DTE_FLASH';
    }
}

if (!function_exists('atenea_dte_set_flash')) {
    function atenea_dte_set_flash(string $type, string $message): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION[atenea_dte_flash_key()] = [
            'type' => $type,
            'message' => $message,
        ];
    }
}

if (!function_exists('atenea_dte_pull_flash')) {
    function atenea_dte_pull_flash(): ?array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $flash = $_SESSION[atenea_dte_flash_key()] ?? null;
        unset($_SESSION[atenea_dte_flash_key()]);

        return is_array($flash) ? $flash : null;
    }
}

if (!function_exists('atenea_dte_alert_class')) {
    function atenea_dte_alert_class(string $type): string
    {
        switch (strtolower(trim($type))) {
            case 'success':
                return 'alert-success';
            case 'warning':
                return 'alert-warning';
            case 'danger':
            case 'error':
                return 'alert-danger';
            default:
                return 'alert-info';
        }
    }
}

if (!function_exists('atenea_dte_status_badge_class')) {
    function atenea_dte_status_badge_class(string $status): string
    {
        $normalized = strtoupper(trim($status));

        if ($normalized === 'PROCESADO') {
            return 'badge-success';
        }

        if ($normalized === 'PROCESADO SIMULADO') {
            return 'badge-warning';
        }

        if ($normalized === 'ERROR') {
            return 'badge-danger';
        }

        return 'badge-secondary';
    }
}

if (!function_exists('atenea_dte_redirect_self')) {
    function atenea_dte_redirect_self(): void
    {
        $location = basename((string) ($_SERVER['PHP_SELF'] ?? 'dte_config.php'));
        header('Location: ' . $location);
        exit;
    }
}

if (!function_exists('atenea_dte_prepare_config_page')) {
    function atenea_dte_prepare_config_page(mysqli $db, string $scope): array
    {
        $meta = atenea_dte_require_role($db, $scope);
        DteSchema::ensure($db);
        DteStorage::ensureDirectories();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = trim((string) ($_POST['action'] ?? 'save_config'));

            try {
                atenea_require_csrf_token('dte_config_form', (string) ($_POST['csrf_token'] ?? ''));

                if ($action === 'test_config') {
                    $result = DteConfig::testConfiguration($db);
                    $type = !empty($result['ok']) ? 'success' : 'warning';
                    $message = (string) ($result['message'] ?? 'La prueba termino sin detalles adicionales.');
                    atenea_dte_set_flash($type, $message);
                    atenea_dte_redirect_self();
                }

                DteConfig::save($db, $_POST, $_FILES);
                atenea_dte_set_flash('success', 'La configuracion DTE fue actualizada correctamente.');
            } catch (Throwable $exception) {
                atenea_dte_set_flash('danger', $exception->getMessage());
            }

            atenea_dte_redirect_self();
        }

        $settings = DteConfig::getActive($db);
        $flash = atenea_dte_pull_flash();

        return [
            'meta' => $meta,
            'settings' => $settings,
            'flash' => $flash,
            'csrf_token' => atenea_csrf_token('dte_config_form'),
            'certificate_name' => basename((string) ($settings['certificate_path'] ?? '')),
            'missing_emitter_fields' => DteConfig::validateEmitter($settings),
        ];
    }
}

if (!function_exists('atenea_dte_render_config_content')) {
    function atenea_dte_render_config_content(array $state): void
    {
        $settings = $state['settings'];
        $flash = $state['flash'];
        $csrfToken = (string) ($state['csrf_token'] ?? '');
        $missingEmitterFields = $state['missing_emitter_fields'] ?? [];
        ?>
        <div class="card shadow mb-4">
          <div class="card-header py-3 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
            <div>
              <h4 class="m-0 font-weight-bold text-primary">Facturacion DTE</h4>
              <p class="mb-0 mt-2 text-muted">El modo real requiere certificado y credenciales validas de Hacienda. Mientras este en simulacion, los documentos no tienen validez fiscal.</p>
            </div>
            <div class="mt-3 mt-lg-0">
              <span class="badge badge-dark px-3 py-2"><?php echo htmlspecialchars((string) ($settings['mode_label'] ?? DteConfig::modeLabel((string) ($settings['mode'] ?? 'simulation')))); ?></span>
            </div>
          </div>
          <div class="card-body">
            <?php if ($flash): ?>
              <div class="alert <?php echo atenea_dte_alert_class((string) ($flash['type'] ?? 'info')); ?>">
                <?php echo htmlspecialchars((string) ($flash['message'] ?? '')); ?>
              </div>
            <?php endif; ?>

            <?php if ($missingEmitterFields !== []): ?>
              <div class="alert alert-warning">
                <strong>Falta configurar datos DTE del emisor.</strong>
                Completa: <?php echo htmlspecialchars(implode(', ', $missingEmitterFields)); ?>.
              </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
              <input type="hidden" name="action" value="save_config">

              <div class="row">
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Ambiente</strong></label>
                    <select class="form-control" name="mode" required>
                      <option value="simulation" <?php echo (string) ($settings['mode'] ?? '') === 'simulation' ? 'selected' : ''; ?>>Simulacion</option>
                      <option value="test" <?php echo (string) ($settings['mode'] ?? '') === 'test' ? 'selected' : ''; ?>>Prueba Hacienda</option>
                      <option value="production" <?php echo (string) ($settings['mode'] ?? '') === 'production' ? 'selected' : ''; ?>>Produccion Hacienda</option>
                    </select>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Codigo establecimiento MH</strong></label>
                    <input type="text" class="form-control" name="cod_estable_mh" maxlength="20" value="<?php echo htmlspecialchars((string) ($settings['cod_estable_mh'] ?? '')); ?>" required>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Codigo establecimiento interno</strong></label>
                    <input type="text" class="form-control" name="cod_estable" maxlength="20" value="<?php echo htmlspecialchars((string) ($settings['cod_estable'] ?? '')); ?>" required>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Codigo punto venta MH</strong></label>
                    <input type="text" class="form-control" name="cod_punto_venta_mh" maxlength="20" value="<?php echo htmlspecialchars((string) ($settings['cod_punto_venta_mh'] ?? '')); ?>" required>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Codigo punto venta interno</strong></label>
                    <input type="text" class="form-control" name="cod_punto_venta" maxlength="20" value="<?php echo htmlspecialchars((string) ($settings['cod_punto_venta'] ?? '')); ?>" required>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>NIT</strong></label>
                    <input type="text" class="form-control" name="emisor_nit" maxlength="20" value="<?php echo htmlspecialchars((string) ($settings['emisor_nit'] ?? '')); ?>" required>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>NRC</strong></label>
                    <input type="text" class="form-control" name="emisor_nrc" maxlength="20" value="<?php echo htmlspecialchars((string) ($settings['emisor_nrc'] ?? '')); ?>" required>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Nombre legal</strong></label>
                    <input type="text" class="form-control" name="emisor_nombre" maxlength="255" value="<?php echo htmlspecialchars((string) ($settings['emisor_nombre'] ?? '')); ?>" required>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Nombre comercial</strong></label>
                    <input type="text" class="form-control" name="emisor_nombre_comercial" maxlength="255" value="<?php echo htmlspecialchars((string) ($settings['emisor_nombre_comercial'] ?? '')); ?>" required>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Codigo actividad economica</strong></label>
                    <input type="text" class="form-control" name="emisor_cod_actividad" maxlength="20" value="<?php echo htmlspecialchars((string) ($settings['emisor_cod_actividad'] ?? '')); ?>" required>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Descripcion actividad</strong></label>
                    <input type="text" class="form-control" name="emisor_desc_actividad" maxlength="255" value="<?php echo htmlspecialchars((string) ($settings['emisor_desc_actividad'] ?? '')); ?>" required>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Tipo establecimiento</strong></label>
                    <input type="text" class="form-control" name="emisor_tipo_establecimiento" maxlength="50" value="<?php echo htmlspecialchars((string) ($settings['emisor_tipo_establecimiento'] ?? '')); ?>" required>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Departamento</strong></label>
                    <input type="text" class="form-control" name="emisor_departamento" maxlength="100" value="<?php echo htmlspecialchars((string) ($settings['emisor_departamento'] ?? '')); ?>" required>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Municipio</strong></label>
                    <input type="text" class="form-control" name="emisor_municipio" maxlength="100" value="<?php echo htmlspecialchars((string) ($settings['emisor_municipio'] ?? '')); ?>" required>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Telefono</strong></label>
                    <input type="text" class="form-control" name="emisor_telefono" maxlength="30" value="<?php echo htmlspecialchars((string) ($settings['emisor_telefono'] ?? '')); ?>" required>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-8">
                  <div class="form-group">
                    <label><strong>Direccion complemento</strong></label>
                    <input type="text" class="form-control" name="emisor_direccion" maxlength="255" value="<?php echo htmlspecialchars((string) ($settings['emisor_direccion'] ?? '')); ?>" required>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Correo</strong></label>
                    <input type="email" class="form-control" name="emisor_correo" maxlength="150" value="<?php echo htmlspecialchars((string) ($settings['emisor_correo'] ?? '')); ?>" required>
                  </div>
                </div>
              </div>

              <hr>
              <div class="row">
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Usuario API Hacienda</strong></label>
                    <input type="text" class="form-control" name="api_user" maxlength="150" value="<?php echo htmlspecialchars((string) ($settings['api_user'] ?? '')); ?>">
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Contrasena / token API</strong></label>
                    <input type="password" class="form-control" name="api_password" autocomplete="new-password" placeholder="Dejar vacio para conservar el actual">
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Contrasena del certificado</strong></label>
                    <input type="password" class="form-control" name="certificate_password" autocomplete="new-password" placeholder="Dejar vacio para conservar la actual">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-8">
                  <div class="form-group">
                    <label><strong>Subir certificado / archivo Hacienda</strong></label>
                    <input type="file" class="form-control-file" name="certificate_file">
                    <small class="form-text text-muted">
                      Archivo actual:
                      <?php echo htmlspecialchars((string) ($state['certificate_name'] ?? 'Sin archivo')); ?>
                    </small>
                  </div>
                </div>
                <div class="col-lg-4 d-flex align-items-end">
                  <div class="alert alert-info mb-0 w-100">
                    <strong>Storage seguro:</strong><br>
                    `storage/dte/certificates/`
                  </div>
                </div>
              </div>

              <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-success btn-lg mr-2"><i class="fas fa-save"></i> Guardar configuracion</button>
                <button type="submit" class="btn btn-outline-primary btn-lg" onclick="this.form.action.value='test_config';"><i class="fas fa-vial"></i> Probar configuracion</button>
              </div>
            </form>
          </div>
        </div>
        <?php
    }
}

if (!function_exists('atenea_dte_prepare_documents_page')) {
    function atenea_dte_prepare_documents_page(mysqli $db, string $scope): array
    {
        $meta = atenea_dte_require_role($db, $scope);
        DteSchema::ensure($db);
        DteStorage::ensureDirectories();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && trim((string) ($_POST['action'] ?? '')) === 'retry_dte') {
            try {
                atenea_require_csrf_token('dte_documents_action', (string) ($_POST['csrf_token'] ?? ''));
                $orderId = (int) ($_POST['order_id'] ?? 0);
                if ($orderId <= 0) {
                    throw new RuntimeException('No se recibio una compra valida para reintentar el DTE.');
                }

                $document = DteService::getDocumentByOrderId($db, $orderId);
                if ($document && !in_array(strtoupper(trim((string) ($document['estado'] ?? ''))), ['ERROR', 'PENDIENTE', ''], true)) {
                    throw new RuntimeException('Solo se puede reintentar un DTE en estado ERROR o PENDIENTE.');
                }

                DteService::generateForOrder($db, $orderId, [
                    'force_retry' => true,
                    'user_id' => (int) ($document['user_id'] ?? 0),
                ]);
                atenea_dte_set_flash('success', 'El DTE fue regenerado correctamente.');
            } catch (Throwable $exception) {
                atenea_dte_set_flash('danger', $exception->getMessage());
            }

            atenea_dte_redirect_self();
        }

        if (isset($_GET['download'], $_GET['id'])) {
            atenea_dte_stream_admin_file($db, (string) $_GET['download'], (int) $_GET['id']);
        }

        $flash = atenea_dte_pull_flash();
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'status' => trim((string) ($_GET['status'] ?? '')),
            'mode' => trim((string) ($_GET['mode'] ?? '')),
            'page' => max(1, (int) ($_GET['page'] ?? 1)),
            'per_page' => 10,
        ];

        [$documents, $total] = atenea_dte_fetch_documents($db, $filters);

        return [
            'meta' => $meta,
            'flash' => $flash,
            'filters' => $filters,
            'documents' => $documents,
            'total' => $total,
            'pages' => max(1, (int) ceil($total / $filters['per_page'])),
            'csrf_token' => atenea_csrf_token('dte_documents_action'),
        ];
    }
}

if (!function_exists('atenea_dte_fetch_documents')) {
    function atenea_dte_fetch_documents(mysqli $db, array $filters): array
    {
        $where = [];
        $types = '';
        $values = [];

        if ($filters['q'] !== '') {
            $where[] = '(o.billing_name LIKE ? OR o.billing_email LIKE ? OR dd.codigo_generacion LIKE ? OR dd.numero_control LIKE ? OR dd.sello_recibido LIKE ?)';
            $needle = '%' . $filters['q'] . '%';
            $types .= 'sssss';
            $values[] = $needle;
            $values[] = $needle;
            $values[] = $needle;
            $values[] = $needle;
            $values[] = $needle;
        }

        if ($filters['status'] !== '') {
            $where[] = 'dd.estado = ?';
            $types .= 's';
            $values[] = $filters['status'];
        }

        if ($filters['mode'] !== '') {
            $where[] = 'dd.modo = ?';
            $types .= 's';
            $values[] = $filters['mode'];
        }

        $whereSql = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';
        $countSql = "SELECT COUNT(*)
            FROM dte_documents dd
            JOIN ordenes o ON o.id = dd.order_id
            {$whereSql}";

        $countStmt = $db->prepare($countSql);
        if (!$countStmt) {
            throw new RuntimeException('No se pudo contar los documentos DTE.');
        }

        if ($types !== '') {
            $countStmt->bind_param($types, ...$values);
        }
        $countStmt->execute();
        $countStmt->bind_result($total);
        $countStmt->fetch();
        $countStmt->close();

        $offset = ($filters['page'] - 1) * $filters['per_page'];
        $listSql = "SELECT
                dd.*,
                o.billing_name,
                o.billing_email,
                o.total_amount,
                COALESCE(o.paid_at, o.created_at) AS purchase_date
            FROM dte_documents dd
            JOIN ordenes o ON o.id = dd.order_id
            {$whereSql}
            ORDER BY COALESCE(o.paid_at, o.created_at) DESC, dd.id DESC
            LIMIT ? OFFSET ?";
        $listStmt = $db->prepare($listSql);
        if (!$listStmt) {
            throw new RuntimeException('No se pudo listar los documentos DTE.');
        }

        $listTypes = $types . 'ii';
        $listValues = $values;
        $listValues[] = $filters['per_page'];
        $listValues[] = $offset;
        $listStmt->bind_param($listTypes, ...$listValues);
        $listStmt->execute();
        $result = $listStmt->get_result();
        $documents = [];

        while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
            $documents[] = DteService::getDocumentByOrderId($db, (int) ($row['order_id'] ?? 0)) ?: $row;
        }

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        $listStmt->close();

        return [$documents, (int) $total];
    }
}

if (!function_exists('atenea_dte_stream_admin_file')) {
    function atenea_dte_stream_admin_file(mysqli $db, string $downloadType, int $orderId): void
    {
        $document = DteService::getDocumentByOrderId($db, $orderId);
        if (!$document) {
            atenea_render_auth_alert('warning', 'Documento no disponible', 'No encontramos el documento DTE solicitado.', basename((string) ($_SERVER['PHP_SELF'] ?? 'dte_documents.php')));
        }

        $map = [
            'pdf' => ['path' => (string) ($document['pdf_absolute_path'] ?? ''), 'type' => 'application/pdf'],
            'json' => ['path' => (string) ($document['json_absolute_path'] ?? ''), 'type' => 'application/json'],
            'response' => ['path' => (string) ($document['response_absolute_path'] ?? ''), 'type' => 'application/json'],
        ];

        if (!isset($map[$downloadType])) {
            atenea_render_auth_alert('warning', 'Descarga no valida', 'El tipo de archivo solicitado no esta soportado.', basename((string) ($_SERVER['PHP_SELF'] ?? 'dte_documents.php')));
        }

        $file = $map[$downloadType];
        if ($file['path'] === '' || !is_file($file['path'])) {
            atenea_render_auth_alert('warning', 'Archivo no disponible', 'El archivo solicitado aun no existe o no se puede leer.', basename((string) ($_SERVER['PHP_SELF'] ?? 'dte_documents.php')));
        }

        header('Content-Type: ' . $file['type']);
        header('Content-Length: ' . (string) filesize($file['path']));
        header('Content-Disposition: attachment; filename="' . basename($file['path']) . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        header('X-Content-Type-Options: nosniff');
        readfile($file['path']);
        exit;
    }
}

if (!function_exists('atenea_dte_render_documents_content')) {
    function atenea_dte_render_documents_content(array $state): void
    {
        $flash = $state['flash'];
        $filters = $state['filters'];
        $documents = $state['documents'];
        $total = (int) ($state['total'] ?? 0);
        $pages = (int) ($state['pages'] ?? 1);
        $csrfToken = (string) ($state['csrf_token'] ?? '');
        ?>
        <div class="card shadow mb-4">
          <div class="card-header py-3 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
            <div>
              <h4 class="m-0 font-weight-bold text-primary">Documentos DTE</h4>
              <p class="mb-0 mt-2 text-muted">Consulta, filtra y reintenta documentos DTE sin afectar el checkout ni las compras registradas.</p>
            </div>
            <span class="badge badge-dark px-3 py-2"><?php echo (int) $total; ?> documentos</span>
          </div>
          <div class="card-body">
            <?php if ($flash): ?>
              <div class="alert <?php echo atenea_dte_alert_class((string) ($flash['type'] ?? 'info')); ?>">
                <?php echo htmlspecialchars((string) ($flash['message'] ?? '')); ?>
              </div>
            <?php endif; ?>

            <form method="get" class="mb-4">
              <div class="row">
                <div class="col-lg-4">
                  <div class="form-group">
                    <label><strong>Busqueda</strong></label>
                    <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars((string) $filters['q']); ?>" placeholder="Cliente, codigo, numero o sello">
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="form-group">
                    <label><strong>Estado</strong></label>
                    <select class="form-control" name="status">
                      <option value="">Todos</option>
                      <?php foreach (['PROCESADO', 'PROCESADO SIMULADO', 'PENDIENTE', 'ERROR'] as $status): ?>
                        <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $filters['status'] === $status ? 'selected' : ''; ?>><?php echo htmlspecialchars($status); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="form-group">
                    <label><strong>Modo</strong></label>
                    <select class="form-control" name="mode">
                      <option value="">Todos</option>
                      <?php foreach (['simulation', 'test', 'production'] as $mode): ?>
                        <option value="<?php echo htmlspecialchars($mode); ?>" <?php echo $filters['mode'] === $mode ? 'selected' : ''; ?>><?php echo htmlspecialchars($mode); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-lg-2 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Filtrar</button>
                </div>
              </div>
            </form>

            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead class="thead-light">
                  <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Compra</th>
                    <th>Codigo generacion</th>
                    <th>Numero control</th>
                    <th>Sello recepcion</th>
                    <th>Estado</th>
                    <th>Total</th>
                    <th>Modo</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($documents === []): ?>
                    <tr>
                      <td colspan="10" class="text-center text-muted">No hay documentos DTE que coincidan con los filtros.</td>
                    </tr>
                  <?php endif; ?>
                  <?php foreach ($documents as $document): ?>
                    <tr>
                      <td><?php echo htmlspecialchars((string) ($document['fecha_emision'] ?? $document['purchase_date'] ?? '')); ?></td>
                      <td>
                        <strong><?php echo htmlspecialchars((string) ($document['billing_name'] ?? '')); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars((string) ($document['billing_email'] ?? '')); ?></small>
                      </td>
                      <td>#<?php echo (int) ($document['order_id'] ?? 0); ?></td>
                      <td><?php echo htmlspecialchars((string) ($document['codigo_generacion'] ?? '')); ?></td>
                      <td><?php echo htmlspecialchars((string) ($document['numero_control'] ?? '')); ?></td>
                      <td><?php echo htmlspecialchars((string) ($document['sello_recibido'] ?? '')); ?></td>
                      <td><span class="badge <?php echo atenea_dte_status_badge_class((string) ($document['estado'] ?? '')); ?>"><?php echo htmlspecialchars((string) ($document['estado'] ?? '')); ?></span></td>
                      <td>$<?php echo number_format((float) ($document['total_pagar'] ?? 0), 2); ?></td>
                      <td><?php echo htmlspecialchars((string) ($document['modo'] ?? '')); ?></td>
                      <td>
                        <div class="d-flex flex-wrap" style="gap: 0.35rem;">
                          <?php if (!empty($document['pdf_available'])): ?>
                            <a class="btn btn-sm btn-outline-dark" href="?download=pdf&id=<?php echo (int) ($document['order_id'] ?? 0); ?>">PDF</a>
                          <?php endif; ?>
                          <?php if (!empty($document['json_available'])): ?>
                            <a class="btn btn-sm btn-outline-primary" href="?download=json&id=<?php echo (int) ($document['order_id'] ?? 0); ?>">JSON</a>
                          <?php endif; ?>
                          <?php if (!empty($document['response_available'])): ?>
                            <a class="btn btn-sm btn-outline-info" href="?download=response&id=<?php echo (int) ($document['order_id'] ?? 0); ?>">Respuesta</a>
                          <?php endif; ?>
                          <?php if (in_array(strtoupper(trim((string) ($document['estado'] ?? ''))), ['ERROR', 'PENDIENTE', ''], true)): ?>
                            <form method="post" class="d-inline">
                              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                              <input type="hidden" name="action" value="retry_dte">
                              <input type="hidden" name="order_id" value="<?php echo (int) ($document['order_id'] ?? 0); ?>">
                              <button type="submit" class="btn btn-sm btn-warning">Reintentar</button>
                            </form>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <?php if ($pages > 1): ?>
              <nav aria-label="Paginacion DTE">
                <ul class="pagination justify-content-end">
                  <?php for ($page = 1; $page <= $pages; $page++): ?>
                    <li class="page-item <?php echo $page === (int) $filters['page'] ? 'active' : ''; ?>">
                      <a class="page-link" href="?<?php echo htmlspecialchars(http_build_query([
                          'q' => $filters['q'],
                          'status' => $filters['status'],
                          'mode' => $filters['mode'],
                          'page' => $page,
                      ])); ?>"><?php echo $page; ?></a>
                    </li>
                  <?php endfor; ?>
                </ul>
              </nav>
            <?php endif; ?>
          </div>
        </div>
        <?php
    }
}
