<?php
// Establecer la zona horaria de El Salvador
date_default_timezone_set('America/El_Salvador');

// Obtén la conexión a la base de datos y otros archivos necesarios
require_once '../includes/connection.php';

// Obtener el ID del trabajo desde el formulario
$jobId = $_POST['id'];

// Obtener el nuevo valor del campo "status"
$status = $_POST['status'];

// Obtener la hora y la fecha actuales
$hour = $_POST['hour'];
$date = $_POST['date'];

// Obtener la hora máxima permitida para la tarea actual
$queryMaxHour = "SELECT maxhour, maxdate FROM jobs WHERE id = '$jobId'";
$resultMaxHour = mysqli_query($db, $queryMaxHour);

if ($resultMaxHour && mysqli_num_rows($resultMaxHour) > 0) {
    $row = mysqli_fetch_assoc($resultMaxHour);
    $maxHour = $row['maxhour'];
    $maxDate = $row['maxdate'];

    // Crear objetos DateTime para comparación
    $currentTime = new DateTime("$date $hour");
    $maxTime = new DateTime("$maxDate $maxHour");

    // Comprobar si se excedió la hora máxima
    if ($currentTime > $maxTime) {
        $status = 'Tiempo Excedido';
    } else {
        $status = 'Completado';
    }

    // Obtener la hora y fecha actuales de El Salvador en el formato requerido
    $currentDateTime = new DateTime("now");
    $hour = $currentDateTime->format('H:i');
    $date = $currentDateTime->format('Y-m-d');

    // Realizar la actualización en la base de datos
    $queryUpdate = "UPDATE jobs SET status = '$status', hour = '$hour', date = '$date' WHERE id = '$jobId'";
    $resultUpdate = mysqli_query($db, $queryUpdate);

    // Verificar si la actualización fue exitosa
    if ($resultUpdate) {
        // Redirigir al usuario a empleados_vista_labores.php
        header("Location: empleados_vista_labores.php");
        exit(); // Asegúrate de salir después de redirigir para evitar problemas
    } else {
        echo "Error al actualizar el estado del trabajo.";
    }
} else {
    echo "Error al obtener la hora máxima del trabajo.";
}
?>