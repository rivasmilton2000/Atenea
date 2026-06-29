<?php
include('../includes/connection.php');

// Iniciar logging
error_log("Iniciando proceso de actualización de labor");

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitizar las entradas
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $job = filter_input(INPUT_POST, 'job', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $maxhour = filter_input(INPUT_POST, 'maxhour', FILTER_SANITIZE_STRING);
    $maxdate = filter_input(INPUT_POST, 'maxdate', FILTER_SANITIZE_STRING);
    $j_estado = filter_input(INPUT_POST, 'j_estado', FILTER_SANITIZE_NUMBER_INT);

    // Logging de los valores recibidos
    error_log("Valores recibidos: " . 
              "ID: $id, " .
              "Labor: $job, " .
              "Descripción: $description, " .
              "Estado: $status, " .
              "Hora máxima: $maxhour, " .
              "Fecha máxima: $maxdate, " .
              "Estado del labor: $j_estado");

    // Convertir la fecha máxima (siempre debe tener un valor)
    $formatted_maxdate = date('Y-m-d', strtotime($maxdate));

    // Validar que la fecha no sea anterior a la fecha actual
    $current_date = date('Y-m-d');
    if ($formatted_maxdate < $current_date) {
        echo "
        <link href='https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap' rel='stylesheet'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script type='text/javascript'>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Fecha Inválida',
                    text: 'La fecha para este labor es inválida. Escoge otra fecha.',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'custom-popup-class',
                        title: 'custom-title-class',
                        confirmButton: 'custom-confirm-button-class'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location = 'sa_labores.php';
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
        exit();
    }

    // Preparar la consulta
    $query = "UPDATE jobs SET 
           job = ?, 
           description = ?, 
           status = ?, 
           maxhour = ?, 
           maxdate = ?, 
           j_estado = ? 
           WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);

    if ($stmt === false) {
        error_log("Error en la preparación de la consulta: " . mysqli_error($db));
        die("Error en la preparación de la consulta: " . mysqli_error($db));
    }

    // Vincular parámetros
    if (!mysqli_stmt_bind_param($stmt, "ssssssi", $job, $description, $status, $maxhour, $formatted_maxdate, $j_estado, $id)) {
        error_log("Error al vincular parámetros: " . mysqli_stmt_error($stmt));
        die("Error al vincular parámetros: " . mysqli_stmt_error($stmt));
    }

    // Ejecutar la consulta
    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        error_log("Filas afectadas: " . $affected_rows);
        if ($affected_rows > 0) {
            // Actualización exitosa
            error_log("Actualización exitosa para el labor ID: $id");
            echo "
            <link href='https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap' rel='stylesheet'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script type='text/javascript'>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Éxito',
                        text: 'Trabajo actualizado correctamente.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'custom-popup-class',
                            title: 'custom-title-class',
                            confirmButton: 'custom-confirm-button-class'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location = 'sa_labores.php';
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
        } else {
            $error = "No se encontró un labor con el ID especificado, o no se realizaron cambios.";
            error_log($error);
            echo "
            <link href='https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap' rel='stylesheet'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script type='text/javascript'>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Sin cambios',
                        text: 'No se encontraron cambios para guardar.',
                        icon: 'info',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'custom-popup-class',
                            title: 'custom-title-class',
                            confirmButton: 'custom-confirm-button-class'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location = 'sa_labores.php';
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
    } else {
        $error = "Error al ejecutar la consulta: " . mysqli_stmt_error($stmt);
        error_log($error);
        echo "
        <link href='https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap' rel='stylesheet'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script type='text/javascript'>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error',
                    text: 'Error al ejecutar la consulta: " . mysqli_stmt_error($stmt) . "',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'custom-popup-class',
                        title: 'custom-title-class',
                        confirmButton: 'custom-confirm-button-class'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location = 'sa_labores.php';
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

    mysqli_stmt_close($stmt);
    mysqli_close($db);
} else {
    // Si no se ha enviado el formulario, redirigir
    error_log("Intento de acceso directo a sa_labores_edit1.php sin envío de formulario");
    header("Location: sa_labores.php");
    exit();
}
?>
