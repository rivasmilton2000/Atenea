<?php
require 'session.php';
require_once '../includes/atenea_auth.php';

if (logged_in()) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <link rel="icon" href="../img/Atenea Logo.png" type="image/png">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Recuperación de contraseña del Aula Virtual Atenea">
  <meta name="author" content="Atenea">
  <title>Recuperar contraseña | Atenea</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css2?family=Handlee&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link href="../css/sb-admin-2.min.css" rel="stylesheet">
  <link href="../css/atenea-ui.css" rel="stylesheet">
  <script src="../js/atenea-ui.js" defer></script>
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
      max-width: 980px;
    }

    .atenea-login-card {
      border: 0;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 18px 60px rgba(0, 0, 0, 0.24);
    }

    .atenea-login-image {
      background:
        linear-gradient(rgba(4, 104, 69, 0.3), rgba(4, 104, 69, 0.4)),
        url("../img/contra.png") center/cover no-repeat;
      min-height: 520px;
    }

    .atenea-login-panel {
      background: #ffffff;
      padding: 40px 36px;
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
      width: 54px;
      height: 54px;
      object-fit: contain;
    }

    .atenea-login-brand span {
      color: #c8a133;
      font-size: 1.65rem;
      font-weight: 800;
    }

    .atenea-login-title {
      font-family: "Handlee", cursive;
      font-size: 2rem;
      color: #083a2b;
      text-align: center;
      margin-bottom: 10px;
    }

    .atenea-login-subtitle {
      text-align: center;
      color: #507166;
      font-size: 0.95rem;
      margin-bottom: 24px;
      line-height: 1.6;
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
      text-decoration: none;
    }

    .atenea-btn-ghost:hover,
    .atenea-btn-ghost:focus {
      background: #f0f8f4;
      color: #09683f;
      text-decoration: none;
    }

    .atenea-form-stack > * + * {
      margin-top: 1rem;
    }

    @media (max-width: 991.98px) {
      .atenea-login-panel {
        padding: 32px 22px;
      }
    }
  </style>
</head>
<body class="atenea-login-page" data-loader-text="Preparando recuperación...">
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

                  <h1 class="atenea-login-title">Recuperar contraseña</h1>
                  <p class="atenea-login-subtitle">
                    Ingresa el correo asociado a tu cuenta. Te enviaremos un mensaje de confirmación para continuar con el proceso.
                  </p>

                  <form id="recoverForm" class="user atenea-form-stack" role="form" action="recover_password1.php" method="post">
                    <div class="form-group mb-0">
                      <input class="form-control atenea-input" placeholder="Correo electrónico..." name="email" type="email" autocomplete="email" autofocus required>
                    </div>
                    <input type="hidden" name="btnrecover" value="1">
                    <button class="btn atenea-btn-main btn-block" type="submit" name="btnrecover">Enviar solicitud</button>
                    <a href="login.php" class="atenea-btn-ghost">Volver al inicio de sesión</a>
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
      var recoverForm = document.getElementById("recoverForm");
      if (!recoverForm) {
        return;
      }

      recoverForm.addEventListener("submit", function (event) {
        event.preventDefault();

        if (window.AteneaUI) {
          window.AteneaUI.showLoader("Enviando solicitud...");
        }

        fetch(recoverForm.action, {
          method: "POST",
          headers: {
            "Accept": "application/json"
          },
          body: new FormData(recoverForm)
        })
          .then(function (response) {
            return response.json().catch(function () {
              return null;
            });
          })
          .then(function (payload) {
            if (window.AteneaUI) {
              window.AteneaUI.hideLoader(true);
            }

            if (!payload || payload.status !== "success") {
              var message = payload && payload.message
                ? payload.message
                : "No pudimos enviar tu solicitud en este momento.";

              return window.AteneaAlerts
                ? window.AteneaAlerts.error("Solicitud no enviada", message)
                : Promise.resolve();
            }

            return (window.AteneaAlerts
              ? window.AteneaAlerts.success("Solicitud enviada", payload.message)
              : Promise.resolve()
            ).then(function () {
              window.location.href = "login.php";
            });
          })
          .catch(function () {
            if (window.AteneaUI) {
              window.AteneaUI.hideLoader(true);
            }

            if (window.AteneaAlerts) {
              window.AteneaAlerts.error(
                "Error de conexión",
                "Ha ocurrido un error temporal. Intenta nuevamente dentro de unos minutos."
              );
            }
          });
      });
    }());
  </script>
</body>
</html>
