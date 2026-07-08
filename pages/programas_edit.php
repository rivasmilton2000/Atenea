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

// Obtener ID del programa
$programa_id = isset($_GET['id']) ? mysqli_real_escape_string($db, $_GET['id']) : 0;

// Consultar el programa
$query = "SELECT * FROM programas_educativos WHERE id = '$programa_id'";
$result = mysqli_query($db, $query) or die(mysqli_error($db));

if (mysqli_num_rows($result) == 0) {
    header('Location: programas_admin.php');
    exit();
}

$programa = mysqli_fetch_assoc($result);
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Editar Programa Educativo
            <a href="programas_admin.php" type="button" class="btn btn-secondary bg-gradient-secondary float-right" style="border-radius: 0px;">
                <i class="fas fa-fw fa-arrow-left"></i> Volver
            </a>
        </h4>
    </div>

    <div class="card-body">
        <form role="form" method="post" action="programas_transac.php?action=edit" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $programa['id']; ?>">
            <input type="hidden" name="current_image" value="<?php echo $programa['imagen']; ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Título del Programa</label>
                        <input class="form-control" placeholder="Título del programa" name="titulo" value="<?php echo htmlspecialchars($programa['titulo']); ?>" required maxlength="100">
                    </div>
                    
                    <div class="form-group">
                        <label>Descripción Corta (Index)</label>
                        <textarea class="form-control" placeholder="Descripción breve para la página principal" name="descripcion_corta" rows="3" required><?php echo htmlspecialchars($programa['descripcion_corta']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Descripción Completa (Capacitación)</label>
                        <textarea class="form-control" placeholder="Descripción completa del programa" name="descripcion_completa" rows="6" required><?php echo htmlspecialchars($programa['descripcion_completa']); ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Imagen Actual</label>
                        <div class="mb-3">
                            <img src="../img/<?php echo $programa['imagen']; ?>" alt="<?php echo $programa['titulo']; ?>" class="img-fluid rounded">
                        </div>
                        <label>Cambiar Imagen (opcional)</label>
                        <input type="file" class="form-control-file" name="imagen" accept="image/*">
                        <small class="form-text text-muted">Deja en blanco si no deseas cambiar la imagen</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Nivel</label>
                        <select class="form-control" name="nivel" required>
                            <option value="Basico" <?php echo $programa['nivel'] == 'Basico' ? 'selected' : ''; ?>>Básico</option>
                            <option value="Intermedio" <?php echo $programa['nivel'] == 'Intermedio' ? 'selected' : ''; ?>>Intermedio</option>
                            <option value="Avanzado" <?php echo $programa['nivel'] == 'Avanzado' ? 'selected' : ''; ?>>Avanzado</option>
                            <option value="Especializado" <?php echo $programa['nivel'] == 'Especializado' ? 'selected' : ''; ?>>Especializado</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Instructor</label>
                        <input class="form-control" placeholder="Nombre del instructor" name="instructor" value="<?php echo htmlspecialchars($programa['instructor']); ?>" required maxlength="100">
                    </div>
                    
                    <div class="form-group">
                        <label>Orden</label>
                        <input type="number" class="form-control" name="orden" value="<?php echo $programa['orden']; ?>" required min="1">
                    </div>
                    
                    <div class="form-group">
                        <label>Estado</label>
                        <select class="form-control" name="estado" required>
                            <option value="1" <?php echo $programa['estado'] == 1 ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo $programa['estado'] == 0 ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <hr>
            <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Actualizar</button>
            <a href="programas_admin.php" class="btn btn-danger"><i class="fa fa-times fa-fw"></i>Cancelar</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>         
