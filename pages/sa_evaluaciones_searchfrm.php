<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

// Verificar permisos de usuario según el tipo (se mantiene igual)
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'];
$result = mysqli_query($db, $query) or die(mysqli_error($db));

while ($row = mysqli_fetch_assoc($result)) {
    $userType = $row['TYPE'];
    
    // Redirigir según el tipo de usuario
    switch ($userType) {
        case 'Personal':
            $redirectUrl = "empleados_vista.php";
            break;
        case 'Estudiante':
            $redirectUrl = "estudiante_vista.php";
            break;
        case 'Docente':
            $redirectUrl = "docentes_vista.php";
            break;
        case 'Admin':
            $redirectUrl = "index.php";
            break;
        default:
            $redirectUrl = "sa_evaluaciones.php"; // Redirigir a la página de inventario si el tipo no está definido
            break;
    }

    // Redireccionar y salir del script si el tipo de usuario es válido
    if (in_array($userType, ['Personal', 'Estudiante', 'Docente', 'Admin'])) {
        ?>
        <script type="text/javascript">
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit(); // Terminar la ejecución del script después de la redirección
    }
}

// Obtener el ID de la evaluación desde el parámetro GET
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Consultar la base de datos para obtener los detalles de la evaluación
    $query = "SELECT e.evaluacion_id, e.titulo, e.descripcion, e.fecha, e.porcentaje, e.evaluacion_estado,
                 c.titulo AS contenido_titulo, c.descripcion AS contenido_descripcion,
                 a.A_NAME as asignatura, g.G_NAME as grado, p.p_name as periodo, 
                 CONCAT(emp.FIRST_NAME, ' ', emp.LAST_NAME) as docente
          FROM evaluaciones e
          JOIN contenidos c ON e.contenido_id = c.contenido_id
          JOIN docentes_asignaturas da ON c.da_id = da.da_id
          JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
          JOIN grados g ON da.grado_id = g.G_ID
          JOIN periodo p ON da.periodo_id = p.p_id
          JOIN employee emp ON da.profesor_id = emp.EMPLOYEE_ID
          WHERE e.evaluacion_id = $id";
    $result = mysqli_query($db, $query) or die(mysqli_error($db));

    // Obtener los datos de la evaluación
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $evaluacion_id = $row['evaluacion_id'];
        $titulo = $row['titulo'];
        $descripcion = $row['descripcion'];
        $fecha = $row['fecha'];
        $porcentaje = $row['porcentaje'];
        $contenido_titulo = $row['contenido_titulo'];
        $contenido_descripcion = $row['contenido_descripcion'];
        $asignatura = $row['asignatura'];
        $grado = $row['grado'];
        $periodo = $row['periodo'];
        $docente = $row['docente'];
        $evaluacion_estado = $row['evaluacion_estado'];
    } else {
        // Manejar el caso si no se encuentra la evaluación
        echo "La evaluación con ID $id no fue encontrada.";
        exit();
    }
} else {
    // Manejar el caso si no se proporciona un ID válido
    echo "ID de evaluación no especificado.";
    exit();
}
?>

<!-- HTML para mostrar los detalles de la evaluación -->
<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Detalles de la evaluación</h4>
        </div>
        <a href="sa_evaluaciones.php" type="button" class="btn btn-primary bg-gradient-primary btn-block">
            <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
        </a>
        <div class="card-body">
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Título de la evaluación</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $titulo; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Descripción de la evaluación</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $descripcion; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Fecha de la evaluación</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo date('d-m-Y', strtotime($fecha)); ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Porcentaje</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $porcentaje; ?>%</h5>
                </div>
            </div>
            <hr>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Título del contenido</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $contenido_titulo; ?></h5>
                </div>
            </div>
            
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Asignatura</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $asignatura; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Grado</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $grado; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Periodo</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $periodo; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Docente</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $docente; ?></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Estado</h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php 
                        if($evaluacion_estado == 1) {
                            echo '<span class="badge badge-success">Activo</span>';
                        } else {
                            echo '<span class="badge badge-danger">Inactivo</span>';
                        }
                    ?></h5>
                </div>
            </div>
        </div>
    </div>
</center>

<?php
include '../includes/footer_superadmin.php';
?>