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
                    window.location = 'sa_grados.php';
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
$grado = mysqli_real_escape_string($db, $_POST['grado']);
$estado = mysqli_real_escape_string($db, $_POST['estado']);

// Obtener valores originales del grado
$queryOriginal = "SELECT G_NAME, G_ESTADO FROM grados WHERE G_ID = '$id'";
$resultOriginal = mysqli_query($db, $queryOriginal);
$rowOriginal = mysqli_fetch_assoc($resultOriginal);

if ($grado == $rowOriginal['G_NAME'] && $estado == $rowOriginal['G_ESTADO']) {
    showAlert('warning', 'Sin cambios', 'No se han realizado cambios en el grado.');
}

// Verificar duplicados en G_NAME
$queryCheckDuplicates = "SELECT G_ID FROM grados WHERE G_NAME = '$grado' AND G_ID != '$id'";
$resultCheckDuplicates = mysqli_query($db, $queryCheckDuplicates);

if (mysqli_num_rows($resultCheckDuplicates) > 0) {
    showAlert('error', 'Error', 'El nombre del grado ya está en uso. Por favor, elija otro.');
}

// Actualizar campos G_NAME y G_ESTADO
$queryUpdate = "UPDATE grados SET 
                G_NAME = '$grado', 
                G_ESTADO = '$estado'
                WHERE G_ID = '$id'";
$resultUpdate = mysqli_query($db, $queryUpdate) or showAlert('error', 'Error', 'Error al actualizar el grado: ' . mysqli_error($db));

// Mostrar mensaje de éxito
showAlert('success', 'Éxito', 'Grado actualizado con éxito.', true);

?>
