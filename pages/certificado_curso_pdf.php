<?php
require_once __DIR__ . '/session.php';
if (!logged_in()) {
    header('Location: ' . atenea_build_login_url('certificado_curso_pdf.php', 'login_required'));
    exit;
}

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/atenea_capacitacion.php';
require_once __DIR__ . '/../includes/certificate_renderer.php';

if (!atenea_capacitacion_phase_three_ready($db)) {
    atenea_render_auth_alert(
        'warning',
        'Migracion pendiente',
        'Debes aplicar Database/migrations/2026_07_09_capacitacion_finalizacion_certificados.sql para generar el PDF del certificado.',
        'record_escolar.php'
    );
}

if (!function_exists('certificado_curso_pdf_filename')) {
    function certificado_curso_pdf_filename(array $enrollment): string
    {
        $studentName = atenea_capacitacion_enrollment_full_name($enrollment);
        $courseName = (string) ($enrollment['programa_titulo'] ?? 'certificado');
        $base = trim($studentName . '_' . $courseName);
        $normalized = preg_replace('/[^A-Za-z0-9_-]+/', '_', $base) ?? 'certificado_atenea';
        $normalized = trim($normalized, '_');

        return $normalized !== '' ? $normalized : 'certificado_atenea';
    }
}

$requestedEnrollmentId = max(0, (int) ($_GET['enrollment_id'] ?? 0));
$download = !empty($_GET['download']);
$regenerate = !empty($_GET['regenerate']);
$currentRole = (string) ($_SESSION['TYPE'] ?? '');
$isAdminRole = in_array($currentRole, ['Admin', 'SuperAdmin'], true);
$isPublicUser = atenea_session_is_public_user();
$enrollment = null;

if ($isPublicUser) {
    $publicUserId = (int) ($_SESSION['PUBLIC_USER_ID'] ?? 0);
    $enrollments = atenea_capacitacion_fetch_enrollments_for_public_user($db, $publicUserId);

    foreach ($enrollments as $index => $row) {
        $updatedEnrollment = atenea_capacitacion_recalculate_enrollment_progress($db, (int) ($row['id'] ?? 0));
        if ($updatedEnrollment) {
            $enrollments[$index] = $updatedEnrollment;
            $row = $updatedEnrollment;
        }

        if (!atenea_capacitacion_certificate_eligible($row)) {
            continue;
        }

        if ($requestedEnrollmentId > 0) {
            if ((int) ($row['id'] ?? 0) === $requestedEnrollmentId) {
                $enrollment = $row;
                break;
            }
        } elseif ($enrollment === null) {
            $enrollment = $row;
        }
    }

    if ($requestedEnrollmentId > 0 && $enrollment === null) {
        atenea_render_auth_alert(
            'warning',
            'Acceso denegado',
            'Ese certificado no pertenece a tu cuenta o aun no esta disponible.',
            'record_escolar.php'
        );
    }
} elseif ($isAdminRole) {
    if ($requestedEnrollmentId <= 0) {
        atenea_render_auth_alert(
            'warning',
            'Inscripcion requerida',
            'Selecciona una inscripcion valida para generar el certificado.',
            'curso_certificados_admin.php'
        );
    }

    $enrollment = atenea_capacitacion_fetch_enrollment_by_id($db, $requestedEnrollmentId);
    if (!$enrollment || !atenea_capacitacion_certificate_eligible($enrollment)) {
        atenea_render_auth_alert(
            'warning',
            'Certificado no disponible',
            'La inscripcion seleccionada todavia no tiene el certificado habilitado.',
            'curso_certificados_admin.php'
        );
    }
} else {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}

if (!$enrollment) {
    atenea_render_auth_alert(
        'warning',
        'Certificado no disponible',
        'No fue posible localizar el certificado solicitado.',
        $isAdminRole ? 'curso_certificados_admin.php' : 'record_escolar.php'
    );
}

if ($regenerate && $isAdminRole) {
    atenea_capacitacion_mark_certificate_generated($db, (int) $enrollment['id'], true);
} else {
    atenea_capacitacion_mark_certificate_generated($db, (int) $enrollment['id'], false);
}

$certificateData = atenea_certificate_build_data(atenea_capacitacion_certificate_payload($enrollment));
$pdfBinary = atenea_certificate_pdf_binary($certificateData);
$fileName = certificado_curso_pdf_filename($enrollment);

if (function_exists('ob_get_length') && ob_get_length()) {
    ob_end_clean();
}

header('Content-Type: application/pdf');
header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="certificado_' . $fileName . '.pdf"');
header('Content-Length: ' . strlen($pdfBinary));

echo $pdfBinary;
exit;
