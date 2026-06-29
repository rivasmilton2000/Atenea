<?php
include '../includes/connection.php';

header('Content-Type: application/json');

function sendJsonResponse($success, $message, $redirectUrl = null) {
    $response = [
        'success' => $success,
        'message' => $message,
        'redirectUrl' => $redirectUrl
    ];
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Escapar los datos de entrada para prevenir inyección SQL
    $evaluacion_id = intval($_POST['evaluacion_id']);
    $alumno_id = intval($_POST['alumno_id']);
    $ed_id = intval($_POST['ed_id']);
    $observacion = mysqli_real_escape_string($db, $_POST['observacion']);

    // Verificar si se ha subido un archivo
    if (isset($_FILES['material']) && $_FILES['material']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['material'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Tipos de archivo permitidos
        $allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];

        // Verificar si el archivo tiene un tipo permitido
        if (!in_array($fileExtension, $allowedTypes)) {
            sendJsonResponse(false, "Formato de archivo no permitido.");
        }

        // Generar un nombre único para el archivo
        $uniqueFileName = uniqid() . '_' . $fileName;

        // Ruta de la carpeta donde se guardará el archivo
        $uploadPath = 'archivos_evaluaciones/';

        // Mover el archivo a la carpeta de destino
        if (move_uploaded_file($fileTmpName, $uploadPath . $uniqueFileName)) {
            // Insertar los datos de la evaluación entregada en la base de datos
            $query = "INSERT INTO ev_entregadas (evaluacion_id, alumno_id, material, observacion)
                      VALUES (?, ?, ?, ?)";

            // Usar una declaración preparada para mayor seguridad
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "iiss", $evaluacion_id, $alumno_id, $uniqueFileName, $observacion);

            if (mysqli_stmt_execute($stmt)) {
                sendJsonResponse(true, "Tu entrega se ha registrado correctamente.", "");
            } else {
                sendJsonResponse(false, "Error al registrar la entrega en la base de datos: " . mysqli_error($db));
            }
            mysqli_stmt_close($stmt);
        } else {
            sendJsonResponse(false, "Error al subir el archivo.");
        }
    } else {
        sendJsonResponse(false, "No se ha seleccionado ningún archivo.");
    }
} else {
    sendJsonResponse(false, "Acceso no autorizado.");
}

mysqli_close($db);
?>