<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/academico_flujo.php';

exigirAutenticacion();
$pdo = obtenerConexion();
$tipo = (string) ($_GET['tipo'] ?? '');
$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$usuarioId = (int) $_SESSION['usuario_id'];
$rol = (string) $_SESSION['usuario_rol'];
$registro = null;
$permitido = false;

if ($tipo === 'contenido') {
    $consulta = $pdo->prepare(
        "SELECT c.*, i.usuario_id AS estudiante
         FROM contenidos c
         LEFT JOIN inscripciones_capacitacion i
           ON i.seccion_id = c.seccion_id
          AND i.usuario_id = :usuario_id
          AND i.estado IN ('inscrito', 'finalizado')
         WHERE c.id = :contenido_id
           AND c.eliminado_at IS NULL"
    );
    $consulta->execute(['usuario_id' => $usuarioId, 'contenido_id' => $id]);
    $registro = $consulta->fetch();
    $permitido = $registro && (
        $rol === 'admin'
        || ($rol === 'docente'
            && (int) $registro['docente_id'] === $usuarioId
            && docentePoseeSeccion($pdo, $usuarioId, (int) $registro['seccion_id']))
        || ($rol === 'usuario'
            && (int) ($registro['estudiante'] ?? 0) === $usuarioId
            && (int) $registro['activo'] === 1
            && $registro['estado'] === 'activo'
            && (!$registro['fecha_publicacion'] || strtotime($registro['fecha_publicacion']) <= time()))
    );
} elseif ($tipo === 'evidencia') {
    $consulta = $pdo->prepare(
        'SELECT ev.*, e.estudiante_id, e.seccion_id, c.docente_id
         FROM entrega_evidencias ev
         INNER JOIN entregas_contenido e ON e.id = ev.entrega_id
         INNER JOIN contenidos c ON c.id = e.contenido_id
         WHERE ev.id = :id'
    );
    $consulta->execute(['id' => $id]);
    $registro = $consulta->fetch();
    $permitido = $registro && (
        $rol === 'admin'
        || ($rol === 'usuario' && (int) $registro['estudiante_id'] === $usuarioId)
        || ($rol === 'docente'
            && (int) $registro['docente_id'] === $usuarioId
            && docentePoseeSeccion($pdo, $usuarioId, (int) $registro['seccion_id']))
    );
} elseif ($tipo === 'certificado') {
    $consulta = $pdo->prepare(
        "SELECT id, pdf_relpath AS archivo_relpath, numero AS archivo_nombre,
                'application/pdf' AS archivo_mime, estudiante_id
         FROM certificados_capacitacion
         WHERE id = :id AND estado = 'emitido'"
    );
    $consulta->execute(['id' => $id]);
    $registro = $consulta->fetch();
    $permitido = $registro && (
        $rol === 'admin'
        || ($rol === 'usuario' && (int) $registro['estudiante_id'] === $usuarioId)
    );
}

if (!$registro || !$permitido) {
    http_response_code(403);
    exit('Acceso denegado.');
}

$ruta = rutaPrivadaAcademica((string) $registro['archivo_relpath']);
if (!$ruta) {
    http_response_code(404);
    exit('Archivo no disponible.');
}

$mime = (string) ($registro['archivo_mime'] ?? 'application/octet-stream');
$nombre = preg_replace('/[^A-Za-z0-9._-]/', '-', basename((string) ($registro['archivo_nombre'] ?? 'archivo')));
if ($tipo === 'certificado') {
    $nombre .= '.pdf';
}
$tamano = filesize($ruta);

header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
header('Content-Type: ' . $mime);
header('Accept-Ranges: bytes');

$inicio = 0;
$fin = $tamano - 1;
if (isset($_SERVER['HTTP_RANGE'])
    && preg_match('/bytes=(\d*)-(\d*)/', (string) $_SERVER['HTTP_RANGE'], $coincidencia)) {
    $inicio = $coincidencia[1] !== '' ? (int) $coincidencia[1] : 0;
    $fin = $coincidencia[2] !== '' ? min((int) $coincidencia[2], $fin) : $fin;
    if ($inicio > $fin) {
        http_response_code(416);
        exit;
    }
    http_response_code(206);
    header("Content-Range: bytes {$inicio}-{$fin}/{$tamano}");
}

header('Content-Length: ' . ($fin - $inicio + 1));
$inline = str_starts_with($mime, 'video/') || str_starts_with($mime, 'image/');
header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $nombre . '"');

$manejador = fopen($ruta, 'rb');
fseek($manejador, $inicio);
$restante = $fin - $inicio + 1;
while ($restante > 0 && !feof($manejador)) {
    $bloque = fread($manejador, min(8192, $restante));
    echo $bloque;
    $restante -= strlen($bloque);
}
fclose($manejador);
