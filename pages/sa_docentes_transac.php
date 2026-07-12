<?php
include '../includes/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = $_POST['firstname'];
    $lname = $_POST['lastname'];
    $gen = $_POST['gender'];
    $email = $_POST['email'];
    $phone = $_POST['phonenumber'];
    $jobb = $_POST['jobs'];
    $hdate = $_POST['hireddate'];
    $prov = $_POST['province'];
    $cit = $_POST['city'];
    $estado = $_POST['estado'];

    $current_date = date('Y-m-d');

    // Validar dominio del correo electrónico
    $allowed_domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
    $email_domain = substr(strrchr($email, "@"), 1);

    if (!in_array($email_domain, $allowed_domains)) {
        $alertTitle = 'Correo Inválido';
        $alertText = 'Dominio de correo electrónico inválido';
        $alertIcon = 'warning';
    } elseif ($hdate < $current_date) {
        $alertTitle = 'Fecha Inválida';
        $alertText = 'La fecha de contratación de este docente es inválida.';
        $alertIcon = 'warning';
    } else {
        // Verificar si el teléfono ya existe
        $stmt = $db->prepare("SELECT COUNT(*) FROM employee WHERE PHONE_NUMBER = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $alertTitle = 'Número Existente';
            $alertText = 'El número de teléfono ' . htmlspecialchars($phone) . ' ya existe en el sistema. Por favor, ingrese un número diferente.';
            $alertIcon = 'warning';
        } else {
            // Verificamos si el correo ya existe
            $stmt = $db->prepare("SELECT COUNT(*) FROM employee WHERE EMAIL = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                $alertTitle = 'Correo Existente';
                $alertText = 'El correo ' . htmlspecialchars($email) . ' ya existe en el sistema. Por favor, ingrese un correo diferente.';
                $alertIcon = 'warning';
            } else {
                // Verificamos si el docente ya existe por nombre completo
                $stmt = $db->prepare("SELECT COUNT(*) FROM employee WHERE FIRST_NAME = ? AND LAST_NAME = ?");
                $stmt->bind_param("ss", $fname, $lname);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                $stmt->close();

                if ($count > 0) {
                    $alertTitle = 'Docente Existente';
                    $alertText = 'El docente ' . htmlspecialchars($fname) . ' ' . htmlspecialchars($lname) . ' ya existe en el sistema. Por favor, ingrese un docente diferente.';
                    $alertIcon = 'warning';
                } else {
                    // Insertamos la ubicación si no existe
                    mysqli_query($db, "INSERT INTO location (LOCATION_ID, PROVINCE, CITY) VALUES (Null, '$prov', '$cit')");
                    
                    // Insertamos el docente
                    $stmt = $db->prepare("INSERT INTO employee (EMPLOYEE_ID, FIRST_NAME, LAST_NAME, GENDER, EMAIL, PHONE_NUMBER, JOB_ID, HIRED_DATE, LOCATION_ID, E_ESTADO) VALUES (Null, ?, ?, ?, ?, ?, ?, ?, (SELECT MAX(LOCATION_ID) FROM location), ?)");
                    $stmt->bind_param("sssssssi", $fname, $lname, $gen, $email, $phone, $jobb, $hdate, $estado);

                    if ($stmt->execute()) {
                        $alertTitle = 'Éxito';
                        $alertText = 'Docente agregado exitosamente.';
                        $alertIcon = 'success';
                    } else {
                        $alertTitle = 'Error';
                        $alertText = 'Error al insertar el docente en la base de datos: ' . $stmt->error;
                        $alertIcon = 'error';
                    }
                    $stmt->close();
                }
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
}

mysqli_close($db);
?>