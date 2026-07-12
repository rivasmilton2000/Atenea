<?php
include('../includes/connection.php');

if ($_GET['action'] === 'update' && isset($_POST['asignatura_id'], $_POST['asignatura'], $_POST['estado'])) {
    $asignatura_id = mysqli_real_escape_string($db, $_POST['asignatura_id']);
    $nombre_asignatura = mysqli_real_escape_string($db, $_POST['asignatura']);
    $estado_asignatura = mysqli_real_escape_string($db, $_POST['estado']);

    // Verificar si se han hecho cambios
    $query_original = "SELECT A_NAME, A_ESTADO FROM asignaturas WHERE ASIGNATURA_ID = $asignatura_id";
    $result_original = mysqli_query($db, $query_original);
    $row_original = mysqli_fetch_assoc($result_original);

    if ($row_original['A_NAME'] == $nombre_asignatura && $row_original['A_ESTADO'] == $estado_asignatura) {
        // No se han hecho cambios
        showAlert('warning', 'Sin cambios', 'No se han realizado modificaciones. Por favor, modifica los campos primero.');
    } else {
        // Verificar si el nuevo nombre ya existe para otra asignatura
        $query_check = "SELECT COUNT(*) as count FROM asignaturas WHERE A_NAME = '$nombre_asignatura' AND ASIGNATURA_ID != $asignatura_id";
        $result_check = mysqli_query($db, $query_check);
        $row_check = mysqli_fetch_assoc($result_check);

        if ($row_check['count'] > 0) {
            // El nombre ya existe
            showAlert('error', 'Asignatura existente', 'Esta asignatura ya existe. Por favor, elige otra asignatura.');
        } else {
            // Realizar la actualización
            $query = "UPDATE asignaturas SET A_NAME = '$nombre_asignatura', A_ESTADO = $estado_asignatura WHERE ASIGNATURA_ID = $asignatura_id";
            if (mysqli_query($db, $query)) {
                showAlert('success', 'Actualización exitosa', 'La asignatura ha sido actualizada correctamente.', true);
            } else {
                showAlert('error', 'Error', 'Error al actualizar la asignatura: ' . mysqli_error($db));
            }
        }
    }
    mysqli_close($db);
} else {
    showAlert('error', 'Error', 'Datos incompletos para actualizar la asignatura.');
}

function showAlert($icon, $title, $text, $redirect = false) {
    echo "
    <link href='https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap' rel='stylesheet'>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script type='text/javascript'>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '$icon',
                title: '$title',
                text: '$text',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'custom-popup-class',
                    title: 'custom-title-class',
                    confirmButton: 'custom-confirm-button-class'
                }
            }).then((result) => {
                if (" . ($redirect ? "true" : "false") . ") {
                    window.location = 'sa_asignaturas.php';
                } else {
                    window.history.back();
                }
            });
        });
    </script>
    <style>
        .custom-popup-class, .custom-title-class, .custom-confirm-button-class {
            font-family: 'Open Sans', sans-serif;
        }
        .custom-title-class {
            font-weight: 700;
        }
        .custom-confirm-button-class {
            font-weight: 600;
        }
    </style>";
    exit();
}
?>