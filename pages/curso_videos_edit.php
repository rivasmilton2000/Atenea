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

if (!atenea_capacitacion_phase_two_ready($db)) {
    atenea_render_auth_alert(
        'warning',
        'Migracion pendiente',
        'Aplica Database/migrations/2026_07_09_capacitacion_acceso_videos.sql para habilitar este modulo.',
        'curso_videos_admin.php'
    );
}

$videoId = max(0, (int) ($_GET['id'] ?? 0));
$video = atenea_capacitacion_fetch_course_video_by_id($db, $videoId);

if (!$video) {
    header('Location: curso_videos_admin.php');
    exit;
}

$programas = [];
$programResult = mysqli_query($db, "SELECT id, titulo FROM programas_educativos ORDER BY orden ASC, id ASC") or die(mysqli_error($db));
while ($programRow = mysqli_fetch_assoc($programResult)) {
    $programas[] = $programRow;
}

$enrollments = atenea_capacitacion_fetch_program_enrollments($db, (int) $video['programa_id']);
$accessMap = atenea_capacitacion_fetch_video_access_map($db, (int) $video['id']);
$sourceMeta = atenea_capacitacion_video_source_meta($video);
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">
            Editar video de capacitacion
            <a href="curso_videos_admin.php<?php echo (int) $video['programa_id'] > 0 ? '?programa_id=' . (int) $video['programa_id'] : ''; ?>" class="btn btn-secondary bg-gradient-secondary float-right">
                <i class="fas fa-fw fa-arrow-left"></i> Volver
            </a>
        </h4>
    </div>

    <div class="card-body">
        <form method="post" action="curso_videos_transac.php?action=edit" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo (int) $video['id']; ?>">
            <input type="hidden" name="current_file_path" value="<?php echo htmlspecialchars((string) ($video['video_file_path'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="current_video_url" value="<?php echo htmlspecialchars((string) ($video['video_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

            <div class="row">
                <div class="col-md-7">
                    <div class="form-group">
                        <label><strong>Curso o certificacion</strong></label>
                        <select class="form-control" name="programa_id" required>
                            <?php foreach ($programas as $programa) : ?>
                                <option value="<?php echo (int) $programa['id']; ?>" <?php echo (int) $video['programa_id'] === (int) $programa['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><strong>Titulo</strong></label>
                        <input class="form-control" name="titulo" maxlength="150" value="<?php echo htmlspecialchars((string) ($video['titulo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><strong>Descripcion</strong></label>
                        <textarea class="form-control" name="descripcion" rows="4"><?php echo htmlspecialchars((string) ($video['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label><strong>Tipo de fuente</strong></label>
                        <select class="form-control" name="source_type">
                            <option value="url" <?php echo (string) ($video['source_type'] ?? 'url') === 'url' ? 'selected' : ''; ?>>Enlace</option>
                            <option value="upload" <?php echo (string) ($video['source_type'] ?? 'url') === 'upload' ? 'selected' : ''; ?>>Archivo</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="form-group">
                        <label><strong>Enlace del video</strong></label>
                        <input class="form-control" name="video_url" value="<?php echo htmlspecialchars((string) ($video['video_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://...">
                    </div>

                    <div class="form-group">
                        <label><strong>Reemplazar archivo</strong> (opcional)</label>
                        <input type="file" class="form-control-file" name="video_file" accept=".mp4,.webm,.ogg,video/mp4,video/webm,video/ogg">
                    </div>

                    <div class="form-group">
                        <label><strong>Orden</strong></label>
                        <input type="number" class="form-control" name="orden" min="1" value="<?php echo (int) ($video['orden'] ?? 1); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><strong>Estado</strong></label>
                        <select class="form-control" name="estado">
                            <option value="1" <?php echo !empty($video['estado']) ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo empty($video['estado']) ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><strong>Acceso masivo</strong></label>
                        <select class="form-control" name="mass_enabled">
                            <option value="0" <?php echo empty($video['mass_enabled']) ? 'selected' : ''; ?>>No, activar por usuario</option>
                            <option value="1" <?php echo !empty($video['mass_enabled']) ? 'selected' : ''; ?>>Si, activar para todo el curso</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="alert alert-light border mt-3">
                <strong>Fuente actual:</strong> <?php echo htmlspecialchars((string) $sourceMeta['label'], ENT_QUOTES, 'UTF-8'); ?>
                <?php if ((string) $sourceMeta['link_url'] !== '') : ?>
                    <a href="<?php echo htmlspecialchars((string) $sourceMeta['link_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm ml-2">
                        Abrir recurso actual
                    </a>
                <?php endif; ?>
            </div>

            <hr>
            <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i> Actualizar video</button>
            <a href="curso_videos_admin.php<?php echo (int) $video['programa_id'] > 0 ? '?programa_id=' . (int) $video['programa_id'] : ''; ?>" class="btn btn-danger"><i class="fa fa-times fa-fw"></i> Cancelar</a>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 0.75rem;">
        <h5 class="m-0 font-weight-bold text-primary">Acceso al video</h5>
        <form method="post" action="curso_videos_transac.php?action=toggle_mass" class="m-0">
            <input type="hidden" name="video_id" value="<?php echo (int) $video['id']; ?>">
            <input type="hidden" name="enabled" value="<?php echo !empty($video['mass_enabled']) ? '0' : '1'; ?>">
            <button type="submit" class="btn <?php echo !empty($video['mass_enabled']) ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                <?php echo !empty($video['mass_enabled']) ? 'Desactivar acceso masivo' : 'Activar para todo el curso'; ?>
            </button>
        </form>
    </div>

    <div class="card-body">
        <?php if (!empty($video['mass_enabled'])) : ?>
            <div class="alert alert-success">
                El acceso masivo esta activo. Todos los usuarios inscritos en <strong><?php echo htmlspecialchars((string) ($video['programa_titulo'] ?? 'este curso'), ENT_QUOTES, 'UTF-8'); ?></strong>
                pueden ver este video sin activar accesos individuales.
            </div>
        <?php else : ?>
            <div class="alert alert-info">
                El acceso masivo esta inactivo. Usa la tabla para habilitar o deshabilitar este video por usuario.
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>INSCRIPCION</th>
                        <th>ESTUDIANTE</th>
                        <th>CORREO</th>
                        <th>ESTADO CURSO</th>
                        <th>APROBACION</th>
                        <th>ACCESO ACTUAL</th>
                        <th>ACCION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($enrollments === []) : ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Todavia no hay usuarios inscritos en este curso.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($enrollments as $enrollment) : ?>
                        <?php
                        $courseStatusMeta = atenea_capacitacion_course_status_meta((string) ($enrollment['estado_curso'] ?? ''));
                        $approvalStatusMeta = atenea_capacitacion_approval_status_meta((string) ($enrollment['estado_aprobacion'] ?? ''));
                        $accessRow = $accessMap[(int) $enrollment['id']] ?? null;
                        $individualEnabled = $accessRow && !empty($accessRow['enabled']);
                        $effectiveAccessLabel = !empty($video['mass_enabled'])
                            ? 'Activo por curso'
                            : ($individualEnabled ? 'Activo individual' : 'Pendiente');
                        ?>
                        <tr>
                            <td><?php echo (int) $enrollment['id']; ?></td>
                            <td><?php echo htmlspecialchars(trim((string) ($enrollment['FIRST_NAME'] ?? '') . ' ' . (string) ($enrollment['LAST_NAME'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string) ($enrollment['EMAIL'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span class="badge badge-<?php echo htmlspecialchars((string) $courseStatusMeta['class'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars((string) $courseStatusMeta['label'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo htmlspecialchars((string) $approvalStatusMeta['class'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars((string) $approvalStatusMeta['label'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($effectiveAccessLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?php if (!empty($video['mass_enabled'])) : ?>
                                    <span class="text-muted">Controlado por acceso masivo</span>
                                <?php else : ?>
                                    <form method="post" action="curso_videos_transac.php?action=toggle_user_access" class="m-0">
                                        <input type="hidden" name="video_id" value="<?php echo (int) $video['id']; ?>">
                                        <input type="hidden" name="enrollment_id" value="<?php echo (int) $enrollment['id']; ?>">
                                        <input type="hidden" name="enabled" value="<?php echo $individualEnabled ? '0' : '1'; ?>">
                                        <button type="submit" class="btn btn-sm <?php echo $individualEnabled ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                                            <?php echo $individualEnabled ? 'Desactivar' : 'Activar'; ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
