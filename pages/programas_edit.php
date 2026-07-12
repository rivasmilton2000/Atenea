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

$programaId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($programaId <= 0) {
    header('Location: programas_admin.php');
    exit;
}

$schemaFlags = atenea_capacitacion_schema_flags($db);
$queryPrograma = "
    SELECT pe.*,
           " . atenea_capacitacion_select_sql($db, 'pe') . "
    FROM programas_educativos pe
    WHERE pe.id = {$programaId}
    LIMIT 1
";
$resultadoPrograma = mysqli_query($db, $queryPrograma) or die(mysqli_error($db));
$programa = mysqli_num_rows($resultadoPrograma) > 0 ? mysqli_fetch_assoc($resultadoPrograma) : null;

if (!$programa) {
    header('Location: programas_admin.php');
    exit;
}

$programType = atenea_capacitacion_normalize_type((string) ($programa['tipo_programa'] ?? 'curso'));
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">
            Editar curso o certificacion
            <a href="programas_admin.php" class="btn btn-secondary bg-gradient-secondary float-right" style="border-radius: 0px;">
                <i class="fas fa-fw fa-arrow-left"></i> Volver
            </a>
        </h4>
    </div>

    <div class="card-body">
        <form method="post" action="programas_transac.php?action=edit" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo (int) $programa['id']; ?>">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars((string) $programa['imagen'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label><strong>Titulo</strong></label>
                        <input class="form-control" name="titulo" maxlength="100" value="<?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><strong>Descripcion corta</strong></label>
                        <textarea class="form-control" name="descripcion_corta" rows="3" required><?php echo htmlspecialchars((string) $programa['descripcion_corta'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label><strong>Descripcion completa</strong></label>
                        <textarea class="form-control" name="descripcion_completa" rows="6" required><?php echo htmlspecialchars((string) $programa['descripcion_completa'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <?php if ($schemaFlags['detalles_programa']) : ?>
                        <div class="form-group">
                            <label><strong>Detalles del programa</strong></label>
                            <textarea class="form-control" name="detalles_programa" rows="4"><?php echo htmlspecialchars((string) ($programa['detalles_programa'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                    <?php endif; ?>

                    <?php if ($schemaFlags['beneficios']) : ?>
                        <div class="form-group">
                            <label><strong>Beneficios</strong></label>
                            <textarea class="form-control" name="beneficios" rows="4"><?php echo htmlspecialchars((string) ($programa['beneficios'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                    <?php endif; ?>

                    <?php if ($schemaFlags['requisitos']) : ?>
                        <div class="form-group">
                            <label><strong>Requisitos</strong></label>
                            <textarea class="form-control" name="requisitos" rows="4"><?php echo htmlspecialchars((string) ($programa['requisitos'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <?php if ($schemaFlags['tipo_programa']) : ?>
                        <div class="form-group">
                            <label><strong>Tipo</strong></label>
                            <select class="form-control" name="tipo_programa">
                                <?php foreach (atenea_capacitacion_type_options() as $value => $label) : ?>
                                    <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $programType === $value ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label><strong>Imagen actual</strong></label>
                        <div class="mb-3">
                            <img src="../img/<?php echo htmlspecialchars((string) $programa['imagen'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid rounded">
                        </div>
                        <label><strong>Cambiar imagen</strong> (opcional)</label>
                        <input type="file" class="form-control-file" name="imagen" accept="image/*">
                    </div>

                    <?php if ($schemaFlags['precio']) : ?>
                        <div class="form-group">
                            <label><strong>Precio</strong></label>
                            <input type="number" step="0.01" min="0" class="form-control" name="precio" value="<?php echo htmlspecialchars((string) atenea_capacitacion_price($programa), ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                    <?php endif; ?>

                    <?php if ($schemaFlags['duracion']) : ?>
                        <div class="form-group">
                            <label><strong>Duracion</strong></label>
                            <input class="form-control" name="duracion" maxlength="120" value="<?php echo htmlspecialchars((string) ($programa['duracion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label><strong>Nivel</strong></label>
                        <select class="form-control" name="nivel" required>
                            <option value="Basico" <?php echo (string) $programa['nivel'] === 'Basico' ? 'selected' : ''; ?>>Basico</option>
                            <option value="Intermedio" <?php echo (string) $programa['nivel'] === 'Intermedio' ? 'selected' : ''; ?>>Intermedio</option>
                            <option value="Avanzado" <?php echo (string) $programa['nivel'] === 'Avanzado' ? 'selected' : ''; ?>>Avanzado</option>
                            <option value="Especializado" <?php echo (string) $programa['nivel'] === 'Especializado' ? 'selected' : ''; ?>>Especializado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><strong>Instructor</strong></label>
                        <input class="form-control" name="instructor" maxlength="100" value="<?php echo htmlspecialchars((string) $programa['instructor'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <?php if ($schemaFlags['modalidad']) : ?>
                        <div class="form-group">
                            <label><strong>Modalidad</strong></label>
                            <input class="form-control" name="modalidad" maxlength="80" value="<?php echo htmlspecialchars((string) ($programa['modalidad'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label><strong>Orden</strong></label>
                        <input type="number" class="form-control" name="orden" min="1" value="<?php echo (int) $programa['orden']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label><strong>Estado</strong></label>
                        <select class="form-control" name="estado" required>
                            <option value="1" <?php echo (int) $programa['estado'] === 1 ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo (int) $programa['estado'] === 0 ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr>
            <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i> Actualizar</button>
            <a href="programas_admin.php" class="btn btn-danger"><i class="fa fa-times fa-fw"></i> Cancelar</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
