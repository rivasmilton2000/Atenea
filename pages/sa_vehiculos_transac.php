<?php
include '../includes/connection.php';

if ($_GET['action'] == 'add') {
    $vl = $_POST['vehicle_license'];
    $vm = $_POST['vehicle_model'];
    $v_estado = $_POST['v_estado'];

    // Verificación de duplicados (placa)
    $stmt = $db->prepare("SELECT COUNT(*) FROM vehicles WHERE vehicle_license = ?");
    $stmt->bind_param("s", $vl);
    $stmt->execute();
    $stmt->bind_result($count_license);
    $stmt->fetch();
    $stmt->close();

    if ($count_license > 0) {
        $alertTitle = 'Placa Existente';
        $alertText = 'La placa del vehículo "' . htmlspecialchars($vl) . '" ya existe en el sistema. Por favor, ingrese una placa diferente.';
        $alertIcon = 'warning';
        showAlert($alertTitle, $alertText, $alertIcon);
        exit();
    }

    // Verificación de duplicados (modelo)
    $stmt = $db->prepare("SELECT COUNT(*) FROM vehicles WHERE vehicle_model = ?");
    $stmt->bind_param("s", $vm);
    $stmt->execute();
    $stmt->bind_result($count_model);
    $stmt->fetch();
    $stmt->close();

    if ($count_model > 0) {
        $alertTitle = 'Modelo Existente';
        $alertText = 'El modelo del vehículo "' . htmlspecialchars($vm) . '" ya existe en el sistema. Por favor, ingrese un modelo diferente.';
        $alertIcon = 'warning';
        showAlert($alertTitle, $alertText, $alertIcon);
        exit();
    }

    // Directorio de destino para las imágenes
    $targetDirectory = "imagenes_vehiculos/";
    $targetFile = $targetDirectory . basename($_FILES["vehicle_image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Verificar si el archivo subido es una imagen válida
    $check = getimagesize($_FILES["vehicle_image"]["tmp_name"]);
    if ($check === false) {
        $alertTitle = 'Archivo no Válido';
        $alertText = 'El archivo no es una imagen válida.';
        $alertIcon = 'error';
        showAlert($alertTitle, $alertText, $alertIcon);
        exit();
    }

    // Verificar si el archivo ya existe
    if (file_exists($targetFile)) {
        $alertTitle = 'Archivo Existente';
        $alertText = 'Lo siento, el archivo ya existe.';
        $alertIcon = 'error';
        showAlert($alertTitle, $alertText, $alertIcon);
        exit();
    }

    // Verificar el tamaño del archivo
    if ($_FILES["vehicle_image"]["size"] > 500000) {
        $alertTitle = 'Archivo inválido';
        $alertText = 'Lo siento, el archivo es demasiado grande.';
        $alertIcon = 'warning';
        showAlert($alertTitle, $alertText, $alertIcon);
        exit();
    }

    // Permitir solo ciertos formatos de archivo
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        $alertTitle = 'Archivo inválido';
        $alertText = 'Lo siento, solo se permiten archivos de imagen.';
        $alertIcon = 'error';
        showAlert($alertTitle, $alertText, $alertIcon);
        exit();
    }

    // Intentar mover el archivo subido al directorio de destino
    if (move_uploaded_file($_FILES["vehicle_image"]["tmp_name"], $targetFile)) {
        // Preparar la consulta SQL usando una declaración preparada
        $query = "INSERT INTO vehicles (vehicle_license, vehicle_model, vehicle_image, v_estado) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt) {
            // Vincular los parámetros
            $stmt->bind_param("sssi", $vl, $vm, $targetFile, $v_estado);
            
            // Ejecutar la declaración
            if ($stmt->execute()) {
                $alertTitle = 'Éxito';
                $alertText = 'El vehículo fue ingresado con éxito.';
                $alertIcon = 'success';
                showAlert($alertTitle, $alertText, $alertIcon); 
            } else {
                $alertTitle = 'Error';
                $alertText = 'Error al guardar los datos en la base de datos: ' . $stmt->error;
                $alertIcon = 'error';
                showAlert($alertTitle, $alertText, $alertIcon);
            }
            
            // Cerrar la declaración
            $stmt->close();
        } else {
            $alertTitle = 'Error';
            $alertText = 'Error al preparar la consulta: ' . $db->error;
            $alertIcon = 'error';
            showAlert($alertTitle, $alertText, $alertIcon);
        }
    } else {
        $alertTitle = 'Error';
        $alertText = 'Lo siento, hubo un error al subir tu archivo.';
        $alertIcon = 'error';
        showAlert($alertTitle, $alertText, $alertIcon);
    }

    mysqli_close($db);
} else {
    $alertTitle = 'Acción no Válida';
    $alertText = 'Acción no válida.';
    $alertIcon = 'error';
    showAlert($alertTitle, $alertText, $alertIcon);
}

function showAlert($title, $text, $icon) {
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
                    window.location = 'sa_vehiculos.php';
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
?>
