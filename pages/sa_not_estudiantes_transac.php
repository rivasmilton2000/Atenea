<?php
include('../includes/connection.php');


// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $id_ev_entregada = $_POST['ev_entregada_id'];
    $valor_nota = $_POST['valor_nota'];
    $nota_estado = $_POST['nota_estado'];

    // Validar el valor de la nota
    if ($valor_nota < 0 || $valor_nota > 10) {
        showAlert('error', 'Error', 'La nota debe estar entre 0 y 10.', false);
        exit();
    }

    // Obtener la fecha y hora actual en la zona horaria de El Salvador
    date_default_timezone_set('America/El_Salvador');
    $fecha = date('Y-m-d H:i:s');

    // Preparar la consulta SQL
    $query = "INSERT INTO notas (id_ev_entregada, valor_nota, fecha, nota_estado) 
              VALUES (?, ?, ?, ?)";

    // Preparar la declaración
    $stmt = $db->prepare($query);

    // Vincular los parámetros
    $stmt->bind_param("idsi", $id_ev_entregada, $valor_nota, $fecha, $nota_estado);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        // Si la inserción fue exitosa
        showAlert('success', 'Éxito', 'Nota insertada con éxito.', true);
    } else {
        // Si hubo un error en la inserción
        showAlert('error', 'Error', 'Error al insertar la nota: ' . $stmt->error, false);
    }

    // Cerrar la declaración
    $stmt->close();
} else {
    // Si se intenta acceder directamente a este archivo sin enviar el formulario
    showAlert('error', 'Acceso inválido', 'Acceso inválido.', true);
}

// Cerrar la conexión
$db->close();

function showAlert($icon, $title, $text, $redirect = false, $redirectUrl = '') {
    $redirectUrl = $redirectUrl ?: 'sa_not_estudiantes.php'; // URL por defecto para redirigir
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