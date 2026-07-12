<?php
require 'session.php';
require_once '../includes/connection.php';

header('Content-Type: application/json; charset=UTF-8');

if (!logged_in() || !in_array((string) ($_SESSION['TYPE'] ?? ''), ['Admin', 'SuperAdmin'], true)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'No tienes permisos para eliminar programas.',
    ]);
    exit;
}

$programaId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($programaId <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'ID no proporcionado.',
    ]);
    exit;
}

$stmtPrograma = $db->prepare('SELECT imagen FROM programas_educativos WHERE id = ? LIMIT 1');
if (!$stmtPrograma) {
    echo json_encode([
        'success' => false,
        'error' => 'No fue posible preparar la consulta.',
    ]);
    exit;
}

$stmtPrograma->bind_param('i', $programaId);
$stmtPrograma->execute();
$resultadoPrograma = $stmtPrograma->get_result();
$programa = $resultadoPrograma instanceof mysqli_result ? $resultadoPrograma->fetch_assoc() : null;
$stmtPrograma->close();

if (!$programa) {
    echo json_encode([
        'success' => false,
        'error' => 'Programa no encontrado.',
    ]);
    exit;
}

$stmtDelete = $db->prepare('DELETE FROM programas_educativos WHERE id = ?');
if (!$stmtDelete) {
    echo json_encode([
        'success' => false,
        'error' => 'No fue posible preparar la eliminacion.',
    ]);
    exit;
}

$stmtDelete->bind_param('i', $programaId);
$deleted = $stmtDelete->execute();
$stmtDelete->close();

if (!$deleted) {
    echo json_encode([
        'success' => false,
        'error' => mysqli_error($db),
    ]);
    exit;
}

$imageName = trim((string) ($programa['imagen'] ?? ''));
if ($imageName !== '') {
    $imagePath = '../img/' . $imageName;
    if (is_file($imagePath)) {
        unlink($imagePath);
    }
}

echo json_encode(['success' => true]);

mysqli_close($db);
