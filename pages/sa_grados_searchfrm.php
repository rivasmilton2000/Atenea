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
<center><div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
  <div class="card-header py-3">
    <h4 class="m-2 font-weight-bold text-primary">Detalles del grado</h4>
  </div>
  <a href="sa_grados.php" type="button" class="btn btn-primary bg-gradient-primary btn-block"> <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar</a>
  <div class="card-body">
    <?php 
    $query = 'SELECT G_ID, G_NAME, G_ESTADO FROM grados WHERE G_ID ='.$_GET['id'];
    $result = mysqli_query($db, $query) or die(mysqli_error($db));
    while($row = mysqli_fetch_array($result))
    {   
      $zz= $row['G_ID'];
      $zzz= $row['G_NAME'];
      $status = $row['G_ESTADO'];
    }
    $id = $_GET['id'];
    ?>

    <div class="form-group row text-left">
      <div class="col-sm-3 text-primary">
        <h5>
          ID<br>
        </h5>
      </div>
      <div class="col-sm-9">
        <h5>
          : <?php echo $zz; ?><br>
        </h5>
      </div>
    </div>
    <div class="form-group row text-left">
      <div class="col-sm-3 text-primary">
        <h5>
          Grado<br>
        </h5>
      </div>
      <div class="col-sm-9">
        <h5>
          : <?php echo $zzz; ?> <br>
        </h5>
      </div>
    </div>
    <div class="form-group row text-left">
      <div class="col-sm-3 text-primary">
        <h5>
          Estado<br>
        </h5>
      </div>
      <div class="col-sm-9">
        <h5>
          : <?php echo ($status == 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>'; ?> <br>
        </h5>
      </div>
    </div>
  </div>
</div></center>

</div>
</div>

<?php
include '../includes/footer_superadmin.php';
?>