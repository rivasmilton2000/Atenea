<?php
include '../includes/connection.php';

// Obtener valores de POST
$vl = $_POST['vehicle_license'];
$vm = $_POST['vehicle_model'];
$v_estado = 1; // Establecer v_estado como 1 siempre

// Directorio de destino para las imágenes
$targetDirectory = "imagenes_vehiculos/";
$targetFile = $targetDirectory . basename($_FILES["vehicle_image"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

// Función para mostrar alertas con SweetAlert2
function showAlert($title, $text, $icon, $redirectUrl) {
    echo "
        <link href='https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap' rel='stylesheet'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script type='text/javascript'>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '" . $title . "',
                    text: '" . $text . "',
                    icon: '" . $icon . "',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'custom-popup-class',
                        title: 'custom-title-class',
                        confirmButton: 'custom-confirm-button-class'
                    }
                }).then((result) => {
                    window.location = '" . $redirectUrl . "';
                });
            });
        </script>
        <style>
            .custom-popup-class {
                font-family: 'Open Sans', sans-serif;
            }
            .custom-title-class {
                font-family: 'Open Sans', sans-serif;
                font-weight: 700;
            }
            .custom-confirm-button-class {
                font-family: 'Open Sans', sans-serif;
                font-weight: 600;
            }
        </style>
    ";
}

// Verificar si el archivo subido es una imagen válida
if (isset($_POST["submit"])) {
    $check = getimagesize($_FILES["vehicle_image"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        showAlert('Archivo no Válido', 'El archivo no es una imagen válida.', 'error', 'vehiculos.php');
        $uploadOk = 0;
        exit();
    }
}

// Verificar si el archivo ya existe
if (file_exists($targetFile)) {
    showAlert('Archivo Existente', 'Lo siento, el archivo ya existe.', 'error', 'vehiculos.php');
    $uploadOk = 0;
    exit();
}

// Verificar el tamaño del archivo
if ($_FILES["vehicle_image"]["size"] > 500000) {
    showAlert('Archivo Inválido', 'Lo siento, el archivo es demasiado grande.', 'warning', 'vehiculos.php');
    $uploadOk = 0;
    exit();
}

// Permitir solo ciertos formatos de archivo
if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
    showAlert('Archivo Inválido', 'Lo siento, solo se permiten archivos JPG, JPEG, PNG y GIF.', 'error', 'vehiculos.php');
    $uploadOk = 0;
    exit();
}

// Verificar si $uploadOk está configurado en 0 por algún error
if ($uploadOk == 0) {
    showAlert('Error', 'Lo siento, tu archivo no ha sido subido.', 'error', 'vehiculos.php');
} else {
    // Intentar mover el archivo subido al directorio de destino
    if (move_uploaded_file($_FILES["vehicle_image"]["tmp_name"], $targetFile)) {
        // Insertar los datos en la base de datos con la ruta de la imagen
        $query = "INSERT INTO vehicles (vehicle_license, vehicle_model, vehicle_image, v_estado) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("sssi", $vl, $vm, $targetFile, $v_estado);
            
            if ($stmt->execute()) {
                showAlert('Éxito', 'El registro fue ingresado con éxito.', 'success', 'vehiculos.php');
            } else {
                showAlert('Error', 'Error al guardar los datos en la base de datos: ' . $stmt->error, 'error', 'vehiculos.php');
            }
            
            $stmt->close();
        } else {
            showAlert('Error', 'Error al preparar la consulta: ' . $db->error, 'error', 'vehiculos.php');
        }
    } else {
        showAlert('Error', 'Lo siento, hubo un error al subir tu archivo.', 'error', 'vehiculos.php');
    }
}

mysqli_close($db);
?>
