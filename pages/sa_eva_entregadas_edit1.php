<?php
include '../includes/connection.php';

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
                    window.location = 'sa_eva_entregadas.php';
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
    $ev_entregada_id = $_POST['ev_entregada_id'];
    $evaluacion_id = $_POST['evaluacion_id'];
    $alumno_id = $_POST['alumno_id'];
    $observacion = $_POST['observacion'];
    $ev_entregada_estado = $_POST['ev_entregada_estado'];

    // Obtener los datos actuales de la base de datos
    $query = "SELECT evaluacion_id, alumno_id, observacion, ev_entregada_estado, material FROM ev_entregadas WHERE ev_entregada_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $ev_entregada_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    $oldEvaluacionId = $row['evaluacion_id'];
    $oldAlumnoId = $row['alumno_id'];
    $oldObservacion = $row['observacion'];
    $oldEvEntregadaEstado = $row['ev_entregada_estado'];
    $oldFileName = $row['material'];

    // Verificar si se ha subido un nuevo archivo
    if (isset($_FILES['material']) && $_FILES['material']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['material'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['doc', 'docx', 'pdf', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
        $maxFileSize = 5 * 1024 * 1024; // 5 MB

        if (!in_array($fileExtension, $allowedExtensions)) {
            showAlert('error', 'Error', 'Este tipo de documentación no es válida', false);
        }

        if ($fileSize > $maxFileSize) {
            showAlert('error', 'Error', 'El tamaño del archivo excede el límite de 5 MB', false);
        }

        // Generar un nombre único para el archivo
        $uniqueFileName = uniqid() . '_' . $fileName;
        $uploadPath = 'archivos_evaluaciones/';

        // Eliminar el archivo anterior si existe
        if (!empty($oldFileName) && file_exists($uploadPath . $oldFileName)) {
            unlink($uploadPath . $oldFileName);
        }

        // Mover el nuevo archivo a la carpeta de destino
        if (move_uploaded_file($fileTmpName, $uploadPath . $uniqueFileName)) {
            $newFileName = $uniqueFileName;
        } else {
            showAlert('error', 'Error', 'Error al subir el archivo', false);
        }
    } else {
        $newFileName = $oldFileName;
    }

    // Verificar si se ha hecho algún cambio
    if (
        $evaluacion_id == $oldEvaluacionId &&
        $alumno_id == $oldAlumnoId &&
        $observacion == $oldObservacion &&
        $ev_entregada_estado == $oldEvEntregadaEstado &&
        $newFileName == $oldFileName
    ) {
        showAlert('warning', 'Advertencia', 'No se ha realizado ningún cambio. Por favor, haga algún cambio antes de guardar.', false);
    }

    // Actualizar los datos de la evaluación entregada en la base de datos
    $query = "UPDATE ev_entregadas SET evaluacion_id = ?, alumno_id = ?, material = ?, observacion = ?, ev_entregada_estado = ? WHERE ev_entregada_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "iissii", $evaluacion_id, $alumno_id, $newFileName, $observacion, $ev_entregada_estado, $ev_entregada_id);
    mysqli_stmt_execute($stmt);

    showAlert('success', 'Éxito', 'La evaluación entregada ha sido actualizada correctamente', true);
} else {
    echo 'Error: Solicitud no válida.';
}
?>
