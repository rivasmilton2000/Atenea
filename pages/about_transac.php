<?php
require 'session.php';
require_once '../includes/connection.php';

if (!logged_in() || atenea_session_is_public_user()) {
    header('Location: login.php');
    exit;
}

if (!in_array((string) ($_SESSION['TYPE'] ?? ''), ['Admin', 'SuperAdmin'], true)) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}

// Función para subir imagen
function uploadImage($file) {
    $target_dir = "../img/";
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = 'about_' . uniqid() . '_' . time() . '.' . $imageFileType;
    $target_file = $target_dir . $newFileName;
    
    // Verificar si es una imagen real
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ['success' => false, 'message' => 'El archivo no es una imagen.'];
    }
    
    // Verificar tamaño (2MB máximo)
    if ($file["size"] > 2000000) {
        return ['success' => false, 'message' => 'El archivo es muy grande. Máximo 2MB.'];
    }
    
    // Permitir solo ciertos formatos
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return ['success' => false, 'message' => 'Solo se permiten archivos JPG, JPEG, PNG y GIF.'];
    }
    
    // Intentar subir archivo
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'filename' => $newFileName];
    } else {
        return ['success' => false, 'message' => 'Error al subir el archivo.'];
    }
}

// Obtener datos del formulario
$id = mysqli_real_escape_string($db, $_POST['id']);
$titulo = mysqli_real_escape_string($db, $_POST['titulo']);
$descripcion_corta = mysqli_real_escape_string($db, $_POST['descripcion_corta']);
$descripcion = mysqli_real_escape_string($db, $_POST['descripcion']);
$caracteristica1 = mysqli_real_escape_string($db, $_POST['caracteristica1']);
$caracteristica2 = mysqli_real_escape_string($db, $_POST['caracteristica2']);
$caracteristica3 = mysqli_real_escape_string($db, $_POST['caracteristica3']);
$estado = mysqli_real_escape_string($db, $_POST['estado']);

// Imágenes actuales
$current_imagen = mysqli_real_escape_string($db, $_POST['current_imagen']);
$current_imagen2 = mysqli_real_escape_string($db, $_POST['current_imagen2']);
$current_imagen3 = mysqli_real_escape_string($db, $_POST['current_imagen3']);

// Variables para las imágenes
$imagen = $current_imagen;
$imagen2 = $current_imagen2;
$imagen3 = $current_imagen3;

// Procesar Imagen 1
if(isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] == 0) {
    $uploadResult = uploadImage($_FILES["imagen"]);
    if($uploadResult['success']) {
        // Eliminar imagen anterior si existe
        if(file_exists("../img/" . $current_imagen)) {
            unlink("../img/" . $current_imagen);
        }
        $imagen = $uploadResult['filename'];
    } else {
        echo "<script>
                alert('Error en Imagen 1: " . $uploadResult['message'] . "');
                window.location.href='about_admin.php';
              </script>";
        exit();
    }
}

// Procesar Imagen 2
if(isset($_FILES["imagen2"]) && $_FILES["imagen2"]["error"] == 0) {
    $uploadResult = uploadImage($_FILES["imagen2"]);
    if($uploadResult['success']) {
        // Eliminar imagen anterior si existe
        if(file_exists("../img/" . $current_imagen2)) {
            unlink("../img/" . $current_imagen2);
        }
        $imagen2 = $uploadResult['filename'];
    } else {
        echo "<script>
                alert('Error en Imagen 2: " . $uploadResult['message'] . "');
                window.location.href='about_admin.php';
              </script>";
        exit();
    }
}

// Procesar Imagen 3
if(isset($_FILES["imagen3"]) && $_FILES["imagen3"]["error"] == 0) {
    $uploadResult = uploadImage($_FILES["imagen3"]);
    if($uploadResult['success']) {
        // Eliminar imagen anterior si existe
        if(file_exists("../img/" . $current_imagen3)) {
            unlink("../img/" . $current_imagen3);
        }
        $imagen3 = $uploadResult['filename'];
    } else {
        echo "<script>
                alert('Error en Imagen 3: " . $uploadResult['message'] . "');
                window.location.href='about_admin.php';
              </script>";
        exit();
    }
}

// Actualizar en la base de datos
$sql = "UPDATE about 
        SET titulo = '$titulo', 
            descripcion_corta = '$descripcion_corta',
            descripcion = '$descripcion',
            imagen = '$imagen',
            imagen2 = '$imagen2',
            imagen3 = '$imagen3',
            caracteristica1 = '$caracteristica1',
            caracteristica2 = '$caracteristica2',
            caracteristica3 = '$caracteristica3',
            estado = '$estado' 
        WHERE id = '$id'";

if (mysqli_query($db, $sql)) {
    echo "<script>
            alert('Información actualizada exitosamente!');
            window.location.href='about_admin.php';
          </script>";
} else {
    echo "<script>
            alert('Error al actualizar: " . mysqli_error($db) . "');
            window.location.href='about_admin.php';
          </script>";
}

mysqli_close($db);
?>SESSION['TYPE'] ?? ''), ['Admin', 'SuperAdmin'], true)) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}
// Función para subir imagen
function uploadImage($file) {
    $target_dir = "../img/";
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = 'about_' . uniqid() . '_' . time() . '.' . $imageFileType;
    $target_file = $target_dir . $newFileName;
    
    // Verificar si es una imagen real
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ['success' => false, 'message' => 'El archivo no es una imagen.'];
    }
    
    // Verificar tamaño (2MB máximo)
    if ($file["size"] > 2000000) {
        return ['success' => false, 'message' => 'El archivo es muy grande. Máximo 2MB.'];
    }
    
    // Permitir solo ciertos formatos
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return ['success' => false, 'message' => 'Solo se permiten archivos JPG, JPEG, PNG y GIF.'];
    }
    
    // Intentar subir archivo
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'filename' => $newFileName];
    } else {
        return ['success' => false, 'message' => 'Error al subir el archivo.'];
    }
}

// Obtener datos del formulario
$id = mysqli_real_escape_string($db, $_POST['id']);
$titulo = mysqli_real_escape_string($db, $_POST['titulo']);
$descripcion_corta = mysqli_real_escape_string($db, $_POST['descripcion_corta']);
$descripcion = mysqli_real_escape_string($db, $_POST['descripcion']);
$caracteristica1 = mysqli_real_escape_string($db, $_POST['caracteristica1']);
$caracteristica2 = mysqli_real_escape_string($db, $_POST['caracteristica2']);
$caracteristica3 = mysqli_real_escape_string($db, $_POST['caracteristica3']);
$estado = mysqli_real_escape_string($db, $_POST['estado']);

// Imágenes actuales
$current_imagen = mysqli_real_escape_string($db, $_POST['current_imagen']);
$current_imagen2 = mysqli_real_escape_string($db, $_POST['current_imagen2']);
$current_imagen3 = mysqli_real_escape_string($db, $_POST['current_imagen3']);

// Variables para las imágenes
$imagen = $current_imagen;
$imagen2 = $current_imagen2;
$imagen3 = $current_imagen3;

// Procesar Imagen 1
if(isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] == 0) {
    $uploadResult = uploadImage($_FILES["imagen"]);
    if($uploadResult['success']) {
        // Eliminar imagen anterior si existe
        if(file_exists("../img/" . $current_imagen)) {
            unlink("../img/" . $current_imagen);
        }
        $imagen = $uploadResult['filename'];
    } else {
        echo "<script>
                alert('Error en Imagen 1: " . $uploadResult['message'] . "');
                window.location.href='about_admin.php';
              </script>";
        exit();
    }
}

// Procesar Imagen 2
if(isset($_FILES["imagen2"]) && $_FILES["imagen2"]["error"] == 0) {
    $uploadResult = uploadImage($_FILES["imagen2"]);
    if($uploadResult['success']) {
        // Eliminar imagen anterior si existe
        if(file_exists("../img/" . $current_imagen2)) {
            unlink("../img/" . $current_imagen2);
        }
        $imagen2 = $uploadResult['filename'];
    } else {
        echo "<script>
                alert('Error en Imagen 2: " . $uploadResult['message'] . "');
                window.location.href='about_admin.php';
              </script>";
        exit();
    }
}

// Procesar Imagen 3
if(isset($_FILES["imagen3"]) && $_FILES["imagen3"]["error"] == 0) {
    $uploadResult = uploadImage($_FILES["imagen3"]);
    if($uploadResult['success']) {
        // Eliminar imagen anterior si existe
        if(file_exists("../img/" . $current_imagen3)) {
            unlink("../img/" . $current_imagen3);
        }
        $imagen3 = $uploadResult['filename'];
    } else {
        echo "<script>
                alert('Error en Imagen 3: " . $uploadResult['message'] . "');
                window.location.href='about_admin.php';
              </script>";
        exit();
    }
}

// Actualizar en la base de datos
$sql = "UPDATE about 
        SET titulo = '$titulo', 
            descripcion_corta = '$descripcion_corta',
            descripcion = '$descripcion',
            imagen = '$imagen',
            imagen2 = '$imagen2',
            imagen3 = '$imagen3',
            caracteristica1 = '$caracteristica1',
            caracteristica2 = '$caracteristica2',
            caracteristica3 = '$caracteristica3',
            estado = '$estado' 
        WHERE id = '$id'";

if (mysqli_query($db, $sql)) {
    echo "<script>
            alert('Información actualizada exitosamente!');
            window.location.href='about_admin.php';
          </script>";
} else {
    echo "<script>
            alert('Error al actualizar: " . mysqli_error($db) . "');
            window.location.href='about_admin.php';
          </script>";
}

mysqli_close($db);
?>
