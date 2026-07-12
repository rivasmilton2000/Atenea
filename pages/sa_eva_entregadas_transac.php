<?php
include '../includes/connection.php';

// Iniciar la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Escapar los datos de entrada para prevenir inyección SQL
    $evaluacion_id = intval($_POST['evaluacion_id']);
    $alumno_id = intval($_POST['alumno_id']);
    $observacion = mysqli_real_escape_string($db, $_POST['observacion']);
    $estado = intval($_POST['ev_entregada_estado']); // Asegurarse de que estado sea un número entero

    // Verificar si se ha subido un archivo
    if (isset($_FILES['material']) && $_FILES['material']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['material'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Tipos de archivo permitidos
        $allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];

        // Verificar si el archivo tiene un tipo permitido
        if (!in_array($fileExtension, $allowedTypes)) {
            showAlert('error', 'Formato de archivo no permitido.', 'Verifica que el archivo sea el correcto');
        }

        // Generar un nombre único para el archivo
        $uniqueFileName = uniqid() . '_' . $fileName;

        // Ruta de la carpeta donde se guardará el archivo
        $uploadPath = 'archivos_evaluaciones/'; // Asegúrate de que esta ruta sea correcta

        // Mover el archivo a la carpeta de destino
        if (move_uploaded_file($fileTmpName, $uploadPath . $uniqueFileName)) {
            // Insertar los datos de la evaluación entregada en la base de datos
            $query = "INSERT INTO ev_entregadas (evaluacion_id, alumno_id, material, observacion, ev_entregada_estado) 
                      VALUES (?, ?, ?, ?, ?)";
            
            // Usar una declaración preparada para mayor seguridad
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "iissi", $evaluacion_id, $alumno_id, $uniqueFileName, $observacion, $estado);
            
            if (mysqli_stmt_execute($stmt)) {
                showAlert('success', 'Éxito', 'La evaluación entregada se ha registrado correctamente.', true);
            } else {
                showAlert('error', 'Error', 'Error al insertar la evaluación entregada en la base de datos: ' . mysqli_error($db));
            }
            mysqli_stmt_close($stmt);
        } else {
            showAlert('error', 'Error', 'Error al subir el archivo.');
        }
    } else {
        showAlert('error', 'Error', 'No se ha seleccionado ningún archivo.');
    }
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
?>
