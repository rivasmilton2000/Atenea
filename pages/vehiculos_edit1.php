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
                    window.location = 'vehiculos.php';
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

// Verificar si se ha realizado algún cambio
$queryCurrentData = "SELECT vehicle_license, vehicle_model, vehicle_attendant FROM vehicles WHERE id = '$id'";
$resultCurrentData = mysqli_query($db, $queryCurrentData);
$currentData = mysqli_fetch_assoc($resultCurrentData);

$changes_made = false;
if ($currentData['vehicle_license'] != $vehicle_license || 
    $currentData['vehicle_model'] != $vehicle_model || 
    $currentData['vehicle_attendant'] != $vehicle_attendant) {
    $changes_made = true;
}

// Si no se han hecho cambios y no se ha subido una nueva imagen, mostrar un mensaje y salir
if (!$changes_made && $_FILES['vehicle_image']['size'] == 0) {
    showAlert('info', 'Sin cambios', 'No se han realizado cambios en el registro.', true);
}

// Verificar duplicados solo si se han hecho cambios
if ($changes_made) {
    $queryCheckDuplicates = "SELECT id FROM vehicles WHERE (vehicle_license = '$vehicle_license' OR vehicle_model = '$vehicle_model') AND id != '$id'";
    $resultCheckDuplicates = mysqli_query($db, $queryCheckDuplicates);

    if (mysqli_num_rows($resultCheckDuplicates) > 0) {
        showAlert('error', 'Error', 'El número de placa o el modelo ya están en uso. Por favor, elija otro.');
    }

    // Actualizar el campo vehicle_attendant
    if ($vehicle_attendant == '') {
        $vehicle_attendant = NULL;
    }

    // Actualizar campos vehicle_license, vehicle_model y vehicle_attendant
    $queryUpdate = "UPDATE vehicles SET 
                    vehicle_license = '$vehicle_license', 
                    vehicle_model = '$vehicle_model', 
                    vehicle_attendant = " . ($vehicle_attendant === NULL ? "NULL" : "'$vehicle_attendant'") . " 
                    WHERE id = '$id'";
    $resultUpdate = mysqli_query($db, $queryUpdate) or showAlert('error', 'Error', 'Error al actualizar el registro: ' . mysqli_error($db));
}

// Verificar si se ha subido una nueva imagen
if ($_FILES['vehicle_image']['size'] > 0) {
    // [El código para manejar la subida de imágenes se mantiene igual]
} elseif ($changes_made) {
    showAlert('success', 'Éxito', 'Registro actualizado con éxito.', true);
}
?>