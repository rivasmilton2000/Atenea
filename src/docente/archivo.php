<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';
require_once dirname(__DIR__, 2) . '/includes/academico_flujo.php';

$pdo = obtenerConexion();
$tipo = (string) ($_GET['tipo'] ?? '');
$id = docenteId($_GET['id'] ?? 0);
$docenteId = docenteSupervisadoAtenea($pdo);
if ($tipo !== 'contenido') mostrarPaginaErrorAtenea(404);

$q = $pdo->prepare('SELECT * FROM contenidos WHERE id=:id AND docente_id=:d');
$q->execute(['id' => $id, 'd' => $docenteId]);
$archivo = $q->fetch();
if (!$archivo || !$archivo['archivo_relpath']) mostrarPaginaErrorAtenea(404);

exigirCursoDocente($pdo, $docenteId, (int) $archivo['asignatura_id']);
$path = rutaPrivadaAcademica((string) $archivo['archivo_relpath']);
if ($path === null) mostrarPaginaErrorAtenea(404);

header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
header('Content-Type: ' . $archivo['archivo_mime']);
header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9._-]/', '-', basename($archivo['archivo_nombre'])) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
