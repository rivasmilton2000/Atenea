<?php
require 'session.php';
require_once '../includes/connection.php';
require_once '../includes/atenea_catalog.php';

if (!logged_in() || atenea_session_is_public_user()) {
    header('Location: login.php');
    exit;
}

if (!in_array((string) ($_SESSION['TYPE'] ?? ''), ['Admin', 'SuperAdmin'], true)) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}

$action = $_GET['action'] ?? '';

function uploadImage(array $file): array
{
    $targetDir = "../img/";
    $imageFileType = strtolower(pathinfo((string) ($file["name"] ?? ''), PATHINFO_EXTENSION));
    $newFileName = 'producto_' . uniqid() . '_' . time() . '.' . $imageFileType;
    $targetFile = $targetDir . $newFileName;

    $check = getimagesize((string) ($file["tmp_name"] ?? ''));
    if ($check === false) {
        return ['success' => false, 'message' => 'El archivo no es una imagen.'];
    }

    if ((int) ($file["size"] ?? 0) > 2000000) {
        return ['success' => false, 'message' => 'El archivo es muy grande. Máximo 2MB.'];
    }

    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
        return ['success' => false, 'message' => 'Solo se permiten archivos JPG, JPEG, PNG, GIF y WEBP.'];
    }

    if (move_uploaded_file((string) ($file["tmp_name"] ?? ''), $targetFile)) {
        return ['success' => true, 'filename' => $newFileName];
    }

    return ['success' => false, 'message' => 'Error al subir el archivo.'];
}

function productosRedirectWithAlert(string $message, string $target): void
{
    echo "<script>alert('" . addslashes($message) . "'); window.location.href='" . addslashes($target) . "';</script>";
    exit();
}

function productosCollectPayload(mysqli $db): array
{
    $flags = atenea_catalog_product_schema_flags($db);
    $videoUrlRaw = trim((string) ($_POST['video_url'] ?? ''));
    $videoUrl = $videoUrlRaw !== '' ? atenea_catalog_normalize_video_url($videoUrlRaw) : '';
    $videoActivo = isset($_POST['video_activo']) && (int) $_POST['video_activo'] === 1 ? 1 : 0;

    if ($videoUrlRaw !== '' && $videoUrl === '') {
        throw new RuntimeException('Ingresa un enlace de YouTube válido para el video.');
    }

    if ($videoUrl === '') {
        $videoActivo = 0;
    }

    $payload = [
        'nombre' => "'" . mysqli_real_escape_string($db, trim((string) ($_POST['nombre'] ?? ''))) . "'",
        'descripcion_corta' => "'" . mysqli_real_escape_string($db, trim((string) ($_POST['descripcion_corta'] ?? ''))) . "'",
        'descripcion' => "'" . mysqli_real_escape_string($db, trim((string) ($_POST['descripcion'] ?? ''))) . "'",
        'precio' => "'" . mysqli_real_escape_string($db, trim((string) ($_POST['precio'] ?? '0'))) . "'",
        'precio_descuento' => trim((string) ($_POST['precio_descuento'] ?? '')) !== ''
            ? "'" . mysqli_real_escape_string($db, trim((string) $_POST['precio_descuento'])) . "'"
            : 'NULL',
        'categoria_id' => (string) max(0, (int) ($_POST['categoria_id'] ?? 0)),
        'stock' => (string) max(0, (int) ($_POST['stock'] ?? 0)),
        'destacado' => isset($_POST['destacado']) ? (string) (int) $_POST['destacado'] : '0',
        'estado' => isset($_POST['estado']) ? (string) (int) $_POST['estado'] : '1',
    ];

    if ($flags['tipo_oferta']) {
        $payload['tipo_oferta'] = "'" . mysqli_real_escape_string($db, atenea_catalog_normalize_type((string) ($_POST['tipo_oferta'] ?? 'producto'))) . "'";
    }

    if ($flags['duracion']) {
        $duracion = trim((string) ($_POST['duracion'] ?? ''));
        $payload['duracion'] = $duracion !== ''
            ? "'" . mysqli_real_escape_string($db, $duracion) . "'"
            : 'NULL';
    }

    if ($flags['video_url']) {
        $payload['video_url'] = $videoUrl !== ''
            ? "'" . mysqli_real_escape_string($db, $videoUrl) . "'"
            : 'NULL';
    }

    if ($flags['video_activo']) {
        $payload['video_activo'] = (string) $videoActivo;
    }

    return $payload;
}

function productosInsert(mysqli $db, array $payload): bool
{
    $sql = 'INSERT INTO productos (' . implode(', ', array_keys($payload)) . ') VALUES (' . implode(', ', array_values($payload)) . ')';

    return mysqli_query($db, $sql) === true;
}

function productosUpdate(mysqli $db, int $id, array $payload): bool
{
    $assignments = [];
    foreach ($payload as $column => $value) {
        $assignments[] = $column . ' = ' . $value;
    }

    $sql = 'UPDATE productos SET ' . implode(', ', $assignments) . ' WHERE id = ' . $id;

    return mysqli_query($db, $sql) === true;
}

switch ($action) {
    case 'add':
        if (!isset($_FILES['imagen']) || (int) ($_FILES['imagen']['error'] ?? 1) !== 0) {
            productosRedirectWithAlert('Por favor selecciona una imagen.', 'productos_add.php');
        }

        $uploadResult = uploadImage($_FILES['imagen']);
        if (!$uploadResult['success']) {
            productosRedirectWithAlert('Error: ' . $uploadResult['message'], 'productos_add.php');
        }

        $imagen = $uploadResult['filename'];

        try {
            $payload = productosCollectPayload($db);
        } catch (Throwable $exception) {
            if (is_file("../img/" . $imagen)) {
                unlink("../img/" . $imagen);
            }
            productosRedirectWithAlert($exception->getMessage(), 'productos_add.php');
        }

        $payload['imagen'] = "'" . mysqli_real_escape_string($db, $imagen) . "'";

        if (productosInsert($db, $payload)) {
            productosRedirectWithAlert('Elemento agregado exitosamente.', 'productos_admin.php');
        }

        if (is_file("../img/" . $imagen)) {
            unlink("../img/" . $imagen);
        }
        productosRedirectWithAlert('Error al agregar el elemento: ' . mysqli_error($db), 'productos_add.php');
        break;

    case 'edit':
        $id = max(0, (int) ($_POST['id'] ?? 0));
        $currentImage = mysqli_real_escape_string($db, (string) ($_POST['current_image'] ?? ''));
        $newImage = null;

        try {
            $payload = productosCollectPayload($db);
        } catch (Throwable $exception) {
            productosRedirectWithAlert($exception->getMessage(), 'productos_edit.php?id=' . $id);
        }

        if (isset($_FILES['imagen']) && (int) ($_FILES['imagen']['error'] ?? 1) === 0) {
            $uploadResult = uploadImage($_FILES['imagen']);
            if (!$uploadResult['success']) {
                productosRedirectWithAlert('Error: ' . $uploadResult['message'], 'productos_edit.php?id=' . $id);
            }

            $newImage = $uploadResult['filename'];
            $payload['imagen'] = "'" . mysqli_real_escape_string($db, $newImage) . "'";
        }

        if (productosUpdate($db, $id, $payload)) {
            if ($newImage !== null && $currentImage !== '' && is_file("../img/" . $currentImage)) {
                unlink("../img/" . $currentImage);
            }
            productosRedirectWithAlert('Elemento actualizado exitosamente.', 'productos_admin.php');
        }

        if ($newImage !== null && is_file("../img/" . $newImage)) {
            unlink("../img/" . $newImage);
        }
        productosRedirectWithAlert('Error al actualizar el elemento: ' . mysqli_error($db), 'productos_edit.php?id=' . $id);
        break;

    default:
        header('Location: productos_admin.php');
        exit();
}

mysqli_close($db);
?>
SESSION['TYPE'] ?? ''), ['Admin', 'SuperAdmin'], true)) {
    header('Location: ' . atenea_dashboard_route_for_session());
    exit;
}
$action = $_GET['action'] ?? '';

function uploadImage(array $file): array
{
    $targetDir = "../img/";
    $imageFileType = strtolower(pathinfo((string) ($file["name"] ?? ''), PATHINFO_EXTENSION));
    $newFileName = 'producto_' . uniqid() . '_' . time() . '.' . $imageFileType;
    $targetFile = $targetDir . $newFileName;

    $check = getimagesize((string) ($file["tmp_name"] ?? ''));
    if ($check === false) {
        return ['success' => false, 'message' => 'El archivo no es una imagen.'];
    }

    if ((int) ($file["size"] ?? 0) > 2000000) {
        return ['success' => false, 'message' => 'El archivo es muy grande. Máximo 2MB.'];
    }

    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
        return ['success' => false, 'message' => 'Solo se permiten archivos JPG, JPEG, PNG, GIF y WEBP.'];
    }

    if (move_uploaded_file((string) ($file["tmp_name"] ?? ''), $targetFile)) {
        return ['success' => true, 'filename' => $newFileName];
    }

    return ['success' => false, 'message' => 'Error al subir el archivo.'];
}

function productosRedirectWithAlert(string $message, string $target): void
{
    echo "<script>alert('" . addslashes($message) . "'); window.location.href='" . addslashes($target) . "';</script>";
    exit();
}

function productosCollectPayload(mysqli $db): array
{
    $flags = atenea_catalog_product_schema_flags($db);
    $videoUrlRaw = trim((string) ($_POST['video_url'] ?? ''));
    $videoUrl = $videoUrlRaw !== '' ? atenea_catalog_normalize_video_url($videoUrlRaw) : '';
    $videoActivo = isset($_POST['video_activo']) && (int) $_POST['video_activo'] === 1 ? 1 : 0;

    if ($videoUrlRaw !== '' && $videoUrl === '') {
        throw new RuntimeException('Ingresa un enlace de YouTube válido para el video.');
    }

    if ($videoUrl === '') {
        $videoActivo = 0;
    }

    $payload = [
        'nombre' => "'" . mysqli_real_escape_string($db, trim((string) ($_POST['nombre'] ?? ''))) . "'",
        'descripcion_corta' => "'" . mysqli_real_escape_string($db, trim((string) ($_POST['descripcion_corta'] ?? ''))) . "'",
        'descripcion' => "'" . mysqli_real_escape_string($db, trim((string) ($_POST['descripcion'] ?? ''))) . "'",
        'precio' => "'" . mysqli_real_escape_string($db, trim((string) ($_POST['precio'] ?? '0'))) . "'",
        'precio_descuento' => trim((string) ($_POST['precio_descuento'] ?? '')) !== ''
            ? "'" . mysqli_real_escape_string($db, trim((string) $_POST['precio_descuento'])) . "'"
            : 'NULL',
        'categoria_id' => (string) max(0, (int) ($_POST['categoria_id'] ?? 0)),
        'stock' => (string) max(0, (int) ($_POST['stock'] ?? 0)),
        'destacado' => isset($_POST['destacado']) ? (string) (int) $_POST['destacado'] : '0',
        'estado' => isset($_POST['estado']) ? (string) (int) $_POST['estado'] : '1',
    ];

    if ($flags['tipo_oferta']) {
        $payload['tipo_oferta'] = "'" . mysqli_real_escape_string($db, atenea_catalog_normalize_type((string) ($_POST['tipo_oferta'] ?? 'producto'))) . "'";
    }

    if ($flags['duracion']) {
        $duracion = trim((string) ($_POST['duracion'] ?? ''));
        $payload['duracion'] = $duracion !== ''
            ? "'" . mysqli_real_escape_string($db, $duracion) . "'"
            : 'NULL';
    }

    if ($flags['video_url']) {
        $payload['video_url'] = $videoUrl !== ''
            ? "'" . mysqli_real_escape_string($db, $videoUrl) . "'"
            : 'NULL';
    }

    if ($flags['video_activo']) {
        $payload['video_activo'] = (string) $videoActivo;
    }

    return $payload;
}

function productosInsert(mysqli $db, array $payload): bool
{
    $sql = 'INSERT INTO productos (' . implode(', ', array_keys($payload)) . ') VALUES (' . implode(', ', array_values($payload)) . ')';

    return mysqli_query($db, $sql) === true;
}

function productosUpdate(mysqli $db, int $id, array $payload): bool
{
    $assignments = [];
    foreach ($payload as $column => $value) {
        $assignments[] = $column . ' = ' . $value;
    }

    $sql = 'UPDATE productos SET ' . implode(', ', $assignments) . ' WHERE id = ' . $id;

    return mysqli_query($db, $sql) === true;
}

switch ($action) {
    case 'add':
        if (!isset($_FILES['imagen']) || (int) ($_FILES['imagen']['error'] ?? 1) !== 0) {
            productosRedirectWithAlert('Por favor selecciona una imagen.', 'productos_add.php');
        }

        $uploadResult = uploadImage($_FILES['imagen']);
        if (!$uploadResult['success']) {
            productosRedirectWithAlert('Error: ' . $uploadResult['message'], 'productos_add.php');
        }

        $imagen = $uploadResult['filename'];

        try {
            $payload = productosCollectPayload($db);
        } catch (Throwable $exception) {
            if (is_file("../img/" . $imagen)) {
                unlink("../img/" . $imagen);
            }
            productosRedirectWithAlert($exception->getMessage(), 'productos_add.php');
        }

        $payload['imagen'] = "'" . mysqli_real_escape_string($db, $imagen) . "'";

        if (productosInsert($db, $payload)) {
            productosRedirectWithAlert('Elemento agregado exitosamente.', 'productos_admin.php');
        }

        if (is_file("../img/" . $imagen)) {
            unlink("../img/" . $imagen);
        }
        productosRedirectWithAlert('Error al agregar el elemento: ' . mysqli_error($db), 'productos_add.php');
        break;

    case 'edit':
        $id = max(0, (int) ($_POST['id'] ?? 0));
        $currentImage = mysqli_real_escape_string($db, (string) ($_POST['current_image'] ?? ''));
        $newImage = null;

        try {
            $payload = productosCollectPayload($db);
        } catch (Throwable $exception) {
            productosRedirectWithAlert($exception->getMessage(), 'productos_edit.php?id=' . $id);
        }

        if (isset($_FILES['imagen']) && (int) ($_FILES['imagen']['error'] ?? 1) === 0) {
            $uploadResult = uploadImage($_FILES['imagen']);
            if (!$uploadResult['success']) {
                productosRedirectWithAlert('Error: ' . $uploadResult['message'], 'productos_edit.php?id=' . $id);
            }

            $newImage = $uploadResult['filename'];
            $payload['imagen'] = "'" . mysqli_real_escape_string($db, $newImage) . "'";
        }

        if (productosUpdate($db, $id, $payload)) {
            if ($newImage !== null && $currentImage !== '' && is_file("../img/" . $currentImage)) {
                unlink("../img/" . $currentImage);
            }
            productosRedirectWithAlert('Elemento actualizado exitosamente.', 'productos_admin.php');
        }

        if ($newImage !== null && is_file("../img/" . $newImage)) {
            unlink("../img/" . $newImage);
        }
        productosRedirectWithAlert('Error al actualizar el elemento: ' . mysqli_error($db), 'productos_edit.php?id=' . $id);
        break;

    default:
        header('Location: productos_admin.php');
        exit();
}

mysqli_close($db);
?>
