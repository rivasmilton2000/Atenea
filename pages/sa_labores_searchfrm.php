<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Personal' || $Aa == 'Estudiante' || $Aa == 'Docente' || $Aa == 'Admin') {
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
      <h4 class="m-2 font-weight-bold text-primary">Detalle del labor</h4>
    </div>
    <a href="sa_labores.php" type="button" class="btn btn-primary bg-gradient-primary btn-block">
      <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
    </a>
    <div class="card-body">
    <?php
$query = 'SELECT j.id, j.employee, j.job, j.description, j.status, j.hour, j.date, j.maxhour, j.maxdate, j.j_estado, e.FIRST_NAME, e.LAST_NAME
FROM jobs j
JOIN employee e ON j.employee = e.EMPLOYEE_ID
WHERE j.id = ' . $_GET['id'];
$result = mysqli_query($db, $query) or die(mysqli_error($db));

while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['id'];
    $emp = $row['employee'];
    $job = $row['job'];
    $des = $row['description'];
    $sta = $row['status'];
    $hou = $row['hour'];
    $dat = $row['date'];
    $mxh = $row['maxhour'];
    $mxd = date('d-m-Y', strtotime($row['maxdate']));
    $FIRST_NAME = $row['FIRST_NAME'];
    $LAST_NAME = $row['LAST_NAME'];
    $j_estado = $row['j_estado'];
    }
    ?>

      <div class="form-group row text-left">
        <div class="col-sm-3 text-primary">
          <h5>Labor<br></h5>
        </div>
        <div class="col-sm-9">
          <h5>: <?php echo $job; ?><br></h5>
        </div>
      </div>
      <div class="form-group row text-left">
        <div class="col-sm-3 text-primary">
          <h5>Descripción del labor<br></h5>
        </div>
        <div class="col-sm-9">
          <h5>: <?php echo $des; ?><br></h5>
        </div>
      </div>
      <div class="form-group row text-left">
        <div class="col-sm-3 text-primary">
            <h5>Encargado<br></h5>
        </div>
        <div class="col-sm-9">
            <h5>: <?php echo (!empty($FIRST_NAME) && !empty($LAST_NAME)) ? $FIRST_NAME . " " . $LAST_NAME : "No asignado"; ?><br></h5>
        </div>
      </div>
      <div class="form-group row text-left">
  <div class="col-sm-3 text-primary">
    <h5>Estado del labor<br></h5>
  </div>
  <div class="col-sm-9">
    <h5>: <?php
      if ($sta == 'Incompleto') {
        echo '<span style="color: darkorange;">' . $sta . '</span>';
      } elseif ($sta == 'Completado') {
        echo '<span style="color: green;">' . $sta . '</span>';
      } elseif ($sta == 'Tiempo Excedido') {
        echo '<span style="color: red;">' . $sta . '</span>';
      } else {
        echo $sta;
      }
    ?><br></h5>
  </div>
</div>

<div class="form-group row text-left">
  <div class="col-sm-3 text-primary">
    <h5>Fecha de finalización<br></h5>
  </div>
  <div class="col-sm-9">
    <h5>: <?php
      if ($sta == 'Incompleto') {
        echo '<span style="color: darkorange;">Incompleto</span>';
      } elseif ($sta == 'Completado') {
        echo '<span style="color: green;">' . date('d-m-Y', strtotime($dat)) . '</span>';
      } elseif ($sta == 'Tiempo Excedido') {
        echo '<span style="color: red;">' . date('d-m-Y', strtotime($dat)) . '</span>';
      } else {
        echo date('d-m-Y', strtotime($dat));
      }
    ?><br></h5>
  </div>
</div>

<div class="form-group row text-left">
  <div class="col-sm-3 text-primary">
    <h5>Hora de finalización<br></h5>
  </div>
  <div class="col-sm-9">
    <h5>: <?php
      if ($sta == 'Incompleto') {
        echo '<span style="color: darkorange;">Incompleto</span>';
      } elseif ($sta == 'Completado') {
        echo '<span style="color: green;">' . $hou . '</span>';
      } elseif ($sta == 'Tiempo Excedido') {
        echo '<span style="color: red;">' . $hou . '</span>';
      } else {
        echo $hou;
      }
    ?><br></h5>
  </div>
</div>
<div class="form-group row text-left">
  <div class="col-sm-3 text-primary">
    <h5>Fecha máxima para finalizar<br></h5>
  </div>
  <div class="col-sm-9">
    <h5>: <?php echo $mxd; ?><br></h5>
  </div>
</div>
      <div class="form-group row text-left">
        <div class="col-sm-3 text-primary">
          <h5>Hora máxima para finalizar<br></h5>
        </div>
        <div class="col-sm-9">
          <h5>: <?php echo $mxh; ?><br></h5>
        </div>
      </div>
      <div class="form-group row text-left">
  <div class="col-sm-3 text-primary">
    <h5>Estado del labor<br></h5>
  </div>
  <div class="col-sm-9">
    <h5>: <?php
      if ($j_estado == 1) {
        echo '<span class="badge badge-success">Activo</span>';
      } else {
        echo '<span class="badge badge-danger">Inactivo</span>';
      }
    ?><br></h5>
  </div>
</div>
    </div>
  </div>
</center>

<div class="card shadow mb-4 col-xs-12 col-md-15 border-bottom-primary">
  <div class="card-header py-3">
    <h4 class="m-2 font-weight-bold text-primary">Otros labores del empleado</h4>
  </div>
  <div class="card-body">
    <div class="table-responsive">
    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
  <thead>
    <tr>
      <th>LABOR</th>
      <th>ENCARGADO</th>
      <th>ESTADO</th>
      <th>FECHA DE FINALIZACIÓN</th>
      <th>HORA DE FINALIZACIÓN</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $query = 'SELECT j.id, j.employee, j.job, j.description, j.status, j.hour, j.date, j.maxhour, j.maxdate, e.FIRST_NAME, e.LAST_NAME
              FROM jobs j
              JOIN employee e ON j.employee = e.EMPLOYEE_ID
              WHERE e.EMPLOYEE_ID = (SELECT employee FROM jobs WHERE id = ' . $_GET['id'] . ')';
    $result = mysqli_query($db, $query) or die(mysqli_error($db));

    while ($row = mysqli_fetch_assoc($result)) {
      echo '<tr>';
      echo '<td>' . $row['job'] . '</td>';

      if ($row['FIRST_NAME'] && $row['LAST_NAME']) {
        echo '<td>' . $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'] . '</td>';
      } else {
        echo '<td>No asignado</td>';
      }

      // Determinar el color y el contenido basado en el estado
      $statusColor = '';
      $dateContent = '';
      $hourContent = '';
      switch ($row['status']) {
        case 'Completado':
          $statusColor = 'green';
          $dateContent = date('d-m-Y', strtotime($row['date']));
          $hourContent = $row['hour'];
          break;
        case 'Incompleto':
          $statusColor = 'darkorange';
          $dateContent = 'Incompleto';
          $hourContent = 'Incompleto';
          break;
        case 'Tiempo Excedido':
          $statusColor = 'red';
          $dateContent = date('d-m-Y', strtotime($row['date']));
          $hourContent = $row['hour'];
          break;
      }

      // Aplicar el color al estado, fecha y hora
      echo '<td style="color: ' . $statusColor . ';">' . $row['status'] . '</td>';
      echo '<td style="color: ' . $statusColor . ';">' . $dateContent . '</td>';
      echo '<td style="color: ' . $statusColor . ';">' . $hourContent . '</td>';

      echo '</tr>';
    }
    ?>
  </tbody>
</table>