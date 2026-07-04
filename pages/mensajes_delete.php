<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/app_security.php';

if ((string) ($_SESSION['TYPE'] ?? '') !== 'Docente') {
    atenea_render_auth_alert('warning', 'Acceso restringido', 'Solo docentes pueden eliminar sus mensajes.', atenea_dashboard_route_for_session());
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mensajes_docente_lista.php');
    exit;
}

$asignaturaId = filter_input(INPUT_POST, 'asignatura_id', FILTER_VALIDATE_INT) ?: 0;

try {
    atenea_require_csrf_token('mensajes_docente_delete', (string) ($_POST['csrf_token'] ?? ''));

    $mensajeId = filter_input(INPUT_POST, 'mensaje_id', FILTER_VALIDATE_INT);
    $docenteUserId = (int) ($_SESSION['MEMBER_ID'] ?? 0);

    if (!$mensajeId || !$asignaturaId || $docenteUserId <= 0) {
        throw new RuntimeException('Solicitud de eliminacion no valida.');
    }

    $stmt = $db->prepare(
        'SELECT mensaje_id, archivo
         FROM mensajes
         WHERE mensaje_id = ? AND asignatura_id = ? AND docente_id = ? AND estado = 1
         LIMIT 1'
    );
    if (!$stmt) {
        throw new RuntimeException('No se pudo consultar el mensaje.');
    }
    $stmt->bind_param('iii', $mensajeId, $asignaturaId, $docenteUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    $message = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
    if ($result instanceof mysqli_result) {
        mysqli_free_result($result);
    }
    $stmt->close();

    if (!$message) {
        throw new RuntimeException('El mensaje no existe o no te pertenece.');
    }

    $update = $db->prepare('UPDATE mensajes SET estado = 0 WHERE mensaje_id = ? AND docente_id = ? LIMIT 1');
    if (!$update) {
        throw new RuntimeException('No se pudo eliminar el mensaje.');
    }
    $update->bind_param('ii', $mensajeId, $docenteUserId);
    $update->execute();
    $update->close();

    $archivo = basename(trim((string) ($message['archivo'] ?? '')));
    if ($archivo !== '') {
        $baseDir = realpath(__DIR__ . '/archivos_mensajes');
        $filePath = realpath(__DIR__ . '/archivos_mensajes/' . $archivo);
        if ($baseDir !== false && $filePath !== false && strpos($filePath, $baseDir) === 0 && is_file($filePath)) {
            @unlink($filePath);
        }
    }

    header('Location: mensajes_docente.php?asignatura_id=' . urlencode((string) $asignaturaId));
    exit;
} catch (Throwable $exception) {
    atenea_render_auth_alert(
        'error',
        'Mensaje no eliminado',
        $exception->getMessage(),
        $asignaturaId > 0 ? 'mensajes_docente.php?asignatura_id=' . urlencode((string) $asignaturaId) : 'mensajes_docente_lista.php'
    );
}
