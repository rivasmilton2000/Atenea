<?php
include '../includes/connection.php';

// Iniciar la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Escapar los datos de entrada para prevenir inyección SQL
    $titulo = mysqli_real_escape_string($db, $_POST['titulo']);
    $descripcion = mysqli_real_escape_string($db, $_POST['descripcion']);
    $fecha = mysqli_real_escape_string($db, $_POST['fecha']);
    $porcentaje = floatval($_POST['porcentaje']); // Asegurarse de que porcentaje sea un número flotante
    $contenido_id = intval($_POST['contenido_id']); // Asegurarse de que contenido_id sea un número entero

    // Validar los datos
    if (empty($titulo) || empty($descripcion) || empty($fecha) || $porcentaje < 0 || $porcentaje > 100 || $contenido_id <= 0) {
        showAlert('error', 'Datos incompletos', 'Por favor, complete todos los campos correctamente.');
    }

    // Insertar los datos de la evaluación en la base de datos
    $query = "INSERT INTO evaluaciones (titulo, descripcion, fecha, porcentaje, contenido_id, evaluacion_estado) 
              VALUES (?, ?, ?, ?, ?, 1)";
    
    // Usar una declaración preparada para mayor seguridad
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "sssdi", $titulo, $descripcion, $fecha, $porcentaje, $contenido_id);
    
    if (mysqli_stmt_execute($stmt)) {
        showAlert('success', 'Éxito', 'La evaluación se ha creado correctamente.', true);
    } else {
        showAlert('error', 'Error', 'Error al insertar la evaluación en la base de datos: ' . mysqli_error($db));
    }
    mysqli_stmt_close($stmt);
} else {
    showAlert('error', 'Acceso no autorizado', 'Acceso no autorizado.');
}

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
                text: '$text',
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
