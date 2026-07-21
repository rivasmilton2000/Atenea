<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/includes/cms.php';
require_once dirname(__DIR__,3).'/includes/database_backups.php';
exigirPermiso('backups.view');
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf((string)($_POST['csrf_token']??''))){http_response_code(419);mostrarPaginaErrorAtenea(419);}
$id=cmsId($_POST['id']??0);$respaldo=respaldoPorId($id);if(!$respaldo){http_response_code(404);mostrarPaginaErrorAtenea(404);}
try{$ruta=verificarIntegridadRespaldo($respaldo);}catch(Throwable){http_response_code(404);mostrarPaginaErrorAtenea(404);}
registrarAuditoria(['actor_user_id'=>$_SESSION['usuario_id'],'event_type'=>'database.backup.downloaded','module'=>'backups','entity_type'=>'database_backup','entity_id'=>$id,'action'=>'download','result'=>'success','description'=>'Un administrador descargó una copia protegida de la base de datos.']);
session_write_close();header('Content-Type: application/gzip');header('Content-Disposition: attachment; filename="'.basename((string)$respaldo['nombre_archivo']).'"');header('Content-Length: '.filesize($ruta));header('Cache-Control: no-store, no-cache, must-revalidate, private');header('Pragma: no-cache');header('X-Content-Type-Options: nosniff');$f=fopen($ruta,'rb');if(!$f){http_response_code(404);exit;}while(!feof($f)){echo fread($f,1048576);flush();}fclose($f);exit;
