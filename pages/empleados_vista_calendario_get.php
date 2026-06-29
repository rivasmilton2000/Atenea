<?php
include('../includes/connection.php');

$query = "SELECT ACT_ID as id, ACT_NOMBRE as title, ACT_FECHA_INICIO as start, ACT_FECHA_FIN as end, ACT_COLOR as color FROM actividades WHERE ACT_ESTADO = 1";
$result = mysqli_query($db, $query);

$events = array();
while ($row = mysqli_fetch_assoc($result)) {
    $events[] = $row;
}

echo json_encode($events);
?>