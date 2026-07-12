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

$action = trim((string) ($_GET['action'] ?? ''));

function programasUploadImage(array $file): array
{
    $targetDir = '../img/';
    $imageFileType = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    $newFileName = 'programa_' . uniqid() . '_' . time() . '.' . $imageFileType;
    $targetFile = $targetDir . $newFileName;

    $check = getimagesize((string) ($file['tmp_name'] ?? ''));
    if ($check === false) {
        return ['success' => false, 'message' => 'El archivo no es una imagen valida.'];
    }

    if ((int) ($file['size'] ?? 0) > 2000000) {
        return ['success' => false, 'message' => 'El archivo es muy grande. Maximo 2MB.'];
    }

    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
        return ['success' => false, 'message' => 'Solo se permiten archivos JPG, JPEG, PNG, GIF y WEBP.'];
    }

    if (move_uploaded_file((string) ($file['tmp_name'] ?? ''), $targetFile)) {
        return ['success' => true, 'filename' => $newFileName];
    }

    return ['success' => false, 'message' => 'No fue posible subir la imagen.'];
}

function programasRedirectWithAlert(string $message, string $target): void
{
    echo "<script>alert('" . addslashes($message) . "'); window.location.href='" . addslashes($target) . "';</script>";
    exit();
}

function programasCollectPayload(mysqli $db): array
{
    $schemaFlags = atenea_capacitacion_schema_flags($db);

    $titulo = trim((string) ($_POST['titulo'] ?? ''));
    $descripcionCorta = trim((string) ($_POST['descripcion_corta'] ?? ''));
    $descripcionCompleta = trim((string) ($_POST['descripcion_completa'] ?? ''));
    $nivel = trim((string) ($_POST['nivel'] ?? ''));
    $instructor = trim((string) ($_POST['instructor'] ?? ''));
    $orden = max(1, (int) ($_POST['orden'] ?? 1));
    $estado = isset($_POST['estado']) && (int) $_POST['estado'] === 0 ? 0 : 1;

    if ($titulo === '' || $descripcionCorta === '' || $descripcionCompleta === '' || $nivel === '' || $instructor === '') {
        throw new RuntimeException('Completa los campos obligatorios del programa antes de guardar.');
    }

    $payload = [
        'titulo' => "'" . mysqli_real_escape_string($db, $titulo) . "'",
        'descripcion_corta' => "'" . mysqli_real_escape_string($db, $descripcionCorta) . "'",
        'descripcion_completa' => "'" . mysqli_real_escape_string($db, $descripcionCompleta) . "'",
        'nivel' => "'" . mysqli_real_escape_string($db, $nivel) . "'",
        'instructor' => "'" . mysqli_real_escape_string($db, $instructor) . "'",
        'orden' => (string) $orden,
        'estado' => (string) $estado,
    ];

    if ($schemaFlags['tipo_programa']) {
        $payload['tipo_programa'] = "'" . mysqli_real_escape_string($db, atenea_capacitacion_normalize_type((string) ($_POST['tipo_programa'] ?? 'curso'))) . "'";
    }

    if ($schemaFlags['precio']) {
        $precio = trim((string) ($_POST['precio'] ?? '100'));
        $payload['precio'] = is_numeric($precio)
            ? "'" . mysqli_real_escape_string($db, (string) number_format((float) $precio, 2, '.', '')) . "'"
            : "'100.00'";
    }

    if ($schemaFlags['duracion']) {
        $duracion = trim((string) ($_POST['duracion'] ?? ''));
        $payload['duracion'] = $duracion !== ''
            ? "'" . mysqli_real_escape_string($db, $duracion) . "'"
            : 'NULL';
    }

    if ($schemaFlags['modalidad']) {
        $modalidad = trim((string) ($_POST['modalidad'] ?? ''));
        $payload['modalidad'] = $modalidad !== ''
            ? "'" . mysqli_real_escape_string($db, $modalidad) . "'"
            : 'NULL';
    }

    if ($schemaFlags['detalles_programa']) {
        $detalles = trim((string) ($_POST['detalles_programa'] ?? ''));
        $payload['detalles_programa'] = $detalles !== ''
            ? "'" . mysqli_real_escape_string($db, $detalles) . "'"
            : 'NULL';
    }

    if ($schemaFlags['beneficios']) {
        $beneficios = trim((string) ($_POST['beneficios'] ?? ''));
        $payload['beneficios'] = $beneficios !== ''
            ? "'" . mysqli_real_escape_string($db, $beneficios) . "'"
            : 'NULL';
    }

    if ($schemaFlags['requisitos']) {
        $requisitos = trim((string) ($_POST['requisitos'] ?? ''));
        $payload['requisitos'] = $requisitos !== ''
            ? "'" . mysqli_real_escape_string($db, $requisitos) . "'"
            : 'NULL';
    }

    return $payload;
}

function programasInsert(mysqli $db, array $payload): bool
{
    $sql = 'INSERT INTO programas_educativos (' . implode(', ', array_keys($payload)) . ') VALUES (' . implode(', ', array_values($payload)) . ')';

    return mysqli_query($db, $sql) === true;
}

function programasUpdate(mysqli $db, int $id, array $payload): bool
{
    $assignments = [];
    foreach ($payload as $column => $value) {
        $assignments[] = $column . ' = ' . $value;
    }

    $sql = 'UPDATE programas_educativos SET ' . implode(', ', $assignments) . ' WHERE id = ' . $id;

    return mysqli_query($db, $sql) === true;
}

switch ($action) {
    case 'add':
        if (!isset($_FILES['imagen']) || (int) ($_FILES['imagen']['error'] ?? 1) !== 0) {
            programasRedirectWithAlert('Selecciona una imagen para el programa.', 'programas_admin.php');
        }

        $uploadResult = programasUploadImage($_FILES['imagen']);
        if (!$uploadResult['success']) {
            programasRedirectWithAlert('Error: ' . $uploadResult['message'], 'programas_admin.php');
        }

        $imagen = $uploadResult['filename'];

        try {
            $payload = programasCollectPayload($db);
        } catch (Throwable $exception) {
            if (is_file('../img/' . $imagen)) {
                unlink('../img/' . $imagen);
            }
            programasRedirectWithAlert($exception->getMessage(), 'programas_admin.php');
        }

        $payload['imagen'] = "'" . mysqli_real_escape_string($db, $imagen) . "'";

        if (programasInsert($db, $payload)) {
            programasRedirectWithAlert('Programa guardado exitosamente.', 'programas_admin.php');
        }

        if (is_file('../img/' . $imagen)) {
            unlink('../img/' . $imagen);
        }

        programasRedirectWithAlert('Error al guardar el programa: ' . mysqli_error($db), 'programas_admin.php');
        break;

    case 'edit':
        $programaId = max(0, (int) ($_POST['id'] ?? 0));
        $currentImage = trim((string) ($_POST['current_image'] ?? ''));
        $newImage = null;

        try {
            $payload = programasCollectPayload($db);
        } catch (Throwable $exception) {
            programasRedirectWithAlert($exception->getMessage(), 'programas_edit.php?id=' . $programaId);
        }

        if (isset($_FILES['imagen']) && (int) ($_FILES['imagen']['error'] ?? 1) === 0) {
            $uploadResult = programasUploadImage($_FILES['imagen']);
            if (!$uploadResult['success']) {
                programasRedirectWithAlert('Error: ' . $uploadResult['message'], 'programas_edit.php?id=' . $programaId);
            }

            $newImage = $uploadResult['filename'];
            $payload['imagen'] = "'" . mysqli_real_escape_string($db, $newImage) . "'";
        }

        if (programasUpdate($db, $programaId, $payload)) {
            if ($newImage !== null && $currentImage !== '' && is_file('../img/' . $currentImage)) {
                unlink('../img/' . $currentImage);
            }

            programasRedirectWithAlert('Programa actualizado exitosamente.', 'programas_admin.php');
        }

        if ($newImage !== null && is_file('../img/' . $newImage)) {
            unlink('../img/' . $newImage);
        }

        programasRedirectWithAlert('Error al actualizar el programa: ' . mysqli_error($db), 'programas_edit.php?id=' . $programaId);
        break;

    default:
        header('Location: programas_admin.php');
        exit;
}

mysqli_close($db);
