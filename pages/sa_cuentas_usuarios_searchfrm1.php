<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

// Sección de verificación de permisos de usuario
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
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

<?php
$query2 = 'SELECT ID, e.FIRST_NAME, e.LAST_NAME, e.GENDER, USERNAME, PASSWORD, e.EMAIL, PHONE_NUMBER, j.JOB_TITLE, e.HIRED_DATE, t.TYPE, l.PROVINCE, l.CITY, u.U_ESTADO
FROM users u
join employee e on u.EMPLOYEE_ID = e.EMPLOYEE_ID
join job j on e.JOB_ID=j.JOB_ID
join location l on e.LOCATION_ID=l.LOCATION_ID
join type t on u.TYPE_ID=t.TYPE_ID
WHERE ID ='.$_GET['id'];

$result2 = mysqli_query($db, $query2) or die(mysqli_error($db));
while ($row = mysqli_fetch_array($result2)) {   
$zz = $row['ID'];
$a = $row['FIRST_NAME'];
$b = $row['LAST_NAME'];
$c = $row['GENDER'];
$d = $row['USERNAME'];
$e = $row['PASSWORD'];
$f = $row['EMAIL'];
$g = $row['PHONE_NUMBER'];
$h = $row['JOB_TITLE'];
$i = $row['HIRED_DATE'];
$j = $row['PROVINCE'];
$k = $row['CITY'];
$l = $row['TYPE'];
$m = $row['U_ESTADO'];
}
$id = $_GET['id'];
?>
<center>
<div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
<div class="card-header py-3">
 <h4 class="m-2 font-weight-bold text-primary">Detalles de la cuenta y usuario</h4>
</div>
<a href="sa_cuentas_usuarios.php" type="button" class="btn btn-primary bg-gradient-primary"><i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar</a>
<div class="card-body">
 <!-- Información de la cuenta -->
 <h5 class="font-weight-bold text-primary mb-3">Información de la cuenta</h5>
 <hr>
 <div class="form-group row text-left">
     <div class="col-sm-3 text-primary">
         <h5>Usuario<br></h5>
     </div>
     <div class="col-sm-9">
         <h5>: <?php echo $d; ?> <br></h5>
     </div>
 </div>
 <div class="form-group row text-left">
     <div class="col-sm-3 text-primary">
         <h5>Tipo de cuenta<br></h5>
     </div>
     <div class="col-sm-9">
         <h5>: <?php echo $l; ?> <br></h5>
     </div>
 </div>
 <br>
 <!-- Información del usuario -->
 <h5 class="font-weight-bold text-primary mb-3">Información del usuario</h5>
 <hr>
 <div class="form-group row text-left">
     <div class="col-sm-3 text-primary">
         <h5>Nombre completo<br></h5>
     </div>
     <div class="col-sm-9">
         <h5>: <?php echo $a; ?> <?php echo $b; ?> <br></h5>
     </div>
 </div>
 <div class="form-group row text-left">
     <div class="col-sm-3 text-primary">
         <h5>Correo electrónico<br></h5>
     </div>
     <div class="col-sm-9">
         <h5>: <?php echo $f; ?> <br></h5>
     </div>
 </div>
 <div class="form-group row text-left">
     <div class="col-sm-3 text-primary">
         <h5>Contacto telefónico<br></h5>
     </div>
     <div class="col-sm-9">
         <h5>: <?php echo $g; ?> <br></h5>
     </div>
 </div>
 <div class="form-group row text-left">
     <div class="col-sm-3 text-primary">
         <h5>Rol<br></h5>
     </div>
     <div class="col-sm-9">
         <h5>: <?php echo $h; ?> <br></h5>
     </div>
 </div>
 <br>
 <!-- Estado del registro -->
 <h5 class="font-weight-bold text-primary mb-3">Estado de la cuenta</h5>
 <hr>
 <div class="form-group row text-left">
     <div class="col-sm-3 text-primary">
         <h5>Estado<br></h5>
     </div>
     <div class="col-sm-9">
         <h5>: 
         <?php 
         if($m == 1) {
             echo '<span class="badge badge-success">Activo</span>';
         } else {
             echo '<span class="badge badge-danger">Inactivo</span>';
         }
         ?>
         <br></h5>
     </div>
 </div>
</div>
</div>
</center>

<?php
include '../includes/footer_superadmin.php';
?>