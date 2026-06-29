<?php 
include '../includes/connection.php';

$id = $_GET['id'];

$query = "DELETE FROM videos WHERE video_id = $id";

if(mysqli_query($db, $query))
    {
        echo "<script>
        alert('Video eliminado correctamente');
        window.location='videos_admin.php';
        </script>";
    }else {
    echo "Error: " . mysqli_error($db);
    }
?>