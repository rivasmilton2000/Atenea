<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/atenea_auth.php';

if (!atenea_session_is_public_user()) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}

$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);
$profile = atenea_fetch_public_profile_by_user_id($db, $memberId);
if (!$profile) {
    atenea_render_auth_alert(
        'warning',
        'Perfil no disponible',
        'No encontramos el perfil asociado a esta cuenta.',
        'logout.php?redirect=homepage.php'
    );
}

$continueUrl = atenea_normalize_internal_redirect((string) ($_POST['return_to'] ?? ($_GET['return'] ?? 'usuario_vista.php')), 'usuario_vista.php');
$promptMode = !empty($_GET['prompt']) || !empty($_POST['prompt']);
$errorMessage = '';

$formValues = [
    'billing_phone' => trim((string) ($profile['PHONE_NUMBER'] ?? '')),
    'billing_tipo_documento' => trim((string) ($profile['TIPO_DOCUMENTO'] ?? 'DUI')),
    'billing_numero_documento' => trim((string) ($profile['NUMERO_DOCUMENTO'] ?? '')),
    'billing_departamento' => trim((string) ($profile['BILLING_DEPARTAMENTO'] ?? '')),
    'billing_municipio' => trim((string) ($profile['BILLING_MUNICIPIO'] ?? '')),
    'billing_distrito' => trim((string) ($profile['BILLING_DISTRITO'] ?? '')),
    'billing_address' => trim((string) ($profile['BILLING_DIRECCION'] ?? '')),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim((string) ($_POST['profile_action'] ?? '')) === 'skip') {
        unset($_SESSION['ATENEA_LOGIN_REDIRECT']);
        header('Location: ' . $continueUrl);
        exit;
    }

    $formValues = [
        'billing_phone' => trim((string) ($_POST['billing_phone'] ?? '')),
        'billing_tipo_documento' => strtoupper(trim((string) ($_POST['billing_tipo_documento'] ?? ''))),
        'billing_numero_documento' => trim((string) ($_POST['billing_numero_documento'] ?? '')),
        'billing_departamento' => trim((string) ($_POST['billing_departamento'] ?? '')),
        'billing_municipio' => trim((string) ($_POST['billing_municipio'] ?? '')),
        'billing_distrito' => trim((string) ($_POST['billing_distrito'] ?? '')),
        'billing_address' => trim((string) ($_POST['billing_address'] ?? '')),
    ];

    $validation = atenea_validate_billing_profile_input($formValues, [
        'require_name' => false,
        'require_email' => false,
    ]);

    if ($validation['errors'] === []) {
        $fullName = atenea_profile_full_name((string) ($profile['FIRST_NAME'] ?? ''), (string) ($profile['LAST_NAME'] ?? ''));
        atenea_sync_public_billing_profile($db, $memberId, [
            'billing_name' => trim((string) ($profile['BILLING_NAME'] ?? $fullName)),
            'billing_email' => trim((string) ($profile['BILLING_EMAIL'] ?? ($profile['EMAIL'] ?? ''))),
            'phone_number' => (string) ($validation['data']['phone_number'] ?? ''),
            'tipo_documento' => (string) ($validation['data']['tipo_documento'] ?? ''),
            'numero_documento' => (string) ($validation['data']['numero_documento'] ?? ''),
            'billing_departamento' => (string) ($validation['data']['billing_departamento'] ?? ''),
            'billing_municipio' => (string) ($validation['data']['billing_municipio'] ?? ''),
            'billing_distrito' => (string) ($validation['data']['billing_distrito'] ?? ''),
            'billing_direccion' => (string) ($validation['data']['billing_direccion'] ?? ''),
            'billing_nrc' => '',
        ]);

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

        unset($_SESSION['ATENEA_LOGIN_REDIRECT']);
        header('Location: ' . $continueUrl);
        exit;
    }

    $errorMessage = (string) ($validation['errors'][0] ?? 'No pudimos guardar los datos de facturacion.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Datos de facturacion | Atenea</title>
  <link rel="icon" href="../img/Atenea Logo.png" type="image/png">
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css2?family=Handlee&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link href="../css/sb-admin-2.min.css" rel="stylesheet">
  <link href="../css/atenea-ui.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      background: linear-gradient(180deg, #f4f8f6 0%, #e7f1ed 100%);
      font-family: "Nunito", sans-serif;
    }

    .billing-profile-backdrop {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 12px;
    }

    .billing-profile-shell {
      width: 100%;
      max-width: 780px;
      border-radius: 24px;
      border: 0;
      box-shadow: 0 20px 60px rgba(15, 23, 42, 0.14);
    }

    .billing-profile-panel {
      border-radius: 24px;
      overflow: hidden;
    }

    .billing-profile-header {
      padding: 28px 28px 10px;
    }

    .billing-profile-title {
      font-family: "Handlee", cursive;
      font-size: 2rem;
      color: #0f2f24;
      margin-bottom: 0.4rem;
    }

    .billing-profile-subtitle {
      color: #5b7268;
      margin-bottom: 0;
      line-height: 1.6;
    }

    .billing-profile-form {
      padding: 0 28px 28px;
    }

    .billing-profile-form .form-group {
      margin-bottom: 1rem;
    }

    .billing-profile-form .form-control {
      border-radius: 12px;
      min-height: 48px;
    }

    .billing-profile-form textarea.form-control {
      min-height: 96px;
    }

    .billing-profile-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      margin-top: 1.25rem;
    }

    @media (max-width: 767.98px) {
      .billing-profile-header,
      .billing-profile-form {
        padding-left: 20px;
        padding-right: 20px;
      }

      .billing-profile-actions .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body data-loader-text="Preparando formulario...">
  <div class="billing-profile-backdrop">
    <div class="card billing-profile-shell">
      <div class="billing-profile-panel">
        <div class="billing-profile-header">
          <h1 class="billing-profile-title">Completa tus datos para continuar</h1>
          <p class="billing-profile-subtitle">Necesitamos estos datos para generar tus comprobantes de compra. Solo tendras que llenarlos una vez.</p>
        </div>
        <div class="billing-profile-form">
          <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
          <?php endif; ?>

          <form method="post" data-atenea-billing-form data-atenea-loading-form data-loader-text="Guardando datos...">
            <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($continueUrl); ?>">
            <input type="hidden" name="prompt" value="<?php echo $promptMode ? '1' : '0'; ?>">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="billing_phone">Telefono</label>
                <input id="billing_phone" class="form-control" type="text" name="billing_phone" maxlength="25" required value="<?php echo htmlspecialchars($formValues['billing_phone']); ?>">
              </div>
              <div class="form-group col-md-6">
                <label for="billing_tipo_documento">Tipo de documento</label>
                <select id="billing_tipo_documento" class="form-control" name="billing_tipo_documento" data-document-type required>
                  <option value="DUI" <?php echo strtoupper($formValues['billing_tipo_documento']) === 'DUI' ? 'selected' : ''; ?>>DUI</option>
                  <option value="NIT" <?php echo strtoupper($formValues['billing_tipo_documento']) === 'NIT' ? 'selected' : ''; ?>>NIT</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label for="billing_numero_documento">Numero de documento</label>
              <input
                id="billing_numero_documento"
                class="form-control"
                type="text"
                name="billing_numero_documento"
                maxlength="25"
                data-document-number
                required
                value="<?php echo htmlspecialchars($formValues['billing_numero_documento']); ?>"
              >
              <small class="form-text text-muted" data-document-help>Formato permitido para tu DUI o NIT.</small>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="billing_departamento">Departamento</label>
                <select
                  id="billing_departamento"
                  class="form-control"
                  name="billing_departamento"
                  data-billing-department
                  data-selected="<?php echo htmlspecialchars($formValues['billing_departamento']); ?>"
                  required
                ></select>
              </div>
              <div class="form-group col-md-6">
                <label for="billing_municipio">Municipio</label>
                <select
                  id="billing_municipio"
                  class="form-control"
                  name="billing_municipio"
                  data-billing-municipality
                  data-selected="<?php echo htmlspecialchars($formValues['billing_municipio']); ?>"
                  required
                ></select>
              </div>
            </div>
            <input type="hidden" name="billing_distrito" value="<?php echo htmlspecialchars($formValues['billing_distrito']); ?>" data-billing-district>
            <div class="form-group">
              <label for="billing_address">Direccion completa</label>
              <textarea id="billing_address" class="form-control" name="billing_address" maxlength="255" required><?php echo htmlspecialchars($formValues['billing_address']); ?></textarea>
            </div>
            <div class="billing-profile-actions">
              <button type="submit" name="profile_action" value="save" class="btn btn-success">Guardar y continuar</button>
              <button type="submit" name="profile_action" value="skip" class="btn btn-outline-secondary">Completar despues</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../js/atenea-ui.js"></script>
  <script src="../js/sv-location-catalog.js"></script>
  <script src="../js/atenea-billing.js"></script>
</body>
</html>
