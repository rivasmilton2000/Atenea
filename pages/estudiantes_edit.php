<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa=='Personal' || $Aa=='Estudiante' || $Aa=='Docente' || $Aa=='SuperAdmin'){
        if ($Aa=='Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa=='Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa=='Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa=='SuperAdmin') {
          $redirectUrl = "sa_vista.php";
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

$estudiante_id = $_GET['id'];
$query = "SELECT e.ESTUDIANTE_ID, e.nombres_estudiante, e.apellidos_estudiante, e.direccion_estudiante, e.correo_estudiante, e.fecha_nac_estudiante, e.edad_estudiante, e.genero_estudiante, g.G_NAME AS grado, g.G_ID AS grado_id, e.carnet_estudiante, e.numero_lista_estudiante, e.info_medica_estudiante, e.fecha_reg_estudiante, e.foto_estudiante
FROM estudiantes e
JOIN grados g ON e.grado_id_estudiante = g.G_ID
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
            <h4 class="m-2 font-weight-bold text-primary">Editar información del estudiante</h4>
        </div>
        <a type="button" class="btn btn-primary bg-gradient-primary btn-block" href="estudiantes.php?">
            <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
        </a>
        
        <div class="card-body">
            <form role="form" method="post" action="estudiantes_edit3.php" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $row['ESTUDIANTE_ID']; ?>" />

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Nombres:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Nombres" name="nombres_estudiante" minlength="5" maxlength="50" value="<?php echo $row['nombres_estudiante']; ?>" required>
                    </div>
                </div>
                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Apellidos:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Apellidos" name="apellidos_estudiante"  minlength="5" maxlength="50" value="<?php echo $row['apellidos_estudiante']; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Dirección:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Dirección" name="direccion_estudiante" minlength="5" maxlength="100" value="<?php echo $row['direccion_estudiante']; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Correo electrónico:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Correo electrónico" name="correo_estudiante" minlength="10" maxlength="55"  value="<?php echo $row['correo_estudiante']; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Fecha de nacimiento:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control fecha" placeholder="Fecha de nacimiento" name="fecha_nac_estudiante" value="<?php echo date('d-m-Y', strtotime($row['fecha_nac_estudiante'])); ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Edad:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Edad" id="edad_estudiante" name="edad_estudiante" value="<?php echo $row['edad_estudiante']; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Género:
                    </div>
                    <div class="col-sm-9">
                        <select class='form-control' name='genero_estudiante' required>
                            <option value="" disabled selected hidden>Seleccionar género</option>
                            <option value="Hombre" <?php if($row['genero_estudiante'] == 'Hombre') echo 'selected'; ?>>Hombre</option>
                            <option value="Mujer" <?php if($row['genero_estudiante'] == 'Mujer') echo 'selected'; ?>>Mujer</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Grado:
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control" name="grado_id_estudiante" required>
                            <option value="" disabled selected hidden>Seleccionar grado</option>
                            <?php
                            // Consulta para obtener los grados disponibles
                            $query_grados = "SELECT G_ID, G_NAME FROM grados WHERE G_ESTADO = 1";
                            $result_grados = mysqli_query($db, $query_grados);

                            // Iterar sobre los resultados y generar las opciones del menú desplegable
                            while ($row_grado = mysqli_fetch_assoc($result_grados)) {
                                $selected = ($row_grado['G_ID'] == $row['grado_id']) ? 'selected' : '';
                                echo "<option value='" . $row_grado['G_ID'] . "' " . $selected . ">" . $row_grado['G_NAME'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Carnet:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Carnet del estudiante (máximo 8 dígitos)" id="carnet_estudiante" name="carnet_estudiante" value="<?php echo $row['carnet_estudiante']; ?>" required>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Número de lista:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Número de lista" name="numero_lista_estudiante" value="<?php echo $row['numero_lista_estudiante']; ?>">
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Observaciones médicas:
                    </div>
                    <div class="col-sm-9">
                        <textarea class="form-control" placeholder="Observaciones médicas" minlength="5" maxlength="130" name="info_medica_estudiante" rows="3"><?php echo $row['info_medica_estudiante']; ?></textarea>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Fecha de registro:
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="Fecha de registro" name="fecha_reg_estudiante" value="<?php echo date('d-m-Y', strtotime($row['fecha_reg_estudiante'])); ?>" readonly>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Foto actual:
                    </div>
                    <div class="col-sm-9">
                        <?php if (!empty($row['foto_estudiante'])): ?>
                            <img src="imagenes_estudiantes/<?php echo $row['foto_estudiante']; ?>" alt="Foto del Estudiante" style="max-width: 200px; height: auto;">
                        <?php else: ?>
                            <p>Estudiante sin foto</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group row text-left text-warning">
                    <div class="col-sm-3" style="padding-top: 5px;">
                        Cambiar foto:
                    </div>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" name="foto_estudiante" accept="image/*">
                    </div>
                </div>

                <hr>

                <button type="submit" class="btn btn-warning btn-block"><i class="fa fa-edit fa-fw"></i>Actualizar</button>
            </form>
        </div>
    </div>
</center>
<script>
$(document).ready(function() {
    $('.fecha').mask('00-00-0000', {placeholder: 'DD-MM-AAAA'});
});
</script>
<?php
include '../includes/footer_superadmin.php';
?>
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