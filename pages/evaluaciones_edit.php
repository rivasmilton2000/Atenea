<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';

// Verificar el tipo de usuario y redirigir según sea necesario
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
        case 'SuperAdmin':
            $redirectUrl = "sa_vista.php";
            break;
        default:
            $redirectUrl = "evaluaciones.php"; // Redirigir a la página de inventario si el tipo no está definido
            break;
    }

    // Redireccionar y salir del script si el tipo de usuario es válido
    if (in_array($userType, ['Personal', 'Estudiante', 'Docente', 'SuperAdmin'])) {
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
    $query = "SELECT e.evaluacion_id, e.titulo, e.descripcion, e.fecha, e.porcentaje, e.contenido_id,
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
          WHERE e.evaluacion_id = ?";
    
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $evaluacion_id = $row['evaluacion_id'];
        $titulo = $row['titulo'];
        $descripcion = $row['descripcion'];
        $fecha = $row['fecha'];
        $porcentaje = $row['porcentaje'];
        $contenido_id = $row['contenido_id'];
        $contenido_titulo = $row['contenido_titulo'];
        $asignatura = $row['asignatura'];
        $grado = $row['grado'];
        $periodo = $row['periodo'];
        $docente = $row['docente'];
    } else {
        echo "La evaluación con ID $id no fue encontrada.";
        exit();
    }
}
?>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Editar evaluación</h4>
        </div>
        <a href="evaluaciones.php" type="button" class="btn btn-primary bg-gradient-primary btn-block">
            <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
        </a>
        <div class="card-body">
            <form role="form" method="post" action="evaluaciones_edit1.php">
                <input type="hidden" name="evaluacion_id" value="<?php echo $evaluacion_id; ?>" />

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Título de la evaluación:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Título de la evaluación" minlength="5"  maxlength="80" name="titulo" value="<?php echo $titulo; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Descripción:
                    </div>
                    <div class="col-sm-9">
                        <textarea class="form-control" name="descripcion" minlength="5"  maxlength="250" rows="3" required><?php echo $descripcion; ?></textarea>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Fecha de la evaluación:
                    </div>
                    <div class="col-sm-9">
                        <input type="date" class="form-control" name="fecha" value="<?php echo $fecha; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                    Porcentaje de la evaluación:
                    </div>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" id="porcentaje" name="porcentaje" value="<?php echo $porcentaje; ?>" min="0" max="100" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Contenido a evaluar:
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control" id="contenido_id" name="contenido_id" required onchange="actualizarInfoContenido()">
                            <?php
                            $query = "SELECT c.contenido_id, c.titulo, a.A_NAME, g.G_NAME, p.p_name, 
                                             CONCAT(e.FIRST_NAME, ' ', e.LAST_NAME) as docente
                                      FROM contenidos c
                                      JOIN docentes_asignaturas da ON c.da_id = da.da_id
                                      JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
                                      JOIN grados g ON da.grado_id = g.G_ID
                                      JOIN periodo p ON da.periodo_id = p.p_id
                                      JOIN employee e ON da.profesor_id = e.EMPLOYEE_ID
                                      WHERE c.c_estado = 1
                                      ORDER BY c.titulo";
                            $result = mysqli_query($db, $query) or die(mysqli_error($db));
                            while ($row = mysqli_fetch_assoc($result)) {
                                $selected = ($row['contenido_id'] == $contenido_id) ? 'selected' : '';
                                echo "<option value='".$row['contenido_id']."' data-asignatura='".$row['A_NAME']."' 
                                      data-grado='".$row['G_NAME']."' data-periodo='".$row['p_name']."' 
                                      data-docente='".$row['docente']."' $selected>"
                                    .$row['titulo']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div id="infoContenido">
    <div class="form-group row text-left text-warning">
        <div class="col-sm-3" style="padding-top: 5px;">
            Asignatura:
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="asignatura" value="<?php echo $asignatura; ?>" readonly>
        </div>
    </div>
    <div class="form-group row text-left text-warning">
        <div class="col-sm-3" style="padding-top: 5px;">
            Grado:
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="grado" value="<?php echo $grado; ?>" readonly>
        </div>
    </div>
    <div class="form-group row text-left text-warning">
        <div class="col-sm-3" style="padding-top: 5px;">
            Periodo:
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="periodo" value="<?php echo $periodo; ?>" readonly>
        </div>
    </div>
    <div class="form-group row text-left text-warning">
        <div class="col-sm-3" style="padding-top: 5px;">
            Docente:
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="docente" value="<?php echo $docente; ?>" readonly>
        </div>
    </div>
</div>

                <hr>

                <button type="submit" class="btn btn-warning btn-block">
                    <i class="fa fa-edit fa-fw"></i> Actualizar
                </button>    
            </form>  
        </div>
    </div>
</center>

<script>
function actualizarInfoContenido() {
    var select = document.getElementById('contenido_id');
    var selectedOption = select.options[select.selectedIndex];
    
    document.getElementById('asignatura').value = selectedOption.getAttribute('data-asignatura');
    document.getElementById('grado').value = selectedOption.getAttribute('data-grado');
    document.getElementById('periodo').value = selectedOption.getAttribute('data-periodo');
    document.getElementById('docente').value = selectedOption.getAttribute('data-docente');
}
// Validación de porcentaje
document.getElementById('porcentaje').addEventListener('input', function() {
    var input = this;
    var value = input.value;

    // Permitir solo números enteros entre 1 y 100
    if (value !== '' && (value < 0 || value > 100 || !Number.isInteger(Number(value)))) {
        input.value = value.slice(0, -1);
    }
});

// Limitar la cantidad de dígitos a 3
document.getElementById('porcentaje').addEventListener('input', function() {
    var input = this;
    var value = input.value;

    if (value.length > 3) {
        input.value = value.slice(0, 3);
    }
});
// Ya no necesitamos llamar a la función al cargar la página
// window.onload = actualizarInfoContenido;
</script>

<?php
include '../includes/footer.php';
?>