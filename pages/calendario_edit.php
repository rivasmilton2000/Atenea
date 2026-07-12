<?php
include('../includes/connection.php');
include('../includes/sidebar_admin.php');

// Verificación de permisos de usuario
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Personal' || $Aa == 'Estudiante' || $Aa == 'Docente' || $Aa == 'SuperAdmin') {
        if ($Aa == 'Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa == 'Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa == 'Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa == 'SuperAdmin') {
            $redirectUrl = "sa_vista.php";
        }
        ?>
        <script type="text/javascript">
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit(); // Terminar la ejecución del script después de la redirección
    }
}

$act_id = mysqli_real_escape_string($db, $_GET['id']);
$query = "SELECT * FROM actividades WHERE ACT_ID = '$act_id' AND ACT_ESTADO = 1";
$result = mysqli_query($db, $query);
$actividad = mysqli_fetch_assoc($result);

if (!$actividad) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Actividad no encontrada o desactivada',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href='calendario.php';
        });
    </script>";
    exit;
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Editar actividad</h4>
    </div>
    <div class="card-body">
        <form id="editActivityForm">
            <input type="hidden" name="act_id" value="<?php echo $actividad['ACT_ID']; ?>">
            <div class="form-group">
                <input class="form-control" placeholder="Nombre de la actividad" name="act_nombre" value="<?php echo $actividad['ACT_NOMBRE']; ?>" required>
            </div>
            <div class="form-group">
                <input type="datetime-local" class="form-control" name="act_fecha_inicio" value="<?php echo date('Y-m-d\TH:i', strtotime($actividad['ACT_FECHA_INICIO'])); ?>" required>
            </div>
            <div class="form-group">
                <input type="datetime-local" class="form-control" name="act_fecha_fin" value="<?php echo date('Y-m-d\TH:i', strtotime($actividad['ACT_FECHA_FIN'])); ?>" required>
            </div>
            <div class="form-group">
                <input type="color" class="form-control" name="act_color" value="<?php echo $actividad['ACT_COLOR']; ?>" required>
            </div>
            <hr>
            <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i> Guardar</button>
            <a href="calendario.php" class="btn btn-primary"><i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar</a>
        </form>  
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('editActivityForm').addEventListener('submit', function(e) {
    e.preventDefault();

    var formData = new FormData(this);
    formData.append('action', 'update');

    fetch('calendario_edit1.php?action=update', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success === false) {
            Swal.fire({
                icon: 'error',  // Cambia 'warning' a 'error'
                title: 'Error',
                text: data.message,
                confirmButtonText: 'OK'
            });
        } else if (data.success === true) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: data.message,
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'calendario.php';
                }
            });
        } else if (data.success === null) {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: data.message,
                showCancelButton: true,
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    formData.append('ignoreWarning', 'true');
                    fetch('calendario_edit1.php?action=update', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success === true) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: data.message,
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'calendario.php';
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message,
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error al procesar la solicitud.',
            confirmButtonText: 'OK'
        });
    });
});
</script>

<?php
include('../includes/footer.php');
?>
