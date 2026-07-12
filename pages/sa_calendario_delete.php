<?php
include('../includes/connection.php');

if(isset($_GET['id'])) {
    $act_id = mysqli_real_escape_string($db, $_GET['id']);
    
    $query = "DELETE FROM actividades WHERE ACT_ID = '$act_id'";
    
    if(mysqli_query($db, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($db)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No se proporcionó ID']);
}
?>