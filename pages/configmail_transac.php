<?php
require 'session.php';
require_once '../includes/connection.php';

if (!logged_in() || atenea_session_is_public_user()) {
    header('Location: login.php');
    exit;
}

if (!in_array((string) ($_SESSION['TYPE'] ?? ''), ['Admin', 'SuperAdmin'], true)) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}

// Obtener datos del formulario
$id = mysqli_real_escape_string($db, $_POST['id']);
$email = mysqli_real_escape_string($db, $_POST['email']);
$token = mysqli_real_escape_string($db, $_POST['token']);

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>
            alert('Error: El formato del email no es válido.');
            window.location.href='configmail_admin.php';
          </script>";
    exit();
}

// Actualizar en la base de datos
$sql = "UPDATE configmail 
        SET email = '$email', 
            token = '$token'
        WHERE id = '$id'";

if (mysqli_query($db, $sql)) {
    echo "<script>
            alert('Configuración de correo actualizada exitosamente!');
            window.location.href='configmail_admin.php';
          </script>";
} else {
    echo "<script>
            alert('Error al actualizar: " . mysqli_error($db) . "');
            window.location.href='configmail_admin.php';
          </script>";
}

mysqli_close($db);
?>SESSION['TYPE'] ?? ''), ['Admin', 'SuperAdmin'], true)) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}
// Obtener datos del formulario
$id = mysqli_real_escape_string($db, $_POST['id']);
$email = mysqli_real_escape_string($db, $_POST['email']);
$token = mysqli_real_escape_string($db, $_POST['token']);

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>
            alert('Error: El formato del email no es válido.');
            window.location.href='configmail_admin.php';
          </script>";
    exit();
}

// Actualizar en la base de datos
$sql = "UPDATE configmail 
        SET email = '$email', 
            token = '$token'
        WHERE id = '$id'";

if (mysqli_query($db, $sql)) {
    echo "<script>
            alert('Configuración de correo actualizada exitosamente!');
            window.location.href='configmail_admin.php';
          </script>";
} else {
    echo "<script>
            alert('Error al actualizar: " . mysqli_error($db) . "');
            window.location.href='configmail_admin.php';
          </script>";
}

mysqli_close($db);
?>
