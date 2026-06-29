<?php
include '../includes/connection.php';
session_start();

$action = $_GET['action'];

// Función para subir imagen
function uploadImage($file) {
    $target_dir = "../img/";
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = 'producto_' . uniqid() . '_' . time() . '.' . $imageFileType;
    $target_file = $target_dir . $newFileName;
    
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ['success' => false, 'message' => 'El archivo no es una imagen.'];
    }
    
    if ($file["size"] > 2000000) {
        return ['success' => false, 'message' => 'El archivo es muy grande. Máximo 2MB.'];
    }
    
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return ['success' => false, 'message' => 'Solo se permiten archivos JPG, JPEG, PNG y GIF.'];
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'filename' => $newFileName];
    } else {
        return ['success' => false, 'message' => 'Error al subir el archivo.'];
    }
}

switch ($action) {
    case 'add':
        $nombre = mysqli_real_escape_string($db, $_POST['nombre']);
        $descripcion_corta = mysqli_real_escape_string($db, $_POST['descripcion_corta']);
        $descripcion = mysqli_real_escape_string($db, $_POST['descripcion']);
        $precio = mysqli_real_escape_string($db, $_POST['precio']);
        $precio_descuento = !empty($_POST['precio_descuento']) ? mysqli_real_escape_string($db, $_POST['precio_descuento']) : NULL;
        $categoria_id = mysqli_real_escape_string($db, $_POST['categoria_id']);
        $stock = mysqli_real_escape_string($db, $_POST['stock']);
        $destacado = mysqli_real_escape_string($db, $_POST['destacado']);
        $estado = mysqli_real_escape_string($db, $_POST['estado']);
        
        // Subir imagen
        if(isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] == 0) {
            $uploadResult = uploadImage($_FILES["imagen"]);
            
            if($uploadResult['success']) {
                $imagen = $uploadResult['filename'];
                
                $precio_descuento_sql = $precio_descuento ? "'$precio_descuento'" : "NULL";
                
                $sql = "INSERT INTO productos (nombre, descripcion_corta, descripcion, precio, precio_descuento, imagen, categoria_id, stock, destacado, estado) 
                        VALUES ('$nombre', '$descripcion_corta', '$descripcion', '$precio', $precio_descuento_sql, '$imagen', '$categoria_id', '$stock', '$destacado', '$estado')";
                
                if (mysqli_query($db, $sql)) {
                    echo "<script>
                            alert('Producto agregado exitosamente!');
                            window.location.href='productos_admin.php';
                          </script>";
                } else {
                    unlink("../img/" . $imagen);
                    echo "<script>
                            alert('Error al agregar el producto: " . mysqli_error($db) . "');
                            window.location.href='productos_add.php';
                          </script>";
                }
            } else {
                echo "<script>
                        alert('Error: " . $uploadResult['message'] . "');
                        window.location.href='productos_add.php';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Por favor selecciona una imagen.');
                    window.location.href='productos_add.php';
                  </script>";
        }
        break;

    case 'edit':
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $nombre = mysqli_real_escape_string($db, $_POST['nombre']);
        $descripcion_corta = mysqli_real_escape_string($db, $_POST['descripcion_corta']);
        $descripcion = mysqli_real_escape_string($db, $_POST['descripcion']);
        $precio = mysqli_real_escape_string($db, $_POST['precio']);
        $precio_descuento = !empty($_POST['precio_descuento']) ? mysqli_real_escape_string($db, $_POST['precio_descuento']) : NULL;
        $categoria_id = mysqli_real_escape_string($db, $_POST['categoria_id']);
        $stock = mysqli_real_escape_string($db, $_POST['stock']);
        $destacado = mysqli_real_escape_string($db, $_POST['destacado']);
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
                
                $precio_descuento_sql = $precio_descuento ? "'$precio_descuento'" : "NULL";
                
                $sql = "UPDATE productos 
                        SET nombre = '$nombre', 
                            descripcion_corta = '$descripcion_corta',
                            descripcion = '$descripcion',
                            precio = '$precio',
                            precio_descuento = $precio_descuento_sql,
                            imagen = '$nueva_imagen',
                            categoria_id = '$categoria_id',
                            stock = '$stock',
                            destacado = '$destacado',
                            estado = '$estado' 
                        WHERE id = '$id'";
            } else {
                echo "<script>
                        alert('Error: " . $uploadResult['message'] . "');
                        window.location.href='productos_edit.php?id=$id';
                      </script>";
                exit();
            }
        } else {
            // No se subió nueva imagen
            $precio_descuento_sql = $precio_descuento ? "'$precio_descuento'" : "NULL";
            
            $sql = "UPDATE productos 
                    SET nombre = '$nombre', 
                        descripcion_corta = '$descripcion_corta',
                        descripcion = '$descripcion',
                        precio = '$precio',
                        precio_descuento = $precio_descuento_sql,
                        categoria_id = '$categoria_id',
                        stock = '$stock',
                        destacado = '$destacado',
                        estado = '$estado' 
                    WHERE id = '$id'";
        }
        
        if (mysqli_query($db, $sql)) {
            echo "<script>
                    alert('Producto actualizado exitosamente!');
                    window.location.href='productos_admin.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error al actualizar el producto: " . mysqli_error($db) . "');
                    window.location.href='productos_edit.php?id=$id';
                  </script>";
        }
        break;

    default:
        header('Location: productos_admin.php');
        break;
}

mysqli_close($db);
?>