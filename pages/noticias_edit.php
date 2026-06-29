<?php 
include '../includes/connection.php';
include '../includes/sidebar_admin.php'; 

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa=='Personal' || $Aa=='Estudiante' || $Aa=='Docente' || $Aa=='SuperAdmin'){
        if ($Aa=='Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa=='Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa=='Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa=='SuperAdmin') {
            $redirectUrl = "sa_vista.php";
        }
        ?>
        <script type="text/javascript">
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit();
    }
}

// Obtener ID de la noticia
$noticia_id = isset($_GET['id']) ? mysqli_real_escape_string($db, $_GET['id']) : 0;

// Consultar la noticia
$query = "SELECT * FROM noticias WHERE id = '$noticia_id'";
$result = mysqli_query($db, $query) or die(mysqli_error($db));

if (mysqli_num_rows($result) == 0) {
    header('Location: noticias_admin.php');
    exit();
}

$noticia = mysqli_fetch_assoc($result);
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Editar Noticia
            <a href="noticias_admin.php" type="button" class="btn btn-secondary bg-gradient-secondary float-right" style="border-radius: 0px;">
                <i class="fas fa-fw fa-arrow-left"></i> Volver
            </a>
        </h4>
    </div>

    <div class="card-body">
        <form role="form" method="post" action="noticias_transac.php?action=edit" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $noticia['id']; ?>">
            <input type="hidden" name="current_image" value="<?php echo $noticia['imagen']; ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Título</label>
                        <input class="form-control" placeholder="Título de la noticia" name="titulo" value="<?php echo htmlspecialchars($noticia['titulo']); ?>" required maxlength="150">
                    </div>
                    
                    <div class="form-group">
                        <label>Descripción Corta</label>
                        <textarea class="form-control" placeholder="Descripción breve para vista previa" name="descripcion_corta" rows="3" required maxlength="250"><?php echo htmlspecialchars($noticia['descripcion_corta']); ?></textarea>
                        <small class="form-text text-muted">Máximo 250 caracteres</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Descripción Completa</label>
                        <textarea class="form-control" placeholder="Contenido completo de la noticia" name="descripcion_completa" rows="8" required><?php echo htmlspecialchars($noticia['descripcion_completa']); ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Imagen Actual</label>
                        <div class="mb-3">
                            <img src="../img/<?php echo $noticia['imagen']; ?>" alt="<?php echo $noticia['titulo']; ?>" class="img-fluid rounded">
                        </div>
                        <label>Cambiar Imagen (opcional)</label>
                        <input type="file" class="form-control-file" name="imagen" accept="image/*">
                        <small class="form-text text-muted">Deja en blanco si no deseas cambiar la imagen</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Fecha de Publicación</label>
                        <input type="date" class="form-control" name="fecha_publicacion" value="<?php echo $noticia['fecha_publicacion']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Estado</label>
                        <select class="form-control" name="estado" required>
                            <option value="1" <?php echo $noticia['estado'] == 1 ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo $noticia['estado'] == 0 ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <hr>
            <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Actualizar</button>
            <a href="noticias_admin.php" class="btn btn-danger"><i class="fa fa-times fa-fw"></i>Cancelar</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>