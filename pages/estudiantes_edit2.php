<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'];
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Personal' || $Aa == 'Estudiante' || $Aa == 'Docente' || $Aa == 'SuperAdmin') {
        $redirectUrl = match ($Aa) {
            'Personal' => 'empleados_vista.php',
            'Estudiante' => 'estudiante_vista.php',
            'Docente' => 'docentes_vista.php',
            'SuperAdmin' => 'sa_vista.php',
        };
        ?>
        <script type="text/javascript">
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit();
    }
}

$estudiante_id = $_GET['id'];

$query = "SELECT e.nombres_encargado, e.apellidos_encargado, e.dui_encargado, e.direccion_encargado, e.correo_encargado, e.trabajo_encargado, e.numero_cel_encargado, e.numero_tel_encargado, e.genero_encargado, e.fecha_nac_encargado
FROM estudiantes e
WHERE e.ESTUDIANTE_ID = ?";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $estudiante_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$row = mysqli_fetch_assoc($result);
?>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Editar información del encargado</h4>
        </div>
        <a type="button" class="btn btn-primary bg-gradient-primary btn-block" href="estudiantes.php?">
            <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
        </a>
        <div class="card-body">
            <form role="form" method="post" action="estudiantes_edit4.php">
                <input type="hidden" name="id" value="<?php echo $estudiante_id; ?>" />

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">Nombres:</div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Nombres" name="nombres_encargado" minlength="5" maxlength="50" value="<?php echo $row['nombres_encargado']; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">Apellidos:</div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Apellidos" name="apellidos_encargado" minlength="5" maxlength="50" value="<?php echo $row['apellidos_encargado']; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">DUI:</div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="DUI" id="dui_encargado" name="dui_encargado" value="<?php echo $row['dui_encargado']; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">Dirección:</div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Dirección" name="direccion_encargado" minlength="5" maxlength="100" value="<?php echo $row['direccion_encargado']; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">Correo electrónico:</div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Correo electrónico" name="correo_encargado" minlength="10" maxlength="55" value="<?php echo $row['correo_encargado']; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">Oficio:</div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Oficio del encargado" name="trabajo_encargado" minlength="10" maxlength="55" value="<?php echo $row['trabajo_encargado']; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">Número de celular:</div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Número de celular" id="numero_cel_encargado" name="numero_cel_encargado" value="<?php echo $row['numero_cel_encargado']; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">Número de teléfono:</div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Número de teléfono" id="numero_tel_encargado" name="numero_tel_encargado" value="<?php echo $row['numero_tel_encargado']; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">Género:</div>
                    <div class="col-sm-9">
                        <select class='form-control' name='genero_encargado' required>
                            <option value="" disabled selected hidden>Seleccionar género</option>
                            <option value="Hombre" <?php if ($row['genero_encargado'] == 'Hombre') echo 'selected'; ?>>Hombre</option>
                            <option value="Mujer" <?php if ($row['genero_encargado'] == 'Mujer') echo 'selected'; ?>>Mujer</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">Fecha de nacimiento:</div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Fecha de nacimiento" name="fecha_nac_encargado" value="<?php echo date('d-m-Y', strtotime($row['fecha_nac_encargado'])); ?>" readonly>
                    </div>
                </div>

                <hr>

                <button type="submit" class="btn btn-warning btn-block"><i class="fa fa-edit fa-fw"></i>Actualizar</button>
            </form>
        </div>
    </div>
</center>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación del número de teléfono
    const phoneInputs = ['#numero_tel_encargado', '#numero_cel_encargado'];
    
    phoneInputs.forEach(selector => {
        const phoneInput = document.querySelector(selector);
        if (phoneInput) {
            phoneInput.addEventListener('input', validatePhone);
            phoneInput.addEventListener('blur', validatePhone);
        }

        function validatePhone() {
            let phoneValue = this.value.replace(/[^\d-]/g, ''); // Elimina caracteres no permitidos
            phoneValue = phoneValue.replace(/-+/g, '-'); // Elimina guiones extras

            // Añadir guion después de los primeros 4 dígitos
            if (phoneValue.length > 4 && phoneValue.charAt(4) !== '-') {
                phoneValue = phoneValue.slice(0, 4) + '-' + phoneValue.slice(4);
            }

            // Limitar a 9 caracteres en total (incluyendo el guion)
            this.value = phoneValue.slice(0, 9);

            if (/[^\d-]/.test(this.value)) {
                Swal.fire({
                    title: 'Número de Teléfono Inválido',
                    text: 'No se permiten letras o caracteres especiales en este campo.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            }
        }
    });

    // Validación del DUI
    const duiInput = document.querySelector('#dui_encargado');
    if (duiInput) {
        duiInput.addEventListener('input', validateDUI);
        duiInput.addEventListener('blur', function() {
            if (this.value.length > 0 && this.value.length < 10) {
                Swal.fire({
                    title: 'DUI Incompleto',
                    text: 'El DUI debe tener 9 dígitos más un guion.',
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            }
        });

        function validateDUI() {
            let duiValue = this.value.replace(/[^\d-]/g, ''); // Elimina caracteres no permitidos
            duiValue = duiValue.replace(/-+/g, ''); // Elimina guiones extras

            // Añadir guion después de los primeros 8 dígitos
            if (duiValue.length > 8) {
                duiValue = duiValue.slice(0, 8) + '-' + duiValue.slice(8);
            }

            // Limitar a 10 caracteres en total (incluyendo el guion)
            this.value = duiValue.slice(0, 10);
        }
    }
});
</script>

<?php
include '../includes/footer.php';
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- libreria online para las alertas -->