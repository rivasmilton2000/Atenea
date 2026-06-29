<?php
include '../includes/connection.php';
include '../includes/sidebar_estudiante.php';

// 🔐 Validación de usuario
$query = 'SELECT ID, t.TYPE FROM users u 
          JOIN type t ON t.TYPE_ID=u.TYPE_ID 
          WHERE ID = ' . $_SESSION['MEMBER_ID'];

$result = mysqli_query($db, $query);

while ($rowUser = mysqli_fetch_assoc($result)) {
    $Aa = $rowUser['TYPE'];
    if ($Aa == 'Admin' || $Aa == 'Docente' || $Aa == 'Personal' || $Aa == 'SuperAdmin') {
        echo "<script>
            alert('Página restringida');
            window.location = 'index.php';
        </script>";
        exit();
    }
}

// 📌 ID asignatura
$ed_id = $_GET['id'];

// 📚 Contenidos
$query = "SELECT c.contenido_id, c.titulo, c.descripcion, c.material,
          (SELECT COUNT(*) FROM evaluaciones e 
           WHERE e.contenido_id = c.contenido_id AND e.evaluacion_estado = 1) as evaluaciones_count
          FROM contenidos c
          INNER JOIN docentes_asignaturas da ON c.da_id = da.da_id
          INNER JOIN estudiantes_docentes ed ON ed.doc_asi_id = da.da_id
          WHERE ed.ed_id = $ed_id AND c.c_estado = 1";

$result = mysqli_query($db, $query);
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Contenidos</h4>
    </div>

    <div class="card-body">

        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {

                echo '<div class="card mb-4 contenido-card">';
                echo '<div class="card-body">';

                echo '<h5 class="card-title">'.$row['titulo'].'</h5>';
                echo '<p class="card-text">'.$row['descripcion'].'</p>';

                echo '<hr>';

                // 🎥 VIDEO
                $query_video = "SELECT youtube_id 
                                FROM videos 
                                WHERE contenido_id = ".$row['contenido_id']." 
                                AND estado = 1 LIMIT 1";

                $res_video = mysqli_query($db, $query_video);

                if ($video = mysqli_fetch_assoc($res_video)) {

                    $contenidoId = $row['contenido_id'];

                    echo '
                    <button class="btn btn-primary btn-sm mb-3 toggle-btn" 
                            onclick="toggleVideo('.$contenidoId.', this)">
                        🎥 Ver video ▼
                    </button>

                    <div id="video'.$contenidoId.'" class="video-container">
                        <iframe 
                            src="https://www.youtube.com/embed/'.$video['youtube_id'].'"
                            allowfullscreen>
                        </iframe>
                    </div>
                    ';
                }

                // 🔽 BOTONES
                echo '<div class="d-flex flex-column flex-md-row justify-content-between align-items-start mt-3">';
                
                echo '<a href="estudiantes_vista_descargar.php?file='.urlencode($row['material']).'" 
                      class="btn btn-info btn-sm mb-2 mb-md-0">
                      Descargar material</a>';

                echo '<p class="text-muted mb-2 mb-md-0">
                      Evaluaciones: '.$row['evaluaciones_count'].'</p>';

                echo '<a href="estudiantes_vista_evaluaciones.php?contenido_id='.$row['contenido_id'].'&ed_id='.$ed_id.'" 
                      class="btn btn-success btn-sm">
                      Ver evaluaciones</a>';

                echo '</div>';

                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p>No hay contenidos disponibles.</p>';
        }
        ?>

    </div>
</div>

<!-- 🎨 ESTILOS PRO -->
<style>
.contenido-card {
    border-radius: 12px;
}

.video-container {
    display: none;
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    transition: all 0.3s ease;
}

.video-container iframe {
    position: absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    border-radius:10px;
}

.toggle-btn {
    transition: all 0.3s ease;
}

.toggle-btn:hover {
    transform: scale(1.05);
}
</style>

<!-- ⚡ JS PRO -->
<script>
function toggleVideo(id, btn) {
    let videoDiv = document.getElementById("video" + id);

    if (videoDiv.style.display === "none" || videoDiv.style.display === "") {
        videoDiv.style.display = "block";
        btn.innerHTML = "🎥 Ocultar video ▲";
    } else {
        videoDiv.style.display = "none";
        btn.innerHTML = "🎥 Ver video ▼";
    }
}
</script>

<?php include '../includes/footer.php'; ?>