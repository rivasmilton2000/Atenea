<?php 
include '../includes/connection.php'; 

$ej = $_POST['employee_jobs'];
$jj = $_POST['job_jobs'];
$dj = $_POST['description_jobs'];
$mhj = $_POST['maxhour_jobs'];
$mdj = $_POST['maxdate_jobs'];

if ($_GET['action'] === 'add') {
    $status = 'Incompleto';
    $hora = 'Incompleto';
    $fecha = 'Incompleto';
    $current_date = date('Y-m-d');

    // Validar que la fecha no sea anterior a la fecha actual
    if ($mdj < $current_date) {
        $alertTitle = 'Fecha Inválida';
        $alertText = 'La fecha para este labor es inválida. Escoge otra fecha.';
        $alertIcon = 'warning';
    } else {
        // Verificar si existe un trabajo asignado al mismo empleado en la misma hora y fecha
        $checkQuery = "SELECT * FROM jobs WHERE employee = ? AND (hour = ? OR (hour IS NULL AND ? IS NULL)) AND (date = ? OR (date IS NULL AND ? IS NULL))";
        $stmt = mysqli_prepare($db, $checkQuery);
        mysqli_stmt_bind_param($stmt, "sssss", $ej, $mhj, $mhj, $mdj, $mdj);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $alertTitle = 'Trabajo Duplicado';
            $alertText = 'Ya existe un trabajo asignado a este empleado en la misma hora y fecha.';
            $alertIcon = 'warning';
        } else {
            // Insertar el nuevo trabajo con 'Incompleto' en los campos hour y date
            $insertJobQuery = "INSERT INTO jobs (employee, job, description, status, hour, date, maxhour, maxdate) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db, $insertJobQuery);
            mysqli_stmt_bind_param($stmt, "ssssssss", $ej, $jj, $dj, $status, $hora, $fecha, $mhj, $mdj);
            
            if (mysqli_stmt_execute($stmt)) {
                $alertTitle = 'Éxito';
                $alertText = 'Labor añadido correctamente.';
                $alertIcon = 'success';
            } else {
                $alertTitle = 'Error';
                $alertText = 'Error al insertar el trabajo en la base de datos: ' . mysqli_error($db);
                $alertIcon = 'error';
            }
        }
    }

    echo "
    <link href='https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap' rel='stylesheet'>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script type='text/javascript'>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '" . $alertTitle . "',
                text: '" . $alertText . "',
                icon: '" . $alertIcon . "',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'custom-popup-class',
                    title: 'custom-title-class',
                    confirmButton: 'custom-confirm-button-class'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = 'labores.php';
                }
            });
        });
    </script>
    <style>
        .custom-popup-class {
            font-family: 'Open Sans', sans-serif;
        }
        .custom-title-class {
            font-family: 'Open Sans', sans-serif;
            font-weight: 700;
        }
        .custom-confirm-button-class {
            font-family: 'Open Sans', sans-serif';
            font-weight: 600;
        }
    </style>
    ";
}

mysqli_close($db);
?>
