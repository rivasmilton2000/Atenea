<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

// Verificar el tipo de usuario y redirigir según sea necesario
// [El código de verificación de usuario se mantiene igual]

// Obtener el ID de la nota desde el parámetro GET
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Consultar la base de datos para obtener los detalles de la nota
    $query = "SELECT n.nota_id, n.valor_nota, n.nota_estado,
                     ee.ev_entregada_id, e.titulo AS evaluacion_titulo, e.fecha AS evaluacion_fecha, e.porcentaje AS evaluacion_porcentaje,
                     CONCAT(es.apellidos_estudiante, ', ', es.nombres_estudiante) AS estudiante_nombre,
                     g.G_NAME AS grado_nombre, es.numero_lista_estudiante
              FROM notas n
              JOIN ev_entregadas ee ON n.id_ev_entregada = ee.ev_entregada_id
              JOIN evaluaciones e ON ee.evaluacion_id = e.evaluacion_id
              JOIN estudiantes es ON ee.alumno_id = es.ESTUDIANTE_ID
              JOIN grados g ON es.grado_id_estudiante = g.G_ID
              WHERE n.nota_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Obtener los datos de la nota
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    } else {
        echo "La nota con ID $id no fue encontrada.";
        exit();
    }
} else {
    echo "ID de nota no especificado.";
    exit();
}
?>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Editar nota</h4>
        </div>
        <a href="sa_not_estudiantes.php" type="button" class="btn btn-primary bg-gradient-primary btn-block">
            <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
        </a>
        <div class="card-body">
            <form role="form" method="post" action="sa_not_estudiantes_edit1.php">
                <input type="hidden" name="nota_id" value="<?php echo $row['nota_id']; ?>" />

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Evaluación:
                    </div>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" value="<?php echo $row['evaluacion_titulo']; ?>" readonly>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Fecha de la evaluación:
                    </div>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" value="<?php echo date('d-m-Y', strtotime($row['evaluacion_fecha'])); ?>" readonly>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Porcentaje de la evaluación:
                    </div>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" value="<?php echo $row['evaluacion_porcentaje']; ?>%" readonly>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Estudiante:
                    </div>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" value="<?php echo $row['estudiante_nombre']; ?>" readonly>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Grado y número de lista:
                    </div>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" value="Grado: <?php echo $row['grado_nombre']; ?> - Número de lista: <?php echo $row['numero_lista_estudiante']; ?>" readonly>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Nota:
                    </div>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" id="valor_nota" name="valor_nota" required oninput="validarNota(this)" value="<?php echo $row['valor_nota']; ?>" step="0.01" min="0" max="10" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Estado:
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control" name="nota_estado" required>
                            <option value="1" <?php if ($row['nota_estado'] == 1) echo 'selected'; ?>>Activo</option>
                            <option value="0" <?php if ($row['nota_estado'] == 0) echo 'selected'; ?>>Inactivo</option>
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

<?php
include '../includes/footer_superadmin.php';
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function showAlert(icon, title, text) {
    Swal.fire({
        icon: icon,
        title: title,
        text: text,
        confirmButtonText: 'Aceptar'
    });
}

function validarNota(input) {
    var value = input.value;
    var regex = /^(?:[0-9]|[1-9][0-9]|10)(?:\.[0-9]?)?$/;

    // Si el campo está vacío, no mostrar alerta
    if (value === '') {
        return;
    }

    // Validar que el valor cumpla con el formato
    if (!regex.test(value)) {
        input.value = '';
        return; // No mostrar alerta aquí para valores no válidos
    }

    // Asegurarse de que el valor no exceda 10
    if (parseFloat(value) > 10) {
        input.value = '0'; // Ajustar el valor a 10
        showAlert('error', 'Nota no válida', 'La nota no puede ser mayor a 10.');
    }
}
</script>