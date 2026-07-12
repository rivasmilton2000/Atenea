<?php
session_start();
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
                    window.location = 'sa_documentacion.php';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'add') {
    $nombre_archivo = mysqli_real_escape_string($db, $_POST['nombre_archivo']);
    $permisos = mysqli_real_escape_string($db, $_POST['permisos']);
    $fecha = mysqli_real_escape_string($db, $_POST['fecha']);
    $estado = mysqli_real_escape_string($db, $_POST['estado']); // Nuevo campo para el estado del archivo

    // Configuración para el archivo
    $target_dir = "archivos_documentacion/";
    $allowed_types = array("pdf", "doc", "docx", "txt", "xls", "xlsx", "ppt", "pptx", "csv", "rtf", "html", "htm", "xml", "odt");
    $max_file_size = 100 * 1024 * 1024; // 100 MB

    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['archivo'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Verificar si el nombre del archivo ya existe en la base de datos
        $checkQuery = "SELECT 1 FROM archivos WHERE nombre_archivo = ?";
        $stmt = mysqli_prepare($db, $checkQuery);
        mysqli_stmt_bind_param($stmt, "s", $nombre_archivo);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            showAlert('error', 'Error', 'Ya existe un archivo con el mismo nombre.');
        }
        mysqli_stmt_close($stmt);

        // Generar un nombre único para el archivo
        $unique_file_name = uniqid() . '_' . $file_name;
        $target_file = $target_dir . $unique_file_name;

        // Verificar el tipo de archivo
        if (!in_array($file_ext, $allowed_types)) {
            showAlert('error', 'Error', 'Solo se permiten documentos.');
        }
        // Verificar el tamaño del archivo
        elseif ($file_size > $max_file_size) {
            showAlert('error', 'Error', 'El archivo es demasiado grande. El tamaño máximo permitido es 100 MB.');
        }
        // Intentar subir el archivo
        elseif (move_uploaded_file($file_tmp, $target_file)) {
            // Insertar registro en la base de datos
            $query = "INSERT INTO archivos (nombre_archivo, archivo, permisos, fecha_subida, a_estado) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "ssssi", $nombre_archivo, $unique_file_name, $permisos, $fecha, $estado);
            
            if (mysqli_stmt_execute($stmt)) {
                showAlert('success', 'Éxito', 'Documentación agregada correctamente.', true);
            } else {
                showAlert('error', 'Error', 'Error al agregar el archivo: ' . mysqli_error($db));
                // Si hay un error en la base de datos, eliminar el archivo subido
                unlink($target_file);
            }
            mysqli_stmt_close($stmt);
        } else {
            showAlert('error', 'Error', 'Lo siento, hubo un error al subir tu archivo.');
        }
    } else {
        showAlert('error', 'Error', 'No se ha seleccionado ningún archivo o ha ocurrido un error en la subida.');
    }

    mysqli_close($db);
} else {
    showAlert('error', 'Error', 'Acceso inválido.');
}
?>
