<?php
include '../includes/connection.php';
include '../includes/sidebar_personal.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Admin' || $Aa == 'Estudiante' || $Aa == 'Docente' || $Aa == 'SuperAdmin') {
        if ($Aa == 'Admin') {
            $redirectUrl = "index.php";
        } elseif ($Aa == 'Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa == 'Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa == 'SuperAdmin') {
            $redirectUrl = "sa_vista.php";
        }
?>
        <script type="text/javascript">
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
<?php
        exit();
    }
}

// Obtener el ID del empleado correspondiente al nombre de usuario desde la base de datos
$queryEmployee = "SELECT EMPLOYEE_ID FROM employee WHERE FIRST_NAME = '{$_SESSION['FIRST_NAME']}' AND LAST_NAME = '{$_SESSION['LAST_NAME']}'";
$resultEmployee = mysqli_query($db, $queryEmployee);

// Verificar si se encontró el empleado en la base de datos
if (mysqli_num_rows($resultEmployee) > 0) {
    $rowEmployee = mysqli_fetch_assoc($resultEmployee);
    $employeeId = $rowEmployee['EMPLOYEE_ID'];

    // Consultar la base de datos para obtener los vehículos asignados al empleado y con v_estado = 1
    $queryVehicles = "SELECT v.id, v.vehicle_license, v.vehicle_model, v.vehicle_image, CONCAT(e.FIRST_NAME, ' ', e.LAST_NAME) AS vehicle_attendant
                      FROM vehicles v
                      LEFT JOIN employee e ON v.vehicle_attendant = e.EMPLOYEE_ID
                      WHERE v.vehicle_attendant = $employeeId AND v.v_estado = 1
                      ORDER BY v.id DESC";
    $resultVehicles = mysqli_query($db, $queryVehicles);

    // Verificar si se encontraron vehículos asignados
    if (mysqli_num_rows($resultVehicles) > 0) {
?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-2 font-weight-bold text-primary">Vehículos asignados</h4>
            </div>
            <div class="card-body">
                <div class="row">
                <?php
                while ($rowVehicle = mysqli_fetch_assoc($resultVehicles)) {
                    echo '<div class="col-lg-6 col-md-6 mb-4">';
                    echo '<div class="card h-100">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title"><strong>Placa del vehículo: </strong>' . $rowVehicle['vehicle_license'] . '</h5>';
                    echo '<h6 class="card-subtitle mb-2 text-muted"><strong>Modelo del vehículo: </strong>' . $rowVehicle['vehicle_model'] . '</h6>';
                    
                    // Mostrar la imagen del vehículo si existe
                    if (!empty($rowVehicle['vehicle_image'])) {
                        echo '<div class="text-center mb-3">';
                        echo '<img src="' . $rowVehicle['vehicle_image'] . '" alt="Imagen del vehículo" class="img-fluid rounded vehicle-image">';
                        echo '</div>';
                    }
                    
                    echo '<p class="card-text" style="color:navy"><strong>Estás a cargo de este vehículo, lee la información.</strong></p>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
                </div>
            </div>
        </div>
<?php
    } else {
        echo '<p>No se encontraron vehículos asignados.</p>';
    }
} else {
    echo '<p>Error: No se encontró el empleado en la base de datos.</p>';
}
?>

<?php
include '../includes/footer.php';
?>

<style>
.vehicle-image {
    width: 100%;
    max-width: 300px;
    height: auto;
    object-fit: cover;
    aspect-ratio: 4/3; /* Esto asegura una relación de aspecto consistente */
}
</style>
