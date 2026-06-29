<?php
include '../../includes/connection.php';

if (isset($_GET['province'])) {
    $province = $_GET['province'];
    $query = "SELECT LOCATION_ID, CITY FROM location WHERE PROVINCE = ? ORDER BY CITY";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $province);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $cities = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $cities[] = $row;
    }

    echo json_encode($cities);
}
?>