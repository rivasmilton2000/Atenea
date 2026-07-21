<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/database_backups.php';
$pdo=obtenerConexion();$id=0;$ruta=null;$tempDb='';$ok=[];$assert=static function(bool $condicion,string $mensaje)use(&$ok):void{if(!$condicion)throw new RuntimeException('FALLO: '.$mensaje);$ok[]=$mensaje;};
try{
    $actor=(int)$pdo->query("SELECT id FROM usuarios WHERE rol='admin' AND es_superadmin=1 AND estado='activo' AND deleted_at IS NULL ORDER BY id LIMIT 1")->fetchColumn();
    $assert($actor>0,'Existe un Superadministrador autorizado para las pruebas de respaldo');
    $respaldo=crearRespaldoBaseDatos($actor);$id=(int)$respaldo['id'];$ruta=respaldoRutaAbsoluta((string)$respaldo['ruta_relativa']);
    $assert($respaldo['estado']==='disponible'&&is_file($ruta),'La creación genera un archivo privado disponible');
    $assert(dirname($ruta)!==realpath(ATENEA_ROOT)&&!str_starts_with(str_replace('\\','/',$ruta),str_replace('\\','/',realpath(ATENEA_ROOT)).'/'),'El archivo permanece fuera del directorio público');
    $assert(preg_match('/^atenea-db-\d{8}-\d{6}-[a-f0-9]{12}\.atenea-db\.gz$/D',(string)$respaldo['nombre_archivo'])===1,'El nombre del archivo es seguro y no contiene datos de conexión');
    $assert(hash_equals((string)$respaldo['sha256'],hash_file('sha256',$ruta)),'La copia registra y valida su hash SHA-256');
    $header=false;$tablas=[];$filas=0;foreach(leerRegistrosRespaldo($ruta) as $registro){if(($registro['kind']??'')==='header')$header=(($registro['format']??'')===ATENEA_BACKUP_FORMAT);if(($registro['kind']??'')==='table')$tablas[(string)$registro['name']]=$registro;if(($registro['kind']??'')==='row')$filas++;}
    $assert($header,'La copia utiliza el formato versionado de Atenea');
    $assert(isset($tablas['usuarios'])&&isset($tablas['audit_logs']),'La copia incluye estructura y datos persistentes necesarios');
    $assert(!isset($tablas[ATENEA_BACKUP_TRACKING_TABLE]),'El historial operativo de copias no se sobrescribe al restaurar');
    foreach(respaldoTablasSinDatos() as $tabla){if($tabla===ATENEA_BACKUP_TRACKING_TABLE||!isset($tablas[$tabla]))continue;$assert(($tablas[$tabla]['data_included']??true)===false,'La tabla temporal '.$tabla.' conserva estructura sin datos');}
    $assert($filas===(int)$respaldo['filas_incluidas']&&$filas>0,'El historial registra la cantidad real de filas incluidas');
    $tempDb='atenea_backup_test_'.bin2hex(random_bytes(5));$pdo->exec('CREATE DATABASE `'.$tempDb.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $prueba=new PDO('mysql:host='.entornoAtenea('ATENEA_DB_HOST','localhost').';dbname='.$tempDb.';charset=utf8mb4',entornoAtenea('ATENEA_DB_USER','root'),entornoAtenea('ATENEA_DB_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
    $restauradas=restaurarArchivoRespaldo($prueba,$ruta);$assert($restauradas===$filas,'El motor restaura todas las filas en una base de datos desechable');
    $assert((int)$prueba->query('SELECT COUNT(*) FROM usuarios')->fetchColumn()===(int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn(),'La restauración desechable conserva los usuarios y sus relaciones');
    $assert((int)$prueba->query('SELECT COUNT(*) FROM auth_remember_tokens')->fetchColumn()===0,'La restauración no recupera sesiones o accesos temporales');
    $q=$pdo->prepare("SELECT COUNT(*) FROM audit_logs WHERE module='backups' AND entity_id=:id AND event_type='database.backup.created'");$q->execute(['id'=>$id]);$assert((int)$q->fetchColumn()===1,'La creación queda registrada en auditoría');
    $download=file_get_contents(dirname(__DIR__,2).'/src/dashboard/backups/descargar.php');$assert(str_contains($download,"REQUEST_METHOD")&&str_contains($download,'validarTokenCsrf')&&str_contains($download,'verificarIntegridadRespaldo'),'La descarga exige sesión administrativa, POST, CSRF e integridad');
    $action=file_get_contents(dirname(__DIR__,2).'/src/dashboard/backups/accion.php');$assert(str_contains($action,"'RESTAURAR '.\$id")&&str_contains($action,'confirmar_riesgo')&&str_contains($action,'reautenticacionAdminValida'),'El backend exige frase, aceptación de riesgo y contraseña para restaurar');
    $service=file_get_contents(dirname(__DIR__,2).'/includes/database_backups.php');$inicioRestore=strpos($service,'function restaurarRespaldoBaseDatos');$seccionRestore=substr($service,$inicioRestore);$assert(strpos($seccionRestore,"crearRespaldoBaseDatos(\$actorId,'previo_restauracion',false)")<strpos($seccionRestore,'restaurarArchivoRespaldo($pdo,$ruta'),'La copia previa automática ocurre antes de modificar la base');
    $assert(str_contains($service,'BACKUP_RETENTION_DAYS')&&str_contains($service,'BACKUP_MAX_FILES'),'La retención por días y cantidad es configurable');
    $page=file_get_contents(dirname(__DIR__,2).'/src/dashboard/backups/index.php');$assert(str_contains($page,'AteneaAlerts.confirm')&&str_contains($page,'Segunda confirmación'),'La interfaz incorpora la segunda confirmación mediante SweetAlert');
    eliminarRespaldoBaseDatos($id,$actor);$ruta=null;
    $q=$pdo->prepare("SELECT estado,eliminado_at FROM respaldos_base_datos WHERE id=:id");$q->execute(['id'=>$id]);$eliminado=$q->fetch();$assert($eliminado['estado']==='eliminado'&&!empty($eliminado['eliminado_at']),'La eliminación es controlada y conserva el historial');
    echo 'OK '.count($ok)." pruebas\n";foreach($ok as $mensaje)echo '- '.$mensaje."\n";
}finally{
    if($ruta&&is_file($ruta))@unlink($ruta);
    if($tempDb!=='')try{$pdo->exec('DROP DATABASE IF EXISTS `'.$tempDb.'`');}catch(Throwable){}
    if($id){$pdo->prepare("DELETE FROM audit_logs WHERE module='backups' AND entity_id=:id")->execute(['id'=>$id]);$pdo->prepare('DELETE FROM respaldos_base_datos WHERE id=:id')->execute(['id'=>$id]);}
}
