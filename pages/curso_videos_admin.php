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

$phaseTwoReady = atenea_capacitacion_phase_two_ready($db);
$selectedProgramId = max(0, (int) ($_GET['programa_id'] ?? 0));
$programas = [];
$videos = $phaseTwoReady ? atenea_capacitacion_fetch_course_videos($db, $selectedProgramId, false) : [];
$enrollmentTotals = [];
$videoAccessTotals = [];

if (atenea_db_has_table($db, 'programas_educativos')) {
    $programResult = mysqli_query($db, "SELECT id, titulo, estado FROM programas_educativos ORDER BY orden ASC, id ASC") or die(mysqli_error($db));
    while ($programRow = mysqli_fetch_assoc($programResult)) {
        $programas[] = $programRow;
    }
}

if ($phaseTwoReady) {
    $resultEnrollmentTotals = mysqli_query($db, "SELECT programa_id, COUNT(*) AS total FROM course_enrollments GROUP BY programa_id") or die(mysqli_error($db));
    while ($row = mysqli_fetch_assoc($resultEnrollmentTotals)) {
        $enrollmentTotals[(int) $row['programa_id']] = (int) $row['total'];
    }

    $resultAccessTotals = mysqli_query($db, "SELECT course_video_id, SUM(CASE WHEN enabled = 1 THEN 1 ELSE 0 END) AS total FROM course_video_access GROUP BY course_video_id") or die(mysqli_error($db));
    while ($row = mysqli_fetch_assoc($resultAccessTotals)) {
        $videoAccessTotals[(int) $row['course_video_id']] = (int) $row['total'];
    }
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 1rem;">
        <h4 class="m-0 font-weight-bold text-primary">
            Videos de capacitacion
        </h4>
        <div class="d-flex flex-wrap" style="gap: 0.5rem;">
            <form method="get" class="form-inline">
                <label class="sr-only" for="programaFiltro">Curso</label>
                <select class="form-control mr-2" name="programa_id" id="programaFiltro">
                    <option value="0">Todos los cursos</option>
                    <?php foreach ($programas as $programa) : ?>
                        <option value="<?php echo (int) $programa['id']; ?>" <?php echo $selectedProgramId === (int) $programa['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-outline-primary">Filtrar</button>
            </form>
            <a href="#" data-toggle="modal" data-target="#addCourseVideoModal" class="btn btn-primary bg-gradient-primary">
                <i class="fas fa-fw fa-plus"></i> Agregar video
            </a>
        </div>
    </div>

    <div class="card-body">
        <?php if (!$phaseTwoReady) : ?>
            <div class="alert alert-warning">
                El modulo de videos no esta disponible. Revisa la configuracion del entorno.
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-left-primary shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Videos registrados</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($videos); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-left-success shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Cursos disponibles</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($programas); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-left-info shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Curso filtrado</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php
                            if ($selectedProgramId > 0) {
                                $filteredLabel = 'Curso #' . $selectedProgramId;
                                foreach ($programas as $programa) {
                                    if ((int) $programa['id'] === $selectedProgramId) {
                                        $filteredLabel = (string) $programa['titulo'];
                                        break;
                                    }
                                }
                                echo htmlspecialchars($filteredLabel, ENT_QUOTES, 'UTF-8');
                            } else {
                                echo 'Todos';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>CURSO</th>
                        <th>TITULO</th>
                        <th>FUENTE</th>
                        <th>ACCESO</th>
                        <th>ORDEN</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($videos as $video) : ?>
                        <?php
                        $sourceMeta = atenea_capacitacion_video_source_meta($video);
                        $programEnrollmentTotal = $enrollmentTotals[(int) ($video['programa_id'] ?? 0)] ?? 0;
                        $individualAccessTotal = $videoAccessTotals[(int) ($video['id'] ?? 0)] ?? 0;
                        $accessLabel = !empty($video['mass_enabled'])
                            ? 'Todos los inscritos (' . $programEnrollmentTotal . ')'
                            : $individualAccessTotal . ' acceso(s) individual(es)';
                        ?>
                        <tr>
                            <td><?php echo (int) $video['id']; ?></td>
                            <td><?php echo htmlspecialchars((string) ($video['programa_titulo'] ?? 'Curso'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string) ($video['titulo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo htmlspecialchars((string) $sourceMeta['label'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($accessLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo (int) ($video['orden'] ?? 0); ?></td>
                            <td>
                                <span class="badge <?php echo !empty($video['estado']) ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo !empty($video['estado']) ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td align="right">
                                <a class="btn btn-primary bg-gradient-primary btn-sm" href="curso_videos_edit.php?id=<?php echo (int) $video['id']; ?>">
                                    <i class="fas fa-fw fa-edit"></i> Editar y accesos
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addCourseVideoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar video de capacitacion</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="curso_videos_transac.php?action=add" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label><strong>Curso o certificacion</strong></label>
                                <select class="form-control" name="programa_id" required>
                                    <option value="">Seleccionar</option>
                                    <?php foreach ($programas as $programa) : ?>
                                        <option value="<?php echo (int) $programa['id']; ?>">
                                            <?php echo htmlspecialchars((string) $programa['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><strong>Titulo</strong></label>
                                <input class="form-control" name="titulo" maxlength="150" required>
                            </div>

                            <div class="form-group">
                                <label><strong>Descripcion</strong></label>
                                <textarea class="form-control" name="descripcion" rows="4" placeholder="Describe brevemente el contenido del video"></textarea>
                            </div>

                            <div class="form-group">
                                <label><strong>Tipo de fuente</strong></label>
                                <select class="form-control" name="source_type">
                                    <option value="url">Enlace</option>
                                    <option value="upload">Archivo</option>
                                </select>
                                <small class="form-text text-muted">Si eliges enlace, puedes usar YouTube o un enlace directo. Si eliges archivo, sube MP4, WEBM u OGG.</small>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label><strong>Enlace del video</strong></label>
                                <input class="form-control" name="video_url" placeholder="https://...">
                            </div>

                            <div class="form-group">
                                <label><strong>Archivo de video</strong></label>
                                <input type="file" class="form-control-file" name="video_file" accept=".mp4,.webm,.ogg,video/mp4,video/webm,video/ogg">
                                <small class="form-text text-muted">Maximo recomendado: 50MB por archivo.</small>
                            </div>

                            <div class="form-group">
                                <label><strong>Orden</strong></label>
                                <input type="number" class="form-control" name="orden" min="1" value="1" required>
                            </div>

                            <div class="form-group">
                                <label><strong>Estado</strong></label>
                                <select class="form-control" name="estado">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><strong>Acceso masivo</strong></label>
                                <select class="form-control" name="mass_enabled">
                                    <option value="0">No, activar por usuario</option>
                                    <option value="1">Si, activar para todo el curso</option>
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
