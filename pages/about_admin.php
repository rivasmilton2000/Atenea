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
 
// Consultar información actual de About
$query = "SELECT * FROM about LIMIT 1";
$result = mysqli_query($db, $query) or die(mysqli_error($db));
$about = mysqli_fetch_assoc($result);
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Editar Sección "Sobre Nosotros"</h4>
    </div>

    <div class="card-body">
        <form role="form" method="post" action="about_transac.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $about['id']; ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label><strong>Título Principal</strong></label>
                        <input class="form-control" name="titulo" value="<?php echo htmlspecialchars($about['titulo']); ?>" required maxlength="150">
                        <small class="form-text text-muted">Aparece en la página About</small>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Descripción Corta</strong> (Para el Index)</label>
                        <textarea class="form-control" name="descripcion_corta" rows="4" required><?php echo htmlspecialchars($about['descripcion_corta']); ?></textarea>
                        <small class="form-text text-muted">Esta descripción aparece en la página de inicio</small>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Descripción Completa</strong> (Para la página About)</label>
                        <textarea class="form-control" name="descripcion" rows="10" required><?php echo htmlspecialchars($about['descripcion']); ?></textarea>
                        <small class="form-text text-muted">Esta descripción aparece en la página "Sobre Nosotros"</small>
                    </div>
                    
                    <hr>
                    <h5 class="mb-3">Características (Aparecen en el Index)</h5>
                    
                    <div class="form-group">
                        <label>Característica 1</label>
                        <input class="form-control" name="caracteristica1" value="<?php echo htmlspecialchars($about['caracteristica1']); ?>" required maxlength="150">
                    </div>
                    
                    <div class="form-group">
                        <label>Característica 2</label>
                        <input class="form-control" name="caracteristica2" value="<?php echo htmlspecialchars($about['caracteristica2']); ?>" required maxlength="150">
                    </div>
                    
                    <div class="form-group">
                        <label>Característica 3</label>
                        <input class="form-control" name="caracteristica3" value="<?php echo htmlspecialchars($about['caracteristica3']); ?>" required maxlength="150">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <h5 class="mb-3">Imágenes</h5>
                    
                    <!-- Imagen 1 (Index - Principal) -->
                    <div class="form-group">
                        <label><strong>Imagen 1</strong> (Index - Principal)</label>
                        <div class="mb-2">
                            <img src="../img/<?php echo $about['imagen']; ?>" alt="Imagen 1" class="img-fluid rounded" style="max-height: 150px;">
                        </div>
                        <input type="file" class="form-control-file" name="imagen" accept="image/*">
                        <input type="hidden" name="current_imagen" value="<?php echo $about['imagen']; ?>">
                        <small class="form-text text-muted">Deja en blanco para mantener la actual</small>
                    </div>
                    
                    <hr>
                    
                    <!-- Imagen 2 (About - Principal) -->
                    <div class="form-group">
                        <label><strong>Imagen 2</strong> (About - Principal)</label>
                        <div class="mb-2">
                            <img src="../img/<?php echo $about['imagen2']; ?>" alt="Imagen 2" class="img-fluid rounded" style="max-height: 150px;">
                        </div>
                        <input type="file" class="form-control-file" name="imagen2" accept="image/*">
                        <input type="hidden" name="current_imagen2" value="<?php echo $about['imagen2']; ?>">
                        <small class="form-text text-muted">Deja en blanco para mantener la actual</small>
                    </div>
                    
                    <hr>
                    
                    <!-- Imagen 3 (Index - Pequeña) -->
                    <div class="form-group">
                        <label><strong>Imagen 3</strong> (Index - Características)</label>
                        <div class="mb-2">
                            <img src="../img/<?php echo $about['imagen3']; ?>" alt="Imagen 3" class="img-fluid rounded" style="max-height: 150px;">
                        </div>
                        <input type="file" class="form-control-file" name="imagen3" accept="image/*">
                        <input type="hidden" name="current_imagen3" value="<?php echo $about['imagen3']; ?>">
                        <small class="form-text text-muted">Deja en blanco para mantener la actual</small>
                    </div>
                    
                    <hr>
                    
                    <div class="form-group">
                        <label><strong>Estado</strong></label>
                        <select class="form-control" name="estado" required>
                            <option value="1" <?php echo $about['estado'] == 1 ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo $about['estado'] == 0 ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <hr>
            <button type="submit" class="btn btn-success btn-lg"><i class="fa fa-check fa-fw"></i>Actualizar Información</button>
            <button type="reset" class="btn btn-warning btn-lg"><i class="fa fa-undo fa-fw"></i>Restablecer</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>