<?php
require 'session.php';
require_once '../includes/atenea_auth.php';

$requestedRedirect = trim((string) ($_GET['redirect'] ?? ''));
$loginRedirect = $requestedRedirect !== ''
    ? atenea_resolve_login_redirect($requestedRedirect, 'productos.php')
    : '';
$loginMessageCode = trim((string) ($_GET['msg'] ?? ''));
$loginMessage = atenea_login_message_for_code($loginMessageCode);

if ($loginRedirect !== '') {
    $_SESSION['ATENEA_LOGIN_REDIRECT'] = $loginRedirect;
} else {
    unset($_SESSION['ATENEA_LOGIN_REDIRECT']);
}

if (logged_in()) {
    header('Location: ' . ($loginRedirect !== '' ? $loginRedirect : atenea_dashboard_route_for_session()));
    exit;
}

$googleClientId = atenea_google_client_id();
$googleEnabled = atenea_google_is_enabled();
$_SESSION['GOOGLE_LOGIN_NONCE'] = bin2hex(random_bytes(16));
$googleNonce = (string) $_SESSION['GOOGLE_LOGIN_NONCE'];
$sessionExpired = isset($_GET['expired']) && (string) $_GET['expired'] === '1';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Inicio de sesión del Aula Virtual Atenea">
  <meta name="author" content="Atenea">
  <title>Aula Virtual | Atenea</title>

  <link rel="icon" href="../img/Atenea Logo.png" type="image/png">
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css2?family=Handlee&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link href="../css/sb-admin-2.min.css" rel="stylesheet">
  <link href="../css/atenea-ui.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/atenea-ui.js" defer></script>
<?php if ($googleEnabled): ?>
  <script src="https://accounts.google.com/gsi/client" async defer></script>
<?php endif; ?>
  <style>
    body.atenea-login-page {
      margin: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background:
        radial-gradient(circle at 12% 18%, #0d8a59 0%, #046845 45%, #035237 100%);
      font-family: "Nunito", sans-serif;
      padding: 24px 12px;
    }

    .atenea-login-shell {
      width: 100%;
      max-width: 1040px;
    }

    .atenea-login-card {
      border: 0;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 18px 60px rgba(0, 0, 0, 0.24);
    }

    .atenea-login-image {
      background:
        linear-gradient(rgba(4, 104, 69, 0.28), rgba(4, 104, 69, 0.4)),
        url("../img/atenea_img.jpg") center/cover no-repeat;
      min-height: 560px;
    }

    .atenea-login-panel {
      background: #ffffff;
      padding: 44px 38px;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .atenea-login-brand {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-bottom: 16px;
    }

    .atenea-login-brand img {
      width: 56px;
      height: 56px;
      object-fit: contain;
    }

    .atenea-login-brand span {
      color: #c8a133;
      font-size: 1.75rem;
      font-weight: 800;
      letter-spacing: 0.3px;
    }

    .atenea-login-title {
      font-family: "Handlee", cursive;
      font-size: 2.15rem;
      color: #083a2b;
      text-align: center;
      margin-bottom: 6px;
    }

    .atenea-login-subtitle {
      text-align: center;
      color: #507166;
      font-size: 0.95rem;
      margin-bottom: 24px;
    }

    .atenea-session-expired-note {
      background: #fff7e6;
      border: 1px solid #f3d7a0;
      border-radius: 16px;
      color: #7a5518;
      font-size: 0.93rem;
      font-weight: 700;
      line-height: 1.55;
      margin-bottom: 18px;
      padding: 14px 16px;
      text-align: center;
    }

    .atenea-login-flow-note {
      background: #ecf8f1;
      border: 1px solid #b8dfcb;
      border-radius: 16px;
      color: #0d5b38;
      font-size: 0.93rem;
      font-weight: 700;
      line-height: 1.55;
      margin-bottom: 18px;
      padding: 14px 16px;
      text-align: center;
    }

    .atenea-input {
      border-radius: 12px !important;
      padding: 0.9rem 1rem !important;
      border: 1px solid #d6e4de;
      color: #14392e;
      font-size: 0.95rem;
      height: auto !important;
    }

    .atenea-input:focus {
      border-color: #0b7a4b;
      box-shadow: 0 0 0 0.18rem rgba(11, 122, 75, 0.16);
    }

    .atenea-btn-main {
      background: #0b7a4b;
      border: 1px solid #0b7a4b;
      color: #ffffff;
      border-radius: 12px;
      font-weight: 700;
      padding: 0.75rem 1rem;
    }

    .atenea-btn-main:hover,
    .atenea-btn-main:focus {
      background: #09683f;
      border-color: #09683f;
      color: #ffffff;
      text-decoration: none;
    }

    .atenea-btn-ghost {
      background: #ffffff;
      border: 1px solid #0b7a4b;
      color: #0b7a4b;
      border-radius: 12px;
      font-weight: 700;
      padding: 0.72rem 1rem;
      text-align: center;
      display: block;
    }

    .atenea-btn-ghost:hover,
    .atenea-btn-ghost:focus {
      background: #f0f8f4;
      color: #09683f;
      border-color: #09683f;
      text-decoration: none;
    }

    .atenea-back-home {
      margin-top: 10px;
      text-align: center;
    }

    .atenea-back-home a {
      color: #507166;
      font-size: 0.92rem;
      font-weight: 700;
      text-decoration: none;
    }

    .atenea-back-home a:hover {
      color: #0b7a4b;
      text-decoration: none;
    }

    .atenea-form-stack > * + * {
      margin-top: 1rem;
    }

    @media (max-width: 991.98px) {
      .atenea-login-panel {
        padding: 34px 24px;
      }

      .atenea-login-title {
        font-size: 1.9rem;
      }
    }

    @media (max-width: 575.98px) {
      body.atenea-login-page {
        padding: 16px 10px;
      }

      .atenea-login-card {
        border-radius: 20px;
      }

      .atenea-login-panel {
        padding: 28px 18px;
      }

      .atenea-login-brand span {
        font-size: 1.45rem;
      }
    }
  </style>
</head>
<body class="atenea-login-page" data-loader-text="Preparando inicio de sesión...">
  <div class="container atenea-login-shell">
    <div class="row justify-content-center">
      <div class="col-lg-11 col-md-12">
        <div class="card atenea-login-card">
          <div class="card-body p-0">
            <div class="row no-gutters">
              <div class="col-lg-6 d-none d-lg-block atenea-login-image"></div>
              <div class="col-lg-6">
                <div class="atenea-login-panel">
                  <div class="atenea-login-brand">
                    <img src="../img/Atenea Logo.png" alt="Atenea">
                    <span>ATENEA</span>
                  </div>

                  <h1 class="atenea-login-title">Aula Virtual</h1>
                  <p class="atenea-login-subtitle">Ingresa para continuar con tu cuenta.</p>
                  <?php if ($sessionExpired): ?>
                    <div class="atenea-session-expired-note" role="alert">
                      Tu sesión se cerró por inactividad. Inicia sesión nuevamente para continuar.
                    </div>
                  <?php endif; ?>

                  <?php if ($loginMessage !== ''): ?>
                    <div class="atenea-login-flow-note" role="alert">
                      <?php echo htmlspecialchars($loginMessage); ?>
                    </div>
                  <?php endif; ?>

                  <form class="user atenea-form-stack" role="form" action="processlogin.php" method="post" data-atenea-loading-form data-loader-text="Validando credenciales...">
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($loginRedirect); ?>">
                    <input type="hidden" name="msg" value="<?php echo htmlspecialchars($loginMessageCode); ?>">
                    <div class="form-group mb-0">
                      <input class="form-control atenea-input" placeholder="Nombre de usuario..." name="user" type="text" autocomplete="username" autofocus required>
                    </div>
                    <div class="form-group mb-0">
                      <input class="form-control atenea-input" placeholder="Contraseña..." name="password" type="password" autocomplete="current-password" required>
                    </div>

                    <button class="btn atenea-btn-main btn-block" type="submit" name="btnlogin">Iniciar sesión</button>

                    <div class="atenea-login-divider"><span>o continua con</span></div>
                    <div class="atenea-google-slot" id="ateneaGoogleSlot"></div>
                    <button class="atenea-google-fallback" type="button" id="ateneaGoogleFallback" disabled<?php echo $googleEnabled ? ' style="display:none;"' : ''; ?>>
                      <span class="atenea-google-icon">G</span>
                      <span>Iniciar sesión con Google</span>
                    </button>
                    <p class="atenea-google-btn-note" id="ateneaGoogleHelp">
                      <?php echo $googleEnabled
                          ? 'Usa el correo que ya está vinculado a tu cuenta dentro de Atenea.'
                          : 'Google Sign-In se activará cuando se configure el acceso de Google para Atenea.'; ?>
                    </p>

                    <div class="mt-2">
                      <a href="recover_password.php" class="atenea-btn-ghost">Recuperar mi contraseña</a>
                    </div>

                    <div class="mt-2">
                      <a href="registro.php" class="atenea-btn-ghost">Crear cuenta nueva</a>
                    </div>

                    <div class="atenea-back-home">
                      <a href="homepage.php"><i class="fa fa-arrow-left mr-1"></i>Regresar a Inicio</a>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="../js/sb-admin-2.min.js"></script>
  <script>
    (function () {
      var googleEnabled = <?php echo $googleEnabled ? 'true' : 'false'; ?>;
      var googleClientId = <?php echo json_encode($googleClientId, JSON_UNESCAPED_SLASHES); ?>;
      var googleNonce = <?php echo json_encode($googleNonce, JSON_UNESCAPED_SLASHES); ?>;
      var loginRedirect = <?php echo json_encode($loginRedirect, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
      var googleContainer = document.getElementById("ateneaGoogleSlot");
      var googleFallback = document.getElementById("ateneaGoogleFallback");
      var googleHelp = document.getElementById("ateneaGoogleHelp");

      function showGoogleFallback(message) {
        if (googleFallback) {
          googleFallback.style.display = "inline-flex";
          googleFallback.disabled = true;
          googleFallback.setAttribute("aria-disabled", "true");
        }

        if (googleContainer) {
          googleContainer.innerHTML = "";
        }

        if (googleHelp && message) {
          googleHelp.textContent = message;
        }
      }

      function handleGoogleCredential(response) {
        if (!response || !response.credential) {
          if (window.AteneaAlerts) {
            window.AteneaAlerts.error("Google no respondió", "No recibimos una credencial válida desde Google.");
          }

          return;
        }

        if (window.AteneaUI) {
          window.AteneaUI.showLoader("Validando cuenta de Google...");
        }

        fetch("process_google_login.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "Accept": "application/json"
          },
          body: JSON.stringify({
            credential: response.credential,
            nonce: googleNonce,
            redirect: loginRedirect
          })
        })
          .then(function (apiResponse) {
            return apiResponse.json().catch(function () {
              return null;
            });
          })
          .then(function (payload) {
            if (window.AteneaUI) {
              window.AteneaUI.hideLoader(true);
            }

            if (!payload || payload.status !== "success") {
              var errorMessage = payload && payload.message
                ? payload.message
                : "No pudimos iniciar sesión con Google en este momento.";

              return window.AteneaAlerts
                ? window.AteneaAlerts.error("Acceso con Google no disponible", errorMessage)
                : Promise.resolve();
            }

            return (window.AteneaAlerts
              ? window.AteneaAlerts.success("Bienvenido a Atenea", payload.message || "Tu sesión ya está lista.")
              : Promise.resolve()
            ).then(function () {
              window.location.href = payload.redirect || "homepage.php";
            });
          })
          .catch(function () {
            if (window.AteneaUI) {
              window.AteneaUI.hideLoader(true);
            }

            if (window.AteneaAlerts) {
              window.AteneaAlerts.error(
                "Error de conexión",
                "No fue posible completar el inicio con Google. Intenta nuevamente en unos segundos."
              );
            }
          });
      }

      function initGoogleSignIn() {
        if (!googleEnabled) {
          showGoogleFallback("Google Sign-In se activará cuando se configure el acceso de Google para Atenea.");
          return;
        }

        if (!(window.google && window.google.accounts && window.google.accounts.id)) {
          showGoogleFallback("Google Sign-In no terminó de cargar. Recarga la página para intentarlo otra vez.");
          return;
        }

        try {
          window.google.accounts.id.initialize({
            client_id: googleClientId,
            callback: handleGoogleCredential,
            auto_select: false,
            cancel_on_tap_outside: true
          });

          if (googleContainer) {
            window.google.accounts.id.renderButton(googleContainer, {
              theme: "outline",
              size: "large",
              shape: "pill",
              text: "continue_with",
              width: googleContainer.offsetWidth > 0 ? googleContainer.offsetWidth : 320,
              logo_alignment: "left"
            });
          }

          if (googleHelp) {
            googleHelp.textContent = "Usa el mismo correo que ya está asociado a tu cuenta dentro de Atenea.";
          }
        } catch (error) {
          showGoogleFallback("No se pudo iniciar Google Sign-In. Revisa el Client ID configurado para Atenea.");
        }
      }

      window.addEventListener("load", function () {
        window.setTimeout(initGoogleSignIn, 120);
      });
    }());
  </script>
</body>
</html>
