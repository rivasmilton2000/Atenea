<?php
require 'session.php';
include '../includes/connection.php';
require_once '../includes/certificate_renderer.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
$isAuthorized = true;
$redirectUrl = 'dashboard_admin.php';

while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Personal' || $Aa == 'Estudiante' || $Aa == 'Docente' || $Aa == 'SuperAdmin') {
        $isAuthorized = false;
        if ($Aa == 'Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa == 'Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa == 'Docente') {
            $redirectUrl = "docentes_vista.php";
        } else {
            $redirectUrl = "sa_vista.php";
        }
    }
}

if (!$isAuthorized) {
    header('Location: ' . $redirectUrl);
    exit();
}

$certificateData = atenea_certificate_build_data($_GET);
$fileNameBase = preg_replace('/[^A-Za-z0-9_-]/', '_', atenea_certificate_upper((string) ($certificateData['certificate_name'] ?? 'certificado'))) ?? 'certificado';
$pdfBinary = atenea_certificate_pdf_binary($certificateData);

if (function_exists('ob_get_length') && ob_get_length()) {
    ob_end_clean();
}

header('Content-Type: application/pdf');
header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Disposition: inline; filename="certificado_' . $fileNameBase . '.pdf"');
header('Content-Length: ' . strlen($pdfBinary));

echo $pdfBinary;
exit();
