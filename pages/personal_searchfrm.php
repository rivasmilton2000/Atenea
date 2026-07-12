<?php
include'../includes/connection.php';

include'../includes/sidebar_admin.php';
  
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa=='Personal' || $Aa=='Estudiante' || $Aa=='Docente' || $Aa=='SuperAdmin'){
      if ($Aa=='Personal') {
          $redirectUrl = "empleados_vista.php";
      } elseif ($Aa=='Estudiante') {
          $redirectUrl = "estudiante_vista.php";
      } elseif ($Aa=='Docente') {
          $redirectUrl = "docentes_vista.php";
      } elseif ($Aa=='SuperAdmin') {
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

// Verificar si el identificador del empleado existe y es válido
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    
    $query2 = 'SELECT e.EMPLOYEE_ID, e.FIRST_NAME, e.LAST_NAME, e.GENDER, e.EMAIL, e.PHONE_NUMBER, j.JOB_TITLE, e.HIRED_DATE, t.TYPE, l.PROVINCE, l.CITY 
               FROM employee e
               LEFT JOIN users u ON u.EMPLOYEE_ID = e.EMPLOYEE_ID
               LEFT JOIN job j ON e.JOB_ID = j.JOB_ID
               LEFT JOIN location l ON e.LOCATION_ID = l.LOCATION_ID
               LEFT JOIN type t ON u.TYPE_ID = t.TYPE_ID
               WHERE e.EMPLOYEE_ID = '.$id;

    $result2 = mysqli_query($db, $query2) or die(mysqli_error($db));

    // Inicializar las variables con valores vacíos
    $a = $b = $c = $f = $g = $h = $i = $j = $k = $l = '';

    if (mysqli_num_rows($result2) > 0) {
        while($row = mysqli_fetch_array($result2)) {
            $zz= $row['EMPLOYEE_ID'];
            $a = $row['FIRST_NAME'];
            $b = $row['LAST_NAME'];
            $c = $row['GENDER'];
            $f = $row['EMAIL'];
            $g = $row['PHONE_NUMBER'];
            $h = $row['JOB_TITLE'];
            $i = $row['HIRED_DATE'];
            $j = $row['PROVINCE'];
            $k = $row['CITY'];
            $l = $row['TYPE'];
        }
    }
} else {
    // Manejar el caso cuando el identificador del empleado no está presente o es inválido
    echo "Identificador de empleado inválido.";
    exit;
}
?>
<!-- Resto del código HTML -->
          <center><div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
            <div class="card-header py-3">
              <h4 class="m-2 font-weight-bold text-primary">Detalles de <?php echo $a; ?></h4>
            </div>
            <a href="personal.php?action=add" type="button" class="btn btn-primary bg-gradient-primary"><i class="fas fa-flip-horizontal fa-fw fa-share"></i>Regresar</a>
            <div class="card-body">
                
                    <div class="form-group row text-left">
                      <div class="col-sm-3 text-primary">
                        <h5>
                          Nombre completo<br>
                        </h5>
                      </div>
                      <div class="col-sm-9">
                        <h5>
                          : <?php echo $a; ?> <?php echo $b; ?> <br>
                        </h5>
                      </div>
                    </div>

                    <div class="form-group row text-left">
                      <div class="col-sm-3 text-primary">
                        <h5>
                          Género<br>
                        </h5>
                      </div>
                      <div class="col-sm-9">
                        <h5>
                          : <?php echo $c; ?> <br>
                        </h5>
                      </div>
                    </div>

                    <div class="form-group row text-left">
                      <div class="col-sm-3 text-primary">
                        <h5>
                          Correo electrónico<br>
                        </h5>
                      </div>
                      <div class="col-sm-9">
                        <h5>
                          : <?php echo $f; ?> <br>
                        </h5>
                      </div>
                    </div>

                    <div class="form-group row text-left">
                      <div class="col-sm-3 text-primary">
                        <h5>
                        Número teléfonico<br>
                        </h5>
                      </div>
                      <div class="col-sm-9">
                        <h5>
                          : <?php echo $g; ?> <br>
                        </h5>
                      </div>
                    </div>
                    
                    <div class="form-group row text-left">
                      <div class="col-sm-3 text-primary">
                        <h5>
                          Dirección<br>
                        </h5>
                      </div>
                      <div class="col-sm-9">
                        <h5>
                          : <?php echo $j; ?>, <?php echo $k; ?> <br>
                        </h5>
                      </div>
                    </div>

                    <div class="form-group row text-left">
                      <div class="col-sm-3 text-primary">
                        <h5>
                          Rol<br>
                        </h5>
                      </div>
                      <div class="col-sm-9">
                        <h5>
                          : <?php echo $h; ?> <br>
                        </h5>
                      </div>
                    </div>
                    <div class="form-group row text-left">
                      <div class="col-sm-3 text-primary">
                        <h5>
                        Fecha de contratación<br>
                        </h5>
                      </div>
                      <div class="col-sm-9">
                        <h5>
                        <h5>: <?php echo date("d-m-Y", strtotime($i)); ?> <br></h5>
                        </h5>
                      </div>
                    </div>
                    
                    
          </div>
          </div>

<?php
include'../includes/footer.php';
?>