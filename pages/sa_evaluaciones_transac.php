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
    $porcentaje = floatval($_POST['porcentaje']);
    $contenido_id = intval($_POST['contenido_id']);
    $estado = intval($_POST['estado']);

    // Validar los datos
    if (empty($titulo) || empty($descripcion) || empty($fecha) || $porcentaje < 0 || $porcentaje > 100 || $contenido_id <= 0 || ($estado != 0 && $estado != 1)) {
        showAlert('error', 'Datos incompletos', 'Por favor, complete todos los campos correctamente.');
    }

    // Verificar si ya existe una evaluación con el mismo nombre
    $query = "SELECT COUNT(*) FROM evaluaciones WHERE titulo = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $titulo);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($count > 0) {
        showAlert('error', 'Evaluación existente', 'Ya existe una evaluación con el mismo nombre.');
    }

    // Validar la fecha
    $currentDate = date('Y-m-d');
    if ($fecha < $currentDate) {
        showAlert('error', 'Fecha inválida', 'La fecha de la evaluación no se puede programar para ese día.');
    }

    // Validar el porcentaje de evaluación
    if ($porcentaje <= 0 || $porcentaje > 100) {
        showAlert('error', 'Porcentaje inválido', 'El porcentaje de evaluación es inválida');
    }

    // Insertar los datos de la evaluación en la base de datos
    $query = "INSERT INTO evaluaciones (titulo, descripcion, fecha, porcentaje, contenido_id, evaluacion_estado)
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "sssdii", $titulo, $descripcion, $fecha, $porcentaje, $contenido_id, $estado);

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
                    window.location = 'sa_evaluaciones.php';
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
