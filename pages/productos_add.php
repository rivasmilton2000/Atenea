<?php 
include '../includes/connection.php';
require_once '../includes/atenea_catalog.php';
include '../includes/sidebar_admin.php'; 

// Verificar permisos (mismo código de antes)
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

// Obtener categorías
$sql_categorias = "SELECT * FROM categorias_productos WHERE estado = 1 ORDER BY nombre";
$resultado_categorias = mysqli_query($db, $sql_categorias);
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Agregar elemento al catálogo
            <a href="productos_admin.php" type="button" class="btn btn-secondary bg-gradient-secondary float-right" style="border-radius: 0px;">
                <i class="fas fa-fw fa-arrow-left"></i> Volver
            </a>
        </h4>
    </div>

    <div class="card-body">
        <form role="form" method="post" action="productos_transac.php?action=add" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label><strong>Nombre</strong></label>
                        <input class="form-control" name="nombre" required maxlength="150">
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Descripción Corta</strong></label>
                        <textarea class="form-control" name="descripcion_corta" rows="2" required maxlength="250"></textarea>
                        <small class="form-text text-muted">Máximo 250 caracteres (aparece en tarjetas)</small>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Descripción Completa</strong></label>
                        <textarea class="form-control" name="descripcion" rows="6" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Precio Regular</strong></label>
                                <input type="number" step="0.01" class="form-control" name="precio" required min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Precio con Descuento</strong> (opcional)</label>
                                <input type="number" step="0.01" class="form-control" name="precio_descuento" min="0">
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
                                <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Usa curso o certificación para la oferta de capacitación.</small>
                    </div>
                    <div class="form-group">
                        <label><strong>Imagen Principal</strong></label>
                        <input type="file" class="form-control-file" name="imagen" accept="image/*" required>
                        <small class="form-text text-muted">JPG, PNG. Máx 2MB</small>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Categoría</strong></label>
                        <select class="form-control" name="categoria_id" required>
                            <option value="">Seleccionar categoría</option>
                            <?php while ($cat = mysqli_fetch_assoc($resultado_categorias)) : ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['nombre']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Stock / cupos</strong></label>
                        <input type="number" class="form-control" name="stock" required min="0" value="0">
                        <small class="form-text text-muted">Para cursos o certificaciones puedes usar este campo como cupos disponibles.</small>
                    </div>

                    <div class="form-group">
                        <label><strong>Duración</strong> (opcional)</label>
                        <input type="text" class="form-control" name="duracion" maxlength="120" placeholder="Ej: 8 horas / 4 semanas">
                    </div>

                    <div class="form-group">
                        <label><strong>Video de YouTube</strong> (opcional)</label>
                        <input type="url" class="form-control" name="video_url" maxlength="255" placeholder="https://www.youtube.com/watch?v=...">
                    </div>

                    <div class="form-group">
                        <label><strong>Mostrar video en vista pública</strong></label>
                        <select class="form-control" name="video_activo">
                            <option value="0">No mostrar</option>
                            <option value="1">Mostrar video</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Destacado</strong></label>
                        <select class="form-control" name="destacado">
                            <option value="0">No</option>
                            <option value="1">Si</option>
                        </select>
                        <small class="form-text text-muted">Aparecerá primero</small>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Estado</strong></label>
                        <select class="form-control" name="estado">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <hr>
            <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Guardar elemento</button>
            <a href="productos_admin.php" class="btn btn-danger"><i class="fa fa-times fa-fw"></i>Cancelar</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
