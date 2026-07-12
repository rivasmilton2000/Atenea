<?php
include('../includes/connection.php');

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
                    window.location = 'sa_vehiculos.php';
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

// Obtener valores del formulario
$id = mysqli_real_escape_string($db, $_POST['id']);
$vehicle_license = mysqli_real_escape_string($db, $_POST['vehicle_license']);
$vehicle_model = mysqli_real_escape_string($db, $_POST['vehicle_model']);
$vehicle_attendant = mysqli_real_escape_string($db, $_POST['vehicle_attendant']);
$v_estado = mysqli_real_escape_string($db, $_POST['v_estado']);

// Verificar duplicados en vehicle_license y vehicle_model
$queryCheckDuplicates = "SELECT id FROM vehicles WHERE (vehicle_license = '$vehicle_license' OR vehicle_model = '$vehicle_model') AND id != '$id'";
$resultCheckDuplicates = mysqli_query($db, $queryCheckDuplicates);

if (mysqli_num_rows($resultCheckDuplicates) > 0) {
    showAlert('error', 'Error', 'El número de placa o el modelo ya están en uso. Por favor, elija otro.');
}

// Actualizar el campo vehicle_attendant
if ($vehicle_attendant == '') {
    $vehicle_attendant = NULL; // Cambiado a NULL para reflejar "No asignado" en la base de datos
}

// Actualizar campos vehicle_license, vehicle_model, vehicle_attendant y v_estado
$queryUpdate = "UPDATE vehicles SET 
                vehicle_license = '$vehicle_license', 
                vehicle_model = '$vehicle_model', 
                vehicle_attendant = " . ($vehicle_attendant === NULL ? "NULL" : "'$vehicle_attendant'") . ", 
                v_estado = '$v_estado' 
                WHERE id = '$id'";
$resultUpdate = mysqli_query($db, $queryUpdate) or showAlert('error', 'Error', 'Error al actualizar el registro: ' . mysqli_error($db));

// Verificar si se ha subido una nueva imagen
if ($_FILES['vehicle_image']['size'] > 0) {
    // Directorio donde se almacenarán las imágenes (ruta relativa)
    $targetDirectory = "imagenes_vehiculos/";
    $targetFile = $targetDirectory . basename($_FILES["vehicle_image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Eliminar la imagen antigua si existe
    $querySelectImage = "SELECT vehicle_image FROM vehicles WHERE id = '$id'";
    $resultSelectImage = mysqli_query($db, $querySelectImage);
    if ($row = mysqli_fetch_assoc($resultSelectImage)) {
        $oldImage = $row['vehicle_image'];
        if (!empty($oldImage) && file_exists($oldImage)) {
            unlink($oldImage); // Eliminar archivo del servidor
        }
    }

    // Verificar tamaño del archivo
    if ($_FILES["vehicle_image"]["size"] > 500000) {
        showAlert('error', 'Archivo inválido', 'El tamaño de este archivo es demasiado grande.');
    }
    // Verificar tipo de archivo permitido
    elseif (!in_array($imageFileType, array("jpg", "jpeg", "png", "gif"))) {
        showAlert('error', 'Archivo inválido', 'Solamente se permiten archivos de tipo imagen.');
    }
    // Si todas las verificaciones son exitosas, intentar subir el archivo
    elseif ($uploadOk == 1 && move_uploaded_file($_FILES["vehicle_image"]["tmp_name"], $targetFile)) {
        // Actualizar el campo vehicle_image en la base de datos con la ruta relativa
        $queryUpdateImage = "UPDATE vehicles SET vehicle_image = '$targetFile' WHERE id = '$id'";
        $resultUpdateImage = mysqli_query($db, $queryUpdateImage) or showAlert('error', 'Error', 'Error actualizando la imagen en la base de datos: ' . mysqli_error($db));
        showAlert('success', 'Éxito', 'Registro e imagen actualizados exitosamente.', true);
    } else {
        showAlert('error', 'Error', 'Hubo un error al subir tu archivo. El registro se actualizó sin cambios en la imagen.');
    }
} else {
    showAlert('success', 'Éxito', 'Registro actualizado con éxito.', true);
}
?>
