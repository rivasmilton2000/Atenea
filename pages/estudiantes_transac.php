<?php
include '../includes/connection.php';

// Verifica si la conexión se estableció correctamente
if (!isset($db)) {
    die("La conexión a la base de datos no se ha establecido correctamente.");
}

// Recoger los datos del formulario
$nombres_estudiante = $_POST['nombres_estudiante'];
$apellidos_estudiante = $_POST['apellidos_estudiante'];
$direccion_estudiante = $_POST['direccion_estudiante'];
$correo_estudiante = $_POST['correo_estudiante'];
$fecha_nac_estudiante = $_POST['fecha_nac_estudiante'];
$edad_estudiante = $_POST['edad_estudiante'];
$genero_estudiante = $_POST['genero_estudiante'];
$grado_id_estudiante = $_POST['grado_id_estudiante'];
$carnet_estudiante = $_POST['carnet_estudiante'];
$info_medica_estudiante = $_POST['info_medica_estudiante'];
$fecha_reg_estudiante = date('d-m-Y');

$nombres_encargado = $_POST['nombres_encargado'];
$apellidos_encargado = $_POST['apellidos_encargado'];
$dui_encargado = $_POST['dui_encargado'];
$direccion_encargado = $_POST['direccion_encargado'];
$correo_encargado = $_POST['correo_encargado'];
$trabajo_encargado = $_POST['trabajo_encargado'];
$numero_cel_encargado = $_POST['numero_cel_encargado'];
$numero_tel_encargado = $_POST['numero_tel_encargado'];
$genero_encargado = $_POST['genero_encargado'];
$fecha_nac_encargado = $_POST['fecha_nac_encargado'];

// Fecha actual en formato Y-m-d
$current_date = date('Y-m-d');

// Validar la fecha de nacimiento del estudiante
if ($fecha_nac_estudiante > $current_date) {
    showAlert('Fecha de nacimiento inválida', 'La fecha de nacimiento del estudiante es inválida.', 'error');
    exit;
}

// Validar la fecha de nacimiento del encargado
if ($fecha_nac_encargado > $current_date) {
    showAlert('Fecha de nacimiento inválida', 'La fecha de nacimiento del encargado es inválida.', 'error');
    exit;
}

// Validar el dominio del correo electrónico del estudiante
$allowed_domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
$email_domain = substr(strrchr($correo_estudiante, "@"), 1);

if (!in_array($email_domain, $allowed_domains)) {
    showAlert('Dominio de correo no permitido', 'Por favor, ingrese un correo electrónico del estudiante con un dominio válido.', 'error');
    exit;
}

// Validar el dominio del correo electrónico del encargado
$email_domain_encargado = substr(strrchr($correo_encargado, "@"), 1);

if (!in_array($email_domain_encargado, $allowed_domains)) {
    showAlert('Dominio de correo no permitido', 'Por favor, ingrese un correo electrónico del encargado con un dominio válido.', 'error');
    exit;
}

// Validar el tipo de archivo de la imagen
$allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
$file_type = $_FILES['foto_estudiante']['type'];

if (!in_array($file_type, $allowed_image_types)) {
    showAlert('Tipo de archivo no permitido', 'Por favor, sube un archivo de imagen con formato JPEG, PNG o GIF.', 'error');
    exit;
}

//validar nombre y apellidos existentes
$query = "SELECT * FROM estudiantes WHERE nombres_estudiante = '$nombres_estudiante' AND apellidos_estudiante = '$apellidos_estudiante'";
$result = mysqli_query($db, $query);

if (mysqli_num_rows($result) > 0) {
    showAlert('Estudiante Existente', 'Ya existe un estudiante con el mismo nombre y apellido.', 'error');
    exit;
}

// Validar si el correo electrónico del estudiante ya existe
$query = "SELECT * FROM estudiantes WHERE correo_estudiante = '$correo_estudiante'";
$result = mysqli_query($db, $query);

if (mysqli_num_rows($result) > 0) {
    showAlert('Correo Electrónico Existente', 'Ya existe un estudiante con el mismo correo electrónico.', 'error');
    exit;
}

// Validar si el número de carnet ya existe
$query = "SELECT * FROM estudiantes WHERE carnet_estudiante = '$carnet_estudiante'";
$result = mysqli_query($db, $query);

if (mysqli_num_rows($result) > 0) {
    showAlert('Número de Carnet Existente', 'Ya existe un estudiante con el mismo número de carnet.', 'error');
    exit;
}

// Subir la imagen
$target_dir = "imagenes_estudiantes/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}
$foto_estudiante = $_FILES['foto_estudiante']['name'];
$target_file = $target_dir . basename($foto_estudiante);

if (!move_uploaded_file($_FILES["foto_estudiante"]["tmp_name"], $target_file)) {
    showAlert('Error al subir imagen', 'Lo siento, hubo un error al subir la imagen del estudiante.', 'error');
    exit;
}

// Inserción en la base de datos
$query = "INSERT INTO estudiantes (nombres_estudiante, apellidos_estudiante, direccion_estudiante, correo_estudiante, fecha_nac_estudiante, edad_estudiante, genero_estudiante, grado_id_estudiante, carnet_estudiante, info_medica_estudiante, fecha_reg_estudiante, nombres_encargado, apellidos_encargado, dui_encargado, direccion_encargado, correo_encargado, trabajo_encargado, numero_cel_encargado, numero_tel_encargado, genero_encargado, fecha_nac_encargado, foto_estudiante) 
VALUES ('$nombres_estudiante', '$apellidos_estudiante', '$direccion_estudiante', '$correo_estudiante', '$fecha_nac_estudiante', '$edad_estudiante', '$genero_estudiante', '$grado_id_estudiante', '$carnet_estudiante', '$info_medica_estudiante', '$fecha_reg_estudiante', '$nombres_encargado', '$apellidos_encargado', '$dui_encargado', '$direccion_encargado', '$correo_encargado', '$trabajo_encargado', '$numero_cel_encargado', '$numero_tel_encargado', '$genero_encargado', '$fecha_nac_encargado', '$foto_estudiante')";

if (mysqli_query($db, $query)) {
    showAlert('Éxito', 'Estudiante añadido exitosamente', 'success');
    exit;
} else {
    showAlert('Error en la base de datos', 'Lo siento, hubo un error al guardar los datos del estudiante.', 'error');
    exit;
}

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
                    window.location.href = 'estudiantes.php';
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
}
?>