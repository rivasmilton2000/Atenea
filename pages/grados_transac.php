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
                    window.location = 'grados.php';
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['action']) && $_GET['action'] === 'add') {
    $grado = mysqli_real_escape_string($db, $_POST['grado']);
    
    // Asumimos que el estado es activo por defecto, pero puedes modificar esto si tienes un campo de estado en tu formulario
    $estado = 1; // 1 para activo, puedes cambiarlo si es necesario

    // Verificar si el nombre del grado ya existe
    $queryCheck = "SELECT G_NAME FROM grados WHERE G_NAME = '$grado'";
    $resultCheck = mysqli_query($db, $queryCheck);

    if (mysqli_num_rows($resultCheck) > 0) {
        showAlert('error', 'Error', 'Este grado ya existe. Inténtalo de nuevo.');
    } else {
        // Insertar el nuevo grado si no existe
        $query = "INSERT INTO grados (G_NAME, G_ESTADO) VALUES ('$grado', '$estado')";
        
        if (mysqli_query($db, $query)) {
            showAlert('success', 'Éxito', 'Grado añadido exitosamente.', true);
        } else {
            showAlert('error', 'Error', 'Error al añadir el grado: ' . mysqli_error($db));
        }
    }

    mysqli_close($db);
} else {
    showAlert('error', 'Error', 'Acceso inválido.', true);
}
?>