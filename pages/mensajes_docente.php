<?php
include '../includes/connection.php';
include '../includes/sidebar_docente.php';

// 🔐 Validación de acceso
$query = 'SELECT ID, t.TYPE FROM users u 
          JOIN type t ON t.TYPE_ID=u.TYPE_ID 
          WHERE ID = ' . $_SESSION['MEMBER_ID'];

$result = mysqli_query($db, $query) or die(mysqli_error($db));

while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Admin' || $Aa == 'Estudiante' || $Aa == 'Personal' || $Aa == 'SuperAdmin') {

        $redirectUrl = "index.php";

        if ($Aa == 'Estudiante') $redirectUrl = "estudiante_vista.php";
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

$asignatura_id = $_GET['asignatura_id'];
$user_id = $_SESSION['MEMBER_ID'];

$nombre_asignatura = "";

$stmt_nombre = $db->prepare(
    "SELECT A_NAME FROM asignaturas WHERE ASIGNATURA_ID = ?"
);

$stmt_nombre->bind_param("i", $asignatura_id);
$stmt_nombre->execute();
$result_nombre = $stmt_nombre->get_result();

if($row_nombre = $result_nombre->fetch_assoc())
    {
        $nombre_asignatura = $row_nombre['A_NAME'];
    }
?>

<div class="card shadow mb-4">
    <div class="card-header">
        <h4>📢 Anuncios de la clase de <?php echo $nombre_asignatura; ?></h4>
    </div>

    <div class="card-body">

        <!-- 🔥 CHAT -->
        <div class="chat-container mb-3">

        <?php
        $stmt = $db->prepare("
            SELECT 
                m.mensaje, 
                m.fecha, m.archivo,
                u.USERNAME
            FROM mensajes m
            JOIN users u ON u.ID = m.docente_id
            WHERE m.asignatura_id = ?
            ORDER BY m.fecha ASC
        ");

        $stmt->bind_param("i", $asignatura_id);
        $stmt->execute();
        $result = $stmt->get_result();

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

                            // limpieza de parametros extra
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
        ?>

        </div>

        <!-- ✏️ FORMULARIO -->
        <form method="post" action="mensajes_transac.php" enctype="multipart/form-data">
    
        <input type="hidden" name="asignatura_id" value="<?php echo $asignatura_id; ?>">
        <input type="hidden" name="docente_id" value="<?php echo $_SESSION['MEMBER_ID']; ?>">

        <div class="form-group">
            <textarea name="mensaje" class="form-control" placeholder="Escribe un mensaje..."></textarea>
        </div>

        <div class="form-group">
            <input type="file" name="archivo" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Enviar</button>
        </form>

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
    justify-content: flex-end;
}

.chat-box {
    max-width: 75%;
    padding: 10px;
    border-radius: 15px;
    background: #007bff;
    color: white;
    word-wrap: break-word;
}

.chat-box small {
    font-size: 11px;
    opacity: 0.8;
}

.chat-box p {
    margin: 5px 0 0 0;
}

.chat-container {
    max-height: 400px;
    overflow-y: auto;
    background: #f5f5f5;
    padding: 10px;
    border-radius: 10px;
}

/* 📱 MÓVIL */
@media (max-width: 768px) {

    .chat-container {
        max-height: 300px;
    }

    .card {
        margin: 10px;
    }

    textarea {
        font-size: 14px;
    }

    .btn {
        width: 100%;
    }
}
</style>


<!-- 🔽 AUTO SCROLL -->
<script>
const chat = document.querySelector('.chat-container');
if(chat){
    chat.scrollTop = chat.scrollHeight;
}
</script>

<?php include '../includes/footer.php'; ?>