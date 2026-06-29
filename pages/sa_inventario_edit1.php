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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $articulo = mysqli_real_escape_string($db, $_POST['articulo']);
    $cantidad = mysqli_real_escape_string($db, $_POST['cantidad']);
    $estado = $_POST['estado'];

    // Obtener los valores actuales del registro
    $query = "SELECT articulo, cantidad, i_estado FROM inventario WHERE i_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $current_articulo, $current_cantidad, $current_estado);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Verificar si se han hecho cambios
    if ($articulo == $current_articulo && $cantidad == $current_cantidad && $estado == $current_estado) {
        showAlert('warning', 'Advertencia', 'No se han realizado cambios.');
    }

    // Validar que la cantidad esté entre 1 y 1500
    if ($cantidad < 1 || $cantidad > 1500) {
        showAlert('error', 'Error', 'Cantidad inválida. Debe ser entre 1 y 1500.');
    }

    // Validar que no haya duplicados en el nombre del artículo
    $checkQuery = "SELECT i_id FROM inventario WHERE articulo = ? AND i_id != ?";
    $stmt = mysqli_prepare($db, $checkQuery);
    mysqli_stmt_bind_param($stmt, "si", $articulo, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        showAlert('error', 'Error', 'Este artículo ya existe en el inventario.');
    }
    mysqli_stmt_close($stmt);

    // Preparar la consulta SQL para actualizar los datos
    $query = "UPDATE inventario SET articulo = ?, cantidad = ?, i_estado = ? WHERE i_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "siii", $articulo, $cantidad, $estado, $id);

    if (mysqli_stmt_execute($stmt)) {
        showAlert('success', 'Éxito', 'Registro de inventario actualizado exitosamente.', true);
    } else {
        showAlert('error', 'Error', 'Error al actualizar el registro de inventario: ' . mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);
    mysqli_close($db);
} else {
    showAlert('error', 'Error', 'Acceso inválido.');
    mysqli_close($db);
}
?>
