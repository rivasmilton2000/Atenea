<?php 
include '../includes/connection.php';

$asignatura_id = $_GET['asignatura_id'];

$query = "
SELECT c.contenido_id, c.titulo
FROM contenidos c
JOIN docentes_asignaturas da ON c.da_id = da.da_id
LEFT JOIN videos v ON c.contenido_id = v.contenido_id
WHERE da.materia_id = $asignatura_id
AND v.video_id IS NULL
AND c.c_estado = 1
";

$result = mysqli_query($db, $query);

if(mysqli_num_rows($result) > 0)
    {
        echo "<option disabled selected>Seleccionar contenido</option>";
        while($row = mysqli_fetch_assoc($result))
            {
                echo "<option value='". $row['contenido_id']."'> ". $row['titulo']."</option>";
            }
    }else 
    {
        echo "<option disabled selected>Seleccionar contenido</option>";
    }
?>