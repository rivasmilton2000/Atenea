<!DOCTYPE html>
<html lang="es">
<?php include '../includes/head_home.php'; ?>
<body>


<?php
// Conexión a la base de datos
include '../includes/connection.php';

// Librerias PHPMailer
require '../vendor/phpmailer/src/Exception.php';
require '../vendor/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



function sendMail($name, $email, $subject, $message, $myemail, $mypassword) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $myemail;
        $mail->Password   = $mypassword; // contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($myemail, 'Formulario Web');
        $mail->addReplyTo($email, $name);
        $mail->addAddress($myemail);

        $mail->isHTML(true);
        $mail->Subject = "Consulta: $subject";
        $mail->Body = "
<!DOCTYPE html>
<html lang='es'>
<head>
<meta charset='UTF-8'>
</head>
<body style='margin:0; padding:0; background-color:#f4f9f4; font-family: Arial, sans-serif;'>

  <table width='100%' cellpadding='0' cellspacing='0'>
    <tr>
      <td align='center'>
        <table width='600' cellpadding='0' cellspacing='0' style='background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.1);'>

          <!-- HEADER -->
          <tr>
            <td style='background:#2e7d32; padding:20px; text-align:center; color:#ffffff;'>
              <h1 style='margin:0; font-size:24px;'>Atenea</h1>
              <p style='margin:5px 0 0;'>Escuela de Naturopatía</p>
            </td>
          </tr>

          <!-- CONTENT -->
          <tr>
            <td style='padding:30px; color:#333333;'>
              <h2 style='color:#2e7d32; margin-top:0;'>Nueva consulta recibida</h2>

              <p><strong>Nombre:</strong> $name</p>
              <p><strong>Email:</strong> $email</p>
              <p><strong>Asunto:</strong> $subject</p>

              <div style='margin-top:20px; padding:15px; background:#f1f8f4; border-left:4px solid #2e7d32;'>
                <p style='margin:0;'><strong>Mensaje:</strong></p>
                <p style='margin-top:10px;'>$message</p>
              </div>
            </td>
          </tr>

          <!-- FOOTER -->
          <tr>
            <td style='background:#e8f5e9; padding:15px; text-align:center; font-size:12px; color:#555;'>
              © " . date('Y') . " Atenea - Escuela de Naturopatía<br>
              Este mensaje fue enviado desde el formulario de contacto del sitio web.
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
";


        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}

function alertaYVolver($titulo, $mensaje, $icono) {
    echo "<script>
        Swal.fire({
            icon: '$icono',
            title: '$titulo',
            text: '$mensaje'
        }).then(() => {
            window.location.href = 'contacto.php';
        });
    </script>";
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    // Limpiar datos
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Validaciones
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        alertaYVolver('Error', 'No dejes campos vacíos', 'warning');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
       alertaYVolver('Error', 'Correo electrónico inválido', 'warning');
    }

    // INSERT SEGURO (Prepared Statement)
    $stmt = $db->prepare(
        "INSERT INTO contactos (nombre, email, asunto, mensaje)
         VALUES (?, ?, ?, ?)"
    );

    $stmt->bind_param("ssss", $name, $email, $subject, $message);

    if ($stmt->execute()) {
        $email_sql = "SELECT email, token FROM configmail WHERE id = 1";
        $result = $db->query($email_sql);
        $row = $result->fetch_assoc();
        $myemail = $row['email'];
        $mypassword = $row['token'];
        if (sendMail($name, $email, $subject, $message, $myemail, $mypassword)) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Mensaje enviado',
                    text: 'Gracias por contactarnos'
                }).then(() => {
                    window.location.href = 'contacto.php';
                });
            </script>";
        } else {
            alertaYVolver('Error', 'El mensaje se guardó pero no se pudo enviar el correo', 'error');
        }

    } else {
        alertaYVolver('Error', 'No se pudo guardar la consulta', 'error');
    }

    $stmt->close();
    $db->close();
}
?>

</body>
</html>
