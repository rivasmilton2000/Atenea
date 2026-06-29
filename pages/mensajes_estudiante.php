<?php
include '../includes/connection.php';
include '../includes/sidebar_estudiante.php';

// 🔐 Validación de acceso
$query = 'SELECT ID, t.TYPE FROM users u 
          JOIN type t ON t.TYPE_ID=u.TYPE_ID 
          WHERE ID = ' . $_SESSION['MEMBER_ID'];

$result = mysqli_query($db, $query) or die(mysqli_error($db));

while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Admin' || $Aa == 'Docente' || $Aa == 'Personal' || $Aa == 'SuperAdmin') {

        $redirectUrl = "index.php";

        if ($Aa == 'Docente') $redirectUrl = "docentes_vista.php";
        if ($Aa == 'Personal') $redirectUrl = "empleados_vista.php";
        if ($Aa == 'SuperAdmin') $redirectUrl = "sa_vista.php";

        echo "<script>
            alert('Página restringida');
            window.location = '$redirectUrl';
        </script>";
        exit();
    }
}

// 📌 Validar asignatura
if (!isset($_GET['asignatura_id'])) {
    die("Error: no se recibió asignatura_id");
}

//ID  de la materia 
$asignatura_id = $_GET['asignatura_id'];

//Conseguir el nombre de la materia
$nombre_est = "";

$stmt_est = $db->prepare("SELECT A_NAME FROM asignaturas WHERE ASIGNATURA_ID = ?");
$stmt_est->bind_param("i", $asignatura_id); 
$stmt_est->execute();
$result_est = $stmt_est->get_result();

if($row = $result_est->fetch_assoc())
    {
        $nombre_est = $row['A_NAME'];
    }
?>

<div class="card shadow mb-4">
    <div class="card-header">
        <h4>📢 Anuncios de la clase <?php echo $nombre_est; ?></h4>
    </div>

    <div class="card-body">

        <!-- CHAT -->
        <div class="chat-container">

        <?php
        $stmt = $db->prepare("
            SELECT 
                m.mensaje, 
                m.fecha, 
                m.archivo, 
                u.USERNAME
            FROM mensajes m
            JOIN users u ON u.ID = m.docente_id
            WHERE m.asignatura_id = ?
            ORDER BY m.fecha ASC
        ");

        $stmt->bind_param("i", $asignatura_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {

                echo "<div class='chat-message docente'>";
                    echo "<div class='chat-box'>";
                        echo "<strong>".$row['USERNAME']."</strong><br>";
                        echo "<small>".$row['fecha']."</small>";
                        $mensaje = $row['mensaje'];
                        // Convertir links en clickeables para el chat
                        $mensaje = preg_replace(
                            '/(https?:\/\/[^\s]+)/',
                            '<a href="$1" target="_blank">🔗 Ver enlace</a>',
                            $mensaje
                        );

                        echo "<p>".$mensaje."</p>";

                        // Detectar video de youtube
                        if(preg_match('/(youtube\.com\/watch\?v=|youtu\.be\/)([^\s]+)/', $row['mensaje'], $match)){

                            $video_id = $match[2];

                            // limpiar parámetros extra (&t=, etc)
                            $video_id = explode("&", $video_id)[0];

                            echo "
                            <div class='mt-2'>
                                <iframe width='250' height='150'
                                src='https://www.youtube.com/embed/$video_id'
                                frameborder='0' allowfullscreen
                                style='border-radius:10px;'>
                                </iframe>
                            </div>
                            ";
                        }

                        // SI HAY ARCHIVO
                        if($row['archivo'] != null)
                        {

                            $archivo = $row['archivo'];
                            $ruta = "archivos_mensajes/" . $archivo;

                            $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));

                            // 🖼️ IMÁGENES
                            if(in_array($ext, ['jpg','jpeg','png','gif','webp'])){

                            echo "
                            <div class='mt-2'>
                                <img src='$ruta' class='img-fluid rounded shadow' style='max-width:200px; cursor:pointer;' onclick='verImagen(\"$ruta\")'>
                            </div>
                            ";

                            } else {

                            // 📄 OTROS ARCHIVOS
                            echo "
                            <div class='mt-2'>
                                <a href='$ruta' download class='btn btn-sm btn-outline-primary'>
                                    📎 Descargar archivo
                                </a>
                            </div>
                            ";
                            }
                        }
                    
                    echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p class='text-muted'>No hay anuncios aún.</p>";
        }
        ?>

        </div>

    </div>
</div>

<!-- 🎨 ESTILOS -->
<style>
.chat-container {
    max-height: 400px;
    overflow-y: auto;
    padding: 15px;
    background: #f8f9fc;
    border-radius: 10px;
}

.chat-message {
    display: flex;
    margin-bottom: 10px;
}

.chat-message.docente {
    justify-content: flex-start;
}

.chat-box {
    max-width: 70%;
    padding: 10px;
    border-radius: 12px;
    background: #28a745;
    color: white;
}

.chat-box small {
    font-size: 11px;
    opacity: 0.8;
}

.chat-box p {
    margin: 5px 0 0 0;
}
</style>

<!-- 🔽 AUTO SCROLL -->
<script>
const chat = document.querySelector('.chat-container');
if(chat){
    chat.scrollTop = chat.scrollHeight;
}
</script>
<!-- MODAL IMAGEN -->
<div id="modalImagen" style="
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.8);
    justify-content:center;
    align-items:center;
    z-index:9999;
">
    <img id="imgGrande" style="max-width:90%; max-height:90%; border-radius:10px;">
</div>

<script>
function verImagen(src){
    document.getElementById("modalImagen").style.display = "flex";
    document.getElementById("imgGrande").src = src;
}

// cerrar al hacer click
document.getElementById("modalImagen").addEventListener("click", function(){
    this.style.display = "none";
});
</script>

<?php include '../includes/footer.php'; ?>