<?php
// Sección de inclusión de archivos
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

// Sección de verificación de permisos de usuario
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Personal' || $Aa == 'Estudiante' || $Aa == 'Docente' || $Aa == 'Admin'){
        if ($Aa == 'Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa == 'Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa == 'Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa == 'Admin') {
            $redirectUrl = "index.php";
        }
        ?>
        <script type="text/javascript">
            Swal.fire({
                icon: 'warning',
                title: 'Página restringida!',
                text: 'Será redirigido.',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = "<?php echo $redirectUrl; ?>";
                }
            });
        </script>
        <?php
        exit(); // Terminar la ejecución del script después de la redirección
    }
}

// Sección de consulta de tablas de la base de datos
$query = 'SHOW TABLES';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
?>

<!-- Sección de visualización de las tablas de la base de datos -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Tablas de la base de datos</h4>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"> 
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre de la tabla</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $counter = 1;
                    while ($row = mysqli_fetch_array($result)) {
                        echo '<tr>';
                        echo '<td>' . $counter . '</td>';
                        echo '<td>' . $row[0] . '</td>';
                        echo '</tr>';
                        $counter++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <button class="btn btn-success" onclick="createBackup()">Crear respaldo</button>
        </div>
    </div>
</div>

<!-- Sección de script para crear respaldo -->
<script>
function createBackup() {
    Swal.fire({
        title: '¿Desea realizar el respaldo?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, crear',
        cancelButtonText: 'No, cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('sa_respaldo_bd_create.php')
                .then(response => response.blob()) // Cambiar a blob para manejar la descarga
                .then(blob => {
                    if (blob.size > 0) {
                        // Crear un enlace para descargar el archivo
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'backup.sql'; // Cambiar si es necesario
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'El archivo de respaldo está vacío o no se creó correctamente.'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al intentar crear el respaldo.'
                    });
                });
        } else {
            Swal.fire('Operación cancelada.', '', 'info');
        }
    });
}
</script>

<?php
// Sección de inclusión del pie de página
include '../includes/footer_superadmin.php';
?>
