<?php
include '../includes/connection.php';

$estudiante_id = $_POST['estudianteId'];
$doc_asi_id = $_POST['asignaturaId'];
$estado = $_POST['estado']; // Nuevo campo para el estado

// Consulta para obtener el ID del período
$query_periodo = "SELECT p.p_id
                  FROM periodo p
                  WHERE p.p_name = ?";

$stmt_periodo = mysqli_prepare($db, $query_periodo);
mysqli_stmt_bind_param($stmt_periodo, 's', $_POST['periodo']);
mysqli_stmt_execute($stmt_periodo);
mysqli_stmt_bind_result($stmt_periodo, $periodo_id);
mysqli_stmt_fetch($stmt_periodo);
mysqli_stmt_close($stmt_periodo);

if ($periodo_id) {
    // Modificamos la consulta para incluir el campo ed_estado
    $query = "INSERT INTO estudiantes_docentes (estudiante_id, doc_asi_id, periodo_id, ed_estado) 
              VALUES (?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'iiii', $estudiante_id, $doc_asi_id, $periodo_id, $estado);

    if (mysqli_stmt_execute($stmt)) {
        $alertTitle = "Añadido exitosamente";
        $alertText = "El estudiante ha sido asignado correctamente.";
        $alertIcon = "success";
    } else {
        $alertTitle = "Error";
        $alertText = "Hubo un error al intentar asignar el estudiante: " . mysqli_stmt_error($stmt);
        $alertIcon = "error";
    }
    
    mysqli_stmt_close($stmt);
} else {
    $alertTitle = "Error";
    $alertText = "No se encontró el período.";
    $alertIcon = "error";
}

mysqli_close($db);

// Mostrar SweetAlert
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
                window.location = 'sa_dis_asignaturas.php';
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
        font-family: 'Open Sans', sans-serif;
        font-weight: 600;
    }
</style>
";
?>
