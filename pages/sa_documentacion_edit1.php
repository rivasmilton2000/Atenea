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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nombre_archivo = mysqli_real_escape_string($db, $_POST['nombre_archivo']);
    $permisos = $_POST['permisos'];
    $estado = $_POST['a_estado'];

    // Configurar la zona horaria a El Salvador
    date_default_timezone_set('America/El_Salvador');
    // Obtener solo la fecha actual
    $fecha_subida = date('Y-m-d');

    // Verificar si el nombre_archivo ya existe (excluyendo el registro actual)
    $query = "SELECT COUNT(*) AS count FROM archivos WHERE nombre_archivo = ? AND a_id != ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "si", $nombre_archivo, $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    if ($row['count'] > 0) {
        showAlert('error', 'Error', 'El nombre del archivo ya existe.');
    }

    // Configuración para el archivo
    $uploadPath = 'archivos_documentacion/';
    $allowed_types = array("pdf", "doc", "docx", "txt", "xls", "xlsx", "ppt", "pptx", "csv", "rtf", "html", "htm", "xml", "odt");
    $max_file_size = 5 * 1024 * 1024; // 5 MB

    // Obtener la información del archivo actual
    $query = "SELECT archivo FROM archivos WHERE a_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $currentFile = $row['archivo'];

    $fileChanged = false;

    // Validar si se ha subido un nuevo archivo
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpName = $_FILES['archivo']['tmp_name'];
        $fileName = basename($_FILES['archivo']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validar tipo de archivo
        if (!in_array($fileExt, $allowed_types)) {
            showAlert('error', 'Error', 'Este tipo de documentación no es válida.');
        }

        // Validar tamaño del archivo
        if ($_FILES['archivo']['size'] > $max_file_size) {
            showAlert('error', 'Error', 'El archivo es demasiado grande. El tamaño máximo permitido es 5 MB.');
        }

        // Crear nombre de archivo único
        $uniqueFileName = uniqid() . '_' . $fileName;
        $newFilePath = $uploadPath . $uniqueFileName;

        if (move_uploaded_file($fileTmpName, $newFilePath)) {
            $fileChanged = true; // Se ha subido un nuevo archivo

            // Actualizar la base de datos con el nuevo archivo, otros campos y la nueva fecha
            $query = "UPDATE archivos SET archivo = ?, nombre_archivo = ?, permisos = ?, fecha_subida = ?, a_estado = ? WHERE a_id = ?";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "ssissi", $uniqueFileName, $nombre_archivo, $permisos, $fecha_subida, $estado, $id);

            if (mysqli_stmt_execute($stmt)) {
                // Eliminar el archivo anterior si existe
                if ($currentFile && file_exists($uploadPath . $currentFile)) {
                    unlink($uploadPath . $currentFile);
                }
                showAlert('success', 'Éxito', 'Archivo actualizado y subido exitosamente.', true);
            } else {
                showAlert('error', 'Error', 'Error al actualizar el archivo en la base de datos: ' . mysqli_error($db));
                unlink($newFilePath);
            }
        } else {
            showAlert('error', 'Error', 'Error al subir el nuevo archivo.');
        }
    } else {
        // No se sube un nuevo archivo, verificar si se realizaron cambios en otros campos
        $query = "SELECT nombre_archivo, permisos, a_estado FROM archivos WHERE a_id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        // Verificar si otros campos han cambiado
        if ($row['nombre_archivo'] == $nombre_archivo && $row['permisos'] == $permisos && $row['a_estado'] == $estado) {
            showAlert('warning', 'Advertencia', 'No se han realizado cambios.', true);
        } else {
            // Si otros campos han cambiado, actualizar la base de datos
            $query = "UPDATE archivos SET nombre_archivo = ?, permisos = ?, fecha_subida = ?, a_estado = ? WHERE a_id = ?";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "sisii", $nombre_archivo, $permisos, $fecha_subida, $estado, $id);

            if (mysqli_stmt_execute($stmt)) {
                showAlert('success', 'Éxito', 'Registro de documentación actualizado exitosamente.', true);
            } else {
                showAlert('error', 'Error', 'Error al actualizar el registro de documentación: ' . mysqli_error($db));
            }
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_close($db);
} else {
    showAlert('error', 'Error', 'Acceso inválido.');
}
?>