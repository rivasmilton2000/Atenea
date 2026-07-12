<?php
include '../includes/connection.php';
include '../includes/sidebar_personal.php';

// Obtener el tipo de usuario
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

    // Configuración de la paginación
    $recordsPerPage = 3;
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($currentPage - 1) * $recordsPerPage;

    // Consultar la base de datos para obtener los trabajos asignados al empleado
    $queryJobs = "
        SELECT id, job, description, status, hour, date, maxhour, maxdate 
        FROM jobs 
        WHERE employee = '$employeeId' AND j_estado = 1
        ORDER BY FIELD(status, 'Incompleto', 'Tiempo Excedido', 'Completado') 
        LIMIT $offset, $recordsPerPage";
    $resultJobs = mysqli_query($db, $queryJobs);

    // Obtener el número total de registros
    $queryTotal = "SELECT COUNT(*) AS total FROM jobs WHERE employee = '$employeeId' AND j_estado = 1";
    $resultTotal = mysqli_query($db, $queryTotal);
    $rowTotal = mysqli_fetch_assoc($resultTotal);
    $totalRecords = $rowTotal['total'];
    $totalPages = ceil($totalRecords / $recordsPerPage);

    // Verificar si se encontraron trabajos asignados
    if (mysqli_num_rows($resultJobs) > 0) {
?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-2 font-weight-bold text-primary">Labores asignados</h4>
            </div>
            <div class="card-body">
                <?php
                while ($rowJob = mysqli_fetch_assoc($resultJobs)) {
                    // Convertir las fechas al formato día-mes-año
                    $maxDateObject = DateTime::createFromFormat('Y-m-d', $rowJob['maxdate']);
                    $jobDateObject = DateTime::createFromFormat('Y-m-d', $rowJob['date']);
                    
                    $maxDate = $maxDateObject ? $maxDateObject->format('d-m-Y') : $rowJob['maxdate'];
                    $jobDate = $jobDateObject ? $jobDateObject->format('d-m-Y') : $rowJob['date'];
                
                    echo '<div class="card mb-3">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title"><strong>Trabajo: </strong>' . $rowJob['job'] . '</h5>';
                    echo '<p class="card-text"><strong>Descripción: </strong> ' . $rowJob['description'] . '</p>';
                
                    // Aplicar estilos de color según el valor del campo "status"
                    $statusColor = '';
                    switch ($rowJob['status']) {
                        case 'Incompleto':
                            $statusColor = 'color: red;';
                            break;
                        case 'Tiempo Excedido':
                            $statusColor = 'color: darkorange;';
                            break;
                        case 'Completado':
                            $statusColor = 'color: green;';
                            break;
                        default:
                            $statusColor = '';
                            break;
                    }
                
                    echo '<p class="card-text" style="' . $statusColor . '"><strong>Estado: </strong> ' . $rowJob['status'] . '</p>';
                    echo '<p class="card-text"><strong>Hora máxima:</strong> ' . date('H:i', strtotime($rowJob['maxhour'])) . '</p>';
                    echo '<p class="card-text"><strong>Fecha máxima:</strong> ' . $maxDate . '</p>';
                
                    echo '<div class="btn-group">';
                
                    // Agregar condición para mostrar el formulario solo si el estado es "Incompleto"
                    if ($rowJob['status'] == 'Incompleto') {
                        echo '<form method="POST" action="empleados_vista_update.php" onsubmit="return confirmAndSubmit(event, \'' . $rowJob['maxhour'] . '\', \'' . $rowJob['maxdate'] . '\')">';
                        echo '<input type="hidden" name="id" value="' . $rowJob['id'] . '">';
                        echo '<input type="hidden" name="status" value="' . $rowJob['status'] . '">';
                        echo '<input type="hidden" name="hour" value="' . date('H:i') . '">';
                        echo '<input type="hidden" name="date" value="' . date('Y-m-d') . '">';
                        echo '<button class="btn btn-info" type="submit">Finalizar</button>';
                        echo '</form>';
                    } else {
                        echo '<p style="color:navy"><strong>Este trabajo ya ha sido finalizado.</strong></p>';
                        echo '<p style="color:navy">‎ Fecha: <strong>' . $jobDate . '</strong> Hora: <strong>' . $rowJob['hour'] . '</strong></p>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }                
                ?>

                <!-- Paginación -->
                <nav aria-label="Page navigation example">
                    <ul class="pagination">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>">Anterior</a>
                            </li>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>">Siguiente</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
<?php
    } else {
        echo '<p>No se encontraron trabajos asignados.</p>';
    }
} else {
    echo '<p>Error: No se encontró el empleado en la base de datos.</p>';
}
?>

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

            // Mostrar una alerta de confirmación al usuario antes de enviar el formulario
            var confirmMessage = '¿Está seguro de que desea finalizar este trabajo?';
            confirmMessage += '\nEste trabajo se está finalizando tarde.';
            if (!confirm(confirmMessage)) {
                // Si el usuario cancela la confirmación, detener el envío del formulario
                event.preventDefault();
            }
        } else {
            // Si la fecha/hora ingresada es anterior o igual a la fecha/hora máxima, mantener el valor actual del campo "status"
                        // Esto evita que se sobrescriba el estado si el trabajo ya ha sido marcado como "Finalizado Tarde"

            // Mostrar una alerta de confirmación estándar al usuario antes de enviar el formulario
            var confirmMessage = '¿Está seguro de que desea finalizar este trabajo?';
            if (!confirm(confirmMessage)) {
                // Si el usuario cancela la confirmación, detener el envío del formulario
                event.preventDefault();
            }
        }
    }
</script>

<?php
include '../includes/footer.php';
?>