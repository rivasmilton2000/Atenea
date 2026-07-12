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

if (!atenea_capacitacion_phase_three_ready($db)) {
    atenea_render_auth_alert(
        'warning',
        'Migracion pendiente',
        'El modulo de certificados no esta disponible. Revisa la configuracion del entorno.',
        'curso_certificados_admin.php'
    );
}

$action = trim((string) ($_GET['action'] ?? ''));
$memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);

if (!function_exists('curso_certificados_redirect_with_alert')) {
    function curso_certificados_redirect_with_alert(string $message, string $target): void
    {
        echo "<script>alert('" . addslashes($message) . "'); window.location.href='" . addslashes($target) . "';</script>";
        exit();
    }
}

if (!function_exists('curso_certificados_redirect_url')) {
    function curso_certificados_redirect_url(array $enrollment): string
    {
        $programId = max(0, (int) ($enrollment['programa_id'] ?? 0));

        return $programId > 0
            ? 'curso_certificados_admin.php?programa_id=' . $programId
            : 'curso_certificados_admin.php';
    }
}

$enrollmentId = max(0, (int) ($_POST['enrollment_id'] ?? $_GET['enrollment_id'] ?? 0));
$enrollment = $enrollmentId > 0 ? atenea_capacitacion_fetch_enrollment_by_id($db, $enrollmentId) : null;

if (!$enrollment) {
    curso_certificados_redirect_with_alert('No encontramos la inscripcion seleccionada.', 'curso_certificados_admin.php');
}

$redirectUrl = curso_certificados_redirect_url($enrollment);

switch ($action) {
    case 'mark_finalized':
        $updatedEnrollment = atenea_capacitacion_mark_enrollment_finalized($db, $enrollmentId, $memberId);
        if (!$updatedEnrollment) {
            curso_certificados_redirect_with_alert('No fue posible marcar el curso como finalizado.', $redirectUrl);
        }

        curso_certificados_redirect_with_alert('El curso fue marcado como finalizado correctamente.', $redirectUrl);
        break;

    case 'approve':
        $updatedEnrollment = atenea_capacitacion_mark_enrollment_approved($db, $enrollmentId, $memberId);
        if (!$updatedEnrollment) {
            curso_certificados_redirect_with_alert('No fue posible aprobar el curso seleccionado.', $redirectUrl);
        }

        curso_certificados_redirect_with_alert('El curso quedo aprobado y el certificado fue habilitado.', $redirectUrl);
        break;

    case 'reset_approval':
        $updatedEnrollment = atenea_capacitacion_reset_enrollment_approval($db, $enrollmentId);
        if (!$updatedEnrollment) {
            curso_certificados_redirect_with_alert('No fue posible devolver la inscripcion a proceso.', $redirectUrl);
        }

        curso_certificados_redirect_with_alert('La inscripcion regreso a estado en proceso.', $redirectUrl);
        break;

    default:
        header('Location: ' . $redirectUrl);
        exit;
}
