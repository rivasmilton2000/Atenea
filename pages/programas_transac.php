<?php
include '../includes/connection.php';
session_start();

$action = $_GET['action'];

// Función para subir imagen
function uploadImage($file) {
    $target_dir = "../img/";
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = 'programa_' . uniqid() . '_' . time() . '.' . $imageFileType;
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

switch ($action) {
    case 'add':
        $titulo = mysqli_real_escape_string($db, $_POST['titulo']);
        $descripcion_corta = mysqli_real_escape_string($db, $_POST['descripcion_corta']);
        $descripcion_completa = mysqli_real_escape_string($db, $_POST['descripcion_completa']);
        $nivel = mysqli_real_escape_string($db, $_POST['nivel']);
        $instructor = mysqli_real_escape_string($db, $_POST['instructor']);
        $orden = mysqli_real_escape_string($db, $_POST['orden']);
        $estado = mysqli_real_escape_string($db, $_POST['estado']);
        
        // Subir imagen
        if(isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] == 0) {
            $uploadResult = uploadImage($_FILES["imagen"]);
            
            if($uploadResult['success']) {
                $imagen = $uploadResult['filename'];
                
                $sql = "INSERT INTO programas_educativos (titulo, descripcion_corta, descripcion_completa, imagen, nivel, instructor, orden, estado) 
                        VALUES ('$titulo', '$descripcion_corta', '$descripcion_completa', '$imagen', '$nivel', '$instructor', '$orden', '$estado')";
                
                if (mysqli_query($db, $sql)) {
                    echo "<script>
                            alert('Programa agregado exitosamente!');
                            window.location.href='programas_admin.php';
                          </script>";
                } else {
                    // Si falla la BD, eliminar la imagen subida
                    unlink("../img/" . $imagen);
                    echo "<script>
                            alert('Error al agregar el programa: " . mysqli_error($db) . "');
                            window.location.href='programas_admin.php';
                          </script>";
                }
            } else {
                echo "<script>
                        alert('Error: " . $uploadResult['message'] . "');
                        window.location.href='programas_admin.php';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Por favor selecciona una imagen.');
                    window.location.href='programas_admin.php';
                  </script>";
        }
        break;

    case 'edit':
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $titulo = mysqli_real_escape_string($db, $_POST['titulo']);
        $descripcion_corta = mysqli_real_escape_string($db, $_POST['descripcion_corta']);
        $descripcion_completa = mysqli_real_escape_string($db, $_POST['descripcion_completa']);
        $nivel = mysqli_real_escape_string($db, $_POST['nivel']);
        $instructor = mysqli_real_escape_string($db, $_POST['instructor']);
        $orden = mysqli_real_escape_string($db, $_POST['orden']);
        $estado = mysqli_real_escape_string($db, $_POST['estado']);
        $current_image = mysqli_real_escape_string($db, $_POST['current_image']);
        
        // Verificar si se subió una nueva imagen
        if(isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] == 0) {
            $uploadResult = uploadImage($_FILES["imagen"]);
            
            if($uploadResult['success']) {
                $nueva_imagen = $uploadResult['filename'];
                
                // Eliminar imagen anterior
                if(file_exists("../img/" . $current_image)) {
                    unlink("../img/" . $current_image);
                }
                
                $sql = "UPDATE programas_educativos 
                        SET titulo = '$titulo', 
                            descripcion_corta = '$descripcion_corta',
                            descripcion_completa = '$descripcion_completa',
                            imagen = '$nueva_imagen',
                            nivel = '$nivel',
                            instructor = '$instructor',
                            orden = '$orden',
                            estado = '$estado' 
                        WHERE id = '$id'";
            } else {
                echo "<script>
                        alert('Error: " . $uploadResult['message'] . "');
                        window.location.href='programas_edit.php?id=$id';
                      </script>";
                exit();
            }
        } else {
            // No se subió nueva imagen, mantener la actual
            $sql = "UPDATE programas_educativos 
                    SET titulo = '$titulo', 
                        descripcion_corta = '$descripcion_corta',
                        descripcion_completa = '$descripcion_completa',
                        nivel = '$nivel',
                        instructor = '$instructor',
                        orden = '$orden',
                        estado = '$estado' 
                    WHERE id = '$id'";
        }
        
        if (mysqli_query($db, $sql)) {
            echo "<script>
                    alert('Programa actualizado exitosamente!');
                    window.location.href='programas_admin.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error al actualizar el programa: " . mysqli_error($db) . "');
                    window.location.href='programas_edit.php?id=$id';
                  </script>";
        }
        break;

    default:
        header('Location: programas_admin.php');
        break;
}

mysqli_close($db);
?>