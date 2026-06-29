<?php
include '../includes/connection.php';

$ed_id = $_POST['ed_id'];
$estudiante_id = $_POST['estudiante_id'];
$asignatura_id = $_POST['asignatura'];
$periodo_id = $_POST['periodo_id'];
$ed_estado = $_POST['ed_estado']; // Nuevo campo para el estado

// Obtener los valores actuales de la base de datos
$query_current = 'SELECT doc_asi_id, periodo_id, ed_estado FROM estudiantes_docentes WHERE ed_id = ? AND estudiante_id = ?';
$stmt_current = mysqli_prepare($db, $query_current);
mysqli_stmt_bind_param($stmt_current, "ii", $ed_id, $estudiante_id);
mysqli_stmt_execute($stmt_current);
mysqli_stmt_bind_result($stmt_current, $current_asignatura_id, $current_periodo_id, $current_ed_estado);
mysqli_stmt_fetch($stmt_current);
mysqli_stmt_close($stmt_current);

// Verificar si se realizaron cambios
if ($asignatura_id == $current_asignatura_id && $periodo_id == $current_periodo_id && $ed_estado == $current_ed_estado) {
    // No se realizaron cambios
    echo "
    <link href='https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap' rel='stylesheet'>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script type='text/javascript'>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Sin cambios',
                text: 'No se realizaron cambios en la asignación.',
                icon: 'warning',
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
        .custom-popup-class, .custom-title-class, .custom-confirm-button-class, .custom-cancel-button-class {
            font-family: 'Open Sans', sans-serif;
        }
        .custom-title-class {
            font-weight: 700;
        }
        .custom-confirm-button-class, .custom-cancel-button-class {
            font-weight: 600;
        }
    </style>
    ";
    mysqli_close($db);
    exit();
}

// Modificamos la consulta para incluir el campo ed_estado
$query = 'UPDATE estudiantes_docentes SET doc_asi_id = ?, periodo_id = ?, ed_estado = ? WHERE ed_id = ? AND estudiante_id = ?';
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "iiiii", $asignatura_id, $periodo_id, $ed_estado, $ed_id, $estudiante_id);
mysqli_stmt_execute($stmt);

// Verificar si la actualización fue exitosa
$affected_rows = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);
mysqli_close($db);

// Redirigir con SweetAlert2
echo "
<link href='https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap' rel='stylesheet'>
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<script type='text/javascript'>
    document.addEventListener('DOMContentLoaded', function() {";

if($affected_rows > 0) {
    // La actualización fue exitosa
    echo "
        Swal.fire({
            title: '¡Actualizado!',
            text: 'La asignación se actualizó correctamente.',
            icon: 'success',
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
    ";
} else {
    // No se realizó ningún cambio, pero esta sección nunca debería alcanzarse debido a la verificación anterior
    echo "
        Swal.fire({
            title: 'Error',
            text: 'Ocurrió un error al actualizar la asignación. Por favor, inténtelo de nuevo.',
            icon: 'error',
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
    ";
}

echo "
    });
</script>
<style>
    .custom-popup-class, .custom-title-class, .custom-confirm-button-class, .custom-cancel-button-class {
        font-family: 'Open Sans', sans-serif;
    }
    .custom-title-class {
        font-weight: 700;
    }
    .custom-confirm-button-class, .custom-cancel-button-class {
        font-weight: 600;
    }
</style>
";
?>
