<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

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
        case 'Admin':
            $redirectUrl = "index.php";
            break;
        default:
            $redirectUrl = "sa_con_evaluacion.php"; // Redirigir a la página de inventario si el tipo no está definido
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

// Obtener el ID de la evaluación entregada desde el parámetro GET
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Consultar la base de datos para obtener los detalles de la evaluación entregada
    $query = "SELECT ee.ev_entregada_id, ee.evaluacion_id, ee.alumno_id, ee.material, ee.observacion, ee.ev_entregada_estado,
                     e.titulo AS evaluacion_titulo, e.fecha AS evaluacion_fecha, e.porcentaje AS evaluacion_porcentaje,
                     CONCAT(es.apellidos_estudiante, ', ', es.nombres_estudiante) AS estudiante_nombre,
                     g.G_NAME AS grado_nombre, es.numero_lista_estudiante
              FROM ev_entregadas ee
              JOIN evaluaciones e ON ee.evaluacion_id = e.evaluacion_id
              JOIN estudiantes es ON ee.alumno_id = es.ESTUDIANTE_ID
              JOIN grados g ON es.grado_id_estudiante = g.G_ID
              WHERE ee.ev_entregada_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Obtener los datos de la evaluación entregada
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    } else {
        echo "La evaluación entregada con ID $id no fue encontrada.";
        exit();
    }
} else {
    echo "ID de evaluación entregada no especificado.";
    exit();
}
?>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Editar evaluación entregada</h4>
        </div>
        <a href="sa_eva_entregadas.php" type="button" class="btn btn-primary bg-gradient-primary btn-block">
            <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
        </a>
        <div class="card-body">
            <form role="form" method="post" action="sa_eva_entregadas_edit1.php" enctype="multipart/form-data">
                <input type="hidden" name="ev_entregada_id" value="<?php echo $row['ev_entregada_id']; ?>" />

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Evaluación:
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control" name="evaluacion_id" id="evaluacion_id" required onchange="actualizarInfoEvaluacion()">
                            <?php
                            $query = "SELECT evaluacion_id, titulo, fecha, porcentaje FROM evaluaciones WHERE evaluacion_estado = 1 ORDER BY titulo";
                            $result = mysqli_query($db, $query) or die(mysqli_error($db));
                            while ($eval_row = mysqli_fetch_assoc($result)) {
                                $selected = ($eval_row['evaluacion_id'] == $row['evaluacion_id']) ? 'selected' : '';
                                echo "<option value='".$eval_row['evaluacion_id']."' 
                                        data-fecha='".date('d-m-Y', strtotime($eval_row['fecha']))."'
                                        data-porcentaje='".$eval_row['porcentaje']."'
                                        $selected>".$eval_row['titulo']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Fecha de la evaluación:
                    </div>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="fecha_evaluacion" readonly 
                               value="<?php echo date('d-m-Y', strtotime($row['evaluacion_fecha'])); ?>">
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Porcentaje de la evaluación:
                    </div>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="porcentaje_evaluacion" readonly 
                               value="<?php echo $row['evaluacion_porcentaje']; ?>%">
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Estudiante:
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control" name="alumno_id" id="alumno_id" required onchange="actualizarInfoAlumno()">
                            <?php
                            $query = "SELECT e.ESTUDIANTE_ID, CONCAT(e.apellidos_estudiante, ', ', e.nombres_estudiante) AS nombre_completo,
                                             g.G_NAME, e.numero_lista_estudiante
                                      FROM estudiantes e
                                      JOIN grados g ON e.grado_id_estudiante = g.G_ID
                                      WHERE e.estado_estudiante = 1 
                                      ORDER BY e.apellidos_estudiante, e.nombres_estudiante";
                            $result = mysqli_query($db, $query) or die(mysqli_error($db));
                            while ($est_row = mysqli_fetch_assoc($result)) {
                                $selected = ($est_row['ESTUDIANTE_ID'] == $row['alumno_id']) ? 'selected' : '';
                                echo "<option value='".$est_row['ESTUDIANTE_ID']."' 
                                        data-grado='".$est_row['G_NAME']."'
                                        data-numero_lista='".$est_row['numero_lista_estudiante']."'
                                        $selected>".$est_row['nombre_completo']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Grado y número de lista:
                    </div>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="grado_numero_alumno" readonly 
                               value="Grado: <?php echo $row['grado_nombre']; ?> - Número de lista: <?php echo $row['numero_lista_estudiante']; ?>">
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Material actual:
                    </div>
                    <div class="col-sm-9">
                        <p><?php echo $row['material']; ?></p>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Nuevo material:
                    </div>
                    <div class="col-sm-9">
                        <input type="file" class="form-control-file" name="material">
                        <small class="form-text text-muted">Deje en blanco si no desea cambiar el material.</small>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Observación:
                    </div>
                    <div class="col-sm-9">
                        <textarea class="form-control" name="observacion" rows="3" minlength="5" maxlength="300" required><?php echo $row['observacion']; ?></textarea>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Estado:
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control" name="ev_entregada_estado" required>
                            <option value="1" <?php if ($row['ev_entregada_estado'] == 1) echo 'selected'; ?>>Activo</option>
                            <option value="0" <?php if ($row['ev_entregada_estado'] == 0) echo 'selected'; ?>>Inactivo</option>
                        </select>
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
function actualizarInfoEvaluacion() {
    var select = document.getElementById('evaluacion_id');
    var selectedOption = select.options[select.selectedIndex];

    document.getElementById('fecha_evaluacion').value = selectedOption.getAttribute('data-fecha');
    document.getElementById('porcentaje_evaluacion').value = selectedOption.getAttribute('data-porcentaje') + '%';
}

function actualizarInfoAlumno() {
    var select = document.getElementById('alumno_id');
    var selectedOption = select.options[select.selectedIndex];

    var grado = selectedOption.getAttribute('data-grado');
    var numeroLista = selectedOption.getAttribute('data-numero_lista');
    document.getElementById('grado_numero_alumno').value = "Grado: " + grado + " - Número de lista: " + numeroLista;
}
</script>

<?php
include '../includes/footer_superadmin.php';
?>