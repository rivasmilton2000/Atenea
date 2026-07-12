<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';
?>

<?php
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
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Distribución de asignaturas&nbsp; <a href="#" data-toggle="modal" data-target="#teacherModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;"><i class="fas fa-fw fa-plus"></i></a></h4>
    </div>

    <div class="card-body">
    <div class="table-responsive">
        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th>ASIGNATURA</th>
                    <th>GRADO</th>
                    <th>ESTUDIANTE</th>
                    <th>PERIODO</th>
                    <th>ESTADO</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = 'SELECT ed.ed_id, CONCAT(e.apellidos_estudiante, ", ", e.nombres_estudiante) AS estudiante, 
                                 CONCAT(emp.FIRST_NAME, " ", emp.LAST_NAME) AS docente, 
                                 g.G_NAME AS grado, a.A_NAME AS materia, p.p_name AS periodo,
                                 ed.ed_estado
                          FROM estudiantes_docentes ed
                          JOIN estudiantes e ON ed.estudiante_id = e.ESTUDIANTE_ID
                          JOIN docentes_asignaturas da ON ed.doc_asi_id = da.da_id
                          JOIN employee emp ON da.profesor_id = emp.EMPLOYEE_ID
                          JOIN grados g ON da.grado_id = g.G_ID
                          JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
                          JOIN periodo p ON ed.periodo_id = p.p_id';
                $result = mysqli_query($db, $query) or die(mysqli_error($db));
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<tr>';
                    echo '<td>' . $row['materia'] . '</td>';
                    echo '<td>' . $row['grado'] . '</td>';
                    echo '<td>' . $row['estudiante'] . '</td>';
                    echo '<td>' . $row['periodo'] . '</td>';
                    echo '<td>';
                    if ($row['ed_estado'] == 1) {
                        echo '<span class="badge badge-success">Activo</span>';
                    } else {
                        echo '<span class="badge badge-danger">Inactivo</span>';
                    }
                    echo '</td>';
                    echo '<td align="right"> 
                                <div class="btn-group">
                                  <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="sa_dis_asignaturas_searchfrm.php?action=edit&id='.$row['ed_id'].'">
                                    <i class="fas fa-fw fa-list-alt"></i> Detalles</a>
                                </div>
                                <div class="btn-group">
                                  <a type="button" class="btn btn-warning bg-gradient-warning btn-sm" href="sa_dis_asignaturas_edit.php?action=edit&id='.$row['ed_id'].'">
                                    <i class="fas fa-fw fa-edit"></i> Editar</a>
                                </div>
                                <div class="btn-group">
                                  <button type="button" class="btn btn-danger bg-gradient-danger btn-sm" onclick="confirmDelete('.$row['ed_id'].')">
                                    <i class="fas fa-fw fa-trash"></i> Eliminar </button>
                                </div> 
                            </td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<?php
include '../includes/footer_superadmin.php';
?>

<!-- Modal para agregar un registro a la tabla "estudiantes_docentes" -->
<div class="modal fade" id="teacherModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Ingresar asignación de estudiantes</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form role="form" method="post" action="sa_dis_asignaturas_transac.php?action=add">
          <div class="form-group">
            <label for="estudiante">Estudiante</label>
            <select class="form-control" id="estudiante" name="estudiante" required>
              <option value="" disabled selected hidden>Seleccionar Estudiante</option>
              <?php
              // Consulta modificada para obtener solo los estudiantes activos (estado_estudiante = 1)
              $query = "SELECT e.ESTUDIANTE_ID, CONCAT(e.apellidos_estudiante, ', ', e.nombres_estudiante, ' - ', g.G_NAME) AS estudiante_grado
                FROM estudiantes e
                LEFT JOIN grados g ON e.grado_id_estudiante = g.G_ID
                WHERE e.estado_estudiante = 1
                ORDER BY e.apellidos_estudiante, e.nombres_estudiante";
              $result = mysqli_query($db, $query);
    
              // Verificar si hay resultados
              if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                  echo '<option value="' . $row['ESTUDIANTE_ID'] . '">' . $row['estudiante_grado'] . '</option>';
                }
              } else {
                echo '<option value="">No hay estudiantes registrados</option>';
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label for="asignatura">Asignatura</label>
            <select class="form-control" id="asignatura" name="asignatura" required onchange="updateDocentePeriodo(this.value)">
              <option value="" disabled selected hidden>Seleccionar asignatura</option>
              <?php
              $query = "SELECT da.da_id, a.A_NAME, g.G_NAME, p.p_name
                FROM docentes_asignaturas da
                JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
                JOIN grados g ON da.grado_id = g.G_ID
                JOIN periodo p ON da.periodo_id = p.p_id
                WHERE da.da_estado = 1
                ORDER BY a.A_NAME, g.G_NAME, p.p_name";
              $result = mysqli_query($db, $query);
              if (mysqli_num_rows($result) > 0) {
              while ($row = mysqli_fetch_assoc($result)) {
                echo '<option value="' . $row['da_id'] . '">' . $row['A_NAME'] . ' - ' . $row['p_name'] . ' - ' . $row['G_NAME'] . '</option>';
              }
              } else {
                echo '<option value="">No hay asignaturas disponibles</option>';
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label for="docente">Docente</label>
            <input type="text" class="form-control" id="docente" name="docente" readonly>
          </div>

          <div class="form-group">
            <label for="periodo">Trimestre</label>
            <input type="text" class="form-control" id="periodo" name="periodo" readonly>
          </div>

          <div class="form-group">
            <label for="estado">Estado</label>
            <select class="form-control" id="estado" name="estado" required>
                <option value="" disabled selected>Seleccione el estado del registro</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
            </div>

            <div class="form-group">
                <input type="hidden" id="estudianteId" name="estudianteId" readonly>
                <input type="hidden" id="asignaturaId" name="asignaturaId" readonly>
            </div>

            <hr>
            <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Guardar</button>
            <button type="reset" class="btn btn-warning"><i class="fa fa-circle-notch fa-fw"></i>Reiniciar</button>
            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i>Cancelar</button>
            </form>
        </div>
    </div>
  </div>
</div>

<script>
  // Función para actualizar los campos ocultos con los IDs correspondientes
  function actualizarIds() {
    const estudianteSelect = document.getElementById('estudiante');
    const asignaturaSelect = document.getElementById('asignatura');
    const estudianteIdInput = document.getElementById('estudianteId');
    const asignaturaIdInput = document.getElementById('asignaturaId');

    estudianteIdInput.value = estudianteSelect.value;
    asignaturaIdInput.value = asignaturaSelect.value;
  }

  // Función para actualizar los campos de docente y período según la asignatura seleccionada
  function updateDocentePeriodo(asignaturaId) {
    const docenteInput = document.getElementById('docente');
    const periodoInput = document.getElementById('periodo');

    <?php
      $query = "SELECT CONCAT(emp.FIRST_NAME, ' ', emp.LAST_NAME) AS docente, p.p_name AS periodo
                FROM docentes_asignaturas da
                JOIN employee emp ON da.profesor_id = emp.EMPLOYEE_ID
                JOIN periodo p ON da.periodo_id = p.p_id
                WHERE da.da_id = ?";
      $stmt = $db->prepare($query);
      $asignaturas = array();
      $result = mysqli_query($db, "SELECT da_id FROM docentes_asignaturas");
      while ($row = mysqli_fetch_assoc($result)) {
        $stmt->bind_param("i", $row['da_id']);
        $stmt->execute();
        $stmt->bind_result($docente, $periodo);
        $stmt->fetch();
        $asignaturas[$row['da_id']] = array(
          'docente' => $docente,
          'periodo' => $periodo
        );
        $stmt->reset();
      }
    ?>

    const asignaturas = <?php echo json_encode($asignaturas); ?>;

    if (asignaturaId in asignaturas) {
      docenteInput.value = asignaturas[asignaturaId]['docente'];
      periodoInput.value = asignaturas[asignaturaId]['periodo'];
    } else {
      docenteInput.value = '';
      periodoInput.value = '';
    }
  }

  // Actualizar los IDs al cambiar los selects
  document.getElementById('estudiante').addEventListener('change', actualizarIds);
  document.getElementById('asignatura').addEventListener('change', actualizarIds);
</script>

<!-- <script>
function confirmDelete(asigId) {
    if (confirm("¿Estás seguro de que deseas eliminar este registro?")) {
        window.location.href = "sa_dis_asignaturas_eliminar.php?id=" + asigId;
    }
} -->
<!-- </script> -->
<style>
        .custom-popup-class, .custom-title-class, .custom-confirm-button-class, .custom-cancel-button-class {
            font-family: 'Open Sans', sans-serif;
        }
        .custom-title-class {
            font-weight: 700;
        }
        .custom-confirm-button-class, .custom-cancel-button-class {
            font-weight: 600;
        }
    </style>
    <script>
function confirmDelete(asigId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Deseas eliminar este estudiante de esta asignatura?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'custom-popup-class',
            title: 'custom-title-class',
            confirmButton: 'custom-confirm-button-class',
            cancelButton: 'custom-cancel-button-class'
        },
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`sa_dis_asignaturas_eliminar.php?id=${asigId}`)
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: data.status === 'success' ? '¡Eliminado!' : 'Error',
                        text: data.message,
                        icon: data.status,
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'custom-popup-class',
                            title: 'custom-title-class',
                            confirmButton: 'custom-confirm-button-class'
                        }
                    }).then(() => {
                        if (data.status === 'success') {
                            window.location = 'sa_dis_asignaturas.php';
                        }
                    });
                });
        }
    });
}
</script>