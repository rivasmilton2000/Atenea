<?php
include '../includes/connection.php';

// Función para mostrar alertas usando SweetAlert2
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
                    window.location = 'sa_doc_asignaturas.php';
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $da_id = $_POST['da_id'];
    $profesor_id = $_POST['profesor_id'];
    $materia_id = $_POST['materia_id'];
    $grado_id = $_POST['grado_id'];
    $periodo_id = $_POST['periodo_id'];
    $da_estado = $_POST['da_estado'];

    // Obtener los valores actuales del registro
    $query = "SELECT profesor_id, materia_id, grado_id, periodo_id, da_estado FROM docentes_asignaturas WHERE da_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $da_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $current_profesor_id, $current_materia_id, $current_grado_id, $current_periodo_id, $current_da_estado);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Verificar si se han hecho cambios
    if ($profesor_id == $current_profesor_id && $materia_id == $current_materia_id && $grado_id == $current_grado_id && $periodo_id == $current_periodo_id && $da_estado == $current_da_estado) {
        showAlert('warning', 'Advertencia', 'No se han realizado cambios.');
    } else {
        // Preparar la consulta SQL para actualizar los datos
        $query = "UPDATE docentes_asignaturas SET 
                  profesor_id = ?, 
                  materia_id = ?, 
                  grado_id = ?, 
                  periodo_id = ?, 
                  da_estado = ? 
                  WHERE da_id = ?";

        $stmt = mysqli_prepare($db, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iiiiii", $profesor_id, $materia_id, $grado_id, $periodo_id, $da_estado, $da_id);

            if (mysqli_stmt_execute($stmt)) {
                showAlert('success', 'Éxito', 'Cambios actualizados exitosamente.', true);
            } else {
                showAlert('error', 'Error', 'Error al actualizar el registro: ' . mysqli_stmt_error($stmt));
            }

            mysqli_stmt_close($stmt);
        } else {
            showAlert('error', 'Error', 'Error en la preparación de la consulta: ' . mysqli_error($db));
        }
    }
} else {
    showAlert('error', 'Error', 'Método de solicitud no válido.');
}

mysqli_close($db);
?>
