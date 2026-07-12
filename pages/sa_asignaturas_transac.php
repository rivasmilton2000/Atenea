<?php
include '../includes/connection.php';

if ($_GET['action'] === 'add') {
    $nombreAsignatura = $_POST['asignatura'];
    $estadoAsignatura = $_POST['estado'];

    // Verificamos si la asignatura ya existe
    $stmt = $db->prepare("SELECT COUNT(*) FROM asignaturas WHERE A_NAME = ?");
    $stmt->bind_param("s", $nombreAsignatura);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $alertTitle = 'Asignatura Existente';
        $alertText = 'La asignatura "' . htmlspecialchars($nombreAsignatura) . '" ya existe en el sistema. Por favor, ingrese una asignatura diferente.';
        $alertIcon = 'warning';
    } else {
        $stmt = $db->prepare("INSERT INTO asignaturas (A_NAME, A_ESTADO) VALUES (?, ?)");
        $stmt->bind_param("si", $nombreAsignatura, $estadoAsignatura);

        if ($stmt->execute()) {
            $alertTitle = 'Éxito';
            $alertText = 'Asignatura agregada exitosamente.';
            $alertIcon = 'success';
        } else {
            $alertTitle = 'Error';
            $alertText = 'Error al insertar la asignatura en la base de datos: ' . $stmt->error;
            $alertIcon = 'error';
        }
        $stmt->close();
    }

    echo "
<link href='https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap' rel='stylesheet'>
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<script type='text/javascript'>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: '" . $alertTitle . "',
            text: '" . $alertText . "',
            icon: '" . $alertIcon . "',
            confirmButtonText: 'OK',
            customClass: {
                popup: 'custom-popup-class',
                title: 'custom-title-class',
                confirmButton: 'custom-confirm-button-class'
            }
        }).then((result) => {
            window.location = 'sa_asignaturas.php';
        });
    });
</script>
<style>
    .custom-popup-class {
        font-family: 'Open Sans', sans-serif;
    }
    .custom-title-class {
        font-family: 'Open Sans', sans-serif;
        font-weight: 700;
    }
    .custom-confirm-button-class {
        font-family: 'Open Sans', sans-serif;
        font-weight: 600;
    }
</style>
";

}

mysqli_close($db);
?>