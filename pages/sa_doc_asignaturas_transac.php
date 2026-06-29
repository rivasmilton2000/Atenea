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

// Verificar si se está realizando una acción de agregar
if (isset($_GET['action']) && $_GET['action'] == 'add') {
    $profesor_id = $_POST['profesor_id'];
    $materia_id = $_POST['materia_id'];
    $grado_id = $_POST['grado_id'];
    $periodo_id = $_POST['periodo_id'];
    $da_estado = $_POST['da_estado'];

    // Preparar la consulta SQL para insertar los datos
    $query = "INSERT INTO docentes_asignaturas (profesor_id, materia_id, grado_id, periodo_id, da_estado) 
              VALUES (?, ?, ?, ?, ?)";
    
    // Preparar la declaración
    $stmt = mysqli_prepare($db, $query);

    // Vincular los parámetros
    mysqli_stmt_bind_param($stmt, "iiiii", $profesor_id, $materia_id, $grado_id, $periodo_id, $da_estado);

    // Ejecutar la declaración
    if (mysqli_stmt_execute($stmt)) {
        // Éxito
        mysqli_stmt_close($stmt);
        showAlert('success', 'Asignación Exitosa', 'Asignación de asignatura a docente exitosa.', true);
    } else {
        // Error
        showAlert('error', 'Error', 'Error al realizar la asignación: ' . mysqli_error($db));
    }
} else {
    // Si no es una acción de agregar, redirigir a la página principal
    header('location: sa_doc_asignaturas.php');
    exit();
}
?>
