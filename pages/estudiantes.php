<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';

$query_user_type = "SELECT TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ?";
$stmt_user_type = mysqli_prepare($db, $query_user_type);
mysqli_stmt_bind_param($stmt_user_type, "i", $_SESSION['MEMBER_ID']);
mysqli_stmt_execute($stmt_user_type);
mysqli_stmt_bind_result($stmt_user_type, $user_type);
mysqli_stmt_fetch($stmt_user_type);
mysqli_stmt_close($stmt_user_type);

if ($user_type == 'Personal' || $user_type == 'Estudiante' || $user_type == 'Docente' || $user_type == 'SuperAdmin') {
    if ($user_type == 'Personal') {
        $redirectUrl = "empleados_vista.php";
    } elseif ($user_type == 'Estudiante') {
        $redirectUrl = "estudiante_vista.php";
    } elseif ($user_type == 'Docente') {
        $redirectUrl = "docentes_vista.php";
    } elseif ($user_type == 'SuperAdmin') {
        $redirectUrl = "sa_vista.php";
    }
    ?>
    <script type="text/javascript">
        alert("Página restringida! Será redirigido.");
        window.location = "<?php echo $redirectUrl; ?>";
    </script>
    <?php
    exit(); // Terminar la ejecución del script después de la redirección
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Estudiantes&nbsp; 
            <a href="#" data-toggle="modal" data-target="#teacherModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;"><i class="fas fa-fw fa-plus"></i></a>&nbsp; 
            <!-- Botón para abrir el modal de carga masiva -->
            <a href="#" data-toggle="modal" data-target="#uploadModal" type="button" class="btn btn-success bg-gradient-success" style="border-radius: 0px;"><i class="fas fa-fw fa-file-excel"></i></a></h4>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>NOMBRE COMPLETO</th>
                        <th>CARNET</th>
                        <th>GRADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                <?php
$query_estudiantes = 'SELECT e.ESTUDIANTE_ID, CONCAT(e.apellidos_estudiante, ", ", e.nombres_estudiante) AS nombre_completo, e.carnet_estudiante, g.G_NAME 
          FROM estudiantes e
          JOIN grados g ON e.grado_id_estudiante = g.G_ID
          WHERE e.estado_estudiante = 1';
$result_estudiantes = mysqli_query($db, $query_estudiantes) or die(mysqli_error($db));
while ($row_estudiantes = mysqli_fetch_assoc($result_estudiantes)) {
    echo '<tr>';
    echo '<td>' . $row_estudiantes['nombre_completo'] . '</td>';
    echo '<td>' . $row_estudiantes['carnet_estudiante'] . '</td>';
    echo '<td>' . $row_estudiantes['G_NAME'] . '</td>';

    echo '<td align="right">
<div class="btn-group">
<button type="button" class="btn btn-primary bg-gradient-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
<i class="fas fa-fw fa-list-alt"></i> Detalles
</button>
<div class="dropdown-menu">
<a class="dropdown-item" href="estudiantes_searchfrm.php?id=' . $row_estudiantes['ESTUDIANTE_ID'] . '"><i class="fas fa-user-graduate"></i> Estudiante</a>
<a class="dropdown-item" href="estudiantes_searchfrm2.php?id=' . $row_estudiantes['ESTUDIANTE_ID'] . '"><i class="fas fa-user-tie"></i> Encargado</a>
</div>
</div>
<div class="btn-group">
<button type="button" class="btn btn-warning bg-gradient-warning btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
<i class="fas fa-fw fa-edit"></i> Editar
</button>
<div class="dropdown-menu">
<a class="dropdown-item" href="estudiantes_edit.php?id=' . $row_estudiantes['ESTUDIANTE_ID'] . '"><i class="fas fa-user-graduate"></i> Estudiante</a>
<a class="dropdown-item" href="estudiantes_edit2.php?id=' . $row_estudiantes['ESTUDIANTE_ID'] . '"><i class="fas fa-user-tie"></i> Encargado</a>
</div>
</div>
<button type="button" class="btn btn-danger bg-gradient-danger btn-sm" onclick="confirmDelete(' . $row_estudiantes['ESTUDIANTE_ID'] . ')">
<i class="fas fa-fw fa-trash"></i> Eliminar
</button>
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
include '../includes/footer2.php';
?>

<!-- Estudiante Modal-->
<div class="modal fade" id="teacherModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ingresar estudiante</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="estudiantes_transac.php?action=add" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>INFORMACIÓN DEL ESTUDIANTE</h6>
                            <div class="form-group">
                                <input class="form-control" placeholder="Nombres del estudiante" minlength="5" maxlength="50" name="nombres_estudiante" required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Apellidos del estudiante" minlength="5" maxlength="50" name="apellidos_estudiante" required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Dirección del estudiante" minlength="5" maxlength="100" name="direccion_estudiante" required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" type="email" placeholder="Correo electrónico del estudiante" name="correo_estudiante" required>
                            </div>
                            <div class="form-group">
                                <input type="hidden" name="foto_estudiante" value="NULL">
                            </div>
                            <div class="form-group">
                                <label>Foto del estudiante:</label>
                                <input class="form-control" type="file" name="foto_estudiante">
                            </div>
                            <div class="form-group">
                                <input placeholder="Fecha de nacimiento del estudiante" type="text" onfocus="(this.type='date')" onblur="(this.type='text')" name="fecha_nac_estudiante" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" id="edad_estudiante" placeholder="Edad del estudiante" name="edad_estudiante" required>
                            </div>
                            <div class="form-group">
                                <select class='form-control' name='genero_estudiante' required>
                                    <option value="" disabled selected hidden>Seleccionar género del estudiante</option>
                                    <option value="Hombre">Hombre</option>
                                    <option value="Mujer">Mujer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="grado_id_estudiante" required>
                                    <option value="" disabled selected hidden>Seleccione grado del estudiante</option>
                                    <?php
                                    $query = "SELECT G_ID, G_NAME FROM grados WHERE G_ESTADO = 1";
                                    $result = mysqli_query($db, $query);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<option value='" . $row['G_ID'] . "'>" . $row['G_NAME'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Carnet del estudiante (máximo 8 dígitos)" id="carnet_estudiante" name="carnet_estudiante" required>
                            </div>
                            <div class="form-group">
                                <input type="hidden" name="numero_lista_estudiante" value="NULL">
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" placeholder="Observaciones médicas del estudiante" minlength="5" maxlength="130" name="info_medica_estudiante" rows="3"></textarea>
                            </div>
                            <div class="form-group" style="display: none;">
                                <input placeholder="Fecha de registro" type="text" onfocus="(this.type='date')" onblur="(this.type='text')" name="fecha_reg_estudiante" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>INFORMACIÓN DEL ENCARGADO</h6>
                            <div class="form-group">
                                <input class="form-control" placeholder="Nombres del encargado" name="nombres_encargado" minlength="5" maxlength="50" required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Apellidos del encargado" name="apellidos_encargado" minlength="5" maxlength="50" required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="DUI del encargado" id="dui_encargado"  name="dui_encargado"  required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Dirección del encargado" name="direccion_encargado" minlength="5" maxlength="100" required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Correo electrónico del encargado" name="correo_encargado" minlength="10" maxlength="55" required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Oficio del encargado" name="trabajo_encargado" minlength="10" maxlength="55" required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Número de celular del encargado" id="numero_cel_encargado" name="numero_cel_encargado" required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Número de teléfono del encargado" id="numero_tel_encargado" name="numero_tel_encargado" required>
                            </div>
                            <div class="form-group">
                                <select class='form-control' name='genero_encargado' required>
                                    <option value="" disabled selected hidden>Seleccionar género del encargado</option>
                                    <option value="Hombre">Hombre</option>
                                    <option value="Mujer">Mujer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <input placeholder="Fecha de nacimiento del encargado" type="text" onfocus="(this.type='date')" onblur="(this.type='text')" name="fecha_nac_encargado" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Guardar</button>
                        <button type="reset" class="btn btn-warning"><i class="fa fa-circle-notch fa-fw"></i>Reiniciar</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i>Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ************************************************* -->
 <!-- Modal para carga masiva -->
 <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Ingresar estudiantes a través de CSV </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" action="estudiantes_procesar_carga_masiva.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="file">Seleccione archivo CSV:</label>
                        <input type="file" class="form-control-file" id="file" name="file" required>
                    </div>
                    <hr>
                    <div class="text-center">
                        <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Guardar</button>
                        <button type="reset" class="btn btn-warning"><i class="fa fa-circle-notch fa-fw"></i>Reiniciar</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i>Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- ************************************************* -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadForm');
    const fileInput = document.getElementById('file');

    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!fileInput.files.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Archivo no seleccionado',
                text: 'Por favor, seleccione un archivo CSV antes de continuar.',
            });
            return;
        }

        const file = fileInput.files[0];
        if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
            Swal.fire({
                icon: 'error',
                title: 'Archivo inválido',
                text: 'Por favor, seleccione un archivo CSV válido.',
            });
            return;
        }

        const formData = new FormData(this);

        fetch('sa_estudiantes_procesar_carga_masiva.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: data.message,
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al procesar la solicitud.',
            });
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(estudianteId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas eliminar al estudiane?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'custom-popup-class',
                title: 'custom-title-class',
                confirmButton: 'custom-confirm-button-class',
                cancelButton: 'custom-cancel-button-class'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar solicitud AJAX para eliminar el registro
                fetch('estudiantes_eliminar.php?id=' + estudianteId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Eliminado',
                                text: 'El estudiante ha sido eliminado.',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'custom-popup-class',
                                    title: 'custom-title-class',
                                    confirmButton: 'custom-confirm-button-class'
                                }
                            }).then(() => {
                                window.location.reload(); // Recargar la página para reflejar los cambios
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'Hubo un problema al eliminar el registro.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'custom-popup-class',
                                    title: 'custom-title-class',
                                    confirmButton: 'custom-confirm-button-class'
                                }
                            });
                        }
                    });
            }
        });
    }
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Script de validación de teléfono cargado");

    const phoneInput = document.querySelector('#numero_tel_encargado');

    if (phoneInput) {
        console.log("Input de teléfono encontrado");

        phoneInput.addEventListener('input', validatePhone);
        phoneInput.addEventListener('blur', validatePhone);
    } else {
        console.log("Input de teléfono no encontrado");
    }

    function validatePhone() {
        console.log("Validando teléfono:", this.value);

        let phoneValue = this.value;
        
        // Verificar si hay caracteres no permitidos
        if (/[^\d-]/.test(phoneValue)) {
            Swal.fire({
                title: 'Número de Teléfono Inválido',
                text: 'No se permiten letras o caracteres especiales en este campo.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            // Eliminar caracteres no permitidos
            phoneValue = phoneValue.replace(/[^\d-]/g, '');
        }

        // Eliminar guiones extras
        phoneValue = phoneValue.replace(/-+/g, '-');

        // Añadir guion después de los primeros 4 dígitos
        if (phoneValue.length > 4 && phoneValue.charAt(4) !== '-') {
            phoneValue = phoneValue.slice(0, 4) + '-' + phoneValue.slice(4);
        }

        // Limitar a 9 caracteres en total (incluyendo el guion)
        phoneValue = phoneValue.slice(0, 9);

        this.value = phoneValue;

        console.log("Número de teléfono formateado:", phoneValue);
    }
});

</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Script de validación de teléfono cargado");

    const phoneInput = document.querySelector('#numero_cel_encargado');

    if (phoneInput) {
        console.log("Input de teléfono encontrado");

        phoneInput.addEventListener('input', validatePhone);
        phoneInput.addEventListener('blur', validatePhone);
    } else {
        console.log("Input de teléfono no encontrado");
    }

    function validatePhone() {
        console.log("Validando teléfono:", this.value);

        let phoneValue = this.value;
        
        // Verificar si hay caracteres no permitidos
        if (/[^\d-]/.test(phoneValue)) {
            Swal.fire({
                title: 'Número de Teléfono Inválido',
                text: 'No se permiten letras o caracteres especiales en este campo.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            // Eliminar caracteres no permitidos
            phoneValue = phoneValue.replace(/[^\d-]/g, '');
        }

        // Eliminar guiones extras
        phoneValue = phoneValue.replace(/-+/g, '-');

        // Añadir guion después de los primeros 4 dígitos
        if (phoneValue.length > 4 && phoneValue.charAt(4) !== '-') {
            phoneValue = phoneValue.slice(0, 4) + '-' + phoneValue.slice(4);
        }

        // Limitar a 9 caracteres en total (incluyendo el guion)
        phoneValue = phoneValue.slice(0, 9);

        this.value = phoneValue;

        console.log("Número de teléfono formateado:", phoneValue);
    }
});

</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Script de validación de edad cargado");

    const ageInput = document.querySelector('#edad_estudiante');
    if (ageInput) {
        console.log("Input de edad encontrado");
        ageInput.addEventListener('input', validateAge);
        ageInput.addEventListener('blur', validateAge);
    } else {
        console.log("Input de edad no encontrado");
    }

    function validateAge() {
        console.log("Validando edad:", this.value);

        let ageValue = this.value;

        // Verificar si hay caracteres no permitidos
        if (/[^\d]/.test(ageValue)) {
            Swal.fire({
                title: 'Edad Inválida',
                text: 'Solo se permiten números en este campo.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            // Eliminar caracteres no permitidos
            ageValue = ageValue.replace(/[^\d]/g, '');
        }

        // Limitar a 2 dígitos
        ageValue = ageValue.slice(0, 2);

        // Convertir a número entero
        let age = parseInt(ageValue);

        // Validar rango de edad (entre 1 y 20)
        if (age < 1 || age > 20 || ageValue === '00') {
            Swal.fire({
                title: 'Edad Fuera de Rango',
                text: 'La edad debe estar entre 1 y 20 años.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            // Si la edad está fuera de rango, resetear el valor
            ageValue = '';
        } else if (ageValue.length === 2 && ageValue[0] === '0') {
            // Si empieza con 0 y tiene 2 dígitos, quitar el 0 inicial
            ageValue = ageValue[1];
        }

        this.value = ageValue;

        console.log("Edad formateada:", ageValue);
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Script de validación de carnet cargado");

    const carnetInput = document.querySelector('#carnet_estudiante');
    if (carnetInput) {
        console.log("Input de carnet encontrado");
        carnetInput.addEventListener('input', validateCarnet);
        carnetInput.addEventListener('blur', checkCarnetLength);
    } else {
        console.log("Input de carnet no encontrado");
    }

    function validateCarnet() {
        console.log("Validando carnet:", this.value);

        let carnetValue = this.value;

        // Verificar si hay caracteres no permitidos
        if (/[^\d]/.test(carnetValue)) {
            Swal.fire({
                title: 'Carnet Inválido',
                text: 'Solo se permiten números en este campo.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            // Eliminar caracteres no permitidos
            carnetValue = carnetValue.replace(/[^\d]/g, '');
        }

        // Limitar a 8 dígitos
        carnetValue = carnetValue.slice(0, 8);

        this.value = carnetValue;

        console.log("Carnet formateado:", carnetValue);
    }

    function checkCarnetLength() {
        let carnetValue = this.value;
        if (carnetValue.length > 0 && carnetValue.length < 8) {
            Swal.fire({
                title: 'Carnet Incompleto',
                text: 'El carnet debe tener 8 dígitos.',
                icon: 'info',
                confirmButtonText: 'OK'
            });
        }
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Script de validación de DUI cargado");

    const duiInput = document.querySelector('#dui_encargado');
    if (duiInput) {
        console.log("Input de DUI encontrado");
        duiInput.addEventListener('input', validateDUI);
        duiInput.addEventListener('blur', checkDUILength);
    } else {
        console.log("Input de DUI no encontrado");
    }

    function validateDUI() {
        console.log("Validando DUI:", this.value);

        let duiValue = this.value;

        // Eliminar cualquier carácter que no sea número o guion
        duiValue = duiValue.replace(/[^\d-]/g, '');

        // Eliminar guiones extras
        duiValue = duiValue.replace(/-+/g, '');

        // Limitar a 9 dígitos (sin contar el guion)
        duiValue = duiValue.slice(0, 9);

        // Insertar guion después del octavo dígito
        if (duiValue.length > 8) {
            duiValue = duiValue.slice(0, 8) + '-' + duiValue.slice(8);
        }

        this.value = duiValue;

        console.log("DUI formateado:", duiValue);
    }

    function checkDUILength() {
        let duiValue = this.value;
        if (duiValue.length > 0 && duiValue.length < 10) {
            Swal.fire({
                title: 'DUI Incompleto',
                text: 'El DUI debe tener 9 dígitos más un guion (formato: 12345678-9).',
                icon: 'info',
                confirmButtonText: 'OK'
            });
        }
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        background-color: #007bff; /* Azul */
        border-color: #007bff;
    }
    .custom-confirm-button-class:hover {
        background-color: #0056b3; /* Azul oscuro */
        border-color: #00408d;
    }
    .custom-cancel-button-class {
        font-family: 'Open Sans', sans-serif;
        font-weight: 600;
        background-color: #dc3545; /* Rojo */
        border-color: #dc3545;
    }
    .custom-cancel-button-class:hover {
        background-color: #c82333; /* Rojo oscuro */
        border-color: #bd2130;
    }
</style>

