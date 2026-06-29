<?php
include '../includes/connection.php';

// Función para mostrar alertas con SweetAlert2
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
                    window.location = '$redirect';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $evaluacion_id = $_POST['evaluacion_id'];
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $fecha = $_POST['fecha'];
    $porcentaje = $_POST['porcentaje'];

    // Obtener los valores actuales de la evaluación
    $query = "SELECT titulo, descripcion, fecha, porcentaje FROM evaluaciones WHERE evaluacion_id = $evaluacion_id";
    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_assoc($result);

    $currentTitulo = $row['titulo'];
    $currentDescripcion = $row['descripcion'];
    $currentFecha = $row['fecha'];
    $currentPorcentaje = $row['porcentaje'];

    // Verificar si no se ha hecho ningún cambio
    if ($titulo == $currentTitulo && $descripcion == $currentDescripcion && $fecha == $currentFecha && $porcentaje == $currentPorcentaje) {
        showAlert('warning', 'Advertencia', 'No has realizado ningún cambio.');
    }

    // Verificar si el título ya existe en la base de datos (excluyendo la evaluación actual)
    $query = "SELECT COUNT(*) AS count FROM evaluaciones WHERE titulo = '$titulo' AND evaluacion_id != $evaluacion_id";
    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row['count'] > 0) {
        showAlert('error', 'Error', 'Este título de evaluación ya está registrado.');
    }

    // Validar que la fecha no sea anterior a la fecha actual
    $currentDate = date('Y-m-d');
    if ($fecha < $currentDate) {
        showAlert('error', 'Error', 'Esta fecha es inválida para esta evaluación.');
    }

    // Validar que el porcentaje esté en el rango de 1 a 100
    if ($porcentaje < 1 || $porcentaje > 100) {
        showAlert('error', 'Error', 'El porcentaje de esta evaluación es inválido.');
    }

    // Actualizar los datos de la evaluación en la base de datos
    $query = "UPDATE evaluaciones 
              SET titulo = '$titulo', descripcion = '$descripcion', fecha = '$fecha', porcentaje = $porcentaje
              WHERE evaluacion_id = $evaluacion_id";
    if (mysqli_query($db, $query)) {
        // Obtener el ID del contenido asociado a la evaluación
        $query = "SELECT contenido_id FROM evaluaciones WHERE evaluacion_id = $evaluacion_id";
        $result = mysqli_query($db, $query) or die(mysqli_error($db));
        $row = mysqli_fetch_assoc($result);
        $contenido_id = $row['contenido_id'];

        showAlert('success', 'Éxito', 'Evaluación actualizada exitosamente.', 'docentes_vista_evaluaciones.php?id=' . $contenido_id);
    } else {
        showAlert('error', 'Error', 'Ocurrió un error al actualizar la evaluación.');
    }
} else {
    showAlert('error', 'Error', 'Solicitud no válida.');
}
?>
