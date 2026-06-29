<?php
include '../includes/connection.php';

$asignatura_id = $_POST['asignatura_id'];
$docente_id = $_POST['docente_id'];
$mensaje = $_POST['mensaje'];

$archivo_nombre = null;

//  SUBIDA DE ARCHIVO
if(isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0){

    $carpeta = "archivos_mensajes/";
    
    // Crear carpeta si no existe
    if(!file_exists($carpeta)){
        mkdir($carpeta, 0777, true);
    }

    $nombreOriginal = $_FILES['archivo']['name'];
    $tmp = $_FILES['archivo']['tmp_name'];

    // VALIDACIÓN DE EXTENSIÓN
    $permitidos = ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','jpg','jpeg','png','gif','webp'];

    $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

    if(!in_array($ext, $permitidos)){
        die("Archivo no permitido");
    }

    // VALIDACION DE TAMAÑO DEL ARCHIVO
    if($_FILES['archivo']['size'] > 5000000){ // 5MB
        die("El archivo es muy grande");
    }

    // Evitar nombres duplicados
    $archivo_nombre = time() . "_" . $nombreOriginal;

    move_uploaded_file($tmp, $carpeta . $archivo_nombre);
}

//  INSERT
$stmt = $db->prepare("
    INSERT INTO mensajes (asignatura_id, docente_id, mensaje, archivo)
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param("iiss", $asignatura_id, $docente_id, $mensaje, $archivo_nombre);

if($stmt->execute()){
    header("Location: mensajes_docente.php?asignatura_id=".$asignatura_id);
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
?>