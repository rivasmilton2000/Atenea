<?php
include '../includes/connection.php';

$id = $_POST['video_id'];
$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$url = $_POST['youtube_url'];

// función igual que antes
function getYoutubeId($url) {
    parse_str(parse_url($url, PHP_URL_QUERY), $params);
    if (isset($params['v'])) return $params['v'];

    if (preg_match("#youtu\.be/([^\?]+)#", $url, $m)) return $m[1];

    return null;
}

$youtube_id = getYoutubeId($url);

$query = "UPDATE videos 
          SET titulo='$titulo', descripcion='$descripcion', youtube_id='$youtube_id'
          WHERE video_id=$id";

if(mysqli_query($db, $query)) {
    echo "<script>
        alert('Video actualizado');
        window.location='videos_admin.php';
    </script>";
}
?>