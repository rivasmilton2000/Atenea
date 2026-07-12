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

$action = $_GET['action'];

// Función para subir imagen
function uploadImage($file) {
    $target_dir = "../img/";
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = uniqid() . '_' . time() . '.' . $imageFileType;
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
        $categoria = mysqli_real_escape_string($db, $_POST['categoria']);
        $orden = mysqli_real_escape_string($db, $_POST['orden']);
        $estado = mysqli_real_escape_string($db, $_POST['estado']);
        
        // Subir imagen
        if(isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] == 0) {
            $uploadResult = uploadImage($_FILES["imagen"]);
            
            if($uploadResult['success']) {
                $imagen = $uploadResult['filename'];
                
                $sql = "INSERT INTO galeria (titulo, imagen, categoria, orden, estado) 
                        VALUES ('$titulo', '$imagen', '$categoria', '$orden', '$estado')";
                
                if (mysqli_query($db, $sql)) {
                    echo "<script>
                            alert('Imagen agregada exitosamente!');
                            window.location.href='galeria_home.php';
                          </script>";
                } else {
                    // Si falla la BD, eliminar la imagen subida
                    unlink("../img/" . $imagen);
                    echo "<script>
                            alert('Error al agregar la imagen: " . mysqli_error($db) . "');
                            window.location.href='galeria_home.php';
                          </script>";
                }
            } else {
                echo "<script>
                        alert('Error: " . $uploadResult['message'] . "');
                        window.location.href='galeria_home.php';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Por favor selecciona una imagen.');
                    window.location.href='galeria_home.php';
                  </script>";
        }
        break;

    case 'edit':
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $titulo = mysqli_real_escape_string($db, $_POST['titulo']);
        $categoria = mysqli_real_escape_string($db, $_POST['categoria']);
        $orden = mysqli_real_escape_string($db, $_POST['orden']);
        $estado = mysqli_real_escape_string($db, $_POST['estado']);
        $current_image = mysqli_real_escape_string($db, $_POST['current_image_name']);
        
        // Verificar si se subió una nueva imagen
        if(isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] == 0) {
            $uploadResult = uploadImage($_FILES["imagen"]);
            
            if($uploadResult['success']) {
                $nueva_imagen = $uploadResult['filename'];
                
                // Eliminar imagen anterior
                if(file_exists("../img/" . $current_image)) {
                    unlink("../img/" . $current_image);
                }
                
                $sql = "UPDATE galeria 
                        SET titulo = '$titulo', 
                            imagen = '$nueva_imagen',
                            categoria = '$categoria', 
                            orden = '$orden', 
                            estado = '$estado' 
                        WHERE id = '$id'";
            } else {
                echo "<script>
                        alert('Error: " . $uploadResult['message'] . "');
                        window.location.href='galeria_home.php';
                      </script>";
                exit();
            }
        } else {
            // No se subió nueva imagen, mantener la actual
            $sql = "UPDATE galeria 
                    SET titulo = '$titulo',
                        categoria = '$categoria', 
                        orden = '$orden', 
                        estado = '$estado' 
                    WHERE id = '$id'";
        }
        
        if (mysqli_query($db, $sql)) {
            echo "<script>
                    alert('Imagen actualizada exitosamente!');
                    window.location.href='galeria_home.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error al actualizar la imagen: " . mysqli_error($db) . "');
                    window.location.href='galeria_home.php';
                  </script>";
        }
        break;

    default:
        header('Location: galeria_home.php');
        break;
}

mysqli_close($db);
?>SESSION['TYPE'] ?? ''), ['Admin', 'SuperAdmin'], true)) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}
$action = $_GET['action'];

// Función para subir imagen
function uploadImage($file) {
    $target_dir = "../img/";
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = uniqid() . '_' . time() . '.' . $imageFileType;
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
        $categoria = mysqli_real_escape_string($db, $_POST['categoria']);
        $orden = mysqli_real_escape_string($db, $_POST['orden']);
        $estado = mysqli_real_escape_string($db, $_POST['estado']);
        
        // Subir imagen
        if(isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] == 0) {
            $uploadResult = uploadImage($_FILES["imagen"]);
            
            if($uploadResult['success']) {
                $imagen = $uploadResult['filename'];
                
                $sql = "INSERT INTO galeria (titulo, imagen, categoria, orden, estado) 
                        VALUES ('$titulo', '$imagen', '$categoria', '$orden', '$estado')";
                
                if (mysqli_query($db, $sql)) {
                    echo "<script>
                            alert('Imagen agregada exitosamente!');
                            window.location.href='galeria_home.php';
                          </script>";
                } else {
                    // Si falla la BD, eliminar la imagen subida
                    unlink("../img/" . $imagen);
                    echo "<script>
                            alert('Error al agregar la imagen: " . mysqli_error($db) . "');
                            window.location.href='galeria_home.php';
                          </script>";
                }
            } else {
                echo "<script>
                        alert('Error: " . $uploadResult['message'] . "');
                        window.location.href='galeria_home.php';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Por favor selecciona una imagen.');
                    window.location.href='galeria_home.php';
                  </script>";
        }
        break;

    case 'edit':
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $titulo = mysqli_real_escape_string($db, $_POST['titulo']);
        $categoria = mysqli_real_escape_string($db, $_POST['categoria']);
        $orden = mysqli_real_escape_string($db, $_POST['orden']);
        $estado = mysqli_real_escape_string($db, $_POST['estado']);
        $current_image = mysqli_real_escape_string($db, $_POST['current_image_name']);
        
        // Verificar si se subió una nueva imagen
        if(isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] == 0) {
            $uploadResult = uploadImage($_FILES["imagen"]);
            
            if($uploadResult['success']) {
                $nueva_imagen = $uploadResult['filename'];
                
                // Eliminar imagen anterior
                if(file_exists("../img/" . $current_image)) {
                    unlink("../img/" . $current_image);
                }
                
                $sql = "UPDATE galeria 
                        SET titulo = '$titulo', 
                            imagen = '$nueva_imagen',
                            categoria = '$categoria', 
                            orden = '$orden', 
                            estado = '$estado' 
                        WHERE id = '$id'";
            } else {
                echo "<script>
                        alert('Error: " . $uploadResult['message'] . "');
                        window.location.href='galeria_home.php';
                      </script>";
                exit();
            }
        } else {
            // No se subió nueva imagen, mantener la actual
            $sql = "UPDATE galeria 
                    SET titulo = '$titulo',
                        categoria = '$categoria', 
                        orden = '$orden', 
                        estado = '$estado' 
                    WHERE id = '$id'";
        }
        
        if (mysqli_query($db, $sql)) {
            echo "<script>
                    alert('Imagen actualizada exitosamente!');
                    window.location.href='galeria_home.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error al actualizar la imagen: " . mysqli_error($db) . "');
                    window.location.href='galeria_home.php';
                  </script>";
        }
        break;

    default:
        header('Location: galeria_home.php');
        break;
}

mysqli_close($db);
?>
