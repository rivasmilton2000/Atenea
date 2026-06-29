<?php
require 'session.php';
require_once '../includes/atenea_auth.php';

$googleClientId = atenea_google_client_id();
$googleEnabled = atenea_google_is_enabled();
$_SESSION['GOOGLE_REGISTER_NONCE'] = bin2hex(random_bytes(16));
$googleNonce = (string) $_SESSION['GOOGLE_REGISTER_NONCE'];

if (logged_in()) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Registro público de usuarios para Atenea">
  <meta name="author" content="Atenea">
  <title>Registro | Atenea</title>

  <link rel="icon" href="../img/Atenea Logo.png" type="image/png">
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css2?family=Handlee&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link href="../css/sb-admin-2.min.css" rel="stylesheet">
  <link href="../css/atenea-ui.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/atenea-ui.js" defer></script>
  <script src="../js/atenea-password-strength.js" defer></script>
  <style>
    body.atenea-register-page {
      margin: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: radial-gradient(circle at 12% 18%, #0d8a59 0%, #046845 45%, #035237 100%);
      font-family: "Nunito", sans-serif;
      padding: 24px 12px;
    }

    .atenea-register-shell {
      width: 100%;
      max-width: 1120px;
    }

    .atenea-register-card {
      border: 0;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 18px 60px rgba(0, 0, 0, 0.24);
    }

    .atenea-register-image {
      background:
        linear-gradient(rgba(4, 104, 69, 0.24), rgba(4, 104, 69, 0.38)),
        url("../img/atenea_img.jpg") center/cover no-repeat;
      min-height: 680px;
    }

    .atenea-register-panel {
      background: #ffffff;
      padding: 40px 34px;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .atenea-register-brand {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-bottom: 16px;
    }

    .atenea-register-brand img {
      width: 54px;
      height: 54px;
      object-fit: contain;
    }

    .atenea-register-brand span {
      color: #c8a133;
      font-size: 1.7rem;
      font-weight: 800;
    }

    .atenea-register-title {
      font-family: "Handlee", cursive;
      font-size: 2rem;
      color: #083a2b;
      text-align: center;
      margin-bottom: 8px;
    }

    .atenea-register-subtitle {
      text-align: center;
      color: #507166;
      font-size: 0.95rem;
      line-height: 1.6;
      margin-bottom: 22px;
    }

    .atenea-register-note {
      background: #f4faf7;
      border: 1px solid rgba(4, 104, 69, 0.1);
      border-radius: 16px;
      padding: 14px 16px;
      color: #45685d;
      font-size: 0.9rem;
      line-height: 1.55;
      margin-bottom: 18px;
    }

    .atenea-register-form .form-row {
      margin-left: -0.45rem;
      margin-right: -0.45rem;
    }

    .atenea-register-form .form-group {
      padding-left: 0.45rem;
      padding-right: 0.45rem;
      margin-bottom: 0.95rem;
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

    .atenea-field-hint {
      display: block;
      margin-top: 0.45rem;
      color: #6b8178;
      font-size: 0.82rem;
      line-height: 1.45;
    }

    .atenea-btn-main {
      background: #0b7a4b;
      border: 1px solid #0b7a4b;
      color: #ffffff;
      border-radius: 12px;
      font-weight: 700;
      padding: 0.78rem 1rem;
    }

    .atenea-btn-main:hover,
    .atenea-btn-main:focus {
      background: #09683f;
      border-color: #09683f;
      color: #ffffff;
    }

    .atenea-btn-ghost {
      background: #ffffff;
      border: 1px solid #0b7a4b;
      color: #0b7a4b;
      border-radius: 12px;
      font-weight: 700;
      padding: 0.75rem 1rem;
      text-align: center;
      display: block;
      text-decoration: none;
    }

    .atenea-btn-ghost:hover,
    .atenea-btn-ghost:focus {
      background: #f0f8f4;
      color: #09683f;
      text-decoration: none;
    }

    @media (max-width: 991.98px) {
      .atenea-register-panel {
        padding: 30px 22px;
      }
    }
  </style>
  <?php if ($googleEnabled): ?>
  <script src="https://accounts.google.com/gsi/client" async defer></script>
  <?php endif; ?>
</head>
<body class="atenea-register-page" data-loader-text="Preparando registro...">
  <div class="container atenea-register-shell">
    <div class="row justify-content-center">
      <div class="col-lg-11 col-md-12">
        <div class="card atenea-register-card">
          <div class="card-body p-0">
            <div class="row no-gutters">
              <div class="col-lg-5 d-none d-lg-block atenea-register-image"></div>
              <div class="col-lg-7">
                <div class="atenea-register-panel">
                  <div class="atenea-register-brand">
                    <img src="../img/Atenea Logo.png" alt="Atenea">
                    <span>ATENEA</span>
                  </div>
                  <h1 class="atenea-register-title">Crear cuenta</h1>
                  <p class="atenea-register-subtitle">
                    Completa tu registro para comprar productos, gestionar tu perfil y preparar tu acceso al aula virtual.
                  </p>
                  <div class="atenea-register-note">
                    Tu cuenta iniciará como usuario registrado. Todavía no entrará al dashboard académico de estudiante
                    hasta que se active el plan o la suscripción correspondiente.
                  </div>

                  <form
                    action="processregister.php"
                    method="post"
                    class="atenea-register-form"
                    data-atenea-loading-form
                    data-loader-text="Creando tu cuenta..."
                  >
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <input class="form-control atenea-input" type="text" name="first_name" placeholder="Nombres..." maxlength="100" required>
                      </div>
                      <div class="form-group col-md-6">
                        <input class="form-control atenea-input" type="text" name="last_name" placeholder="Apellidos..." maxlength="100" required>
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <input class="form-control atenea-input" type="email" name="email" placeholder="Correo electrónico..." maxlength="150" autocomplete="email" required>
                      </div>
                      <div class="form-group col-md-6">
                        <input class="form-control atenea-input" type="text" name="phone_number" placeholder="Teléfono o WhatsApp..." maxlength="25">
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <input class="form-control atenea-input" type="text" name="username" placeholder="Nombre de usuario..." maxlength="50" autocomplete="username" required>
                        <small class="atenea-field-hint">Usa letras, números, punto, guion o guion bajo.</small>
                      </div>
                      <div class="form-group col-md-6">
                        <input class="form-control atenea-input" type="date" name="birthdate" max="<?php echo date('Y-m-d'); ?>">
                        <small class="atenea-field-hint">Opcional. Podrás actualizarla más adelante desde tu perfil.</small>
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <input
                          id="registerPassword"
                          class="form-control atenea-input"
                          type="password"
                          name="password"
                          placeholder="Contraseña..."
                          maxlength="80"
                          autocomplete="new-password"
                          data-password-strength-input
                          data-password-strength-target="#registerPasswordStrength"
                          data-password-strength-text="#registerPasswordStrengthText"
                          data-password-checklist="#registerPasswordChecklist"
                          required
                        >
                      </div>
                      <div class="form-group col-md-6">
                        <input
                          id="registerPasswordConfirm"
                          class="form-control atenea-input"
                          type="password"
                          name="password_confirm"
                          placeholder="Confirmar contraseña..."
                          maxlength="80"
                          autocomplete="new-password"
                          data-password-confirm="#registerPassword"
                          data-password-confirm-text="#registerPasswordMatch"
                          required
                        >
                      </div>
                    </div>

                    <div class="form-group col-12">
                      <div class="atenea-password-panel">
                        <p class="atenea-password-panel__title">Seguridad de la contraseña</p>
                        <p class="atenea-password-panel__hint">Elige una clave fuerte para proteger tu cuenta desde el primer acceso.</p>
                        <div class="atenea-password-meter" id="registerPasswordStrength">
                          <span></span>
                          <span></span>
                          <span></span>
                          <span></span>
                        </div>
                        <div class="atenea-password-status">
                          <small id="registerPasswordStrengthText" class="text-muted">La contraseña aún no ha sido evaluada.</small>
                          <small id="registerPasswordMatch" class="text-muted">La confirmación debe coincidir exactamente.</small>
                        </div>
                        <ul class="atenea-password-checklist mt-3 mb-0" id="registerPasswordChecklist">
                          <li data-rule="length">Mínimo 8 caracteres.</li>
                          <li data-rule="upper">Al menos una mayúscula.</li>
                          <li data-rule="lower">Al menos una minúscula.</li>
                          <li data-rule="number">Al menos un número.</li>
                          <li data-rule="symbol">Al menos un símbolo.</li>
                        </ul>
                      </div>
                    </div>

                    <button class="btn atenea-btn-main btn-block mt-4" type="submit" name="btnregister">Crear mi cuenta</button>

                    <div class="atenea-google-stack">
                      <div class="atenea-login-divider"><span>o regístrate con</span></div>
                      <div class="atenea-google-slot" id="ateneaGoogleRegisterSlot"></div>
                      <button class="atenea-google-fallback" type="button" id="ateneaGoogleRegisterFallback" disabled<?php echo $googleEnabled ? ' style="display:none;"' : ''; ?>>
                        <span class="atenea-google-icon">G</span>
                        <span class="atenea-google-fallback__label">Regístrate con Google</span>
                        <span class="atenea-google-fallback__spacer" aria-hidden="true"></span>
                      </button>
                      <p class="atenea-google-btn-note" id="ateneaGoogleRegisterHelp">
                        <?php echo $googleEnabled
                          ? 'Usa un correo de Google para crear tu cuenta en Atenea.'
                          : 'Google Sign-In se activará cuando se configure el acceso de Google para Atenea.';
                        ?>
                      </p>
                    </div>

                    <div class="mt-3">
                      <a href="login.php" class="atenea-btn-ghost">Ya tengo una cuenta</a>
                    </div>
                    <div class="mt-3 text-center">
                      <a href="homepage.php" class="text-decoration-none" style="color:#507166;font-weight:700;">Regresar al inicio</a>
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
      var googleClientId = <?php echo json_encode($googleClientId, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
      var googleNonce = <?php echo json_encode($googleNonce, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

      var googleContainer = document.getElementById("ateneaGoogleRegisterSlot");
      var googleFallback = document.getElementById("ateneaGoogleRegisterFallback");
      var googleHelp = document.getElementById("ateneaGoogleRegisterHelp");

      function showGoogleFallback(message) {
        if (googleFallback) {
          googleFallback.style.display = "inline-grid";
          googleFallback.disabled = true;
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
          return Swal.fire("Error", "No se recibió una credencial válida de Google.", "error");
        }

        if (window.AteneaUI) {
          window.AteneaUI.showLoader("Creando cuenta con Google...");
        }

        fetch("process_google_register.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "Accept": "application/json"
          },
          body: JSON.stringify({
            credential: response.credential,
            nonce: googleNonce
          })
        })
          .then(function (res) {
            return res.json();
          })
          .then(function (payload) {
            if (window.AteneaUI) {
              window.AteneaUI.hideLoader(true);
            }

            if (!payload || payload.status !== "success") {
              return Swal.fire("No se pudo registrar", payload.message || "Intenta nuevamente.", "error");
            }

            Swal.fire("Cuenta creada", payload.message, "success").then(function () {
              window.location.href = payload.redirect || "usuario_vista.php";
            });
          })
          .catch(function () {
            if (window.AteneaUI) {
              window.AteneaUI.hideLoader(true);
            }

            Swal.fire("Error", "No fue posible completar el registro con Google.", "error");
          });
      }

      function initGoogleRegister() {
        if (!googleEnabled) {
          showGoogleFallback("Google Sign-In no está configurado todavía.");
          return;
        }

        if (!(window.google && window.google.accounts && window.google.accounts.id)) {
          showGoogleFallback("Google Sign-In no terminó de cargar. Recarga la página.");
          return;
        }

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
            text: "signup_with",
            width: googleContainer.offsetWidth > 0 ? googleContainer.offsetWidth : 320,
            logo_alignment: "left"
          });
        }
      }

      window.addEventListener("load", function () {
        setTimeout(initGoogleRegister, 120);
      });
    }());
  </script>
</body>
</html>
