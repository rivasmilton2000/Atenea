<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
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

<center>
  <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
    <div class="card-header py-3">
      <h4 class="m-2 font-weight-bold text-primary">Detalles del vehículo</h4>
    </div>
    <a href="sa_vehiculos.php" type="button" class="btn btn-primary bg-gradient-primary btn-block">
      <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
    </a>
    <div class="card-body">
      <?php
      $query = 'SELECT v.id, v.vehicle_license, v.vehicle_model, v.vehicle_attendant, e.FIRST_NAME, e.LAST_NAME, v.vehicle_image, v.v_estado
                FROM vehicles v
                LEFT JOIN employee e ON v.vehicle_attendant = e.EMPLOYEE_ID
                WHERE v.id = ' . $_GET['id'];
      $result = mysqli_query($db, $query) or die(mysqli_error($db));

      while ($row = mysqli_fetch_assoc($result)) {
          $id = $row['id'];
          $vehicle_license = $row['vehicle_license'];
          $vehicle_model = $row['vehicle_model'];
          $vehicle_attendant = $row['vehicle_attendant'];
          $FIRST_NAME = $row['FIRST_NAME'];
          $LAST_NAME = $row['LAST_NAME'];
          $vehicle_image = $row['vehicle_image'];
          $v_estado = $row['v_estado'];
      ?>
      <div class="form-group row text-left">
        <div class="col-sm-3 text-primary">
          <h5>Placa del vehículo</h5>
        </div>
        <div class="col-sm-9">
          <h5>: <?php echo $vehicle_license; ?><br></h5>
        </div>
      </div>
    
      <div class="form-group row text-left">
        <div class="col-sm-3 text-primary">
          <h5>Modelo del vehículo</h5>
        </div>
        <div class="col-sm-9">
          <h5>: <?php echo $vehicle_model; ?><br></h5>
        </div>
      </div>
      
      <div class="form-group row text-left">
    <div class="col-sm-3 text-primary">
        <h5>Encargado del vehículo</h5>
    </div>
    <div class="col-sm-9">
        <h5>: <?php echo ($FIRST_NAME && $LAST_NAME) ? $FIRST_NAME . ' ' . $LAST_NAME : 'No asignado'; ?><br></h5>
    </div>
    </div>

      <div class="form-group row text-left">
        <div class="col-sm-3 text-primary">
          <h5>Imagen del vehículo</h5>
        </div>
        <div class="col-sm-9">
          <?php
          // Display the image
          if ($vehicle_image) {
              echo '<img src="' . $vehicle_image . '" alt="Vehicle Image" style="max-width: 300px; max-height: 300px;">';
          } else {
              echo '<p>Sin imagen</p>';
          }
          ?>
        </div>
      </div>
      <?php
      }
      ?>
      <div class="form-group row text-left">
        <div class="col-sm-3 text-primary">
          <h5>Estado del vehículo</h5>
        </div>
        <div class="col-sm-9">
          <h5>: <?php echo ($v_estado == 1 ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>'); ?><br></h5>
        </div>
      </div>
    </div>
  </div>
</center>




<div class="card shadow mb-4 col-xs-12 col-md-15 border-bottom-primary">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Otros vehículos a cargo del personal</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>PLACA</th>
                        <th>MODELO</th>
                        <th>ENCARGADO</th>
                        <th>IMAGEN</th>
                        <th>ESTADO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = 'SELECT v.id, v.vehicle_license, v.vehicle_model, v.vehicle_attendant, v.vehicle_image, e.FIRST_NAME, e.LAST_NAME, v.v_estado
                              FROM vehicles v
                              LEFT JOIN employee e ON v.vehicle_attendant = e.EMPLOYEE_ID
                              WHERE e.EMPLOYEE_ID = (SELECT vehicle_attendant FROM vehicles WHERE id = ' . $_GET['id'] . ')';
                    $result = mysqli_query($db, $query) or die(mysqli_error($db));

                    while ($row = mysqli_fetch_assoc($result)) {
                        $id = $row['id'];
                        $vehicle_license = $row['vehicle_license'];
                        $vehicle_model = $row['vehicle_model'];
                        $vehicle_attendant = $row['vehicle_attendant'];
                        $FIRST_NAME = $row['FIRST_NAME'];
                        $LAST_NAME = $row['LAST_NAME'];
                        $vehicle_image = $row['vehicle_image'];
                        $v_estado = $row['v_estado'];

                        echo '<tr>';
                        echo '<td>' . $vehicle_license . '</td>';
                        echo '<td>' . $vehicle_model . '</td>';
                        echo '<td>' . (($FIRST_NAME && $LAST_NAME) ? $FIRST_NAME . ' ' . $LAST_NAME : 'No asignado') . '</td>';
                        echo '<td><img src="' . $vehicle_image . '" alt="Vehicle Image" style="max-width: 80px; max-height: 80px;"></td>';
                        echo '<td>' . ($v_estado == 1 ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>') . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>