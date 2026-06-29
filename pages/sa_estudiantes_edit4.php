<?php
include('../includes/connection.php');

// Función para mostrar alertas con SweetAlert2
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
                if (result.isConfirmed) {
                    window.location.href = 'sa_estudiantes.php';
                }
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
    exit;
}

$estudiante_id = $_POST['id'];
$nombres_encargado = $_POST['nombres_encargado'];
$apellidos_encargado = $_POST['apellidos_encargado'];
$dui_encargado = $_POST['dui_encargado'];
$direccion_encargado = $_POST['direccion_encargado'];
$correo_encargado = $_POST['correo_encargado'];
$trabajo_encargado = $_POST['trabajo_encargado'];
$numero_cel_encargado = $_POST['numero_cel_encargado'];
$numero_tel_encargado = $_POST['numero_tel_encargado'];
$genero_encargado = $_POST['genero_encargado'];

// Obtener los datos originales del encargado
$query = "SELECT * FROM estudiantes WHERE ESTUDIANTE_ID = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $estudiante_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$original_data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Validaciones

// Validar que los campos obligatorios no estén vacíos
if (empty($nombres_encargado) || empty($apellidos_encargado) || empty($dui_encargado) || empty($correo_encargado)) {
    showAlert('Campos vacíos', 'Por favor, complete todos los campos obligatorios del encargado.', 'error');
}

// Validar el formato del DUI (asumiendo un formato de 8 dígitos seguidos de un guion y un dígito verificador)
if (!preg_match('/^\d{8}-\d$/', $dui_encargado)) {
    showAlert('DUI inválido', 'El formato del DUI del encargado no es válido.', 'error');
}

// Validar el dominio del correo electrónico del encargado
$allowed_domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
$email_domain = substr(strrchr($correo_encargado, "@"), 1);

if (!in_array($email_domain, $allowed_domains)) {
    showAlert('Dominio de correo no permitido', 'Por favor, ingrese un correo electrónico del encargado con un dominio válido.', 'error');
}

// Comparar datos originales con los nuevos
$changes_made = 
    $nombres_encargado !== $original_data['nombres_encargado'] ||
    $apellidos_encargado !== $original_data['apellidos_encargado'] ||
    $dui_encargado !== $original_data['dui_encargado'] ||
    $direccion_encargado !== $original_data['direccion_encargado'] ||
    $correo_encargado !== $original_data['correo_encargado'] ||
    $trabajo_encargado !== $original_data['trabajo_encargado'] ||
    $numero_cel_encargado !== $original_data['numero_cel_encargado'] ||
    $numero_tel_encargado !== $original_data['numero_tel_encargado'] ||
    $genero_encargado !== $original_data['genero_encargado'];

if (!$changes_made) {
    showAlert('Sin cambios', 'No se ha realizado ningún cambio en los datos del encargado.', 'warning');
}

// Actualizar los datos del encargado
$query = "UPDATE estudiantes SET 
            nombres_encargado = ?,
            apellidos_encargado = ?,
            dui_encargado = ?,
            direccion_encargado = ?,
            correo_encargado = ?,
            trabajo_encargado = ?,
            numero_cel_encargado = ?,
            numero_tel_encargado = ?,
            genero_encargado = ?
          WHERE ESTUDIANTE_ID = ?";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "sssssssssi",
                        $nombres_encargado,
                        $apellidos_encargado,
                        $dui_encargado,
                        $direccion_encargado,
                        $correo_encargado,
                        $trabajo_encargado,
                        $numero_cel_encargado,
                        $numero_tel_encargado,
                        $genero_encargado,
                        $estudiante_id);

if (mysqli_stmt_execute($stmt)) {
    showAlert('Éxito', 'Datos del encargado actualizados exitosamente', 'success');
} else {
    showAlert('Error en la base de datos', 'Lo siento, hubo un error al actualizar los datos del encargado.', 'error');
}

mysqli_stmt_close($stmt);
mysqli_close($db);
?>
