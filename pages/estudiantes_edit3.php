<?php
include('../includes/connection.php');

// Función para mostrar alertas con SweetAlert2
function showAlert($title, $text, $icon, $redirect = false) {
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
                    " . ($redirect ? "window.location.href = 'estudiantes.php';" : "window.history.back();") . "
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

// Recoger datos del formulario
$estudiante_id = $_POST['id'];
$nombres_estudiante = $_POST['nombres_estudiante'];
$apellidos_estudiante = $_POST['apellidos_estudiante'];
$direccion_estudiante = $_POST['direccion_estudiante'];
$correo_estudiante = $_POST['correo_estudiante'];
$fecha_nac_estudiante = date('Y-m-d', strtotime($_POST['fecha_nac_estudiante']));
$edad_estudiante = $_POST['edad_estudiante'];
$genero_estudiante = $_POST['genero_estudiante'];
$grado_id_estudiante = $_POST['grado_id_estudiante'];
$carnet_estudiante = $_POST['carnet_estudiante'];
$numero_lista_estudiante = $_POST['numero_lista_estudiante'];
$info_medica_estudiante = $_POST['info_medica_estudiante'];

// Obtener los datos originales del estudiante
$query = "SELECT * FROM estudiantes WHERE ESTUDIANTE_ID = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $estudiante_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$original_data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Validaciones

// Validar la fecha de nacimiento del estudiante
$current_date = date('Y-m-d');
if ($fecha_nac_estudiante > $current_date) {
    showAlert('Fecha de nacimiento inválida', 'La fecha de nacimiento del estudiante es inválida.', 'error');
}

// Validar el dominio del correo electrónico del estudiante
$allowed_domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
$email_domain = substr(strrchr($correo_estudiante, "@"), 1);

if (!in_array($email_domain, $allowed_domains)) {
    showAlert('Dominio de correo no permitido', 'Por favor, ingrese un correo electrónico del estudiante con un dominio válido.', 'error');
}

// Validar si el correo electrónico ya existe (excluyendo el estudiante actual)
$query = "SELECT * FROM estudiantes WHERE correo_estudiante = ? AND ESTUDIANTE_ID != ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "si", $correo_estudiante, $estudiante_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    showAlert('Correo Electrónico Existente', 'Ya existe otro estudiante con el mismo correo electrónico.', 'error');
}

// Validar si el nombre y apellidos ya existen (excluyendo el estudiante actual)
$query = "SELECT * FROM estudiantes WHERE nombres_estudiante = ? AND apellidos_estudiante = ? AND ESTUDIANTE_ID != ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "ssi", $nombres_estudiante, $apellidos_estudiante, $estudiante_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    showAlert('Nombre y Apellidos Existentes', 'Ya existe otro estudiante con el mismo nombre y apellidos.', 'error');
}

// Validar si el número de carnet ya existe (excluyendo el estudiante actual)
$query = "SELECT * FROM estudiantes WHERE carnet_estudiante = ? AND ESTUDIANTE_ID != ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "si", $carnet_estudiante, $estudiante_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    showAlert('Número de Carnet Existente', 'Ya existe otro estudiante con el mismo número de carnet.', 'error');
}

// Verificar si se subió una nueva foto
$new_photo_uploaded = false;
if (isset($_FILES['foto_estudiante']) && $_FILES['foto_estudiante']['error'] === UPLOAD_ERR_OK) {
    $foto_temp = $_FILES['foto_estudiante']['tmp_name'];
    $foto_nombre = $_FILES['foto_estudiante']['name'];
    $foto_destino = "imagenes_estudiantes/" . $foto_nombre;

    // Validar el tipo de archivo de la imagen
    $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['foto_estudiante']['type'];

    if (!in_array($file_type, $allowed_image_types)) {
        showAlert('Tipo de archivo no permitido', 'Por favor, sube un archivo de imagen con formato JPEG, PNG o GIF.', 'error');
    }

    // Subir la nueva foto
    if (!move_uploaded_file($foto_temp, $foto_destino)) {
        showAlert('Error al subir imagen', 'Lo siento, hubo un error al subir la imagen del estudiante.', 'error');
    }

    // Obtener y eliminar la foto actual del estudiante
    $query_foto = "SELECT foto_estudiante FROM estudiantes WHERE ESTUDIANTE_ID = ?";
    $stmt_foto = mysqli_prepare($db, $query_foto);
    mysqli_stmt_bind_param($stmt_foto, "i", $estudiante_id);
    mysqli_stmt_execute($stmt_foto);
    mysqli_stmt_bind_result($stmt_foto, $foto_actual);
    mysqli_stmt_fetch($stmt_foto);
    mysqli_stmt_close($stmt_foto);

    if (!empty($foto_actual) && file_exists("imagenes_estudiantes/" . $foto_actual)) {
        unlink("imagenes_estudiantes/" . $foto_actual);
    }

    // Actualizar el campo foto_estudiante en la base de datos
    $query_update_foto = "UPDATE estudiantes SET foto_estudiante = ? WHERE ESTUDIANTE_ID = ?";
    $stmt_update_foto = mysqli_prepare($db, $query_update_foto);
    mysqli_stmt_bind_param($stmt_update_foto, "si", $foto_nombre, $estudiante_id);
    mysqli_stmt_execute($stmt_update_foto);
    mysqli_stmt_close($stmt_update_foto);

    $new_photo_uploaded = true;
}

// Comparar datos originales con los nuevos
$changes_made = $new_photo_uploaded ||
    $nombres_estudiante !== $original_data['nombres_estudiante'] ||
    $apellidos_estudiante !== $original_data['apellidos_estudiante'] ||
    $direccion_estudiante !== $original_data['direccion_estudiante'] ||
    $correo_estudiante !== $original_data['correo_estudiante'] ||
    $fecha_nac_estudiante !== $original_data['fecha_nac_estudiante'] ||
    $edad_estudiante !== $original_data['edad_estudiante'] ||
    $genero_estudiante !== $original_data['genero_estudiante'] ||
    $grado_id_estudiante !== $original_data['grado_id_estudiante'] ||
    $carnet_estudiante !== $original_data['carnet_estudiante'] ||
    $numero_lista_estudiante !== $original_data['numero_lista_estudiante'] ||
    $info_medica_estudiante !== $original_data['info_medica_estudiante'];

if (!$changes_made) {
    showAlert('Sin cambios', 'No se ha realizado ningún cambio en los datos del estudiante.', 'warning');
}

// Actualizar los datos del estudiante si hay cambios
$query = "UPDATE estudiantes SET 
            nombres_estudiante = ?,
            apellidos_estudiante = ?,
            direccion_estudiante = ?,
            correo_estudiante = ?,
            fecha_nac_estudiante = ?,
            edad_estudiante = ?,
            genero_estudiante = ?,
            grado_id_estudiante = ?,
            carnet_estudiante = ?,
            numero_lista_estudiante = ?,
            info_medica_estudiante = ?
          WHERE ESTUDIANTE_ID = ?";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "sssssisssssi",
                        $nombres_estudiante,
                        $apellidos_estudiante,
                        $direccion_estudiante,
                        $correo_estudiante,
                        $fecha_nac_estudiante,
                        $edad_estudiante,
                        $genero_estudiante,
                        $grado_id_estudiante,
                        $carnet_estudiante,
                        $numero_lista_estudiante,
                        $info_medica_estudiante,
                        $estudiante_id);
if (mysqli_stmt_execute($stmt)) {
    showAlert('Éxito', 'Los datos del estudiante se han actualizado correctamente.', 'success', true);
} else {
    showAlert('Error', 'Ocurrió un error al actualizar los datos del estudiante.', 'error');
}
mysqli_stmt_close($stmt);
mysqli_close($db);
?>
