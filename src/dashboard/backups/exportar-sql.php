<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/cms.php';
require_once dirname(__DIR__, 3) . '/includes/database_sql_export.php';

exigirPermiso('backups.manage');

$esJson = str_contains(strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json')
    || strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !validarTokenCsrf((string)($_POST['csrf_token'] ?? ''))) {
    if ($esJson) { http_response_code(419); header('Content-Type: application/json; charset=UTF-8'); echo json_encode(['ok'=>false,'mensaje'=>'La solicitud venció. Recarga la página e inténtalo nuevamente.'], JSON_UNESCAPED_UNICODE); exit; }
    http_response_code(419); mostrarPaginaErrorAtenea(419);
}

$archivo = null;
try {
    set_time_limit(0);
    $pdo = obtenerConexion();
    $opciones = normalizarExportacionSqlAtenea($pdo, (string)($_POST['alcance'] ?? ''), (string)($_POST['contenido'] ?? ''), isset($_POST['tabla']) ? (string)$_POST['tabla'] : null);
    $archivo = crearArchivoExportacionSqlAtenea($pdo, $opciones);
    registrarAuditoria([
        'actor_user_id'=>(int)$_SESSION['usuario_id'],
        'event_type'=>'database.sql_export.generated',
        'module'=>'backups',
        'entity_type'=>'database_sql_export',
        'action'=>'export',
        'result'=>'success',
        'description'=>'Un administrador generó una copia SQL de la base de datos.',
        'metadata'=>['scope'=>$opciones['alcance'],'content'=>$opciones['contenido'],'table'=>$opciones['tabla'],'tables'=>$archivo['tablas'],'rows'=>$archivo['filas'],'size_bytes'=>$archivo['tamano']],
    ], $pdo);
    session_write_close();
    header('Content-Type: application/sql; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $archivo['nombre'] . '"');
    header('Content-Length: ' . (int)$archivo['tamano']);
    header('Cache-Control: no-store, no-cache, must-revalidate, private');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');
    $entrada = fopen((string)$archivo['ruta'], 'rb');
    if (!$entrada) throw new RuntimeException('No fue posible abrir la copia SQL generada.');
    while (!feof($entrada)) { $bloque = fread($entrada, 1048576); if ($bloque === false) break; echo $bloque; flush(); }
    fclose($entrada); @unlink((string)$archivo['ruta']); exit;
} catch (DomainException $e) {
    if (is_array($archivo) && !empty($archivo['ruta'])) @unlink((string)$archivo['ruta']);
    registrarAuditoria(['actor_user_id'=>(int)($_SESSION['usuario_id']??0),'event_type'=>'database.sql_export.rejected','module'=>'backups','entity_type'=>'database_sql_export','action'=>'export','result'=>'failure','description'=>'Se rechazó una solicitud de exportación SQL.','metadata'=>['reason'=>$e->getMessage()]]);
    if ($esJson) { http_response_code(422); header('Content-Type: application/json; charset=UTF-8'); echo json_encode(['ok'=>false,'mensaje'=>$e->getMessage()], JSON_UNESCAPED_UNICODE); exit; }
    cmsFlash('error', $e->getMessage()); header('Location:index.php'); exit;
} catch (Throwable $e) {
    if (is_array($archivo) && !empty($archivo['ruta'])) @unlink((string)$archivo['ruta']);
    error_log('Exportación SQL Atenea: ' . $e->getMessage());
    registrarAuditoria(['actor_user_id'=>(int)($_SESSION['usuario_id']??0),'event_type'=>'database.sql_export.failed','module'=>'backups','entity_type'=>'database_sql_export','action'=>'export','result'=>'failure','description'=>'Falló la generación de una copia SQL.']);
    if ($esJson) { http_response_code(500); header('Content-Type: application/json; charset=UTF-8'); echo json_encode(['ok'=>false,'mensaje'=>'No fue posible generar la copia SQL.'], JSON_UNESCAPED_UNICODE); exit; }
    cmsFlash('error','No fue posible generar la copia SQL.'); header('Location:index.php'); exit;
}

