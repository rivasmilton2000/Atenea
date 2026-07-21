<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/includes/cms.php';
require_once dirname(__DIR__,3).'/includes/database_sql_export.php';
exigirPermiso('backups.view');
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf((string)($_POST['csrf_token']??''))){http_response_code(419);mostrarPaginaErrorAtenea(419);}
$id=cmsId($_POST['id']??0);$respaldo=respaldoPorId($id);if(!$respaldo){http_response_code(404);mostrarPaginaErrorAtenea(404);}
$archivo=null;
try{$archivo=crearArchivoSqlDesdeRespaldoAtenea($respaldo);}catch(Throwable $e){error_log('Descarga SQL histórica Atenea: '.$e->getMessage());http_response_code(404);mostrarPaginaErrorAtenea(404);}
registrarAuditoria(['actor_user_id'=>$_SESSION['usuario_id'],'event_type'=>'database.backup.downloaded','module'=>'backups','entity_type'=>'database_backup','entity_id'=>$id,'action'=>'download','result'=>'success','description'=>'Un administrador descargó una copia histórica convertida a SQL.','metadata'=>['format'=>'sql','tables'=>$archivo['tablas'],'rows'=>$archivo['filas'],'size_bytes'=>$archivo['tamano']]]);
session_write_close();header('Content-Type: application/sql; charset=UTF-8');header('Content-Disposition: attachment; filename="'.$archivo['nombre'].'"');header('Content-Length: '.(int)$archivo['tamano']);header('Cache-Control: no-store, no-cache, must-revalidate, private');header('Pragma: no-cache');header('X-Content-Type-Options: nosniff');$f=fopen((string)$archivo['ruta'],'rb');if(!$f){@unlink((string)$archivo['ruta']);http_response_code(404);exit;}while(!feof($f)){$bloque=fread($f,1048576);if($bloque===false)break;echo $bloque;flush();}fclose($f);@unlink((string)$archivo['ruta']);exit;
