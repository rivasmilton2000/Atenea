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
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $fecha = $_POST['fecha'];
    $porcentaje = $_POST['porcentaje'];
    $contenido_id = $_POST['contenido_id'];

    // Verificar si el título ya existe en la base de datos
    $query = "SELECT COUNT(*) AS count FROM evaluaciones WHERE titulo = '$titulo'";
    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row['count'] > 0) {
        showAlert('error', 'Error', 'Este título de evaluación  ya está registrada.');
    }

    // Validar que la fecha no sea anterior a la fecha actual
    $currentDate = date('Y-m-d');
    if ($fecha < $currentDate) {
        showAlert('error', 'Error', 'Esta fecha es inválida para esta evaluación.');
    }

    // Validar que el porcentaje esté en el rango de 1 a 100
    if ($porcentaje < 1 || $porcentaje > 100) {
        showAlert('error', 'Error', 'El porcentaje de esta evaluación es inválida.');
    }

    // Insertar los datos de la evaluación en la base de datos
    $query = "INSERT INTO evaluaciones (titulo, descripcion, fecha, porcentaje, contenido_id) 
              VALUES ('$titulo', '$descripcion', '$fecha', $porcentaje, $contenido_id)";
    if (mysqli_query($db, $query)) {
        showAlert('success', 'Éxito', 'Evaluación registrada exitosamente.', 'docentes_vista_evaluaciones.php?id=' . $contenido_id);
    } else {
        showAlert('error', 'Error', 'Ocurrió un error al registrar la evaluación.');
    }
} else {
    showAlert('error', 'Error', 'Solicitud no válida.');
}
?>
