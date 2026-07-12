<?php
include'../includes/connection.php';

include'../includes/topp.php';
  $query = 'SELECT ID, t.TYPE
            FROM users u
            JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
  $result = mysqli_query($db, $query) or die (mysqli_error($db));
  
  while ($row = mysqli_fetch_assoc($result)) {
            $Aa = $row['TYPE'];
                   
  if ($Aa=='User'){
?>
  <!-- <script type="text/javascript">
    //then it will be redirected
    alert("Restricted Page! You will be redirected to POS");
    window.location = "pos.php";
  </script> -->
<?php
  }           
}
$sql = "SELECT DISTINCT FIRST_NAME, LAST_NAME, EMPLOYEE_ID FROM employee order by FIRST_NAME asc";
$result = mysqli_query($db, $sql) or die ("Bad SQL: $sql");

$emp = "<select class='form-control' name='employee_jobs' required>
        <option disabled selected hidden>Seleccionar empleado</option>";
  while ($row = mysqli_fetch_assoc($result)) {
    $emp .= "<option value='".$row['EMPLOYEE_ID']."'>".$row['FIRST_NAME']." ".$row['LAST_NAME']."</option>";
  }

$emp .= "</select>";

$sql2 = "SELECT DISTINCT EMPLOYEE_ID, FIRST_NAME, LAST_NAME FROM employee order by EMPLOYEE_ID asc";
$result2 = mysqli_query($db, $sql2) or die ("Bad SQL: $sql2");

$sup = "<select class='form-control' name='employee' required>
        <option disabled selected hidden>Seleccionar empleado</option>";
  while ($row = mysqli_fetch_assoc($result2)) {
    $sup .= "<option value='".$row['EMPLOYEE_ID']."'>".$row['FIRST_NAME']."</option>";
  }

$sup .= "</select>";
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Trabajos&nbsp;</h4>
    </div>
    <div class="card-body">
    <?php

    // Establecer la zona horaria de El Salvador
    date_default_timezone_set('America/El_Salvador');

    // Obtener el ID del empleado correspondiente al nombre de usuario desde la base de datos
    $queryEmployee = "SELECT EMPLOYEE_ID FROM employee WHERE FIRST_NAME = '{$_SESSION['FIRST_NAME']}' AND LAST_NAME = '{$_SESSION['LAST_NAME']}'";
    $resultEmployee = mysqli_query($db, $queryEmployee);

    // Verificar si se encontró el empleado en la base de datos
    if (mysqli_num_rows($resultEmployee) > 0) {
        $rowEmployee = mysqli_fetch_assoc($resultEmployee);
        $employeeId = $rowEmployee['EMPLOYEE_ID'];

        // Consultar la base de datos para obtener los trabajos asignados al empleado
        $queryJobs = "SELECT id, job, description, status, maxhour, maxdate FROM jobs WHERE employee = '$employeeId'";
        $resultJobs = mysqli_query($db, $queryJobs);

        // Verificar si se encontraron trabajos asignados
        if (mysqli_num_rows($resultJobs) > 0) {
            // Mostrar los trabajos asignados en cartas individuales
            while ($rowJob = mysqli_fetch_assoc($resultJobs)) {
                echo '<div class="card mb-3">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title"><strong>Trabajo: </strong>' . $rowJob['job'] . '</h5>';
                echo '<p class="card-text"><strong>Descripción: </strong> ' . $rowJob['description'] . '</p>';
                echo '<p class="card-text"><strong>Estado: </strong> ' . $rowJob['status'] . '</p>';
                echo '<p class="card-text"><strong>Hora máxima:</strong> ' . date('H:i', strtotime($rowJob['maxhour'])) . '</p>';
                echo '<p class="card-text"><strong>Fecha máxima:</strong> ' . $rowJob['maxdate'] . '</p>';
                echo '<form method="POST" action="update_job.php" onsubmit="return confirmAndSubmit(event, \'' . $rowJob['maxhour'] . '\', \'' . $rowJob['maxdate'] . '\')">';
                echo '<input type="hidden" name="id" value="' . $rowJob['id'] . '">';
                echo '<input type="hidden" name="status" value="' . $rowJob['status'] . '">';
                echo '<input type="hidden" name="hour" value="' . date('H:i') . '">';
                echo '<input type="hidden" name="date" value="' . date('Y-m-d') . '">';
                echo '<button class="btn btn-info" type="submit">Finalizar</button>';
                echo '</form>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p>No se encontraron trabajos asignados.</p>';
        }
    } else {
        echo '<p>Error: No se encontró el empleado en la base de datos.</p>';
    }
    ?>
    </div>
</div>

<script>
function confirmAndSubmit(event, maxHour, maxDate) {
    // Obtener el formulario y los valores de fecha/hora ingresados por el usuario
    var form = event.target;
    var hourInput = form.querySelector('input[name="hour"]');
    var dateInput = form.querySelector('input[name="date"]');
    var statusInput = form.querySelector('input[name="status"]');

    // Convertir las fechas y horas a objetos de fecha para facilitar la comparación
    var maxDateTime = new Date(maxDate + 'T' + maxHour + ':00');
    var userDateTime = new Date(dateInput.value + 'T' + hourInput.value + ':00');

    // Comparar las fechas y horas
    if (userDateTime > maxDateTime) {
        // Si la fecha/hora ingresada es posterior a la fecha/hora máxima, actualizar el campo "status" a "Finalizado Tarde"
        statusInput.value = 'Tiempo Excedido';
    } else {
       //Continuación:

        // Si la fecha/hora ingresada es anterior o igual a la fecha/hora máxima, mantener el valor actual del campo "status"
        // Esto evita que se sobrescriba el estado si el trabajo ya ha sido marcado como "Finalizado Tarde"
    }

    // Mostrar una confirmación al usuario antes de enviar el formulario
    var confirmMessage = '¿Está seguro de que desea finalizar este trabajo?';
    if (statusInput.value === 'Tiempo Excedido') {
        confirmMessage += '\nEste trabajo se está finalizando tarde.';
    }
    return confirm(confirmMessage);
}
</script>

<?php
include '../includes/footer.php';
?>