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
                    window.location = 'documentacion.php';
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
    
    // Configurar la zona horaria a El Salvador
    date_default_timezone_set('America/El_Salvador');
    // Obtener solo la fecha actual
    $fecha_subida = date('Y-m-d');

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

    // Si se sube un nuevo archivo
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['archivo'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Verificar el tipo de archivo
        if (!in_array($fileExt, $allowed_types)) {
            showAlert('error', 'Error', 'Este tipo de documentación no es válida.');
        }
        // Verificar el tamaño del archivo
        elseif ($fileSize > $max_file_size) {
            showAlert('error', 'Error', 'El archivo es demasiado grande. El tamaño máximo permitido es 5 MB.');
        }
        else {
            // Generar un nombre único para el archivo
            $uniqueFileName = uniqid() . '_' . $fileName;
            $newFilePath = $uploadPath . $uniqueFileName;

            // Eliminar el archivo anterior si existe
            if (!empty($currentFile) && file_exists($uploadPath . $currentFile)) {
                if (unlink($uploadPath . $currentFile)) {
                    error_log("Archivo antiguo eliminado: " . $uploadPath . $currentFile);
                } else {
                    error_log("Error al eliminar archivo antiguo: " . $uploadPath . $currentFile);
                }
            }

            // Mover el nuevo archivo a la carpeta de destino
            if (move_uploaded_file($fileTmpName, $newFilePath)) {
                // Actualizar la base de datos con el nuevo archivo y otros campos
                $query = "UPDATE archivos SET archivo = ?, nombre_archivo = ?, permisos = ?, fecha_subida = ? WHERE a_id = ?";
                $stmt = mysqli_prepare($db, $query);
                mysqli_stmt_bind_param($stmt, "ssisi", $uniqueFileName, $nombre_archivo, $permisos, $fecha_subida, $id);

                if (mysqli_stmt_execute($stmt)) {
                    showAlert('success', 'Éxito', 'Archivo actualizado y subido exitosamente.', true);
                } else {
                    showAlert('error', 'Error', 'Error al actualizar el archivo en la base de datos: ' . mysqli_error($db));
                    unlink($newFilePath);
                }
            } else {
                showAlert('error', 'Error', 'Error al subir el nuevo archivo.');
            }
        }
    } else {
        // No se sube un nuevo archivo, solo actualizar otros campos
        $query = "SELECT nombre_archivo, permisos FROM archivos WHERE a_id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        // Verificar si otros campos han cambiado
        if ($row['nombre_archivo'] == $nombre_archivo && $row['permisos'] == $permisos) {
            showAlert('warning', 'Advertencia', 'No se han realizado cambios.', true);
        } else {
            $query = "UPDATE archivos SET nombre_archivo = ?, permisos = ?, fecha_subida = ? WHERE a_id = ?";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "sisi", $nombre_archivo, $permisos, $fecha_subida, $id);

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
