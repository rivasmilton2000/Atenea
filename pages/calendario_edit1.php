<?php
include '../includes/connection.php';

if ($_GET['action'] == 'update') {
    $act_id = mysqli_real_escape_string($db, $_POST['act_id']);
    $act_nombre = mysqli_real_escape_string($db, $_POST['act_nombre']);
    $act_fecha_inicio = mysqli_real_escape_string($db, $_POST['act_fecha_inicio']);
    $act_fecha_fin = mysqli_real_escape_string($db, $_POST['act_fecha_fin']);
    $act_color = mysqli_real_escape_string($db, $_POST['act_color']);
    // No se incluye $act_estado ya que no se manipula

    $ignoreWarning = isset($_POST['ignoreWarning']) && $_POST['ignoreWarning'] == 'true';

    // Obtener los datos actuales de la actividad
    $query = "SELECT * FROM actividades WHERE ACT_ID = '$act_id'";
    $result = mysqli_query($db, $query);
    $actividadActual = mysqli_fetch_assoc($result);

    // Verificar si se ha realizado algún cambio
    $changes = ($act_nombre != $actividadActual['ACT_NOMBRE'] ||
                $act_fecha_inicio != $actividadActual['ACT_FECHA_INICIO'] ||
                $act_fecha_fin != $actividadActual['ACT_FECHA_FIN'] ||
                $act_color != $actividadActual['ACT_COLOR']);

    if (!$changes) {
        echo json_encode([
            'success' => false,
            'message' => 'No se han realizado cambios en la actividad.'
        ]);
        exit();
    }

    // Validación: Nombre duplicado (excluyendo la actividad actual)
    $query = "SELECT * FROM actividades WHERE act_nombre = '$act_nombre' AND ACT_ID != '$act_id'";
    $result = mysqli_query($db, $query);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe otra actividad con ese nombre.'
        ]);
        exit();
    }

    // Validación: Fechas
    $currentDate = date('Y-m-d H:i:s');
    if (!$ignoreWarning && $act_fecha_inicio < $currentDate) {
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

    // Actualizar la actividad
    $query = "UPDATE actividades SET
                  act_nombre = '$act_nombre',
                  act_fecha_inicio = '$act_fecha_inicio',
                  act_fecha_fin = '$act_fecha_fin',
                  act_color = '$act_color'
              WHERE ACT_ID = '$act_id'";

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
