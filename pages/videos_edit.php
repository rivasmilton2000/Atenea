<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';

$id = $_GET['id'];

$query = "SELECT * FROM videos WHERE video_id = $id";
$result = mysqli_query($db, $query);
$row = mysqli_fetch_assoc($result);
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">
            Editar Video
        </h4>
    </div>

    <div class="card-body">
        <form method="post" action="videos_update.php">

            <input type="hidden" name="video_id" value="<?php echo $row['video_id']; ?>">

            <!-- TITULO -->
            <div class="form-group">
                <label>Título</label>
                <input type="text" name="titulo" class="form-control"
                    value="<?php echo $row['titulo']; ?>" required>
            </div>

            <!-- DESCRIPCION -->
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"><?php echo $row['descripcion']; ?></textarea>
            </div>

            <!-- LINK YOUTUBE -->
            <div class="form-group">
                <label>Link de YouTube</label>
                <input type="text" name="youtube_url" class="form-control"
                    value="https://www.youtube.com/watch?v=<?php echo $row['youtube_id']; ?>" required>
            </div>

            <!-- VIDEO PREVIEW -->
            <div class="form-group">
                <label>Vista previa</label>
                <div style="position: relative; padding-bottom: 56.25%; height: 0;">
                    <iframe 
                        src="https://www.youtube.com/embed/<?php echo $row['youtube_id']; ?>"
                        style="position:absolute; top:0; left:0; width:100%; height:100%; border-radius:10px;"
                        frameborder="0"
                        allowfullscreen>
                    </iframe>
                </div>
            </div>

            <!-- BOTONES -->
            <div class="d-flex justify-content-between">
                <a href="videos_admin.php" class="btn btn-secondary">
                    ← Volver
                </a>

                <button type="submit" class="btn btn-success">
                    💾 Actualizar Video
                </button>
            </div>

        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>