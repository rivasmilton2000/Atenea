<?php 
include '../includes/connection.php';
require_once '../includes/atenea_catalog.php';
include '../includes/sidebar_admin.php'; 

// Verificar permisos
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

// Obtener ID del producto
$producto_id = isset($_GET['id']) ? mysqli_real_escape_string($db, $_GET['id']) : 0;

// Consultar el producto
$query = "SELECT * FROM productos WHERE id = '$producto_id'";
$result = mysqli_query($db, $query) or die(mysqli_error($db));

if (mysqli_num_rows($result) == 0) {
    header('Location: productos_admin.php');
    exit();
}

$producto = mysqli_fetch_assoc($result);

// Obtener categorías
$sql_categorias = "SELECT * FROM categorias_productos WHERE estado = 1 ORDER BY nombre";
$resultado_categorias = mysqli_query($db, $sql_categorias);
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Editar elemento del catálogo
            <a href="productos_admin.php" type="button" class="btn btn-secondary bg-gradient-secondary float-right" style="border-radius: 0px;">
                <i class="fas fa-fw fa-arrow-left"></i> Volver
            </a>
        </h4>
    </div>

    <div class="card-body">
        <form role="form" method="post" action="productos_transac.php?action=edit" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
            <input type="hidden" name="current_image" value="<?php echo $producto['imagen']; ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label><strong>Nombre</strong></label>
                        <input class="form-control" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required maxlength="150">
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Descripción Corta</strong></label>
                        <textarea class="form-control" name="descripcion_corta" rows="2" required maxlength="250"><?php echo htmlspecialchars($producto['descripcion_corta']); ?></textarea>
                        <small class="form-text text-muted">Máximo 250 caracteres (aparece en tarjetas)</small>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Descripción Completa</strong></label>
                        <textarea class="form-control" name="descripcion" rows="6" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Precio Regular</strong></label>
                                <input type="number" step="0.01" class="form-control" name="precio" value="<?php echo $producto['precio']; ?>" required min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Precio con Descuento</strong> (opcional)</label>
                                <input type="number" step="0.01" class="form-control" name="precio_descuento" value="<?php echo $producto['precio_descuento']; ?>" min="0">
                                <small class="form-text text-muted">Dejar vacío si no hay descuento</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label><strong>Tipo de oferta</strong></label>
                        <select class="form-control" name="tipo_oferta">
                            <?php foreach (atenea_catalog_type_options() as $value => $label) : ?>
                                <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($producto['tipo_oferta'] ?? 'producto') === $value ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><strong>Imagen Actual</strong></label>
                        <div class="mb-3">
                            <img src="../img/<?php echo $producto['imagen']; ?>" alt="" class="img-fluid rounded">
                        </div>
                        <label><strong>Cambiar Imagen</strong> (opcional)</label>
                        <input type="file" class="form-control-file" name="imagen" accept="image/*">
                        <small class="form-text text-muted">Dejar vacío para mantener la actual</small>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Categoría</strong></label>
                        <select class="form-control" name="categoria_id" required>
                            <?php while ($cat = mysqli_fetch_assoc($resultado_categorias)) : ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $producto['categoria_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo $cat['nombre']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Stock / cupos</strong></label>
                        <input type="number" class="form-control" name="stock" value="<?php echo $producto['stock']; ?>" required min="0">
                    </div>

                    <div class="form-group">
                        <label><strong>Duración</strong> (opcional)</label>
                        <input type="text" class="form-control" name="duracion" maxlength="120" value="<?php echo htmlspecialchars((string) ($producto['duracion'] ?? '')); ?>" placeholder="Ej: 8 horas / 4 semanas">
                    </div>

                    <div class="form-group">
                        <label><strong>Video de YouTube</strong> (opcional)</label>
                        <input type="url" class="form-control" name="video_url" maxlength="255" value="<?php echo htmlspecialchars((string) ($producto['video_url'] ?? '')); ?>" placeholder="https://www.youtube.com/watch?v=...">
                    </div>

                    <div class="form-group">
                        <label><strong>Mostrar video en vista pública</strong></label>
                        <select class="form-control" name="video_activo">
                            <option value="0" <?php echo (int) ($producto['video_activo'] ?? 0) === 0 ? 'selected' : ''; ?>>No mostrar</option>
                            <option value="1" <?php echo (int) ($producto['video_activo'] ?? 0) === 1 ? 'selected' : ''; ?>>Mostrar video</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Destacado</strong></label>
                        <select class="form-control" name="destacado">
                            <option value="0" <?php echo $producto['destacado'] == 0 ? 'selected' : ''; ?>>No</option>
                            <option value="1" <?php echo $producto['destacado'] == 1 ? 'selected' : ''; ?>>Si</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Estado</strong></label>
                        <select class="form-control" name="estado">
                            <option value="1" <?php echo $producto['estado'] == 1 ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo $producto['estado'] == 0 ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <hr>
            <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Actualizar elemento</button>
            <a href="productos_admin.php" class="btn btn-danger"><i class="fa fa-times fa-fw"></i>Cancelar</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
