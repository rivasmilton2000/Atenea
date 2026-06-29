<?php
include('../includes/connection.php');

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitizar las entradas
    $zz = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $a = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING);
    $b = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING);
    $c = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
    $f = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $g = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $i = filter_input(INPUT_POST, 'hireddate', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_NUMBER_INT);
    $location_id = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_NUMBER_INT);

    // Validar dominio del correo electrónico
    $allowed_domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
    $email_domain = substr(strrchr($f, "@"), 1);

    if (!in_array($email_domain, $allowed_domains)) {
        $alertTitle = 'Correo Inválido';
        $alertText = 'Dominio de correo electrónico inválido';
        $alertIcon = 'warning';
    } else {
        // Verificar si el teléfono ya existe (excluyendo el del docente actual)
        $stmt = $db->prepare("SELECT COUNT(*) FROM employee WHERE PHONE_NUMBER = ? AND EMPLOYEE_ID != ?");
        $stmt->bind_param("si", $g, $zz);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $alertTitle = 'Número Existente';
            $alertText = 'El número de teléfono ' . htmlspecialchars($g) . ' ya existe en el sistema. Por favor, ingrese un número diferente.';
            $alertIcon = 'warning';
        } else {
            // Verificamos si el correo ya existe (excluyendo el del docente actual)
            $stmt = $db->prepare("SELECT COUNT(*) FROM employee WHERE EMAIL = ? AND EMPLOYEE_ID != ?");
            $stmt->bind_param("si", $f, $zz);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                $alertTitle = 'Correo Existente';
                $alertText = 'El correo ' . htmlspecialchars($f) . ' ya existe en el sistema. Por favor, ingrese un correo diferente.';
                $alertIcon = 'warning';
            } else {
                // Verificar si se ha realizado algún cambio
                $stmt = $db->prepare("SELECT * FROM employee WHERE EMPLOYEE_ID = ?");
                $stmt->bind_param("i", $zz);
                $stmt->execute();
                $result = $stmt->get_result();
                $old_data = $result->fetch_assoc();
                $stmt->close();

                if ($old_data['FIRST_NAME'] == $a && 
                    $old_data['LAST_NAME'] == $b && 
                    $old_data['GENDER'] == $c && 
                    $old_data['EMAIL'] == $f && 
                    $old_data['PHONE_NUMBER'] == $g && 
                    $old_data['HIRED_DATE'] == $i && 
                    $old_data['LOCATION_ID'] == $location_id && 
                    $old_data['E_ESTADO'] == $status) {
                    $alertTitle = 'Sin Cambios';
                    $alertText = 'No se han realizado cambios en los datos del docente.';
                    $alertIcon = 'info';
                } else {
                    // Preparar la consulta
                    $query = 'UPDATE employee SET FIRST_NAME = ?, LAST_NAME = ?, GENDER = ?, EMAIL = ?, LOCATION_ID = ?, PHONE_NUMBER = ?, HIRED_DATE = ?, E_ESTADO = ? WHERE EMPLOYEE_ID = ?';
                    $stmt = mysqli_prepare($db, $query);

                    if ($stmt === false) {
                        $alertTitle = 'Error';
                        $alertText = 'Error en la preparación de la consulta: ' . mysqli_error($db);
                        $alertIcon = 'error';
                    } else {
                        // Vincular parámetros
                        if (!mysqli_stmt_bind_param($stmt, "ssssissii", $a, $b, $c, $f, $location_id, $g, $i, $status, $zz)) {
                            $alertTitle = 'Error';
                            $alertText = 'Error al vincular parámetros: ' . mysqli_stmt_error($stmt);
                            $alertIcon = 'error';
                        } else {
                            // Ejecutar la consulta
                            if (mysqli_stmt_execute($stmt)) {
                                $affected_rows = mysqli_stmt_affected_rows($stmt);
                                if ($affected_rows > 0) {
                                    $alertTitle = 'Éxito';
                                    $alertText = 'Docente actualizado exitosamente.';
                                    $alertIcon = 'success';
                                } else {
                                    $alertTitle = 'Sin Cambios';
                                    $alertText = 'No se encontró un docente con el ID especificado, o no se realizaron cambios.';
                                    $alertIcon = 'info';
                                }
                            } else {
                                $alertTitle = 'Error';
                                $alertText = 'Error al ejecutar la consulta: ' . mysqli_stmt_error($stmt);
                                $alertIcon = 'error';
                            }
                        }
                        mysqli_stmt_close($stmt);
                    }
                }
            }
        }
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
                    window.location = 'sa_docentes.php';
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
} else {
    // Si no se ha enviado el formulario, redirigir
    header("Location: sa_docentes.php");
    exit();
}
?>