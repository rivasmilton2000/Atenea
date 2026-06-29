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
$query2 = 'SELECT u.ID, e.nombres_estudiante, e.apellidos_estudiante, e.genero_estudiante, u.USERNAME, u.PASSWORD, e.correo_estudiante, e.direccion_estudiante, g.G_NAME, e.fecha_nac_estudiante, e.edad_estudiante, e.foto_estudiante, e.carnet_estudiante, e.numero_lista_estudiante, t.TYPE, u.U_ESTADO
FROM users u
JOIN estudiantes e ON u.ESTUDIANTE_ID = e.ESTUDIANTE_ID
JOIN grados g ON e.grado_id_estudiante = g.G_ID
JOIN type t ON u.TYPE_ID = t.TYPE_ID
WHERE u.ID = '.$_GET['id'];

$result2 = mysqli_query($db, $query2) or die(mysqli_error($db));
while ($row = mysqli_fetch_array($result2)) {   
$zz = $row['ID'];
$a = $row['nombres_estudiante'];
$b = $row['apellidos_estudiante'];
$c = $row['genero_estudiante'];
$d = $row['USERNAME'];
$e = $row['PASSWORD'];
$f = $row['correo_estudiante'];
$g = $row['direccion_estudiante'];
$h = $row['G_NAME'];
$i = $row['fecha_nac_estudiante'];
$j = $row['edad_estudiante'];
$k = $row['foto_estudiante'];
$l = $row['TYPE'];
$m = $row['U_ESTADO'];
$n = $row['carnet_estudiante'];
$o = $row['numero_lista_estudiante'];
}
$id = $_GET['id'];
?>
<center>
<div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
<div class="card-header py-3">
<h4 class="m-2 font-weight-bold text-primary">Detalles de la cuenta de estudiante</h4>
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
<h5 class="font-weight-bold text-primary mb-3">Información del estudiante</h5>
<hr>
<div class="form-group row text-left">
    <div class="col-sm-3 text-primary">
        <h5>Foto del estudiante:<br></h5>
    </div>
    <div class="col-sm-9">
        <?php
        $foto_path = "imagenes_estudiantes/" . $k;
        if (file_exists($foto_path) && !empty($k)) {
            echo '<img src="' . $foto_path . '" alt="Foto del estudiante" style="max-width: 200px; height: auto;">';
        } else {
            echo '<p>Estudiante sin foto</p>';
        }
        ?>
    </div>
</div>
<div class="form-group row text-left">
 <div class="col-sm-3 text-primary">
     <h5>Carnet del estudiante<br></h5>
 </div>
 <div class="col-sm-9">
     <h5>: <?php echo $n; ?> <br></h5>
 </div>
</div>
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
     <h5>Grado<br></h5>
 </div>
 <div class="col-sm-9">
     <h5>: <?php echo $h; ?> <br></h5>
 </div>
</div>
<div class="form-group row text-left">
 <div class="col-sm-3 text-primary">
     <h5>Número de lista<br></h5>
 </div>
 <div class="col-sm-9">
     <h5>: <?php echo $o; ?> <br></h5>
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