<?php 
include '../includes/connection.php';
include '../includes/sidebar_admin.php'; 

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa=='Personal' || $Aa=='Estudiante' || $Aa=='Docente'){
        if ($Aa=='Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa=='Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa=='Docente') {
            $redirectUrl = "docentes_vista.php";
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

// Consultar información actual de configuración de correo
$query = "SELECT * FROM configmail LIMIT 1";
$result = mysqli_query($db, $query) or die(mysqli_error($db));
$configmail = mysqli_fetch_assoc($result);
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Configuración de Correo Electrónico</h4>
    </div>

    <div class="card-body">
        <form role="form" method="post" action="configmail_transac.php">
            <input type="hidden" name="id" value="<?php echo $configmail['id']; ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label><strong>Email</strong></label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($configmail['email']); ?>" required maxlength="150">
                        <small class="form-text text-muted">Correo electrónico principal del sistema</small>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Token de Autenticación</strong></label>
                        <textarea class="form-control" name="token" rows="6" required><?php echo htmlspecialchars($configmail['token']); ?></textarea>
                        <small class="form-text text-muted">Token de acceso para el servicio de correo</small>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fa fa-info-circle"></i> Información</h6>
                        <p class="mb-0">Esta configuración se utiliza para el envío de correos electrónicos desde el sistema.</p>
                        <hr>
                        <p class="mb-0 small">Asegúrate de mantener el token seguro y actualizado.</p>
                    </div>
                </div>
            </div>
            
            <hr>
            <button type="submit" class="btn btn-success btn-lg"><i class="fa fa-check fa-fw"></i>Actualizar Configuración</button>
            <button type="reset" class="btn btn-warning btn-lg"><i class="fa fa-undo fa-fw"></i>Restablecer</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>