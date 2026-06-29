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
                    window.location = 'sa_con_evaluacion.php';
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
    $contenidoId = $_POST['contenido_id'];
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $da_id = $_POST['da_id'];
    $estado = $_POST['c_estado'];

    // Verificar si el título ya existe
    $checkTitleQuery = "SELECT COUNT(*) as count FROM contenidos WHERE titulo = '$titulo' AND contenido_id != $contenidoId";
    $result = mysqli_query($db, $checkTitleQuery);
    $row = mysqli_fetch_assoc($result);
    if ($row['count'] > 0) {
        showAlert('error', 'Error', 'Este título de contenido ya está registrado.');
    }

    $changes = false;
    $fileUploaded = false;

    // Verificar si se ha subido un nuevo archivo
    if (isset($_FILES['material']) && $_FILES['material']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['material'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Verificar que el archivo sea del tipo permitido
        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $uniqueFileName = uniqid() . '_' . $fileName;
            $uploadPath = 'archivos_contenidos/';

            // Obtener el nombre del archivo anterior
            $query = "SELECT material FROM contenidos WHERE contenido_id = $contenidoId";
            $result = mysqli_query($db, $query) or die(mysqli_error($db));
            $row = mysqli_fetch_assoc($result);
            $oldFileName = $row['material'];

            // Eliminar el archivo anterior si existe
            if (file_exists($uploadPath . $oldFileName)) {
                unlink($uploadPath . $oldFileName);
            }

            // Mover el nuevo archivo a la carpeta de destino
            if (move_uploaded_file($fileTmpName, $uploadPath . $uniqueFileName)) {
                $fileUploaded = true;
                $changes = true;
            } else {
                showAlert('error', 'Error', 'Error al subir el archivo.');
            }
        } else {
            showAlert('error', 'Error', 'Tipo de archivo no permitido.');
        }
    }

    // Verificar si hubo cambios en los otros campos
    $query = "SELECT * FROM contenidos WHERE contenido_id = $contenidoId";
    $result = mysqli_query($db, $query) or die(mysqli_error($db));
    $oldData = mysqli_fetch_assoc($result);

    if ($oldData['titulo'] != $titulo || $oldData['descripcion'] != $descripcion || 
        $oldData['da_id'] != $da_id || $oldData['c_estado'] != $estado) {
        $changes = true;
    }

    if ($changes) {
        // Actualizar los datos del contenido en la base de datos
        $updateQuery = "UPDATE contenidos SET 
                        titulo = '$titulo', 
                        descripcion = '$descripcion', 
                        da_id = '$da_id', 
                        c_estado = '$estado'";
        
        if ($fileUploaded) {
            $updateQuery .= ", material = '$uniqueFileName'";
        }
        
        $updateQuery .= " WHERE contenido_id = $contenidoId";
        
        if (mysqli_query($db, $updateQuery)) {
            showAlert('success', 'Éxito', 'Contenido actualizado correctamente.', true);
        } else {
            showAlert('error', 'Error', 'Error al actualizar el contenido: ' . mysqli_error($db));
        }
    } else {
        showAlert('warning', 'Advertencia', 'No se realizaron cambios.');
    }
} else {
    showAlert('error', 'Error', 'Solicitud no válida.');
}
?>