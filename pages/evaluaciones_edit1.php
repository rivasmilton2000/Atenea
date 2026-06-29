<?php
include '../includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $evaluacion_id = intval($_POST['evaluacion_id']);
    $titulo = mysqli_real_escape_string($db, $_POST['titulo']);
    $descripcion = mysqli_real_escape_string($db, $_POST['descripcion']);
    $fecha = mysqli_real_escape_string($db, $_POST['fecha']);
    $porcentaje = floatval($_POST['porcentaje']);
    $contenido_id = intval($_POST['contenido_id']);
    $evaluacion_estado = isset($_POST['evaluacion_estado']) ? intval($_POST['evaluacion_estado']) : 1;

    // Validar los datos
    $errors = [];

    // Validar título duplicado
    $query = "SELECT COUNT(*) FROM evaluaciones WHERE titulo = ? AND evaluacion_id != ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "si", $titulo, $evaluacion_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($count > 0) {
        $errors[] = 'Ya existe una evaluación con el mismo nombre.';
    }

    // Validar fecha no anterior a la fecha actual
    $current_date = date('Y-m-d');
    if (strtotime($fecha) < strtotime($current_date)) {
        $errors[] = 'La fecha de la evaluación no puede ser anterior a la fecha actual.';
    }

    // Validar porcentaje entre 1 y 100
    if ($porcentaje < 1 || $porcentaje > 100) {
        $errors[] = 'El porcentaje de evaluación es inválido.';
    }

    // Verificar si hay cambios
    $query = "SELECT * FROM evaluaciones WHERE evaluacion_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $evaluacion_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $evaluacion = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $changes = false;
    foreach ($evaluacion as $key => $value) {
        if ($key != 'evaluacion_id' && $value != $$key) {
            $changes = true;
            break;
        }
    }

    if (!$changes) {
        showAlert('warning', 'Advertencia', 'No se han realizado cambios.');
    }

    if (empty($errors)) {
        // Preparar la consulta usando una declaración preparada
        $query = "UPDATE evaluaciones SET 
                  titulo = ?, 
                  descripcion = ?, 
                  fecha = ?, 
                  porcentaje = ?, 
                  contenido_id = ?,
                  evaluacion_estado = ?
                  WHERE evaluacion_id = ?";

        $stmt = mysqli_prepare($db, $query);

        if ($stmt) {
            // Vincular los parámetros
            mysqli_stmt_bind_param($stmt, "sssdiii", $titulo, $descripcion, $fecha, $porcentaje, $contenido_id, $evaluacion_estado, $evaluacion_id);

            // Ejecutar la consulta
            if (mysqli_stmt_execute($stmt)) {
                showAlert('success', 'Éxito', 'La evaluación se ha actualizado correctamente.', true);
            } else {
                showAlert('error', 'Error', 'Error al actualizar la evaluación: ' . mysqli_stmt_error($stmt));
            }

            mysqli_stmt_close($stmt);
        } else {
            showAlert('error', 'Error', 'Error al preparar la consulta: ' . mysqli_error($db));
        }
    } else {
        showAlert('error', 'Errores de validación', implode('<br>', $errors));
    }
} else {
    showAlert('error', 'Error', 'Solicitud no válida.');
}

// Cerrar la conexión
mysqli_close($db);

function showAlert($icon, $title, $text, $redirect = false) {
    echo "
    <link href='https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap' rel='stylesheet'>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script type='text/javascript'>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '$icon',
                title: '$title',
                html: '$text',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'custom-popup-class',
                    title: 'custom-title-class',
                    confirmButton: 'custom-confirm-button-class'
                }
            }).then((result) => {
                if (" . ($redirect ? "true" : "false") . ") {
                    window.location = 'evaluaciones.php';
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
