<?php
include '../includes/connection.php';

function showAlert($icon, $title, $text, $redirect = false, $redirectUrl = '') {
    $redirectUrl = $redirectUrl ?: 'docentes_vista_contenidos.php';
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
                if (result.isConfirmed) {
                    if (" . ($redirect ? "true" : "false") . ") {
                        window.location = '$redirectUrl';
                    } else {
                        window.history.back();
                    }
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

    // Verificar si el título ya existe en otro contenido
    $query = "SELECT * FROM contenidos WHERE titulo = ? AND contenido_id != ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("si", $titulo, $contenidoId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        showAlert('error', 'Error', 'Ya existe un contenido con este título. Por favor, elija otro título.', false);
        exit();
    }
    $stmt->close();

    // Obtener los datos actuales del contenido
    $query = "SELECT * FROM contenidos WHERE contenido_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $contenidoId);
    $stmt->execute();
    $result = $stmt->get_result();
    $contenido = $result->fetch_assoc();
    $stmt->close();

    // Verificar si se ha hecho algún cambio
    $changesMade = ($titulo !== $contenido['titulo'] || $descripcion !== $contenido['descripcion']);

    // Iniciar la transacción
    $db->begin_transaction();

    try {
        // Actualizar título y descripción
        if ($changesMade) {
            $query = "UPDATE contenidos SET titulo = ?, descripcion = ? WHERE contenido_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("ssi", $titulo, $descripcion, $contenidoId);
            $stmt->execute();
        }

        // Manejar la subida de archivo si se proporciona uno nuevo
        if (isset($_FILES['material']) && $_FILES['material']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['material'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Extensiones permitidas
            $allowedExtensions = ['doc', 'docx', 'pdf', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];

            if (!in_array($fileExtension, $allowedExtensions)) {
                throw new Exception('Tipo de archivo no permitido. Solo se permiten archivos de Word, PDF, Excel, PowerPoint y TXT.');
            }

            $uniqueFileName = uniqid() . '_' . $fileName;
            $uploadPath = 'archivos_contenidos/';

            // Eliminar el archivo anterior si existe
            if (file_exists($uploadPath . $contenido['material'])) {
                unlink($uploadPath . $contenido['material']);
            }

            // Mover el nuevo archivo
            if (move_uploaded_file($fileTmpName, $uploadPath . $uniqueFileName)) {
                $query = "UPDATE contenidos SET material = ? WHERE contenido_id = ?";
                $stmt = $db->prepare($query);
                $stmt->bind_param("si", $uniqueFileName, $contenidoId);
                $stmt->execute();
                $changesMade = true;
            } else {
                throw new Exception('Error al subir el archivo.');
            }
        }

        // Confirmar la transacción
        $db->commit();

        if ($changesMade) {
            showAlert('success', 'Éxito', 'Contenido actualizado con éxito.', true, "docentes_vista_contenidos.php?id=$da_id");
        } else {
            showAlert('warning', 'Advertencia', 'No se han realizado cambios.', false);
        }
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $db->rollback();
        showAlert('error', 'Error', $e->getMessage(), false);
    }
} else {
    showAlert('error', 'Error', 'Solicitud no válida.', false);
}

$db->close();
?>