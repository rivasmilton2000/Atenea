<?php
require 'session.php';
require_once '../includes/connection.php';
require_once '../includes/atenea_capacitacion.php';

if (!logged_in()) {
    header('Location: login.php');
    exit;
}

if (!in_array((string) ($_SESSION['TYPE'] ?? ''), ['Admin', 'SuperAdmin'], true)) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}

if (!atenea_capacitacion_phase_two_ready($db)) {
    atenea_render_auth_alert(
        'warning',
        'Migracion pendiente',
        'El modulo de videos no esta disponible. Revisa la configuracion del entorno.',
        'curso_videos_admin.php'
    );
}

$action = trim((string) ($_GET['action'] ?? ''));

function courseVideosRedirectWithAlert(string $message, string $target): void
{
    echo "<script>alert('" . addslashes($message) . "'); window.location.href='" . addslashes($target) . "';</script>";
    exit();
}

function courseVideosNormalizeSourceType(?string $value): string
{
    return strtolower(trim((string) $value)) === 'upload' ? 'upload' : 'url';
}

function courseVideosUploadFile(array $file): array
{
    if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Selecciona un archivo de video valido.'];
    }

    if ((int) ($file['size'] ?? 0) > 50 * 1024 * 1024) {
        return ['success' => false, 'message' => 'El video no puede exceder los 50MB.'];
    }

    $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    $allowedExtensions = ['mp4', 'webm', 'ogg'];
    if (!in_array($extension, $allowedExtensions, true)) {
        return ['success' => false, 'message' => 'Solo se permiten archivos MP4, WEBM u OGG.'];
    }

    $uploadDir = '../uploads/course_videos';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        return ['success' => false, 'message' => 'No fue posible preparar la carpeta de videos.'];
    }

    $newFileName = 'course_video_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $targetPath = $uploadDir . '/' . $newFileName;

    if (!move_uploaded_file((string) ($file['tmp_name'] ?? ''), $targetPath)) {
        return ['success' => false, 'message' => 'No fue posible guardar el archivo de video.'];
    }

    return [
        'success' => true,
        'relative_path' => 'uploads/course_videos/' . $newFileName,
    ];
}

function courseVideosDeleteFile(?string $relativePath): void
{
    $relativePath = ltrim(str_replace('\\', '/', trim((string) $relativePath)), '/');
    if ($relativePath === '') {
        return;
    }

    $projectRoot = realpath(__DIR__ . '/..');
    $absolutePath = realpath(__DIR__ . '/../' . $relativePath);

    if ($projectRoot === false || $absolutePath === false || strpos($absolutePath, $projectRoot) !== 0 || !is_file($absolutePath)) {
        return;
    }

    unlink($absolutePath);
}

function courseVideosProgramRedirect(int $programId): string
{
    return $programId > 0 ? 'curso_videos_admin.php?programa_id=' . $programId : 'curso_videos_admin.php';
}

function courseVideosFetchEnrollmentProgramId(mysqli $db, int $enrollmentId): int
{
    if ($enrollmentId <= 0) {
        return 0;
    }

    $stmt = $db->prepare('SELECT programa_id FROM course_enrollments WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param('i', $enrollmentId);
    $stmt->execute();
    $stmt->bind_result($programId);
    $stmt->fetch();
    $stmt->close();

    return (int) $programId;
}

switch ($action) {
    case 'add':
        $programId = max(0, (int) ($_POST['programa_id'] ?? 0));
        $titulo = trim((string) ($_POST['titulo'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $sourceType = courseVideosNormalizeSourceType((string) ($_POST['source_type'] ?? 'url'));
        $videoUrl = trim((string) ($_POST['video_url'] ?? ''));
        $orden = max(1, (int) ($_POST['orden'] ?? 1));
        $estado = isset($_POST['estado']) && (int) $_POST['estado'] === 0 ? 0 : 1;
        $massEnabled = !empty($_POST['mass_enabled']) ? 1 : 0;
        $videoFilePath = null;
        $uploadedFilePath = null;

        if ($programId <= 0 || !atenea_capacitacion_fetch_program_by_id($db, $programId, false)) {
            courseVideosRedirectWithAlert('Selecciona un curso valido.', 'curso_videos_admin.php');
        }

        if ($titulo === '') {
            courseVideosRedirectWithAlert('Ingresa el titulo del video.', courseVideosProgramRedirect($programId));
        }

        if ($sourceType === 'upload') {
            $uploadResult = courseVideosUploadFile($_FILES['video_file'] ?? []);
            if (!$uploadResult['success']) {
                courseVideosRedirectWithAlert((string) $uploadResult['message'], courseVideosProgramRedirect($programId));
            }

            $uploadedFilePath = (string) $uploadResult['relative_path'];
            $videoFilePath = $uploadedFilePath;
            $videoUrl = '';
        } elseif ($videoUrl === '') {
            courseVideosRedirectWithAlert('Ingresa el enlace del video para continuar.', courseVideosProgramRedirect($programId));
        }

        $youtubeId = $videoUrl !== '' ? atenea_capacitacion_extract_youtube_id($videoUrl) : '';
        $stmtInsert = $db->prepare(
            "INSERT INTO course_videos
                (programa_id, titulo, descripcion, source_type, video_url, video_file_path, youtube_id, mass_enabled, orden, estado)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmtInsert) {
            if ($uploadedFilePath !== null) {
                courseVideosDeleteFile($uploadedFilePath);
            }
            courseVideosRedirectWithAlert('No fue posible preparar el guardado del video.', courseVideosProgramRedirect($programId));
        }

        $stmtInsert->bind_param(
            'issssssiii',
            $programId,
            $titulo,
            $descripcion,
            $sourceType,
            $videoUrl,
            $videoFilePath,
            $youtubeId,
            $massEnabled,
            $orden,
            $estado
        );

        if (!$stmtInsert->execute()) {
            $stmtInsert->close();
            if ($uploadedFilePath !== null) {
                courseVideosDeleteFile($uploadedFilePath);
            }
            courseVideosRedirectWithAlert('No fue posible guardar el video: ' . mysqli_error($db), courseVideosProgramRedirect($programId));
        }

        $stmtInsert->close();
        courseVideosRedirectWithAlert('Video guardado correctamente.', courseVideosProgramRedirect($programId));
        break;

    case 'edit':
        $videoId = max(0, (int) ($_POST['id'] ?? 0));
        $video = atenea_capacitacion_fetch_course_video_by_id($db, $videoId);
        if (!$video) {
            courseVideosRedirectWithAlert('No encontramos el video solicitado.', 'curso_videos_admin.php');
        }

        $programId = max(0, (int) ($_POST['programa_id'] ?? 0));
        $titulo = trim((string) ($_POST['titulo'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $sourceType = courseVideosNormalizeSourceType((string) ($_POST['source_type'] ?? 'url'));
        $videoUrl = trim((string) ($_POST['video_url'] ?? ''));
        $orden = max(1, (int) ($_POST['orden'] ?? 1));
        $estado = isset($_POST['estado']) && (int) $_POST['estado'] === 0 ? 0 : 1;
        $massEnabled = !empty($_POST['mass_enabled']) ? 1 : 0;
        $currentFilePath = (string) ($video['video_file_path'] ?? '');
        $videoFilePath = $currentFilePath !== '' ? $currentFilePath : null;
        $uploadedFilePath = null;

        if ($programId <= 0 || !atenea_capacitacion_fetch_program_by_id($db, $programId, false)) {
            courseVideosRedirectWithAlert('Selecciona un curso valido.', 'curso_videos_edit.php?id=' . $videoId);
        }

        if ($titulo === '') {
            courseVideosRedirectWithAlert('Ingresa el titulo del video.', 'curso_videos_edit.php?id=' . $videoId);
        }

        if ($sourceType === 'upload') {
            if (isset($_FILES['video_file']) && (int) ($_FILES['video_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $uploadResult = courseVideosUploadFile($_FILES['video_file']);
                if (!$uploadResult['success']) {
                    courseVideosRedirectWithAlert((string) $uploadResult['message'], 'curso_videos_edit.php?id=' . $videoId);
                }

                $uploadedFilePath = (string) $uploadResult['relative_path'];
                $videoFilePath = $uploadedFilePath;
            } elseif ($videoFilePath === null || trim($videoFilePath) === '') {
                courseVideosRedirectWithAlert('Debes subir un archivo de video para esta fuente.', 'curso_videos_edit.php?id=' . $videoId);
            }

            $videoUrl = '';
        } else {
            if ($videoUrl === '') {
                courseVideosRedirectWithAlert('Ingresa el enlace del video para continuar.', 'curso_videos_edit.php?id=' . $videoId);
            }

            $videoFilePath = null;
        }

        $youtubeId = $videoUrl !== '' ? atenea_capacitacion_extract_youtube_id($videoUrl) : '';
        $stmtUpdate = $db->prepare(
            "UPDATE course_videos
             SET programa_id = ?,
                 titulo = ?,
                 descripcion = ?,
                 source_type = ?,
                 video_url = ?,
                 video_file_path = ?,
                 youtube_id = ?,
                 mass_enabled = ?,
                 orden = ?,
                 estado = ?
             WHERE id = ?
             LIMIT 1"
        );

        if (!$stmtUpdate) {
            if ($uploadedFilePath !== null) {
                courseVideosDeleteFile($uploadedFilePath);
            }
            courseVideosRedirectWithAlert('No fue posible preparar la actualizacion del video.', 'curso_videos_edit.php?id=' . $videoId);
        }

        $stmtUpdate->bind_param(
            'issssssiiii',
            $programId,
            $titulo,
            $descripcion,
            $sourceType,
            $videoUrl,
            $videoFilePath,
            $youtubeId,
            $massEnabled,
            $orden,
            $estado,
            $videoId
        );

        if (!$stmtUpdate->execute()) {
            $stmtUpdate->close();
            if ($uploadedFilePath !== null) {
                courseVideosDeleteFile($uploadedFilePath);
            }
            courseVideosRedirectWithAlert('No fue posible actualizar el video: ' . mysqli_error($db), 'curso_videos_edit.php?id=' . $videoId);
        }

        $stmtUpdate->close();

        if ($uploadedFilePath !== null && $currentFilePath !== '' && $currentFilePath !== $uploadedFilePath) {
            courseVideosDeleteFile($currentFilePath);
        }

        if ($sourceType === 'url' && $currentFilePath !== '') {
            courseVideosDeleteFile($currentFilePath);
        }

        courseVideosRedirectWithAlert('Video actualizado correctamente.', 'curso_videos_edit.php?id=' . $videoId);
        break;

    case 'toggle_mass':
        $videoId = max(0, (int) ($_POST['video_id'] ?? 0));
        $enabled = !empty($_POST['enabled']);

        if (!atenea_capacitacion_set_mass_video_access($db, $videoId, $enabled)) {
            courseVideosRedirectWithAlert('No fue posible cambiar el acceso masivo.', 'curso_videos_edit.php?id=' . $videoId);
        }

        courseVideosRedirectWithAlert(
            $enabled ? 'Acceso masivo activado para el curso.' : 'Acceso masivo desactivado para el curso.',
            'curso_videos_edit.php?id=' . $videoId
        );
        break;

    case 'toggle_user_access':
        $videoId = max(0, (int) ($_POST['video_id'] ?? 0));
        $enrollmentId = max(0, (int) ($_POST['enrollment_id'] ?? 0));
        $enabled = !empty($_POST['enabled']);
        $video = atenea_capacitacion_fetch_course_video_by_id($db, $videoId);
        $enrollmentProgramId = courseVideosFetchEnrollmentProgramId($db, $enrollmentId);

        if (!$video || $enrollmentProgramId <= 0 || $enrollmentProgramId !== (int) $video['programa_id']) {
            courseVideosRedirectWithAlert('No fue posible validar la asignacion del video.', 'curso_videos_admin.php');
        }

        if (!atenea_capacitacion_set_user_video_access($db, $videoId, $enrollmentId, $enabled, (int) ($_SESSION['MEMBER_ID'] ?? 0))) {
            courseVideosRedirectWithAlert('No fue posible cambiar el acceso individual.', 'curso_videos_edit.php?id=' . $videoId);
        }

        courseVideosRedirectWithAlert(
            $enabled ? 'Acceso individual activado.' : 'Acceso individual desactivado.',
            'curso_videos_edit.php?id=' . $videoId
        );
        break;

    default:
        header('Location: curso_videos_admin.php');
        exit;
}
