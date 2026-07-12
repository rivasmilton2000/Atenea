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

$action = $_GET['action'];

switch ($action) {
    case 'add':
        $titulo = mysqli_real_escape_string($db, $_POST['titulo']);
        $descripcion = mysqli_real_escape_string($db, $_POST['descripcion']);
        $orden = mysqli_real_escape_string($db, $_POST['orden']);
        $estado = mysqli_real_escape_string($db, $_POST['estado']);

        $sql = "INSERT INTO facilities (titulo, descripcion, orden, estado) 
                VALUES ('$titulo', '$descripcion', '$orden', '$estado')";
        
        if (mysqli_query($db, $sql)) {
            echo "<script>
                    alert('Servicio agregado exitosamente!');
                    window.location.href='servicios.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error al agregar el servicio: " . mysqli_error($db) . "');
                    window.location.href='servicios.php';
                  </script>";
        }
        break;

    case 'edit':
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $titulo = mysqli_real_escape_string($db, $_POST['titulo']);
        $descripcion = mysqli_real_escape_string($db, $_POST['descripcion']);
        $orden = mysqli_real_escape_string($db, $_POST['orden']);
        $estado = mysqli_real_escape_string($db, $_POST['estado']);

        $sql = "UPDATE facilities 
                SET titulo = '$titulo', 
                    descripcion = '$descripcion', 
                    orden = '$orden', 
                    estado = '$estado' 
                WHERE id = '$id'";
        
        if (mysqli_query($db, $sql)) {
            echo "<script>
                    alert('Servicio actualizado exitosamente!');
                    window.location.href='servicios.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error al actualizar el servicio: " . mysqli_error($db) . "');
                    window.location.href='servicios.php';
                  </script>";
        }
        break;

    default:
        header('Location: servicios.php');
        break;
}

mysqli_close($db);
?>SESSION['TYPE'] ?? ''), ['Admin', 'SuperAdmin'], true)) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}
$action = $_GET['action'];

switch ($action) {
    case 'add':
        $titulo = mysqli_real_escape_string($db, $_POST['titulo']);
        $descripcion = mysqli_real_escape_string($db, $_POST['descripcion']);
        $orden = mysqli_real_escape_string($db, $_POST['orden']);
        $estado = mysqli_real_escape_string($db, $_POST['estado']);

        $sql = "INSERT INTO facilities (titulo, descripcion, orden, estado) 
                VALUES ('$titulo', '$descripcion', '$orden', '$estado')";
        
        if (mysqli_query($db, $sql)) {
            echo "<script>
                    alert('Servicio agregado exitosamente!');
                    window.location.href='servicios.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error al agregar el servicio: " . mysqli_error($db) . "');
                    window.location.href='servicios.php';
                  </script>";
        }
        break;

    case 'edit':
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $titulo = mysqli_real_escape_string($db, $_POST['titulo']);
        $descripcion = mysqli_real_escape_string($db, $_POST['descripcion']);
        $orden = mysqli_real_escape_string($db, $_POST['orden']);
        $estado = mysqli_real_escape_string($db, $_POST['estado']);

        $sql = "UPDATE facilities 
                SET titulo = '$titulo', 
                    descripcion = '$descripcion', 
                    orden = '$orden', 
                    estado = '$estado' 
                WHERE id = '$id'";
        
        if (mysqli_query($db, $sql)) {
            echo "<script>
                    alert('Servicio actualizado exitosamente!');
                    window.location.href='servicios.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error al actualizar el servicio: " . mysqli_error($db) . "');
                    window.location.href='servicios.php';
                  </script>";
        }
        break;

    default:
        header('Location: servicios.php');
        break;
}

mysqli_close($db);
?>
