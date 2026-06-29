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
    // Escapar los datos de entrada para prevenir inyección SQL
    $titulo = mysqli_real_escape_string($db, $_POST['titulo']);
    $descripcion = mysqli_real_escape_string($db, $_POST['descripcion']);
    $da_id = intval($_POST['da_id']); // Asegurarse de que da_id sea un número entero
    $estado = intval($_POST['estado']); // Asegurarse de que estado sea un número entero

    // Verificar si el título del contenido ya existe
    $query = "SELECT COUNT(*) AS count FROM contenidos WHERE titulo = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $titulo);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    if ($row['count'] > 0) {
        showAlert('error', 'Error', 'El título de este contenido ya existe. Prueba con otro.');
    }

    // Verificar si se ha subido un archivo
    if (isset($_FILES['material']) && $_FILES['material']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['material'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validar tipo de archivo permitido
        $allowed_types = array("pdf", "doc", "docx", "txt", "xls", "xlsx", "ppt", "pptx");
        if (!in_array($fileExtension, $allowed_types)) {
            showAlert('error', 'Error', 'El tipo de archivo no es permitido.');
        }

        // Generar un nombre único para el archivo
        $uniqueFileName = uniqid() . '_' . $fileName;

        // Ruta de la carpeta donde se guardará el archivo
        $uploadPath = 'archivos_contenidos/'; // Asegúrate de que esta ruta sea correcta

        // Mover el archivo a la carpeta de destino
        if (move_uploaded_file($fileTmpName, $uploadPath . $uniqueFileName)) {
            // Insertar los datos del contenido en la base de datos
            $query = "INSERT INTO contenidos (titulo, descripcion, material, da_id, c_estado) 
                      VALUES (?, ?, ?, ?, ?)";
            
            // Usar una declaración preparada para mayor seguridad
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "sssii", $titulo, $descripcion, $uniqueFileName, $da_id, $estado);
            
            if (mysqli_stmt_execute($stmt)) {
                // Redireccionar a la página de contenidos
                showAlert('success', 'Éxito', 'Contenido añadido exitosamente.', true);
            } else {
                showAlert('error', 'Error', 'Error al insertar el contenido en la base de datos: ' . mysqli_error($db));
            }
            mysqli_stmt_close($stmt);
        } else {
            showAlert('error', 'Error', 'Error al subir el archivo.');
        }
    } else {
        showAlert('error', 'Error', 'No se ha seleccionado ningún archivo.');
    }
}

mysqli_close($db);
?>
