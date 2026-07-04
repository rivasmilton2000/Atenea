<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/app_security.php';

if ((string) ($_SESSION['TYPE'] ?? '') !== 'Docente') {
    atenea_render_auth_alert('warning', 'Acceso restringido', 'Solo docentes pueden publicar mensajes.', atenea_dashboard_route_for_session());
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mensajes_docente_lista.php');
    exit;
}

try {
    atenea_require_csrf_token('mensajes_docente_post', (string) ($_POST['csrf_token'] ?? ''));

    $asignaturaId = filter_input(INPUT_POST, 'asignatura_id', FILTER_VALIDATE_INT);
    $docenteUserId = (int) ($_SESSION['MEMBER_ID'] ?? 0);
    $employeeId = (int) ($_SESSION['EMPLOYEE_ID'] ?? 0);
    $mensaje = trim((string) ($_POST['mensaje'] ?? ''));

    if (!$asignaturaId || $docenteUserId <= 0 || $employeeId <= 0) {
        throw new RuntimeException('No se pudo validar la asignatura o el docente.');
    }

    if ($mensaje === '' || strlen($mensaje) > 2000) {
        throw new RuntimeException('El mensaje es obligatorio y no debe superar 2000 caracteres.');
    }

    $stmtSubject = $db->prepare(
        'SELECT da.da_id
         FROM docentes_asignaturas da
         WHERE da.profesor_id = ? AND da.materia_id = ? AND da.da_estado = 1
         LIMIT 1'
    );
    if (!$stmtSubject) {
        throw new RuntimeException('No se pudo validar la asignatura.');
    }
    $stmtSubject->bind_param('ii', $employeeId, $asignaturaId);
    $stmtSubject->execute();
    $subjectResult = $stmtSubject->get_result();
    $subject = $subjectResult instanceof mysqli_result ? $subjectResult->fetch_assoc() : null;
    if ($subjectResult instanceof mysqli_result) {
        mysqli_free_result($subjectResult);
    }
    $stmtSubject->close();

    if (!$subject) {
        throw new RuntimeException('No tienes permisos para publicar en esta asignatura.');
    }

    $archivoNombre = null;
    if (isset($_FILES['archivo']) && (int) ($_FILES['archivo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        if ((int) $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No fue posible subir el archivo.');
        }

        if ((int) $_FILES['archivo']['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException('El archivo no puede exceder los 5 MB.');
        }

        $permitidos = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'webp'];
        $nombreOriginal = (string) ($_FILES['archivo']['name'] ?? '');
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        if (!in_array($extension, $permitidos, true)) {
            throw new RuntimeException('Archivo no permitido.');
        }

        $carpeta = __DIR__ . '/archivos_mensajes';
        if (!is_dir($carpeta) && !mkdir($carpeta, 0775, true) && !is_dir($carpeta)) {
            throw new RuntimeException('No se pudo preparar la carpeta de mensajes.');
        }

        $archivoNombre = 'mensaje_' . $docenteUserId . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(5)) . '.' . $extension;
        $destino = $carpeta . DIRECTORY_SEPARATOR . $archivoNombre;

        if (!move_uploaded_file((string) $_FILES['archivo']['tmp_name'], $destino)) {
            throw new RuntimeException('No se pudo guardar el archivo.');
        }
    }

    $stmt = $db->prepare(
        'INSERT INTO mensajes (asignatura_id, docente_id, mensaje, archivo, estado)
         VALUES (?, ?, ?, ?, 1)'
    );
    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar el mensaje.');
    }
    $stmt->bind_param('iiss', $asignaturaId, $docenteUserId, $mensaje, $archivoNombre);
    $stmt->execute();
    $stmt->close();

    header('Location: mensajes_docente.php?asignatura_id=' . urlencode((string) $asignaturaId));
    exit;
} catch (Throwable $exception) {
    atenea_render_auth_alert(
        'error',
        'Mensaje no publicado',
        $exception->getMessage(),
        'mensajes_docente.php?asignatura_id=' . urlencode((string) ($_POST['asignatura_id'] ?? ''))
    );
}
