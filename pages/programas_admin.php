<?php
require 'session.php';
require_once '../includes/connection.php';
require_once '../includes/material_dashboard.php';
require_once '../includes/atenea_capacitacion.php';

dashboard_require_role(
    $db,
    ['Admin', 'SuperAdmin'],
    [
        'Personal' => 'empleados_vista.php',
        'Estudiante' => 'estudiante_vista.php',
        'Docente' => 'docentes_vista.php',
    ]
);

$currentRole = (string) ($_SESSION['TYPE'] ?? '');
include $currentRole === 'SuperAdmin'
    ? '../includes/sidebar_superadmin.php'
    : '../includes/sidebar_admin.php';

$schemaFlags = atenea_capacitacion_schema_flags($db);
$missingFields = array_keys(array_filter($schemaFlags, static function (bool $exists): bool {
    return !$exists;
}));

$queryProgramas = "
    SELECT pe.*,
           " . atenea_capacitacion_select_sql($db, 'pe') . "
    FROM programas_educativos pe
    ORDER BY pe.orden ASC, pe.id ASC
";
$resultadoProgramas = mysqli_query($db, $queryProgramas) or die(mysqli_error($db));
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">
            Cursos y certificaciones de capacitacion
            <a href="#" data-toggle="modal" data-target="#addProgramModal" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;">
                <i class="fas fa-fw fa-plus"></i>
            </a>
        </h4>
    </div>

    <div class="card-body">
        <?php if ($missingFields !== []) : ?>
            <div class="alert alert-warning">
                Los detalles avanzados de capacitacion no estan disponibles. Revisa la configuracion del entorno.
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ORDEN</th>
                        <th>IMAGEN</th>
                        <th>TITULO</th>
                        <th>TIPO</th>
                        <th>PRECIO</th>
                        <th>NIVEL</th>
                        <th>INSTRUCTOR</th>
                        <th>DURACION</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($programa = mysqli_fetch_assoc($resultadoProgramas)) : ?>
                        <?php
                        $programType = atenea_capacitacion_normalize_type((string) ($programa['tipo_programa'] ?? 'curso'));
                        $programTypeLabel = atenea_capacitacion_type_label($programType);
                        $programPrice = atenea_capacitacion_price($programa);
                        $programDuration = atenea_capacitacion_text_value($programa['duracion'] ?? '');
                        $estadoClass = (int) $programa['estado'] === 1 ? 'badge-success' : 'badge-danger';
                        $estadoText = (int) $programa['estado'] === 1 ? 'Activo' : 'Inactivo';
                        ?>
                        <tr>
                            <td><?php echo (int) $programa['id']; ?></td>
                            <td><?php echo (int) $programa['orden']; ?></td>
                            <td>
                                <img
                                    src="../img/<?php echo htmlspecialchars((string) $programa['imagen'], ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                                    style="width: 80px; height: 80px; object-fit: cover;"
                                    class="rounded"
                                >
                            </td>
                            <td><?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span class="badge <?php echo $programType === 'certificacion' ? 'badge-success' : 'badge-primary'; ?>">
                                    <?php echo htmlspecialchars($programTypeLabel, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>$<?php echo number_format($programPrice, 2); ?></td>
                            <td><span class="badge badge-info"><?php echo htmlspecialchars((string) $programa['nivel'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td><?php echo htmlspecialchars((string) $programa['instructor'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($programDuration !== '' ? $programDuration : 'Por definir', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span class="badge <?php echo $estadoClass; ?>"><?php echo $estadoText; ?></span></td>
                            <td align="right">
                                <div class="btn-group">
                                    <a class="btn btn-primary bg-gradient-primary btn-sm" href="programas_edit.php?id=<?php echo (int) $programa['id']; ?>">
                                        <i class="fas fa-fw fa-edit"></i> Editar
                                    </a>
                                </div>
                                <div class="btn-group">
                                    <a class="btn btn-danger bg-gradient-danger btn-sm" href="#" onclick="confirmDelete(<?php echo (int) $programa['id']; ?>)">
                                        <i class="fas fa-fw fa-trash"></i> Eliminar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(programId) {
    Swal.fire({
        title: 'Estas seguro?',
        text: 'Deseas eliminar este programa?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        fetch('programas_delete.php?id=' + programId)
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    Swal.fire({
                        title: 'Eliminado',
                        text: 'El programa fue eliminado correctamente.',
                        icon: 'success'
                    }).then(() => {
                        window.location.href = 'programas_admin.php';
                    });
                    return;
                }

                Swal.fire({
                    title: 'Error',
                    text: data.error || 'No fue posible eliminar el programa.',
                    icon: 'error'
                });
            });
    });
}
</script>

<div class="modal fade" id="addProgramModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar curso o certificacion</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="programas_transac.php?action=add" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label><strong>Titulo</strong></label>
                                <input class="form-control" name="titulo" maxlength="100" required>
                            </div>

                            <div class="form-group">
                                <label><strong>Descripcion corta</strong></label>
                                <textarea class="form-control" name="descripcion_corta" rows="3" required></textarea>
                            </div>

                            <div class="form-group">
                                <label><strong>Descripcion completa</strong></label>
                                <textarea class="form-control" name="descripcion_completa" rows="5" required></textarea>
                            </div>

                            <?php if ($schemaFlags['detalles_programa']) : ?>
                                <div class="form-group">
                                    <label><strong>Detalles del programa</strong></label>
                                    <textarea class="form-control" name="detalles_programa" rows="4" placeholder="Escribe un detalle por linea"></textarea>
                                </div>
                            <?php endif; ?>

                            <?php if ($schemaFlags['beneficios']) : ?>
                                <div class="form-group">
                                    <label><strong>Beneficios</strong></label>
                                    <textarea class="form-control" name="beneficios" rows="4" placeholder="Escribe un beneficio por linea"></textarea>
                                </div>
                            <?php endif; ?>

                            <?php if ($schemaFlags['requisitos']) : ?>
                                <div class="form-group">
                                    <label><strong>Requisitos</strong></label>
                                    <textarea class="form-control" name="requisitos" rows="4" placeholder="Escribe un requisito por linea"></textarea>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <?php if ($schemaFlags['tipo_programa']) : ?>
                                <div class="form-group">
                                    <label><strong>Tipo</strong></label>
                                    <select class="form-control" name="tipo_programa">
                                        <?php foreach (atenea_capacitacion_type_options() as $value => $label) : ?>
                                            <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label><strong>Imagen</strong></label>
                                <input type="file" class="form-control-file" name="imagen" accept="image/*" required>
                                <small class="form-text text-muted">JPG, JPEG, PNG, GIF o WEBP. Maximo 2MB.</small>
                            </div>

                            <?php if ($schemaFlags['precio']) : ?>
                                <div class="form-group">
                                    <label><strong>Precio</strong></label>
                                    <input type="number" step="0.01" min="0" class="form-control" name="precio" value="100.00" required>
                                </div>
                            <?php endif; ?>

                            <?php if ($schemaFlags['duracion']) : ?>
                                <div class="form-group">
                                    <label><strong>Duracion</strong></label>
                                    <input class="form-control" name="duracion" maxlength="120" placeholder="Ej: 8 horas / 4 semanas">
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label><strong>Nivel</strong></label>
                                <select class="form-control" name="nivel" required>
                                    <option value="">Seleccionar nivel</option>
                                    <option value="Basico">Basico</option>
                                    <option value="Intermedio">Intermedio</option>
                                    <option value="Avanzado">Avanzado</option>
                                    <option value="Especializado">Especializado</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><strong>Instructor</strong></label>
                                <input class="form-control" name="instructor" maxlength="100" required>
                            </div>

                            <?php if ($schemaFlags['modalidad']) : ?>
                                <div class="form-group">
                                    <label><strong>Modalidad</strong></label>
                                    <input class="form-control" name="modalidad" maxlength="80" placeholder="Presencial, virtual o mixta">
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label><strong>Orden</strong></label>
                                <input type="number" class="form-control" name="orden" min="1" required>
                            </div>

                            <div class="form-group">
                                <label><strong>Estado</strong></label>
                                <select class="form-control" name="estado" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i> Guardar</button>
                    <button type="reset" class="btn btn-warning"><i class="fa fa-circle-notch fa-fw"></i> Reiniciar</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i> Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
