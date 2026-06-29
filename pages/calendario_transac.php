<?php
include('../includes/connection.php');

if ($_GET['action'] == 'add') {
    $act_nombre = mysqli_real_escape_string($db, $_POST['act_nombre']);
    $act_fecha_inicio = mysqli_real_escape_string($db, $_POST['act_fecha_inicio']);
    $act_fecha_fin = mysqli_real_escape_string($db, $_POST['act_fecha_fin']);
    $act_color = mysqli_real_escape_string($db, $_POST['act_color']);
    $ignoreWarning = isset($_POST['ignoreWarning']) && $_POST['ignoreWarning'] == 'true';

    // Validación: Nombre duplicado
    $query = "SELECT * FROM actividades WHERE act_nombre = '$act_nombre'";
    $result = mysqli_query($db, $query);
    
    if (mysqli_num_rows($result) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe una actividad con ese nombre.'
        ]);
        exit();
    }
    
    // Validación: Fechas
    $currentDate = date('Y-m-d H:i:s');
    if (!$ignoreWarning && $act_fecha_inicio < $currentDate) {
        // La fecha de inicio ya pasó, advertencia
        echo json_encode([
            'success' => null,
            'message' => 'La fecha de inicio ya pasó. ¿Estás seguro de que deseas añadir esta actividad?',
            'data' => [
                'act_nombre' => $act_nombre,
                'act_fecha_inicio' => $act_fecha_inicio,
                'act_fecha_fin' => $act_fecha_fin,
                'act_color' => $act_color
            ]
        ]);
        exit();
    }
    
    if ($act_fecha_fin <= $act_fecha_inicio) {
        echo json_encode([
            'success' => false,
            'message' => 'La fecha de fin debe ser posterior a la fecha de inicio.'
        ]);
        exit();
    }

    // Si pasa todas las validaciones o ignora la advertencia, insertar la actividad
    $query = "INSERT INTO actividades (act_nombre, act_fecha_inicio, act_fecha_fin, act_color, act_estado) 
              VALUES ('$act_nombre', '$act_fecha_inicio', '$act_fecha_fin', '$act_color', 1)";
    
    if (mysqli_query($db, $query)) {
        echo json_encode([
            'success' => true,
            'message' => 'Actividad añadida exitosamente.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al añadir la actividad. Por favor, inténtalo nuevamente.'
        ]);
    }
    exit();
}

if ($_GET['action'] == 'update') {
    $act_id = mysqli_real_escape_string($db, $_POST['act_id']);
    $act_nombre = mysqli_real_escape_string($db, $_POST['act_nombre']);
    $act_fecha_inicio = mysqli_real_escape_string($db, $_POST['act_fecha_inicio']);
    $act_fecha_fin = mysqli_real_escape_string($db, $_POST['act_fecha_fin']);
    $act_color = mysqli_real_escape_string($db, $_POST['act_color']);

    // Validación: Nombre duplicado (excepto el actual)
    $query = "SELECT * FROM actividades WHERE act_nombre = '$act_nombre' AND act_id != '$act_id'";
    $result = mysqli_query($db, $query);
    
    if (mysqli_num_rows($result) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe una actividad con ese nombre.'
        ]);
        exit();
    }

    // Validación: Fechas
    $currentDate = date('Y-m-d H:i:s');
    if ($act_fecha_inicio < $currentDate) {
        echo json_encode([
            'success' => null,
            'message' => 'La fecha de inicio ya pasó. ¿Estás seguro de que deseas actualizar esta actividad?',
            'data' => [
                'act_id' => $act_id,
                'act_nombre' => $act_nombre,
                'act_fecha_inicio' => $act_fecha_inicio,
                'act_fecha_fin' => $act_fecha_fin,
                'act_color' => $act_color
            ]
        ]);
        exit();
    }
    
    if ($act_fecha_fin <= $act_fecha_inicio) {
        echo json_encode([
            'success' => false,
            'message' => 'La fecha de fin debe ser posterior a la fecha de inicio.'
        ]);
        exit();
    }

    // Si pasa todas las validaciones, actualizar la actividad
    $query = "UPDATE actividades SET 
              act_nombre = '$act_nombre', 
              act_fecha_inicio = '$act_fecha_inicio', 
              act_fecha_fin = '$act_fecha_fin', 
              act_color = '$act_color'
              WHERE act_id = '$act_id'";
    
    if (mysqli_query($db, $query)) {
        echo json_encode([
            'success' => true,
            'message' => 'Actividad actualizada exitosamente.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar la actividad. Por favor, inténtalo nuevamente.'
        ]);
    }
    exit();
}
?>
