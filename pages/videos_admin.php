<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
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
    <h4 class="m-2 font-weight-bold text-primary">
      Videos
      <a href="#" data-toggle="modal" data-target="#videoModal"
        class="btn btn-primary bg-gradient-primary">
        <i class="fas fa-fw fa-plus"></i>
      </a>
    </h4>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Contenido</th>
            <th>Video</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $query = "SELECT v.video_id, v.titulo, v.youtube_id, c.titulo AS contenido FROM videos v JOIN 
                        contenidos c ON v.contenido_id = c.contenido_id WHERE v.estado = 1";

          $result = mysqli_query($db, $query) or die(mysqli_error($db));

          while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['video_id'] . "</td>";
            echo "<td>" . $row['titulo'] . "</td>";
            echo "<td>" . $row['contenido'] . "</td>";

            echo "<td>
                                        <iframe width='200' height='120'
                                        src='https://www.youtube.com/embed/" . $row['youtube_id'] . "'
                                        frameborder='0' allowfullscreen>
                                        </iframe>
                                        </td>";
            echo "<td>
                 <a href='videos_edit.php?id=" . $row['video_id'] . "' 
                 class='btn btn-primary btn-sm'>Editar</a>

                 <button class='btn btn-danger btn-sm' 
                 onclick='eliminarVideo(" . $row['video_id'] . ")'>
                 Eliminar
                 </button>
                 </td>";

            echo "</tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="modal fade" id="videoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Agregar Video</h5>
        <button class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
        <form method="post" action="videos_transac.php?action=add">

          <!-- ASIGNATURA -->
          <div class="form-group">
            <label>Asignaturas</label>
            <select id="asignaturaSelect" class="form-control" required>
              <option disabled selected>Seleccionar</option>

              <?php
              $query = "SELECT ASIGNATURA_ID, A_NAME FROM asignaturas WHERE A_ESTADO = 1";
              $result_a = mysqli_query($db, $query);

              while ($a = mysqli_fetch_assoc($result_a)) {
                echo "<option value='" . $a['ASIGNATURA_ID'] . "'>" . $a['A_NAME'] . "</option>";
              }
              ?>
            </select>
          </div>
          <!-- CONTENIDO -->
          <div class="form-group">
            <label>Contenido</label>
            <select name="contenido_id" id="contenidoSelect" class="form-control" required>
              <option disabled selected>Seleccionar</option>

              <?php
              $query = "SELECT contenido_id, titulo FROM contenidos WHERE c_estado = 1";
              $res = mysqli_query($db, $query);

              while ($c = mysqli_fetch_assoc($res)) {
                echo "<option value='" . $c['contenido_id'] . "'>" . $c['titulo'] . "</option>";
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <input type="text" name="titulo" class="form-control" placeholder="Título del video" required>
          </div>

          <div class="form-group">
            <textarea name="descripcion" class="form-control" placeholder="Descripción"></textarea>
          </div>

          <div class="form-group">
            <input type="text" name="youtube_url" class="form-control" placeholder="Link de YouTube" required>
          </div>

          <button type="submit" class="btn btn-success">Guardar</button>
        </form>
      </div>

    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>

<!-- SCRIPT DEL SELECT DINÁMICO -->
<script>
  document.addEventListener("DOMContentLoaded", function() {

    document.getElementById("asignaturaSelect").addEventListener("change", function() {
      let asignaturaId = this.value;

      fetch("get_contenidos.php?asignatura_id=" + asignaturaId)
        .then(res => res.text())
        .then(data => {
          document.getElementById("contenidoSelect").innerHTML = data;
        });
    });

  });
</script>
<script>
  function eliminarVideo(id)
  {
    if(confirm("¿Seguro que quieres eliminar este video?"))
    {
      window.location = "videos_delete.php?id=" + id;
    }
  }
</script>