<?php
include '../includes/connection.php';
include '../includes/sidebar_estudiante.php';

// Asegúrate de que esta línea esté al principio del archivo
date_default_timezone_set('America/El_Salvador');

// Verificación de tipo de usuario (sin cambios)
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Admin' || $Aa == 'Docente' || $Aa == 'Personal' || $Aa == 'SuperAdmin') {
        if ($Aa == 'Admin') {
            $redirectUrl = "index.php";
        } elseif ($Aa == 'Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa == 'Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa == 'SuperAdmin') {
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

$evaluacion_id = isset($_GET['evaluacion_id']) ? $_GET['evaluacion_id'] : null;
$ed_id = isset($_GET['ed_id']) ? $_GET['ed_id'] : null;


// Obtener el ID del estudiante correspondiente al nombre de usuario desde la base de datos
$queryEstudiante = "SELECT ESTUDIANTE_ID FROM estudiantes WHERE nombres_estudiante = '{$_SESSION['nombres_estudiante']}' AND apellidos_estudiante = '{$_SESSION['apellidos_estudiante']}'";
$resultEstudiante = mysqli_query($db, $queryEstudiante);

// Verificar si se encontró el estudiante en la base de datos
if (mysqli_num_rows($resultEstudiante) > 0) {
    $rowEstudiante = mysqli_fetch_assoc($resultEstudiante);
    $estudianteId = $rowEstudiante['ESTUDIANTE_ID'];
} else {
    // Manejar el caso en que no se encuentra el estudiante
    echo "Error: No se encontró el estudiante en la base de datos.";
    exit();
}

// Ahora puedes usar $estudianteId en lugar de $_SESSION['MEMBER_ID']
$alumno_id = $estudianteId;

if (!$evaluacion_id || !$ed_id) {
    echo "Error: Faltan parámetros necesarios.";
    exit;
}

// Obtener los detalles de la evaluación
$query_evaluacion = "SELECT * FROM evaluaciones WHERE evaluacion_id = ?";
$stmt_evaluacion = mysqli_prepare($db, $query_evaluacion);
if ($stmt_evaluacion === false) {
    die("Error en la preparación de la consulta de evaluación: " . mysqli_error($db));
}
mysqli_stmt_bind_param($stmt_evaluacion, "i", $evaluacion_id);
mysqli_stmt_execute($stmt_evaluacion);
$result_evaluacion = mysqli_stmt_get_result($stmt_evaluacion);
$row_evaluacion = mysqli_fetch_assoc($result_evaluacion);

if (!$row_evaluacion) {
    echo "No se encontró la evaluación especificada.";
    exit;
}

// Consulta combinada para obtener información de entrega y nota
$sql = "SELECT ev.*, n.valor_nota, n.fecha AS fecha_nota
        FROM ev_entregadas AS ev
        LEFT JOIN notas AS n ON n.id_ev_entregada = ev.ev_entregada_id
        INNER JOIN estudiantes AS e ON ev.alumno_id = e.ESTUDIANTE_ID
        WHERE ev.evaluacion_id = ? AND e.ESTUDIANTE_ID = ?";

$queryn = mysqli_prepare($db, $sql);
if ($queryn === false) {
    die("Error en la preparación de la consulta combinada: " . mysqli_error($db));
}
mysqli_stmt_bind_param($queryn, "ii", $evaluacion_id, $alumno_id);
mysqli_stmt_execute($queryn);
$result = mysqli_stmt_get_result($queryn);

$entrega_existe = mysqli_num_rows($result) > 0;
$row_entrega = mysqli_fetch_assoc($result);


// Después de obtener los detalles de la evaluación
$fecha_evaluacion = new DateTime($row_evaluacion['fecha']);
$fecha_actual = new DateTime();

$fecha_sobrepasada = $fecha_actual > $fecha_evaluacion;
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Detalle de la evaluación
        <a href="#" type="button" class="btn btn-primary bg-gradient-info" style="border-radius: 0px;" onclick="history.back()" value="volver atrás"><i class="fas fa-fw fa-backward"></i> Regresar</a>
        </h4>
    </div>

    <div class="card-body">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Título: <?php echo htmlspecialchars($row_evaluacion['titulo']); ?></h5>
                <p class="card-text">Descripción: <?php echo htmlspecialchars($row_evaluacion['descripcion']); ?></p>
                <p class="card-text">Fecha: <?php echo date('d-m-Y', strtotime($row_evaluacion['fecha'])); ?></p>
                <p class="card-text">Porcentaje de la evaluación: <?php echo htmlspecialchars($row_evaluacion['porcentaje']); ?>%</p>
            </div>
        </div>

        <div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">Estado de tu entrega</h5>
        <?php if ($entrega_existe): ?>
            <div class="alert alert-success" role="alert">
                <strong>ENTREGADO</strong> - Has completado esta actividad.
            </div>
            <div class="row mb-3">
                <div class="col-md-3"><strong>Observación:</strong></div>
                <div class="col-md-9"><?php echo htmlspecialchars($row_entrega['observacion']); ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3"><strong>Material entregado:</strong></div>
                <div class="col-md-9">
                    <?php if (!empty($row_entrega['material'])): ?>
                        <a href="estudiantes_vista_descargar2.php?id=<?php echo $row_entrega['ev_entregada_id']; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-download"></i> Descargar material
                        </a>
                    <?php else: ?>
                        <span class="text-muted">No se ha subido ningún archivo.</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3"><strong>Estado de calificación:</strong></div>
                <div class="col-md-9">
                    <?php if ($row_entrega['valor_nota']): ?>
                        <kbd class="bg-success">Calificado</kbd></p>
                    <?php else: ?>
                        <kbd class="bg-danger">Sin Calificar</kbd></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($row_entrega['valor_nota']): ?>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Nota obtenida:</strong></div>
                    <div class="col-md-9"><?php echo htmlspecialchars($row_entrega['valor_nota']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Fecha de calificación:</strong></div>
                    <div class="col-md-9"><?php echo date('d-m-Y', strtotime($row_entrega['fecha_nota'])); ?></div>
                </div>
            <?php endif; ?>
            <?php elseif ($fecha_sobrepasada): ?>
            <div class="alert alert-danger" role="alert">
                <strong>FECHA SOBREPASADA</strong> - Ya no puedes entregar esta actividad.
            </div>
            <p>La fecha límite de entrega era: <?php echo $fecha_evaluacion->format('d-m-Y'); ?></p>
        <?php else: ?>
            <div class="alert alert-warning" role="alert">
                <strong>PENDIENTE</strong> - Aún no has entregado esta actividad.
            </div>
            <p>Fecha límite de entrega: <?php echo $fecha_evaluacion->format('d-m-Y'); ?></p>
        <form id="entregaForm" action="estudiantes_vista_procesar_entrega.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="evaluacion_id" value="<?php echo $evaluacion_id; ?>">
            <input type="hidden" name="alumno_id" value="<?php echo $alumno_id; ?>">
            <input type="hidden" name="ed_id" value="<?php echo $ed_id; ?>">
                
                <div class="form-group mb-3">
                    <label for="observacion">Observación:</label>
                    <textarea class="form-control" id="observacion" name="observacion" minlength="3" maxlength="100" rows="3" required></textarea>
                </div>
                
                <div class="form-group mb-3">
                    <label for="material">Archivo:</label>
                    <input type="file" class="form-control" id="material" name="material" required>
                </div>
                
                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane"></i> Enviar entrega
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('entregaForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    
    fetch('estudiantes_vista_procesar_entrega.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Éxito',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    // En lugar de redirigir, recargamos la página actual
                    window.location.reload();
                }
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'Ocurrió un error al procesar la solicitud.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
});
</script>
<?php
include '../includes/footer.php';
?>