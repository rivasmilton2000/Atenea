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

<!-- ADMIN TABLE -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Cuentas de personal interno
            <a href="#" data-toggle="modal" data-target="#personalModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;">
                <i class="fas fa-fw fa-plus"></i>
            </a>
        </h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered js-datatable" id="dataTableInternalAccounts" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>NOMBRE COMPLETO</th>
                        <th>USUARIO</th>
                        <th>TIPO DE CUENTA</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = 'SELECT ID, FIRST_NAME, LAST_NAME, USERNAME, t.TYPE, U_ESTADO 
                              FROM users u
                              JOIN employee e ON e.EMPLOYEE_ID = u.EMPLOYEE_ID
                              JOIN type t ON t.TYPE_ID = u.TYPE_ID
                              WHERE u.TYPE_ID IN (1, 2, 4)';
                    $result = mysqli_query($db, $query) or die(mysqli_error($db));

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'] . '</td>';
                        echo '<td>' . $row['USERNAME'] . '</td>';
                        echo '<td>' . $row['TYPE'] . '</td>';
                        echo '<td>';
                        if ($row['U_ESTADO'] == '1') {
                            echo '<span class="badge badge-success">Activo</span>';
                        } else {
                            echo '<span class="badge badge-danger">Inactivo</span>';
                        }
                        echo '</td>';
                        echo '<td align="right">
                                <div class="btn-group">
                                    <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="sa_cuentas_usuarios_searchfrm1.php?action=edit&id=' . $row['ID'] . '">
                                        <i class="fas fa-fw fa-list-alt"></i> Detalles
                                    </a>
                                </div>
                                <div class="btn-group">
                                    <a type="button" class="btn btn-warning bg-gradient-warning btn-sm" href="sa_cuentas_usuarios_edit1.php?action=edit&id=' . $row['ID'] . '">
                                        <i class="fas fa-fw fa-edit"></i> Editar
                                    </a>
                                </div>
                                <div class="btn-group">
                                    <a type="button" class="btn btn-danger bg-gradient-danger btn-sm" href="javascript:void(0);" onclick="confirmDelete(' . $row['ID'] . ')">
                                        <i class="fas fa-fw fa-trash"></i> Eliminar
                                    </a>
                                </div>
                            </td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- STUDENT TABLE -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Cuentas de estudiantes
            <a href="#" data-toggle="modal" data-target="#studentModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;">
                <i class="fas fa-fw fa-plus"></i>
            </a>
        </h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered js-datatable" id="dataTableStudentAccounts" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>NOMBRE COMPLETO</th>
                        <th>USUARIO</th>
                        <th>TIPO DE CUENTA</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = 'SELECT ID, nombres_estudiante, apellidos_estudiante, USERNAME, t.TYPE, U_ESTADO 
                              FROM users u
                              JOIN estudiantes es ON es.ESTUDIANTE_ID = u.ESTUDIANTE_ID
                              JOIN type t ON t.TYPE_ID = u.TYPE_ID
                              WHERE u.TYPE_ID = 3';
                    $result = mysqli_query($db, $query) or die(mysqli_error($db));

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . $row['apellidos_estudiante'] . ', ' . $row['nombres_estudiante'] . '</td>';
                        echo '<td>' . $row['USERNAME'] . '</td>';
                        echo '<td>' . $row['TYPE'] . '</td>';
                        echo '<td>';
                        if ($row['U_ESTADO'] == '1') {
                            echo '<span class="badge badge-success">Activo</span>';
                        } else {
                            echo '<span class="badge badge-danger">Inactivo</span>';
                        }
                        echo '</td>';
                        echo '<td align="right">
                                <div class="btn-group">
                                    <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="sa_cuentas_usuarios_searchfrm2.php?action=edit&id=' . $row['ID'] . '">
                                        <i class="fas fa-fw fa-list-alt"></i> Detalles
                                    </a>
                                </div>
                                <div class="btn-group">
                                    <a type="button" class="btn btn-warning bg-gradient-warning btn-sm" href="sa_cuentas_usuarios_edit3.php?action=edit&id=' . $row['ID'] . '">
                                        <i class="fas fa-fw fa-edit"></i> Editar
                                    </a>
                                </div>
                                <div class="btn-group">
                                    <a type="button" class="btn btn-danger bg-gradient-danger btn-sm" href="javascript:void(0);" onclick="confirmDelete(' . $row['ID'] . ')">
                                        <i class="fas fa-fw fa-trash"></i> Eliminar
                                    </a>
                                </div>
                            </td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include '../includes/footer_superadmin.php';

// Dropdown options for employees
$sql = "SELECT EMPLOYEE_ID, FIRST_NAME, LAST_NAME, j.JOB_TITLE 
        FROM employee e 
        JOIN job j ON j.JOB_ID=e.JOB_ID 
        WHERE e.E_ESTADO = 1
        ORDER BY e.LAST_NAME ASC
        ";
$res = mysqli_query($db, $sql) or die("Bad SQL: $sql");

$opt_employee = "<select class='form-control' name='empid' required>
                 <option value='' disabled selected hidden>Seleccionar empleado</option>";
while ($row = mysqli_fetch_assoc($res)) {
    $opt_employee .= "<option value='" . $row['EMPLOYEE_ID'] . "'>" . $row['FIRST_NAME'] . " " . $row['LAST_NAME'] . " - " . $row['JOB_TITLE'] . "</option>";
}
$opt_employee .= "</select>";

// Dropdown options for students
$sql2 = "SELECT ESTUDIANTE_ID, nombres_estudiante, apellidos_estudiante, carnet_estudiante
        FROM estudiantes
        WHERE estado_estudiante = 1
        ORDER BY apellidos_estudiante ASC";
$res2 = mysqli_query($db, $sql2) or die("Bad SQL: $sql2");

$opt_student = "<select class='form-control' name='estid' required>
                <option value='' disabled selected hidden>Seleccionar estudiante</option>";
while ($row2 = mysqli_fetch_assoc($res2)) {
    $opt_student .= "<option value='" . $row2['ESTUDIANTE_ID'] . "'>" . $row2['apellidos_estudiante'] . ", " . $row2['nombres_estudiante'] . " - " . $row2['carnet_estudiante'] ."</option>";
}
$opt_student .= "</select>";
?>

<!-- Personal Modal -->
<div class="modal fade" id="personalModal" tabindex="-1" role="dialog" aria-labelledby="personalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="personalModalLabel">Agregar cuenta de personal</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addPersonalForm" role="form">
                    <div class="form-group">
                        <?php echo $opt_employee; ?>
                    </div>
                    <div class="form-group">
                        <input class="form-control" placeholder="Usuario" minlength="5" maxlength="70" name="username" required>
                    </div>
                    <div class="form-group">
                        <input class="form-control" placeholder="Contraseña"  minlength="8" maxlength="80" name="password" type="password" required>
                    </div>
                    <div class="form-group">
                    <input class="form-control" placeholder="Confirmar contraseña" minlength="8" maxlength="80" name="confirm_password" type="password" required>
                    </div>
                    <div class="form-group">
                        <select class="form-control" name="type" required>
                            <option value="" disabled selected hidden>Tipo de cuenta</option>
                            <option value="1">Administrador</option>
                            <option value="2">Personal</option>
                            <option value="4">Docente</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <select class="form-control" name="estado" required>
                            <option value="" disabled selected hidden>Estado del registro</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Guardar</button>
                    <button type="reset" class="btn btn-warning"><i class="fa fa-circle-notch fa-fw"></i>Reiniciar</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i>Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Student Modal -->
<div class="modal fade" id="studentModal" tabindex="-1" role="dialog" aria-labelledby="studentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentModalLabel">Agregar cuenta de estudiante</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addStudentForm" role="form">
                    <div class="form-group">
                        <?php echo $opt_student; ?>
                    </div>
                    <div class="form-group">
                        <input class="form-control" minlength="5" maxlength="70" placeholder="Usuario" name="username" required>
                    </div>
                    <div class="form-group">
                        <input class="form-control"  minlength="8" maxlength="80" placeholder="Contraseña" name="password" type="password" required>
                    </div>
                    <div class="form-group">
                    <input class="form-control" minlength="8" maxlength="80" placeholder="Confirmar contraseña" name="confirm_password" type="password" required
                    </div>
                    <div class="form-group">
                        <input class="form-control" value="Estudiante" readonly>
                        <input type="hidden" name="type" value="3">
                    </div>
                    <div class="form-group">
                        <select class="form-control" name="estado" required>
                            <option value="" disabled selected hidden>Estado del registro</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Guardar</button>
                    <button type="reset" class="btn btn-warning"><i class="fa fa-circle-notch fa-fw"></i>Reiniciar</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i>Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Agrega esto justo antes de cerrar el tag </body> -->
<script>
$(document).ready(function() {
    // Formulario para personal
    $('#addPersonalForm').on('submit', function(e) {
        e.preventDefault();
        
        var password = $('input[name="password"]', this).val();
        var confirmPassword = $('input[name="confirm_password"]', this).val();
        
        if (password !== confirmPassword) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Las contraseñas no coinciden. Por favor, inténtalo de nuevo.'
            });
            return;
        }
        
        $.ajax({
            url: 'sa_cuentas_usuarios_transac1.php?action=add',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ha ocurrido un error en la comunicación con el servidor.'
                });
            }
        });
    });

    // Formulario para estudiantes
    $('#addStudentForm').on('submit', function(e) {
        e.preventDefault();
        
        var password = $('input[name="password"]', this).val();
        var confirmPassword = $('input[name="confirm_password"]', this).val();
        
        if (password !== confirmPassword) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Las contraseñas no coinciden. Por favor, inténtalo de nuevo.'
            });
            return;
        }
        
        $.ajax({
            url: 'sa_cuentas_usuarios_transac2.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ha ocurrido un error en la comunicación con el servidor.'
                });
            }
        });
    });
});

function confirmDelete(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Quieres eliminar este registro?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'sa_cuentas_usuarios_delete.php',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ha ocurrido un error en la comunicación con el servidor.'
                    });
                }
            });
        }
    });
}

function confirmDelete(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Quieres eliminar este registro?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'sa_cuentas_usuarios_delete.php',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ha ocurrido un error en la comunicación con el servidor.'
                    });
                }
            });
        }
    });
}

</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
