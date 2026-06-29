<?php
session_start();
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
                    window.location = 'sa_inventario.php';
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $articulo = mysqli_real_escape_string($db, $_POST['articulo']);
    $cantidad = mysqli_real_escape_string($db, $_POST['cantidad']);
    $estado = mysqli_real_escape_string($db, $_POST['estado']);

    // Verificar si el artículo ya existe
    $query = "SELECT * FROM inventario WHERE articulo = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $articulo);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        showAlert('warning', 'Advertencia', 'Este artículo ya se encuentra en el inventario.', true);
    }

    // Validar cantidad
    if ($cantidad < 1 || $cantidad > 1500) {
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        showAlert('error', 'Error', 'Cantidad inválida. Ingresa una cantidad entre 1 a 1500 existencias', true);
    }

    mysqli_stmt_close($stmt);

    // Insertar el artículo
    $query = "INSERT INTO inventario (articulo, cantidad, i_estado) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "sii", $articulo, $cantidad, $estado);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        showAlert('success', 'Éxito', 'Artículo agregado al inventario.', true);
    } else {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        showAlert('error', 'Error', 'Error al agregar el artículo: ' . $error, true);
    }
} else {
    showAlert('error', 'Error', 'Acceso inválido.', true);
}
?>
