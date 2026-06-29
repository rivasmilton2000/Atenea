<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa=='Personal' || $Aa=='Estudiante' || $Aa=='Docente' || $Aa=='Admin'){
        if ($Aa=='Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa=='Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa=='Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa=='Admin') {
            $redirectUrl = "index.php";
        }
        ?>
        <script type="text/javascript">
            //then it will be redirected
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit(); // Terminar la ejecución del script después de la redirección
    }
}

$id = $_GET['id'];

$query = "SELECT ed.ed_id, e.nombres_estudiante, e.apellidos_estudiante, e.carnet_estudiante, e.numero_lista_estudiante, 
                 g.G_NAME AS grado_estudiante, a.A_NAME AS materia, emp.FIRST_NAME, emp.LAST_NAME, p.p_name AS periodo,
                 da.da_id, p.p_id, e.ESTUDIANTE_ID, ed.periodo_id, da.grado_id, ed.ed_estado
          FROM estudiantes_docentes ed
          JOIN estudiantes e ON ed.estudiante_id = e.ESTUDIANTE_ID
          JOIN grados g ON e.grado_id_estudiante = g.G_ID
          JOIN docentes_asignaturas da ON ed.doc_asi_id = da.da_id
          JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
          JOIN employee emp ON da.profesor_id = emp.EMPLOYEE_ID
          JOIN periodo p ON ed.periodo_id = p.p_id
          WHERE ed.ed_id = ?";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_array($result)) {
    $ed_id = $row['ed_id'];
    $estudiante_nombre = $row['nombres_estudiante'] . ' ' . $row['apellidos_estudiante'];
    $estudiante_carnet = $row['carnet_estudiante'];
    $estudiante_numero_lista = $row['numero_lista_estudiante'];
    $grado_estudiante = $row['grado_estudiante'];
    $materia = $row['materia'];
    $docente = $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'];
    $periodo = $row['periodo'];
    $asignatura_id = $row['da_id'];
    $periodo_id = $row['periodo_id'];
    $estudiante_id = $row['ESTUDIANTE_ID'];
    $grado_id = $row['grado_id'];
}
// Obtener el nombre del grado asociado a la asignatura
$query_grado = "SELECT G_NAME FROM grados WHERE G_ID = ?";
$stmt_grado = mysqli_prepare($db, $query_grado);
mysqli_stmt_bind_param($stmt_grado, "i", $grado_id);
mysqli_stmt_execute($stmt_grado);
$result_grado = mysqli_stmt_get_result($stmt_grado);
$row_grado = mysqli_fetch_assoc($result_grado);
$grado_nombre = $row_grado['G_NAME'];

?>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Editar Asignación</h4>
        </div>
        <a type="button" class="btn btn-primary bg-gradient-primary btn-block" href="sa_dis_asignaturas.php"> <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar </a>
        <div class="card-body">
            <form role="form" method="post" action="sa_dis_asignaturas_edit1.php">
                <input type="hidden" name="ed_id" value="<?php echo $ed_id; ?>" />

    <div class="form-group row text-left text-warning">
  <div class="col-sm-3" style="padding-top: 5px;">
    Asignatura:
  </div>
  <div class="col-sm-9">
    <select class="form-control" name="asignatura" id="asignatura" required onchange="updateDocentePeriodo(this.value)">
      <option value="" disabled selected hidden>Seleccionar Asignatura</option>
      <?php
      $query_asignaturas = "SELECT da.da_id, CONCAT(a.A_NAME, ' - ', p.p_name, ' - ', g.G_NAME) AS asignatura_info
        FROM docentes_asignaturas da
        JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
        JOIN grados g ON da.grado_id = g.G_ID
        JOIN periodo p ON da.periodo_id = p.p_id
        WHERE da.da_estado = 1
        ORDER BY a.A_NAME, g.G_NAME, p.p_name";
      $result_asignaturas = mysqli_query($db, $query_asignaturas);
      if (mysqli_num_rows($result_asignaturas) > 0) {
      while ($row_asignaturas = mysqli_fetch_assoc($result_asignaturas)) {
        $selected = ($row_asignaturas['da_id'] == $asignatura_id) ? 'selected' : '';
        echo '<option value="' . $row_asignaturas['da_id'] . '" ' . $selected . '>' . $row_asignaturas['asignatura_info'] . '</option>';
      }
      } else {
        echo '<option value="">No hay asignaturas activas disponibles</option>';
      }
      ?>
    </select>
  </div>
</div>

<div class="form-group row text-left text-warning">
    <div class="col-sm-3" style="padding-top: 5px;">
        Grado:
    </div>
    <div class="col-sm-9">
        <input class="form-control" id="grado" name="grado" value="<?php echo $grado_nombre; ?>" readonly>
        <input type="hidden" name="grado_id" id="grado_id" value="<?php echo $grado_id; ?>">
    </div>
</div>

<div class="form-group row text-left text-warning">
  <div class="col-sm-3" style="padding-top: 5px;">
    Docente:
  </div>
  <div class="col-sm-9">
    <input class="form-control" id="docente" name="docente" value="<?php echo $docente; ?>" readonly>
  </div>
</div>

<div class="form-group row text-left text-warning">
  <div class="col-sm-3" style="padding-top: 5px;">
    Trimestre:
  </div>
  <div class="col-sm-9">
    <input class="form-control" id="periodo" name="periodo" value="<?php echo $periodo; ?>" readonly>
    <input type="hidden" name="periodo_id" id="periodo_id" value="<?php echo $periodo_id; ?>">
  </div>
</div>

<hr>

<div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Estudiante:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" name="estudiante" id="estudiante" value="<?php echo $estudiante_nombre . ' - ' . $grado_estudiante; ?>" readonly>
                        <input type="hidden" name="estudiante_id" value="<?php echo $estudiante_id; ?>">
                        <input type="hidden" name="ed_id" value="<?php echo $ed_id; ?>">
                    </div>
                </div>


<div class="form-group row text-left text-warning">
  <div class="col-sm-3" style="padding-top: 5px;">
    Carnet:
  </div>
  <div class="col-sm-9">
    <input class="form-control" id="carnet" name="carnet" value="<?php echo $estudiante_carnet; ?>" readonly>
  </div>
</div>

<div class="form-group row text-left text-warning">
  <div class="col-sm-3" style="padding-top: 5px;">
    Número de lista:
  </div>
  <div class="col-sm-9">
    <input class="form-control" id="numero_lista" name="numero_lista" value="<?php echo $estudiante_numero_lista; ?>" readonly>
  </div>
</div>

<div class="form-group row text-left text-warning">
    <div class="col-sm-3" style="padding-top: 5px;">
        Estado:
    </div>
    <div class="col-sm-9">
        <select class="form-control" name="ed_estado" required>
            <option value="1" <?php echo (isset($row['ed_estado']) && $row['ed_estado'] == 1) ? 'selected' : ''; ?>>Activo</option>
            <option value="0" <?php echo (isset($row['ed_estado']) && $row['ed_estado'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
        </select>
    </div>
</div>

                <hr>

                <button type="submit" class="btn btn-warning btn-block"><i class="fa fa-edit fa-fw"></i>Actualizar</button>
            </form>
        </div>
    </div>
</center>

<script>
  function updateDocentePeriodo(asignaturaId) {
    const docenteInput = document.getElementById('docente');
    const periodoInput = document.getElementById('periodo');
    const periodoIdInput = document.getElementById('periodo_id');
    const gradoInput = document.getElementById('grado');
    const gradoIdInput = document.getElementById('grado_id');

    

    <?php
$query = "SELECT CONCAT(emp.FIRST_NAME, ' ', emp.LAST_NAME) AS docente, p.p_name AS periodo, p.p_id AS periodo_id, g.G_NAME AS grado, da.grado_id
          FROM docentes_asignaturas da
          JOIN employee emp ON da.profesor_id = emp.EMPLOYEE_ID
          JOIN periodo p ON da.periodo_id = p.p_id
          JOIN grados g ON da.grado_id = g.G_ID
          WHERE da.da_id = ?";
$stmt = $db->prepare($query);
$asignaturas = array();
$result = mysqli_query($db, "SELECT da_id FROM docentes_asignaturas");
while ($row = mysqli_fetch_assoc($result)) {
    $stmt->bind_param("i", $row['da_id']);
    $stmt->execute();
    $stmt->bind_result($docente, $periodo, $periodo_id, $grado, $grado_id);
    $stmt->fetch();
    $asignaturas[$row['da_id']] = array(
        'docente' => $docente,
        'periodo' => $periodo,
        'periodo_id' => $periodo_id,
        'grado' => $grado,
        'grado_id' => $grado_id
    );
    $stmt->reset();
}
?>

const asignaturas = <?php echo json_encode($asignaturas); ?>;
function updateDocentePeriodo(asignaturaId) {
    const docenteInput = document.getElementById('docente');
    const periodoInput = document.getElementById('periodo');
    const periodoIdInput = document.getElementById('periodo_id');
    const gradoInput = document.getElementById('grado');
    const gradoIdInput = document.getElementById('grado_id');

    if (asignaturaId in asignaturas) {
        docenteInput.value = asignaturas[asignaturaId]['docente'];
        periodoInput.value = asignaturas[asignaturaId]['periodo'];
        periodoIdInput.value = asignaturas[asignaturaId]['periodo_id'];
        gradoInput.value = asignaturas[asignaturaId]['grado'];
        gradoIdInput.value = asignaturas[asignaturaId]['grado_id'];
    } else {
        docenteInput.value = '';
        periodoInput.value = '';
        periodoIdInput.value = '';
        gradoInput.value = '';
        gradoIdInput.value = '';
    }
}
</script>

<?php
include '../includes/footer_superadmin.php';
?>