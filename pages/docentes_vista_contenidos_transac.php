<?php
include '../includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $da_id = $_POST['da_id'];

    // Verificar si el título ya existe
    $query = "SELECT * FROM contenidos WHERE titulo = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $titulo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        showAlert('error', 'Error', 'Ya existe un contenido con el mismo título.', false);
        exit();
    }

    // Verificar si se ha subido un archivo
    if (isset($_FILES['material']) && $_FILES['material']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['material'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Extensiones permitidas
        $allowedExtensions = ['doc', 'docx', 'pdf', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            showAlert('error', 'Error', 'Tipo de archivo no permitido.', false);
            exit();
        }

        // Generar un nombre único para el archivo
        $uniqueFileName = uniqid() . '_' . $fileName;

        // Ruta de la carpeta donde se guardará el archivo
        $uploadPath = 'archivos_contenidos/';

        // Mover el archivo a la carpeta de destino
        if (move_uploaded_file($fileTmpName, $uploadPath . $uniqueFileName)) {
            // Insertar los datos del contenido en la base de datos
            $query = "INSERT INTO contenidos (titulo, descripcion, material, da_id) 
                      VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bind_param("sssi", $titulo, $descripcion, $uniqueFileName, $da_id);

            if ($stmt->execute()) {
                showAlert('success', 'Éxito', 'Contenido insertado con éxito.', true, "docentes_vista_contenidos.php?id=$da_id");
            } else {
                showAlert('error', 'Error', 'Error al insertar el contenido en la base de datos: ' . $stmt->error, false);
            }

            $stmt->close();
        } else {
            showAlert('error', 'Error', 'Error al subir el archivo.', false);
        }
    } else {
        showAlert('error', 'Error', 'No se ha seleccionado ningún archivo.', false);
    }
}

mysqli_close($db);

function showAlert($icon, $title, $text, $redirect = false, $redirectUrl = '') {
    $redirectUrl = $redirectUrl ?: 'docentes_vista_contenidos.php'; // URL por defecto para redirigir
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
?>
