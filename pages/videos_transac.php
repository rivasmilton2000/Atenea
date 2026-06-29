<?php 
include '../includes/connection.php';

if($_GET['action'] === 'add')
    {
        $contenido_id = $_POST['contenido_id'];
        $titulo = $_POST['titulo'];
        $descripcion = $_POST['descripcion'];
        $youtube_url = $_POST['youtube_url'];

        //Funcion video de youtube 
     function getYoutubeId($url) {
    // Caso normal: youtube.com/watch?v=
    parse_str(parse_url($url, PHP_URL_QUERY), $params);
    if (isset($params['v'])) {
        return $params['v'];
    }

    // Caso corto: youtu.be/ID
    if (preg_match("#youtu\.be/([^\?]+)#", $url, $matches)) {
        return $matches[1];
    }

    // Caso embed
    if (preg_match("#youtube\.com/embed/([^\?]+)#", $url, $matches)) {
        return $matches[1];
    }

    return null;
}
        $youtube_id = getYoutubeId($youtube_url);
        if(!$youtube_id)
            {
                die("Link de YouTube inválido");
            }
        
            $stmt = $db->prepare("INSERT INTO videos (contenido_id, titulo, descripcion, youtube_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $contenido_id, $titulo, $descripcion, $youtube_id);
            if($stmt->execute())
                {
                    echo "<script>
                         alert('Video agregado correctamente');
                         window.location.href='videos_admin.php';
                         </script>";
                }else
                {
                    echo "Error: " . $stmt->error;
                }
                $stmt->close();
    }
    mysqli_close($db);
?>