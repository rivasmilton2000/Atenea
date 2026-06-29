<?php
include('../includes/connection.php');

$query = "SELECT ACT_ID as id, ACT_NOMBRE as title, ACT_FECHA_INICIO as start, ACT_FECHA_FIN as end, ACT_COLOR as color, ACT_ESTADO as estado FROM actividades";
$result = mysqli_query($db, $query);

$events = array();
while ($row = mysqli_fetch_assoc($result)) {
    $row['estado'] = $row['estado'] == '1' ? true : false;
    $events[] = $row;
}

echo json_encode($events);
?>